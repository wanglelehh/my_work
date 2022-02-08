<?php
namespace app\publics\controller\api;
use app\ApiController;
use think\Db;

use app\mainadmin\model\PaymentModel;
use app\member\model\UsersModel;

/*------------------------------------------------------ */
//-- 支付相关API
/*------------------------------------------------------ */
class Pay extends ApiController{

    public $payment; //  具体的支付类
    public $pay_code; //  具体的支付code
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new PaymentModel();
    }

    /*------------------------------------------------------ */
    //-- 支付处理
    /*------------------------------------------------------ */
    public function getCode()
    {
        // 获取支付方式
        $pay_code = input('pay_code');
        unset($_GET['pay_code']); // 用完之后删除, 以免进入签名判断里面去 导致错误
        define('SITE_URL',config('config.host_path'));
        // 导入具体的支付类文件
        $code = str_replace('/', '\\', "/payment/{$pay_code}/{$pay_code}");
        $this->payment = new $code();
        $order_id = input('order_id/d'); // 订单id
        $_from = input('_from','','trim'); // 来源
        $payType = input('payType','','trim'); // 类型

        $config = parseUrlParam($this->pay_code); // 类似于 pay_code=alipay&bank_code=CCB-DEBIT 参数

        $payment = (new PaymentModel)->where('pay_code', $pay_code)->find();
        if (empty($payment)){
            return $this->error('相关支付方式不存在.');
        }
        if ($payment['status'] == 0){
            return $this->error('相关支付方式已停用.');
        }

        if ($_from == 'shop'){ //
            if ($payType == 'order' || $payType == 'fgorder') { //订单
                $OrderModel = new \app\shop\model\OrderModel();
                $order = $OrderModel->where("order_id", $order_id)->find();
                if ($order['pay_status'] == 1 || $order['pay_status'] == 2) {
                    return $this->error('此订单，已完成.');
                }
                if ($order['order_status'] == 2) {
                    return $this->error('此订单，已取消不能执行支付.');
                }
                $upArr['pay_code'] = $payment['pay_code'];
                $upArr['pay_id'] =  $payment['pay_id'];
                $upArr['pay_name'] = $payment['pay_name'];
                $OrderModel->where('order_id',$order_id)->update($upArr);
                $config['body'] = $OrderModel->getPayBody($order_id);
            }else{
                return $this->error('未知操作.');
            }
        } elseif ($_from == 'member') {
            if ($payType == 'recharge') { //充值
                $config['body'] = '在线充值';
                $RechargeLogModel = new \app\member\model\RechargeLogModel();
                $order = $RechargeLogModel->where('order_id', $order_id)->find();
                $upArr['pay_code'] = $payment['pay_code'];
                $upArr['pay_id'] =  $payment['pay_id'];
                $upArr['pay_name'] = $payment['pay_name'];
                $RechargeLogModel->where('order_id',$order_id)->update($upArr);
            }
        }elseif($_from == 'channel'){
            if ($payType == 'order') { //订单
                $OrderModel = new \app\channel\model\OrderModel();
                $order = $OrderModel->where("order_id", $order_id)->find();
                if ($order['pay_status'] == 1 || $order['pay_status'] == 2) {
                    return $this->error('此订单，已完成.');
                }
                if ($order['order_status'] == 2) {
                    return $this->error('此订单，已取消不能执行支付.');
                }
                $upArr['pay_code'] = $payment['pay_code'];
                $upArr['pay_id'] =  $payment['pay_id'];
                $upArr['pay_name'] = $payment['pay_name'];
                $OrderModel->where('order_id',$order_id)->update($upArr);
            }elseif ($payType == 'recharge'){ //充值
                $RechargeLogModel = new \app\channel\model\RechargeLogModel();
                $order = $RechargeLogModel->where("order_id", $order_id)->find();
                if ($order['status'] == 9){
                    return $this->error('已完成支付.');
                }
                $upArr['pay_code'] = $payment['pay_code'];
                $upArr['pay_id'] =  $payment['pay_id'];
                $upArr['pay_name'] = $payment['pay_name'];
                $RechargeLogModel->where('order_id',$order_id)->update($upArr);
            }else{
                return $this->error('未知操作.');
            }
        }

        if ($pay_code == 'weixin') {
            $openid = input('openid', '', 'trim');
            $trade_type = input('trade_type', '', 'trim');
            if ($trade_type == 'js') {
                //微信JS支付
                $payConfig = $this->payment->getJSAPI($order, $openid);
                if (is_array($payConfig) == false){
                    $payConfig = json_decode($payConfig,true);
                }
                if($payConfig['return_code'] == 'FAIL'){
                    return $this->error($payConfig['return_msg']);
                }
            } else {
                //微信H5支付
                $payConfig = $this->payment->get_H5code($order, $config);
                if ($payConfig['status'] == -1){
                    return $this->error($payConfig['msg']);
                }
            }
        }elseif ($pay_code == 'miniAppPay') {
            $openid = input('openid', '', 'trim');
            $payConfig = $this->payment->getJSAPI($order,$openid);
            $payConfig = json_decode($payConfig,true);
            if($payConfig['result_code'] == 'FAIL'){
                return $this->error($payConfig['err_code_des']);
            }
        }elseif ($pay_code == 'appWeixinPay') {
            $payConfig = $this->payment->get_code($order, $config);
            if($payConfig['return_code'] == 'FAIL'){
                return $this->error($payConfig['return_msg']);
            }
        }else{
            //其他支付（支付宝、银联...）
            $payConfig = $this->payment->get_code($order, $config);
        }
        if ($payConfig == false){
            return $this->error('处理支付失败.');
        }
        $data['payConfig'] = $payConfig;
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 余额支付
    /*------------------------------------------------------ */
    public function balancePay()
    {
        $payment = (new PaymentModel)->where('pay_code', 'balance')->find();
        if (empty($payment) || $payment['status'] != 1){
            return $this->error('支付方式错误.');
        }
        $_from = input('_from','shop','trim');
        $order_id = input('order_id/d'); // 订单id

        $pay_password = input('pay_password','','trim');
        $res = (new UsersModel)->checkPayPwd($this->userInfo['user_id'],$pay_password);
        if ($res !== true){
            return $this->error($res);
        }

        if ($_from == 'channel'){
            return $this->channelBalancePay($payment,$order_id);
        }else{
            return $this->publicBalancePay($payment,$order_id);
        }

    }
    /*------------------------------------------------------ */
    //-- 货款支付
    /*------------------------------------------------------ */
    public function goodsMoneyPay(){
        $order_id = input('order_id/d'); // 订单id
        $OrderModel = new \app\channel\model\OrderModel();
        $order = $OrderModel->where("order_id", $order_id)->find();
        if ($order['pay_status'] == 1 || $order['pay_status'] == 2) {
            return $this->error('此订单，已完成.');
        }
        if ($order['order_status'] == 2) {
            return $this->error('此订单，已取消不能执行支付.');
        }
        if ($order['user_id'] != $this->userInfo['user_id']){
            return $this->error('此订单你无权操作.');
        }

        $pay_password = input('pay_password','','trim');
        $res = (new UsersModel)->checkPayPwd($this->userInfo['user_id'],$pay_password);
        if ($res !== true){
            return $this->error($res);
        }

        $WalletModel = new \app\channel\model\WalletModel();
        $account = $WalletModel->getWalletInfo($this->userInfo['user_id'],false);//获取代理会员帐户信息
        if ($order['order_amount'] > $account['goods_money']) {
            return $this->error('货款余额不足，请使用其它支付方式.');
        }
        Db::startTrans();//启动事务
        $changedata['by_id'] = $order_id;
        $changedata['change_desc'] = '订单支付';
        $changedata['change_type'] = 2;
        $changedata['goods_money'] = $order['order_amount'] * -1;
        $changedata['goods_money_outlay_total'] = $order['order_amount'];
        $res = $WalletModel->change($changedata, $this->userInfo['user_id'],false);
        if ($res !== true) {
            Db::rollback();//回滚事务
            return $this->error('更新帐户信息失败.');
        }
        //完成支付
        $upArr['order_id'] = $order_id;
        $upArr['pay_code'] = 'goodsMoney';
        $upArr['pay_id'] = 99;
        $upArr['pay_name'] = '货款支付';
        $upArr['money_paid'] = $order['order_amount'];
        $res = $OrderModel->updatePay($upArr, '货款支付成功.',true);
        if ($res !== true) {
            Db::rollback();// 回滚事务
            return $this->error($res);
        }
        return $this->success();
    }
    /*------------------------------------------------------ */
    //-- 代理余额支付
    /*------------------------------------------------------ */
    private function channelBalancePay($payment,$order_id){
        $OrderModel = new \app\channel\model\OrderModel();
        $order = $OrderModel->where("order_id", $order_id)->find();
        if ($order['pay_status'] == 1 || $order['pay_status'] == 2) {
            return $this->error('此订单，已完成.');
        }
        if ($order['order_status'] == 2) {
            return $this->error('此订单，已取消不能执行支付.');
        }
        if ($order['user_id'] != $this->userInfo['user_id']){
            return $this->error('此订单你无权操作.');
        }

        $WalletModel = new \app\channel\model\WalletModel();
        $account = $WalletModel->getWalletInfo($this->userInfo['user_id'],false);//获取代理会员帐户信息
        if ($order['order_amount'] > $account['balance_money']) {
            return $this->error('余额不足，请使用其它支付方式.');
        }
        Db::startTrans();//启动事务
        $changedata['by_id'] = $order_id;
        $changedata['change_desc'] = '订单支付';
        $changedata['change_type'] = 2;
        $changedata['balance_money'] = $order['order_amount'] * -1;
        $res = $WalletModel->change($changedata, $this->userInfo['user_id'],false);
        if ($res !== true) {
            Db::rollback();//回滚事务
            return $this->error('更新帐户信息失败.');
        }
        //余额完成支付
        $upArr['order_id'] = $order_id;
        $upArr['pay_code'] = $payment['pay_code'];
        $upArr['pay_id'] = $payment['pay_id'];
        $upArr['pay_name'] = $payment['pay_name'];
        $upArr['money_paid'] = $order['order_amount'];
        $res = $OrderModel->updatePay($upArr, '余额支付成功.',true);
        if ($res !== true) {
            Db::rollback();// 回滚事务
            return $this->error($res);
        }
        return $this->success();
    }

    /*------------------------------------------------------ */
    //-- 公用余额支付，一般商城使用
    /*------------------------------------------------------ */
    private function publicBalancePay($payment,$order_id){
        $OrderModel = new \app\shop\model\OrderModel();
        $order = $OrderModel->where("order_id", $order_id)->find();
        if ($order['pay_status'] == 1 || $order['pay_status'] == 2) {
            return $this->error('此订单，已完成.');
        }
        if ($order['order_status'] == 2) {
            return $this->error('此订单，已取消不能执行支付.');
        }
        if ($order['user_id'] != $this->userInfo['user_id']){
            return $this->error('此订单你无权操作.');
        }

        if ($order['order_amount'] > $this->userInfo['account']['balance_money']) {
            return $this->error('余额不足，请使用其它支付方式.');
        }
        Db::startTrans();//启动事务
        $upData['money_paid'] = $order['order_amount'];
        $upData['pay_time'] = time();
        $changedata['change_desc'] = '余额支付';
        $changedata['change_type'] = 3;
        $changedata['by_id'] = $order_id;
        $changedata['balance_money'] = $order['order_amount'] * -1;
        $res = (new \app\member\model\AccountModel)->change($changedata, $order['user_id'], false);
        if ($res !== true) {
            Db::rollback();// 回滚事务
            return $this->error('支付失败，扣减余额失败.');
        }
        $balance_money = (new \app\member\model\AccountModel)->where('user_id',$order['user_id'])->value('balance_money');
        if ($balance_money < 0){
            Db::rollback();// 回滚事务
            return $this->error('支付失败，扣减余额失败.');
        }
        //余额完成支付
        $upArr['order_id'] = $order_id;
        $upArr['pay_code'] = $payment['pay_code'];
        $upArr['pay_id'] = $payment['pay_id'];
        $upArr['pay_name'] = $payment['pay_name'];
        $upArr['money_paid'] = $order['order_amount'];
        $res = $OrderModel->updatePay($upArr, '余额支付成功.',true);
        if ($res !== true) {
            Db::rollback();// 回滚事务
            return $this->error($res);
        }
        return $this->success();
    }


}
