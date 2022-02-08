<?php
namespace app\shop\controller\sys_admin;
use app\AdminController;

use app\mainadmin\model\SettingsModel;
use app\member\model\NavMenuModel;
/*------------------------------------------------------ */
//-- 设置
/*------------------------------------------------------ */
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
	//-- 首页
	/*------------------------------------------------------ */
    public function index(){
		$this->assign("setting", $this->Model->getRows());
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function otherPage(){
        $this->assign("setting", $this->Model->getRows());
        $tabbar = (new NavMenuModel)->where('type',0)->order('sort_order DESC id ASC')->select()->toArray();
        $this->assign("tabbar", $tabbar);
        return $this->fetch();
    }
	/*------------------------------------------------------ */
	//-- 保存配置
	/*------------------------------------------------------ */
    public function save(){
        $set = input('post.');
        $favour_time_cycle= settings('favour_time_cycle');
        $favour_start_time= settings('favour_start_time');
        $res = $this->Model->editSave($set);
        if ($res == false) return $this->error();
        //档期档期间隔 或 活动开始时间 变动，清空档期记录
        if($set['favour_time_cycle']!=$favour_time_cycle||$set['favour_start_time']!=$favour_start_time){
            if (class_exists('app\favour\model\FavourGoodsModel')) {
                (new \app\favour\model\FavourGoodsModel)->clearTimeSlot();
            }
        }
		return $this->success('设置成功.');
    }

    /*------------------------------------------------------ */
    //-- 管理底部菜单
    /*------------------------------------------------------ */
    public function tabbar(){
        $id = input('id',0,'intval');
        $NavMenuModel = new NavMenuModel();
        if ($id < 0){
            $count = $NavMenuModel->where('type',0)->count('id');
            if ($count >=5 ){
                return $this->error('最多只能设置5个菜单');
            }
        }
        if ($this->request->isPost()){
            $data['title'] = input('title','','trim');
            $data['imgurl'] = input('imgurl','','trim');
            $data['imgurl_sel'] = input('imgurl_sel','','trim');
            $data['status'] = input('status',0,'intval');
            $data['sort_order'] = input('sort_order',0,'intval');
            $data['url'] = input('url','','trim');
            if (empty($data['title'])){
                return $this->error('请输入菜单名称');
            }
            if (empty($data['imgurl'])){
                return $this->error('请设置未选中图标');
            }
            if (empty($data['imgurl_sel'])){
                return $this->error('请设置选中图标');
            }
            if (empty($data['url'])){
                return $this->error('请绑定URL');
            }
            if ($id > 0){
                $data['update_time'] = time();
                $res = $NavMenuModel->where('id',$id)->update($data);
            }else{
                $data['type'] = 0;
                $data['bind_type'] = 'link';
                $res = $NavMenuModel->save($data);
            }
            if ($res < 1){
                return $this->error('保存错误，请重试');
            }
            $NavMenuModel->cleanMemcache(0);
            return $this->success('操作成功.');
        }
        $row = [];
        if ($id > 0){
            $where = [];
            $where[] = ['id','=',$id];
            $where[] = ['type','=',0];
            $row = $NavMenuModel->where($where)->find();
            if (empty($row)){
                return $this->error('没有找到相关记录.');
            }
            $row->toArray();
        }
        $this->assign("row", $row);
        return $this->fetch('tabbar');
    }
    /*------------------------------------------------------ */
    //-- 删除底部菜单
    /*------------------------------------------------------ */
    public function delTabbar(){
        $id = input('id', 0, 'intval');
        if ($id < 1) return $this->error('传递参数失败！');
        $NavMenuModel = new NavMenuModel();
        $data = $NavMenuModel->where('id', $id)->find();
        if (!strstr($data['imgurl'],'menuimg')){
            unlink('.' . $data['imgurl']);
        }
        if (!strstr($data['imgurl_sel'],'menuimg')){
            unlink('.' . $data['imgurl_sel']);
        }
        $res = $data->delete();
        if ($res < 1) return $this->error();
        $NavMenuModel->cleanMemcache(0);
        return $this->success('删除成功.');
    }
}
