<?php


namespace app\common;


use app\distribution\model\DividendModel;
use app\fightgroup\model\FightGroupModel;
use app\mainadmin\model\SettingsModel;
use app\member\model\AccountModel;
use app\member\model\RoleModel;
use app\member\model\UsersBindSuperiorModel;
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
        $UsersBindSuperiorModel = new UsersBindSuperiorModel();
        $DividendModel=new DividendModel();
        $AccountModel=new AccountModel();
        $RoleModel=new RoleModel();

        $max_id = $RoleModel->order('level ASC')->value('role_id'); //获取当前最高级别 的id

        $userIds=$UsersModel->where('role_id','>',19)->column('user_id');

        if(count($userIds)<1) return true;

        $time=time();
        $findTime='last month';
        Db::startTrans();
        foreach ($userIds as $key => $uid){
            $my_total = $OrderModel->getTotalOrderMoney($uid,$findTime);   //自己总业绩
            if ($my_total == 0) continue;
            $usersIds = $UsersModel->getTeamUsers($uid);
            $arr['delUserIds'] = []; //需要剔除的会员ID
            foreach ($usersIds as $value){
                if ($value == $uid || in_array($value,$arr['delUserIds'])) continue;
                if (($UsersModel->where('user_id',$value)->value('role_id'))<20) continue;
                $user_total = $OrderModel->getTotalOrderMoney($value,$findTime);
                $my_total -= $user_total;
                if ($user_total > 0){
                    $uwhere = [];
                    $uwhere[] = ['', 'exp', Db::raw("FIND_IN_SET('" . $value . "',superior)")];
                    $delIds = $UsersBindSuperiorModel->where($uwhere)->column('user_id');
                    $arr['delUserIds'] = array_merge($arr['delUserIds'],$delIds);
                }
            }
            $userInfo=$UsersModel->info($uid);
            $award=$my_total;
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
                echo '用户:'.$uid.'身份:'.$userInfo['role']['role_name'].'-奖励:'.$award.'-----;';
                trace('用户:'.$uid.'身份:'.$userInfo['role']['role_name'].'-奖励:'.$award.'-----;','debug');
            }
        }
        Db::commit();
        echo '全部成功';
    }
}