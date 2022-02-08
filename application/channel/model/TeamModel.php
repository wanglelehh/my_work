<?php

namespace app\channel\model;
use think\facade\Cache;
use think\Db;

use app\member\model\UsersModel;
use app\member\model\UsersBindSuperiorModel;
use app\member\model\RoleModel;
//*------------------------------------------------------ */
//-- 团队相关
/*------------------------------------------------------ */

class TeamModel extends ProxyUsersModel
{
    /*------------------------------------------------------ */
    //-- 优先自动执行
    /*------------------------------------------------------ */
    public function initialize(){
        parent::initialize();
        $this->mkey = 'channel_team_mkey_';
    }
    /*------------------------------------------------------ */
    //-- 团队统计-前端会员中心调用
    //-- $user_id int 代理ID
    /*------------------------------------------------------ */
    public function getTeamCountToCenter($user_id = 0)
    {
        $mkey = $this->mkey.'getNum' . $user_id;
        $data = Cache::get($mkey);
        if (empty($data) == false) return $data;
        $data['allNum'] = $this->teamAllCount($user_id);
        $where[] = ['pid','=',$user_id];
        $where[] = ['is_channel','=',1];//只查询代理
        $data['underNum'] = $this->where($where)->count();//直属下级
        $where = [];
        $where[] = ['role_pid','=',$user_id];
        $data['supplyNum'] = $this->where($where)->count();
        Cache::set($mkey,$data,15);
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 全部下级团队统计
    //-- $user_id int 代理ID
    /*------------------------------------------------------ */
    public function teamAllCount($user_id = 0)
    {
        if ($user_id < 1) return 0;
        $where[] = ['', 'exp', Db::raw("FIND_IN_SET('" . $user_id . "',us.superior)")];
        return (new UsersBindSuperiorModel)->alias('us')->join('users u ',"us.user_id=u.user_id AND u.is_channel=1")->where($where)->count() - 1;
    }
    /*------------------------------------------------------ */
    //-- 获取团队数据
    //-- $user_id int 代理ID
    //-- $type str 类型 direct 直推，indirect 间推
    /*------------------------------------------------------ */
    public function getTeamCount($user_id,$type = 'direct'){
        $mkey = $this->mkey.'_under_team_'.$type.'_'.$user_id;
        $data = Cache::get($mkey);
        if (empty($data) == true){
            if ($type == 'direct'){
                $userList = $this->where('pid',$user_id)->group('role_id')->column('count(user_id) as count','role_id');
            }else{
                $UsersBindSuperiorModel = new UsersBindSuperiorModel();
                $where[] = ['', 'exp', Db::raw("FIND_IN_SET('" . $user_id . "',us.superior)")];
                $where[] = ['us.user_id','<>',$user_id];
                $userList = $UsersBindSuperiorModel->alias('us')->join("users u", 'us.user_id=u.user_id', 'left')->where($where)->group('u.role_id')->column('count(u.user_id) as count','u.role_id');
            }
            $data = [];
            $roleList = (new RoleModel)->getRows(1);
            foreach ($roleList as $key=>$role){
                if (empty($userList[$role['role_id']])){
                    $row['count'] = 0;
                }else{
                    $row['count'] = $userList[$role['role_id']];
                }
                $row['role_name'] = $role['role_name'];
                $row['role_id'] = $role['role_id'];
                $data[] = $row;
            }
            Cache::set($mkey,$data,60);
        }
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 获取团队指定层级代理数据
    //-- $user_id int 代理ID
    //-- $type string 类型 direct 直推，indirect 间推
    //-- $role_id int 指定代理层级
    /*------------------------------------------------------ */
    public function getTeamUserList($user_id,$type = 'direct',$role_id)
    {
        $mkey = $this->mkey.'_under_team_'.$type.'_'.$user_id.'_'.$role_id;
        $data = Cache::get($mkey);
        if (empty($data) == true){
            if ($type == 'direct'){
                $where[] = ['pid','=',$user_id];
                $where[] = ['role_id','=',$role_id];
                $userIds = $this->where($where)->column('user_id');
            }else{
                $UsersBindSuperiorModel = new UsersBindSuperiorModel();
                $where[] = ['', 'exp', Db::raw("FIND_IN_SET('" . $user_id . "',us.superior)")];
                $where[] = ['us.user_id','<>',$user_id];
                $userIds = $UsersBindSuperiorModel->alias('us')->join("users u", 'us.user_id=u.user_id AND u.role_id='.$role_id)->where($where)->column('u.user_id');
            }
            $data = [];
            if (empty($userIds) == false){
                $where = [];
                $where[] = ['user_id','in',$userIds];
                $data = $this->where($where)->field('user_id,real_name,headimgurl,mobile')->select()->toArray();
                Cache::set($mkey,$data,60);
            }
        }
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 获取指定会员下级
    //-- $pid int 查询下级的会员ID
    //-- $user_id int 会员ID
    /*------------------------------------------------------ */
    public function getUnderUserlist($pid,$user_id = 0){
        if ($pid < 1){
            $pid = $user_id;
        }
        $mkey = $this->mkey.'_under_user_'.$pid.'_'.$user_id;
        $data = Cache::get($mkey);
        if (empty($data) == true){
            if ($pid != $user_id && $user_id > 0){//查询当前pid是否存在于当前会员下级
                $where = [];
                $where[] = ['user_id','=',$pid];
                $where[] = ['', 'exp', Db::raw("FIND_IN_SET('" . $user_id . "',superior)")];
                $count = (new UsersBindSuperiorModel)->where($where)->count();
                if ($count < 1){
                    return false;//无权查询
                }
            }
            $userList = $this->where('pid',$pid)->order('reg_time DESC')->column('user_id');
            $data['list'] = [];
            if (empty($userList) == false){
                if ($pid == $user_id){//查询当前登陆会员，团队总数
                    $data['count'] = $this->teamAllCount($user_id);
                }
                foreach ($userList as $uid){
                    $proxyUser = $this->info($uid);
                    $uInfo['user_id'] = $uid;
                    $uInfo['role_name'] = $proxyUser['role']['role_name'];
                    $uInfo['real_name'] = $proxyUser['real_name'];
                    $uInfo['headimgurl'] = $proxyUser['headimgurl'];
                    $uInfo['mobile'] = $proxyUser['mobile'];
                    $uInfo['count'] = $this->teamAllCount($uid);
                    $uInfo['underList'] = [];//前端UI需要，须保留
                    $data['list'][] = $uInfo;
                }

                Cache::set($mkey,$data,60);
            }
        }
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 获取直属拿货团队数据
    //-- $user_id int 代理ID
    /*------------------------------------------------------ */
    public function getPurchaseTeamCount($user_id){
        $mkey = $this->mkey.'_under_purchase_'.$user_id;
        $data = Cache::get($mkey);
        if (empty($data) == true){
            $userList = $this->where('pid',$user_id)->group('role_id')->column('count(user_id) as count','role_id');
            $data = [];
            $roleList = (new RoleModel)->getRows();
            foreach ($roleList as $key=>$role){
                if (empty($userList[$role['role_id']])){
                    $row['count'] = 0;
                }else{
                    $row['count'] = $userList[$role['role_id']];
                }
                $row['role_name'] = $role['role_name'];
                $row['role_id'] = $role['role_id'];
                $data[] = $row;
            }
            Cache::set($mkey,$data,60);
        }
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 获取拿货下级指定层级会员数据
    //-- $user_id int 代理ID
    //-- $role_id int 指定代理层级
    /*------------------------------------------------------ */
    public function getPurchaseUserList($user_id,$role_id)
    {
        $mkey = $this->mkey.'_under_purchase_'.$user_id.'_'.$role_id;
        $data = Cache::get($mkey);
        if (empty($data) == true){
            $where[] = ['role_pid','=',$user_id];
            $where[] = ['role_id','=',$role_id];
            $data = $this->where($where)->field('user_id,real_name,headimgurl,mobile')->select()->toArray();
            Cache::set($mkey,$data,60);
        }
        return $data;
    }
}
