<?php
namespace app\channel\controller\sys_admin;
use app\AdminController;

use app\channel\model\ProxyUsersModel;
use app\channel\model\AccountLogModel;
use app\channel\model\WalletModel;

use app\member\model\UsersModel;
use app\member\model\RoleModel;

use think\Db;

/**
 * 会员帐户变动明细
 */
class AccountLog extends AdminController
{
	//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize()
   {	
   		parent::initialize();
		$this->Model = new AccountLogModel(); 
    }
	/*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
    public function index()
    {
		$this->assign("start_date", date('Y/m/01',strtotime("-1 months")));
		$this->assign("end_date",date('Y/m/d'));
		$this->getList(true);
		$this->assign("userInfo" ,(new UsersModel)->info($this->search['user_id']));
        $channelInfo = (new ProxyUsersModel)->returnInfo($this->search['user_id']);
        $this->assign("channelInfo" ,$channelInfo);
        return $this->fetch('sys_admin/account_log/index');
    }  
	/*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {		
		$this->search['user_id'] = input('user_id/d');	
		$this->search['account_type'] = input('account_type/s');
		$this->search['change_type'] = input('change_type/d');
		$this->assign("search", $this->search);
		$reportrange = input('reportrange');
		$where = [];
		if ($this->search['change_type'] > 0 ){
			$where[] = ['change_type','=',$this->search['change_type']];	
		}
		switch ($this->search['account_type']) {
            case 'uplevel_goods_money':
                $where[] = ['uplevel_goods_money','<>',0];
                break;
			case 'balance_money':
				$where[] = ['balance_money','<>',0];	
				break;
            case 'goods_money':
                $where[] = ['goods_money','<>',0];
                break;
			case 'earnest_money':
				$where[] = ['earnest_money','<>',0];	
				break;
		}
		
		if (empty($reportrange) == false){
			$dtime = explode('-',$reportrange);
			$where[] = ['change_time','between',[strtotime($dtime[0]),strtotime($dtime[1])+86399]];
		}else{
			$where[] = ['change_time','between',[strtotime(date('Y/m/01',strtotime("-1 months"))),time()]];
		}
		if (0 < $this->search['user_id'] ){
			$where[] = ['user_id','=',$this->search['user_id'] ];
		}
		$this->sqlOrder = 'change_time DESC';
        $data = $this->getPageList($this->Model,$where);			
		$this->assign("data", $data);
		if ($runData == false){
			$data['content']= $this->fetch('list')->getContent();
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }
    /*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
    public function account()
    {
        $this->assign("roleList", (new RoleModel)->getRows(1));
        $this->getAccountList(true);
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 获取帐户列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getAccountList($runData = false) {
        $role_id = input('role_id',0,'intval');
        $where[] = ['is_channel','=',1];
        if ($role_id > 0){
            $where[] = ['role_id','=',$role_id];
        }
        $keyword = input('keyword','','trim');
        if (empty($keyword) == false) {
            if (is_numeric($keyword)) {
                $where[] = ['','exp',Db::raw("user_id = '" . $keyword . "' or mobile = '".$keyword."'")];
            } else {
                $where[] = ['real_name','like', $keyword. "%" ];
            }
        }
        $UsersModel = new UsersModel();

        $data = $this->getPageList($UsersModel,$where,'user_id');
        foreach ($data['list'] as $key=>$user){
            $data['list'][$key] = $UsersModel->info($user['user_id']);
        }
        $this->assign("data", $data);
        if ($runData == false){
            $data['content']= $this->fetch('account_list')->getContent();
            unset($data['list']);
            return $this->success('','',$data);
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 帐户
    /*------------------------------------------------------ */
    public function logIndex(){
        return $this->index();
    }
    /*------------------------------------------------------ */
	//-- 调节会员帐号
	/*------------------------------------------------------ */ 
	public function manage(){
		$user_id = input('user_id',0,'intval');
		if ($this->request->isPost()){
            $logs = [];
			$balance_money_type = input('balance_money_type','add','trim');
			$balance_money = input('balance_money',0,'float');
			if ($balance_money > 0){
				$data['balance_money'] = $number = $balance_money_type == 'add' ? $balance_money : $balance_money * -1;
                $logs[] = '余额:'.$number;
			}
			$goods_money_type = input('goods_money_type','add','trim');
			$goods_money = input('goods_money',0,'intval');
			if ($goods_money > 0){
                $data['goods_money'] = $number = $goods_money_type == 'add' ? $goods_money : $goods_money * -1;
                $logs[] = '货款:'.$number;
			}
			$earnest_money_type = input('earnest_money_type','add','trim');
			$earnest_money = input('earnest_money',0,'intval');
			if ($earnest_money > 0){
                $data['earnest_money'] = $number = $earnest_money_type == 'add' ? $earnest_money : $earnest_money * -1;
                $logs[] = '保证金:'.$number;
			}
			if (empty($data)) return $this->error('请核实是否有输入正确的更改值？');
            $logs = join('_',$logs);
			$data['user_id'] = $user_id;
			$data['change_desc'] = input('change_desc','','trim').'，'.$logs;
			$data['change_type'] = 1;
			$data['by_id'] = AUID;
			$res = (new WalletModel)->change($data,$user_id);
			if ($res < 1) return $this->error();
            $this->_log($user_id, '调节会员账户：' . $logs,'member');
			return $this->success('操作成功','reload');
		}
        $this->assign("userInfo" ,(new UsersModel)->info($user_id));
		return $this->fetch('sys_admin/account_log/manage');
	}
}
