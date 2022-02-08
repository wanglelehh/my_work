<?php

namespace app\member\controller\api;

use app\ApiController;
use think\facade\Cache;

use app\member\model\UsersModel;
/*------------------------------------------------------ */
//-- 会员登陆、注册、找回密码相关API
/*------------------------------------------------------ */

class Passport extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new UsersModel();
    }

    /*------------------------------------------------------ */
    //-- 用户登陆
    /*------------------------------------------------------ */
    public function login()
    {
        $this->checkPostLimit('login');//验证请求次数
        $mobile = input('mobile','','trim');
        if(empty($mobile)){
            return $this->error('请填写手机号码.');
        }
        if (checkMobile($mobile) == false){
            return $this->error('登录手机号码格式不正确.');
        }
        $loginErrorNumKey = 'loginErrorNum_'.$mobile;
        $loginError = Cache::get($loginErrorNumKey);
        $data['showVerify'] = false;
        if (empty($loginError)){
            $loginError['num'] = 0;
        }elseif($loginError['num'] > 3){
            $data['showVerify'] = true;
        }
        if ($data['showVerify'] == true){
            $verifyCode = input('verifyCode','','trim');
            $captcha = new \lib\Captcha();
            if(!$captcha->check($verifyCode,request()->header('appsessionid')))
            {
                return $this->error(['图形验证码错误.',-1],'',$data);
            }
        }
        $time = time();
        if ($loginError['num'] >= 10 && $loginError['limitTime'] > $time){//限制不能登陆
            $limitTime = lastLimitTime($loginError['limitTime']);
            return $this->error('登录错误次数过多，'.$limitTime.'后再操作','',$data);
        }
        $login_type = input('login_type',0,'intval');
        $password = input('password','','trim');
        $code = input('code',0,'intval');
        if ($login_type == 1){
            if (empty($code)){
                return $this->error('请填写短信验证码.');
            }
            $res = $this->checkCode('login',$mobile,$code);//验证短信验证
            if ($res !== true){
                $loginError['num']++;
                $loginError['limitTime'] = $time + 600;
                Cache::set($loginErrorNumKey,$loginError,600);
                return $this->error([$res,-1],'',$data);
            }
        }elseif(empty($password)){
            return $this->error('请填写登录密码.','',$data);
        }
        $userInfo = $this->Model->where('mobile',$mobile)->field('user_id,is_ban,password,token')->find();
        if (empty($userInfo)){
            return $this->error('用户不存在.');
        }

        if($login_type == 0){
            require EXTEND_PATH . '/../Data/rsaKey.php';
            $private_key = openssl_pkey_get_private(RSA_PRIVATE);
            if(!$private_key){
                return $this->error('密码无法识别.');
            }
            $return_de = openssl_private_decrypt(base64_decode($password), $decrypted, $private_key);
            if(!$return_de){
                return $this->error('密码解密失败,请检查RSA秘钥.');
            }
            $password = f_hash($decrypted);
            if ($password != $userInfo['password']){
                $loginError['num']++;
                $loginError['limitTime'] = $time + 600;
                Cache::set($loginErrorNumKey,$loginError,600);
                return $this->error(['登录密码不正确.',-1],'',$data);
            }
        }
        if ($userInfo['is_ban'] == 1) {
            return $this->error(langMsg('用户已被禁用.', 'member.login.is_ban'));
        }
        Cache::rm($loginErrorNumKey);

        $upData = [];
        $wx_openid = input('wx_openid','','trim');
        if (empty($wx_openid) == false) {
            $WeiXinUsersModel = new \app\weixin\model\WeiXinUsersModel();
            $wxInfo = $WeiXinUsersModel->where('wx_openid',$wx_openid)->field('wxuid,user_id,wx_nickname,wx_headimgurl')->find();
            if (empty($wxInfo) == false && $wxInfo['user_id'] < 1) {
                $WeiXinUsersModel->bindUserId($wxInfo['wxuid'], $userInfo['user_id']);
                if (empty($userInfo['nick_name'])){
                    $upData['nick_name'] = $wxInfo['wx_nickname'];
                }
                if (empty($upData['headimgurl'])){
                    $upData['headimgurl'] = $this->Model->getHeadImg($userInfo['user_id'],$wxInfo['wx_headimgurl'],false);
                    $upData['headimgurl'] = trim($upData['headimgurl'], '.');
                }
            }
        }

        $data['share_token'] = $userInfo['token'];
        $data['token'] = $this->Model->doLogin($userInfo['user_id'],$upData);
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 用户退出
    /*------------------------------------------------------ */
    public function logout()
    {
        $this->Model->logout();
        return $this->success('退出成功');
    }
    /*------------------------------------------------------ */
    //-- 注册用户
    /*------------------------------------------------------ */
    public function register()
    {
		$register_status = settings('register_status');
		if ($register_status != 1){
			return $this->error(langMsg('暂不开放注册.','member.register.register_close'));
		}
        $openid = input('openid','','trim');
		$mobile = input('mobile','','trim');
		$code = input('code','','trim');
		if (empty($openid)){
            $res = $this->checkCode('register',$mobile,$code);//验证短信验证
            if ($res !== true){
                return $this->error($res);
            }
        }
        $inArr['pid'] = input('pid','0','intval');
        $inArr['openid'] = $openid;
        $inArr['mobile'] = $mobile;
        $inArr['nick_name'] = input('nick_name','','trim');
        $inArr['password'] = input('password','','trim');
        $inArr['invite_code'] = input('invite_code','','trim');
        $inArr['iv'] = input('iv','','trim');
        $inArr['encryptedData'] = input('encryptedData','','trim');
        $res = $this->Model->register($inArr);
        if ($res !== true) return $this->error($res);

        $upData = [];
        if (empty($openid) == false) {
            $WeiXinUsersModel = new \app\weixin\model\WeiXinUsersModel();
            $wxInfo = $WeiXinUsersModel->where('wx_openid',$openid)->field('wxuid,user_id,wx_nickname,wx_headimgurl')->find();
            if (empty($wxInfo) == false && $wxInfo['user_id'] < 1) {
                $WeiXinUsersModel->bindUserId($wxInfo['wxuid'], $this->Model->userId);
                $upData['nick_name'] = $wxInfo['wx_nickname'];
                $upData['headimgurl'] = $this->Model->getHeadImg($this->Model->userId,$wxInfo['wx_headimgurl'],false);
                $upData['headimgurl'] = trim($upData['headimgurl'],'.');
            }
        }

        //注册成功自动登录
        $userInfo = $this->Model->where('user_id',$this->Model->userId)->find();
        $data['share_token'] = $userInfo['token'];
        $data['token'] = $this->Model->doLogin($this->Model->userId,$upData);
        $data['msg'] = '注册成功';
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取分享码的注册人的user_id/mobile
    /*------------------------------------------------------ */
    public function getShareInfo(){
        $invite_code = input('invite_code','','trim');
        $data['share_user'] = [];
        if (empty($invite_code)){
            return $this->success($data);
        }
        if(is_numeric($invite_code)){
            $where[] = ['mobile','=',$invite_code];
        }else{
            $where[] = ['token','=',$invite_code];
        }
        $userInfo = $this->Model->where($where)->field('user_id,nick_name,real_name,headimgurl,mobile')->find();
        if (empty($userInfo)){
            return $this->success($data);
        }
        if (settings('register_share_user_nick_name') != 1){
            unset($userInfo['nick_name']);
        }
        if (settings('register_share_user_real_name') != 1){
            unset($userInfo['real_name']);
        }
        if (settings('register_share_user_mobile') != 1){
            unset($userInfo['mobile']);
        }
        $data['share_user'] = $userInfo;
        return $this->success($data);
    }
	/*------------------------------------------------------ */
    //-- 找回用户密码
    /*------------------------------------------------------ */
    public function forgetPwd()
    {
        $res = $this->checkCode('forget_password',input('mobile'),input('code'));//验证短信验证
        if ($res !== true){
            return $this->error($res);
        }
        $res = $this->Model->forgetPwd(input(),$this);
        if ($res !== true) return $this->error($res);		
        return $this->success(langMsg('密码已重置，请用新密码登陆.','member.forgetpwd.success'));
    }

    /*------------------------------------------------------ */
    //-- 线下支付上传图片
    /*------------------------------------------------------ */
    public function proposal_img()
    {
        if ($_FILES['file']) {
            $dir = 'proposal/';
            $result = $this->_upload($_FILES['file'], $dir);
            if ($result['error']) {
                $data['code'] = 1;
                $data['msg'] = $result['info'];
                return $this->ajaxReturn($data);
            }
            $data['code'] = 1;

            $result['url'] = '/' . $result['info'][0]['savepath'] . $result['info'][0]['savename'];
            return $this->ajaxReturn($result);
        }
        return $this->ajaxReturn(['code' => 0]);

    }
	
}
