<?php
namespace app\distribution\controller\sys_admin;
use app\AdminController;

use app\distribution\model\DividendModel;
use app\distribution\model\DividendAwardModel;

//*------------------------------------------------------ */
//-- 佣金明细
/*------------------------------------------------------ */
class DividendLog extends AdminController{
	//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
    public function initialize(){	
   		parent::initialize();
		$this->Model = new DividendModel();
    }
	
	//*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
    public function index(){
        $this->assign("awardList",(new DividendAwardModel)->getRows());
		$this->assign("start_date", date('Y/m/01',strtotime("-1 months")));
		$this->assign("end_date",date('Y/m/d'));
		$this->getList(true);
        return $this->fetch('index');
    }
	/*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false){
        $orderlang = lang('order');
		$this->assign("divdend_satus", $orderlang['ds']);
		$search['status'] = input('status','-1','intval');
        $search['award_id'] = input('award_id','0','intval');
		$search['keyword'] = input('keyword','','trim');
		$reportrange = input('reportrange');
		$where = [];
		if ($search['status'] >= 0 ){
		    if ($search['status'] == 99){
                $where[] = ['status','in',[1,2,3]];
            }else{
                $where[] = ['status','=',$search['status']];
            }
		}
        if ($search['award_id'] > 0 ){
            $where[] = ['award_id','=',$search['award_id']];
        }
		if (empty($search['keyword']) == false){
			$where[] = ['order_sn|dividend_uid','=',$search['keyword']*1];
		}
		if (empty($reportrange) == false){
			$dtime = explode('-',$reportrange);
			$stime = strtotime($dtime[0]);
            $etime = strtotime($dtime[1])+86399;
			$where[] = ['add_time','between',[$stime,$etime]];
		}else{
            $stime = strtotime(date('Y/m/01',strtotime("-1 months")));
			$where[] = ['add_time','between',[$stime,time()]];
		}
        $is_export =  input('is_export',0,'intval');
        if ($is_export > 0) {
            if (empty($reportrange) == false){
                if ($etime - $stime > 5184000){
                    return $this->error('导出时间范围不能超出60天.');
                }
            }
            return $this->export($where);
        }
        $data = $this->getPageList($this->Model, $where);	
		$this->assign("data", $data);
		$this->assign("search", $search);
		if ($runData == false){
			$data['content'] = $this->fetch('list')->getContent();
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }
    //*------------------------------------------------------ */
    //-- 统计数据
    /*------------------------------------------------------ */
    public function statc(){
        $award_id = input('award_id',0,'intval');
        $reportrange = input('reportrange','','trim');
        if (empty($reportrange) == false){
            $dtime = explode('-',base64_decode($reportrange));
            $where[] = ['add_time','between',[strtotime($dtime[0]),strtotime($dtime[1])+86399]];
        }else{
            $where[] = ['add_time','between',[strtotime("-1 months"),time()]];
        }
        if ($award_id > 0 ){
            $where[] = ['award_id','=',$award_id];
        }
        $data = $this->Model->where($where)->group('status')->column('SUM(dividend_amount) as amount,count(log_id) as num','status');
        $this->assign("data", $data);
        $okData['num'] = 0;
        $okData['amount'] = 0;
        foreach ($data as $key=>$row){
            if ($key >= 1 && $key<=9){
                $okData['num'] += $row['num'];
                $okData['amount'] += $row['amount'];
            }
        }
        $this->assign("okData", $okData);
        $orderlang = lang('order');
        $this->assign("divdend_satus", $orderlang['ds']);
        return $this->fetch('statc');
    }
    /*------------------------------------------------------ */
    //-- 导出
    /*------------------------------------------------------ */
    public function export(&$where)
    {
        $export_arr['记录时间'] = 'add_time';
        $export_arr['订单SN'] = 'order_sn';
        $export_arr['分佣会员'] = 'dividend_uid';
        $export_arr['分佣身份'] = 'role_name';
        $export_arr['奖项相关'] = 'award_name';
        $export_arr['佣金'] = 'dividend_amount';
        $export_arr['来源会员'] = 'buy_uid';
        $export_arr['状态'] = 'status';

        $page = 0;
        $page_size = 500;
        $page_count = 100;
        $title = join("\t", array_keys($export_arr)) . "\t";
        $lang = lang('order');
        $data = '';
        do {
            $rows = $this->Model->where($where)->limit($page * $page_size, $page_size)->select();
            if (empty($rows)) break;
            foreach ($rows as $row) {
                foreach ($export_arr as $val) {
                    if (strstr($val, '_time')) {
                        $data .= dateTpl($row[$val]) . "\t";
                    }elseif ($val == 'status'){
                        $data .= $lang['ds'][$row[$val]]. "\t";
                    } elseif ($val == 'dividend_uid') {
                        $data .= $row['dividend_uid'].'-'.userInfo($row['dividend_uid']). "\t";
                    } elseif ($val == 'buy_uid') {
                        $data .= $row['buy_uid'].'-'.userInfo($row['buy_uid']). "\t";
                    } elseif ($val == 'award_name') {
                        $data .= $row['award_name'].'-'.$row['level_award_name']. "\t";
                    }else{
                        $data .= str_replace(array("\r\n", "\n", "\r"), '', strip_tags($row[$val])) . "\t";
                    }
                }
                $data .= "\n";
            }
            $page++;
        } while ($page <= $page_count);

        $filename = '佣金明细_' . date("YmdHis") . '.xls';
        $filename = iconv('utf-8', 'GBK//IGNORE', $filename);
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=$filename");
        echo iconv('utf-8', 'GBK//IGNORE', $title . "\n" . $data) . "\t";
        exit;
    }
}
