<?php

namespace app\channel\model;
use app\BaseModel;
use app\member\model\RoleModel;
use think\facade\Cache;
use app\shop\model\GoodsModel;
use app\shop\model\GoodsSkuModel;
use app\shop\model\SkuCustomModel;

//*------------------------------------------------------ */
//-- 代理商品
/*------------------------------------------------------ */
class ChannelGoodsModel extends BaseModel
{
    protected $table = 'channel_goods';
    public  $pk = 'id';
    protected $mkey = 'channel_goods_mkey_';
    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache($goods_id = 0)
    {
        Cache::rm($this->mkey . $goods_id);
        Cache::rm($this->mkey .'prices_' . $goods_id);
        Cache::rm($this->mkey .'byshop_' . $goods_id);
    }
    /*------------------------------------------------------ */
    //-- 验证商品数据
    /*------------------------------------------------------ */
    public function checkData($input)
    {
        if ($input['is_channel'] == 0){
            return true;
        }
        $data['unit'] = intval($input['channel_unit']);
        $data['convert_unit_id'] = $input['channel_convert_unit_id'];
        $data['convert_unit_val'] = $input['channel_convert_unit_val'];
        $data['price_type'] = intval($input['channel_price_type']);
        $data['pricing_type'] = intval($input['channel_pricing_type']);
        $data['isputaway'] = intval($input['isputaway']);
        $prices = $input['channel_prices'];
        if (empty($data['unit'])){
            return '请选择默认计量单位.';
        }
        $data['convert_unit'] = [];
        $convert_unit = 0;
        if (empty($data['convert_unit_id']) == false){
            foreach ($data['convert_unit_id'] as $key=>$cuid){
                if (empty($data['convert_unit_val'][$key])){
                    return '请填写换算单位数量.';
                }
                if ($convert_unit > $data['convert_unit_val'][$key]){
                    return '请按从小到大顺序添加换算单位数量.';
                }
                $convert_unit = $data['convert_unit_val'][$key];
                $data['convert_unit'][$cuid] = $convert_unit;
            }
        }
        unset($data['convert_unit_id'],$data['convert_unit_val']);
        $data['convert_unit'] = json_encode($data['convert_unit']);

        $roleList = (new RoleModel)->getRows(1);
        $pl_prices = [];
        $now_price = 0;
        foreach ($roleList as $role){
            if (empty($prices[$role['role_id']])){
                return '请填写【'.$role['role_name'].'】的层级定价.';
            }
            if ($now_price > $prices[$role['role_id']]){
                return '【'.$role['role_name'].'】的层级定价，不能低于上级定价.';
            }
            $now_price = $prices[$role['role_id']];
            $arr = [];
            $arr['price_type'] = $data['price_type'];
            $arr['role_id'] = $role['role_id'];
            $arr['price'] = $now_price;
            $arr['sku_id'] = 0;
            $pl_prices[] = $arr;
        }
        unset($data['price']);
        $this->pl_prices = $pl_prices;
        $this->goodsData = $data;
        return true;
    }
    /*------------------------------------------------------ */
    //-- 写入数据
    /*------------------------------------------------------ */
    public function saveData($goods_id,$is_channel = 0)
    {
        $id = $this->where('goods_id',$goods_id)->value('id');
        if ($is_channel == 0){
            $goodsData['isputaway'] = 0;
            if ($id > 0){
                 $this->where('id',$id)->update($goodsData);
            }
            return true;
        }

        $goodsData = $this->goodsData;
        $goodsData['update_time'] = time();
        if ($id > 0){
            $res = $this->where('id',$id)->update($goodsData);
        }else{
            $goodsData['add_time'] = time();
            $goodsData['goods_id'] = $goods_id;
            $res = $this->create($goodsData);
        }
        if ($res < 1) {
            return '写入代理商品数据失败，请重试.'.json_encode($goodsData);
        }
        $update_time = time();
        $ChannelGoodsPricesModel = new ChannelGoodsPricesModel();
        foreach ($this->pl_prices as $price){
            $where = [];
            $where['price_type'] = $price['price_type'];
            $where['role_id'] = $price['role_id'];
            $where['goods_id'] = $goods_id;
            $where['sku_id'] = $price['sku_id'];
            $id = $ChannelGoodsPricesModel->where($where)->value('id');
            if ($id > 0){
                $price['update_time'] = $update_time;
                $res = $ChannelGoodsPricesModel->where('id',$id)->update($price);
            }else{
                $price['update_time'] = $update_time;
                $price['goods_id'] = $goods_id;
                $res = $ChannelGoodsPricesModel::create($price);
            }
            if ($res < 1) {
                return '操作失败:写入层级定价失败，请重试.';
            }
        }
        $this->cleanMemcache($goods_id);
        return true;
    }
    /*------------------------------------------------------ */
    //-- 获取代理商品信息
    //-- $goods_id int 商品id
    /*------------------------------------------------------ */
    public function info($goods_id)
    {
        $goods = Cache::get($this->mkey . $goods_id);
        if (empty($goods)) {
            $goods = $this->where('goods_id', $goods_id)->find();
            if (empty($goods) == true) return [];
            $goods = $goods->toArray();
            Cache::set($this->mkey . $goods_id, $goods, 600);
        }

        $goods['is_on_sale'] = 0;
        if ($goods['isputaway'] == 1){
            $goods['is_on_sale'] = 1;
        }elseif ($goods['isputaway'] == 2){
            $time = time();
            //自动上下架判断
            if ($goods['added_time'] <= $time && $goods['shelf_time'] >= $time) {
                $goods['is_on_sale'] = 1;
            } elseif ($goods['shelf_time'] < $time) {
                $goods['isputaway'] = 0;
            }
        }
        $goods['convert_unit'] = json_decode($goods['convert_unit'],true);
        return $goods;
    }
    /*------------------------------------------------------ */
    //-- 获取代理商品出货信息
    //-- $goods_id int 商品id
    //-- $proxy_id int 当前代理的层级ID
    /*------------------------------------------------------ */
    public function purchaseGoodsInfo($goods_id,$proxy_id)
    {
        $channelGoods = $this->info($goods_id);//代理商品数据

        if (empty($channelGoods)){
            return [];
        }
        $goods = $this->getShopGoods($goods_id);//商城的商品数据

        $goods['price_type'] = $channelGoods['price_type'];
        $goods['pricing_type'] = $channelGoods['pricing_type'];
        $goods['is_on_sale'] = $channelGoods['is_on_sale'];
        $goods['unit'] = $channelGoods['unit'];
        $goods['convert_unit'] = $channelGoods['convert_unit'];
        $goods['unitLits'] = (new GoodsUnitModel)->getRows();

        $prices = $this->getPrcies($goods,$proxy_id);//出货价
        if ($goods['is_spec'] == 0) {
            $goods['price'] = $prices[0]['price'];
        }else{
            if ($channelGoods['pricing_type'] == 0){ //固定价
                foreach ($goods['sub_goods'] as $key=>$sub_goods){
                    $sub_goods['price'] = $prices[$sub_goods['sku_id']]['price'];
                    $goods['sub_goods'][$key] = $sub_goods;
                }
            }else{
                foreach ($goods['sub_goods'] as $key=>$sub_goods){
                    $sub_goods['price'] = $prices[$sub_goods['sku_id']]['price'];
                    $goods['sub_goods'][$key] = $sub_goods;
                }
            }
        }

        return $goods;
    }
    /*------------------------------------------------------ */
    //-- 查询商品出货价
    //-- $goods array 商品信息
    //-- role_id intval 代理层级ID
    //-- $showMini bool 是否返回最小价格，否则返回所有
    /*------------------------------------------------------ */
    public function getPrcies(&$goods,$role_id = 0,$showMini = false)
    {
        $mkey = $this->mkey.'prices_' . $goods['goods_id'].'_'.$role_id;
        $prices = Cache::get($mkey);
        if (empty($prices)){
            $ChannelGoodsPricesModel = new ChannelGoodsPricesModel();
            $where = [];
            $where[] = ['goods_id','=',$goods['goods_id']];
            $where[] = ['role_id','=',$role_id];
            $where[] = ['price_type','=',$goods['price_type']];
            $priceList = $ChannelGoodsPricesModel->where('goods_id',$goods['goods_id'])->order('price ASC')->select()->toArray();
            //如果为折扣定价，多规格商品时获取零售价
            if ($goods['is_spec'] == 1){
                $skuPrice = (new GoodsSkuModel)->where('goods_id', $goods['goods_id'])->column('shop_price','sku_id');
            }
            $prices = [];
            foreach ($priceList as $key=>$price){
                if ($goods['price_type'] == $price['price_type'] && $role_id == $price['role_id']){
                    if ($goods['pricing_type'] == 1){//折扣定价
                        if ($goods['is_spec'] == 1){//多规格
                            if ($goods['price_type'] == 1){ //独立出价
                                $price['price'] = sprintf("%.2f",$skuPrice[$price['sku_id']] / 100 * $price['price']);
                                $prices[$price['sku_id']] = $price;
                            }else{//统一出价
                                $price_pre = $price['price'];
                                foreach ($skuPrice as $sku_id=>$sku_price){
                                    $price['price'] = sprintf("%.2f",$sku_price / 100 * $price_pre);
                                    $prices[$sku_id] = $price;
                                }
                            }
                        }else{
                            $price['price'] = sprintf("%.2f",$goods['shop_price'] / 100 * $price['price']);
                            $prices[$price['sku_id']] = $price;
                        }
                    }else{//固定售价
                        $prices[$price['sku_id']] = $price;
                        if ($goods['is_spec'] == 1 ) {//多规格
                            if ($goods['price_type'] == 0){ //统一出价
                                foreach ($skuPrice as $sku_id=>$sku_price){
                                    $prices[$sku_id] = $price;
                                }
                            }
                        }
                    }
                }
            }
            Cache::set($mkey, $prices, 600);
        }
        if ($showMini == true){
            return reset($prices);
        }
        return $prices;
    }

