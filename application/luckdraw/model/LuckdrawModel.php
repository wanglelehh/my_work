<?php

namespace app\luckdraw\model;
use app\BaseModel;
use think\Db;
use app\member\model\AccountModel;
//*------------------------------------------------------ */
//-- 抽奖
/*------------------------------------------------------ */
class LuckdrawModel extends BaseModel
{
	protected $table = 'luck_draw_list';
	public  $pk = 'luck_id';
    public  $prize_type = ['entity'=>'实物奖品','integral'=>'赠送积分','bonus'=>'赠送优惠券','luckdrawnum'=>'抽奖次数','thinkjoin'=>'感谢参与'];
    /*------------------------------------------------------ */
    //-- 获取奖品
    //-- $luck_id int 抽奖ID
    //-- $isAll bool 是否全部数据
    //-- return array
    /*------------------------------------------------------ */
    public function getPrizes($luck_id = 0,$isAll = false){
        $rows = (new LuckdrawPrizeModel)->where('luck_id',$luck_id)->select();
        if (empty($rows)){
            return [];
        }
        if ($isAll == false){
            foreach ($rows as $key=>$row){
                $arr['prize_name'] = $row['prize_name'];
                $arr['prize_img'] = $row['prize_img'];
                $data[] = $arr;
            }
        }else{
            foreach ($rows as $key=>$row){
                $data[$row['prize_id']] = $row;
            }
        }
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 开奖
    //-- $luckdrawm array 抽奖信息
    //-- return array/string
    /*------------------------------------------------------ */
    public function openGame(&$luckdrawm) {
        $prizeList = $this->getPrizes($luckdrawm['luck_id'],true);
        if (empty($prizeList)){
            return '没有设置奖项.';
        }
        $keys = [];
        $prize_pre = [];
        $i = 0;
        foreach ($prizeList as $key => $row) {
            $prize_pre[$row['prize_id']] = $row['prize_pre'];
            $keys[$row['prize_id']] = $i;
            $i++;
        }
        $prize_id = $this->getRand($prize_pre); //根据概率获取奖项id
        $prize = $prizeList[$prize_id];
        if ($prize['prize_num'] > 0 && $prize['prize_num'] <= $prize['open_num']){
            return $this->openGame($luckdrawm);//当前抽中奖品已没库存，重抽
        }
        if ($prize['prize_limit'] > 0){
            if ($luckdrawm['open_num'] < $prize['prize_limit']){
                return $this->openGame($luckdrawm);//当总抽奖数量小于此数值时，不开此项奖品，重抽
            }
        }
        $res = $this->savePrize($luckdrawm,$prize);//记录奖品
        if ($res === false){
            return $this->openGame($luckdrawm);//以防奖品超发，记录奖品失败，重抽
        }elseif (is_array($res) == false){
            return $res;
        }
        $isthink = 0;
        if ($prize['prize_type'] == 'thinkjoin'){
            $isthink = 1;
        }
        return ['log_id'=>$res['log_id'],'prize_index'=>$keys[$prize_id],'prize_id'=>$prize_id,'isthink'=>$isthink];

    }
    //根据概率，随机获取中奖的奖项id
    protected function getRand($proArr) {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);

        return $result;
    }
    /*------------------------------------------------------ */
    //-- 记录奖品并发放
    //-- $luckdrawm array 抽奖信息
    //-- $prize array 中奖的奖品信息
    //-- return array
    /*------------------------------------------------------ */
    protected function savePrize(&$luckdrawm,&$prize) {
        Db::startTrans();//启动事务
        $LuckdrawPrizeModel = new LuckdrawPrizeModel();
        if ($prize['prize_num'] > 0){//奖品数大于0时
            $prize = $LuckdrawPrizeModel->where('prize_id',$prize['prize_id'])->find();
            if ($prize['prize_num'] <= $prize['open_num']){
                return false;//当前抽中奖品已没库存，重抽
            }
        }
        //扣除抽奖次数
        $inData['luckdraw_num'] = -1;
        $inData['change_type'] = 11;
        $inData['by_id'] = $luckdrawm['luck_id'];
        $inData['change_desc'] = '抽奖扣除抽奖次数';
        $res = (new AccountModel)->change($inData, $this->userInfo['user_id']);
        if ($res < 1){
            Db::rollback();//事务回滚
            return '系统发生错误-0，请重试。';
        }
        //增加中奖数量
        $res = $LuckdrawPrizeModel->where('prize_id',$prize['prize_id'])->setInc('open_num');
        if ($res < 1){
            Db::rollback();//事务回滚
            return '系统发生错误-1，请重试。';
        }
        //增加开奖数量
        $res = $this->where('luck_id',$luckdrawm['luck_id'])->setInc('open_num');
        if ($res < 1){
            Db::rollback();//事务回滚
            return '系统发生错误-2，请重试。';
        }

        if ($prize['prize_type'] != 'entity'){
            $inArr['status'] = 1;//非实物奖品直接发放
            if ($prize['prize_type'] != 'thinkjoin'){
                $res = $this->givePrize($prize);
                if ($res !== true){
                    Db::rollback();//事务回滚
                    return '系统发生错误-3，请重试。';
                }
            }
        }
        $inArr['user_id'] = $this->userInfo['user_id'];
        $inArr['luck_id'] = $luckdrawm['luck_id'];
        $inArr['type'] = $luckdrawm['type'];
        $inArr['prize_id'] = $prize['prize_id'];
        $inArr['prize_pre'] = $prize['prize_pre'];
        $inArr['prize_type'] = $prize['prize_type'];
        $inArr['prize_name'] = $prize['prize_name'];
        $inArr['prize_img'] = $prize['prize_img'];
        $inArr['relation_val'] = $prize['relation_val'];
        $inArr['add_time'] = time();
        $LuckdrawLogModel = new LuckdrawLogModel();
        $res = $LuckdrawLogModel->save($inArr);
        if ($res < 1){
            Db::rollback();//事务回滚
            return '系统发生错误-4，请重试。';
        }
        Db::commit();//事务提交
        return ['log_id'=>$LuckdrawLogModel->log_id];
    }
    /*------------------------------------------------------ */
    //-- 奖品发放
    //-- $luckdrawm array 抽奖信息
    //-- $prize array 中奖的奖品信息
    //-- return bool
    /*------------------------------------------------------ */
    protected function givePrize(&$prize) {
        $res = 0;
        if ($prize['prize_type'] == 'integral'){
            $inData['use_integral'] = $prize['relation_val'];
            $inData['change_type'] = 11;
            $inData['by_id'] = $prize['prize_id'];
            $inData['change_desc'] = '中奖获赠积分:' . $prize['relation_val'];
            $res = (new AccountModel)->change($inData, $this->userInfo['user_id']);
        }elseif ($prize['prize_type'] == 'luckdrawnum'){
            $inData['luckdraw_num'] = $prize['relation_val'];
            $inData['change_type'] = 11;
            $inData['by_id'] = $prize['prize_id'];
            $inData['change_desc'] = '中奖获赠抽奖次数:' . $prize['relation_val'];
            $res = (new AccountModel)->change($inData, $this->userInfo['user_id']);
        }elseif ($prize['prize_type'] == 'bonus'){
            $res = (new \app\shop\model\BonusModel)->makeBonusSn($prize['relation_val'], 1, $this->userInfo['user_id']);
        }
        if ($res < 1){
            return false;
        }
        return true;
    }
}

