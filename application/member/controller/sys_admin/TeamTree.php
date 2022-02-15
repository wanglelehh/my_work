<?php
namespace app\member\controller\sys_admin;
use app\AdminController;

use app\member\model\UsersModel;
use app\member\model\UserAddressModel;

use app\member\model\RoleModel;
use app\distribution\model\DividendModel;
use app\shop\model\OrderModel;
use app\weixin\model\WeiXinUsersModel;
/**
 * 团队架构
 * Class Index
 * @package app\store\controller
 */
class TeamTree extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new UsersModel();
    }
	/*------------------------------------------------------ */
	//-- 主页
	/*------------------------------------------------------ */
    public function index(){
        $this->assign('isTeamTree',1);
        $this->assign('teamCount',$this->Model->count());
		return $this->fetch();
	}
    /*------------------------------------------------------ */
    //-- 获取代理列表
    /*------------------------------------------------------ */
    public function getTeamList(){
        $pid = input('pid',0,'intval');
        $rows = $this->Model->where('pid',$pid)->field('user_id,nick_name,role_id')->select()->toArray();
        $data = [];
        $DividendRole = (new RoleModel)->getRows();
        foreach ($rows as $key=>$row){
            $arr = [];
            $name = $row['user_id'].'-';
            $name .= empty($row['nick_name'])?'未获取':$row['nick_name'];
            if (empty($DividendRole[$row['role_id']]['role_name']) == false){
                $role_name = $DividendRole[$row['role_id']]['role_name'];
            }else{
                $role_name = '普通会员';
            }
            $name .= ' - '.$role_name;
            $teamCount = $this->Model->teamCount($row['user_id']);
            $arr['name'] = $name.'('.$teamCount.')';
            $arr['type'] = 'folder';
            $arr['additionalParameters'] = ['user_id'=>$row['user_id']];
            $data[] = $arr;
        }
        return $this->success('ok','',$data);
    }
    /*------------------------------------------------------ */
    //-- 获取会员信息
    /*------------------------------------------------------ */
    public function info(){
        $user_id = input('user_id/d');
        if ($user_id < 1) return $this->error('获取用户ID传值失败！');
        $row = $this->Model->info($user_id);
        if (empty($row)) return $this->error('用户不存在！');
        $row['wx'] = (new WeiXinUsersModel)->where('user_id', $user_id)->find();
        $this->assign("userShareStats", $this->Model->userShareStats($user_id));
        $row['user_address'] = (new UserAddressModel)->where('user_id', $user_id)->select();
        $Twhere = [];
        $getIds=$this->Model->teamUid($user_id);
        $Twhere[]=['user_id','in',$getIds];
        $Twhere[]=['order_status','=',1];
        $row['teamConsume'] = (new OrderModel())->where($Twhere)->sum('order_amount');
        $this->assign('d_level', config('config.dividend_level'));
        $RoleModel = new RoleModel();
        $this->assign("roleList", $RoleModel->getRows());
        $this->assign("teamCount",$this->Model->teamCount($user_id));
        $where[] = ['dividend_uid', '=', $user_id];
        $where[] = ['status', 'in', [2, 3]];
        $DividendModel = new DividendModel();
        $dividend_amount = $DividendModel->where($where)->sum('dividend_amount');
        $this->assign("wait_money", $dividend_amount );
        $has_order = 0;
        //判断订单模块是否存在
        if (class_exists('app\shop\model\OrderModel')) {
            $has_order = 1;
            $this->assign("start_date", date('Y/m/01', strtotime("-1 months")));
            $this->assign("end_date", date('Y/m/d'));
        }
        //代理处理
        if ($row['role']['role_type'] == 1){
            if (class_exists('app\channel\model\ProxyUsersModel')) {
                $row['channelInfo'] = (new \app\channel\model\ProxyUsersModel)->returnInfo($user_id);
            }
        }
        $this->assign('row', $row);

        $this->assign('has_order',$has_order);
        $this->assign('isTeamTree',1);
        $data['content'] = $this->fetch('sys_admin/users/user_info')->getContent();
        return $this->success('','',$data);
    }
}
