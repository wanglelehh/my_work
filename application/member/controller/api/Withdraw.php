<?php
/*------------------------------------------------------ */
//-- 提现相关
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\member\controller\api;
use app\ApiController;

use app\member\model\AccountModel;
use app\member\model\WithdrawLogModel;
use app\member\model\UsersModel;

class Withdraw  extends ApiController{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->checkLogin();//验证登陆
        $this->Model = new AccountModel();
    }
    /*------------------------------------------------------ */
    //-- 获取提现手续费
    /*------------------------------------------------------ */
    public function getFee()
    {
        $maxMoney = input('maxMoney','0','intval');
        if ($maxMoney == 1){
            $money = $this->Model->getMaxMoney($this->userInfo['account']['balance_money']);
        }else{
            $money = input('money','0','float');
        }
        $data = $this->Model->checkWithdraw($this->userInfo['account']['balance_money'],$money);
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 提交提现
    /*------------------------------------------------------ */
    public function post()
    {
        $money = input('money') * 1;
        if ($money <= 0){
            return $this->error('请输入提现金额.');
        }

        $payment = input('payment','','trim');
        if (empty($payment)){
            return $this->error('选择提现方式.');
        }
        $settings = settings();
        $withdraw_status = settings('withdraw_status');
        if ($withdraw_status < 1){
            return $this->error('暂不开放提现.');
        }
        if($settings['withdraw_week_' . date('w')] < 1){
            return $this->error('今天暂不开放提现.');
        }
        $now_h = date('H');
        if($settings['withdraw_day_start'] > $now_h || $settings['withdraw_day_stop'] <= $now_h){
            return $this->error($settings['withdraw_day_start'] . '点到' . $settings['withdraw_day_stop'] . '点外时间暂不开放提现.');
        }

        $pay_password = input('pay_password','','trim');
        $res = (new UsersModel)->checkPayPwd($this->userInfo['user_id'],$pay_password);
        if ($res !== true){
            return $this->error($res);
        }

        $lookKey = 'user_withdarw_'.$this->userInfo['user_id'];
        $res = redisLook($lookKey);
        if ($res == false){
            return $this->error('正在处理，请不要重复提交.');
        }
        $res = $this->Model->withdraw($money,$payment);
        redisLook($lookKey,-1);//销毁锁
        if ($res !== true){
            return $this->error($res);
        }
        return $this->success('提现申请成功.');
    }
    /*------------------------------------------------------ */
    //-- 获取提现日志统计
    /*------------------------------------------------------ */
    public function getStatic()
    {
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $rows = (new WithdrawLogModel)->where('user_id',$this->userInfo['user_id'])->group('status')->column('count(log_id)','status');
        $data['all'] = 0;
        $data['waitCheck'] = 0;
        $data['complete'] = 0;
        $data['fail'] = 0;
        foreach ($rows as $key=>$num){
            if ($key == 0){
                $data['waitCheck'] = $num;
            }elseif ($key == 1){
                $data['fail'] = $num;
            }else{
                $data['complete'] = $num;
            }
            $data['all'] += $num;
        }
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取提现日志列表
    /*------------------------------------------------------ */
    public function getLog()
    {
        $state = input('state','all','trim');
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        if ($state == 'waitCheck') {//待审核
            $where[] = ['status', '=', 0];
        }elseif ($state == 'complete'){//已完成
            $where[] = ['status','=',9];
        }elseif ($state == 'fail'){//失败
            $where[] = ['status','=',1];
        }
        $WithdrawLogModel = new WithdrawLogModel();
        $status = $WithdrawLogModel->status;
        $account_type = $this->Model->account_type;
        $data = $this->getPageList($WithdrawLogModel, $where,'',10);
        foreach ($data['list'] as $key=>$row){
            $row['add_time'] = dateTpl($row['add_time'],'Y-m-d H:i:s',true);
            $row['status'] = $status[$row['status']];
            $row['account_type'] = $account_type[$row['account_type']];
            $row['account_info'] = json_decode($row['account_info'],true);
            $data['list'][$key] = $row;
        }
        return $this->success($data);
    }

}