<?php

namespace app\channel\model;
use app\BaseModel;
use think\Db;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 充值日志
/*------------------------------------------------------ */
class RechargeLogModel extends BaseModel
{
	protected $table = 'channel_proxy_users_recharge_log';
	public $pk = 'order_id';
    public $status = ['0'=>'待审核','1'=>'审核拒绝','8'=>'已支付','9'=>'已完成'];
    public $typeList = ['earnestMoney'=>'保证金','goodsMoney'=>'货款'];
    /*------------------------------------------------------ */
    //-- 充值支付成功处理
    /*------------------------------------------------------ */
    public function updatePay($order_id,$check_remark = '',$transaction_id = ''){
        $upData['status'] = 8;//待处理，异步处理，以防出错，影响在线支付回调
        $upData['transaction_id'] = $transaction_id;
        $upData['check_remark'] = $check_remark;
        $upData['check_time'] = time();
        $upData['pay_time'] = time();
        $res = $this->where('order_id',$order_id)->update($upData);
        if ($res < 1){
            return '操作失败，请重试.';
        }
        asynRun('channel/RechargeLogModel/asynRunPaySuccessEval',['order_id'=>$order_id]);//异步执行
        return true;
    }
    /*------------------------------------------------------ */
    //-- 执行待异步处理的充值订单到帐
    /*------------------------------------------------------ */
    public function runPaySuccessEval(){
        $order_ids = $this->where('status',8)->column('order_id');
        foreach ($order_ids as $order_id){
            asynRun('channel/RechargeLogModel/asynRunPaySuccessEval',['order_id'=>$order_id]);//异步执行
        }
    }
    /*------------------------------------------------------ */
    //-- 支付成功后异步处理
    /*------------------------------------------------------ */
    public function asynRunPaySuccessEval($param){
        $order_id = $param['order_id'] * 1;//异步执行传入必须强制类型
        if ($order_id < 1){
            return '缺少订单ID';
        }
        $mkey = 'rechargeChannelSuccessEvalIng'.$order_id;
        $res = redisLook($mkey,10);//redis锁
        if ($res == false) return true;

        Db::startTrans();//开启事务
        $orderInfo = $this->find($order_id);
        if ($orderInfo['type'] == 'earnestMoney'){
            $changedata['change_desc'] = '充值保证金';
            $changedata['change_type'] = 4;
            $changedata['earnest_money'] = $orderInfo['order_amount'];
        }elseif($orderInfo['type'] == 'goodsMoney') {
            $changedata['change_desc'] = '充值货款';
            $changedata['change_type'] = 3;
            $changedata['goods_money'] = $orderInfo['order_amount'];
        }else{
            Db::rollback();//回滚事务
            return '类型错误.';
        }
        $changedata['by_id'] = $orderInfo['order_id'];
        $res = (new WalletModel)->change($changedata, $orderInfo['user_id'],false);
        if ($res !== true) {
            Db::rollback();//回滚事务
            return '更新帐户信息失败.';
        }

        $upData['status'] = 9;
        $res = $this->where('order_id',$order_id)->update($upData);
        if ($res < 1){
            Db::rollback();//回滚事务
            return '更新充值记录状失败，请重试.';
        }
        Db::commit();//提交事务
        redisLook($mkey,-1);//销毁锁
        return true;
    }
}
