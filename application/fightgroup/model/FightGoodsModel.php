<?php
namespace app\fightgroup\model;
use app\BaseModel;
use app\shop\model\GoodsModel;
use app\shop\model\GoodsSkuModel;
//*------------------------------------------------------ */
//-- 拼团商品列表
/*------------------------------------------------------ */
class FightGoodsModel extends BaseModel
{
	protected $table = 'fightgroup_goods';
    public  $pk = 'gid';
    /*------------------------------------------------------ */
    //-- 拼团商品库存&销量处理
    /*------------------------------------------------------ */
    public function evalGoodsStore($fg_id,$goods_id = 0,$sku_id = 0,$number = 0,$type = 'addOrder')
    {
        if ($type == 'cancel') {
            $upData['sale_num'] = ['DEC', $number];
        } else {
            $upData['sale_num'] = ['INC', $number];
        }
        $upData['update_time'] = time();
        $where[] = ['fg_id','=',$fg_id];
        $where[] = ['goods_id','=',$goods_id];
        $where[] = ['sku_id','=',$sku_id * 1];
        $res = $this->where($where)->update($upData);
        if ($res < 1) return false;
        $upData = [];
        $GoodsModel = new GoodsModel();
        if ($sku_id > 0){
            $GoodsSkuModel = new GoodsSkuModel();
            if ($type == 'cancel') {
                $subData['goods_number'] = ['INC', $number];
                $upData['sale_num'] = ['DEC', $number];
            } else {
                $subData['goods_number'] = ['DEC', $number];
                $upData['sale_num'] = ['INC', $number];
            }
            $res = $GoodsSkuModel->where('sku_id',$sku_id)->update($subData);
            if ($res < 1) return false;
            $res = $GoodsModel->where('goods_id', $goods_id)->update($upData);
            if ($res < 1) return false;
        }else{
            if ($type == 'cancel') {
                $subData['goods_number'] = ['INC', $number];
                $upData['sale_num'] = ['DEC', $number];
            } else {
                $subData['goods_number'] = ['DEC', $number];
                $upData['sale_num'] = ['INC', $number];
            }
            $res = $GoodsModel->where('goods_id', $goods_id)->update($upData);
            if ($res < 1) return false;
        }

        $GoodsModel->cleanMemcache($goods_id);
        return true;
    }

    /*------------------------------------------------------ */
    //-- 获取购物车数据
    /*------------------------------------------------------ */
    public function getCartList()
    {
        $fg_id = input('fg_id', 0, 'intval');
        $number = input('number', 1, 'intval');
        $sku_id = input('sku_id', 0, 'trim');
        $fgInfo = (new FightGroupModel)->info($fg_id);
        if (empty($fgInfo)) return '拼团不存在.';
        $goods = $fgInfo['goods'];
        unset($fgInfo['goods']);
        $orderTotal = 0;
        if ($goods['is_spec'] == 1) {//多规格处理
            if (empty($sku_id)) {
                return '传参错误-1.';
            }
            if (empty($goods['sub_goods'][$sku_id])) {
                return '传参错误-2.';
            }
            $skuGoods = $goods['sub_goods'][$sku_id];
            $skuGoods['goods_number'] = $number;
            $skuGoods['sale_price'] = $skuGoods['fg_sale_price'];
            $orderTotal = $skuGoods['sale_price'] * $number;
            $return['goodsList'][$goods['goods_id'].'_'.$sku_id] = $skuGoods;
        } else {
            $goods['goods_number'] = $number;
            $goods['sale_price'] = $goods['fg_sale_price'];
            $orderTotal = $goods['sale_price'] * $number;
            $return['goodsList'][$goods['goods_id'].'_'.$sku_id] = $goods;
        }
        $return['buyGoodsNum'] = $number;//计算运费时调用
        $return['orderTotal'] = $orderTotal;//计算优惠券是否可使用时调用
        return $return;
    }

}
