<?php

namespace app\favour\controller\api;

use app\ApiController;

use app\favour\model\FavourModel;
use app\favour\model\FavourGoodsModel;


/*------------------------------------------------------ */
//-- 限时优惠相关API
/*------------------------------------------------------ */

class Goods extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new FavourGoodsModel();
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    /*------------------------------------------------------ */
    public function getList()
    {
        $data['tabList'] = (new FavourModel)->getCycleList();
        $data['list'] = $this->Model->getGoodsList();
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取首页推荐列表
    /*------------------------------------------------------ */
    public function getBestList()
    {
        $time = time();
        $toTime = date("G", $time);
        $cycleList = (new FavourModel)->getCycleList();
        $goodsList = $this->Model->getGoodsList();
        $ableCycle = [];
        //获取需显示的档期
        foreach ($cycleList as $cycle) {
            if ($toTime >= $cycle['start'] && $toTime < $cycle['end']) {
                $cycle['status'] = 1;
                $cycle['_status'] = '抢购中';
                $ableCycle = $cycle;
                break;
            } elseif ($toTime < $cycle['start']) {
                $cycle['status'] = 0;
                $cycle['_status'] = '即将开抢';
            } elseif ($toTime >= $cycle['end']) {
                $cycle['status'] = 2;
                $cycle['_status'] = '已结束';
            }
        }
        $favour_show_num = settings('favour_show_num');
        $goodsArr = [];
        foreach ($goodsList[$ableCycle['name']] as $goods) {
            if ($favour_show_num > 0 && count($goodsArr) >= $favour_show_num) break;
            if ($goods['is_best'] != 1) continue;
            $goodsArr[] = $goods;
        }
        $return['code'] = 1;
        $ableCycle['list'] = $goodsArr;
        $return['data'] = $ableCycle;
        return $this->ajaxReturn($return);
    }


}
