<?php
namespace app\weixin\controller\sys_admin;

use app\AdminController;
use think\facade\Cache;


use app\mainadmin\model\SettingsModel;
/**
 * 微信设置
 * Class Index
 * @package app\store\controller
 */
class Setting extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new SettingsModel();		
    }
	/*------------------------------------------------------ */
	//-- 主页
	/*------------------------------------------------------ */
    public function index(){
		
		$this->assign("domain", $this->request->domain());	
		$this->assign("setting", $this->Model->getRows());
		return $this->fetch();
	}
   
	/*------------------------------------------------------ */
	//-- 保存配置
	/*------------------------------------------------------ */
    public function save(){
        $set = input('post.setting');
		$res = $this->Model->editSave($set);
		if ($res == false) return $this->error();
        Cache::rm('weixin_access_token');
		return $this->success('设置成功.');
    }
    /*
	// 小程序配置
    */
    public function xcxconfig(){
        $this->assign("domain", $this->request->domain());
        $this->assign("setting", $this->Model->getRows());
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 上传点击上传微信验证文件
    /*------------------------------------------------------ */
    public function uploadVerifyTxt(){
        $file_type = end(explode('.',$_FILES['file']['name']));
        if (in_array($file_type,['txt']) == false){
            $result['code'] = 0;
            $result['msg'] = '格式不对，只支持 (txt格式)，请核实.';
            return $this->ajaxReturn($result);
        }
        $dir = config('config._upload_').'../';
        $file_name = $_FILES['file']['name'];
        move_uploaded_file($_FILES['file']['tmp_name'],$dir.$file_name);
        $result['code'] = 1;
        $result['filename'] = trim($dir.$file_name,'.');
        return $this->ajaxReturn($result);
    }
}
