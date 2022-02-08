<?php
namespace app\channel\controller\sys_admin;
use app\member\model\RoleModel;

use app\AdminController;
use app\channel\model\ChannelGoodsModel;
use app\channel\model\ChannelGoodsPricesModel;
use app\channel\model\ProxyLevelModel;

use app\channel\model\GoodsUnitModel;


/**
 * 代理商品管理
 * Class Index
 * @package app\store\controller
 */
class Goods extends AdminController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize(){
        parent::initialize();
        $this->Model = new ChannelGoodsModel();
    }
    /*------------------------------------------------------ */
    //-- 详细页调用
    /*------------------------------------------------------ */
    public function info() {
        $goods_id = input('goods_id',0,'intval');
        $prices = [];
        $row = [];
        if ($goods_id > 0){
            $row = $this->Model->where('goods_id',$goods_id)->find();
        }
        if (empty($row) == false){
            $row['convert_unit'] = json_decode($row['convert_unit'],true);
            $prices = (new ChannelGoodsPricesModel)->where('goods_id',$goods_id)->select()->toArray();
        }

        $this->assign('roleList',(new RoleModel)->getRows(1));
        $this->assign('prices',$prices);
        $this->assign('goodsUnit',(new GoodsUnitModel)->getRows());
        $this->assign('row',$row);
        return $this->fetch();
    }


}
