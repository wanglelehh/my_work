<?php
namespace app\integral\model;
use app\BaseModel;
use think\facade\Cache;
use app\shop\model\GoodsModel;

//*------------------------------------------------------ */
//-- 积分商品
/*------------------------------------------------------ */
class IntegralGoodsModel extends BaseModel
{

    protected $table = 'integral_goods';
    public $pk = 'ig_id';
    protected $mkey = 'integral_goods_mkey';
    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache($fg_id = 0)
    {
        Cache::rm($this->mkey . $fg_id);
    }
    /*------------------------------------------------------ */
    //-- 获取积分商品信息
    //-- $ig_id int 积分商品id
    //-- $hideSettle bool 是否隐藏供货价
    /*------------------------------------------------------ */
    public function info($ig_id,$hideSettle = true)
    {
        $igInfo = Cache::get($this->mkey . $ig_id);
        if (empty($igInfo)) {
            $igInfo = $this->where('ig_id', $ig_id)->find();
            if (empty($igInfo) == true) return array();
            $igInfo = $igInfo->toArray();
            Cache::set($this->mkey . $ig_id, $igInfo, 30);
        }
        $IntegralGoodsListModel = new IntegralGoodsListModel();
        $GoodsModel = new GoodsModel();
        $goods = $GoodsModel->info($igInfo['goods_id'],$hideSettle);
        $goods['imgsList'] = $GoodsModel->getImgsList($goods['goods_id']);//获取图片
        if ($goods['is_spec'] == 1) {//多规格处理
            $igInfo['goods'] = $IntegralGoodsListModel->where('ig_id', $ig_id)->select()->toArray();
            $this->getGoodsSku($goods, $igInfo);
            $goods['skuImgs'] = $GoodsModel->getImgsList($goods['goods_id'], true, true);//获取sku图片
        } else {
            $igGoods = $IntegralGoodsListModel->where('ig_id', $ig_id)->find()->toArray();
            $BuyMaxNum = $igGoods['ig_number'] - $igGoods['sale_num'];
            if ($BuyMaxNum > $goods['goods_number']){
                $BuyMaxNum = $goods['goods_number'];
            }
            $goods['goods_number'] = $BuyMaxNum;
            $goods['BuyMaxNum'] = $BuyMaxNum;
            $goods['sale_num'] = $igGoods['sale_num'];
            $goods['integral'] = $igGoods['integral'];

        }
        $igInfo['goods'] = $goods;
        $time = time();
        $igInfo['is_on_sale'] = $goods['is_on_sale'];//判断是否可以进行兑换
        unset($goods);
        if ($igInfo['start_date'] > $time) {
            $igInfo['is_on_sale'] = 0;//未开始
        } elseif ($igInfo['end_date'] < $time) {
            $igInfo['is_on_sale'] = 9;//已结束
        }
        return $igInfo;
    }

    /*------------------------------------------------------ */
    //-- 获取商品规格及子商品信息
    //-- $goods array 商品信息
    //-- $igInfo array 积分相关
    /*------------------------------------------------------ */
    public function getGoodsSku(&$goods, &$igInfo)
    {
        if ($goods['is_spec'] == 0) return $goods;

        $igGoods = array_column($igInfo['goods'], null, 'sku_id');//重置数组的键
        $skuTotal = [];
        $goods['skuValList'] = [];
        foreach ($goods['sub_goods'] as  $key => $row) {
            if (empty($igGoods[$row['sku_id']]) == false) {
                $goods_number = $igGoods[$row['sku_id']]['ig_number'];
                $row['sale_num'] = $igGoods[$row['sku_id']]['sale_num'];
                $row['integral'] = $igGoods[$row['sku_id']]['integral'];
                $BuyMaxNum = $goods_number - $row['sale_num'];//活动库存减去已销售库存，得出现可售数量
                if ($BuyMaxNum > $row['goods_number']){
                    $BuyMaxNum = $row['goods_number'];
                }
                $row['goods_number'] = $BuyMaxNum;
                $row['BuyMaxNum'] = $BuyMaxNum;
                $sub_goods[$key] = $row;
                $sku_val = explode(':', $row['sku_val']);
                $skuLink = [];
                foreach ($sku_val as $val){
                    $skuLink[] = $val;
                    $sku_key = join('_',$skuLink);
                    if (empty($skuTotal[$sku_key])){
                        $skuTotal[$sku_key]['goods_number'] = 0;
                    }
                    $skuTotal[$sku_key]['goods_number'] += $BuyMaxNum;
                    if ($val != $sku_key){
                        $skuTotal[$val]['goods_number'] += $BuyMaxNum;
                    }
                }
                $goods['skuValList'][] = $row['sku_val'];
            }

        }
        $goods['skuTotal'] = $skuTotal;
        $goods['sub_goods'] = $sub_goods;
        return true;
    }
}
