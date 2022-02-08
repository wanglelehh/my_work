<?php
namespace app\school\controller\sys_admin;
use app\AdminController;

use app\school\model\HdPicModel;
/**
 * 高清大图
 */
class HdPic extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new HdPicModel();
    }
	/*------------------------------------------------------ */
	//-- 主页
	/*------------------------------------------------------ */
    public function index(){
        $this->getList(true);
		return $this->fetch();
	}
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false)
    {
        $where = [];
        $search['keyword'] = input('keyword', '', 'trim');
        if (empty($search['keyword']) == false) $where[] = ['title', 'like', '%' . $search['keyword'] . '%'];
        $data = $this->getPageList($this->Model, $where);
        $this->assign("data", $data);
        $this->assign("search", $search);
        if ($runData == false) {
            $data['content'] = $this->fetch('list')->getContent();
            unset($data['list']);
            return $this->success('', '', $data);
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 验证数据
    /*------------------------------------------------------ */
    private function checkData($data) {
        if (empty($data['image'])){
            $this->error('请上传图片.');
        }
        $image = $data['image'];
        if (strstr($image,'snap_file')){
            $file_path = config('config._upload_').'school/hdimg/';
            $data['image'] = copyFile($image,$file_path);
            @unlink('.'.$image);
            if ($data['id'] > 0){
                $oldimg = $this->Model->where('id',$data['id'])->value('image');
                @unlink('.'.$oldimg);
            }
        }
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 添加前调用
    /*------------------------------------------------------ */
    public function beforeAdd($data)
    {
        $data['add_time'] = time();
        return $this->checkData($data);
    }
    //-- 添加后调用
    /*------------------------------------------------------ */
    public function afterAdd($data)
    {
        return $this->success('添加成功.', url('index'));
    }
    /*------------------------------------------------------ */
    //-- 修改前调用
    /*------------------------------------------------------ */
    public function beforeEdit($data)
    {
        return $this->checkData($data);
    }
    /*------------------------------------------------------ */
    //-- 修改后调用
    /*------------------------------------------------------ */
    public function afterEdit($data)
    {
        return $this->success('修改成功.');
    }
    /*------------------------------------------------------ */
    //-- 删除
    /*------------------------------------------------------ */
    public function del()
    {
        $map['id'] = input('id', 0, 'intval');
        if ($map['id'] < 1) return $this->error('传递参数失败！');
        $row = $this->Model->where($map)->find();
        $res = $this->Model->where($map)->delete();
        if ($res < 1) return $this->error();
        @unlink('.'.$row['image']);
        return $this->success('删除成功.');
    }
    /*------------------------------------------------------ */
    //-- 上传
    /*------------------------------------------------------ */
    public function upload(){
        $this->returnJson = true;//统一返回json
        if($_FILES['file']['size'] > 20000000){
            return $this->error('最大支持 20M MB上传.');
        }
        if (strstr($_FILES["file"]['type'],'image') == false) {
            return $this->error('未能识别文件格式，请核实.');
        }
        $file = $_FILES['file']['name'];
        $extend = getFileExtend($file);
        if (in_array($extend[1],['jpg','png']) == false){
            return $this->error('只允许上传jpg、png格式图片.');
        }
        $dir = config('config._upload_').'snap_file/';
        makeDir($dir);
        $file_name = random_str(16).'.'.$extend[1];
        move_uploaded_file($_FILES['file']['tmp_name'],$dir.$file_name);
        $data['filename'] = trim($dir.$file_name,'.');
        return $this->success($data);
    }

}
