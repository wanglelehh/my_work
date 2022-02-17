<?php

namespace app\shop\controller\api;

use app\ApiController;
use app\shop\model\GoodsModel;
use app\member\model\NavMenuModel;

class Index extends ApiController
{
    /*------------------------------------------------------ */
    //-- 自定义商城首页
    /*------------------------------------------------------ */
    public function getIndexInfo(){
        $ShopPageTheme = new \app\shop\model\ShopPageTheme();
        $data['page'] = $ShopPageTheme->getToWxApp();
        $data['tipsubscribe'] = 0;
        $data['shop_index_img']=settings('shop_index_img');
        $data['shop_index_img_open']=settings('shop_index_img_open');
        $source = request()->header('source');
        if ($source == 'H5-WX' ){
            $openid = input('openid','','trim');
            if (empty($openid) == false){
                $where[] = ['wx_openid','=',$openid];
                $where[] = ['is_mp','=',0];
                $subscribe = (new \app\weixin\model\WeiXinUsersModel)->where($where)->value('subscribe');
                $data['tipsubscribe'] = $subscribe == 1 ? 0 : 1;
            }
        }
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 自定义页页
    /*------------------------------------------------------ */
    public function getDiyPage(){
        $pageid = input('pageid',0,'intval');
        if ($pageid < 1){
            return $this->error('传参错误.');
        }
        $ShopPageTheme = new \app\shop\model\ShopPageTheme();
        $data['page'] = $ShopPageTheme->getToWxApp($pageid);
        return $this->success($data);
    }
}
