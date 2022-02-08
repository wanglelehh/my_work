<?php

namespace app\channel\controller\api;
use app\channel\ApiController;
use think\Db;

use app\channel\model\OrderModel;
use app\channel\model\ProxyUsersModel;
use app\channel\model\StockModel;
use app\channel\model\RewardLogModel;
use app\mainadmin\model\PaymentModel;

use app\shop\model\ShippingModel;
/*------------------------------------------------------ */
//-- 代理订单相关API
/*------------------------------------------------------ */

class Order extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new OrderModel();
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

        $data['order_id'] = $orderInfo['order_id'];
        $data['order_amount'] = $orderInfo['order_amount'];
        $data['order_sn'] = $orderInfo['order_sn'];
        $data['is_stock'] = $orderInfo['is_stock'];
        $data['lackGoods'] = [];
        if ($data['is_stock'] == 0){//未扣库存，尝试执行扣库存
            Db::startTrans();//开启事务
            $StockModel = new StockModel();
            $data['lackGoods'] = $StockModel->checkLackGoods($orderInfo['goodsList'],$orderInfo['supply_uid'],$orderInfo['purchase_type']);
            if (empty($data['lackGoods']) == false){
                $data['lack_tip'] = '上级库存不足不能支付，请联系上级补充库存.';
                if ($orderInfo['supply_uid']>0){
                    if ($orderInfo['purchase_type'] == 1){
                        $title = '云仓商品库存不足';
                    }else{
                        $title = '现货商品库存不足';
                    }
                    $MessageUserModel = new \app\mainadmin\model\MessageUserModel();
                    $content = '下级代理下单，您的库存不足，请及时补充库存.';
                    $msWhere = [];
                    $msWhere[] = ['user_id','=',$orderInfo['supply_uid']];
                    $msWhere[] = ['type','=',3];
                    $msWhere[] = ['ext_id','=', $orderInfo['order_id']];
                    $count =  $MessageUserModel->where($msWhere)->count();
                    if ($count < 1){
                        $MessageUserModel->sendMessage($orderInfo['supply_uid'], 3, $orderInfo['order_id'],$title,$content);
                    }
                }
            }else{
                //执行扣库存
                $res = $StockModel->evalSupplyStock($orderInfo);
                if ($res == false){
                   Db::rollback();//回滚事务
                }else{
                    $upData['order_id'] = $order_id;
                    $upData['is_stock'] = 1;
                    $res = $this->Model->upInfo($upData);
                    if ($res == false){
                        Db::rollback();//回滚事务
                        $data['lack_tip'] = '扣库存失败，暂不能支付.';
                    }else{
                        Db::commit();//提交事务
                        $this->Model->cleanMemcache($order_id);
                        $data['is_stock'] = 1;
                    }
                }
            }
        }
        if ($data['is_stock'] == 1){//已扣库存
            $settings = settings();
            $platform = input('platform','','trim');
            $payList = (new PaymentModel)->getRows($platform,'',true);
            $channel_order_payment = explode(',',$settings['channel_order_payment']);
            $data['payList'] = [];
            $isOffLinePay = 0;
            foreach ($payList as $pay){
                if (in_array($pay['pay_code'],$channel_order_payment) == false){
                    continue;
                }
                $_pay['money'] = -1;
                if ($pay['pay_code'] == 'offline'){
                    $isOffLinePay = 1;
                }elseif ($pay['pay_code'] == 'balance'){
                    $_pay['money'] = $this->userInfo['proxy_account']['balance_money'];
                }
                $_pay['pay_id'] = $pay['pay_id'];
                $_pay['pay_code'] = $pay['pay_code'];
                $_pay['pay_name'] = $pay['pay_name'];
                $data['payList'][] = $_pay;
            }
            if (in_array('goodsMoney',$channel_order_payment) == true){//货款支付额外处理
                $_pay['money'] = $this->userInfo['proxy_account']['goods_money'];
                $_pay['pay_id'] = 99;
                $_pay['pay_code'] = 'goodsMoney';
                $_pay['pay_name'] = '货款支付';
                $data['payList'][] = $_pay;
            }
            if ($isOffLinePay == 1){
                $data['offline']['is_sys'] = 0;//是否平台收款
                if ($orderInfo['supply_uid'] == 0 || $settings['channel_receive_payment_set'] == 0){//平台供货/平台统一收款
                    $data['offline']['is_sys'] = 1;
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
                        $data['offline']['is_usd'] = 1;
                        $data['offline']['bank_name'] = $settings['channel_bank_name'];
                        $data['offline']['bank_subbranch'] = $settings['channel_bank_subbranch'];
                        $data['offline']['bank_user_name'] = $settings['channel_bank_user_name'];
                        $data['offline']['bank_card_number'] = $settings['channel_bank_card_number'];
                    }
                }else{
                    $userInfo = (new ProxyUsersModel)->info($orderInfo['supply_uid']);

                    $data['offline']['is_usd'] = 0;
                    $data['offline']['weixin_usd'] = $userInfo['weixin_usd'];
                    if ($userInfo['weixin_usd'] == 1){
                        $data['offline']['is_usd'] = 1;
                        $data['offline']['weixin_qrcode'] = $userInfo['weixin_qrcode'];
                        $data['offline']['weixin_name'] = $userInfo['weixin_name'];
                        $data['offline']['weixin_account'] = $userInfo['weixin_account'];
                    }
                    $data['offline']['alipay_usd'] = $userInfo['alipay_usd'];
                    if ($userInfo['alipay_usd'] == 1){
                        $data['offline']['is_usd'] = 1;
                        $data['offline']['alipay_qrcode'] = $userInfo['alipay_qrcode'];
                        $data['offline']['alipay_name'] = $userInfo['alipay_name'];
                        $data['offline']['alipay_account'] = $userInfo['alipay_account'];
                    }
                    $data['offline']['bank_usd'] = $userInfo['bank_usd'];
                    if ($userInfo['bank_usd'] == 1){
                        $data['offline']['is_usd'] = 1;
                        $data['offline']['bank_name'] = $userInfo['bank_name'];
                        $data['offline']['bank_subbranch'] = $userInfo['bank_subbranch'];
                        $data['offline']['bank_card_number'] = $userInfo['bank_card_number'];
                        $data['offline']['bank_user_name'] = $userInfo['bank_user_name'];
                    }
                }
            }

        }
        return $this->success($data);
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
        $upData['pay_id'] = $payList['offline']['pay_id'];
        $upData['pay_code'] = $payList['offline']['pay_code'];
        $upData['pay_name'] = $payList['offline']['pay_name'];
        $upData['offlineimg'] = join(',',$offlineimg);
        $upData['order_id'] = $order_id;
        $upData['pay_status'] = config('config.PS_WAITCHACK');
        $upData['pay_time'] = time();

        if (settings('channel_receive_payment_set') == 1) {//转帐到上级收款，收款人设置供货代理
            $upData['payee_uid'] = $orderInfo['supply_uid'];
        }

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
    /*------------------------------------------------------ */
    //-- 获取订单详细
    /*------------------------------------------------------ */
    public function info(){
        $order_id = input('order_id',0,'intval');
        if ($order_id < 1){
            return $this->error('订单ID传值错误.');
        }
        $orderInfo = $this->Model->info($order_id,true,$this->userInfo['user_id']);
        if (empty($orderInfo)){
            return $this->error('没有找到相关订单信息.');
        }
        if ($orderInfo['user_id'] != $this->userInfo['user_id'] && $orderInfo['supply_uid'] != $this->userInfo['user_id']){
            return $this->error('订单不存在.');
        }
        return $this->success($orderInfo);
    }
    /*------------------------------------------------------ */
    //-- 获取订单数量统计
    /*------------------------------------------------------ */
    public function getNum(){
        $data = $this->Model->getNum();
        //直购订单查询
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $where[] = ['order_status','IN',[0,1]];
        $where[] = ['shipping_status','IN',[0,1]];
        $data[4] = (new \app\shop\model\OrderModel)->where($where)->count();
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取下级订单数量统计
    /*------------------------------------------------------ */
    public function getSubNum(){
        return $this->success($this->Model->getSubNum());
    }
    /*------------------------------------------------------ */
    //-- 获订单列表
    /*------------------------------------------------------ */
    public function getList()
    {
        $state = input('state',0,'intval');
        $purchase_type = input('purchase_type',0,'intval');
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $where[] = ['purchase_type','=',$purchase_type];
        $where[] = ['is_delete','=',0];
        if ($state == 1) {//待确定
            $where[] = ['order_status', '=', 0];
            $where[] = ['is_pay', '=', 0];
        }elseif ($state == 2){//待支付
            $where[] = ['order_status','in',[0,1]];
            $where[] = ['pay_status','=',0];
        }elseif ($state == 3){//待审核
            $where[] = ['order_status','=',0];
            $where[] = ['pay_status','=',10];
        }elseif ($state == 4){//待发货
            $where[] = ['order_status','=',1];
            $where[] = ['shipping_status','=',0];
        }elseif ($state == 5){//待签收
            $where[] = ['order_status','=',1];
            $where[] = ['shipping_status','=',1];
        }elseif ($state == 9){//已入仓、已完成
            $where[] = ['order_status','=',1];
            $where[] = ['shipping_status','=',2];
        }elseif ($state == 10){//已失效
            $where[] = ['order_status','=',2];
        }
        $data = $this->getPageList($this->Model, $where,'order_id',6);
        foreach ($data['list'] as $key=>$order){
            $orderInfo = $this->Model->info($order['order_id']);
            $orderInfo['add_time'] = dateTpl($orderInfo['add_time'],'Y-m-d H:i:s',true);
            $data['list'][$key] = $orderInfo;
        }
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获下级订单列表
    /*------------------------------------------------------ */
    public function getSubList()
    {
        $state = input('state',0,'intval');
        $purchase_type = input('purchase_type',0,'intval');
        $where[] = ['supply_uid','=',$this->userInfo['user_id']];
        $where[] = ['purchase_type','=',$purchase_type];
        $where[] = ['is_delete','=',0];
        if ($state == 2){//待支付
            $where[] = ['order_status','=',0];
            $where[] = ['pay_status','=',0];
        }elseif ($state == 3){//待审核
            $where[] = ['order_status','=',0];
            $where[] = ['payee_uid','=',$this->userInfo['user_id']];
            $where[] = ['pay_status','=',10];
        }elseif ($state == 4){//待发货
            $where[] = ['order_status','=',1];
            $where[] = ['shipping_status','=',0];
        }elseif ($state == 5){//待签收
            $where[] = ['order_status','=',1];
            $where[] = ['shipping_status','=',1];
        }elseif ($state == 9){//已出仓、已完成
            $where[] = ['order_status','=',1];
            $where[] = ['shipping_status','=',2];
        }elseif ($state == 20){//已拒绝，线下付款审核拒绝
            $where[] = ['pay_status','=',11];
        }else{
            $where[] = ['order_status','<>',2];
        }
        $data = $this->getPageList($this->Model, $where,'order_id',6);
        foreach ($data['list'] as $key=>$order){
            $orderInfo = $this->Model->info($order['order_id'],true,$this->userInfo['user_id']);
            $orderInfo['add_time'] = dateTpl($orderInfo['add_time'],'Y-m-d H:i:s',true);
            $data['list'][$key] = $orderInfo;
        }
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 线下支付收款确认
    /*------------------------------------------------------ */
    public function cfmCodPay()
    {
        $order_id = input('order_id', 0, 'intval');
        $operate = input('operate', 0, 'intval');
        if ($operate < 1){
            return $this->error('请选择需要执行的操作.');
        }
        $orderInfo = $this->Model->info($order_id);
        $res = $this->Model->cfmCodPay($orderInfo,$operate,'',$this->userInfo['user_id']);
        if ($res !== true) return $this->error($res);
        return $this->success('收款确认成功.');
    }

    /*------------------------------------------------------ */
    //-- 取消订单
    /*------------------------------------------------------ */
    public function cancel()
    {
        $order_id = input('order_id',0,'intval');
        if ($order_id < 1){
            return $this->error('订单ID传值错误.');
        }
        $orderInfo = $this->Model->info($order_id);
        $res = $this->Model->cancel($orderInfo,$this->userInfo['user_id']);
        if ($res !== true){
            return $this->error($res);
        }
        return $this->success();
    }
    /*------------------------------------------------------ */
    //-- 删除订单
    /*------------------------------------------------------ */
    public function delete()
    {
        $order_id = input('order_id',0,'intval');
        if ($order_id < 1){
            return $this->error('订单ID传值错误.');
        }
        $orderInfo = $this->Model->info($order_id);
        if (empty($orderInfo)){
            return $this->error('没有找到相关订单信息.');
        }
        if ($orderInfo['isDelete'] == 0){
            return $this->error('当前订单不能操作删除.');
        }
        if ($orderInfo['user_id'] != $this->userInfo['user_id']){
            return $this->error('你无权进行操作.');
        }
        $upData['order_id'] = $order_id;
        $upData['is_delete'] = 1;
        $res = $this->Model->upInfo($upData);
        if ($res !== true) return $this->error('操作失败，请重试.');
        $this->Model->_log($orderInfo, '代理删除订单');
        return $this->success();
    }
    /*------------------------------------------------------ */
    //-- 获取快递列表
    /*------------------------------------------------------ */
    public function getShippingList()
    {
        $shippingRows = (new ShippingModel)->getRows();
        $data = [];
        foreach ($shippingRows as $row){
            if ($row['is_zt'] == 1 || $row['status'] == 0 || $row['is_front'] == 0){
                continue;
            }
            $shipping['value'] = $row['shipping_id'];
            $shipping['label'] = $row['shipping_name'];
            $data[] = $shipping;
        }
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 订单发货
    /*------------------------------------------------------ */
    public function postShipping()
    {
        $order_id = input('order_id',0,'intval');
        if ($order_id < 1){
            return $this->error('订单ID传值错误.');
        }
        $orderInfo = $this->Model->info($order_id);
        if (empty($orderInfo)){
            return $this->error('没有找到相关订单信息.');
        }
        if ($orderInfo['isShipping'] == 0){
            return $this->error('当前订单不能操作发货.');
        }
        if ($orderInfo['supply_uid'] != $this->userInfo['user_id']){
            return $this->error('你无权进行操作.');
        }
        $shipping_id = input('shipping_id', 0, 'intval');
        $invoice_no = input('invoice_no', '', 'trim');
        if ($shipping_id < 1){
            return $this->error('请选择快递公司.');
        }
        if (empty($invoice_no)){
            return $this->error('请输入快递单号.');
        }
        $ShippingModel = new ShippingModel();
        $shipping = $ShippingModel->getRows();
        $upData['order_id'] = $order_id;
        $upData['shipping_status'] = 1;
        $upData['shipping_time'] = time();
        $upData['shipping_id'] = $shipping_id;
        $upData['shipping_name'] = $shipping[$shipping_id]['shipping_name'];
        $upData['invoice_no'] = $invoice_no;
        $res = $this->Model->upInfo($upData);
        if ($res !== true) return $this->error('操作失败，请重试.');
        $orderInfo['shipping_status'] = $upData['shipping_status'];
        $this->Model->_log($orderInfo, '代理操作发货');
        return $this->success();
    }
    /*------------------------------------------------------ */
    //-- 设置为已签收
    /*------------------------------------------------------ */
    public function sign()
    {
        $order_id = input('order_id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id,false,$this->userInfo['user_id']);
        $config = config('config.');
        if ($orderInfo['isSign'] == 0) return $this->error('订单当前状态不能操作签收.');
        $data['order_id'] = $order_id;
        $data['shipping_status'] = $config['SS_SIGN'];
        $data['sign_time'] = time();
        Db::startTrans();//开启事务
        $res = $this->Model->upInfo($data);
        if ($res !== true){
            Db::rollback();//回滚事务
            return $this->error($res);
        }
        $orderInfo['shipping_status'] = $data['shipping_status'];
        $res = $this->Model->_log($orderInfo, '前台用户操作设为签收');
        if ($res < 1) {
            Db::rollback();// 回滚事务
            return $this->error('订单日志写入失败，请重试.');
        }
        $res = (new StockModel)->stockByBuy($orderInfo,'INC','订单签收增加库存');
        if ($res !== true) {
            Db::rollback();// 回滚事务
            return $this->error($res);
        }
        $res = (new RewardLogModel)->runRewardToUserByOrder($orderInfo['order_id']);//执行相关奖励到帐
        if ($res !== true) {
            Db::rollback();// 回滚事务
            return $this->error('处理失败.');
        }
        Db::commit();//提交事务
        return $this->success('订单签收成功.');
    }
}

