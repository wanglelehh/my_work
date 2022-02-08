<?php

namespace app\channel\controller\api;

use app\channel\ApiController;


use app\channel\model\CartModel;
use app\channel\model\OrderModel;

/*------------------------------------------------------ */
//-- 购物相关API
/*------------------------------------------------------ */

class Flow extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new CartModel();
        if ($this->userInfo['role']['role_type'] == 0){
            return $this->error(['您无权限操作.',-1]);
        }
    }
    /*------------------------------------------------------ */
    //-- 添加购物车
    /*------------------------------------------------------ */
    public function addCart()
    {
        $goods_id = input('goods_id', 0, 'intval');
        $buyNumber = input('buyNumber', 1, 'intval');
        $sku_id = input('sku_id', 1, 'intval');
        $purchaseType = input('purchaseType', 0, 'intval');
        $buyUnit = input('buyUnit', 0, 'intval');
        if ($purchaseType < 1){
            return $this->error('进货类型错误.');
        }
        if ($buyNumber < 1) $buyNumber = 1;
        if ($goods_id < 1) return $this->error('传值失败，请重新尝试提交！');
        $res = $this->Model->addToCart($goods_id, $buyNumber, $buyUnit, $sku_id,$purchaseType);
        if (is_numeric($res) == false) {
            return $this->error($res);
        }
        $data['rec_id'] = $res;
        $data['msg'] = '添加成功.';
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 提货添加购物车
    /*------------------------------------------------------ */
    public function addCartByPickUp()
    {
        $goods = input('goods','','trim');
        if (empty($goods)){
            return $this->error('请选择需要提货的商品.');
        }
        $res = $this->Model->addCartByPickUp($goods);
        if ($res !== true) {
            return $this->error($res);
        }
        return $this->success();
    }
    /*------------------------------------------------------ */
    //-- 获取购物车信息,返回购物车中商品条数
    /*------------------------------------------------------ */
    public function getCartNum()
    {
        $purchaseType = input('purchaseType', 0, 'intval');
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $where[] = ['user_role_id','=',$this->userInfo['role_id']];
        $where[] = ['purchase_type','=',$purchaseType];
        $where[] = ['supply_uid','=',$this->userInfo['role_pid']];
        $data['cartNum'] = $this->Model->where($where)->count();
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取购物车列表
    /*------------------------------------------------------ */
    public function getCartList()
    {
        $purchaseType = input('purchaseType', 0, 'intval');
        $is_select = input('is_select', 0, 'intval');
        $rec_id = input('rec_id', '', 'trim');
        $data['cartList'] = $this->Model->getCartList($purchaseType,$is_select,$rec_id);
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 设置商品是否选中
    /*------------------------------------------------------ */
    public function setSel()
    {
        $rec_id = input('rec_id');
        if ($rec_id < 1 && $rec_id != 'all') return $this->error('传值失败，请重新尝试提交！');
        $is_select = input('is_select', 0, 'intval');
        $res = $this->Model->updateCart($rec_id, ['is_select' => $is_select]);
        return $this->success();
    }
    /*------------------------------------------------------ */
    //-- 删除购物车的商品
    /*------------------------------------------------------ */
    public function delGoods()
    {
        $rec_id = input('rec_id', 0, 'intval');
        if ($rec_id < 1) return $this->error('传值错误，请重试.');
        $where[] = ['rec_id','=',$rec_id];
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $res = $this->Model->where($where)->delete();
        if ($res < 1){
            return $this->error('删除失败，请重试.');
        }
        return $this->success();
    }
    /*------------------------------------------------------ */
    //-- 清空购物车
    /*------------------------------------------------------ */
    public function clearCart()
    {
        $purchaseType = input('purchaseType', 0, 'intval');
        if ($purchaseType < 1) return $this->error('传值错误，请重试.');
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $where[] = ['purchase_type','=',$purchaseType];
        $res = $this->Model->where($where)->delete();
        if ($res < 1){
            return $this->error('删除失败，请重试.');
        }
        return $this->success();
    }
    /*------------------------------------------------------ */
    //-- 修改商品订购数量
    /*------------------------------------------------------ */
    public function editNum()
    {
        $rec_id = input('rec_id', 0, 'intval');
        if ($rec_id < 1) return $this->error('传值失败，请重新尝试提交.');
        $num = input('num', 1, 'intval');
        if ($num < 1) return $this->error('订购数量不能小于1.');

        $where[] = ['rec_id','=',$rec_id];
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $convert_unit_val = $this->Model->where($where)->value('convert_unit_val');
        $res = $this->Model->where($where)->update(['goods_number'=>$num,'real_number'=>$num * $convert_unit_val]);
        if ($res < 1){
            return $this->error('更新数量失败.');
        }
        return $this->success();
    }
    /**计算运费
     * @param int $address_id 收货地址
     * @param bool $is_return 是否直接返回数据
     * @param string $recids 指定购物车中商品
     * @return mixed|void
     */
    public function evalShippingFee()
    {
        $address_id = input('address_id', '0', 'intval');
        $rec_id = input('rec_id', '', 'trim');
        $purchaseType = input('purchaseType', 0, 'intval');
        $is_select = input('is_select', 0, 'intval');
        if ($address_id < 1) {
            $data['shipping_fee'] = sprintf("%.2f", 0);
            return $this->success($data);
        }
        $addressList = (new \app\member\model\UserAddressModel)->getRows();
        $address = $addressList[$address_id];
        $cartList = $this->Model->getCartList($purchaseType,$is_select,$rec_id);
        $shippingFee = $this->Model->_evalShippingFee($address,$cartList);
        $data['shippingFee'] = $shippingFee;
        return $this->success($data);
    }


    /*------------------------------------------------------ */
    //-- 执行下单
    /*------------------------------------------------------ */
    public function addOrder()
    {
        $lookKey = 'channel_add_order_'.$this->userInfo['user_id'];
        $res = redisLook($lookKey);
        if ($res == false){
            return $this->error('正在处理，请不要重复提交.');
        }
        $OrderModel = new OrderModel();
        $res = $OrderModel->add(input('post.'));
        redisLook($lookKey,-1);//销毁锁
        if ($res !== true){
            return $this->error($res);
        }
        $data['order_id'] = $OrderModel->order_id;
        return $this->success($data);
    }
}