    /*------------------------------------------------------ */
    //-- 获取商品规格及子商品信息
    //-- $goods_id int 商品ID
    /*------------------------------------------------------ */
    public function getShopGoods($goods_id)
    {
        $mkey = $this->mkey.'byshop_' . $goods_id;
       //$shopGoods = Cache::get($mkey);
        if (empty($shopGoods) == true){
            $GoodsModel = new GoodsModel();
            $shopGoods = $GoodsModel->where('goods_id',$goods_id)->field('brand_id,cid,brand_id,goods_id,is_spec,goods_sn,goods_name,shop_price,goods_thumb,goods_img,goods_number,goods_weight,m_goods_desc')->find();
            if (empty($shopGoods)){
                return [];
            }
            $shopGoods = $shopGoods->toArray();
            $web_path = config('config.host_path');
            $shopGoods['m_goods_desc'] = preg_replace('/<img src=\"\/upload/', '<img src="' .$web_path.'/upload',$shopGoods['m_goods_desc']);
            $shopGoods['m_goods_desc'] = preg_replace('/<img/', '<img style="width:100%;height:auto;"',$shopGoods['m_goods_desc']);
            if ($shopGoods['is_spec'] == 1){
                $where = [];
                $where[] = ['goods_id','=',$goods_id];
                $where[] = ['is_sale','=',1];
                $gsrows = (new GoodsSkuModel)->where($where)->field('sku_id,sku,sku_val,sku_name,goods_id,goods_sn,goods_number')->order('sku_val ASC')->select()->toArray();
                $skuValArr = [];
                $skuTotal = [];
                $goods['skuValList'] = [];
                foreach ($gsrows as $row) {
                    $sku = $row['sku'];
                    $skuValArr[] = $row['sku_val'];
                    $shopGoods['sub_goods'][] = $row;

                    $sku_val = explode(':',$row['sku_val']);
                    $skuLink = [];
                    foreach ($sku_val as $val){
                        $skuLink[] = $val;
                        $sku_key = join('_',$skuLink);
                        if (empty($skuTotal[$sku_key])){
                            $skuTotal[$sku_key]['goods_number'] = 0;
                        }
                        $skuTotal[$sku_key]['goods_number'] += $row['goods_number'];
                        if ($val != $sku_key){
                            if (empty($skuTotal[$val])){
                                $skuTotal[$val]['goods_number'] = 0;
                            }
                            $skuTotal[$val]['goods_number'] += $row['goods_number'];
                        }
                    }
                    $shopGoods['skuValList'][] = $row['sku_val'];
                }
                $shopGoods['skuTotal'] = $skuTotal;
                $skuArr = explode(':', $sku);
                $SkuCustomModel = new SkuCustomModel();
                $where = [];
                $where[] = ['id', 'IN', $skuArr];
                $shopGoods['specList'] = $SkuCustomModel->field('id,val as name,speid as pid,img_type')->where($where)->order('sort_order DESC,id ASC')->select()->toArray();
                $where = [];
                $skuValArr = join(':',$skuValArr);
                $skuValArr = explode(':', $skuValArr);
                $where[] = ['id', 'IN', $skuValArr];
                $specChildList = $SkuCustomModel->field('id,val as name,speid as pid')->where($where)->order('sort_order DESC,id ASC')->select()->toArray();
                $shopGoods['specChildList'] = [];
                $index = 0;
                foreach ($shopGoods['specList'] as $spec){
                    foreach ($specChildList as $specChild){
                        if ($spec['id'] == $specChild['pid']){
                            $specChild['index'] = $index;
                            $shopGoods['specChildList'][] = $specChild;
                            $index++;
                        }
                    }
                }
                $shopGoods['imgSkuList'] = $GoodsModel->getImgsList($goods_id,true);
            }
            $shopGoods['imgList'] = $GoodsModel->getImgsList($goods_id);
            Cache::set($mkey,$shopGoods,60);
        }

        return $shopGoods;
    }


}
