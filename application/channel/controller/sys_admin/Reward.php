<?php
namespace app\channel\controller\sys_admin;
use app\AdminController;

use app\channel\model\RewardModel;
use app\member\model\RoleModel;
/**
 * 奖励政策
 */
class Reward extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new RewardModel();
    }
	/*------------------------------------------------------ */
	//-- 主页
	/*------------------------------------------------------ */
    public function index(){
        $this->assign('rewardList',$this->Model->getList());
        $this->assign('role_list',(new RoleModel)->getRows(1));
		return $this->fetch();
	}

    /*------------------------------------------------------ */
    //-- 直推升级奖励
    /*------------------------------------------------------ */
    public function tzsjjl(){
        $tzsjjl = input('tzsjjl');
        foreach ($tzsjjl as $key=>$arr){
            foreach ($arr as $keyb=>$val){
                if (empty($val)){
                    $val = 0;
                }
                $tzsjjl[$key][$keyb] = $val * 1;
            }
        }
        $res = $this->Model->_save('tzsjjl',$tzsjjl);
        if ($res == false){
            return $this->error('操作失败，请重试.');
        }
        $this->_Log(0,'修改直推升级奖励设置');
        return $this->success('设置成功.','');
    }
    /*------------------------------------------------------ */
    //-- 间推升级奖励
    /*------------------------------------------------------ */
    public function tzsjjlb(){
        $tzsjjlb = input('tzsjjlb');
        foreach ($tzsjjlb as $key=>$arr){
            foreach ($arr as $keyb=>$val){
                if (empty($val)){
                    $val = 0;
                }
                $tzsjjlb[$key][$keyb] = $val * 1;
            }
        }
        $res = $this->Model->_save('tzsjjlb',$tzsjjlb);
        if ($res == false){
            return $this->error('操作失败，请重试.');
        }
        $this->_Log(0,'修改间推升级奖励设置');
        return $this->success('设置成功.','');
    }
    /*------------------------------------------------------ */
    //-- 补货奖励
    /*------------------------------------------------------ */
    public function bhjl(){
        $bhjl = input('bhjl');
        foreach ($bhjl as $key=>$arr){
            foreach ($arr as $keyb=>$val){
                if (empty($val)){
                    $val = 0;
                }
                $bhjl[$key][$keyb] = $val * 1;
            }
        }
        $res = $this->Model->_save('bhjl',$bhjl);
        if ($res == false){
            return $this->error('操作失败，请重试.');
        }
        $this->_Log(0,'修改补货奖励设置');
        return $this->success('设置成功.','');
    }
    /*------------------------------------------------------ */
    //-- 创业金
    /*------------------------------------------------------ */
    public function cyj(){
        $is_cyj = input('is_cyj');
        $distance = input('distance');
        $cyj = [];
        foreach ($is_cyj as $key=>$val){
            $arr['is_join'] = $val * 1;
            if (empty($distance[$key])){
                if ($arr['is_join'] == 1){
                    return $this->error('设置参与，须同时设置业绩区间.');
                }
                $arr['distance'] = [];
            }else{
                $arr['distance'] = $distance[$key];
            }
            $cyj[$key] = $arr;
        }
        $res = $this->Model->_save('cyj',$cyj);
        if ($res == false){
            return $this->error('操作失败，请重试.');
        }
        $this->_Log(0,'修改创业金设置');
        return $this->success('设置成功.','');
    }
    /*------------------------------------------------------ */
    //-- 月分红
    /*------------------------------------------------------ */
    public function yfh(){
        $is_yfh = input('is_yfh');
        $distance = input('distance');
        $yfh = [];
        foreach ($is_yfh as $key=>$val){
            $arr['is_join'] = $val * 1;
            if (empty($distance[$key])){
                if ($arr['is_join'] == 1){
                    return $this->error('设置参与，须同时设置业绩区间.');
                }
                $arr['distance'] = [];
            }else{
                $arr['distance'] = $distance[$key];
            }
            $yfh[$key] = $arr;
        }
        $res = $this->Model->_save('yfh',$yfh);
        if ($res == false){
            return $this->error('操作失败，请重试.');
        }
        $this->_Log(0,'修改月分红设置');
        return $this->success('设置成功.','');
    }
    /*------------------------------------------------------ */
    //-- 区域奖
    /*------------------------------------------------------ */
    public function qyj(){
        $info['province'] = input('province','0','float');
        $info['city']= input('city','0','float');
        $info['district'] = input('district','0','float');
        $res = $this->Model->_save('qyj',$info);
        if ($res == false){
            return $this->error('操作失败，请重试.');
        }
        $this->_Log(0,'修改区域奖设置');
        return $this->success('设置成功.','');
    }
    /*------------------------------------------------------ */
    //-- 考道基金
    /*------------------------------------------------------ */
    public function xdjj(){
        $info['deduct_per'] = input('deduct_per','0','float');
        $xdjj_opt = input('xdjj_opt','inc','trim');
        $info['ext_count'] = input('opt_val','','float');
        if ($xdjj_opt == 'dec'){
            $info['ext_count'] = $info['ext_count'] * -1;
        }
        $res = $this->Model->_save('xdjj',$info);
        if ($res == false){
            return $this->error('操作失败，请重试.');
        }
        $this->_Log(0,'修改考道基金设置');
        return $this->success('设置成功.','');
    }
}
