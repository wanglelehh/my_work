<?php
namespace app\member\controller\sys_admin;
use think\Db;

use app\AdminController;
use app\member\model\RechargeLogModel;
use app\mainadmin\model\PaymentModel;
use app\member\model\AccountModel;
use app\member\model\UsersModel;
//*------------------------------------------------------ */
//-- 充值
/*------------------------------------------------------ */
class Recharge extends AdminController
{
    public $pay_id;
	 //*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize(){	
   		parent::initialize();
		$this->Model = new RechargeLogModel(); 
    }
	/*------------------------------------------------------ */
	//-- 主页
	/*------------------------------------------------------ */
    public function index(){		
		$this->assign("start_date", date('Y/m/01',strtotime("-1 months")));
		$this->assign("end_date",date('Y/m/d'));
        $this->pay_id = 3;//默认选中线下打款
		$this->getList(true);
		$this->assign("userRechargeTypeOpt", arrToSel($this->userRechargeType,0));	
		$this->assign("payList",$this->payList);
		return $this->fetch();
	}
   /*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
		$this->userRechargeType = $this->getDict('UserRechargeType');	
		$this->payList = (new PaymentModel)->getRows(false,'pay_code');
		$search['keyword'] = input('keyword','','trim');
		$search['status'] = input('status',0,'intval');
		$search['pay_id'] = input('pay_id',$this->pay_id,'intval');
		$reportrange = input('reportrange');
		$where = [];
		if (empty($reportrange) == false){
			$dtime = explode('-',$reportrange);
			$where[] = ['add_time','between',[strtotime($dtime[0]),strtotime($dtime[1])+86399]];
		}else{
            $stime = strtotime(date('Y/m/01',strtotime("-1 months")));
            $where[] = ['add_time','between',[$stime,time()]];
		}
		if ($search['status'] >= 0){
			$where[] = ['status','=',$search['status']];
		}
		if ($search['pay_id'] > 0){
			$where[] = ['pay_id','=',$search['pay_id']];
		}
		if (empty($search['keyword']) == false){
			 $UsersModel = new UsersModel();
			 $uids = $UsersModel->where(" mobile LIKE '%".$search['keyword']."%' OR user_name LIKE '%".$search['keyword']."%' OR nick_name LIKE '%".$search['keyword']."%' OR user_id = '".$search['keyword']."'")->column('user_id');
			 $uids[] = -1;//增加这个为了以上查询为空时，限制本次主查询失效			 
			 $where[] = ['user_id','in',$uids];
		}
        $is_export =  input('is_export',0,'intval');
        if ($is_export > 0) {
            return $this->export($where);
        }
        $data = $this->getPageList($this->Model,$where);
		$this->assign("search", $search);	
		$this->assign("userRechargeType", $this->userRechargeType);
		$this->assign("payment", $this->payList);		
		$this->assign("data", $data);
		if ($runData == false){
			$data['content']= $this->fetch('list')->getContent();
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }
	/*------------------------------------------------------ */
	//-- 信息页调用
	//-- $data array 自动读取对应的数据
	/*------------------------------------------------------ */
	public function asInfo($data){
		$userRechargeType = $this->getDict('UserRechargeType');
		$data['status_name'] = $userRechargeType[$data['status']]['name'];
		$this->assign("payList", (new PaymentModel)->getRows(false,'pay_code'));
		if ($data['pay_code'] == 'offline'){
			$data['imgs'] = explode(',',$data['imgs']);
		}	
		return $data;
	}
	
	/*------------------------------------------------------ */
	//-- 修改前处理
	/*------------------------------------------------------ */
    public function beforeEdit($data){
        $this->mkey = 'rechargeCheck_'.$data['order_id'];
        $res = redisLook($this->mkey,10);//redis锁
        if ($res == false){
            return $this->error('正在执行中，请销后再试.');
        }
		$operating = input('operating','','trim');
		if ($operating == 'refuse'){			
			$data['status'] = 1;			
		}elseif ($operating == 'arrival'){
		    $_data = $this->Model->where('order_id',$data['order_id'])->find();
		    if ($_data['pay_code'] != 'offline'){
                return $this->error('非法操作，只有线下支付的才能操作审批.');
            }
			$data['status'] = 9;			
		}else{
			return $this->error('非法操作.');
		}
		$data['admin_id'] = AUID;
		$data['check_time'] = time();
		Db::startTrans();//启动事务
		return $data;		
	}
	/*------------------------------------------------------ */
	//-- 修改后处理
	/*------------------------------------------------------ */
    public function afterEdit($data){
		if ($data['status'] == 9){
			$info = $this->Model->find($data['order_id']);
			$AccountModel = new AccountModel();
			$changedata['change_desc'] = '充值到帐';
			$changedata['change_type'] = 6;
			$changedata['by_id'] = $info['order_id'];
			$changedata['balance_money'] = $info['order_amount'];
			$res = $AccountModel->change($changedata, $info['user_id'], false);
			if ($res !== true) {
				Db::rollback();// 回滚事务
				return $this->error('更新帐户信息失败.');
			}			
		}
		Db::commit();// 提交事务
        redisLook($this->mkey,-1);//销毁锁
        return $this->success('操作成功.','reload');
	}

    //*------------------------------------------------------ */
    //-- 统计数据
    /*------------------------------------------------------ */
    public function statc(){
        $reportrange = input('reportrange','','trim');
        $where[] = ['status','=',9];
        $dtime = explode('-',base64_decode($reportrange));
        $where[] = ['add_time','between',[strtotime($dtime[0]),strtotime($dtime[1])+86399]];

        $data = $this->Model->where($where)->group('pay_code')->field('pay_name,SUM(order_amount) as total_amount,count(order_id) as num')->select();
        $this->assign("data", $data);
        $totalData['num'] = 0;
        $totalData['total_amount'] = 0;
        foreach ($data as $key=>$row){
            $totalData['num'] += $row['num'];
            $totalData['total_amount'] += $row['total_amount'];
        }
        $this->assign("totalData", $totalData);
        return $this->fetch('statc');
    }
    /*------------------------------------------------------ */
    //-- 导出
    /*------------------------------------------------------ */
    public function export(&$where)
    {
        $export_arr['会员ID'] = 'user_id';
        $export_arr['会员名称'] = 'user_name';
        $export_arr['充值日期'] = 'add_time';
        $export_arr['充值方式'] = 'pay_name';
        $export_arr['充值金额'] = 'order_amount';
        $export_arr['第三方平台交易流水号'] = 'transaction_id';
        $export_arr['处理状态'] = 'status';

        $page = 0;
        $page_size = 500;
        $page_count = 100;
        $title = join("\t", array_keys($export_arr)) . "\t";
        $data = '';
        $userRechargeType = $this->getDict('UserRechargeType');
        do {
            $rows = $this->Model->where($where)->limit($page * $page_size, $page_size)->select();
            if (empty($rows)) break;
            foreach ($rows as $row) {
                foreach ($export_arr as $val) {
                    if (strstr($val, '_time')) {
                        $data .= dateTpl($row[$val]) . "\t";
                    } elseif ($val == 'user_name') {
                        $data .= userInfo($row['user_id']). "\t";
                    }elseif ($val == 'status'){
                        $data .= $userRechargeType[$row['status']]['dict_val']. "\t";
                    }else{
                        $data .= $row[$val] . "\t";
                    }
                }
                $data .= "\n";
            }
            $page++;
        } while ($page <= $page_count);

        $filename = '充值记录_' . date("YmdHis") . '.xls';
        $filename = iconv('utf-8', 'GBK//IGNORE', $filename);
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=$filename");
        echo iconv('utf-8', 'GBK//IGNORE', $title . "\n" . $data) . "\t";
        exit;
    }
}
