<?php

namespace app\channel\controller\api;
use app\channel\ApiController;


/*------------------------------------------------------ */
//-- 统计相关API
/*------------------------------------------------------ */

class Stat extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
    }

    protected function priceToW($price){
        if ($price > 10000){
            return sprintf("%.2f",$price / 10000).'万';
        }
        return $price;
    }
    /*------------------------------------------------------ */
    //-- 获取充值业绩
    /*------------------------------------------------------ */
    public function getMyRechargeStat(){
        $yearMonth = input('yearMonth','','trim');
        if (empty($yearMonth)){
            $yearMonth = date('Y-m');
        }
        $days = date('t', strtotime($yearMonth));
        $data['days'] = [];
        for ($i=1;$i<=$days;$i++){
            $start_time = strtotime($yearMonth.'-'.$i);
            if ($start_time > time()){
                break;
            }
            $data['days'][] = str_pad($i,2,"0",STR_PAD_LEFT);
        }
        $data['myTotal'] = 0;
        $data['teamTotal'] = 0;
        $RechargeLogModel = new \app\channel\model\RechargeLogModel();
        foreach ($data['days'] as $day){
            $uplevelGoodsMoney = 0;
            $goodsMoney = 0;
            $start_time = strtotime($yearMonth.'-'.$day);
            $timeWhere = [$start_time,$start_time+86399];
            $where = [];
            $where[] = ['user_pid','=',$this->userInfo['user_id']];
            $where[] = ['pay_time','between',$timeWhere];
            $where[] = ['status','=',9];
            $rows = $RechargeLogModel->group('type')->where($where)->column('sum(order_amount) as amount','type');
            foreach ($rows as $key=>$amount){
                if ($key == 'uplevelGoodsMoney'){
                    $uplevelGoodsMoney += $amount;
                    $data['teamTotal'] += $amount;
                }elseif ($key == 'goodsMoney'){
                    $goodsMoney += $amount;
                    $data['teamTotal'] += $amount;
                }
            }
            $where = [];
            $where[] = ['user_id','=',$this->userInfo['user_id']];
            $where[] = ['pay_time','between',$timeWhere];
            $where[] = ['status','=',9];
            $rows = $RechargeLogModel->group('type')->where($where)->column('sum(order_amount) as amount','type');
            foreach ($rows as $key=>$amount){
                if ($key == 'uplevelGoodsMoney'){
                    $uplevelGoodsMoney += $amount;
                    $data['myTotal'] += $amount;
                }elseif ($key == 'goodsMoney'){
                    $goodsMoney += $amount;
                    $data['myTotal'] += $amount;
                }
            }
            $data['series'][0][] = $uplevelGoodsMoney;
            $data['series'][1][] = $goodsMoney;
        }
        $data['total'] = $this->priceToW($data['myTotal']+$data['teamTotal']);
        $data['myTotal'] = $this->priceToW($data['myTotal']);
        $data['teamTotal'] = $this->priceToW($data['teamTotal']);
        return $this->success($data);
    }

    /*------------------------------------------------------ */
    //-- 获取订单业绩
    /*------------------------------------------------------ */
    public function getMyOrderStat(){
        $yearMonth = input('yearMonth','','trim');
        if (empty($yearMonth)){
            $yearMonth = date('Y-m');
        }
        $days = date('t', strtotime($yearMonth));
        $data['days'] = [];
        for ($i=1;$i<=$days;$i++){
            $start_time = strtotime($yearMonth.'-'.$i);
            if ($start_time > time()){
                break;
            }
            $data['days'][] = str_pad($i,2,"0",STR_PAD_LEFT);
        }
        $data['myTotal'] = 0;
        $data['teamTotal'] = 0;
        $OrderModel = new \app\channel\model\OrderModel();
        foreach ($data['days'] as $day){
            $cloudOrderMoney = 0;
            $entityOrderMoney = 0;
            $start_time = strtotime($yearMonth.'-'.$day);
            $timeWhere = [$start_time,$start_time+86399];
            $where = [];
            $where[] = ['user_pid','=',$this->userInfo['user_id']];
            $where[] = ['add_time','between',$timeWhere];
            $where[] = ['order_status','=',1];
            $where[] = ['purchase_type','in',[1,2]];
            $rows = $OrderModel->group('purchase_type')->where($where)->column('sum(order_amount) as amount','purchase_type');
            foreach ($rows as $key=>$amount){
                if ($key == 1){
                    $cloudOrderMoney += $amount;
                    $data['teamTotal'] += $amount;
                }elseif ($key == 2){
                    $entityOrderMoney += $amount;
                    $data['teamTotal'] += $amount;
                }
            }
            $where = [];
            $where[] = ['user_id','=',$this->userInfo['user_id']];
            $where[] = ['add_time','between',$timeWhere];
            $where[] = ['order_status','=',1];
            $where[] = ['purchase_type','=',1];
            $rows = $OrderModel->group('purchase_type')->where($where)->column('sum(order_amount) as amount','purchase_type');
            foreach ($rows as $key=>$amount){
                $cloudOrderMoney += $amount;
                $data['myTotal'] += $amount;
            }
            $data['series'][] = $cloudOrderMoney;
        }
        $data['total'] = $this->priceToW($data['myTotal']+$data['teamTotal']);
        $data['myTotal'] = $this->priceToW($data['myTotal']);
        $data['teamTotal'] = $this->priceToW($data['teamTotal']);
        return $this->success($data);
    }
}

