<?php

namespace app\member\controller\api;
use app\ApiController;

use app\member\model\AccountModel;
use app\member\model\AccountLogModel;
use app\member\model\UsersModel;
use app\member\model\RechargeLogModel;
use app\mainadmin\model\PaymentModel;
use app\distribution\model\DividendModel;
use app\distribution\model\DividendAwardModel;
use app\weixin\model\WeiXinUsersModel;

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
        $this->checkLogin();//验证登陆
        $this->Model = new AccountModel();
    }
    /*------------------------------------------------------ */
    //-- 获取帐户信息
    /*------------------------------------------------------ */
    public function getWalletInfo()
    {
        $data['account'] = $this->Model->getWalletInfo($this->userInfo['user_id']);

        $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取余额信息
    /*------------------------------------------------------ */
    public function getBalanceInfo()
    {
        $data['account'] = $this->Model->getWalletInfo($this->userInfo['user_id']);
        $data['withdrawing'] = $this->Model->getWithdrawing($this->userInfo['user_id']);
        $data['wait_returned'] = $this->Model->getWaitReturned($this->userInfo['user_id']);
        $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取收款信息
    /*------------------------------------------------------ */
    public function getPaymentList()
    {
        $paymentList = [];
        $userInfo = (new UsersModel)->info($this->userInfo['user_id']);
        $settings = settings();

        if ($userInfo['weixin_usd'] == 1 && $settings['member_receive_wxpay'] == 1){
            $row['payment_code'] = 'wxpay';
            $row['payment_name']  = '微信收款';
            $row['payment_account'] = $userInfo['weixin_account'];
            $paymentList[] = $row;
        }
        if ($userInfo['alipay_usd'] == 1 && $settings['member_receive_alipay'] == 1){
            $row['payment_code'] = 'alipay';
            $row['payment_name'] = '支付宝';
            $row['payment_account'] = $userInfo['alipay_account'];
            $paymentList[] = $row;
        }
        if ($userInfo['bank_usd'] == 1 && $settings['member_receive_bank'] == 1){
            $row['payment_code'] = 'bank';
            $row['payment_name'] = $userInfo['bank_name'];
            $row['payment_account'] = $userInfo['bank_card_number'];
            $paymentList[] = $row;
        }
        if($settings['wxbag_is_used'] == 1){
            $platform = input('platform','','trim');
            if($platform == 'H5-WX'){
                $WeiXinUsersModel = new WeiXinUsersModel();
                $wxUser = $WeiXinUsersModel->where('user_id',$this->userInfo['user_id'])->find();
                $row['payment_code'] = 'wxbag';
                $row['payment_name'] = '微信零钱';
                $row['payment_account'] = '零钱钱包';
                $row['wx_nickname'] = '';
                $row['wx_headimgurl'] = '';
                $row['wx_openid'] = '';
                $row['is_auth'] = 0;
                if($wxUser){
                    $row['payment_account'] = $wxUser['wx_nickname'];
                    if(mb_strlen($row['payment_account']) > 8){
                        $row['payment_account'] = substr($wxUser['wx_nickname'],0,8).'..';
                    }
                    $row['wx_nickname'] = $wxUser['wx_nickname'];
                    $row['wx_headimgurl'] = $wxUser['wx_headimgurl'];
                    $row['wx_openid'] = substr($wxUser['wx_openid'],0,6).'******';
                    $row['is_auth'] = 1;
                }
                $paymentList[] = $row;
            }
        }
        $data['paymentList'] = $paymentList;
        $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取余额日志列表
    /*------------------------------------------------------ */
    public function getAccountLog()
    {
        $searchType = input('searchType','all','trim');
        $calendar = input('calendar','','trim');
        $pageType = input('pageType','balance','trim');
        if (empty($calendar)){
            $startDate = date('Y-m-01', time());
            $startTime = strtotime($startDate);
            $endTime =  strtotime("$startDate +1 month") - 1;
            $endDate = date('Y-m-d', $endTime);
        }else{
            $startTime = strtotime($calendar);
            $endTime =  strtotime("$calendar +1 month") - 1;
        }
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $where[] = ['change_time', 'between', [$startTime, $endTime]];
        $AccountLogModel = new AccountLogModel();
        $searchField = 'balance_money';
        if ($pageType == 'points'){//积分查询
            $searchField = 'use_integral';
        }
        if ($searchType == 'income') {//收入
            $where[] = [$searchField, '>', 0];
        }elseif ($searchType == 'outlay'){//支出
            $where[] = [$searchField,'<',0];
        }else{
            $where[] = [$searchField,'<>',0];
        }
        $data = $this->getPageList($AccountLogModel, $where,'log_id,change_time,balance_money,use_integral,change_desc,from_uid,old_balance_money,old_use_integral',12);
        foreach ($data['list'] as $key=>$row){
            $row['change_time'] = dateTpl($row['change_time'],'Y.m.d',true);
            if ($pageType == 'points'){
                $row['now_val'] = bcadd($row['use_integral'],$row['old_use_integral'],2);
            }else{
                $row['now_val'] = bcadd($row['balance_money'],$row['old_balance_money'],2);
            }
            $data['list'][$key] = $row;
        }

        $p = input('p','1','intval');
        if ($searchType == 'all' && $p == 1){//全部，并且为第一页时统计收入和支出
            $_where = $where;
            $_where[] = [$searchField, '>', 0];
            $data['income'] = $AccountLogModel->where($_where)->SUM($searchField);
            $_where = $where;
            $_where[] = [$searchField, '<', 0];
            $data['outlay'] = $AccountLogModel->where($_where)->SUM($searchField);
            $data['outlay'] = $data['outlay'] * -1;
            if ($pageType == 'points'){
                $data['balance'] = $this->userInfo['account']['use_integral'];
            }else{
                $data['balance'] = $this->userInfo['account']['balance_money'];
            }

        }
        return $this->success($data);
    }

    /*------------------------------------------------------ */
    //-- 充值日志列表
    /*------------------------------------------------------ */
    public function getRechargeLog()
    {
        $time = input('time', '', 'trim');
        if (empty($time)) {
            $time = date('Y-m');
        }
        $_time = strtotime($time);
        $RechargeLogModel = new RechargeLogModel();
        $where[] = ['user_id', '=', $this->userInfo['user_id']];
        $where[] = ['add_time','between',array($_time,strtotime(date('Y-m-t',$_time))+86399)];
        $rows = $RechargeLogModel->where($where)->order('add_time DESC')->select()->toArray();
        $lang = lang('recharge');
        $data = [];
        foreach ($rows as $key => $row) {
            $row['_time'] = timeTran($row['add_time']);
            $row['order_amount'] = $row['order_amount'];
            $row['status_lang'] = $lang[$row['status']];
            $row['imglist'] = [];
            if (empty($row['imgs']) == false){
                $row['imglist'] = explode(',',$row['imgs']);
            }
            $data['list'][] = $row;
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
        $data['payList'] = [];
        $isOffLinePay = 0;
        foreach ($payList as $pay){
            if ($pay['is_recharge'] == 0){
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
            $data['offline']['is_sys'] = 1;
            $data['offline']['weixin_usd'] = empty($settings['receive_weixin_usd']) ? 0 : 1;
            $data['offline']['alipay_usd'] = empty($settings['receive_alipay_usd']) ? 0 : 1;
            $data['offline']['bank_usd'] = empty($settings['receive_bank_usd']) ? 0 : 1;
            if ($data['offline']['weixin_usd'] == 1) {
                $data['offline']['is_usd'] = 1;
                $data['offline']['weixin_qrcode'] = $settings['receive_weixin_qrcode'];
                $data['offline']['weixin_name'] = $settings['receive_weixin_name'];
                $data['offline']['weixin_account'] = $settings['receive_weixin_account'];
            }
            if ($data['offline']['alipay_usd'] == 1) {
                $data['offline']['is_usd'] = 1;
                $data['offline']['alipay_qrcode'] = $settings['receive_alipay_qrcode'];
                $data['offline']['alipay_name'] = $settings['receive_alipay_name'];
                $data['offline']['alipay_account'] = $settings['receive_alipay_account'];
            }
            if ($data['offline']['bank_usd'] == 1) {
                $data['offline']['is_usd'] = 1;
                $data['offline']['bank_name'] = $settings['receive_bank_name'];
                $data['offline']['bank_subbranch'] = $settings['receive_bank_subbranch'];
                $data['offline']['bank_user_name'] = $settings['receive_bank_user_name'];
                $data['offline']['bank_card_number'] = $settings['receive_bank_card_number'];
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
    //-- 获取会员佣金日志
    /*------------------------------------------------------ */
    public function getDividendLog()
    {
        $type = input('type', 'all_balance_money', 'trim');
        $rewardType = input('rewardType','all','trim');
        $calendar = input('calendar','','trim');
        if (empty($calendar)){
            $startDate = date('Y-m-01', time());
            $startTime = strtotime($startDate);
            $endTime =  strtotime("$startDate +1 month") - 1;
        }else{
            $startTime = strtotime($calendar);
            $endTime =  strtotime("$calendar +1 month") - 1;
        }
        $DividendModel = new DividendModel();
        $where[] = ['dividend_uid', '=', $this->userInfo['user_id']];
        if ($rewardType != 'all'){
            $where[] = ['award_id', '=', intval($rewardType)];
        }
        $where[] = ['add_time', 'between', [$startTime, $endTime]];
        switch ($type) {
            case 'all_balance_money'://佣金
                $where[] = ['dividend_amount', '<>', 0];
                break;
            case 'wait_balance_money'://等待分佣金
                $where[] = ['dividend_amount', '<>', 0];
                $where[] = ['status', '=', 3];
                break;
            case 'arrival_balance_money'://已到帐明细
                $where[] = ['dividend_amount', '<>', 0];
                $where[] = ['status', '=', 9];
                break;
            case 'cancel_balance_money'://失效明细
                $where[] = ['dividend_amount', '<>', 0];
                $where[] = ['status', 'in', [10, 11]];
                break;
            default:
                return $this->error('类型错误.');
                break;
        }
        $data['income'] = 0;
        $rows = $DividendModel->where($where)->order('add_time DESC')->select();
        $lang = lang('order');
        $data['list'] = [];
        foreach ($rows as $key => $row) {
            $income = $row['dividend_amount'];
            $data['income'] = bcadd($data['income'],$income,2);
            $row['_time'] = timeTran($row['add_time']);
            $row['value'] = $income;
            $row['nick_name'] = userInfo($row['buy_uid']);
            $row['status'] = $lang['ds'][$row['status']];
            $data['list'][] = $row;
        }
        $data['income'] = priceFormat($data['income']);
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取收支明细
    /*------------------------------------------------------ */
    public function getLogInfo()
    {
        $log_id = input('log_id', '0', 'intval');
        if ($log_id <= 0){
            return $this->error('ID传值错误.');
        }
        $type = input('type', 'balance', 'trim');
        $where = [];
        $where[] = ['log_id','=',$log_id];
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $AccountLogModel = new AccountLogModel();
        $info = $AccountLogModel->where($where)->find();
        if (empty($info)){
            return $this->error('没有找到相关信息.');
        }
        $info = $info->toArray();
        $info['change_time'] = date('Y-m-d H:i:s',$info['change_time']);
        $info['log_type'] = 'income';
        $info['change_val'] = 0;
        if ($type == 'points'){//积分相关
            $info['change_val'] = $info['use_integral'];
            $info['balance'] = bcadd($info['use_integral'], $info['old_use_integral'],2);
        }else{
            $info['change_val'] = $info['balance_money'];
            $info['balance'] = bcadd($info['balance_money'], $info['old_use_integral'],2);
        }
        if ($info['change_val'] < 0){
            $info['log_type'] = 'outlay';
        }
        $info['desc'] = '';
        if (in_array($info['change_type'],[2,3,4])){//关联订单
            $OrderModel = new \app\shop\model\OrderModel();
            $orderInfo = $OrderModel->where('order_id',$info['by_id'])->find();
            $info['desc'] = '来源订单：'.$orderInfo['order_sn'].'，订单金额：￥'.$orderInfo['order_amount'].'，下单会员：'.$orderInfo['user_id'].'-'.userInfo($orderInfo['user_id']);
            if ($info['change_type'] == 4){
                $DividendModel = new \app\distribution\model\DividendModel();
                $dWhere = [];
                $dWhere[] = ['order_id','=',$info['by_id']];
                $dWhere[] = ['dividend_uid','=',$this->userInfo['user_id']];
                $dWhere[] = ['dividend_amount','=',$info['change_val']];
                $dlog = $DividendModel->where($dWhere)->find();
                $info['desc'] .= '，奖项：'.$dlog['award_name'].$dlog['level_award_name'];
            }
        }elseif($info['change_type'] == 5){//提现
            $WithdrawLogModel = new \app\member\model\WithdrawLogModel();
            $log = $WithdrawLogModel->where('log_id',$info['by_id'])->find();
            $account_type = $this->Model->account_type;
            $info['desc'] = '提现类型：'.$account_type[$log['account_type']].'，提现到帐：￥'.$log['arrival_money'].'，提现手续费：￥'.$log['withdraw_fee'].'，实扣余额：￥'.$log['real_money'].';';
        }
        return $this->success($info);
    }
}