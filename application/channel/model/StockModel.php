<?php

namespace app\channel\model;
use app\BaseModel;
use think\facade\Cache;
use app\shop\model\GoodsModel;
use app\shop\model\GoodsSkuModel;
//*------------------------------------------------------ */
//-- 库存表
/*------------------------------------------------------ */
class StockModel extends BaseModel
{
	protected $table = 'channel_proxy_stock';
	public  $pk = 'id';
    public  $time = 0;
    /*------------------------------------------------------ */
    //-- 优先自动执行
    /*------------------------------------------------------ */
    public function initialize(){
        parent::initialize();
        $this->time = time();
    }
    /*------------------------------------------------------ */
    //-- 检验库存不足的商品，云仓与现货订单调用
    //-- $goodsList array 订购商品
    //-- $supply_uid int 出货代理ID
    //-- $purchaseType int 订单类型
    /*------------------------------------------------------ */
    public function checkLackGoods(&$goodsList = array(),$supply_uid,$purchaseType)
    {
        $lackGoods = [];
        if ($supply_uid == 0) {//平台出货
            $GoodsModel = new GoodsModel();
            $GoodsSkuModel = new GoodsSkuModel();
            foreach ($goodsList as $grow) {
                if ($grow['sku_id'] > 0) {//多规格商品执行
                    $goods_number = $GoodsSkuModel->where('sku_id',$grow['sku_id'])->value('goods_number');
                }else{
                    $goods_number = $GoodsModel->where('goods_id', $grow['goods_id'])->value('goods_number');
                }
                if ($goods_number < $grow['real_number']){
                    $lackGoods[] = $grow;
                }
            }
        }else{//代理出货
            if ($purchaseType == 3){//提货订单查询云仓库存
                $purchaseType = 1;
            }
            foreach ($goodsList as $grow) {
                $where = [];
                $where[] = ['user_id','=',$supply_uid];
                $where[] = ['purchase_type','=',$purchaseType];
                if ($grow['sku_id'] > 0) {//多规格商品执行
                    $where[] = ['sku_id','=',$grow['sku_id']];
                    $goods_number = $this->where($where)->value('goods_number');
                }else{
                    $where[] = ['goods_id','=',$grow['goods_id']];
                    $goods_number = $this->where($where)->value('goods_number');
                }
                if ($goods_number < $grow['real_number']){
                    $lackGoods[] = $grow;
                }
            }
        }
        return $lackGoods;
    }
    /*------------------------------------------------------ */
    //-- 供货库存处理
    //-- $orderInfo array 订单信息
    //-- $type str DEC减库存，INC加库存
    /*------------------------------------------------------ */
    public function evalSupplyStock(&$orderInfo,$type = 'DEC')
    {
        $cache = Cache::init();
        $_redis = $cache->handler();
        if ($orderInfo['purchase_type'] == 3){//提货订单，实际供货为自己的云仓
            $supply_uid = $orderInfo['user_id'];
        }else{
            $supply_uid = $orderInfo['supply_uid'];
        }
        $mkey = 'channel_eval_store_'.$supply_uid;
        $isLock = $_redis->setnx($mkey,time()+2);
        if ($isLock == false){
            for($i = 0; $i < 3; $i++){
                $lock_time = $_redis->get($mkey);
                if(time() > $lock_time){
                    $_redis->del($mkey);
                    $isLock = $_redis->setnx($mkey,time()+2);
                    if ($isLock == true){
                        break;
                    }
                }
                sleep(1);
            }
            if ($lock_time == false){
                return false;
            }
        }
        if ($supply_uid == 0){//平台出货
            $GoodsModel = new GoodsModel();
            $GoodsSkuModel = new GoodsSkuModel();
            foreach ($orderInfo['goodsList'] as $grow) {
                if ($grow['sku_id'] > 0) {//多规格商品执行
                    $sub_map['sku_id'] = $grow['sku_id'];
                    if ($type == 'INC') {
                        $sub_data['goods_number'] = ['INC', $grow['goods_number']];
                    } else {
                        $goods_number = $GoodsSkuModel->where($sub_map)->value('goods_number');
                        if ($goods_number < $grow['real_number']){
                            return false;
                        }
                        $sub_data['goods_number'] = ['DEC', $grow['real_number']];
                    }
                    $res = $GoodsSkuModel->where($sub_map)->update($sub_data);
                    if ($res < 1) return false;
                } else {
                    if ($type == 'INC') {
                        $data['goods_number'] = ['INC', $grow['real_number']];
                    } else {
                        $goods_number = $GoodsModel->where('goods_id', $grow['goods_id'])->value('goods_number');
                        if ($goods_number < $grow['real_number']){
                            return false;
                        }
                        $data['goods_number'] = ['DEC', $grow['real_number']];
                    }
                    $res = $GoodsModel->where('goods_id', $grow['goods_id'])->update($data);
                    if ($res < 1) return false;
                }
                $GoodsModel->cleanMemcache($grow['goods_id']);
            }
        }else{//代理出货
            $res = $this->stockBySupply($orderInfo['order_id'],$orderInfo['goodsList'],$orderInfo['purchase_type'],$supply_uid,$type);
            if ($res !== true){
                return false;
            }
        }
        $_redis->del($mkey);
        return true;
    }
    /*------------------------------------------------------ */
    //-- 代理供货库存处理
    //-- $orderInfo array 订单信息
    //-- $type str DEC减库存，INC加库存
    /*------------------------------------------------------ */
    public function stockBySupply($order_id,&$goodsList,$purchase_type,$supply_uid,$type)
    {
        //订单商品按计量单位区分，这里先汇总
        $goodsTotal = [];
        foreach ($goodsList as $goods) {
            $goodsTotal[$goods['goods_id']][$goods['sku_id']]['purchase_price'] = $goods['sale_price'];
            if (empty($goodsTotal[$goods['goods_id']][$goods['sku_id']]['goods_number'])){
                $goodsTotal[$goods['goods_id']][$goods['sku_id']]['goods_number'] = $goods['real_number'];
            }else{
                $goodsTotal[$goods['goods_id']][$goods['sku_id']]['goods_number'] += $goods['real_number'];
            }
        }
        $_purchase_type = $purchase_type;
        if ($purchase_type == 3){
            $_purchase_type = 1;//提货订单，操作云仓库存
        }
        //处理代理库存总表
        foreach ($goodsTotal as $goods_id=>$skuGoods){
            foreach ($skuGoods as $sku_id=>$sg){
                $hash_code = md5($supply_uid.'_'.$_purchase_type.'_'.$goods_id.'_'.$sku_id);
                $where = [];
                $where[] = ['user_id','=',$supply_uid];
                $where[] = ['hash_code','=',$hash_code];
                $upData['update_time'] = $this->time;
                if ($type == 'INC') {
                    $upData['goods_number'] = ['INC', $sg['goods_number']];
                    $upData['out_number'] = ['DEC', $sg['goods_number']];
                }else{
                    $upData['goods_number'] = ['DEC', $sg['goods_number']];
                    $upData['out_number'] = ['INC', $sg['goods_number']];
                }
                $res = $this->where($where)->update($upData);
                if ($res < 1) {
                    return '更新代理库存失败.';//更新失败数据失败
                }
            }
        }
        $StockDetailModel = new StockDetailModel();
        //写库明细记录
        foreach ($goodsTotal as $goods_id=>$skuGoods) {
            foreach ($skuGoods as $sku_id => $sg) {
                if ($purchase_type == 3){
                    if ($type == 'INC') {
                        $operate = 1;//入库
                        $log = '提货订单取消，返还库存.';
                    }else{
                        $operate = 2;//出库
                        $log = '下单提货，扣减库存.';
                    }
                }else{
                    if ($type == 'INC') {
                        $operate = 1;//入库
                        $log = '下级订单取消，返还库存.';
                    }else{
                        $operate = 2;//出库
                        $log = '下级进货，扣减库存.';
                    }
                }
                $res = $StockDetailModel->saveLog($supply_uid,$_purchase_type,$goods_id,$sku_id,$order_id,$operate,$sg['goods_number'],$log,$this->time);
                if ($res == false) {
                    return '写入库存明细失败.';
                }
            }
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 订单执行购买代理出入库存处理，云仓订单付款成功执行，其它签收执行
    //-- $orderInfo array 订单信息
    //-- $type string DEC减库存，INC加库存
    //-- $log string 操作备注
    /*------------------------------------------------------ */
    public function stockByBuy($orderInfo,$type='INC',$log = '')
    {
        $purchase_type = $orderInfo['purchase_type'];
        if ($purchase_type == 3){
            $purchase_type = 2;//提货订单，库存归入现货库存
        }
        if ($purchase_type == 2){
            return true;//只操作云仓库存，取消实体库存定义
        }
        //订单商品按计量单位区分，这里先汇总
        $goodsTotal = [];
        foreach ($orderInfo['goodsList'] as $goods){
            $goodsTotal[$goods['goods_id']][$goods['sku_id']]['purchase_price'] = $goods['sale_price'];
            if (empty($goodsTotal[$goods['goods_id']][$goods['sku_id']]['goods_number'])){
                $goodsTotal[$goods['goods_id']][$goods['sku_id']]['goods_number'] = $goods['real_number'];
            }else{
                $goodsTotal[$goods['goods_id']][$goods['sku_id']]['goods_number'] += $goods['real_number'];
            }
        }

        //处理代理库存总表
        foreach ($goodsTotal as $goods_id=>$skuGoods){
            foreach ($skuGoods as $sku_id=>$sg){
                $hash_code = md5($orderInfo['user_id'].'_'.$purchase_type.'_'.$goods_id.'_'.$sku_id);
                $where = [];
                $where[] = ['user_id','=',$orderInfo['user_id']];
                $where[] = ['hash_code','=',$hash_code];
                $id = $this->where($where)->value('id');
                if ($id < 1){
                    if ($type == 'DEC'){
                        return false;//扣库存不应该没有找到库存信息
                    }
                    $inData = [];
                    $inData['hash_code'] = $hash_code;
                    $inData['purchase_type'] = $purchase_type;
                    $inData['user_id'] = $orderInfo['user_id'];
                    $inData['goods_id'] = $goods_id;
                    $inData['sku_id'] = $sku_id;
                    $inData['goods_number'] = $sg['goods_number'];
                    $inData['update_time'] = $this->time;
                    $res = $this->create($inData);
                    if ($res->id < 1) {
                        return '新增代理库存失败.';//写入数据失败
                    }
                }else{
                    $upData['update_time'] = $this->time;
                    if ($type == 'INC') {
                        $upData['goods_number'] = ['INC', $sg['goods_number']];
                    }else{
                        $upData['goods_number'] = ['DEC', $sg['goods_number']];
                    }
                    $res = $this->where('id',$id)->update($upData);
                    if ($res < 1) {
                        return '更新代理库存失败.';//更新失败数据失败
                    }
                }
            }
        }
        $StockDetailModel = new StockDetailModel();
        //写库明细记录
        foreach ($goodsTotal as $goods_id=>$skuGoods) {
            foreach ($skuGoods as $sku_id => $sg) {
                if ($type == 'INC') {
                    $operate = 1;//入库
                    $log = $log.'，增加库存.';
                }else{
                    $operate = 2;//出库
                    $log = $log. '，扣减库存.';
                }
                $res = $StockDetailModel->saveLog($orderInfo['user_id'],$orderInfo['purchase_type'],$goods_id,$sku_id,$orderInfo['order_id'],$operate,$sg['goods_number'],$log,$this->time);
                if ($res == false) {
                    return '写入库存明细失败.';
                }
            }
        }
        return true;
    }

}
