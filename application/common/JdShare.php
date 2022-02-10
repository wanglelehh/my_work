<?php


namespace app\common;


use app\distribution\model\DividendModel;
use app\mainadmin\model\SettingsModel;
use app\member\model\AccountModel;
use app\member\model\RoleModel;
use app\member\model\UsersModel;
use app\shop\model\GoodsModel;
use app\shop\model\OrderGoodsModel;
use app\shop\model\OrderModel;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class JdShare extends Command
{

    // 团队分红
    protected function configure()
    {
        $this->setName('Jd_share')->setDescription('季度市场补贴');
    }

    // 季度分红
    protected function execute(Input $input, Output $output)
    {

        echo date('Y-m-d H:i:s');


        //按照默认时间走
        $month = date('m', strtotime("-1 month"));     //上个月
//        $day = date('y', time());      //年份
        //季度时间段


        $start_time = strtotime(date('Y-m-01', strtotime("-3 month")));//开始时间
        $end_time = strtotime(date('Y-m-01', time())) - 1;//结束时间
        //季度时间段 end

        //判断是否需要结算
        if ($month % 3 != 0) {
            echo "未到季度结算时间";
            return true;
        }

        $OrderModel=new OrderModel();
        $OrderGoodsModel=new OrderGoodsModel();
        $GoodsModel=new GoodsModel();
        $UsersModel=new UsersModel();
        $DividendModel=new DividendModel();
        $AccountModel=new AccountModel();
        $RoleModel=new RoleModel();
        $SettingsModel=new SettingsModel();

        $max_id = $RoleModel->order('level ASC')->value('role_id'); //获取当前最高级别 的id

        $userIds=$UsersModel->where('role_id','>',19)->column('user_id');

        if(count($userIds)<1) return true;

        $performance=settings('team_performance');
        $allAward=settings('team_pool');

        $time=time();

        $get_uid=array();
        foreach ($userIds as $key => $uid){
            $userInfo=$UsersModel->info($uid);

            if(empty($userInfo)) continue;
            if($userInfo['last_up_role_time']>$end_time) continue;      //如果在季度时间段之后才升级  跳过

            $teamUid=$UsersModel->teamUid($uid);  //团队用户id（包括自己）

            if($userInfo['last_up_role_time']>$start_time && $userInfo['last_up_role_time']<$end_time){   //如果在这个时间段升级了
                $start_time=$userInfo['last_up_role_time'];                  //开始时间  设置为  升级时的时间
            }
            $owhere = [];
            $owhere[] = ['order_status','=',1];
            $owhere[] = ['add_time','between',[$start_time,$end_time]];
            $owhere[] = ['user_id','in',$teamUid];
            $orderIds=$OrderModel->where($owhere)->column('order_id');     //符合条件的订单
            if(count($orderIds)<1) continue;      //没有订单，跳过

            $goodsList=$OrderGoodsModel->where('order_id','in',$orderIds)->select();      //订单商品信息
            $total=0;  //总业绩
            foreach ($goodsList as $ke=>$ve){
                $goods=$GoodsModel->info($ve['goods_id']);
                $is_rolePrice = $GoodsModel->rolePrice($ve['goods_id'], $max_id); //商品联创的身份价;
                $price= empty($is_rolePrice) ? $goods['shop_price']:$is_rolePrice;
                $total += $price * $ve['goods_number'];
            }
            if($total>=$performance){
                $get_uid[]=$userInfo;
            }
        }
        if(count($get_uid)<1) return true; //没有平分用户
        $award=$allAward/$get_uid;
        if($award<=0) return true;

        Db::startTrans();
        foreach ($get_uid as $vid){
            $inData = [];
            $inData['order_id'] = 0;
            $inData['buy_uid'] = 0;
            $inData['dividend_uid'] = $vid['user_id'];
            $inData['role_id'] = $vid['role']['role_id'];
            $inData['role_name'] = $vid['role']['role_name'];
            $inData['level'] = 1;
            $inData['order_sn'] = 0;
            $inData['award_name'] = '季度市场补贴';
            $inData['order_amount']=0;
            $inData['dividend_amount'] = $award;
            $inData['add_time'] = $inData['update_time'] = $time;
            $inData['status'] = 9;
            $inData['is_type'] = 'jd_fh';

            $res = $DividendModel->create($inData);
            if ($res->log_id < 1) {
                Db::rollback();
                trace($vid['user_id'].'～补贴失败-----','debug');
                echo $vid['user_id'].'～补贴失败-----';
                break;
            }
            $changedata['change_desc'] = '月度管理补贴';
            $changedata['change_type'] = 13;
            $changedata['by_id'] = 0;
            $changedata['from_uid'] = 0;
            $changedata['balance_money'] = $award;
            $changedata['total_dividend'] = $award;
            $res1 = $AccountModel->change($changedata, $vid['user_id'], false);
            if ($res1 !== true) {
                Db::rollback();
                trace($vid['user_id'].'～更新钱包失败-----','debug');
                echo $vid['user_id'].'～更新钱包失败-----';
                break;
            }
            echo '用户:'.$vid['user_id'].'身份:'.$vid['role']['role_name'].'-奖励:'.$award.'-----;';
            trace('用户:'.$vid['user_id'].'身份:'.$vid['role']['role_name'].'-奖励:'.$award.'-----;','debug');
        }

        $res=$SettingsModel->where('name','team_pool')->update(['data' =>0]);
        if(!$res){
            Db::rollback();
            echo '重置失败';
        }
        Db::commit();
        echo '全部成功';
    }
}