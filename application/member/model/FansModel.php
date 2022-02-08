<?php

namespace app\member\model;
use think\facade\Cache;
use think\Db;

use app\member\model\RoleModel;

//*------------------------------------------------------ */
//-- 团队相关
/*------------------------------------------------------ */

class FansModel extends UsersModel
{
    /*------------------------------------------------------ */
    //-- 优先自动执行
    /*------------------------------------------------------ */
    public function initialize(){
        parent::initialize();
        $this->mkey = 'member_fans_mkey_';
    }
    /*------------------------------------------------------ */
    //-- 团队统计-前端会员中心调用
    //-- $user_id int 代理ID
    /*------------------------------------------------------ */
    public function getFansCountToCenter($user_id = 0)
    {
        $mkey = $this->mkey.'getNum' . $user_id;
        $data = Cache::get($mkey);
        if (empty($data) == false) return $data;
        $data['allNum'] = $this->fansAllCount($user_id);
        $data['underNum'] = $this->where('pid',$user_id)->count();
        Cache::set($mkey,$data,15);
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 全部下级粉丝统计
    //-- $user_id int 代理ID
    /*------------------------------------------------------ */
    public function fansAllCount($user_id = 0)
    {
        if ($user_id < 1) return 0;
        $where[] = ['', 'exp', Db::raw("FIND_IN_SET('" . $user_id . "',superior)")];
        return (new UsersBindSuperiorModel)->where($where)->count() - 1;
    }
    /*------------------------------------------------------ */
    //-- 获取粉丝数据
    //-- $user_id int 代理ID
    //-- $type str 类型 direct 直推，indirect 间推
    /*------------------------------------------------------ */
    public function getFansCount($user_id,$type = 'direct'){
        $mkey = $this->mkey.'_under_team_'.$type.'_'.$user_id;
        $data = Cache::get($mkey);
        if (empty($data) == true){

            if ($type == 'direct'){
                $userList = $this->where('pid',$user_id)->group('role_id')->column('count(user_id) as count','role_id');
            }else{
                $UsersBindSuperiorModel = new UsersBindSuperiorModel();
                $where[] = ['', 'exp', Db::raw("FIND_IN_SET('" . $user_id . "',pus.superior)")];
                $where[] = ['pus.user_id','<>',$user_id];
                $where[] = ['pus.pid','<>',$user_id];
                $userList = $UsersBindSuperiorModel->alias('pus')->join("users u", 'pus.user_id=u.user_id', 'left')->where($where)->group('u.role_id')->column('count(u.user_id) as count','u.role_id');
            }
            $data = [];
            $roleLevel = (new RoleModel)->getRows();
            foreach ($roleLevel as $key=>$rl){
                if (empty($userList[$rl['role_id']])){
                    $row['count'] = 0;
                }else{
                    $row['count'] = $userList[$rl['role_id']];
                }
                $row['role_name'] = $rl['role_name'];
                $row['role_id'] = $rl['role_id'];
                $data[] = $row;
            }
            Cache::set($mkey,$data,60);
        }
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 获取粉丝指定层级代理数据
    //-- $user_id int 代理ID
    //-- $type string 类型 direct 直推，indirect 间推
    //-- $proxy_id int 指定代理层级
    /*------------------------------------------------------ */
    public function getFansListByRole($user_id,$type = 'direct',$role_id)
    {
        $mkey = $this->mkey.'_under_team_'.$type.'_'.$user_id.'_'.$role_id;
        $data = Cache::get($mkey);
        if (empty($data) == true){
            if ($type == 'direct'){
                $where[] = ['pid','=',$user_id];
                $where[] = ['role_id','=',$role_id];
                $userList = $this->where($where)->column('user_id');
            }else{
                $UsersBindSuperiorModel = new UsersBindSuperiorModel();
                $where[] = ['', 'exp', Db::raw("FIND_IN_SET('" . $user_id . "',pus.superior)")];
                $where[] = ['pus.user_id','<>',$user_id];
                $where[] = ['pus.pid','<>',$user_id];
                $where[] = ['u.role_id','=',$role_id];
                $userList = $UsersBindSuperiorModel->alias('pus')->join("users u", 'pus.user_id=u.user_id', 'left')->where($where)->column('u.user_id');
            }
            $data = [];
            if (empty($userList) == false){
                $where = [];
                $where[] = ['user_id','in',$userList];
                $data = $this->where($where)->field('user_id,nick_name,real_name,headimgurl,mobile')->select()->toArray();
                Cache::set($mkey,$data,60);
            }
        }
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 获取拿货粉丝数据
    //-- $pid int 查询下级的会员ID
    //-- $user_id int 当前登陆会员ID,后台可不输入
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
                    $UserInfo = $this->info($uid);
                    $uInfo['user_id'] = $uid;
                    $uInfo['role_name'] = $UserInfo['role']['role_name'];
                    $uInfo['real_name'] = $UserInfo['real_name'];
                    $uInfo['nick_name'] = $UserInfo['nick_name'];
                    $uInfo['headimgurl'] = $UserInfo['headimgurl'];
                    $uInfo['mobile'] = $UserInfo['mobile'];
                    $uInfo['count'] = $this->teamAllCount($uid);
                    $uInfo['underList'] = [];//前端UI需要，须保留
                    $data['list'][] = $uInfo;
                }

                Cache::set($mkey,$data,60);
            }
        }
        return $data;
    }

}
