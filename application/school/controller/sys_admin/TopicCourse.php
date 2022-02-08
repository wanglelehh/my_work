<?php
namespace app\school\controller\sys_admin;
use app\AdminController;

use app\school\model\TopicCourseModel;
/**
 * 专题课程
 */
class TopicCourse extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new TopicCourseModel();
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
            $this->error('请上传标题图片.');
        }
        if (empty($data['video_url']) == false){
            $video_url = $data['video_url'];
            $file_path = config('config._upload_').'school/video/';
            if ($data['id'] > 0){
                $oldRow = $this->Model->where('id',$data['id'])->find();
                if ($oldRow['video_url'] != $video_url){
                    $data['video_url'] = copyFile($video_url,$file_path);
                    @unlink('.'.$oldRow['video_url']);
                    @unlink('.'.$video_url);
                }
            }else{
                $data['video_url'] = copyFile($video_url,$file_path);
                @unlink('.'.$video_url);
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
        @unlink('.'.$row['video_url']);
        return $this->success('删除成功.');
    }
    /*------------------------------------------------------ */


}
