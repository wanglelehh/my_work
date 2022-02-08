<?php
namespace app\channel\controller\sys_admin;

use app\AdminController;
use app\channel\model\RewardModel;
use app\channel\model\RewardLogModel;
use app\member\model\UsersModel;
use app\member\model\RoleModel;
//*------------------------------------------------------ */
//-- 奖励明细
/*------------------------------------------------------ */
class RewardLog extends AdminController
{
	 //*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize(){	
   		parent::initialize();
		$this->Model = new RewardLogModel();
    }
	/*------------------------------------------------------ */
	//-- 主页
	/*------------------------------------------------------ */
    public function index(){
		$this->assign("start_date", date('Y/m/01',strtotime("-1 months")));
		$this->assign("end_date",date('Y/m/d'));
		$this->getList(true);
		$this->assign("payList",$this->payList);
		return $this->fetch();
	}
   /*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {

        $search['reward_type'] = input('reward_type','','trim');
		$search['status'] = input('status',-1,'intval');
        $search['keyword'] = input('keyword','','trim');
		$reportrange = input('reportrange');
		$where = [];
		if (empty($search['reward_type']) == false){
            $where[] = ['reward_type','=',$search['reward_type']];
        }
		if (empty($reportrange) == false){
			$dtime = explode('-',$reportrange);
			$where[] = ['add_time','between',[strtotime($dtime[0]),strtotime($dtime[1])+86399]];
		}else{
			$where[] = ['add_time','between',[strtotime("-1 months"),time()]];
		}
		if ($search['status'] >= 0){
			$where[] = ['status','=',$search['status']];
		}
		if ($search['pay_id'] > 0){
			$where[] = ['pay_id','=',$search['pay_id']];
		}
		if (empty($search['type']) == false){
            $where[] = ['type','=',$search['type']];
        }
		if (empty($search['keyword']) == false){
			 $UsersModel = new UsersModel();
			 $uids = $UsersModel->where(" mobile LIKE '%".$search['keyword']."%' OR real_name LIKE '%".$search['keyword']."%' OR user_id = '".$search['keyword']."'")->column('user_id');
			 $uids[] = -1;//增加这个为了以上查询为空时，限制本次主查询失效
			 $where[] = ['to_uid','in',$uids];
		}

        $data = $this->getPageList($this->Model,$where);
        $this->assign("rewardList", (new RewardModel)->rewardList);
        $this->assign("roleList", (new RoleModel)->getRows());
		$this->assign("search", $search);
        $this->assign("status", $this->Model->status);
		$this->assign("data", $data);
		if ($runData == false){
			$data['content']= $this->fetch('list')->getContent();
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }

}
