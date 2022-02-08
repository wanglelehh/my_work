<?php

namespace app\luckdraw\controller\api;

use app\ApiController;
use app\luckdraw\model\LuckdrawModel;
use app\luckdraw\model\LuckdrawLogModel;
use app\member\model\UsersModel;
/*------------------------------------------------------ */
//-- 大转盘相关API
/*------------------------------------------------------ */
class Turntable extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new LuckdrawModel();
    }
    /*------------------------------------------------------ */
    //-- 获取大转盘信息
    /*------------------------------------------------------ */
    public function getInfo(){
        $data = $this->Model->where('type','turntable')->find();
        if (empty($data)){
            return $this->error('获取大转盘数据失败.');
        }
        $data = $data->toArray();
        $data['prizeList'] = $this->Model->getPrizes($data['luck_id']);
        $data['luckdraw_num'] = empty($this->userInfo['account']['luckdraw_num'])?0:$this->userInfo['account']['luckdraw_num'];//可抽奖次数
        return $this->success($data);
    }

    /*------------------------------------------------------ */
    //-- 抽奖
    /*------------------------------------------------------ */
    public function start(){
        $this->checkLogin();//验证登陆
        if ($this->userInfo['account']['luckdraw_num'] < 1){
            return $this->error('您已没有抽奖次数了.');
        }
        $info = $this->Model->where('type','turntable')->find();
        if (empty($info)){
            return $this->error('请求失败.');
        }
        if ($info['status'] == 0){
            return $this->error('暂未开放.');
        }
        $lockKey = 'luckdraw_user_'.$this->userInfo['user_id'];
        $res = redisLook($lockKey);
        if ($res == false) return $this->error('请求频繁，请销后再试.');

        $data = $this->Model->openGame($info);
        redisLook($lockKey,-1);
        if (is_array($data)==false){
            return $this->error($data);
        }
        $userInfo = (new UsersModel)->info($this->userInfo['user_id']);
        $data['luckdraw_num'] = $userInfo['account']['luckdraw_num'];//可抽奖次数
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 我的中奖记录
    /*------------------------------------------------------ */
    public function getMyPrize(){
        $this->checkLogin();//验证登陆
        $luck_id = input('luck_id',0,'intval');
        $where[] = ['luck_id','=',$luck_id];
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $rows = (new LuckdrawLogModel)->where($where)->field('log_id,prize_name,prize_type,status,add_time')->order('log_id DESC')->select()->toArray();
        $data['list'] = [];
        foreach ($rows as $row){
            $row['add_time'] = date('Y-m-d H:i:s',$row['add_time']);
            $row['_status'] = '';
            if ($row['prize_type'] == 'entity'){
                if ($row['status'] == 0){
                    $row['_status'] = '待领奖';
                }else{
                    $row['_status'] = '已领奖';
                }
            }
            $data['list'][] = $row;
        }
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取兑换信息
    /*------------------------------------------------------ */
    public function getChangeInfo(){
        $this->checkLogin();//验证登陆
        $data['integral_add_luckdrawnum'] = settings('integral_add_luckdrawnum');
        $data['use_integral'] = $this->userInfo['account']['use_integral'];
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 兑换抽奖次数
    /*------------------------------------------------------ */
    public function changeLuckdrawNum(){
        $change_num = input('change_num',0,'intval');
        if ($change_num < 1){
            return $this->error('兑换次数不能小于1.');
        }
        $integral_add_luckdrawnum = settings('integral_add_luckdrawnum');
        $all_integral = $integral_add_luckdrawnum * $change_num;
        if ($all_integral > $this->userInfo['account']['use_integral']){
            return $this->error('当前积分不足够兑换.');
        }
        $AccountModel = new \app\member\model\AccountModel();
        $inData['use_integral'] = $all_integral * -1;
        $inData['luckdraw_num'] = $change_num;
        $inData['change_type'] = 11;
        $inData['by_id'] = 0;
        $inData['change_desc'] = '兑换抽奖次数:' . $change_num.'，扣除积分：'.$all_integral;
        $res = $AccountModel->change($inData, $this->userInfo['user_id']);
        if ($res < 1){
            return false;
        }
        $userInfo = (new UsersModel)->info($this->userInfo['user_id']);
        $data['luckdraw_num'] = $userInfo['account']['luckdraw_num'];//可抽奖次数
        return $this->success($data);
    }
}
