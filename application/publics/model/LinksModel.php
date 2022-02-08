<?php

namespace app\publics\model;
use app\BaseModel;

//*------------------------------------------------------ */
//-- 商城link调用
/*------------------------------------------------------ */
class LinksModel extends BaseModel
{
    /*------------------------------------------------------ */
    //-- 链接列表
    /*------------------------------------------------------ */
    public function links(){
        $links =  [
            [
                'name' => '首页',
                'url' => '/pages/shop/index/index'
            ],[
                'name' => '用户中心',
                'url' => '/pages/member/center/index'
            ],[
                'name' => '所有商品',
                'url' =>'/pages/shop/goods/index'
            ],[
                'name' => '购物车',
                'url' =>'/pages/shop/flow/cart'
            ],[
                'name' => '商品分类',
                'url' =>'/pages/shop/goods/cateList'
            ],[
                'name' => '我的订单',
                'url' =>'/pages/shop/order/list'
            ],[
                'name' => '地址管理',
                'url' =>'/pages/member/address/list'
            ],[
                'name' => '我的信息',
                'url' =>'/pages/member/center/editInfo'
            ],[
                'name' => '我的粉丝',
                'url' =>'/pages/member/fans/index'
            ],[
                'name' => '我的收藏',
                'url' =>'/pages/member/center/collect'
            ],[
                'name' => '我的二维码',
                'url' =>'/pages/member/center/myCode'
            ],[
                'name' => '我的钱包',
                'url' =>'/pages/member/wallet/index'
            ],[
                'name' => '每日签到',
                'url' =>'/pages/member/center/sign'
            ],[
                'name' => '我的售后',
                'url' =>'/pages/shop/aftersale/index'
            ],[
                'name' => '商学院',
                'url' =>'/pagesA/school/index'
            ]

        ];
        $setting = settings();
        if ($setting['shop_goods_comment'] == 1){
            $links[] = [
                'name' => '我的评价',
                'url' =>'/pages/shop/comment/my'
            ];
        }
        if ($setting['luckdraw_turntable'] == 1){
            $links[] = [
                'name' => '大转盘',
                'url' =>'/pagesA/luckdraw/turntable'
            ];
        }
        if ($setting['sys_model_xcx_live_room'] == 1) {
            $links[] = [
                'name' => '直播列表',
                'url' =>'/pagesA/live/index'
            ];
        }

        if ($setting['sys_model_shop_integral'] == 1) {
            $links[] = [
                'name' => '积分兑换',
                'url' =>'/pagesA/integral/index'
            ];
        }

        if ($setting['sys_model_shop_bonus'] == 1) {
            $links[] = [
                'name' => '我的优惠券',
                'url' =>'/pages/shop/bonus/index'
            ];
            $links[] =[
                'name' => '领劵中心',
                'url' =>'/pages/shop/bonus/center'
            ];
        }

        if ($setting['sys_model_shop_fightgroup'] == 1) {
            $links[] = [
                'name' => '拼团活动',
                'url' =>'/pagesA/fightgroup/index'
            ];
            $links[] = [
                'name' => '我的拼团',
                'url' =>'/pagesA/fightgroup/orderList'
            ];
        }
        //判断限时优惠模块是否存在
        if ($setting['sys_model_shop_favour'] == 1) {
            $links[] = [
                'name' => '限时优惠',
                'url' =>'/pagesA/favour/index'
            ];
        }
       
        //代理端键接
        if ($setting['sys_model_channel'] == 1) {
            $links[] = [
                'name' => '进入代理端',
                'url' =>'/pagesB/channel/center/index'
            ];
        }
        return $links;
    }
}
