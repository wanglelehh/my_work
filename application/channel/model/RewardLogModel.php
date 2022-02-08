<?php
/**
 * Created by PhpStorm.
 * User: iqgmy
 * Date: 2020/10/23
 * Time: 2:02 PM
 */
namespace app\channel\model;
use think\facade\Cache;
use think\Db;
//*------------------------------------------------------ */
//-- 奖励记录
/*------------------------------------------------------ */
class RewardLogModel extends RewardLogRunModel
{
    protected $table = 'channel_reward_log';
    public $pk = 'id';
    public $status = ['0'=>'待处理','1'=>'待返','2'=>'已返','9'=>'取消'];

    /*------------------------------------------------------ */
    //-- 获取订单奖励明细
    //-- $order_id int 订单ID
    /*------------------------------------------------------ */
    public function getOrderRewardLog($order_id)
    {
        $mkey = 'channel_order_reward_list_'.$order_id;
        $list = Cache::get($mkey);
        if (empty($list) == false){
            return $list;
        }
        $where[] = ['from_type','=','order'];
        $where[] = ['from_id','=',$order_id];
        return $this->where($where)->select()->toArray();
    }

    /*------------------------------------------------------ */
    //-- 执行订单奖励为待返
    //-- $order_id int 订单ID
    /*------------------------------------------------------ */
    public function runRewardUpByOrder($order_id)
    {
        $mkey = 'runRewardUpByOrder_'.$order_id;
        $res = redisLook($mkey,60);//redis锁
        if ($res == false) return '正在执行中，请销后再试.';
        $where[] = ['from_type','=','order'];
        $where[] = ['from_id','=',$order_id];
        $where[] = ['status','=',0];
        $this->where($where)->update(['status'=>1]);
        redisLook($mkey,-1);//销毁锁
        return true;
    }

    /*------------------------------------------------------ */
    //-- 执行订单奖励待返到帐
    //-- $order_id int 订单ID
    /*------------------------------------------------------ */
    public function runRewardToUserByOrder($order_id)
    {
        $mkey = 'runRewardToUserByOrder_'.$order_id;
        $res = redisLook($mkey,60);//redis锁
        if ($res == false) return '正在执行中，请销后再试.';
        $where[] = ['from_type','=','order'];
        $where[] = ['from_id','=',$order_id];
        $where[] = ['status','=',1];
        $where[] = ['payer_uid','=',0];//平台付款的才处理
        $rows = $this->where($where)->select()->toArray();
        $time = time();
        if (empty($rows) == false){
            $WalletModel = new WalletModel();
            foreach ($rows as $row){
                $changedata['change_desc'] = $row['reward_name'];
                $changedata['change_type'] = 9;
                $changedata['balance_money'] = $row['reward_money'];
                $changedata['total_brokerage'] = $row['reward_money'];
                $changedata['by_id'] = $row['from_id'];
                $res = $WalletModel->change($changedata, $row['to_uid'],false);
                if ($res !== true) {
                    return '更新帐户信息失败.';
                }
                $res = $this->where('id',$row['id'])->update(['status'=>2,'update_time'=>$time]);
                if ($res < 1) {
                    return '更新奖励状态失败.';
                }
            }
        }
        redisLook($mkey,-1);//销毁锁
        return true;
    }

    /*------------------------------------------------------ */
    //-- 执行奖励退回处理
    //-- $from_type string 来源
    //-- $type string 类型，return 退回, cencal 订单取消
    //-- $by_id int 相关ID
    /*------------------------------------------------------ */
    public function returnReward($from_type = '',$by_id,$type = '',$desc = ''){
        $mkey = 'returnReward_'.$from_type.'_'.$by_id;
        $res = redisLook($mkey,60);//redis锁
        if ($res == false) return '正在执行中，请销后再试';
        //查询已到帐的
        $where[] = ['from_type','=',$from_type];
        $where[] = ['from_id','=',$by_id];
        $rows = $this->where($where)->select()->toArray();
        if (empty($rows)){
            redisLook($mkey,-1);//销毁锁
            return true;
        }
        $WalletModel = new WalletModel();
        $time = time();
        foreach ($rows as $row) {
            if ($row['status'] == 2){//已到帐的，扣回奖励
                $changedata['change_desc'] = $desc.$row['reward_name'];
                $changedata['by_id'] = $row['from_id'];
                $changedata['change_type'] = 9;
                $changedata['balance_money'] = $row['reward_money'] * -1;
                $changedata['total_brokerage'] = $row['reward_money'] * -1;
                $res = $WalletModel->change($changedata, $row['to_uid'],false);
                if ($res !== true) {
                    return '更新帐户信息失败.';
                }
            }
            if ($type == 'return'){//退回
                $res = $this->where('id',$row['id'])->update(['status'=>1,'update_time'=>$time]);
            }else{//取消
                $res = $this->where('id',$row['id'])->update(['status'=>9,'update_time'=>$time]);
            }
            if ($res < 1) {
                return '更新奖励状态失败.';
            }
        }
        redisLook($mkey,-1);//销毁锁
        return true;
    }
}