<?php

namespace app\fightgroup\model;

use app\BaseModel;
use think\facade\Cache;

use app\shop\model\GoodsModel;
use app\shop\model\SkuCustomModel;

//*------------------------------------------------------ */
//-- 拼团
/*------------------------------------------------------ */

class FightGroupModel extends BaseModel
{
    protected $table = 'fightgroup';
    public $pk = 'fg_id';
    protected $mkey = 'fightgroup_mkey';
    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache($fg_id = 0)
    {
        if ($fg_id > 0) {
            Cache::rm($this->mkey . $fg_id);
        }
    }
    /*------------------------------------------------------ */
    //-- 获取拼团信息
    //-- $fg_id int 拼团id
    //-- $hideSettle bool 是否隐藏供货价，默认隐藏
    /*------------------------------------------------------ */
    public function info($fg_id,$hideSettle = true)
    {
        $fgInfo = Cache::get($this->mkey . $fg_id);
        if (empty($fgInfo)) {
            $fgInfo = $this->where('fg_id', $fg_id)->find();
            if (empty($fgInfo) == true) return array();
            $fgInfo = $fgInfo->toArray();
            $fgInfo['exp_price'] = explode('.', $fgInfo['show_price']);
            Cache::set($this->mkey . $fg_id, $fgInfo, 30);
        }

        $goods = (new GoodsModel)->info($fgInfo['goods_id'],$hideSettle);
        if ($goods['is_spec'] == 1) {//多规格处理
            $fgInfo['goods'] = (new FightGoodsModel)->where('fg_id', $fg_id)->select()->toArray();
            $this->getGoodsSku($goods, $fgInfo);
        } else {
            $goods['shop_price'] = $goods['sale_price'];
            $goods['exp_sale_price'] = explode('.', $goods['sale_price']);
            $fgGoods = (new FightGoodsModel)->where('fg_id', $fg_id)->find()->toArray();
            $goods['fg_number'] = $fgGoods['fg_number'];
            $goods['sale_num'] = $fgGoods['sale_num'];
            $goods['fg_sale_price'] = $fgGoods['fg_price'];
            $goods['fg_sale_number'] = $fgGoods['fg_number'] - $fgGoods['sale_num'];
            $goods['exp_price'] = explode('.', $fgGoods['fg_price']);
        }
        $fgInfo['goods'] = $goods;

        $time = time();
        $fgInfo['is_on_sale'] = 1;//判断是否进行拼团销售中
        $fgInfo['fg_status'] = 1;
        $fgInfo['_end_date'] = date('Y-m-d H:i:s',$fgInfo['end_date']);
        if ($fgInfo['start_date'] > $time) {
            $fgInfo['is_on_sale'] = 0;//未开始
            $fgInfo['fg_status'] = 0;
            $fgInfo['downDate'] = date('Y-m-d H:i:s',$fgInfo['start_date']);
        } elseif ($fgInfo['end_date'] < $time) {
            $fgInfo['is_on_sale'] = 9;//已结束
            $fgInfo['fg_status'] = 9;
            $fgInfo['downDate'] = $fgInfo['_end_date'];
        }else{
            $fgInfo['downDate'] = $fgInfo['_end_date'];
        }

        if ($fgInfo['is_on_sale'] == 1) {
            $goods_num = 0;
            if ($fgInfo['goods']['is_spec'] == 1) {//多规格处理
                foreach ($fgInfo['goods'] as $goods) {
                    $goods_num += $goods['fg_number'];
                }
            } else {
                $goods_num += $fgInfo['goods']['fg_number'];
            }
            if ($goods_num < $fgInfo['success_num']) {//不能发起拼团，可购买
                $fgInfo['is_on_sale'] = 2;
            }
        }

        return $fgInfo;
    }

    /*------------------------------------------------------ */
    //-- 获取商品规格及子商品信息
    //-- $goods array 商品信息
    //-- $fgInfo array 拼团相关
    /*------------------------------------------------------ */
    public function getGoodsSku(&$goods, &$fgInfo)
    {
        if ($goods['is_spec'] == 0) return $goods;
        $sub_goods = $goods['sub_goods'];
        $fgGoods = array_column($fgInfo['goods'], null, 'sku_id');//重置数组的键
        $goods['fg_number'] = 0;
        $skuTotal = [];
        foreach ($sub_goods as $key => $row) {
            $skuval[] = $row['sku_val'];
            $row['is_group'] = 0;
            $row['fg_number'] = 0;
            $row['BuyMaxNum'] = $row['goods_number'];
            $goods['goods_number'] += $row['goods_number'];
            $BuyMaxNum = 0;
            if (empty($fgGoods[$row['sku_id']]) == false) {//规格活动存在
                $row['is_group'] = 1;
                $row['fg_number'] = $fgGoods[$row['sku_id']]['fg_number'];
                $goods['fg_number'] += $row['fg_number'];
                $row['fg_sale_price'] = $fgGoods[$row['sku_id']]['fg_price'];
                $row['sale_num'] = $fgGoods[$row['sku_id']]['sale_num'];
                $row['exp_price'] = explode('.', $fgGoods[$row['sku_id']]['fg_price']);
                $BuyMaxNum = $row['fg_number'] - $row['sale_num'];//拼团活动库存减去已销售库存，得出现可售拼团数量
                if ($BuyMaxNum > $row['goods_number']){//
                    $BuyMaxNum = $row['goods_number'];
                }
                $row['fg_goods_number'] = $BuyMaxNum;

            }

            $sku_val = explode(':', $row['sku_val']);
            $skuLink = [];
            foreach ($sku_val as $val){
                $skuLink[] = $val;
                $sku_key = join('_',$skuLink);
                if (empty($skuTotal[$sku_key])){
                    $skuTotal[$sku_key]['goods_number'] = 0;
                    $skuTotal[$sku_key]['show'] = 0;
                }
                if ($row['fg_goods_number'] > 0 && $row['fg_sale_price'] > 0){//设置售价为零时，不显示相关规格
                    $skuTotal[$sku_key]['show'] = 1;//显示
                }
                $skuTotal[$sku_key]['goods_number'] += $BuyMaxNum;
                if ($val != $sku_key){
                    if (empty($skuTotal[$val])){
                        $skuTotal[$val]['goods_number'] = 0;
                    }
                    $skuTotal[$val]['goods_number'] += $BuyMaxNum;
                    $skuTotal[$val]['show'] = $skuTotal[$val]['goods_number'] > 0 ? 1 : 0;//显示
                }
            }
            $sub_goods[$key] = $row;
        }

        $goods['fgSkuTotal'] = $skuTotal;
        $goods['sub_goods'] = $sub_goods;
        $goods['exp_sale_price'] = explode('.', $goods['sale_price']);
        unset($sub_goods);
        return true;
    }
    /*------------------------------------------------------ */
    //-- 获取拼团中列表
    //-- $fg_id int 拼团id
    /*------------------------------------------------------ */
    public function getFGing($fg_id)
    {
        $FightGroupListModel = new FightGroupListModel();
        $where[] = ['fg_id','=',$fg_id];
        $where[] = ['status','<',3];
        $gids = $FightGroupListModel->where($where)->limit(15)->column('gid');
        $fgList = [];
        foreach ($gids as $gid){
            $fginfo = $FightGroupListModel->info($gid);
            if ($fginfo['status'] == 1){
                $fgList[] = $fginfo;
            }
        }
        return $fgList;
    }
}
