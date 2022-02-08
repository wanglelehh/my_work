<?php
namespace app\shop\controller\api;
use app\ApiController;
use think\facade\Cache;
use think\Db;

use app\shop\model\OrderModel;
use app\mainadmin\model\PaymentModel;
/*------------------------------------------------------ */
//-- 订单相关API
/*------------------------------------------------------ */
class Order extends ApiController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->checkLogin();//验证登陆
        $this->Model = new OrderModel();
    }
	/*------------------------------------------------------ */
	//-- 获取列表
	/*------------------------------------------------------ */
 	public function getList(){
        $where[] = ['order_type','in',[0,1]];
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $where[] = ['is_split','in',[0,1]];
        $where[] = ['is_del','=',0];
        $type = input('type','','trim');
        switch ($type){
            case 'waitPay':
                $where[] = ['is_pay', '>', 0];
                $where[] = ['order_status', '=', '0'];
                $where[] = ['pay_status', '=', '0'];
                break;
            case 'waitShipping':
                $where[] = ['order_status', '=', '1'];
                $where[] = ['shipping_status', '=', '0'];
                $where['and'][] = "(pay_status = 1 OR is_pay = 0)";
                break;
            case 'waitSign':
                $where[] = ['order_status', '=', '1'];
                $where[] = ['shipping_status', '=', '1'];
                break;
            case 'sign':
                $where[] = ['order_status', '=', '1'];
                $where[] = ['shipping_status', '=', '2'];
                break;
            default:
                break;
        }
        $data = $this->getPageList($this->Model, $where,'order_id',5);
        $config = config('config.');
        foreach ($data['list'] as $key=>$order){
            $order = $this->Model->info($order['order_id'],$config);
            $data['list'][$key] = $order;
        }
        return $this->success($data);
	}
    /*------------------------------------------------------ */
    //-- 获取订单详细
    /*------------------------------------------------------ */
    public function getOrderStats(){
        $data['orderStats'] = $this->Model->userOrderStats($this->userInfo['user_id']);
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取订单详细
    /*------------------------------------------------------ */
    public function info(){
        $order_id = input('order_id',0,'intval');
        if ($order_id < 1) return $this->error('传参错误.');
        $orderInfo = $this->Model->info($order_id);
        if ($orderInfo['user_id'] != $this->userInfo['user_id']) return $this->error('无权访问.');

        return $this->success($orderInfo);
    }
    /*------------------------------------------------------ */
    //-- 获取待支付订单
    /*------------------------------------------------------ */
    public function getPayOrder()
    {
        $order_id = input('order_id',0,'intval');
        if ($order_id < 1){
            return $this->error('订单ID传值错误.');
        }
        $orderInfo = $this->Model->info($order_id);
        if ($orderInfo['is_pay'] == 0){
            return $this->error('当前订单不能进行支付操作.');
        }
        $data['order_id'] = $orderInfo['order_id'];
        $data['order_amount'] = $orderInfo['order_amount'];
        $data['order_sn'] = $orderInfo['order_sn'];
        $data['is_stock'] = 1;
        $settings = settings();
        $platform = input('platform','','trim');
        $payList = (new PaymentModel)->getRows($platform);
        $data['payList'] = [];
        $isOffLinePay = 0;

        foreach ($payList as $pay){
            if ($pay['is_buy_pay'] == 0){
                continue;
            }
            $_pay['money'] = -1;
            if ($pay['pay_code'] == 'offline'){
                $isOffLinePay = 1;
            }elseif ($pay['pay_code'] == 'balance'){
                $_pay['money'] = $this->userInfo['account']['balance_money'];
            }
            $_pay['pay_id'] = $pay['pay_id'];
            $_pay['pay_code'] = $pay['pay_code'];
            $_pay['pay_name'] = $pay['pay_name'];
            $data['payList'][] = $_pay;
        }

        if ($isOffLinePay == 1) {
            $data['offline']['is_sys'] = 1;
            $data['offline']['weixin_usd'] = empty($settings['receive_weixin_qrcode']) ? 0 : 1;
            $data['offline']['alipay_usd'] = empty($settings['receive_alipay_qrcode']) ? 0 : 1;
            $data['offline']['bank_usd'] = empty($settings['receive_bank_name']) ? 0 : 1;
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
    //-- 执行订单操作
    /*------------------------------------------------------ */
    public function action(){
        $order_id = input('order_id',0,'intval');
        $lookKey = 'lockOrderIng_'.$order_id;
        $res = redisLook($lookKey);
        if ($res == false){
           return $this->error('请求失败');
        }
        $orderInfo = $this->Model->info($order_id);
        $type = input('type','','trim');
        $config = config('config.');
        $upData['order_id'] = $order_id;
        switch ($type){
            case 'cancel'://取消
                if ($orderInfo['isCancel'] == 0){
                    return $this->error('请求失败');
                }
                $upData['order_status'] = $config['OS_CANCELED'];
                $upData['cancel_time'] = time();
                $_log = '用户取消订单';
                break;
            case 'sign'://签收
                if ($orderInfo['isSign'] == 0){
                    return $this->error('请求失败');
                }
                $upData['shipping_status'] = $config['SS_SIGN'];
                $upData['sign_time'] = time();
                $_log = '用户签收订单';
                break;
            case 'del'://删除
                if ($orderInfo['isDelete'] == 0){
                    return $this->error('请求失败');
                }
                $upData['is_del'] = 1;
                $_log = '用户删除订单';
                break;
            default:
                return $this->error('没有相关操作.');
                break;
        }
        $res = $this->Model->upInfo($upData);
        if ($res !== true) return $this->error($res);
        $orderInfo = $this->Model->info($order_id);
        $this->Model->_log($orderInfo,$_log);
        redisLook($lookKey,-1);
        return $this->success();

    }
    /*------------------------------------------------------ */
    //-- 提交支付凭证
    /*------------------------------------------------------ */
    public function postOffLinePay(){
        $order_id = input('order_id',0,'intval');
        if ($order_id < 1){
            return $this->error('﻿订单ID传值错误.');
        }
        $fileList = input('fileList','','trim');
        if (empty($fileList)){
            return $this->error('请上传付款凭证.');
        }
        $orderInfo = $this->Model->info($order_id);
        if ($orderInfo['isPay'] == 0){
            return $this->error('当前订单不能进行支付操作.');
        }

        if ($orderInfo['is_stock'] == 0){
            return $this->error('未进行库存处理订单，不能完成操作.');
        }
        $file_path = config('config._upload_').'offline_pay/'.date('m');
        $offlineimg = [];
        $fileList = explode(',',$fileList);
        foreach ($fileList as $file){
            $offlineimg[] = copyFile($file,$file_path);
        }
        $payList = (new PaymentModel)->getRows(true,'pay_code');
        if (empty($payList['offline'])){
            return $this->error('支付方式错误，请重试.');
        }

        Db::startTrans();//开启事务
        $upData['is_pay'] = 2;
        $upData['pay_id'] = $payList['offline']['pay_id'];
        $upData['pay_code'] = $payList['offline']['pay_code'];
        $upData['pay_name'] = $payList['offline']['pay_name'];
        $upData['offlineimg'] = join(',',$offlineimg);
        $upData['order_id'] = $order_id;
        $upData['pay_status'] = config('config.PS_WAITCHACK');
        $upData['pay_time'] = time();
        $res = $this->Model->upInfo($upData);
        if ($res == false){
            foreach ($offlineimg as $img){
                @unlink('.'.$img);
            }
            Db::rollback();// 回滚事务
            return $this->error('更新订单失败.');
        }
        $res = $this->Model->_log($orderInfo,'提交支付凭证.');
        if ($res < 1) {
            Db::rollback();// 回滚事务
            return '订单日志写入失败，请重试.';
        }
        Db::commit();//提交事务
        foreach ($fileList as $file){
            @unlink('.'.$file);
        }
        return $this->success();
    }


}
