<?php

namespace app\school\controller\api;
use app\ApiController;

use app\school\model\GraphicMaterialModel;

/*------------------------------------------------------ */
//-- 发圏素材
/*------------------------------------------------------ */

class GraphicMaterial extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new GraphicMaterialModel();
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
        $data = $this->getPageList($this->Model, $where,'id,title,images,add_time',6);
        foreach ($data['list'] as $key=>$row){
            $row['add_time'] = dateTpl($row['add_time']);
            $row['images'] = explode(',',$row['images']);
            $data['list'][$key] = $row;
        }
        return $this->success($data);
    }

}