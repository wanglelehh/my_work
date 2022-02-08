<?php
namespace app\channel\controller\sys_admin;


/**
 * 邀请海报模板
 * Class Index
 * @package app\store\controller
 */
class InviteTemp extends LicenseTemp
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->tempType = 2;
    }
	/*------------------------------------------------------ */
	//-- 主页
	/*------------------------------------------------------ */
    public function index(){
        $row = $this->Model->where('type',$this->tempType)->find();
        $this->assign('row',$row);
        $this->assign('tempType',$this->tempType);
        $select_dom = $this->Model->select_dom;
        $select_dom['invite_level'] = ['title' => '邀请您成为', 'text' => 'XXX代理'];
        $this->assign('select_dom',$select_dom);
		return $this->fetch('sys_admin/license_temp/info');
	}
}
