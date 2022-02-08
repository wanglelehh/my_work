<?php

namespace app\mainadmin\controller;
use app\AdminController;
use app\mainadmin\model\AdminUserModel;
use think\captcha\Captcha;
/**
 * 登陆
 * Class Passport
 * @package app\store\controller
 */
class Passport extends AdminController
{
    //*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    public function initialize()
    {
        $this->isCheckPriv = false;
        parent::initialize();
    }
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
            $AdminUserModel = new AdminUserModel;
            $data['user_name'] = input('post.user_name','','trim');
            $data['password'] = input('post.password','','trim');
            if (empty($data['user_name']) || empty($data['password'])){
                return $this->error('请填写用户帐号和密码.');
            }
            $verifyCode = input('post.verifyCode','','trim');
            if (empty($verifyCode)){
                return $this->error('请填写验证码.');
            }
            $captcha = new Captcha();
            if( !$captcha->check($verifyCode))
            {
                return $this->error(['验证码错误.',-1]);
            }
            $private_key = openssl_pkey_get_private(RSA_PRIVATE);
            if(!$private_key){
                return $this->error('密码无法识别.');
            }
            $return_de = openssl_private_decrypt(base64_decode($data['password']), $decrypted, $private_key);
            if(!$return_de){
                return $this->error('密码解密失败,请检查RSA秘钥.');
            }
            $data['password'] = $decrypted;
            if ($AdminUserModel->login($data)) {
                return $this->success('登录成功','/lzadmin.php');
            }
            return $this->error($AdminUserModel->getError() ?: '登录失败');
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
       (new AdminUserModel)->logout();
       $this->redirect('passport/login');
    }
    //*------------------------------------------------------ */
    //-- 验证码
    /*------------------------------------------------------ */
    public function captcha()
    {
        $config = [
            // 验证码字体大小
            'fontSize'    =>    25,
            // 验证码位数
            'length'      =>    4,
            // 关闭验证码杂点
            'useCurve'    =>    false,
            'codeSet'=> '123456789'
        ];
        $captcha = new Captcha($config);
        return $captcha->entry();
    }
}
