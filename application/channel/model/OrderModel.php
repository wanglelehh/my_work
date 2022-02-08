<?php

namespace app\channel\model;
use app\BaseModel;
use think\Db;
use think\facade\Cache;

use app\member\model\UserAddressModel;
use app\member\model\UsersModel;
use app\member\model\UpLevelModel;

//*------------------------------------------------------ */
//-- 代理订单
/*------------------------------------------------------ */
class OrderModel extends BaseModel
{
	protected $table = 'channel_order_info';
	public  $pk = 'order_id';
    protected $mkey = 'channel_order_mky_';
    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache($order_id = 0)
    {
        Cache::rm($this->mkey . $order_id);
        Cache::rm($this->mkey . '_goods_' . $order_id);
    }
    /*------------------------------------------------------ */
    //-- 生成订单编号
    /*------------------------------------------------------ */
    public function getOrderSn()
    {
        $date = date('ymd');
        $order_sn = 'ws'.$date . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT).date('s');
        $where[] = ['order_sn', '=', $order_sn];
        $count = $this->where($where)->count('order_id');
        if ($count > 0) return $this->getOrderSn();
        return $order_sn;
    }
    /*------------------------------------------------------ */
    //-- 写入订单日志
    /*------------------------------------------------------ */
    function _log(&$order, $logInfo = '')
    {
        return OrderLogModel::_log($order, $logInfo);
    }
    /*------------------------------------------------------ */
    //-- 写入订单
    /*------------------------------------------------------ */
    public function add($post){
        $purchaseType = intval($post['purchaseType']);
        $rec_id = trim($post['rec_id']);
        $CartModel = new CartModel();
        $cartList = $CartModel->getCartList($purchaseType,1,$rec_id);
        if (empty($cartList) == true){
            return '请选择订购商品.';
        }
        $purchaseTypeList = config('config.purchaseTypeList');
        if (empty($purchaseTypeList[$purchaseType])){
            return '不存在相关进货类型.';
        }
        $inArr['buyer_message'] = trim($post['buyer_message']);
        $inArr['order_status'] = 0;
        $inArr['pay_status'] = 0;
        $inArr['shipping_status'] = 0;
        $inArr['is_pay'] = $purchaseType==3 ? 0 : 1;
        $inArr['shipping_fee'] = 0;
        if ($purchaseType > 1){
            $address_id = intval($post['address_id']);
            if ($address_id < 0) return '请设置收货地址后，再操作.';
            $addressList = (new UserAddressModel)->getRows();
            $address = $addressList[$address_id];
            if (empty($address)) return '相关收货地址不存在.';
            $inArr['consignee'] = $address['consignee'];
            $inArr['address'] = $address['address'];
            $inArr['merger_name'] = $address['merger_name'];
            $inArr['province'] = $address['province'];
            $inArr['city'] = $address['city'];
            $inArr['district'] = $address['district'];
            $inArr['mobile'] = $address['mobile'];
            //运费处理
            $shippingFee = $CartModel->_evalShippingFee($address, $cartList);
            $inArr['shipping_fee'] = $shippingFee['shipping_fee'];

        }
        $time = time();
        $goods_amount = 0;
        $buy_goods_sn = [];
        $buy_goods_id = [];

        foreach ($cartList as $cg){
            $buy_goods_sn[] = $cg['goods_sn'];
            $buy_goods_id[] = $cg['goods_id'];
            $goods_amount += $cg['sale_price'] * $cg['real_number'];
        }
        $inArr['goods_amount'] = $goods_amount;
        $inArr['buy_goods_sn'] = join(',',$buy_goods_sn);
        $inArr['buy_goods_id'] = join(',',$buy_goods_id);

        $StockModel = new StockModel();
        if ($purchaseType == 3) {//提货订单
            $lackGoods = $StockModel->checkLackGoods($cartList,$this->userInfo['user_id'],$purchaseType);//验证库存
            if (empty($lackGoods) == false){
                return '库存不足，不能下单.';
            }
            $inArr['is_stock'] = 1;
            $inArr['order_amount'] = $inArr['shipping_fee'];
            if ($inArr['order_amount'] <= 0){
                $inArr['is_pay'] = 0;//不需要支付
                $inArr['order_status'] = 1;
                $inArr['confirm_time'] = $time;
                $inArr['is_pay_eval'] = 1;
            }else{
                $inArr['is_pay'] = 1;
            }
            $inArr['supply_uid'] = $this->userInfo['user_id'];//提货订单供货人为自己

        }else{
            $inArr['is_pay'] = 1;//需要支付
            $inArr['order_amount'] = $goods_amount  + $inArr['shipping_fee'];//暂不考虑运费，订单直接等于商品金额
            $inArr['supply_uid'] = $this->userInfo['role_pid'];
        }

        $ProxyUsersModel = new ProxyUsersModel();
        if ($inArr['supply_uid'] > 0){//获取供货上级当时层级
            $supplyUserInfo = $ProxyUsersModel->info($inArr['supply_uid']);
            $inArr['supply_role_id'] = $supplyUserInfo['role_id'];
        }


        $inArr['purchase_type'] = $purchaseType;
        $inArr['add_time'] = $time;
        $inArr['user_id'] = $this->userInfo['user_id'];
        $inArr['user_role_id'] = $this->userInfo['role_id'];

        $inArr['user_pid'] = $this->userInfo['pid'];
        if ($inArr['user_pid'] > 0){//获取推荐上级当时层级
            $pUserInfo = $ProxyUsersModel->info($inArr['user_pid']);
            $inArr['p_role_id'] = $pUserInfo['role_id'];
        }

        $inArr['add_time'] = $time;
        $inArr['order_sn'] = $this->getOrderSn();

        Db::startTrans();//启动事务
        $res = $this->save($inArr);
        if ($res < 1) {
            Db::rollback();// 回滚事务
            return '未知原因，订单写入失败.';
        }
        $order_id = $this->order_id;
        $_log = '生成'.$purchaseTypeList[$purchaseType];
        $inArr['order_id'] = $order_id;
        $res = $this->_log($inArr,$_log);
        if ($res < 1) {
            Db::rollback();// 回滚事务
            return '未知原因，订单日志写入失败.';
        }
        $res = $this->addOrderGoods($cartList,$order_id);//写入商品
        if ($res !== true) {
            Db::rollback();// 回滚事务
            return $res;
        }
        if ($purchaseType == 3) {//提现下单时扣除库存，云仓与现货，跳转支付时处理
            $res = $StockModel->stockBySupply($order_id,$cartList,$purchaseType, $this->userInfo['user_id'],'DEC');
            if ($res !== true){
                Db::rollback();//回滚事务
                return '扣库存失败，不能完成此操作.';
            }
        }
        Db::commit();// 提交事务
        return true;
    }
    /*------------------------------------------------------ */
    //-- 写入订单商品
    //-- $cartList array 购物车列表
    //-- $order_id int 订单ID
    /*------------------------------------------------------ */
    public function addOrderGoods(&$cartList,$order_id)
    {
        $add_time = time();
        $orderGoods = [];
        $rec_ids = [];
        foreach ($cartList as $key => $cg) {
            $goods = array(
                'order_id' => $order_id,
                'brand_id' => $cg['brand_id'],
                'cid' => $cg['cid'],
                'goods_id' => $cg['goods_id'],
                'goods_name'=>$cg['goods_name'],
                'sku_id' => $cg['sku_id'],
                'sku_val' => $cg['sku_val'],
                'sku_name' => $cg['sku_name'],
                'goods_sn' => $cg['goods_sn'],
                'sale_price' => $cg['sale_price'],
                'price_type' => $cg['price_type'],
                'goods_weight' => $cg['goods_weight'],
                'base_unit' => $cg['base_unit'],
                'base_unit_name' => $cg['base_unit_name'],
                'goods_unit' => $cg['goods_unit'],
                'goods_unit_name' => $cg['goods_unit_name'],
                'convert_unit_val' => $cg['convert_unit_val'],
                'goods_number' => $cg['goods_number'],
                'real_number' => $cg['real_number'],
                'pic' => $cg['pic'],
                'add_time'=>$add_time,
            );
            $orderGoods[] = $goods;
            $rec_ids[] = $cg['rec_id'];
        }
        $res = (new OrderGoodsModel)->insertAll($orderGoods);
        if ($res < 1) {
            Db::rollback();// 回滚事务
            return '未知原因，订单商品写入失败.';
        }

        $where[] = ['rec_id', 'in', $rec_ids];
        (new CartModel)->where($where)->delete();// 清理购物车的商品
        return true;
    }

    /*------------------------------------------------------ */
    //-- 订单信息
    //-- $order_id int 订单ID
    //-- $iscache bool 是否读取缓存
    //-- $loginUid int 当前登陆会员ID
    /*------------------------------------------------------ */
    public function info($order_id, $iscache = true,$loginUid = 0)
    {
        static $StockModel;
        if ($iscache == true) {
           $orderInfo = Cache::get($this->mkey . $order_id);
        }
        if (empty($orderInfo) == true) {
            $orderInfo = $this->where('order_id', $order_id)->find();
            if (empty($orderInfo)) return [];
            $orderInfo = $orderInfo->toArray();
            $orderGoods = $this->orderGoods($order_id);
            $orderInfo['goodsList'] = $orderGoods['goodsList'];
            $orderInfo['goodsNum'] = $orderGoods['goodsNum'];
            $orderInfo['_add_time'] = dateTpl($orderInfo['add_time'],'Y-m-d H:i:s',true);
            $orderInfo['_pay_time'] = dateTpl($orderInfo['pay_time'],'Y-m-d H:i:s',true);
            $orderInfo['_shipping_time'] = dateTpl($orderInfo['shipping_time'],'Y-m-d H:i:s',true);
            $orderInfo['_cancel_time'] = dateTpl($orderInfo['cancel_time'],'Y-m-d H:i:s',true);
            $orderInfo['offlineimg'] = explode(',',$orderInfo['offlineimg']);
            $UsersModel = new UsersModel();
            $userInfo = $UsersModel->info($orderInfo['user_id']);
            $orderInfo['user_name'] = $userInfo['real_name'];
            if ($orderInfo['supply_uid'] > 0){
                $supplyUserInfo = $UsersModel->info($orderInfo['supply_uid']);
                $orderInfo['supply_name'] = $supplyUserInfo['real_name'];
            }else{
                $orderInfo['supply_name'] = '厂家';
            }
            if (empty($StockModel)) {
                $StockModel = new StockModel();
            }
            $orderInfo['lackGoods'] = 0;
            if ($orderInfo['is_stock'] == 0 && $orderInfo['purchase_type'] <= 3){//云仓、现货订单未扣库存，判断库存是否满足
                $lackGoods = $StockModel->checkLackGoods($orderInfo['goodsList'],$orderInfo['supply_uid'],$orderInfo['purchase_type']);
                if (empty($lackGoods) == false){
                    $orderInfo['lackGoods'] = 1;
                }
            }
            if ($orderInfo['is_pay_eval'] == 1){//支付成功后待执行处理
                asynRun('channel/OrderModel/asynRunPaySuccessEval',['order_id'=>$orderInfo['order_id']]);//异步执行
            }
            Cache::set($this->mkey . $order_id, $orderInfo, 20);
        }
        $orderInfo['isConfirmed'] = 0;//确认订单
        $orderInfo['isCancel'] = 0;//取消订单
        $orderInfo['isDelete'] = 0;//删除订单
        $orderInfo['isPay'] = 0;//支付订单
        $orderInfo['isSign'] = 0;//签收订单
        $orderInfo['isCheckPay'] = 0;//审核付款
        $orderInfo['isAddStock'] = 0;//一键补货
        $orderInfo['isShipping'] = 0;//发货
        $config = config('config.');

        $orderInfo['purchase_type_name'] = $config['purchaseTypeList'][$orderInfo['purchase_type']];
        if ($orderInfo['pay_status'] == $config['PS_WAITCHACK']) {
            $orderInfo['ostatus'] = '付款待审核';
            if ($loginUid == $orderInfo['supply_uid'] && $orderInfo['supply_uid'] == $orderInfo['payee_uid']){
                $orderInfo['isCheckPay'] = 1;
            }

        }elseif ($orderInfo['order_status'] == $config['OS_UNCONFIRMED']) {//订单未确定
            if ($orderInfo['pay_status'] == $config['PS_CHACKERROR']){
                $orderInfo['ostatus'] = '付款审核失败';
                if ($orderInfo['user_id'] == $loginUid){
                    $orderInfo['isCancel'] = 1;//可操作：取消
                    $orderInfo['isPay'] = 1;//可操作：支付
                }
            }elseif ($orderInfo['lackGoods'] == 1){
                $orderInfo['ostatus'] = '缺货';
                if ($orderInfo['supply_uid'] == $loginUid) {
                    $orderInfo['isAddStock'] = 1;
                }else{
                    $orderInfo['isPay'] = 1;
                }
            }elseif ($orderInfo['is_pay'] == 1 && $orderInfo['pay_status'] == $config['PS_UNPAYED']) {
                $orderInfo['isCancel'] = 1;//可操作：取消
                $orderInfo['isPay'] = 1;//可操作：支付
                $orderInfo['ostatus'] = '待付款';
            } else {
                $orderInfo['ostatus'] = '待确认';
                $orderInfo['isCancel'] = 1;//可操作：取消
                if ($orderInfo['supply_uid'] == $loginUid){
                     $orderInfo['isConfirmed'] = 1;//可操作：确认订单
                }
            }
        } elseif ($orderInfo['order_status'] == $config['OS_CONFIRMED']) {
            if ($orderInfo['shipping_status'] == $config['SS_UNSHIPPED']) {
                $orderInfo['ostatus'] = '待发货';
                if ($orderInfo['purchase_type'] == 2 && $orderInfo['supply_uid'] == $loginUid){//现货订单前端才有发货权限，提货订单为云仓提货，由平台发货
                    $orderInfo['isShipping'] = 1;
                }
            } elseif ($orderInfo['shipping_status'] == $config['SS_SHIPPED']) {
                $orderInfo['ostatus'] = '已发货';
                if ($orderInfo['user_id'] == $loginUid){
                    $orderInfo['isSign'] = 1;
                }
            } elseif ($orderInfo['shipping_status'] == $config['SS_SIGN']) {
                if ($orderInfo['purchase_type'] == 1){
                    $orderInfo['ostatus'] = '已入仓';
                }else{
                    $orderInfo['ostatus'] = '已完成';
                }
            }
            if ($orderInfo['is_pay'] > 0 && $orderInfo['pay_status'] == $config['PS_UNPAYED']) {
                if ($orderInfo['user_id'] == $loginUid) {
                    $orderInfo['isCancel'] = 1;//可操作：取消
                    $orderInfo['isPay'] = 1;//可操作：支付
                }
                $orderInfo['ostatus'] = '待付款';
            }
        } else {
            if ($orderInfo['is_delete'] == 1){
                $orderInfo['ostatus'] = '已删除';
            }else{
                $orderInfo['isDelete'] = 1;
                $orderInfo['ostatus'] = '已取消';
            }
        }
        return $orderInfo;
    }
    /*------------------------------------------------------ */
    //-- 获取订单商品
    //-- $order_id int 订单ID
    /*------------------------------------------------------ */
    public function orderGoods($order_id)
    {
        static $OrderGoodsModel;
        if (empty($OrderGoodsModel)) {
            $OrderGoodsModel = new OrderGoodsModel();
        }
        $rows = $OrderGoodsModel->order('rec_id ASC')->where('order_id', $order_id)->select()->toArray();
        if (empty($rows)) return [];
        $goodsNum = 0;
        foreach ($rows as $key => $row) {
            $row['exp_price'] = explode('.', $row['sale_price']);
            $goodsList[] = $row;
            $goodsNum += $row['goods_number'];
        }
        return ['goodsList'=>$goodsList, 'goodsNum'=>$goodsNum];
    }
    /*------------------------------------------------------ */
    //-- 获取订单商品
    //-- $upDate array 更新订单
    /*------------------------------------------------------ */
    public function upInfo($upData)
    {
        $order_id = $upData['order_id'];
        unset($upData['order_id']);
        $upData['update_time'] = time();
        $res = $this->where('order_id',$order_id)->update($upData);
        if ($res < 1){
            return false;
        }
        $this->cleanMemcache($order_id);
        return true;
    }
    /*------------------------------------------------------ */
    //-- 获取订单数量统计 不包括已完成，已取消
    /*------------------------------------------------------ */
    public function getNum()
    {
        $mkey = $this->mkey.'getNum' . $this->userInfo['user_id'];
        $data = Cache::get($mkey);
        if (empty($data) == false) return $data;
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $where[] = ['order_status','in',[0,1]];
        $where[] = ['shipping_status','<',2];
        $rows = $this->where($where)->field('count(order_id) as num,purchase_type')->group('purchase_type')->select();
        $data = [];
        foreach ($rows as $row){
            $data[$row['purchase_type']] = $row['num'];
        }
        Cache::set($mkey,$data,15);
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 获取下级订单数量统计 不包括已完成，已取消
    /*------------------------------------------------------ */
    public function getSubNum()
    {
        $mkey = $this->mkey.'getSubNum' . $this->userInfo['user_id'];
        $data = Cache::get($mkey);
        if (empty($data) == false) return $data;
        $where[] = ['supply_uid','=',$this->userInfo['user_id']];
        $where[] = ['order_status','IN',[0,1]];
        $where[] = ['shipping_status','=',0];
        $rows = $this->where($where)->field('count(order_id) as num,purchase_type')->group('purchase_type')->select();
        $data = [];
        foreach ($rows as $row){
            $data[$row['purchase_type']] = $row['num'];
        }
        Cache::set($mkey,$data,15);
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 确定订单操作
    //-- $order_id int 订单ID
    //-- $supply_uid int 供货代理ID，前端调用必须传，用于识别是否正确供货代理操作
    /*------------------------------------------------------ */
    public function confirmed($order_id,$supply_uid = 0)
    {
        $orderInfo = $this->info($order_id);
        $operating = $this->operating($orderInfo);//订单操作权限
        if ($operating['confirmed'] !== true){
            return '订单当前状态不能进行此操作.';
        }
        if ($supply_uid > 0 && $supply_uid != $orderInfo['supply_uid']){
            return '你无权操作.';
        }
        Db::startTrans();//开启事务
        if ($orderInfo['is_stock'] == 0){//未扣库存，尝试执行扣库存
            $StockModel = new StockModel();
            //执行扣库存
            $res = $StockModel->evalSupplyStock($orderInfo);
            if ($res == false){
                Db::rollback();//回滚事务
                return '扣库存失败，不能完成此操作.';
            }
            $upData['is_stock'] = 1;
        }
        $upData['order_id'] = $order_id;
        $upData['order_status'] = 1;
        $upData['confirm_time'] = time();
        $upData['cancel_time'] = 0;
        $upData['money_paid'] = 0;
        $res = $this->upInfo($upData);
        if ($res !== true){
            Db::rollback();//回滚事务
            return '更新订单失败.';
        }
        Db::commit();//提交事务
        $orderInfo['order_status'] = $upData['order_status'];
        if ($supply_uid > 0){
            $this->_log($orderInfo, '设为已确认');
        }else{
            $this->_log($orderInfo, '后台设为已确认');
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 线下支付收款确认
    //-- $orderInfo array 订单信息
    //-- $operate int 审核结果
    //-- $supply_uid int 供货代理ID，前端调用必须传，用于识别是否正确供货代理操作
    /*------------------------------------------------------ */
    public function cfmCodPay(&$orderInfo,$operate,$transaction_id = '',$supply_uid)
    {
        if ($orderInfo['pay_code'] != 'offline'){
            return '非线下打款订单，不允许操作.';
        }
        if ($supply_uid > 0 && $supply_uid != $orderInfo['supply_uid']){
            return '你无权操作此订单.';
        }
        $operating = $this->operating($orderInfo);//订单操作权限
        if ($operating['cfmCodPay'] !== true){
            return '订单当前状态不能操作线下打款确认.';
        }
        $config = config('config.');
        //审核失败
        if ($operate == 2){
            $upData['order_id'] = $orderInfo['order_id'];
            $upData['pay_status'] = $config['PS_CHACKERROR'];
            $upData['transaction_id'] = $transaction_id;
            $upData['cfmpay_user'] = $supply_uid > 0 ? $supply_uid : AUID;
            $res = $this->upInfo($upData);
            if ($res !== true){
                return '操作失败.';
            }
            $orderInfo['pay_status'] = $upData['pay_status'];
            $res = $this->_log($orderInfo, '线下打款，审核失败：'.$transaction_id);
            if ($res < 1) {
                return '订单日志写入失败，请重试.';
            }
            return true;
        }
        //审核失败end

        $time = time();
        $upData['order_status'] = $config['OS_CONFIRMED'];
        $upData['pay_status'] = $config['PS_PAYED'];
        if ($orderInfo['confirm_time'] < 1) {
            $upData['confirm_time'] = $time;
        }
        $upData['pay_time'] = $time;
        $upData['money_paid'] = $orderInfo['order_amount'];
        $upData['cfmpay_user'] = $supply_uid > 0 ? $supply_uid : AUID;
        $upData['order_id'] = $orderInfo['order_id'];
        $upData['is_pay_eval'] = 1;//设为待执行支付成功后的相关处理

        Db::startTrans();//开启事务
        $res = $this->upInfo($upData);
        if ($res !== true){
            Db::rollback();// 回滚事务
            return '操作失败.';
        }
        $orderInfo['order_status'] = $upData['order_status'];
        $orderInfo['pay_status'] = $upData['pay_status'];
        if ($supply_uid > 0){
            $log = '线下打款，供货代理收款确认：'.$transaction_id;
        }else{
            $log = '线下打款，后台收款确认：'.$transaction_id;
        }
        $res = $this->_log($orderInfo, $log);
        if ($res < 1) {
            Db::rollback();// 回滚事务
            return '订单日志写入失败，请重试.';
        }
        Db::commit();//提交事务
        asynRun('channel/OrderModel/asynRunPaySuccessEval',['order_id'=>$orderInfo['order_id']]);//异步执行
        return true;
    }
    /*------------------------------------------------------ */
    //-- 设置为未付款
    /*------------------------------------------------------ */
    public function setUnPay($order_id){
        $config = config('config.');
        $upData['order_status'] = 0;
        $orderInfo['order_status'] = $upData['order_status'];
        $upData['order_id'] = $order_id;
        $upData['pay_status'] = $config['PS_WAITCHACK'];
        $upData['shipping_status'] = $config['SS_UNSHIPPED'];
        $upData['pay_time'] = 0;
        $upData['money_paid'] = 0;
        Db::startTrans();//开启事务
        $res = $this->upInfo($upData);
        if ($res !== true){
            Db::rollback();// 回滚事务
            return '操作失败.';
        }
        $orderInfo = $this->info($order_id);
        $log = '撤回付款审核';
        $res = $this->_log($orderInfo, $log);
        if ($res < 1) {
            Db::rollback();// 回滚事务
            return '订单日志写入失败，请重试.';
        }
        if ($orderInfo['purchase_type'] == 1){//云仓订单
            $res = (new StockModel)->stockByBuy($orderInfo,'DEC',$log);//库存处理减库存
            if ($res !== true) {
                Db::rollback();// 回滚事务
                return $res;
            }
            $res = (new RewardLogModel)->returnReward('order',$order_id,'return','撤回付款审核,撤销：');//退回相关奖励
            if ($res !== true) {
                Db::rollback();// 回滚事务
                return $res;
            }
        }
        Db::commit();//提交事务

        return true;
    }
    /*------------------------------------------------------ */
    //-- 订单支付成功处理，在线支付回调执行
    /*------------------------------------------------------ */
    public function updatePay($upData = [], $_log = '支付成功', $is_commit = false)
    {
        unset($upData['order_amount']);
        $config = config('config.');
        $upData['pay_status'] = $config['PS_PAYED'];
        $upData['order_status'] = $config['OS_CONFIRMED'];
        $upData['pay_time'] = time();
        $upData['is_pay_eval'] = 1;//设为待执行支付成功后的相关处理
        $res = $this->upInfo($upData);
        if ($res !== true) {
            return false;
        }
        $orderInfo = $this->find($upData['order_id'])->toArray();
        $this->_log($orderInfo,$_log);
        if ($is_commit == true){
            Db::commit();// 提交事务
        }
        asynRun('channel/OrderModel/asynRunPaySuccessEval',['order_id'=>$orderInfo['order_id']]);//异步执行
        return true;
    }
    /*------------------------------------------------------ */
    //-- 订单支付成功后异步处理
    /*------------------------------------------------------ */
    public function asynRunPaySuccessEval($param){
        $order_id = $param['order_id'] * 1;//异步执行传入必须强制类型
        if ($order_id < 1){
            return '缺少订单ID';
        }
        $mkey = 'payChannelSuccessEvalIng'.$order_id;
        $res = redisLook($mkey,10);//redis锁
        if ($res == false) return true;

        $orderInfo = $this->info($order_id,false);
        if ($orderInfo['is_pay_eval'] != 1){
            return true;
        }
        $config = config('config.');
        $upData = [];
        if ($orderInfo['purchase_type'] == 1 && $orderInfo['shipping_status'] != $config['SS_SIGN']) {//云仓订单库存处理
            Db::startTrans();//开启事务
            //库存处理
            $res = (new StockModel)->stockByBuy($orderInfo, 'INC','支付成功');
            if ($res !== true) {
                Db::rollback();// 回滚事务
                return $res;
            }
            //库存处理end
            $upData['shipping_status'] = $config['SS_SIGN'];//云仓订单直接为已完成
            $upData['sign_time'] = time();//云仓订单直接为已完成
            $res = $this->where('order_id',$orderInfo['order_id'])->update($upData);
            if ($res < 1){
                Db::rollback();// 回滚事务
                $this->_log($orderInfo,'增加云库存失败');
                return '增加云库存更新订单状态失败';
            }
            Db::commit();// 提交事务
        }
        if ($orderInfo['is_reward'] == 0){//奖励处理
            Db::startTrans();//开启事务
            $RewardLogModel = new RewardLogModel();
            $res = $RewardLogModel->runByOrder($orderInfo);//计算奖项
            if ($res !== true) {
                Db::rollback();// 回滚事务
                return $res;
            }
            if ($orderInfo['purchase_type'] == 1){//云仓订单，立返奖项
                $res = $RewardLogModel->runRewardToUserByOrder($orderInfo['order_id']);
                if ($res !== true) {
                    Db::rollback();// 回滚事务
                    return $res;
                }
            }else{
                $res = $RewardLogModel->runRewardUpByOrder($orderInfo['order_id']);
                if ($res !== true) {
                    Db::rollback();// 回滚事务
                    return $res;
                }
            }
            $UpLevelModel = new UpLevelModel();
            $orderInfo['channel_order'] = 1;
            $res = $UpLevelModel->evalLevelUp($orderInfo);//升级处理
            if ($res == false) return false;
            Db::commit();// 提交事务
            $upData['is_reward'] = 1;
        }
        $upData['is_pay_eval'] = 2;
        $this->where('order_id',$orderInfo['order_id'])->update($upData);
        $this->cleanMemcache($orderInfo['order_id']);
        redisLook($mkey,-1);//销毁锁
        return true;
    }
    /*------------------------------------------------------ */
    //-- 取消订单操作
    //-- $order_id int 订单ID
    //-- $user_id int 会员ID 前端取消时必须传
    /*------------------------------------------------------ */
    public function cancel(&$orderInfo,$user_id = 0)
    {
        if (empty($orderInfo)){
            return '没有找到相关订单信息.';
        }

        if ($user_id > 0 && $orderInfo['user_id'] != $user_id){
            if ($orderInfo['isCancel'] == 0){
                return '当前订单不能操作取消.';
            }
            if ($orderInfo['supply_uid'] == $user_id){//供货代理操作取消
                if (time() - $orderInfo['add_time'] < 3600){
                    return '下级订单超过1小时才能操作取消.';
                }
            }else{
                return '你无权进行操作.';
            }
        }
        Db::startTrans();//启动事务

        $upData['is_stock'] = 0;
        $upData['order_id'] = $orderInfo['order_id'];
        $upData['order_status'] = config('config.OS_CANCELED');
        $upData['cancel_time'] = time();
        $res = $this->upInfo($upData);
        if ($res < 1){
            Db::rollback();// 回滚事务
            return '操作失败，请重试.';
        }
        if ($user_id > 0){
            if ($orderInfo['user_id'] == $user_id){
                $log = '用户取消订单';
            }elseif ($orderInfo['supply_uid'] == $user_id) {//供货代理操作取消
                $log = '拿货上级取消订单';
            }
        }else{
            $log = '后台取消订单';
        }
        $orderInfo['order_status'] = $upData['order_status'];
        $res = $this->_log($orderInfo, $log);
        if ($res < 1) {
            Db::rollback();// 回滚事务
            return '订单日志写入失败，请重试.';
        }
        if ($orderInfo['is_stock'] == 1){//已扣库存的，返还库存到供货人
            $res = (new StockModel)->evalSupplyStock($orderInfo,'INC');
            if ($res !== true) {
                Db::rollback();// 回滚事务
                return $res;
            }
        }
        //退回相关奖励
        $RewardLogModel = new RewardLogModel();
        $res = $RewardLogModel->returnReward('order',$orderInfo['order_id'],'cancel');
        if ($res !== true) {
            Db::rollback();// 回滚事务
            return $res;
        }
        Db::commit();//提交事务
        return true;
    }
    /*------------------------------------------------------ */
    //-- 订单退款操作
    //-- $order_id int 订单ID
    /*------------------------------------------------------ */
    public function returnPay($order_id)
    {
        $orderInfo = $this->info($order_id);
        $operating = $this->operating($orderInfo);//订单操作权限
        if ($operating['returnPay'] !== true){
            return '订单当前状态不能操作退款.';
        }
        $config = config('config.');
        Db::startTrans();//开启事务
        $upData['order_id'] = $order_id;
        $upData['pay_status'] = $config['PS_RUNPAYED'];
        $res = $this->upInfo($upData);
        if ($res !== true){
            Db::rollback();//回滚
            return '更新订单失败.';
        }
        $orderInfo['pay_status'] = $upData['pay_status'];
        $res = $this->_log($orderInfo, '设为退款');
        if ($res < 1) {
            Db::rollback();// 回滚事务
            return '订单日志写入失败，请重试.';
        }
        $WalletModel = new WalletModel();
        if ($orderInfo['money_paid'] > 0) {
            if (in_array($orderInfo['pay_code'] ,['balance','offline'])) {//线下打款和余额支付，退回余额
                $changedata['by_id'] = $orderInfo['order_id'];
                $changedata['change_desc'] = '订单退款到余额:' . $orderInfo['money_paid'];
                $changedata['change_type'] = 2;
                $changedata['balance_money'] = $orderInfo['money_paid'];
                $res = $WalletModel->change($changedata, $orderInfo['user_id'], false);
                if ($res != true) {
                    Db::rollback();//回滚
                    return '订单退款到余额失败.';
                }
            }elseif ($orderInfo['pay_code'] == 'goodsMoney') {//货款退回货款
                $changedata['by_id'] = $orderInfo['order_id'];
                $changedata['change_desc'] = '订单退款到货款余额:' . $orderInfo['money_paid'];
                $changedata['change_type'] = 3;
                $changedata['goods_money'] = $orderInfo['money_paid'];
                $changedata['goods_money_outlay_total'] = $orderInfo['money_paid'] * -1;
                $res = $WalletModel->change($changedata, $orderInfo['user_id'],false);
                if ($res != true) {
                    Db::rollback();//回滚
                    return '订单退款到余额失败.';
                }
            }elseif ($orderInfo['pay_code'] == 'upLevelgoodsMoney') {//货款退回货款
                $changedata['by_id'] = $orderInfo['order_id'];
                $changedata['change_desc'] = '订单退款到升级货款余额:' . $orderInfo['money_paid'];
                $changedata['change_type'] = 5;
                $changedata['uplevel_goods_money'] = $orderInfo['money_paid'];
                $res = $WalletModel->change($changedata, $orderInfo['user_id'],false);
                if ($res != true) {
                    Db::rollback();//回滚
                    return '订单退款到余额失败.';
                }
            } else {//在线退款，必须放最后
                $code = str_replace('/', '\\', "/payment/" . $orderInfo['pay_code'] . "/" . $orderInfo['pay_code']);
                $payment = new $code();
                $orderInfo['refund_amount'] = $orderInfo['money_paid'];//实付金额
                $res = $payment->refund($orderInfo);
                if ($res !== true) {
                    Db::rollback();//回滚
                    return '请求退款接口失败：' . $res;
                }
            }
        }
        Db::commit();//提交事务
        return true;
    }

    /*------------------------------------------------------ */
    //-- 订单签收处理
    /*------------------------------------------------------ */
    public function sign(&$orderInfo)
    {
        $config = config('config.');
        $data['order_id'] = $orderInfo['order_id'];
        $data['shipping_status'] = $config['SS_SIGN'];
        $data['sign_time'] = time();
        Db::startTrans();//开启事务
        $res = $this->upInfo($data);
        if ($res !== true){
            Db::rollback();//回滚事务
            return '更新失败.';
        }
        $orderInfo['shipping_status'] = $data['shipping_status'];
        $res = $this->_log($orderInfo, '后台操作设为签收');
        if ($res < 1) {
            Db::rollback();// 回滚事务
            return '订单日志写入失败，请重试.';
        }
        if ($orderInfo['purchase_type'] > 1) {//非云仓订单
            $res = (new StockModel)->stockByBuy($orderInfo, 'INC', '订单签收增加库存');
            if ($res !== true) {
                Db::rollback();// 回滚事务
                return $res;
            }
            $res = (new RewardLogModel)->runRewardToUserByOrder($orderInfo['order_id']);//执行相关奖励到帐
            if ($res !== true) {
                Db::rollback();// 回滚事务
                return $res;
            }
        }
        Db::commit();//提交事务
        return true;
    }
    /*------------------------------------------------------ */
    //-- 设为未签收处理
    /*------------------------------------------------------ */
    public function setUnSign(&$orderInfo)
    {
        $config = config('config.');
        $upData['order_id'] = $orderInfo['order_id'];
        $upData['shipping_status'] = $config['SS_SHIPPED'];
        Db::startTrans();//开启事务
        $res = $this->upInfo($upData);
        if ($res !== true){
            Db::rollback();// 回滚事务
            return '操作失败.';
        }
        $orderInfo['shipping_status'] = $config['SS_SHIPPED'];
        $log = '设为未签收';
        $res = $this->_log($orderInfo, $log);
        if ($res < 1) {
            Db::rollback();// 回滚事务
            return '订单日志写入失败，请重试.';
        }
        $res = (new StockModel)->stockByBuy($orderInfo,'DEC',$log);//库存处理减库存
        if ($res !== true) {
            Db::rollback();// 回滚事务
            return $res;
        }
        $res = (new RewardLogModel)->returnReward('order',$orderInfo['order_id'],'return','设为未签收,撤销：');//退回相关奖励
        if ($res !== true) {
            Db::rollback();// 回滚事务
            return $res;
        }
        Db::commit();//提交事务
        return true;
    }
    /**订单后台操作权限
     * @param array $order 订单详情
     * @param bool $by_supplyer 供应商管理
     * @return array
     */
    public function operating(&$order)
    {
        $os = $order['order_status'];
        $ss = $order['shipping_status'];
        $ps = $order['pay_status'];
        $time = time();
        $config = config('config.');
        if ($os == $config['OS_UNCONFIRMED']) {//未确认
            $operating['isCancel'] = true;//取消
            if ($order['purchase_type']> 1) {
                $operating['editConsignee'] = true; //修改收货信息
            }

            if ($order['pay_status'] >= 10) {
                $operating['cfmCodPay'] = true;//设为已付款
            }
        } elseif ($os == $config['OS_CONFIRMED']) { //已确认
            if ($ss == $config['SS_UNSHIPPED']) {//未发货
                $operating['isCancel'] = true;
                if ($ps == $config['PS_UNPAYED']) {//未支付


                } elseif ($ps == $config['PS_PAYED']) {//已支付
                    if ($order['pay_status'] == 'offline') {
                        $operating['setUnPay'] = true;//撤回付款审核
                    }
                }
                if ($order['purchase_type'] > 1){
                    $operating['shipping'] = true;
                    $operating['editConsignee'] = true; //修改收货信息
                }
            } elseif ($ss == $config['SS_SHIPPED']) {//已发货
                $operating['sign'] = true;
                $operating['unshipping'] = true;//设为未发货
                $operating['returned'] = true;//设为退货

            } elseif ($ss == $config['SS_SIGN']) {//已签收/已入仓
                if ($order['purchase_type'] == 1){
                    if ($order['pay_status'] == 'offline' && $order['sign_time'] > $time - 86400) {
                        $operating['setUnPay'] = true;//撤回付款审核
                    }
                }else{
                    if (($order['sign_time'] > $time - 604800)) {
                        $operating['unsign'] = true;//设为未签收
                    }
                }
                unset($operating['unconfirmed']);
            } else {
                $operating['isCancel'] = true;
            }
        } elseif ($os == $config['OS_RETURNED']) { //已退货
            if ($ps == $config['PS_PAYED']) {//退货后可操作退款
                $operating['returnPay'] = true;
            }
        } elseif ($os == $config['OS_CANCELED']) { //已关闭
            //涉及库存问题，取消订单不允许恢复
            //涉及库存问题，取消订单不允许恢复
            //涉及库存问题，取消订单不允许恢复
            if ($ps == $config['PS_PAYED']) {//取消后可操作退款
                $operating['returnPay'] = true;
            }
        }
        return $operating;
    }


}
