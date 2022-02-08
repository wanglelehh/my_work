<?php

namespace app\channel\controller\api;
use app\channel\ApiController;

use app\channel\model\ProxyUsersModel;
use app\member\model\RoleModel;

/*------------------------------------------------------ */
//-- 代理会员相关API
/*------------------------------------------------------ */

class ProxyUsers extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行  当前由继承Users，$this->userInfo为会员
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new ProxyUsersModel();
    }
    /*------------------------------------------------------ */
    //-- 获取登陆代理信息
    /*------------------------------------------------------ */
    public function getInfo()
    {
        $data['proxyInfo'] = $this->Model->info($this->userInfo['user_id']);
        if ($data['proxyInfo']['role']['role_type'] == 0){
            return $this->error(['您无权限访问.',-1]);
        }
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取登陆代理上级信息
    /*------------------------------------------------------ */
    public function getSuperiorInfo()
    {
        $proxyInfo = $this->Model->info($this->userInfo['user_id']);
        //推荐上级
        if ($proxyInfo['pid'] == 0){
            $data['parent_name'] = '厂家';
            $data['parent_level'] = '厂家';
            $data['parent_headimgurl'] = settings('logo');
            $data['parent_mobile'] = settings('channel_mobile');
            $data['parent_reg_time'] = '--';
        }else{
            $parentUser = $this->Model->info($proxyInfo['pid']);
            $data['parent_name'] = $parentUser['real_name'];
            $data['parent_headimgurl'] = $parentUser['headimgurl'];
            $data['parent_mobile'] = $parentUser['mobile'];
            $data['parent_level'] = $parentUser['role']['role_name'];
            $data['parent_reg_time'] = dateTpl($parentUser['reg_time'],'Y-m-d H:i:s',true);
        }
        //供货上级
        if ($this->userInfo['role_pid'] == 0){
            $data['supply_name'] = '厂家';
            $data['supply_level'] = '厂家';
            $data['supply_headimgurl'] = settings('logo');
            $data['supply_mobile'] = settings('channel_mobile');
            $data['supply_warrant_end_time'] = '--';
            $data['supply_reg_time'] = '--';
        }else{
            $supplytUser = $this->Model->info($this->userInfo['role_pid']);
            $data['supply_name'] = $supplytUser['real_name'];
            $data['supply_headimgurl'] = $supplytUser['headimgurl'];
            $data['supply_mobile'] = $supplytUser['mobile'];
            $data['supply_level'] = $supplytUser['role']['role_name'];
            $data['supply_reg_time'] = dateTpl($supplytUser['reg_time'],'Y-m-d H:i:s',true);
        }
        return $this->success($data);
    }



    /*------------------------------------------------------ */
    //-- 获取可邀请的代理层级
    /*------------------------------------------------------ */
    public function getInviteRole(){
        $data['roleList'] = (new RoleModel)->getInvitelist($this->userInfo['role']['role_id']);
        return $this->success($data);
    }

    /*------------------------------------------------------ */
    //-- 获取邀请海报
    /*------------------------------------------------------ */
    public function getInvitePoster(){
        $role_id = input('role_id',0,'intval');
        $is_wxmp = input('is_wxmp',0,'intval');
        $res = $this->Model->getInvitePoster($this->userInfo['user_id'],$role_id,$is_wxmp);
        if (is_array($res) == false){
            return $this->error($res);
        }
        return $this->success($res);
    }
    /*------------------------------------------------------ */
    //-- 获取可升级的层级
    /*------------------------------------------------------ */
    public function getUpgradeList(){
        $role_id = $this->Model->where('user_id',$this->userInfo['user_id'])->value('role_id');
        $data = (new RoleModel)->getUpgradeList($this->userInfo['user_id'],$role_id);
        return $this->success($data);
    }

}
