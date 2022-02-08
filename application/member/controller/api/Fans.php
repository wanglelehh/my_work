<?php

namespace app\member\controller\api;

use app\ApiController;

use app\member\model\FansModel;
use app\member\model\RoleModel;
use app\member\model\UsersBindSuperiorModel;
use think\Db;

/*------------------------------------------------------ */
//-- 我的团队相关API
/*------------------------------------------------------ */

class Fans extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
		$this->checkLogin();//验证登陆
        $this->Model = new FansModel();
    }
    /*------------------------------------------------------ */
    //-- 获取角色列表
    /*------------------------------------------------------ */
    public function getRoleList(){
        $data = [];
        $data['roleList'] = (new RoleModel)->getRows();
        $data['selRole'] = [];
        $data['selRole'][] = ['value'=>0,'label'=>'全部'];
        foreach ($data['roleList'] as $role){
            $data['selRole'][] = ['value'=>$role['role_id'],'label'=>$role['role_name']];
        }
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取角色列表
    /*------------------------------------------------------ */
    public function getFansInfo(){
        $user_id = input('user_id',0,'intval');
        if ($user_id < 1){
            return $this->error('缺少用户ID伟参.');
        }
        $superior = (new UsersBindSuperiorModel)->where('user_id',$user_id)->value('superior');
        $superior = explode(',',$superior);
        if (in_array($this->userInfo['user_id'],$superior) == false){
            return $this->error('你无权查看.');
        }
        $userInfo = $this->Model->info($user_id);
        $data['user_id'] = $userInfo['user_id'];
        $data['nick_name'] = $userInfo['nick_name'];
        $data['role_name'] = $userInfo['role']['role_name'];
        $data['mobile'] = mobileSubstr($userInfo['mobile']);
        $data['reg_time'] = date('Y-m-d',$userInfo['reg_time']);
        return $this->success($data);
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
    public function getFansUserList()
    {
        $user_id = input('user_id',0,'intval');
        $role_id = input('role_id',0,'intval');
        $type = input('type','all','trim');
        $keyword = input('keyword','','trim');

        $viewObj = $this->Model->alias('u');
        $where[] = ' u.is_ban = 0';
        $selUserId = $this->userInfo['user_id'];
        if ($user_id > 0 ){
            $selUserId = $user_id;
        }else{
            if ($role_id > 0){
                $where[] = ' u.role_id = '.$role_id;
            }
            if (empty($keyword) == false){
                $where[] = " u.user_id = '".$keyword."' OR u.mobile = '".$keyword."' OR u.nick_name like '%".$keyword."%' ";
            }
        }

        if($type == 'direct'){//直推
            $where[] = ' u.pid = '.$selUserId;
        }else{
            $viewObj->join("users_bind_superior ubs", 'u.user_id=ubs.user_id', 'left');
            if($type == 'indirect') {//间推
                $where[] = ' ubs.pid_b = ' . $selUserId;
            }else{//全部
                $where[] = ' FIND_IN_SET('.$selUserId.',ubs.superior)';
                $where[] = ' u.user_id <> '.$selUserId;
            }
        }
        $viewObj->where(join(' AND ', $where))->field('u.user_id,u.headimgurl,u.nick_name,u.role_id');
        $data = $this->getPageList($this->Model, $viewObj);
        $p = input('p',0,'intval');
        if ($p == 1){
            $res = Db::query($viewObj->count());
            $data['userTotal'] = $res[0]['tp_count'];
        }
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

}
