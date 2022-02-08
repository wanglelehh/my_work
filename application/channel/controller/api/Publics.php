<?php

namespace app\channel\controller\api;
use app\channel\ApiController;


/*------------------------------------------------------ */
//-- 公共查询API，不限制登陆
/*------------------------------------------------------ */

class Publics extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize(false);
    }
    /*------------------------------------------------------ */
    //-- 查询代理授权信息
    /*------------------------------------------------------ */
    public function getAuthorizedInfo(){
        $field = input('field','mobile','trim');
        $value = input('value','','trim');
        $ProxyUsersModel = new \app\channel\model\ProxyUsersModel();
        if ($field == 'my'){
            $userInfo = $this->userInfo;
        }else{
            $UsersModel = new \app\member\model\UsersModel();
            if ($field == 'mobile'){
                $user_id = $UsersModel->where('mobile',$value)->value('user_id');
            }else{
                $user_id = $UsersModel->where('id_card_number',$value)->value('user_id');
            }
            $userInfo = $ProxyUsersModel->info($user_id);
        }

        if ($userInfo['is_ban'] == 1){
            return $this->error(['相关代理帐号已被冻结.',-1]);
        }
        if ($userInfo['role']['role_type'] == 0){
            return $this->error(['没有找到相关代理.',-1]);
        }

        $data['real_name'] = $userInfo['real_name'];
        $data['role_name'] = $userInfo['role']['role_name'];
        $data['img_err'] = 0;
        $returnImg = $ProxyUsersModel->getAuthorizedImg($userInfo);
        if ($returnImg !== false){
            $data['img_url'] = $returnImg['file'];
            $data['share_url'] = $returnImg['url'];
        }
        return $this->success($data);
    }
}

