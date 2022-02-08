<?php

namespace app\school\controller\api;
use app\ApiController;

use app\school\model\HdPicModel;

/*------------------------------------------------------ */
//-- 高清大图
/*------------------------------------------------------ */

class HdPic extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new HdPicModel();
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
        $data = $this->getPageList($this->Model, $where,'id,title,image,add_time',6);
        foreach ($data['list'] as $key=>$row){
            $row['add_time'] = dateTpl($row['add_time']);
            $data['list'][$key] = $row;
        }
        return $this->success($data);
    }

}