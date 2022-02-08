<?php
namespace app\channel;
use app\ApiController as baseApiController;

/**
 * 渠道端API控制器基类
 */
class ApiController extends baseApiController
{
    /**
     * 后台初始化
     */
    public function initialize($checkLogin = true)
    {
        parent::initialize();
        if ($checkLogin == true){
            $this->checkLogin(true);//验证登录
            if ($this->userInfo['status'] == 9){
                return $this->error('账号已被禁用.');
            }
        }
    }
    /*------------------------------------------------------ */
    //-- 获取前端登陆会员数据
    /*------------------------------------------------------ */
    protected function getLoginUserInfo(){
        $userId = $this->getLoginUid();
        if ($userId < 1){
            return [];
        }
        return (new \app\channel\model\ProxyUsersModel)->info($userId);
    }
}
