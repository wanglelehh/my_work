<?php


namespace app\common;


use app\distribution\model\DividendModel;
use app\fightgroup\model\FightGroupModel;
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

class YdShare extends Command{

    // 团队分红
    protected function configure()
    {
        $this->setName('Yd_share')->setDescription('月度管理补贴');
    }

    // 团队分红
    protected function execute(Input $input, Output $output)
    {
        echo date('Y-m-d H:i:s');
        $OrderModel=new OrderModel();
        $OrderGoodsModel=new OrderGoodsModel();
        $GoodsModel=new GoodsModel();
        $UsersModel=new UsersModel();
        $DividendModel=new DividendModel();
        $AccountModel=new AccountModel();
        $RoleModel=new RoleModel();

        $max_id = $RoleModel->order('level ASC')->value('role_id'); //获取当前最高级别 的id

        $userIds=$UsersModel->where('role_id','>',19)->column('user_id');

        if(count($userIds)<1) return true;

        $time=time();
        Db::startTrans();
        foreach ($userIds as $key => $uid){
            $userInfo=$UsersModel->info($uid);
            if(empty($userInfo)) continue;
            $teamUid=$UsersModel->teamUid($uid);
            $team=$RoleModel->where('role_id',$userInfo['role_id'])->value('team');

            $team=json_decode($team,true);  //对应身份设置的补贴比例

            $owhere = [];
            $owhere[] = ['order_status','=',1];
            $owhere[] = ['add_time','>=',$userInfo['last_up_role_time']];
            $owhere[] = ['user_id','in',$teamUid];
            $orderIds=$OrderModel->where($owhere)->whereTime('pay_time','month')->column('order_id');
            if(count($orderIds)<1) continue;

            $goodsList=$OrderGoodsModel->where('order_id','in',$orderIds)->select();
            $total=0;  //总业绩
            foreach ($goodsList as $ke=>$ve){
                $goods=$GoodsModel->info($ve['goods_id']);
                $is_rolePrice = $GoodsModel->rolePrice($ve['goods_id'], $max_id); //商品联创的身份价;
                $price= empty($is_rolePrice) ? $goods['shop_price']:$is_rolePrice;
                $total += $price * $ve['goods_number'];
            }
            if($total>0){
                $reward1 = 0;  //获得比例
                foreach ($team as $t){
                    if($total >= $t['num1'] && $reward1 < $t['reward1']){
                        $reward1 = $t['reward1'];
                    }
                }
                $award=$total * $reward1 * 0.01;
                if($award > 0){
                    $inData = [];
                    $inData['order_id'] = 0;
                    $inData['buy_uid'] = 0;
                    $inData['dividend_uid'] = $userInfo['user_id'];
                    $inData['role_id'] = $userInfo['role']['role_id'];
                    $inData['role_name'] = $userInfo['role']['role_name'];
                    $inData['level'] = 1;
                    $inData['order_sn'] = 0;
                    $inData['award_name'] = '月度管理补贴';
                    $inData['order_amount']=0;
                    $inData['dividend_amount'] = $award;
                    $inData['add_time'] = $inData['update_time'] = $time;
                    $inData['status'] = 9;
                    $inData['is_type'] = 'yd_fh';

                    $res = $DividendModel->create($inData);
                    if ($res->log_id < 1) {
                        Db::rollback();
                        trace($userInfo['user_id'].'～补贴失败-----','debug');
                        echo $userInfo['user_id'].'～补贴失败-----';
                        break;
                    }
                    $changedata['change_desc'] = '月度管理补贴';
                    $changedata['change_type'] = 13;
                    $changedata['by_id'] = 0;
                    $changedata['from_uid'] = 0;
                    $changedata['balance_money'] = $award;
                    $changedata['total_dividend'] = $award;
                    $res1 = $AccountModel->change($changedata, $userInfo['user_id'], false);
                    if ($res1 !== true) {
                        Db::rollback();
                        trace($userInfo['user_id'].'～更新钱包失败-----','debug');
                        echo $userInfo['user_id'].'～更新钱包失败-----';
                        break;
                    }
                    echo '用户:'.$uid.'身份:'.$userInfo['role']['role_name'].'-业绩:'.$total.'-比例:'.$reward1.'-奖励:'.$award.'-----;';
                    trace('用户:'.$uid.'身份:'.$userInfo['role']['role_name'].'-业绩:'.$total.'-比例:'.$reward1.'-奖励:'.$award.'-----;','debug');
                }
            }
        }
        Db::commit();
        echo '全部成功';
    }
}