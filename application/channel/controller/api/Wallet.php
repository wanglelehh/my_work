<?php

namespace app\channel\controller\api;
use app\channel\ApiController;

use app\channel\model\WalletModel;
use app\channel\model\AccountLogModel;
use app\channel\model\RechargeLogModel;
use app\channel\model\RewardLogModel;
use app\channel\model\RewardModel;
use app\channel\model\ProxyUsersModel;
use app\mainadmin\model\PaymentModel;
use app\member\model\RoleModel;
/*------------------------------------------------------ */
//-- 代理钱包相关
/*------------------------------------------------------ */

class Wallet extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new WalletModel();
    }
    /*------------------------------------------------------ */
    //-- 获取帐户信息
    /*------------------------------------------------------ */
    public function getWalletInfo()
    {
        $data['account'] = $this->Model->getWalletInfo($this->userInfo['user_id']);
        $income['day'] = $this->Model->countIncome($this->userInfo['user_id'],'day');
        $income['month'] = $this->Model->countIncome($this->userInfo['user_id'],'month');
        $income['day_pre'] = $income['month'] <= 0 ? 0 : priceFormat($income['day'] / $income['month'] * 100);
        $total_income = $data['account']['total_income'] + $data['account']['total_brokerage'];
        $data['account']['total_income'] = $total_income;
        $income['month_pre'] = $income['month'] <= 0 ? 0 :  priceFormat($income['month'] / $total_income * 100);
        $data['income'] = $income;
        $data['xdjj_total'] = (new RewardModel)->where('reward_type','xdjj')->value('ext_count');
        $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取余额信息
    /*------------------------------------------------------ */
    public function getBalanceInfo()
    {
        $data['account'] = $this->Model->getWalletInfo($this->userInfo['user_id']);
        $data['withdrawing'] = $this->Model->getWithdrawing($this->userInfo['user_id']);
        $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取收款信息
    /*------------------------------------------------------ */
    public function getPaymentList()
    {
        $paymentList = [];
        $settings = settings();
        if ($this->userInfo['weixin_usd'] == 1 && $settings['member_receive_wxpay'] == 1){
            $row['payment_code'] = 'wxpay';
            $row['payment_name']  = $this->Model->account_type['wxpay'];
            $row['payment_account'] = $this->userInfo['weixin_account'];
            $paymentList[] = $row;
        }
        if ($this->userInfo['alipay_usd'] == 1 && $settings['member_receive_alipay'] == 1){
            $row['payment_code'] = 'alipay';
            $row['payment_name'] = '支付宝';
            $row['payment_account'] = $this->userInfo['alipay_account'];
            $paymentList[] = $row;
        }
        if ($this->userInfo['bank_usd'] == 1 && $settings['member_receive_bank'] == 1){
            $row['payment_code'] = 'bank';
            $row['payment_name'] = $this->userInfo['bank_name'];
            $row['payment_account'] = $this->userInfo['bank_card_number'];
            $paymentList[] = $row;
        }
        $data['paymentList'] = $paymentList;
        $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取余额日志列表
    /*------------------------------------------------------ */
    public function getBalanceLog()
    {
        $searchType = input('searchType','all','trim');
        $calendar = input('calendar','','trim');
        if (empty($calendar)){
            $startDate = date('Y-m-01', time());
            $startTime = strtotime($startDate);
            $endTime =  strtotime("$startDate +1 month") - 1;
            $endDate = date('Y-m-d', $endTime);
        }else{
            $calendar = explode('~',$calendar);
            $startTime = strtotime($calendar[0]);
            $endTime = strtotime($calendar[1])+86399;
        }
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $where[] = ['change_time', 'between', [$startTime, $endTime]];
        $AccountLogModel = new AccountLogModel();
        if ($searchType == 'income') {//收入
            $where[] = ['balance_money', '>', 0];
        }elseif ($searchType == 'outlay'){//支出
            $where[] = ['balance_money','<',0];
        }else{
            $where[] = ['balance_money','<>',0];
        }
        $data = $this->getPageList($AccountLogModel, $where,'change_time,balance_money,change_desc',6);
        foreach ($data['list'] as $key=>$row){
            $row['change_time'] = dateTpl($row['change_time'],'Y-m-d H:i:s',true);
            $data['list'][$key] = $row;
        }
        if (empty($calendar)){
            $data['startDate'] = $startDate;
            $data['endDate'] = $endDate;
        }
        $p = input('p','1','intval');
        if ($searchType == 'all' && $p == 1){//全部，并且为第一页时统计收入和支出
            $_where = $where;
            $_where[] = ['balance_money', '>', 0];
            $data['income'] = $AccountLogModel->where($_where)->SUM('balance_money');
            $_where = $where;
            $_where[] = ['balance_money', '<', 0];
            $data['outlay'] = $AccountLogModel->where($_where)->SUM('balance_money');
            $data['outlay'] = $data['outlay'] * -1;
        }
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取货款收支统计列表
    /*------------------------------------------------------ */
    public function getGoodsMoneyStatic()
    {
        $data['goods_money'] = $this->userInfo['proxy_account']['goods_money'];
        $data['goods_money_total'] = $this->userInfo['proxy_account']['goods_money'] + $this->userInfo['proxy_account']['goods_money_outlay_total'];
        $data['goods_money_outlay_total'] = $this->userInfo['proxy_account']['goods_money_outlay_total'];
        $data['goods_money_total_pre'] = priceFormat($data['goods_money_outlay_total'] / $data['goods_money_total']  * 100) * 1;
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取货款日志列表
    /*------------------------------------------------------ */
    public function getGoodsMoneyLog()
    {
        $state = input('state','all','trim');
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        if ($state == 'inc') {//充值
            $where[] = ['goods_money', '>', 0];
        }elseif ($state == 'dec'){//支出
            $where[] = ['goods_money', '<', 0];
        }else{
            $where[] = ['goods_money', '<>', 0];
        }
        $AccountLogModel = new AccountLogModel();
        $data = $this->getPageList($AccountLogModel, $where,'',10);
        foreach ($data['list'] as $key=>$row){
            $row['change_time'] = dateTpl($row['change_time'],'Y-m-d H:i:s',true);
            $data['list'][$key] = $row;
        }
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取升级货款收支统计列表
    /*------------------------------------------------------ */
    public function getUplevelGoodsMoneyStatic()
    {
        $data['money'] = $this->userInfo['proxy_account']['uplevel_goods_money'];
        $data['money_total'] = $this->userInfo['proxy_account']['uplevel_goods_money_total'];
        $data['money_outlay_total'] = $this->userInfo['proxy_account']['uplevel_goods_money_total'] - $this->userInfo['proxy_account']['uplevel_goods_money'];
        $data['money_total_pre'] = priceFormat($data['money_outlay_total'] / $data['money_total']  * 100) * 1;
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取升级货款日志列表
    /*------------------------------------------------------ */
    public function getUplevelGoodsMoneyLog()
    {
        $state = input('state','all','trim');
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        if ($state == 'inc') {//充值
            $where[] = ['uplevel_goods_money', '>', 0];
        }elseif ($state == 'dec'){//支出
            $where[] = ['uplevel_goods_money', '<', 0];
        }else{
            $where[] = ['uplevel_goods_money', '<>', 0];
        }
        $AccountLogModel = new AccountLogModel();
        $data = $this->getPageList($AccountLogModel, $where,'',10);
        foreach ($data['list'] as $key=>$row){
            $row['change_time'] = dateTpl($row['change_time'],'Y-m-d H:i:s',true);
            $data['list'][$key] = $row;
        }
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取保证金收支统计列表
    /*------------------------------------------------------ */
    public function getEarnestMoneyStatic()
    {
        $data['earnest_money'] = $this->userInfo['proxy_account']['earnest_money'];
        $data['earnest_money_outlay_total'] = $this->userInfo['proxy_account']['earnest_money_outlay_total'];
        $data['earnest_money_total'] = $data['earnest_money'] + $data['earnest_money_outlay_total'];
        $data['earnest_money_total_pre'] = priceFormat($data['earnest_money_outlay_total'] / $data['earnest_money_total']  * 100) * 1;
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取保证金日志列表
    /*------------------------------------------------------ */
    public function getEarnestMoneyLog()
    {
        $state = input('state','all','trim');
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        if ($state == 'inc') {//充值
            $where[] = ['earnest_money', '>', 0];
        }elseif ($state == 'dec'){//支出
            $where[] = ['earnest_money', '<', 0];
        }else{
            $where[] = ['earnest_money', '<>', 0];
        }
        $AccountLogModel = new AccountLogModel();
        $data = $this->getPageList($AccountLogModel, $where,'',10);
        foreach ($data['list'] as $key=>$row){
            $row['change_time'] = dateTpl($row['change_time'],'Y-m-d H:i:s',true);
            $data['list'][$key] = $row;
        }
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 充值日志列表
    /*------------------------------------------------------ */
    public function getRechargeLog()
    {
        $type = input('type','','trim');
        $RechargeLogModel = new RechargeLogModel();
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $where[] = ['type','=',$type];
        $where[] = ['pay_time','>',0];
        $data = $this->getPageList($RechargeLogModel, $where,'',10);
        foreach ($data['list'] as $key=>$row){
            $row['add_time'] = dateTpl($row['add_time'],'Y-m-d H:i:s',true);
            $data['list'][$key] = $row;
        }
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 充值支付调用
    /*------------------------------------------------------ */
    public function getRechargePayInfo()
    {
        $settings = settings();
        $platform = input('platform','','trim');
        $payList = (new PaymentModel)->getRows($platform);
        $channel_recharge_payment = explode(',',$settings['channel_recharge_payment']);
        $data['payList'] = [];
        $isOffLinePay = 0;
        foreach ($payList as $pay){
            if (in_array($pay['pay_code'],$channel_recharge_payment) == false){
                continue;
            }
            if ($pay['pay_code'] == 'offline'){
                $isOffLinePay = 1;
            }
            $_pay['pay_id'] = $pay['pay_id'];
            $_pay['pay_code'] = $pay['pay_code'];
            $_pay['pay_name'] = $pay['pay_name'];
            $data['payList'][] = $_pay;
        }
        if ($isOffLinePay == 1){
            $data['offline']['weixin_usd'] = $settings['channel_weixin_usd'] * 1;
            $data['offline']['alipay_usd'] = $settings['channel_alipay_usd'] * 1;
            $data['offline']['bank_usd'] = $settings['channel_bank_usd'] * 1;
            if ($data['offline']['weixin_usd'] == 1) {
                $data['offline']['is_usd'] = 1;
                $data['offline']['weixin_qrcode'] = $settings['channel_weixin_qrcode'];
                $data['offline']['weixin_name'] = $settings['channel_weixin_name'];
                $data['offline']['weixin_account'] = $settings['channel_weixin_account'];
            }
            if ($data['offline']['alipay_usd'] == 1) {
                $data['offline']['is_usd'] = 1;
                $data['offline']['alipay_qrcode'] = $settings['channel_alipay_qrcode'];
                $data['offline']['alipay_name'] = $settings['channel_alipay_name'];
                $data['offline']['alipay_account'] = $settings['channel_alipay_account'];
            }
            if ($data['offline']['bank_usd'] == 1) {
                $data['offline']['bank_name'] = $settings['channel_bank_name'];
                $data['offline']['bank_subbranch'] = $settings['channel_bank_subbranch'];
                $data['offline']['bank_user_name'] = $settings['channel_bank_user_name'];
                $data['offline']['bank_card_number'] = $settings['channel_bank_card_number'];
            }
        }
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 充值提交
    /*------------------------------------------------------ */
    public function postRecharge()
    {
        $money = input('money') * 1;
        if ($money <= 0){
            return $this->error('请输入充值金额.');
        }
        $remark = input('remark','','trim');
        $type = input('type','','trim');
        $payCode = input('payCode','','trim');
        $fileList = input('fileList','','trim');
        if ($payCode == 'offline' && empty($fileList)){
            return $this->error('请上传付款凭证.');
        }
        $payList = (new PaymentModel)->getRows(false,'pay_code');
        if (empty($payList[$payCode])){
            return $this->error('支付方式错误，请重试.');
        }
        $order_id = $this->Model->saveRecharge($money,$type,$remark,$payList[$payCode],$payCode,$fileList);
        if (is_numeric($order_id) == false){
            return $this->error($order_id);
        }
        $data['order_id'] = $order_id;
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取奖励日志列表
    /*------------------------------------------------------ */
    public function getRewardLog()
    {
        $state = input('state','all','trim');
        $rewardType = input('rewardType','all','trim');
        $calendar = input('calendar','','trim');
        if (empty($calendar)){
            //获取当前用户参与的奖项
            $rewardList = (new RewardModel)->getJoinReward($this->userInfo['proxy_id'],$this->userInfo['region_proxy']);
            $startDate = date('Y-m-01', time());
            $startTime = strtotime($startDate);
            $endTime =  strtotime("$startDate +1 month") - 1;
            $endDate = date('Y-m-d', $endTime);
        }else{
            $calendar = explode('~',$calendar);
            $startTime = strtotime($calendar[0]);
            $endTime = strtotime($calendar[1])+86399;
        }
        $where[] = ['to_uid','=',$this->userInfo['user_id']];
        if ($rewardType != 'all'){
            $where[] = ['reward_type', '=', $rewardType];
        }
        $where[] = ['add_time', 'between', [$startTime, $endTime]];
        if ($state == '1') {//待返
            $where[] = ['status', '<=', 1];
        }elseif ($state == '2'){//已返
            $where[] = ['status', '=', 2];
        }elseif ($state == '9'){//已取消
            $where[] = ['status', '=', 9];
        }
        $RewardLogModel = new RewardLogModel();
        $data = $this->getPageList($RewardLogModel, $where,'',10);
        $RoleModel = new RoleModel();
        $roleList = $RoleModel->getRows();
        foreach ($data['list'] as $key=>$row){
            $row['add_time'] = dateTpl($row['add_time'],'Y-m-d H:i:s',true);
            $row['to_role_name'] = $roleList[$row['to_role_id']]['role_name'];
            $row['status'] = $RewardLogModel->status[$row['status']];
            $data['list'][$key] = $row;
        }
        if (empty($calendar)){
            $data['rewardList'] = $rewardList;
            $data['startDate'] = $startDate;
            $data['endDate'] = $endDate;
        }
        return $this->success($data);
    }
}