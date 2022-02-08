<?php

namespace app\integral\controller\api;

use app\ApiController;

use app\integral\model\IntegralGoodsModel;
use app\shop\model\GoodsModel;

/*------------------------------------------------------ */
//-- 商品相关API
/*------------------------------------------------------ */

class Goods extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new IntegralGoodsModel();
    }
    /*------------------------------------------------------ */
    //-- 获取商品列表
    /*------------------------------------------------------ */
    public function getList()
    {
        $cateId = input('cateId',0,'intval');

        $time = time();
        $where[] = ['ig.start_date','<',$time];
        $where[] = ['ig.end_date','>',$time];
        $where[] = ['g.is_delete', '=', 0];
        $where[] = ['g.is_alone_sale', '=', 1];
        $where[] = ['g.isputaway', '>', 0];

        if ($cateId > 0){
            $classList = (new GoodsModel)->getClassList();
            $where[] = ['g.cid','in',$classList[$cateId]['children']];
        }

        $sqlOrder = input('order', '', 'trim');

        $sort_by = strtoupper(input('g.sort', 'DESC', 'trim'));
        if (in_array(strtoupper($sort_by), array('DESC', 'ASC')) == false) {
            $sort_by = 'DESC';
        }
        switch ($sqlOrder) {
            case 'integral':
                $this->sqlOrder = "ig.show_integral $sort_by";
                break;
            default:
                break;
        }

        $viewObj = $this->Model->alias('ig')->join("shop_goods g", 'ig.goods_id=g.goods_id', 'left')->where($where)->order($this->sqlOrder);

        $data = $this->getPageList($this->Model, $viewObj, 'ig.ig_id', 10);
        $show_stock_num = settings('shop_goods_show_stock_num');
        $show_market_price = settings('shop_goods_show_market_price');
        foreach ($data['list'] as $key=>$goods){
            $igoods = $this->Model->info($goods['ig_id']);
            $igoods['goods']['ig_id'] = $goods['ig_id'];
            $igoods['goods']['show_stock_num'] = $show_stock_num;
            $igoods['goods']['show_market_price'] = $show_market_price;
            $data['list'][$key] = $igoods;
        }
        return $this->success($data);
    }

    /*------------------------------------------------------ */
    //-- 获取商品详情
    /*------------------------------------------------------ */
    public function info()
    {
        $ig_id = input('ig_id', '', 'intval');
        if (empty($ig_id)) return $this->error('传参失败.');
        $data = $this->Model->info($ig_id);
        if (empty($data)) return $this->error('没有找到相关商品.');
        $data['goods']['ig_id'] = $data['ig_id'];
        $data['goods']['show_integral'] = $data['show_integral'];
        $data['goods']['shop_goods_comment'] = settings('shop_goods_comment');
        $data['goods']['show_stock_num'] = settings('shop_goods_show_stock_num');
        return $this->success($data);
    }
}
