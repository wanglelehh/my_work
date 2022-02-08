<?php
namespace app\school\controller\sys_admin;
use app\AdminController;

use app\school\model\VideoMaterialModel;
/**
 * 视频素材
 */
class VideoMaterial extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new VideoMaterialModel();
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
        if (empty($data['title_img'])){
            return $this->error('请上传标题图片.');
        }
        if (empty($data['video_url']) == false){
            $video_url = $data['video_url'];
            $file_path = config('config._upload_').'school/video/';
            if ($data['id'] > 0){
                $oldRow = $this->Model->where('id',$data['id'])->find();
                if ($oldRow['video_url'] != $video_url){
                    $data['video_url'] = copyFile($video_url,$file_path);
                    @unlink('.'.$oldRow['video_url']);
                }
            }else{
                $row['video_url'] = copyFile($video_url,$file_path);
            }
            @unlink('.'.$video_url);
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
        @unlink('.'.$row['video_url']);
        return $this->success('删除成功.');
    }
    /*------------------------------------------------------ */
    //-- 上传视频
    /*------------------------------------------------------ */
    public function uploadVideo(){
        $this->returnJson = true;//统一返回json
        if($_FILES['file']['size'] > 200000000){
            return $this->error('最大支持 200M MB上传.');
        }
        if (strstr($_FILES["file"]['type'],'video/mp4') == false) {
            return $this->error('未能识别文件格式，请核实.');
        }
        $file_type = end(explode('.',$_FILES['file']['name']));
        if (in_array($file_type,['mp4']) == false){
            return $this->error('格式不对，只支持 (apk格式)，请核实.');
        }
        $dir = config('config._upload_').'snap_file/';
        makeDir($dir);
        $file_name = random_str(16).'.'.$file_type;
        move_uploaded_file($_FILES['file']['tmp_name'],$dir.$file_name);
        $data['filename'] = trim($dir.$file_name,'.');
        return $this->success($data);
    }

}
