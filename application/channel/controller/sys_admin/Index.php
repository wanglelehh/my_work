<?php

namespace app\channel\controller\sys_admin;

use app\AdminController;
use think\facade\Cache;

use app\channel\model\WalletModel;
use app\channel\model\RewardLogModel;
use app\channel\model\RechargeLogModel;
use app\channel\model\WithdrawLogModel;
use app\channel\model\StockModel;
use app\channel\model\OrderModel;

use app\member\model\UsersModel;
use app\member\model\RoleModel;

/**
 * 渠道代理首页
 * Class Index
 * @package app\store\controller
 */
class Index extends AdminController
{

    //*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    public function initialize($isretrun = true)
    {
        parent::initialize($isretrun);
    }

    /*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index(){
        $this->isClearSetTip();//清理数据提示
        $this->stats();
        return $this->fetch();
    }

    /*------------------------------------------------------ */
    //-- 获取信息汇总
    /*------------------------------------------------------ */
    public function stats(){
        $start_day = date("Y-m-d",strtotime("-1 week"));
        $this->assign('start_day',$start_day);
        $end_day = date('Y-m-d', strtotime("+1 day") );
        $this->assign('end_day',$end_day);
        $dt_start = strtotime($start_day);
        $today = strtotime(date("Y-m-d"));
        $this->assign('today',date('Y_m_d',$today));
        $yesterday = strtotime('-1 day',$today);
        $this->assign('yesterday',date('Y_m_d',$yesterday));
        $riqi = [];

        //帐户统计
        $WalletModel = new WalletModel();
        $account['balance_money'] = priceToW($WalletModel->sum('balance_money'));
        $account['withdraw_money_total'] = priceToW($WalletModel->sum('withdraw_money_total'));
        $account['total_goods_money'] = priceToW($WalletModel->sum('goods_money'));
        $this->assign('account',$account);
        //end
        //云仓总库存
        $cloud_goods_number = (new StockModel)->where('purchase_type',1)->sum('goods_number');
        $this->assign('cloud_goods_number',priceToW($cloud_goods_number));

        $UsersModel = new UsersModel();
        //代理统计
        $roleTotal = $UsersModel->where('is_channel',1)->group('role_id')->column('count(user_id) as num','role_id');
        $this->assign('roleTotal',$roleTotal);
        $this->assign('roleList',(new RoleModel)->getRows(1));
        //充值待审
        $where = [];
        $where[] = ['status','=',0];
        $where[] = ['pay_code','=','offline'];
        $rchargeLogNum = (new RechargeLogModel)->where($where)->count('order_id');
        $this->assign('rechargeLogNum',$rchargeLogNum);
        //提现待审
        $withdrawLogNum = (new WithdrawLogModel)->where('status',0)->count('log_id');
        $this->assign('withdrawLogNum',$withdrawLogNum);

        $this->OrderModel = new orderModel();
        $this->RechargeLogModel = new RechargeLogModel();
        $this->RewardLogModel = new RewardLogModel();
        //现货订单待发货
        $where = [];
        $where[] = ['purchase_type','=',2];
        $where[] = ['order_status','=',1];
        $where[] = ['shipping_status','=',0];
        $shipinngStats['entity_order'] = $this->OrderModel->where($where)->count();
        //提货订单待发货
        $where = [];
        $where[] = ['purchase_type','=',3];
        $where[] = ['order_status','=',1];
        $where[] = ['shipping_status','=',0];
        $shipinngStats['pickup_order'] = $this->OrderModel->where($where)->count();


        //订单统计相关
        $stats = [];
        $i = 0;
        $stats['seven_cloud_order_num'] = 0;
        $stats['seven_cloud_order_amount'] = 0;

        $stats['seven_pickup_order_num'] = 0;
        $stats['seven_pickup_order_amount'] = 0;

        $stats['seven_goods_money'] = 0;
        $stats['seven_brokerage'] = 0;

        while ($dt_start <= strtotime($end_day)) {
            $riqi[] = date('Y-m-d', $dt_start);
            $searchtime = $dt_start . ',' . ($dt_start + 86399);
            $data = $this->orderStats($searchtime);
            $mdata = $this->moneyStats($searchtime);
            if ($dt_start == $today) {
                $stats['today']['cloud_order_num'] = $data['cloud_order_num'] * 1;
                $stats['today']['shop_order_num'] = $data['shop_order_num'] * 1;
                $stats['today']['pickup_order_num'] = $data['pickup_order_num'] * 1;
            }

            if ($dt_start < strtotime($end_day)) {
                $stats['seven_cloud_order_num'] += $data['cloud_order_num'] * 1;
                $stats['seven_cloud_order_amount'] += $data['cloud_order_amount'] * 1;
                $stats['seven_cloud_order_arr'][$i][] = $data['cloud_order_amount'] * 1;


                $stats['seven_pickup_order_num'] += $data['pickup_order_num'] * 1;
                $stats['seven_pickup_order_amount'] += $data['pickup_order_amount'] * 1;
                $stats['seven_pickup_order_arr'][$i][] = $data['pickup_order_amount'] * 1;

                $stats['seven_goods_money'] += $mdata['goods_money'] * 1;
                $stats['seven_brokerage'] += $mdata['brokerage'] * 1;

                $stats['seven_goods_money_arr'][$i][] = $mdata['goods_money'] * 1;
                $stats['seven_brokerage_arr'][$i][] = $mdata['brokerage'] * 1;
            }
            $dt_start = strtotime('+1 day', $dt_start);
            $i++;
        }

        $stats['seven_cloud_order_amount'] = priceToW($stats['seven_cloud_order_amount']);
        $stats['seven_entity_order_amount'] = priceToW($stats['seven_entity_order_amount']);

        $stats['seven_goods_money'] = priceToW($stats['seven_goods_money']);
        $stats['seven_brokerage'] = priceToW($stats['seven_brokerage']);

        //订单统计相关end
        $this->assign('stats', $stats);
        $this->assign('riqi', json_encode($riqi));
    }
    /*------------------------------------------------------ */
    //-- 获取订单信息汇总
    /*------------------------------------------------------ */
    public function orderStats($timeWhere = [])
    {

        $mkey = 'channel_order_stat'.md5(json_encode($timeWhere));
        $info = Cache::get($mkey);
        if (empty($info) == false) return $info;
        $where[] = ['add_time','between',$timeWhere];
        $where[] = ['order_status','=',1];
        $rows = $this->OrderModel->field('order_amount,purchase_type')->where($where)->select();
        $info['cloud_order_num'] = 0;
        $info['cloud_order_amount'] = 0;
        $info['entity_order_num'] = 0;
        $info['entity_order_amount'] = 0;
        $info['shop_order_num'] = 0;
        $info['shop_order_amount'] = 0;
        $info['pickup_order_num'] = 0;
        foreach ($rows as $row){
            if ($row['purchase_type'] == 1){
                $info['cloud_order_num']+=1;
                $info['cloud_order_amount'] += $row['order_amount'];
            }elseif ($row['purchase_type'] == 3){//提货订单
                $info['pickup_order_num'] += 1;
            }
        }

        Cache::set($mkey, $info, 20);
        return $info;
    }
    /*------------------------------------------------------ */
    //-- 获取金额信息汇总
    /*------------------------------------------------------ */
    public function moneyStats($timeWhere = [])
    {
        $mkey = 'channel_money_stat'.md5(json_encode($timeWhere));
        $info = Cache::get($mkey);
        if (empty($info) == false) return $info;
        $where[] = ['pay_time','between',$timeWhere];
        $where[] = ['status','=',9];
        $rows = $this->RechargeLogModel->field('order_amount,type')->where($where)->select();
        $info['uplevel_goods_money'] = 0;
        $info['goods_money'] = 0;
        $info['brokerage'] = 0;
        foreach ($rows as $row){
            if ($row['type'] == 'uplevelGoodsMoney'){
                $info['uplevel_goods_money'] += $row['order_amount'];
            }elseif ($row['type'] == 'goodsMoney'){
                $info['goods_money'] += $row['order_amount'];
            }
        }
        $where = [];
        $where[] = ['add_time','between',$timeWhere];
        $where[] = ['status','IN',[1,2]];
        $rows = $this->RewardLogModel->field('reward_type,reward_money')->where($where)->select();
        foreach ($rows as $row){
            if ($row['reward_type'] == 'purchase'){
                continue;
            }
            $info['brokerage'] += $row['reward_money'];
        }
        Cache::set($mkey, $info, 20);
        return $info;
    }
}
