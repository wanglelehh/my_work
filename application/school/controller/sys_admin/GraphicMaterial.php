<?php
namespace app\school\controller\sys_admin;
use app\AdminController;

use app\school\model\GraphicMaterialModel;
/**
 * 图文素材
 */
class GraphicMaterial extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new GraphicMaterialModel();
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
    //-- 详细页调用
    /*------------------------------------------------------ */
    public function asInfo($data) {
        if (empty($data['images']) == false){
            $data['images'] = explode(',',$data['images']);
        }
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 验证数据
    /*------------------------------------------------------ */
    private function checkData($data) {
        if (empty($data['title'])){
            return $this->error('请填写素材标题.');
        }
        if (empty($data['images'])){
            return $this->error('请上传素材图片.');
        }
        $file_path = config('config._upload_').'school/images/';
        $images = [];
        foreach ($data['images']['path'] as $key => $img){
            if (strstr($img,'snap_file')){
                $images[] = copyFile($img,$file_path);
                @unlink('.'.$img);
            }else{
                $images[] = $img;
            }
        }
        $data['images'] = join(',',$images);
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
        $images = explode(',',$row['images']);
        foreach ($images as $img){
            @unlink('.'.$img);
        }
        return $this->success('删除成功.');
    }
    /*------------------------------------------------------ */
    //-- 上传分享图片
    /*------------------------------------------------------ */
    public function uploadImg(){
        $this->returnJson = true;//统一返回json
        $result = $this->_upload($_FILES['file'],'snap_file/');
        if ($result['error']) {
            return $this->error('上传失败，请重试.');
        }
        $file_url = str_replace('./','/',$result['info'][0]['savepath'].$result['info'][0]['savename']);
        $data['image'] = array('thumbnail'=>$file_url,'path'=>$file_url);
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 删除图片
    /*------------------------------------------------------ */
    public function removeImg() {
        $file = input('post.url','','trim');
        unlink('.'.$file);
        return $this->success('删除成功.');
    }

}
