<?php

namespace app\luckdraw\controller\api;

use app\ApiController;
use app\member\model\UserAddressModel;
use app\luckdraw\model\LuckdrawLogModel;

use app\shop\model\OrderModel;
use app\shop\model\OrderGoodsModel;
use app\shop\model\GoodsModel;
use app\shop\model\CartModel;

use think\Db;
/*------------------------------------------------------ */
//-- 奖品兑换
/*------------------------------------------------------ */
class Receive extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->checkLogin();//验证登陆
        $this->Model = new LuckdrawLogModel();
    }
    /*------------------------------------------------------ */
    //-- 获取奖品信息
    /*------------------------------------------------------ */
    public function getInfo(){
        $log_id = input('log_id',0,'intval');
        if ($log_id < 1){
            return $this->error('传参错误.');
        }
        $data = $this->Model->where('log_id',$log_id)->find();
        if (empty($data)){
            return $this->error('没有找到相关记录.');
        }
        if ($data['user_id'] != $this->userInfo['user_id']){
            return $this->error('传参错误，你无权操作.');
        }
        if ($data['prize_type'] != 'entity'){
            return $this->error('奖品非实体，无法兑换.');
        }
        if ($data['status'] == 1){
            return $this->error('奖品已领取.');
        }
        $data = $data->toArray();
        $GoodsModel = new GoodsModel();
        $data['goods'] = $GoodsModel->info($data['relation_val']);
        return $this->success($data);
    }
    /**计算运费
     * @param int $address_id 收货地址
     * @param bool $is_return 是否直接返回数据
     * @return mixed|void
     */
    public function evalShippingFee($log_id,$address_id = 0, $is_return = false)
    {
        if ($log_id < 1){
            $log_id = input('log_id',0,'intval');
        }
        if ($log_id < 1){
            return $this->error('传参错误.');
        }
        if ($address_id < 0) {
            $address_id = input('address_id', '0', 'intval');
        }
        if ($address_id < 1) {
            $data['shipping_fee'] = sprintf("%.2f", 0);
            if ($is_return == true) return $data;
            return $this->success($data);
        }
        $addressList = (new UserAddressModel)->getRows();
        $address = $addressList[$address_id];

        $cartList['buyGoodsNum'] = 1;
        $prize = $this->Model->where('log_id',$log_id)->find();
        $goods = (new GoodsModel)->info($prize['relation_val']);
        $goods['goods_number'] = 1;
        $cartList['goodsList'][] = $goods;

        $CartModel = new CartModel();
        $shippingFee = $CartModel->evalShippingFee($address,$cartList);
        $shippingFee = reset($shippingFee);//现在只返回默认快递
        $shippingFee['shipping_fee'] = sprintf("%.2f", $shippingFee['shipping_fee'] * 1);
        if ($is_return == true) return $shippingFee;
        $data['shippingFee'] = $shippingFee;
        return $this->success($data);
    }

    /*------------------------------------------------------ */
    //-- 执行下单
    /*------------------------------------------------------ */
    public function addOrder()
    {
        $lookKey = 'receive_addorder_'.$this->userInfo['user_id'];
        $res = redisLook($lookKey);
        if ($res == false) return $this->error('请求频繁，请销后再试.');

        $log_id = input('log_id',0,'intval');
        if ($log_id < 1){
            return $this->error('传参错误.');
        }
        $prize = $this->Model->where('log_id',$log_id)->find();
        if (empty($prize)){
            return $this->error('没有找到相关记录.');
        }
        if ($prize['user_id'] != $this->userInfo['user_id']){
            return $this->error('传参错误，你无权操作.');
        }
        if ($prize['prize_type'] != 'entity'){
            return $this->error('奖品非实体，无法兑换.');
        }
        if ($prize['status'] == 1){
            return $this->error('奖品已领取.');
        }

        $address_id = input('address_id', 0, 'intval');
        if ($address_id < 0) return $this->error('请设置收货地址后，再操作.');
        $UserAddressModel = new UserAddressModel();
        $addressList = $UserAddressModel->getRows();
        $address = $addressList[$address_id];
        if (empty($address)) return $this->error('相关收货地址不存在.');


        $GoodsModel = new GoodsModel();
        $cartList['buyGoodsNum'] = 1;
        $goods = $GoodsModel->info($prize['relation_val']);
        $goods['goods_number'] = 1;
        $cartList['goodsList'][] = $goods;

        $inArr['give_integral'] = 0;
        $inArr['settle_price'] =  0;
        $inArr['settle_goods_price'] =  0;

        $inArr['use_bonus'] = 0;
        $time = time();
        $inArr['order_status'] = 0;
        $inArr['pay_status'] = 0;
        $inArr['shipping_status'] = 0;
        $_log = '生成订单';

        //运费处理
        $shippingFee = (new CartModel)->evalShippingFee($address,$cartList);
        $shippingFee = reset($shippingFee);//现在只返回默认快递
        $shippingFee['shipping_fee'] = sprintf("%.2f", $shippingFee['shipping_fee'] * 1);

        $inArr['shipping_fee'] = $shippingFee['shipping_fee'] * 1;
        $inArr['shipping_fee_detail'] = json_encode($shippingFee['supplyerShippingFee']);


        $inArr['settle_price'] = $inArr['settle_goods_price'] + $inArr['shipping_fee'];
        $inArr['order_amount'] = $inArr['shipping_fee'];
        $inArr['order_type'] = 4;//订单类型，4.奖品兑换
        $payment['is_pay'] = 0;
        if ($inArr['order_amount'] > 0){
            $payment['is_pay'] = 1;
        }

        $inArr['buyer_message'] = input('buy_msg', '', 'trim');
        $inArr['consignee'] = $address['consignee'];
        $inArr['address'] = $address['address'];
        $inArr['merger_name'] = $address['merger_name'];
        $inArr['province'] = $address['province'];
        $inArr['city'] = $address['city'];
        $inArr['district'] = $address['district'];
        $inArr['mobile'] = $address['mobile'];

        $inArr['add_time'] = $time;
        $inArr['user_id'] = $this->userInfo['user_id'];
        $inArr['dividend_role_id'] = $this->userInfo['role_id'];

        $inArr['buy_goods_sn'] = $goods['goods_sn'];
        $inArr['buy_goods_id'] = $goods['goods_id'];
        $inArr['ipadderss'] = request()->ip();
        $inArr['is_pay'] = $payment['is_pay'];//是否需要支付,1线上支付，0，不需要支付，
        $inArr['is_success'] = 1;//普通订单默认有效，如果拼团默认为0，须拼团成功才会为1
        //执行商品库存和销量处理，后台设置下单减库存或余额支付时执行
        $shop_reduce_stock = settings('shop_reduce_stock');
        $inArr['is_stock'] = $shop_reduce_stock == 0 ? 1 : 0;
        Db::startTrans();//启动事务
        $OrderModel = new OrderModel();
        $inArr['order_sn'] = $OrderModel->getOrderSn();
        $res = $OrderModel->save($inArr);
        if ($res < 1) {
            Db::rollback();// 回滚事务
            return $this->error('未知原因，订单写入失败.');
        }
        $order_id = $OrderModel->order_id;

        $inArr['order_id'] = $order_id;
        $res = $OrderModel->_log($inArr,$_log);
        if ($res < 1) {
            Db::rollback();// 回滚事务
            return $this->error('未知原因，订单日志写入失败.');
        }

        //执行扣库存
        if ($inArr['is_stock'] == 1) {
            $res = $GoodsModel->evalGoodsStore($cartList['goodsList']);
            if ($res != true) {
                Db::rollback();//回滚
                return $this->error('扣库存失败.');
            }
        }
        //end
        $goods = array(
            'order_id' => $order_id,
            'brand_id' => $goods['brand_id'],
            'cid' => $goods['cid'],
            'supplyer_id' => $goods['supplyer_id'],
            'goods_id' => $goods['goods_id'],
            'goods_name'=>$goods['goods_name'],
            'pic' => $goods['﻿goods_thumb'],
            'goods_sn' => $goods['goods_sn'],
            'goods_number' => 1,
            'market_price' => $goods['market_price'],
            'shop_price' => $goods['shop_price'],
            'sale_price' => 0,
            'settle_price' => $goods['settle_price'],
            'goods_weight' => $goods['goods_weight'],
            'discount' => $goods['sale_price'],
            'add_time'=>$time,
            'user_id' => $this->userInfo['user_id'],
            'is_dividend' => 0,
            );
        $res = (new OrderGoodsModel)->save($goods);
        if ($res < 1) {
            Db::rollback();// 回滚事务
            return $this->error('未知原因，订单商品写入失败.');
        }

        $res = $this->Model->where('log_id',$log_id)->update(['status'=>1]);
        if ($res < 1) {
            Db::rollback();// 回滚事务
            return $this->error('发生错误，请重试.');
        }
        Db::commit();// 提交事务
        $data['order_id'] = $order_id;
        $data['is_pay'] = $payment['is_pay'];
        $data['msg'] =  '下单成功.';
        return $this->success($data);
    }
}
