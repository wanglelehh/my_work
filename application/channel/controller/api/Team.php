<?php

namespace app\channel\controller\api;
use app\channel\ApiController;

use app\channel\model\TeamModel;

/*------------------------------------------------------ */
//-- 团队相关API
/*------------------------------------------------------ */

class Team extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new TeamModel();
    }
    /*------------------------------------------------------ */
    //-- 获取团队数量统计
    /*------------------------------------------------------ */
    public function getTeamCountToCenter(){
        return $this->success($this->Model->getTeamCountToCenter($this->userInfo['user_id']));
    }
    /*------------------------------------------------------ */
    //-- 获取团队数据
    /*------------------------------------------------------ */
    public function getTeamCount()
    {
        $type = input('type','','trim');
        $data = $this->Model->getTeamCount($this->userInfo['user_id'],$type);
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取团队指定层级会员数据
    /*------------------------------------------------------ */
    public function getTeamUserList()
    {
        $role_id = input('role_id',0,'intval');
        $type = input('type','','trim');
        $data = $this->Model->getTeamUserList($this->userInfo['user_id'],$type,$role_id);
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取指定会员下级
    /*------------------------------------------------------ */
    public function getUnderUserlist()
    {
        $pid = input('pid',0,'intval');
        $data = $this->Model->getUnderUserlist($pid,$this->userInfo['user_id']);
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取拿货团队数据
    /*------------------------------------------------------ */
    public function getPurchaseTeamCount(){
        $data = $this->Model->getPurchaseTeamCount($this->userInfo['user_id']);
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取拿货下级指定层级会员数据
    /*------------------------------------------------------ */
    public function getPurchaseUserList(){
        $role_id = input('role_id',0,'intval');
        $data = $this->Model->getPurchaseUserList($this->userInfo['user_id'],$role_id);
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取代理审核统计
    /*------------------------------------------------------ */
    public function getCheckStatic()
    {
        $data['waitCheck'] = 0;
        $data['complete'] = 0;
        $data['fail'] = 0;
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取代理审核日志列表
    /*------------------------------------------------------ */
    public function getCheckLog()
    {
        $state = input('state','waitCheck','trim');
        $data['list'] = [];
        $data['page_count'] = 0;
        return $this->success($data);
    }

}

