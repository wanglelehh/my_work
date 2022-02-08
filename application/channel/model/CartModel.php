<?php

namespace app\channel\model;
use app\BaseModel;

use app\shop\model\GoodsModel;
use app\shop\model\GoodsSkuModel;
//*------------------------------------------------------ */
//-- 购物车
/*------------------------------------------------------ */
class CartModel extends BaseModel
{
	protected $table = 'channel_cart';
	public  $pk = 'rec_id';

    /*------------------------------------------------------ */
    //-- 添加购物车处理
    //-- $goods_id int 商品ID
    //-- $buyNumber int 购买数量
    //-- $buyUnit int 购买单位
    //-- $sku_id int 购买商品的SKU
    //-- $purchaseType int 进货类型，1云仓，2现货
    /*------------------------------------------------------ */
    public function addToCart($goods_id, $buyNumber, $buyUnit, $sku_id,$purchaseType)
    {
        $ChannelGoodsModel = new ChannelGoodsModel();
        $goods = $ChannelGoodsModel->purchaseGoodsInfo($goods_id,$this->userInfo['role_id']);
        if (empty($goods)) return '商品不存在';
        if ($goods['is_on_sale'] == 0 && $purchaseType != 3){
            return '商品未上架暂不能购买.';
        }
        if ($goods['is_spec'] == 1 && $sku_id < 1){
            return '请选择需购买的商品规格.';
        }
        if ($buyUnit < 1){
            $buyUnit = $goods['unit'];
        }
        if ($buyUnit == $goods['unit']){
            $data['convert_unit_val'] = 1;
        }else{
            if (empty($goods['convert_unit'][$buyUnit])){
                return '购买单位规格错误.';
            }
            $data['convert_unit_val'] = $goods['convert_unit'][$buyUnit];
        }

        if ($purchaseType == 3){//提货处理
            $supply_uid = $this->userInfo['user_id'];
        }else{
            $supply_uid = $this->userInfo['role_pid'];
        }
        $data['supply_uid'] = $supply_uid;
        $data['base_unit'] = $goods['unit'];
        $unitLits = (new GoodsUnitModel)->getRows();
        $data['base_unit_name'] = $unitLits[$goods['unit']]['name'];
        $data['goods_unit'] = $buyUnit;
        $data['goods_unit_name'] = $unitLits[$buyUnit]['name'];
        $data['goods_number'] = $buyNumber;
        $data['real_number'] = $buyNumber * $data['convert_unit_val'];
        $data['price_type'] = $goods['price_type'];
        if ($goods['is_spec'] == 1){
            foreach ($goods['sub_goods'] as $sub_goods){
                if ($sub_goods['sku_id'] == $sku_id){
                    $data['sale_price'] = $sub_goods['price'];
                    $data['sku_id'] = $sub_goods['sku_id'];
                    $data['sku_name'] = $sub_goods['sku_name'];
                    $data['sku_val'] = $sub_goods['sku_val'];
                    $goods_sn = $sub_goods['goods_sn'];
                }
            }
        }else{
            $sku_id = 0;
            $data['sale_price'] = $goods['price'];
            $goods_sn = $goods['goods_sn'];
        }
        $data['user_id'] = $this->userInfo['user_id'];
        $data['user_role_id'] = $this->userInfo['role_id'];
        $data['purchase_type'] = $purchaseType;
        $data['goods_id'] = $goods['goods_id'];
        $data['goods_sn'] = $goods_sn;
        $data['goods_name'] = $goods['goods_name'];
        $data['goods_weight'] = $goods['goods_weight'];
        $data['pic'] = $goods['goods_thumb'];
        $data['brand_id'] = $goods['brand_id'];
        $data['cid'] = $goods['cid'];

        $where = [];
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $where[] = ['supply_uid','=',$supply_uid];
        $where[] = ['purchase_type','=',$purchaseType];
        $where[] = ['goods_id','=',$goods_id];
        $where[] = ['sku_id','=',$sku_id];
        $where[] = ['goods_unit','=',$buyUnit];
        $rec_id = $this->where($where)->value('rec_id');
        $time = time();
        if ($rec_id > 0){//已存在处理
            $data['update_time'] = $time;
            $res = $this->where('rec_id', $rec_id)->update($data);
            if ($res < 1) return '更新购物车失败，请尝试重新提交';
        }else{
            $data['update_time'] = $time;
            $data['add_time'] = $time;
            $res = $this->create($data);
            if ($res->rec_id < 1) return '写入购物车失败，请尝试重新提交';
            $rec_id = $res->rec_id;
        }
        return $rec_id;
    }
    /*------------------------------------------------------ */
    //-- 添加购物车处理
    //-- $purchaseType int 进货类型，1云仓，2现货
    //-- $isSelect int 是否只查询选中的
    //-- $rec_id str 查询指定购物车商品
    /*------------------------------------------------------ */
    public function getCartList($purchaseType,$isSelect = 0,$rec_id = 0){
        $where = [];
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $where[] = ['user_role_id','=',$this->userInfo['role_id']];
        $where[] = ['purchase_type','=',$purchaseType];
        if ($purchaseType == 3){//提货供货人为自己
            $where[] = ['supply_uid','=',$this->userInfo['user_id']];
        }else{
            $where[] = ['supply_uid','=',$this->userInfo['role_pid']];
        }
        if ($rec_id > 0){
            $where[] = ['rec_id','=',$rec_id];
        }elseif ($isSelect == 1){
            $where[] = ['is_select','=',1];
        }
        $data = $this->where($where)->select()->toArray();
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 提货商品写入购物车
    //-- $goods array 商品列表
    /*------------------------------------------------------ */
    public function addCartByPickUp(&$goodsList){
        $StockModel = new StockModel();
        $GoodsModel = new GoodsModel();
        $GoodsSkuModel = new GoodsSkuModel();
        foreach ($goodsList as $goods){
            $hash_code = md5($this->userInfo['user_id'].'_1_'.$goods['goods_id'].'_'.$goods['sku_id']);
            $goods_number = $StockModel->where('hash_code',$hash_code)->value('goods_number');
            if ($goods_number < $goods['goods_number']){
                $goods_name = $GoodsModel->where('goods_id',$goods['goods_id'])->value('goods_name');
                if ($goods['sku_id'] > 0){
                    $sku_name = $GoodsSkuModel->where('sku_id',$goods['sku_id'])->value('sku_name');
                    return $goods_name.'-'.$sku_name.'，库存不足，请核实.';
                }
                return $goods_name.'，库存不足，请核实.';
            }
        }
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $where[] = ['purchase_type','=',3];
        $this->where($where)->delete();// 清理购物车旧的提货商品
        foreach ($goodsList as $goods){
            $res = $this->addToCart($goods['goods_id'], $goods['goods_number'], 0, $goods['sku_id'],3);
            if (is_numeric($res) == false){
                return $res;
            }
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 更新购物车数据
    /*------------------------------------------------------ */
    public function updateCart($rec_id, $data)
    {
        if (is_numeric($rec_id)) {
            $where['rec_id'] = $rec_id;
        }
        $where['user_id'] = $this->userInfo['user_id'] * 1;
        return $this->where($where)->update($data);
    }
    /**
     * @param $address 收货地址信息
     * @param $cartList 订购商品
     * @return mixed
     */
    public function _evalShippingFee(&$address,&$cartList){
        $shippingFee = $this->evalShippingFee($address,$cartList);
        $shippingFee = reset($shippingFee);//现在只返回默认快递
        $shippingFee['shipping_fee'] = sprintf("%.2f", $shippingFee['shipping_fee'] * 1);
        return $shippingFee;
    }
    /**计算运费
     * @param array $userAddress 收货地址信息
     * @param array $cartList 购物车商品
     * @return int
     */
    public function evalShippingFee(&$userAddress = [], &$cartList = [])
    {
        if (empty($cartList)) return 0;
        $GoodsModel = new \app\shop\model\GoodsModel();
        $CategoryModel = new \app\shop\model\CategoryModel();
        $Category = $CategoryModel->getRows();
        $sf_id = array();
        $shipping_fee_plus = settings('shipping_fee_plus');//运费累加计算方式
        $sfCartList = [];
        $ShippingTplModel = new \app\shop\model\ShippingTplModel();
        $defSf = [];
        foreach ($cartList as $goods) {
            $goods_sfid = $GoodsModel->where('goods_id',$goods['goods_id'])->value('freight_template');

            if ($goods_sfid == -1) {
                if (empty($defSf) == true){
                    //获取对应平台
                    $defSf = $ShippingTplModel->where('supplyer_id',0)->order('is_default DESC')->value('sf_id');
                }
                $goods_sfid = $defSf;
                //判断平台商品分类运费模板
                $class = $Category[$goods['cid']];
                if ($class['freight_template'] > 0) {
                    $goods_sfid = $class['freight_template'];
                }
                $sf_id[$goods_sfid] = 0;
            }elseif ($goods_sfid > 0) {//设置商品运模板
                $sf_id[$goods_sfid] = 0;
            }

            if (empty($sfCartList[$goods_sfid]) == true){
                $sfCartList[$goods_sfid]['totalGoodsPrice'] = 0;
                $sfCartList[$goods_sfid]['buyGoodsNum'] = 0;
                $sfCartList[$goods_sfid]['buyGoodsWeight'] = 0;
            }
            $sfCartList[$goods_sfid]['totalGoodsPrice'] += $goods['goods_number'] * $goods['sale_price'];
            $sfCartList[$goods_sfid]['buyGoodsNum'] += $goods['goods_number'];
            $sfCartList[$goods_sfid]['buyGoodsWeight'] += $goods['goods_number'] * $goods['goods_weight'];
        }
        if (empty($sf_id)) {
            return 0;
        }
        $ShippingModel = new \app\shop\model\ShippingModel();
        $shippingList = $ShippingModel->getToSTRows();

        $sfList = array();
        //获取最贵的运费模板，根据起步价判断
        foreach ($sf_id as $key => $val) {
            $ssTpl = $ShippingTplModel->find($key);
            if (empty($ssTpl)) continue;
            $sf_info = json_decode($ssTpl['sf_info'],true);
            if ($shipping_fee_plus == 0){//不累加运费
                $setSfId = 0;
            }else{
                $setSfId = $ssTpl['sf_id'];
            }
            foreach ($shippingList as $code => $shipping) {
                if ($shipping['status'] == 0 || empty($sf_info[$code])) continue;
                foreach ($sf_info[$code] as $rowb) {
                    $region_id = empty($rowb['region_id']) ? array() : explode(',', $rowb['region_id']);
                    if (empty($rowb['area']) && empty($rowb['region_id'])) continue;//区域定义为空跳出

                    if (empty($rowb['area']) == false) {//如果是全国
                        if (empty($sfList[$setSfId][$code]) == false){//已有区域定义，跳出
                            continue;
                        }
                    }elseif (in_array($userAddress['city'], $region_id) == false) {
                        //如果不存在区域，跳出
                        continue;
                    }

                    if (empty($sfList[$setSfId][$code]) == true) {//如果已存在相关运费的模板，按初始运费对比，不论计件或计重
                        if ($sfList[$setSfId][$code]['postage'] > $rowb['postage']) {
                            continue;
                        }
                    }
                    $rowb['sf_id'] = $ssTpl['sf_id'];
                    $rowb['consume'] = $ssTpl['consume'];
                    $rowb['sf_name'] = $ssTpl['sf_name'];
                    $rowb['valuation'] = $ssTpl['valuation'];
                    $sfList[$setSfId][$code] = $rowb;
                }
            }
        }
        foreach ($sfList as $sf){
            foreach ($sf as $code => $row) {
                if (empty($n_info[$code]['shipping_fee']) == true){
                    $n_info[$code]['shipping_fee'] = 0;
                }

                $n_info[$code]['name'] = $shippingList[$code]['shipping_name'];
                $n_info[$code]['code'] = $code;
                $n_info[$code]['sf_id'] = $row['sf_id'];
                $sf_id = $row['sf_id'];

                if ($row['consume'] > 0 && $sfCartList[$sf_id]['totalGoodsPrice'] >= $row['consume']) {
                    $n_info[$code]['shipping_fee'] += 0;
                } else {
                    if ($row['valuation'] == 1){//根据商品数量计算
                        if ($sfCartList[$sf_id]['buyGoodsNum'] > $row['start']) {
                            $d_num = $sfCartList[$sf_id]['buyGoodsNum'] - $row['start'];
                            $d_num = ceil($d_num / $row['plus']);
                            $n_info[$code]['shipping_fee'] += $row['postage'] + ($d_num * $row['postageplus']);
                        } else {
                            $n_info[$code]['shipping_fee'] += $row['postage'];
                        }
                    }else{//根据商品重量计算
                        $buyGoodsWeight = $sfCartList[$sf_id]['buyGoodsWeight'] / 1000;
                        if ($buyGoodsWeight > $row['start']) {
                            $d_num = $buyGoodsWeight - $row['start'];
                            $d_num = ceil($d_num / $row['plus']);
                            $n_info[$code]['shipping_fee'] += $row['postage'] + ($d_num * $row['postageplus']);
                        } else {
                            $n_info[$code]['shipping_fee'] += $row['postage'];
                        }
                    }
                }
            }
        }
        return $n_info;
    }
}
