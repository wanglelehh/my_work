<?php
namespace app\channel\controller\sys_admin;

use app\AdminController;

use app\channel\model\LicenseTempModel;

/**
 * 授权模板
 * Class Index
 * @package app\store\controller
 */
class LicenseTemp extends AdminController
{
    public $tempType = 0;
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new LicenseTempModel();
        $this->tempType = 1;
    }
	/*------------------------------------------------------ */
	//-- 主页
	/*------------------------------------------------------ */
    public function index(){
		$list = $this->Model->where('type',$this->tempType)->order('is_default DESC,id DESC')->select()->toArray();
		$this->assign('list',$list);
		return $this->fetch();
	}
    /*------------------------------------------------------ */
    //-- 详情调用
    /*------------------------------------------------------ */
    public function asInfo($data) {
        $this->assign('select_dom',$this->Model->select_dom);
        $this->assign('tempType',$this->tempType);
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 添加前处理
    /*------------------------------------------------------ */
    public function beforeAdd($data) {
        if (empty($data['temp_name'])){
            return $this->error('模板名称不能为空.');
        }
        if (empty($data['content'])){
            return $this->error('请设置模板内容.');
        }
        $where[] = ['type','=',$this->tempType];
        $where[] = ['temp_name','=',$data['temp_name']];
        $count = $this->Model->where($where)->count();
        if ($count > 0) return $this->error('已存在相同的模板名称，不允许重复添加.');
        $data['type'] = $this->tempType;
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 添加后调用
    /*------------------------------------------------------ */
    public function afterAdd($data){
        if ($data['is_default'] == 1){
            $where[] = ['type','=',$this->tempType];
            $where[] = ['is_default','=','1'];
            $where[] = ['id','<>',$data['id']];
            $this->Model->where($where)->update(['is_default'=>0]);
        }
        $this->_Log($data['id'],'添加授权模板:'.$data['temp_name']);
        return $this->success('添加成功.',url('index'));
    }

    /*------------------------------------------------------ */
    //-- 修改前处理
    /*------------------------------------------------------ */
    public function beforeEdit($data){
        if (empty($data['temp_name'])){
            return $this->error('模板名称不能为空.');
        }
        if (empty($data['content'])){
            return $this->error('请设置模板内容.');
        }
        $where[] = ['type','=',$this->tempType];
        $where[] = ['temp_name','=',$data['temp_name']];
        $where[] = ['id','<>',$data['id']];
        $count = $this->Model->where($where)->count();
        if ($count > 0) return $this->error('已存在相同的模板名称，不允许重复添加.');
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 修改后调用
    /*------------------------------------------------------ */
    public function afterEdit($data){
        if ($data['is_default'] == 1){
            $where[] = ['type','=',$this->tempType];
            $where[] = ['is_default','=','1'];
            $where[] = ['id','<>',$data['id']];
            $this->Model->where($where)->update(['is_default'=>0]);
        }
        $this->_Log($data['id'],'修改授权模板:'.$data['temp_name']);
        return $this->success('修改成功.','reload');
    }
    /*------------------------------------------------------ */
    //-- 添加后调用
    /*------------------------------------------------------ */
    public function delete(){
        $id = input('id','0','intval');
        $this->Model->where('id',$id)->delete();
        $this->_Log($id,'删除授权模板.');
        return $this->success('删除成功.');
    }
    /*------------------------------------------------------ */
    //-- 授权图片合成处理
    /*------------------------------------------------------ */
    public function mergeImg(){
        $MergeImg = new \lib\MergeImg();
        $post = input('post.');
        if (empty($post['temp_bg'])){
            return false;
        }
        $content = htmlspecialchars_decode($post['content']);
        $content = json_decode($content,true);
        $select_dom = $this->Model->select_dom;
        foreach ($content as $key=>$arr){
            $arr['info'] = $select_dom[$key];
            $content[$key] = $arr;
        }
        $post['content'] = $content;
        $file_path = './upload/snap_file/';
        makeDir($file_path);
        $MergeImg->licenseImg($post,$file_path.'test_temp.jpg');
        return $this->success('请求成功.');
    }
}
