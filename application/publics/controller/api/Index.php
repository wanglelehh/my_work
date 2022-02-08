<?php
namespace app\publics\controller\api;
use app\ApiController;
/*------------------------------------------------------ */
//-- 公共调用
/*------------------------------------------------------ */
class Index extends ApiController
{
    /*------------------------------------------------------ */
    //-- 获取默认设置
    /*------------------------------------------------------ */
    public function defaultSetting(){
        $rows = (new \app\mainadmin\model\SettingsModel)->where('is_open',1)->select()->toArray();
        $web_path = config('config.host_path');
        foreach ($rows as $row){
            $row['data'] = htmlspecialchars_decode($row['data']);
            $row['data'] = preg_replace('/<img src=\"\/upload/', '<img src="'.$web_path.'/upload',$row['data']);
            $row['data'] = preg_replace('/<img/', '<img style="width:100%"',$row['data']);
            $setting[$row['name']] = isJson($row['data']);
        }
        if (empty($setting['sms_fun']) == false){
            $setting['sms_public_register'] = $setting['sms_fun']['register'];
            $setting['sms_public_login'] = $setting['sms_fun']['login'];
            $setting['sms_public_forget_password'] = $setting['sms_fun']['forget_password'];
            $setting['sms_public_edit_pay_pwd'] = $setting['sms_fun']['edit_pay_pwd'];
            $setting['sms_public_bind_mobile'] = $setting['sms_fun']['bind_mobile'];
            unset($setting['sms_fun']);
        }
        //没有设置微信，公众号不进行授权请求
        if (settings('sys_model_weixin') == 0){
            $setting['weixin_is_used'] = 0;
        }

        $setting['tabarList'] = [];
        $rows = (new \app\member\model\NavMenuModel)->getRows(0);
        $sys_model_xcx_live_room = settings('sys_model_xcx_live_room');
        foreach ($rows as $row){
            if (($setting['xcx_check_model'] == 1 || $sys_model_xcx_live_room == 0) && $row['url'] == '/pagesA/live/index'){
                continue;
            }
            $tabar['iconPath'] = $web_path.$row['imgurl'];
            $tabar['selectedIconPath'] = $web_path.$row['imgurl_sel'];
            $tabar['text'] = $row['title'];
            $tabar['isDot'] = false;
            $tabar['customIcon'] = true;
            $tabar['url'] = $row['url'];
            $setting['tabarList'][] = $tabar;
        }

        require EXTEND_PATH . '/../Data/rsaKey.php';
        $setting['rsa_public'] = RSA_PUBLIC;
        $data['setting'] = $setting;

        session_start();
        $data['session_id'] = session_id();
        return $this->success($data);
    }

    /*------------------------------------------------------ */
    //-- 验证码
    /*------------------------------------------------------ */
    public function verify(){
        $session_id = input('session_id','','trim');
        $config =    [
            'fontSize' => 30,// 验证码字体大小
            'length'   => 4,// 验证码位数
            'useNoise' => false,// 关闭验证码杂点
            'codeSet' => '0123456789'
        ];
        $Captcha = new \lib\Captcha($config);
        return $Captcha->entry($session_id);
    }
    /*------------------------------------------------------ */
    //-- 生成自定义随机字符串
    /*------------------------------------------------------ */
    public function getRandstr(){
        $length = input('length',10,'intval');
        $data['str'] = random_str($length);
        return $this->success($data);
    }

    /*------------------------------------------------------ */
    //-- 公共上传临时图片
    /*------------------------------------------------------ */
    public function upImage(){
        $file = $_FILES['file']['name'];
        $type = input('type','','trim');
        $extend = getFileExtend($file);
        if (in_array($extend[1],['jpg','png']) == false){
            return $this->error('只允许上传jpg、png格式图片.');
        }
        $file_path = config('config._upload_').'snap_file/'.date('m').'/';
        if(!file_exists($file_path)){
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            makeDir($file_path);
        }
        $file = $file_path.random_str(15).rand(10,99).'.'.$extend[1];

        $res = move_uploaded_file($_FILES['file']['tmp_name'], $file) ;

        if($res == false) {
            return $this->error('上传文件失败.');
        }
        $data['file'] = trim($file,'.');
        $data['type'] = $type;
        return $this->success($data);
    }

    /*------------------------------------------------------ */
    //-- 删除上传的临时图片
    /*------------------------------------------------------ */
    public function removeImage(){
        $this->checkLogin();//验证登陆
        $file = input('file','','trim');
        if (strstr($file,'snap_file') == false){
            return $this->error('此接口只能用于删除临时图片.');
        }
        @unlink('.'.$file);
        return $this->success();
    }
    /*------------------------------------------------------ */
    //-- 获取合同
    /*------------------------------------------------------ */
    public function getContract()
    {
        $contract = htmlspecialchars_decode(settings('channel_contract'));
        $channel_contract_first_party = settings('channel_contract_first_party');
        $contract = str_replace('{甲方姓名}',$channel_contract_first_party,$contract);
        $contract = str_replace('{乙方姓名}',$this->userInfo['real_name'],$contract);
        $contract = str_replace('{乙方身份证号码}',$this->userInfo['id_card_number'],$contract);
        $data['channel_contract'] = $contract;
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取语言包
    /*------------------------------------------------------ */
    public function getLangPack(){
        $language = request()->header('language');
        $data['data'] = (new \app\mainadmin\model\LanguagePackModel)->getLangPack($language);
        $data['code'] = 1;
        return $this->ajaxReturn($data,false);
    }
}
