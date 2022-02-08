<?php
namespace app\member\controller\sys_admin;
use think\Db;

use app\AdminController;
use app\member\model\WithdrawLogModel;
use app\member\model\UsersModel;
use app\member\model\AccountModel;
use app\mainadmin\model\RegionModel;
//*------------------------------------------------------ */
//-- 提现
/*------------------------------------------------------ */
class Withdraw extends AdminController
{
	 //*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize(){	
   		parent::initialize();
		$this->Model = new WithdrawLogModel();
    }
	/*------------------------------------------------------ */
	//-- 主页
	/*------------------------------------------------------ */
    public function index(){		
		$this->assign("start_date", date('Y/m/01',strtotime("-1 months")));
		$this->assign("end_date",date('Y/m/d'));
		$this->getList(true);
		return $this->fetch();
	}
   /*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
		$this->userWithdrawType = $this->getDict('UserWithdrawType');	
		$search['keyword'] = input('keyword','','trim');
		$search['status'] = input('status',0,'intval');
		$search['type'] = input('type','','trim');
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
		if (empty($search['keyword']) == false){
			 $UsersModel = new UsersModel();
			 $uids = $UsersModel->where(" mobile LIKE '%".$search['keyword']."%' OR user_name LIKE '%".$search['keyword']."%' OR nick_name LIKE '%".$search['keyword']."%' OR user_id = '".$search['keyword']."'")->column('user_id');
			 $uids[] = -1;//增加这个为了以上查询为空时，限制本次主查询失效			 
			 $where[] = ['user_id','in',$uids];
		}
		if (empty($search['type']) == false){
			$where[] = ['account_type','=',$search['type']];
		}
        $is_export =  input('is_export',0,'intval');
        if ($is_export > 0) {
            return $this->export($where);
        }
		$viewObj = $this->Model->where($where);

        $data = $this->getPageList($this->Model,$viewObj);
        foreach ($data['list'] as $key=>$row){
            $row['account_info'] = json_decode($row['account_info'],true);
            $data['list'][$key] = $row;
        }
		$this->assign("status", $this->Model->status);
		$this->assign("search", $search);		
		$this->assign("data", $data);
		$fee_types = config('config.fee_types');
        $this->assign('fee_types', $fee_types);
        $this->assign('withdraw_account_type', config('config.withdraw_account_type'));
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
        $data['account'] = json_decode($data['account_info'],true);
		$userWithdrawType = $this->getDict('UserWithdrawType');
		$data['status_name'] = $userWithdrawType[$data['status']]['name'];
        $this->assign('withdraw_account_type', config('config.withdraw_account_type'));
		return $data;
	}
	
	/*------------------------------------------------------ */
	//-- 修改前处理
	/*------------------------------------------------------ */
    public function beforeEdit($data){
		$operating = input('operating','note','trim');
		if ($operating == 'note'){
			if (empty($data['admin_note'])){
				return $this->error('请填写备注.');
			}
		}elseif ($operating == 'refuse'){
			if (empty($data['admin_note'])){
				return $this->error('请填写备注，列明拒绝原因.');
			}
			$data['status'] = 1;
			$data['refuse_time'] = time();
			$data['admin_id'] = AUID;
		}elseif ($operating == 'pay'){
			if (empty($data['pay_info']) && $data['trans_type'] == 0){
				return $this->error('请填写打款信息.');
			}
			$data['status'] = 9;
			$data['complete_time'] = time();
			$data['admin_id'] = AUID;
		}else{
			return $this->error('非法操作.');
		}		
		$data['update_time'] = time();
        $this->mkey = 'withdrawCheck_'.$data['log_id'];
        $res = redisLook($this->mkey,10);//redis锁
        if ($res == false){
            return $this->error('正在执行中，请勿重复提交.');
        }
		Db::startTrans();//启动事务
		return $data;		
	}
	/*------------------------------------------------------ */
	//-- 修改后处理
	/*------------------------------------------------------ */
    public function afterEdit($data){
        $info = $this->Model->find($data['log_id']);
        $AccountModel = new AccountModel();
        if ($data['status'] == 1){//拒绝提现，退回帐户
			$changedata['change_desc'] = '提现失败退回';
			$changedata['change_type'] = 5;
			$changedata['by_id'] = $info['log_id'];
			$changedata['balance_money'] = $info['real_money'];
            $changedata['money'] = $info['money'] * -1;
			$res = $AccountModel->change($changedata, $info['user_id'], false);
			if ($res !== true) {
				Db::rollback();// 回滚事务
                redisLook($this->mkey,-1);//销毁锁
				return $this->error('未知错误，提现退回用户余额失败.');
			}			
		}elseif ($data['status'] == 9){
            if ($info['account_type'] == 'alipay' && $data['trans_type'] == 1){
                $account = json_decode($info['account_info'],true);
                $alipayMobile = new \payment\alipayMobile\alipayMobile();
                $transferInfo['order_sn'] = date('ym').str_pad($data['log_id'],8,"0");
                $transferInfo['arrival_money'] = $info['arrival_money'];
                $transferInfo['alipay_account'] = $account['account'];
                $transferInfo['alipay_name'] = $account['name'];
                $res = $alipayMobile->oneTransfer($transferInfo);
                if ($res !== true){
                    Db::rollback();// 回滚事务
                    redisLook($this->mkey,-1);//销毁锁
                    return $this->error('转帐到支付宝失败:'.$res->sub_msg);
                }
            }
            if ($info['account_type'] == 'wxbag') {
                $account = json_decode($info['account_info'],true);
                if(empty($account['account'])){
                    Db::rollback();// 回滚事务
                    redisLook($this->mkey,-1);//销毁锁
                    return $this->error('企业付款失败:无效openid');
                }
                $code = str_replace('/', '\\', "/payment/" . 'weixin' . "/" . 'weixin');
                $payment = new $code();
                $res = $payment->sendMoney($info['arrival_money'], $account['account'], '微信提现到零钱', $account['name']);
                if ($res !== 'SUCCESS') {
                    Db::rollback();//回滚
                    redisLook($this->mkey,-1);//销毁锁
                    return $this->error('操作失败-'.$res);
                }
            }
        }
		Db::commit();// 提交事务
        redisLook($this->mkey,-1);//销毁锁
        //模板消息通知
        $WeiXinMsgTplModel = new \app\weixin\model\WeiXinMsgTplModel();
        $WeiXinUsersModel = new \app\weixin\model\WeiXinUsersModel();
        if ($data['status'] == 1) {
            $info['send_scene'] = 'withdraw_fail_msg';//提现拒绝通知
        }elseif ($data['status'] == 9) {
            $info['send_scene'] = 'withdraw_ok_msg';//提现打款通知
        }
        $wxInfo = $WeiXinUsersModel->where('user_id', $info['user_id'])->field('wx_openid,wx_nickname')->find();
        $info['openid'] = $wxInfo['wx_openid'];
        $info['send_nick_name'] = $wxInfo['wx_nickname'];
        $WeiXinMsgTplModel->send($info);//模板消息通知
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

        $this->assign("status", $this->Model->status);
       echo $this->Model->where($where)->count();
        $data = $this->Model->where($where)->group('account_type')->field('account_type,SUM(real_money) as realMoney,SUM(arrival_money) as totalMoney,SUM(withdraw_fee) as totalFee,count(log_id) as num')->select()->toArray();
        $this->assign("data", $data);
        $totalData['num'] = 0;
        $totalData['realMoney'] = 0;
        $totalData['totalMoney'] = 0;
        $totalData['totalFee'] = 0;
        foreach ($data as $key=>$row){
            $totalData['num'] += $row['num'];
            $totalData['realMoney'] += $row['realMoney'];
            $totalData['totalMoney'] += $row['totalMoney'];
            $totalData['totalFee'] += $row['totalFee'];
        }
        $this->assign("totalData", $totalData);
        $this->assign('withdraw_account_type', config('config.withdraw_account_type'));
        return $this->fetch('statc');
    }
    /*------------------------------------------------------ */
    //-- 导出
    /*------------------------------------------------------ */
    public function export(&$where)
    {
        $export_arr['会员ID'] = 'user_id';
        $export_arr['会员名称'] = 'user_name';
        $export_arr['申请日期'] = 'add_time';
        $export_arr['实扣金额'] = 'real_money';
        $export_arr['到帐金额'] = 'arrival_money';
        $export_arr['处理状态'] = 'status';
        $export_arr['提现方式'] = 'account_type';

        $export_arr['账户姓名'] = 'account_name';
        $export_arr['支付宝/微信帐号'] = 'account_user_name';

        $export_arr['银行'] = 'bank_name';
        $export_arr['卡号'] = 'bank_card_number';
        $export_arr['网点所在地'] = 'bank_location_outlet';
        $export_arr['支行名称'] = 'bank_subbranch';
        $export_arr['持卡人'] = 'bank_user_name';
        $export_arr['持卡人身份证'] = 'bank_idcard_munber';


        $page = 0;
        $page_size = 500;
        $page_count = 100;
        $title = join("\t", array_keys($export_arr)) . "\t";
        $lang = lang('withdraw');
        $data = '';
        $withdraw_account_type = config('config.withdraw_account_type');
        $RegionModel = new RegionModel();
        do {
            $rows = $this->Model->where($where)->limit($page * $page_size, $page_size)->select();
            if (empty($rows)) break;
            foreach ($rows as $row) {
                $account_info = json_decode($row['account_info'],true);
                foreach ($export_arr as $val) {
                    if (strstr($val, '_time')) {
                        $data .= dateTpl($row[$val]) . "\t";
                    }elseif ($val == 'status'){
                        $data .= $lang[$row[$val]]. "\t";
                    } elseif ($val == 'user_name') {
                        $data .= userInfo($row['user_id']). "\t";
                    } elseif ($val == 'account_type') {
                        $data .= $withdraw_account_type[$row['account_type']]. "\t";
                    }elseif (strstr($val, 'account_name')) {
                        $data .= $account_info['name']. "\t";
                    }elseif (strstr($val, 'account_user_name')) {
                        $data .= $account_info['account']. "\t";
                    }elseif (strstr($val, 'bank_')) {
                        if ($row['account_type']=='bank'){
                            if ($val == 'bank_card_number') {
                                $data .= "'" . $account_info['bank_card_number'] . "\t";
                            }elseif ($val == 'bank_idcard_munber') {
                                $data .= "'".$account_info['bank_idcard_munber']. "\t";
                            }elseif ($val == 'bank_location_outlet') {
                                $rwhere[] = ['id','IN',[$account_info['bank_province'],$account_info['bank_city']]];
                                $regionName = $RegionModel->where($rwhere)->column('name');
                                $data .= join('-',$regionName). "\t";
                            }else{
                                $data .= $account_info[$val]. "\t";
                            }
                        }else{
                            $data .= "\t";
                        }
                    }else{
                       $data .= str_replace(array("\r\n", "\n", "\r"), '', strip_tags($row[$val])) . "\t";
                    }
                }
                $data .= "\n";
            }
            $page++;
        } while ($page <= $page_count);

        $filename = '提现资料_' . date("YmdHis") . '.xls';
        $filename = iconv('utf-8', 'GBK//IGNORE', $filename);
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=$filename");
        echo iconv('utf-8', 'GBK//IGNORE', $title . "\n" . $data) . "\t";
        exit;
    }
}
