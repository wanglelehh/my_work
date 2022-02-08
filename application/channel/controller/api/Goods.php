<?php

namespace app\channel\controller\api;
use app\channel\ApiController;

use app\channel\model\ChannelGoodsModel;
use app\channel\model\GoodsUnitModel;
use app\channel\model\StockModel;
use app\shop\model\GoodsModel;
/*------------------------------------------------------ */
//-- 代理会员相关API
/*------------------------------------------------------ */

class Goods extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new ChannelGoodsModel();
    }
    /*------------------------------------------------------ */
    //-- 获取代理商品列表
    /*------------------------------------------------------ */
    public function getList()
    {
        $where[] = ['cg.isputaway','=',1];
        $cateId = input('cateId',0,'intval');
        $GoodsModel = new GoodsModel();
        if ($cateId > 0){
            $classList = $GoodsModel->getClassList();
            $where[] = ['sg.cid', 'in', $classList[$cateId]['children']];
        }
        $keyword =  input('keyword','','trim');
        if (empty($keyword) == false){
            $where[] =['sg.goods_name|sg.keywords','like','%'.$keyword.'%'];
        }
        $purchaseType = input('purchaseType',0,'intval');

        $viewObj = $this->Model->alias('cg')->join("shop_goods sg", 'cg.goods_id=sg.goods_id', 'left')->where($where)->field('cg.id,cg.goods_id,cg.unit,cg.convert_unit,cg.price_type,cg.pricing_type,sg.goods_number,sg.goods_name,sg.shop_price,sg.is_spec,sg.short_name,sg.goods_thumb,sg.goods_img')->order('cg.goods_id DESC');

        $data = $this->getPageList($this->Model, $viewObj,'',6);
        foreach ($data['list'] as $key=>$goods){
            if ($purchaseType == 4){
                $goods['price'] = $goods['shop_price'];
            }else{
                $gprice = $this->Model->getPrcies($goods,$this->userInfo['role_id'],true);
                $goods['price'] = $gprice['price'];
            }
            $goods['convert_unit'] = json_decode($goods['convert_unit'],true);
            $data['list'][$key] = $goods;
        }
        $p = input('p',0,'intval');
        if ($p == 1){
            $data['unitLits'] = (new GoodsUnitModel)->getRows();
        }
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取商品信息
    /*------------------------------------------------------ */
    public function getGoodsInfo()
    {
        $goods_id = input('goods_id',0,'intval');
        $purchaseType = input('purchaseType',0,'intval');
        if ($goods_id < 1){
            return $this->error('请传递商品ID.');
        }
        $data = $this->Model->purchaseGoodsInfo($goods_id,$this->userInfo['role_id']);
        if (empty($data)){
            return $this->error('没有找到相关商品.');
        }
        if ($this->userInfo['role_pid'] > 0 && $purchaseType != 4){//非平台供货，获取上级代理库存
            $where[] = ['user_id','=',$this->userInfo['role_pid']];
            $where[] = ['goods_id','=',$goods_id];
            $where[] = ['purchase_type','=',$purchaseType];
            $stocks = (new StockModel)->where($where)->select()->toArray();
            if ($data['is_spec'] == 0){
                if (empty($stocks) == false){
                    $data['goods_number'] = $stocks[0]['goods_number'];
                }else{
                    $data['goods_number'] = 0;
                }
            }else{
                $goodsStock = [];
                foreach ($stocks as $stock){
                    $goodsStock[$stock['goods_id'].'_'.$stock['sku_id']] = $stock['goods_number'];
                }
                foreach ($data['sub_goods'] as $key=>$sub_good){
                    if (empty($goodsStock[$sub_good['goods_id'].'_'.$sub_good['sku_id']]) == false){
                        $data['sub_goods'][$key]['goods_number'] = $goodsStock[$sub_good['goods_id'].'_'.$sub_good['sku_id']];
                    }else{
                        $data['sub_goods'][$key]['goods_number'] = 0;
                    }
                }
            }
        }
        return $this->success($data);
    }
}

