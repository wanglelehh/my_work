<?php

namespace app\supplyer\controller;
use app\supplyer\Controller;
use app\supplyer\model\SupplyerModel;

/**
 * 登陆
 * Class Passport
 * @package app\store\controller
 */
class Passport extends Controller
{

    /**
     * 后台登录
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login()
    {
        require EXTEND_PATH . '/../Data/rsaKey.php';
        if ($this->request->isAjax()) {
            $model = new SupplyerModel;
            $data['user_name'] = input('post.user_name','','trim');
            $data['password'] = input('post.password','','trim');
            if (empty($data['user_name']) || empty($data['password'])){
                return $this->error('请填写用户帐号和密码.');
            }
            $private_key = openssl_pkey_get_private(RSA_PRIVATE);
            if(!$private_key){
                return $this->error('密码无法识别.'.$data['password']);
            }
            $return_de = openssl_private_decrypt(base64_decode($data['password']), $decrypted, $private_key);
            if(!$return_de){
                return $this->error('密码解密失败,请检查RSA秘钥.');
            }
            $data['password'] = $decrypted;
            if ($model->login($data)) {
                return $this->success('登录成功', url('index/index'));
            }
            return $this->error($model->getError() ?: '登录失败');
        }
        $this->view->engine->layout(false);
        $this->assign('public_key',RSA_PUBLIC);
        return $this->fetch('login');
    }

    /**
     * 退出登录
     */
    public function logout()
    {
       $model = new SupplyerModel;
	   $model->logout();
       $this->redirect('passport/login');
    }

}
