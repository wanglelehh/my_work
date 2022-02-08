<?php
namespace app\channel\controller\sys_admin;

use app\AdminController;
use app\channel\model\RechargeLogModel;
use app\mainadmin\model\PaymentModel;
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

		$this->assign("payList",$this->payList);
        $this->Model->runPaySuccessEval();//执行待异步处理的充值订单到帐
		return $this->fetch();
	}
   /*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
		$this->payList = (new PaymentModel)->getRows(false,'pay_code');
		$search['keyword'] = input('keyword','','trim');
        $search['type'] = input('type','','trim');
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
		if (empty($search['type']) == false){
            $where[] = ['type','=',$search['type']];
        }
		if (empty($search['keyword']) == false){
			 $UsersModel = new UsersModel();
			 $uids = $UsersModel->where(" mobile LIKE '%".$search['keyword']."%' OR real_name LIKE '%".$search['keyword']."%' OR user_id = '".$search['keyword']."'")->column('user_id');
			 $uids[] = -1;//增加这个为了以上查询为空时，限制本次主查询失效			 
			 $where[] = ['user_id','in',$uids];
		}
        $is_export =  input('is_export',0,'intval');
        if ($is_export > 0) {
            return $this->export($where);
        }
        $data = $this->getPageList($this->Model,$where);
		$this->assign("search", $search);
        $this->assign("status", $this->Model->status);
		$this->assign("payment", $this->payList);		
		$this->assign("data", $data);
        $this->assign("typeList", $this->Model->typeList);
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
		$data['status_name'] = $this->Model->status[$data['status']];
		$this->assign("payList", (new PaymentModel)->getRows(false,'pay_code'));
		if ($data['pay_code'] == 'offline'){
			$data['imgs'] = explode(',',$data['imgs']);
		}
        $this->assign("typeList", $this->Model->typeList);
		return $data;
	}
	
	/*------------------------------------------------------ */
	//-- 确认收款
	/*------------------------------------------------------ */
    public function checkPay(){
		$operating = input('operating','','trim');
        $order_id = input('order_id','','trim');

        $check_remark = input('check_remark','','trim');
        if ($operating == 'arrival'){
            $mkey = 'channelRechargeCheckPay_'.$order_id;
            $res = redisLook($mkey,10);//redis锁
            if ($res == false){
                return $this->error('正在执行中，请销后再试.');
            }
            $_data = $this->Model->where('order_id',$order_id)->find();
            if ($_data['pay_code'] != 'offline'){
                return $this->error('非法操作，只有线下支付的才能操作审批.');
            }
            $res = $this->Model->updatePay($order_id,$check_remark);
            redisLook($mkey,-1);//销毁锁
            if ($res !== true){
                return $this->error($res);
            }
        }else{
            if (empty($check_remark)){
                return $this->error('请填写操作备注，列明拒绝原因.');
            }
            $upData['status'] = 1;
            $upData['check_remark'] = $check_remark;
            $upData['admin_id'] = AUID;
            $upData['check_time'] = time();
            $res = $this->Model->where('order_id',$order_id)->update($upData);
            if ($res < 1){
                return $this->error('操作失败，请重试.');
            }
        }
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

        $data = $this->Model->where($where)->group('type,pay_code')->field('type,pay_name,SUM(order_amount) as total_amount,count(order_id) as num')->select();
        $this->assign("data", $data);
        $totalData['type'] = [];
        $totalData['num'] = 0;
        $totalData['total_amount'] = 0;
        foreach ($data as $key=>$row){
            $totalData['type'][$row['type']]['num'] += $row['num'];
            $totalData['type'][$row['type']]['total_amount'] += $row['total_amount'];
            $totalData['num'] += $row['num'];
            $totalData['total_amount'] += $row['total_amount'];
        }
        $this->assign("typeList", $this->Model->typeList);
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
        $export_arr['充值类型'] = 'type';
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
        $typeList = $this->Model->typeList;
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
                    }elseif ($val == 'type'){
                        $data .= $typeList[$row['type']]. "\t";
                    }else{
                        $data .= $row[$val] . "\t";
                    }
                }
                $data .= "\n";
            }
            $page++;
        } while ($page <= $page_count);

        $filename = '代理充值记录_' . date("YmdHis") . '.xls';
        $filename = iconv('utf-8', 'GBK//IGNORE', $filename);
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=$filename");
        echo iconv('utf-8', 'GBK//IGNORE', $title . "\n" . $data) . "\t";
        exit;
    }

}
