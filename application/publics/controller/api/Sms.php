<?php
namespace app\publics\controller\api;
use app\ApiController;
use think\facade\Cache;
use app\mainadmin\model\SmsTplModel;
use app\member\model\UsersModel;
/*------------------------------------------------------ */
//-- 短信相关API
/*------------------------------------------------------ */
class Sms extends ApiController{

	/*------------------------------------------------------ */
	//-- 发送验证短信
	/*------------------------------------------------------ */
 	public function sendCode(){
 	    $this->checkPostLimit('sendCode');//验证请求次数
        $time = time();
		$type = input('type','','trim');
      	$sms_fun = settings('sms_fun');
        $is_bind = input('is_bind','0','intval');
        $_type = $type;
        if ($_type == 'channel_sms_register') {
            $_type = 'register';
            $sms_type_status = settings('channel_sms_register');
        }elseif ($_type == 'channel_sms_login'){
            $_type = 'login';
            $sms_type_status = settings('channel_sms_login');
        }elseif ($_type == 'channel_sms_forget_pw'){
            $_type = 'forget_password';
            $sms_type_status = settings('channel_sms_forget_pw');
        }else{
            if (isset($sms_fun[$_type])){
                $sms_type_status = $sms_fun[$_type];
            }else{
                $sms_type_status = 1;
            }
        }
        if ($sms_type_status != 1) return $this->error('相关短信模板未开通.');
        $smsTpl = SmsTplModel::getRows();
        $smsTpl =  $smsTpl[$_type];
        if (empty($smsTpl['sms_tpl_code'])) return $this->error('相关短信未设置.');
        $mobile = input('mobile','','trim');
        if (empty($mobile) == true){
            $mobile = $this->userInfo['mobile'];
        }else{
            if (checkMobile($mobile) == false)  return $this->error('手机号码不正确.');
        }
        if (empty($mobile) == true){
            return $this->error('请填写手机号码.');
        }
        $sendLogMkey = 'sendLog_'.$mobile;
        $sendLog = Cache::get($sendLogMkey);
        if (empty($sendLog)) $sendLog = ['num'=>0,'time'=>$time,'limit'=>0];
        if ($sendLog['limit'] == 1 ){//视为攻击行为，禁止指定分钟(缓存时长即禁止时长)
            return $this->error('不能频繁向同一号码发送短信.');
        }
        $codemkey = 'code_'.$type.$mobile;
        $codeCache = Cache::get($codemkey);
        if (empty($codeCache) == false && $codeCache['time'] > $time - 60){
            return $this->error('短信已发送，请稍后再试.');
        }
        $data = [];
        //查询手机号码是否存在
        $usersModel = new UsersModel();
        if (in_array($type,['login','forget_password'])){
            $count = $usersModel->where('mobile',$mobile)->count('user_id');
            if ($count < 1 ) return $this->error('手机号码不存在.');
        }elseif(in_array($type,['register']) && $is_bind == 0){
            $count = $usersModel->where('mobile',$mobile)->count('user_id');
            if ($count > 0 ) return $this->error('手机号码已存在.');
        }elseif(in_array($type,['bind_mobile'])){
            $count = $usersModel->where('mobile',$mobile)->count('user_id');
            if ($count > 0 ) return $this->error('手机号码已绑定帐号.');
        }
        elseif(in_array($type,['channel_register'])){
            $data['user_exist'] = 0;
            $row = $usersModel->where('mobile',$mobile)->field('mobile,is_channel')->find();
            if (empty($row) == false){
                if ($row['is_channel'] == 1){
                    return $this->error('当前手机号码已注册代理.');
                }
                $data['user_exist'] = 1;
            }
        }
        $fun = str_replace('/','\\','/sms/'.$sms_fun['function']);
        //生成随机码
        $code = rand(100000,999999);

        $is_send = settings('sys_send_sms');//真值为0测试，不发送短信，直接返回验证码

        if (empty($is_send)){
            $data['code'] = $code;
        }else{
            $Class  = new $fun($sms_fun['function_val']);
            $res = $Class->send($mobile,$smsTpl['sms_tpl_code'],['code'=>$code]);
            if ($res !== true) $this->error($res);

            //单个号码发送记录处理
            $sendLog['num'] += 1;
            if ($sendLog['num']>10 && $sendLog['time'] > $time - 1200){
                $sendLog['limit'] = 1;//同一号码20分钟发送达10次，视为攻击行为，禁止执行
            }
            Cache::set($sendLogMkey,$sendLog,1800);
        }
        Cache::set($codemkey,['code'=>$code,'time'=>$time],1800);//验证码缓存
        //单个号码发送记录处理end
        return  $this->success('','',$data);
	}
    /*------------------------------------------------------ */
    //-- 验证短信验证码
    /*------------------------------------------------------ */
    public function checkMobileCode(){
        $type = input('type','','trim');
        $mobile = input('mobile','','trim');
        $code = input('code','','intval');
        $res = $this->checkCode($type,$mobile,$code);//验证短信验证
        if ($res !== true){
            return $this->error($res);
        }
        return  $this->success('验证成功.');
    }

}
