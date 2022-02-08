<?php

namespace app\school\controller\api;
use app\ApiController;

use app\school\model\TopicCourseModel;

/*------------------------------------------------------ */
//-- 专题课题
/*------------------------------------------------------ */

class TrainTopic extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new TopicCourseModel();
    }


    /*------------------------------------------------------ */
    //-- 获取列表
    /*------------------------------------------------------ */
    public function getList()
    {
        $this->sqlOrder = "sort_order DESC,id DESC";
        $where = [];
        $where[] = ['is_show','=',1];
        $keyword = input('keyword','','trim');
        if (empty($keyword) == false){
            $where[] = ['title','like','%'.$keyword.'%'];
        }
        $data = $this->getPageList($this->Model, $where,'id,add_time,view_num,virtual_num,title,title_img,description',6);
        foreach ($data['list'] as $key=>$row){
            $row['add_time'] = dateTpl($row['add_time']);
            $row['view_num'] += $row['virtual_num'];
            unset($row['virtual_num']);
            $data['list'][$key] = $row;
        }
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取详情
    /*------------------------------------------------------ */
    public function getInfo()
    {
        $id = input('id','0','intval');
        if ($id < 1){
            return $this->error('传参错误.');
        }
        $row = $this->Model->where('id',$id)->find();
        if (empty($row)){
            return $this->error('没有找相关数据.');
        }
        $upData['view_num'] = ['INC',1];
        $this->Model->where('id',$id)->update($upData);
        $row = $row->toArray();
        $row['view_num'] += $row['virtual_num'] + 1;
        $row['add_time'] = dateTpl($row['add_time']);
        $web_path = config('config.host_path');
        $row['content'] = htmlspecialchars_decode($row['content']);
        $row['content'] = preg_replace('/<img src=\"/', '<img style="width:100%;height:auto;" src="' .$web_path,$row['content']);
        unset($row['virtual_num']);
        $this->success($row);
    }
}