<?php
/**
 * Created by PhpStorm.
 * User: iqgmy
 * Date: 2020/10/23
 * Time: 2:02 PM
 */
namespace app\channel\model;
use app\BaseModel;
use think\Db;
//*------------------------------------------------------ */
//-- 奖励处理计算
/*------------------------------------------------------ */
class RewardLogRunModel extends BaseModel
{


    /*------------------------------------------------------ */
    //-- 订单奖项计算
    /*------------------------------------------------------ */
    public function runByOrder(&$orderInfo){
        $RewardModel = new RewardModel();
        $ChannelGoodsModel = new ChannelGoodsModel();
        $GoodsModel = new \app\shop\model\GoodsModel();
        $setting = settings();
        $time = time();

        if ($orderInfo['purchase_type'] >= 3){//提货订单不执行以下
            return true;
        }

        //计算出货代理的收益
        if ($orderInfo['supply_uid'] > 0){
            $totalPurchasePrice = 0;//进货总价
            $rewardDetail = [];

            //根据出货代理的层级，计算出货代理的成本价
            foreach ($orderInfo['goodsList'] as $goods){
                $_goods = $ChannelGoodsModel->where('goods_id',$goods['goods_id'])->field('price_type,pricing_type')->find();
                if ($goods['sku_id'] > 0){
                    $goods['is_spec'] = 1;
                }else{
                    $goods['shop_price'] = $GoodsModel->where('goods_id',$goods['goods_id'])->value('shop_price');
                }
                $goods['price_type'] = $_goods['price_type'];
                $goods['pricing_type'] = $_goods['pricing_type'];

                $prices = $ChannelGoodsModel->getPrcies($goods,$orderInfo['supply_role_id']);//获取出货代理的进货价

                $purchasePrice = $prices[$goods['sku_id']];
                $purchasePriceTotal = $purchasePrice['price'] * $goods['real_number'];
                $totalPurchasePrice += $purchasePriceTotal;
                $detail = '商品:'.$goods['goods_name'];
                if ($goods['sku_id'] > 0){
                    $detail .= " - ".$goods['sku_name'];
                }
                $salePriceTotal = $goods['sale_price'] * $goods['real_number'];
                $diffPriceTotal = $salePriceTotal - $purchasePriceTotal;
                $detail .= '_出货价:'.$goods['sale_price'].'_进货价:'.$purchasePrice['price'].'_数量:'.$goods['real_number'];
                $detail .= '_出货收益:'.$salePriceTotal;
                $detail .= '_差价收益:'.$diffPriceTotal;
                $rewardDetail[] = $detail;
            }
            $inData['reward_type'] = 'purchase';
            $inData['reward_name'] = '出货收益';
            $inData['reward_info'] =  join('，',$rewardDetail);
            if ($orderInfo['shipping_fee'] > 0){
                $inData['reward_info'] .= '到帐收益中包括运费:'.$orderInfo['shipping_fee'];
            }
            $inData['reward_money'] = $orderInfo['order_amount'];
            $inData['is_log'] = 0;
            if ($setting['channel_receive_payment_set'] == 1 && $orderInfo['pay_code'] == 'offline'){ //非平台统一收款，线下收款是打款给供货代理，只记录奖项
                $inData['is_log'] = 1;
                $inData['reward_info'] .= '，供货代理已线下收款';
            }

            $inData['to_uid'] = $orderInfo['supply_uid'];
            $inData['to_role_id'] = $orderInfo['supply_role_id'];
            $inData['from_uid'] = $orderInfo['user_id'];
            $inData['from_role_id'] = $orderInfo['user_role_id'];
            $inData['from_type'] = 'order';
            $inData['from_id'] = $orderInfo['order_id'];
            if ($inData['is_log'] == 1){//只记录时
                $inData['status'] = 2;//已返，供货代理已线下收款
            }elseif ($orderInfo['purchase_type'] == 1){//云仓立返
                $inData['status'] = 1;//待返
            }else{//签收后返
                $inData['status'] = 0;
            }
            $inData['add_time'] = $time;
            $res = $this->create($inData);
            if ($res < 1){
                return '计算出货代理的收益失败';
            }
        }
        //计算出货代理的收益end

        //如果出货代理与推荐者不一致，推荐者享受补货奖励
        if ($orderInfo['user_pid'] > 0 && $orderInfo['user_pid'] != $orderInfo['supply_uid']){
            $reward = $RewardModel->where('reward_type','bhjl')->find();
            $rewardInfo = json_decode($reward['info'],true);
            $rewardInfo = $rewardInfo[$orderInfo['p_role_id']];
            $rewardPer = $rewardInfo[$orderInfo['user_role_id']];
            $inData['reward_type'] = 'bhjl';
            $inData['reward_name'] = '推荐下级补贴奖奖励';
            $inData['reward_info'] = '商品金额：'.$orderInfo['goods_amount'].'，奖励：'.$rewardPer.'%';
            $inData['reward_money'] = $orderInfo['goods_amount'] / 100 * $rewardPer;
            $inData['to_uid'] = $orderInfo['user_pid'];
            $inData['to_role_id'] = $orderInfo['p_role_id'];
            $inData['from_uid'] = $orderInfo['user_id'];
            $inData['from_role_id'] = $orderInfo['user_role_id'];
            $inData['from_type'] = 'order';
            $inData['from_id'] = $orderInfo['order_id'];
            if ($setting['channel_receive_payment_set'] == 1 && $orderInfo['pay_code'] == 'offline'){ //非平台统一收款，线下收款是打款给供货代理，只记录奖项
                $inData['payer_uid'] = $orderInfo['supply_uid'];//记录奖励支付人，0为平台
            }

            if ($orderInfo['purchase_type'] == 1){//云仓立返
                $inData['status'] = 1;//待返
            }else{//签收后返
                $inData['status'] = 0;
            }
            $inData['add_time'] = $time;
            $res = $this->create($inData);
            if ($res < 1){
                return '计算补货奖励失败';
            }
        }
        //推荐者享受补货奖励end
        return true;
    }
    /*------------------------------------------------------ */
    //-- 计算创业金 根据升级时间每10天计算一次
    //-- $istest bool 是否测试
    /*------------------------------------------------------ */
    public function runCyj($istest = false)
    {
        $RewardModel = new RewardModel();
        $reward = $RewardModel->where('reward_type','cyj')->find();
        $rewardInfo = json_decode($reward['info'],true);
        $proxy_ids = [];
        //查询所有参与创业金的代理层级
        foreach ($rewardInfo as $key=>$proxy){
            if ($proxy['is_join'] == 1){
                $role_ids[] = $key;
            }
        }
        if (empty($role_ids) == true){
            return true;//没有找到终止
        }
        $ProxyUsersModel = new ProxyUsersModel();
        $WalletModel = new WalletModel();
        $RechargeLogModel = new RechargeLogModel();
        $where = [];
        $where[] = ['role_id','IN',$role_ids];
        $userList = $ProxyUsersModel->where($where)->select()->toArray();

        $time = time();
        $start_time = strtotime(date('Y/m/01', strtotime("-1 months")));
        $end_time =  strtotime(date('Y/m/01')) - 1;
        foreach ($userList as $user){
            if ($user['uplevel_time'] > $start_time){//没有最近执行时间，使用升级时间计算
                $runTime = $user['uplevel_time'];
            }else{
                $runTime = $start_time;
            }

            if ($istest == true){//测试时使用
                $endTime = $time;
            }else{
                $endTime = $end_time;
            }
            $where = [];
            $where[] = ['user_pid','=',$user['user_id']];
            $where[] = ['status','=',9];
            $where[] = ['pay_time','between',[$runTime,$endTime]];
            $totalMoney = $RechargeLogModel->where($where)->SUM('order_amount');
            $distance = $rewardInfo[$user['proxy_id']]['distance'];
            $rewardPer = 0;
            foreach ($distance['min'] as $key=>$min){
                $min = $min * 10000;
                $max = $distance['max'][$key] * 10000;
                if ($totalMoney >= $min){
                    if ($totalMoney < $max){
                        $rewardPer = $distance['val'][$key];
                    }elseif($totalMoney >= $max){
                        $rewardPer = $distance['val'][$key];
                    }
                }
            }

            $inData['reward_type'] = 'cyj';
            $inData['reward_name'] = '创业金';
            if ($istest == true){
                $inData['reward_name'] .= 'by测试';
            }
            $inData['reward_info'] = '直推升级货款汇总:'.date('Y-m-d',$runTime).'至'.date('Y-m-d',$endTime)."，合计充值：".$totalMoney.'，奖励：'.$rewardPer.'%';
            $inData['reward_money'] = $totalMoney / 100 * $rewardPer;
            $inData['to_uid'] = $user['user_id'];
            $inData['to_role_id'] = $user['proxy_id'];
            $inData['from_type'] = 'recharge';
            $inData['status'] = 2;//立即到帐
            $inData['add_time'] = $time;
            $res = $this->create($inData);
            if ($res->id < 1){
                return '计算创业金奖励失败';
            }

            if ($inData['reward_money'] > 0){
                $changedata['change_desc'] = '创业金-'.$inData['reward_info'];
                $changedata['change_type'] = 9;
                $changedata['balance_money'] =  $inData['reward_money'];
                $changedata['total_brokerage'] = $inData['reward_money'];
                $res = $WalletModel->change($changedata, $user['user_id'],false);
                if ($res !== true) {
                    return '更新代理帐户失败.';
                }
            }

        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 计算月分红 根据升级时间每月计算一次
    //-- $istest bool 是否测试
    /*------------------------------------------------------ */
    public function runYfh($istest = false)
    {
        $RewardModel = new RewardModel();
        $reward = $RewardModel->where('reward_type','yfh')->find();
        $rewardInfo = json_decode($reward['info'],true);
        $join_role_id = [];//参与月分红的代理层级
        foreach ($rewardInfo as $key=>$info){
            if ($info['is_join'] == 1){
                $join_role_id[] = $key;
            }
        }
        $ProxyUsersModel = new ProxyUsersModel();
        $where = [];
        $where[] = ['role_id','in',$join_role_id];
        $where[] = ['status','=',1];
        $users = $ProxyUsersModel->where($where)->column('user_id,pid,role_id');


        //时间处理
        if ($istest == true) {//测试时使用
            $start_time = strtotime(date('Y/m/01', strtotime("-1 months")));
            $end_time = time();
        }else{
            $start_time = strtotime(date('Y/m/01', strtotime("-1 months")));
            $end_time =  strtotime(date('Y/m/01')) - 1;
        }
        $timeWhere = [$start_time,$end_time];

        $WalletModel = new WalletModel();
        $RechargeLogModel = new RechargeLogModel();
        $OrderModel = new OrderModel();
        $UsersBindSuperiorModel = new \app\member\model\UsersBindSuperiorModel();
        $stat = [];//统计结果
        $pUsers = [];
        foreach ($users as $user){
            $pUsers[$user['pid']][] = $user['user_id'];
            $where = [];
            $where[] = ['', 'exp', Db::raw("FIND_IN_SET('" . $user['user_id'] . "',superior)")];
            $uIds = $UsersBindSuperiorModel->where($where)->column('user_id');

            //统计升级货款
            $where = [];
            $where[] = ['user_id','IN',$uIds];
            $where[] = ['type','=','uplevelGoodsMoney'];
            $where[] = ['pay_time','between',$timeWhere];
            $where[] = ['status','=',9];
            $uplevelGoodsMoney = $RechargeLogModel->where($where)->sum('order_amount');

            //统计出货订单(云仓+现货)
            $where = [];
            $where[] = ['user_id','IN',$uIds];
            $where[] = ['purchase_type','in',[1,2]];
            $where[] = ['order_status','=',1];
            $where[] = ['add_time','between',$timeWhere];
            $order_amount = $OrderModel->where($where)->sum('goods_amount');

            $distance = $rewardInfo[$user['proxy_id']]['distance'];
            $totalMoney = $order_amount + $uplevelGoodsMoney;
            $rewardPer = 0;//计算比例
            foreach ($distance['min'] as $key=>$min){
                $min = $min * 10000;
                $max = $distance['max'][$key] * 10000;
                if ($totalMoney >= $min){
                    if ($totalMoney < $max){
                        $rewardPer = $distance['val'][$key];
                    }elseif($totalMoney >= $max){
                        $rewardPer = $distance['val'][$key];
                    }
                }
            }
            $stat[$user['user_id']]['role_id'] = $user['role_id'];
            $stat[$user['user_id']]['uplevelGoodsMoney'] = $uplevelGoodsMoney;
            $stat[$user['user_id']]['order_amount'] = $order_amount;
            $stat[$user['user_id']]['rewardPer'] = $rewardPer;
            $stat[$user['user_id']]['totalMoney'] = $totalMoney;
            $stat[$user['user_id']]['distanceMoney'] = $totalMoney / 100 * $rewardPer;
        }
        $time = time();
        foreach ($stat as $uid=>$row){
            $reward_money = $row['distanceMoney'];
            $log = "，减去直推同级明细：";
            $cutLog = [];
            foreach ($pUsers[$uid] as $sameUid){
                $reward_money -= $stat[$sameUid]['distanceMoney'];
                $_log = '【用户ID：'.$sameUid."】总业绩：￥".$stat[$sameUid]['totalMoney'];
                $_log .= '，对应业绩区间的比例：'.$stat[$sameUid]['rewardPer'].'%，共：￥'.$stat[$sameUid]['distanceMoney'];
                $cutLog[] = $_log;
            }
            if ($reward_money < 0){
                $reward_money = 0;
            }
            if (empty($cutLog) == false){
                $log .= join('，',$cutLog);
            }else{
                $log .= '【无直推同级】';
            }
            $inData['reward_type'] = 'yfh';
            $inData['reward_name'] = '月分红';
            if ($istest == true){
                $inData['reward_name'] .= 'by测试';
            }
            $inData['reward_info'] = '时间:'.date('Y-m-d H:i:s',$start_time).'至'.date('Y-m-d H:i:s',$end_time);
            $inData['reward_info'] .= "，本人和团队的总业绩：￥".$row['totalMoney'];
            $inData['reward_info'] .= "，对应业绩区间的比例：".$row['rewardPer'].'%';
            $inData['reward_info'] .= $log;
            $inData['reward_info'] .= '，实得：￥'.$reward_money;
            $inData['reward_money'] = $reward_money;
            $inData['to_uid'] = $uid;
            $inData['to_role_id'] = $row['role_id'];
            $inData['from_type'] = 'order_recharge';
            $inData['status'] = 2;//立即到帐
            $inData['add_time'] = $time;
            $res = $this->create($inData);
            if ($res->id < 1){
                return '计算月分红奖励失败';
            }
            if ($inData['reward_money'] > 0){
                $changedata['change_desc'] = '月分红';
                $changedata['change_type'] = 9;
                $changedata['balance_money'] =  $inData['reward_money'];
                $changedata['total_brokerage'] = $inData['reward_money'];
                $res = $WalletModel->change($changedata, $uid,false);
                if ($res !== true) {
                    return '更新代理帐户失败.';
                }
            }
        }
        return true;
    }
}