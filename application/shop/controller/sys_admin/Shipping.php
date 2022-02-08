<?php
//物流管理
namespace app\shop\controller\sys_admin;
use app\AdminController;
use app\shop\model\ShippingModel;
use think\facade\Env;
use app\mainadmin\model\SettingsModel;

class Shipping extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new ShippingModel();		
    }
   /*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
    public function index()
    {
        $this->assign("list", $this->Model->order('is_sys DESC,sort_order DESC')->select());
		return $this->fetch();
    }
	/*------------------------------------------------------ */
	//-- 信息页调用
	//-- $data array 自动读取对应的数据
	/*------------------------------------------------------ */
	public function asInfo($data){
		if ($data['is_sys'] == 1) return $this->error('系统定义不允许修改.');
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 添加后处理
	/*------------------------------------------------------ */
    public function afterAdd($data) {
		$this->_Log($data['id'],'添加快递');
	}
	/*------------------------------------------------------ */
	//-- 修改后处理
	/*------------------------------------------------------ */
    public function afterEdit($data) {
		$this->_Log($data['id'],'修改快递');
	}
	/*------------------------------------------------------ */
	//-- 快捷修改
	/*------------------------------------------------------ */
	public function afterAjax($id,$data){
		$log = '快速修改快递：';
		if (isset($data['status'])){
			$log .= '状态为';
			$log .= ($data['status'] == 1)?'开启':'关闭';
		}elseif (isset($data['support_cod'])){
			$log .= '货到付款为';
			$log .=($data['support_cod'] == 1)?'支持':'不支持';
		}elseif (isset($data['is_front'])){
			$log .= '前台开放为';
			$log .=($data['is_front'] == 1)?'开放':'不开放';
		}else{
			return false;	
		}
		$this->_Log($id,$log);
	}
    /*------------------------------------------------------ */
    //-- 设置
    /*------------------------------------------------------ */
    public function setting(){
        $this->assign("setting", settings());
        $this->assign('shippingFunction',  $this->getShippingFunction());
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 获取所快递有接口程序
    /*------------------------------------------------------ */
    public function getShippingFunction() {
        $rows = readModules(Env::get('extend_path').'/shipping');
        $modules = array();
        foreach ($rows as $row){
            $modules[$row['function']] = $row;
        }
        return $modules;
    }
    /*------------------------------------------------------ */
    //-- 保存配置
    /*------------------------------------------------------ */
    public function save(){
        $set = input('post.');
        $res = (new SettingsModel)->editSave($set);
        if ($res == false) return $this->error();
        return $this->success('设置成功.');
    }
}
