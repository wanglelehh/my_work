<?php

namespace app\channel\controller\api;
use app\channel\ApiController;

use app\channel\model\StockModel;
use app\channel\model\StockDetailModel;
use app\shop\model\GoodsModel;
use app\shop\model\GoodsSkuModel;
/*------------------------------------------------------ */
//-- 库存相关查询API
/*------------------------------------------------------ */

class Stock extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new StockModel();
    }
    /*------------------------------------------------------ */
    //-- 获取我的库存列表
    /*------------------------------------------------------ */
    public function getList(){
        $state = input('state','cloud','trim');
        $where[] = ['s.user_id','=',$this->userInfo['user_id']];
        if ($state == 'cloud'){
            $where[] = ['s.purchase_type','=',1];
        }else{
            $where[] = ['s.purchase_type','=',2];
        }
        $GoodsModel = new GoodsModel();
        $GoodsSkuModel = new GoodsSkuModel();
        $cateId = input('cateId',0,'intval');
        if ($cateId > 0){
            $classList = $GoodsModel->getClassList();
            $where[] = ['sg.cid', 'in', $classList[$cateId]['children']];
        }
        $keyword =  input('keyword','','trim');
        if (empty($keyword) == false){
            $where[] =['sg.goods_name|sg.keywords','like','%'.$keyword.'%'];
        }
        $rows = $this->Model->alias('s')->join("shop_goods sg", 's.goods_id=sg.goods_id', 'left')->where($where)->field('s.*,sg.goods_name,sg.goods_thumb')->select();
        $goodsList = [];

        foreach ($rows as $row){
            if (empty($goodsList[$row['goods_id']])){
                $goodsList[$row['goods_id']] = $GoodsModel->where('goods_id',$row['goods_id'])->field('goods_id,goods_name,goods_thumb')->find()->toArray();
                $goodsList[$row['goods_id']]['goods_number'] = 0;
                $goodsList[$row['goods_id']]['out_number'] = 0;
                $goodsList[$row['goods_id']]['pickup_number'] = 0;//进货数量
                $goodsList[$row['goods_id']]['skuList'] = [];
            }
            if ($row['sku_id'] == 0){
                $goodsList[$row['goods_id']]['hash_code'] = $row['hash_code'];
                $goodsList[$row['goods_id']]['goods_number'] = $row['goods_number'];
                $goodsList[$row['goods_id']]['out_number'] = $row['out_number'];
            }else{
                $sku = [];
                $sku['sku_name'] = $GoodsSkuModel->where('sku_id',$row['sku_id'])->value('sku_name');
                $sku['sku_id'] = $row['sku_id'];
                $sku['goods_number'] = $row['goods_number'];
                $sku['out_number'] = $row['out_number'];
                $sku['hash_code'] = $row['hash_code'];
                $goodsList[$row['goods_id']]['goods_number'] += $row['goods_number'];
                $goodsList[$row['goods_id']]['out_number'] += $row['out_number'];
                $goodsList[$row['goods_id']]['skuList'][] = $sku;
            }
        }
        $data['list'] = [];
        foreach ($goodsList as $goods){
            $data['list'][] = $goods;
        }
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取我的库存列表
    /*------------------------------------------------------ */
    public function getDetail(){
        $state = input('state','enter','trim');
        $hash = input('hash','','trim');
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $where[] = ['hash_code','=',$hash];
        if ($state == 'enter'){//入库
            $where[] = ['operate','=',1];
        }else{//出库
            $where[] = ['operate','=',2];
        }
        $StockDetailModel = new StockDetailModel();
        $data = $this->getPageList($StockDetailModel, $where,'',6);
        foreach ($data['list'] as $key=>$row){
            $row['add_time'] = dateTpl($row['add_time'],'Y-m-d H:i:s',true);
            $data['list'][$key] = $row;
        }

        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取商品详细情
    /*------------------------------------------------------ */
    public function getGoodsInfo(){
        $hash = input('hash','','trim');
        $where[] = ['hash_code','=',$hash];
        $data['goods'] = $this->Model->alias('s')->join("shop_goods sg", 's.goods_id=sg.goods_id', 'left')->where($where)->field('s.*,sg.goods_name,sg.goods_thumb')->find()->toArray();
        if ($data['goods']['sku_id'] > 0){
            $data['goods']['sku_name'] = (new GoodsSkuModel)->where('sku_id',$data['goods']['sku_id'])->value('sku_name');
        }
        return $this->success($data);
    }

}

