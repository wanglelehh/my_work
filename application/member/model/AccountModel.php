<?php
namespace app\member\model;
use app\BaseModel;
use think\facade\Cache;
use think\Db;
use app\weixin\model\WeiXinUsersModel;
/*------------------------------------------------------ */
//-- 会员帐户表
//-- Author: iqgmy
/*------------------------------------------------------ */
class AccountModel extends BaseModel
{
	protected $table = 'users_account';
	public  $pk = 'user_id';
    protected $mkey = 'member_wallet_mkey_';
    public $account_type = ['wxpay'=>'微信','alipay'=>'支付宝','bank'=>'银行卡'];
    /*------------------------------------------------------ */
    //-- 创建帐号数据
    /*------------------------------------------------------ */
    public function createData($data = array()){
        return $this->save($data);
    }
	/*------------------------------------------------------ */
	//-- 处理更新的内容
	/*------------------------------------------------------ */ 
	public function returnUpData(&$data){	
	    $updata = array();	
		if (isset($data['total_dividend'])){
			$updata['total_dividend'] = ['INC',$data['total_dividend']];			
		}
		//总积分
		if (isset($data['total_integral'])){
			$updata['total_integral'] = ['INC',$data['total_integral']];	
		}
        //余额
		if (isset($data['balance_money'])){
			$updata['balance_money'] = ['INC',$data['balance_money']];
		}
        //累计提现
        if (isset($data['withdraw_money_total'])){
            $updata['withdraw_money_total'] = ['INC',$data['withdraw_money_total']];
        }
        //可用积分
		if (isset($data['use_integral'])){
			$updata['use_integral'] = ['INC',$data['use_integral']];
		}
        //抽奖次数
        if (isset($data['luckdraw_num'])){
            $updata['luckdraw_num'] = ['INC',$data['luckdraw_num']];
        }
    	return $updata;
	}
    /*------------------------------------------------------ */
    //-- 记录明细,更新用户帐户，更新用户信息
    //-- $data array 更新的字段数据
    //-- $user_id int 会员ID
    //-- $isTrans 是否使用事务回调，默认为开启，一般不开启即外部开启了
    /*------------------------------------------------------ */
    public function change($data,$user_id =0,$isTrans = true){
        if ($user_id < 1) return false;
        if ($isTrans == true){
            Db::startTrans();
        }
        $account = $this->where('user_id',$user_id)->lock(true)->find();
        $data['user_id'] = $user_id;
        $data['change_time'] = time();
        $data['change_ip'] = request()->ip();
        $data['old_total_dividend'] = $account['total_dividend'];
        $data['old_total_integral'] = $account['total_integral'];
        $data['old_balance_money'] = $account['balance_money'];
        $data['old_use_integral'] = $account['use_integral'];
        $data['old_luckdraw_num'] = $account['luckdraw_num'];
        $res = (new AccountLogModel)->create($data);
        if ($res->log_id < 1){
            if ($isTrans == true){// 回滚事务
                Db::rollback();
            }
            return false;
        }
        $upData = $this->returnUpData($data);
        if (empty($upData)){
            if ($isTrans == true){// 回滚事务
                Db::rollback();
            }
            return false;
        }
        $upData['update_time'] = $data['change_time'];
        $res = $this->where('user_id',$data['user_id'])->update($upData);
        if ($res < 1){
            if ($isTrans == true){// 回滚事务
                Db::rollback();
            }
            return false;
        }
        if ($isTrans == true){// 提交事务
            Db::commit();
        }
        (new UsersModel)->cleanMemcache($data['user_id']);
        return true;

    }
    /*------------------------------------------------------ */
    //-- 获取钱包信息
    //-- $user_id int 代理ID
    //-- $is_cache bool 是否读缓存
    /*------------------------------------------------------ */
    public function getWalletInfo($user_id = 0,$is_cache = true)
    {
        $mkey = $this->mkey.'info' . $user_id;
        if ($is_cache == true){
            $data = Cache::get($mkey);
            if (empty($data) == false) return $data;
        }
        $data = $this->where('user_id',$user_id)->find();
        if (empty($data)){//查询不到数据，插入
            $res = $this->save(['user_id'=>$user_id,'update_time'=>time()]);
            if ($res < 1){
                return false;
            }
            $data = $this->where('user_id',$user_id)->find();
        }
        $data = $data->toArray();
        Cache::set($mkey,$data,15);
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 计算提现中的金额
    //-- $user_id int 代理会员ID
    /*------------------------------------------------------ */
    public function getWithdrawing($user_id){
        $where[] = ['user_id','=',$user_id];
        $where[] = ['status','=',0];
        $money = (new WithdrawLogModel)->where($where)->SUM('money');
        return sprintf("%.2f",$money);
    }
    /*------------------------------------------------------ */
    //-- 计算最大提现金额
    //-- $balance_money float 用户余额
    /*------------------------------------------------------ */
    public function getMaxMoney($balance_money){
        $settings = settings();
        //账户余额大于每次最高可提现金额，则用最高可提现金额。
        $max_money = $balance_money > $settings['withdraw_max_money'] ? $settings['withdraw_max_money'] : $balance_money;
        //账户余额小于每次最低可提现金额，则用0。
        $max_money = $max_money < $settings['withdraw_min_money'] ? 0 : $max_money;
        if ($settings['withdraw_fee_type'] == 1){
            return $max_money;
        }
        //外扣
        $min_fee = $settings['withdraw_min_money'] * ($settings['withdraw_fee'] / 100);
        $min_fee = $min_fee < $settings['withdraw_fee_min'] ? $settings['withdraw_fee_min'] : $min_fee;
        if ($balance_money < $settings['withdraw_min_money'] + $min_fee) {
            $tmp_money = 0;
        } else {
            $tmp_money = bcdiv($max_money, (100 + $settings['withdraw_fee']) / 100, 2);
            $money_fee = bcmul($max_money, $settings['withdraw_fee'] / 100, 2);
            //若临时手续费高于最大手续费，则手续费为最大手续费
            if ($money_fee > $settings['withdraw_fee_max']) {
                $money_fee = $settings['withdraw_fee_max'];
                //最大可提现金额为总额减去最大手续费
                $tmp_money = bcsub($max_money, $money_fee, 2);
            }
            //若临时手续费低于最小手续费，则手续费为最小手续费
            if ($money_fee < $settings['withdraw_fee_min']) {
                $money_fee = $settings['withdraw_fee_min'];
                //最大可提现金额为总额减去最小手续费
                $tmp_money = bcsub($max_money, $money_fee, 2);
            }
        }
        return $tmp_money;
    }
    /*------------------------------------------------------ */
    //-- 计算提现手续费,返回提现手续费
    //-- $balance_money float 用户余额
    //-- $money float 提现金额
    /*------------------------------------------------------ */
    public function checkWithdraw($balance_money,$money){
        $return['isOk'] = 1;
        if($balance_money < $money){
            $return['isOk'] = 0;
            $return['tip'] = '余额不足.';
            return $return;
        }
        $settings = settings();
        if ($money < $settings['withdraw_min_money']){
            $return['isOk'] = 0;
            $return['tip'] = '每次提现不能低于￥'.$settings['withdraw_min_money'];
            return $return;
        }
        if ($money > $settings['withdraw_max_money']){
            $return['isOk'] = 0;
            $return['tip'] = '每次提现不能高于￥'.$settings['withdraw_max_money'];
            return $return;
        }
        $withdraw_fee = priceFormat($money / 100 * $settings['withdraw_fee']);
        if ($settings['withdraw_fee_min'] > $withdraw_fee){
            $withdraw_fee = $settings['withdraw_fee_min'];
        }elseif ($settings['withdraw_fee_max'] > 0 && $withdraw_fee > $settings['withdraw_fee_max']){
            $withdraw_fee = $settings['withdraw_fee_max'];
        }

        $return['withdraw_fee'] = $withdraw_fee;
        if ($settings['withdraw_fee_type'] == 0){//外扣
            $return['real_arrive_money'] = $money;//实际到帐
            $return['show_money'] = $money;//提现显示
            $return['real_money'] = bcadd($money , $withdraw_fee,2);//实扣
            if ($withdraw_fee > 0){
                if ($return['real_money'] > $balance_money){
                    $return['isOk'] = 0;
                    $return['tip'] = '提现手续费使用外扣：余额不足够支付手续费.';
                }else{
                    $return['tip'] = '提现手续费使用外扣：提现到帐￥'.$money.'，实扣￥'.$return['real_money'];
                }
            }
        }else{//内扣
            $return['real_arrive_money'] = bcsub($money , $withdraw_fee,2);//实际到帐
            $return['show_money'] = $money;//提现显示
            $return['real_money'] = $money;//实扣
            if ($return['real_arrive_money'] <= 0){
                $return['isOk'] = 0;
                $return['tip'] = '提现错误，提现手续费使用内扣，提现金额必须大于手续费:'.$withdraw_fee;
                return $return;
            }
            if ($withdraw_fee > 0){
                $return['tip'] = '提现手续费使用内扣：提现￥'.$money.'，实现到帐￥'.$return['real_arrive_money'];
            }
        }

        return $return;
    }
    /*------------------------------------------------------ */
    //-- 提现处理
    //-- $money float 提现金额
    //-- $payment string 提现方式
    /*------------------------------------------------------ */
    public function withdraw($money,$payment){
        $res = $this->checkWithdraw($this->userInfo['account']['balance_money'],$money);
        if ($res['isOk'] != 1){
            return $res['tip'];
        }

        $settings = settings();
        $WithdrawLogModel = new WithdrawLogModel();
        //判断提现限制
        if ($settings['withdraw_num'] > 0){
            $where[] = ['user_id','=',$this->userInfo['user_id']];
            $where[] = ['add_time','>',strtotime(date('Y-m-d'))];
            $wnum = $WithdrawLogModel->where($where)->count('log_id');
            if ($wnum >= $settings['withdraw_num']){
                return '每天最多只能提现'.$settings['withdraw_num'].'次.';
            }
        }
        $inArr['user_id'] = $this->userInfo['user_id'];
        $inArr['money'] = $money;//提现金额
        $inArr['withdraw_fee'] = $res['withdraw_fee'];//手续费
        $inArr['real_money'] = $res['real_money'];//实际扣款
        $inArr['fee_type'] = $settings['withdraw_fee_type'];
        $inArr['account_type'] = $payment;
        $inArr['arrival_money'] = $res['real_arrive_money'];//实际打款


        $inArr['account_info'] = [];
        if ($inArr['account_type'] == 'wxpay'){
            $inArr['account_info']['name'] = $this->userInfo['weixin_name'];
            $inArr['account_info']['account'] = $this->userInfo['weixin_account'];
            $inArr['qrcode_file'] = $this->userInfo['weixin_qrcode'];
        }elseif($inArr['account_type'] == 'alipay'){
            $inArr['account_info']['name'] = $this->userInfo['alipay_name'];
            $inArr['account_info']['account'] = $this->userInfo['alipay_account'];
            $inArr['qrcode_file'] = $this->userInfo['alipay_qrcode'];
        }elseif($inArr['account_type'] == 'bank'){
            $inArr['account_info']['bank_name'] = $this->userInfo['bank_name'];
            $inArr['account_info']['bank_province'] = $this->userInfo['bank_province'];
            $inArr['account_info']['bank_city'] = $this->userInfo['bank_city'];
            $inArr['account_info']['bank_subbranch'] = $this->userInfo['bank_subbranch'];
            $inArr['account_info']['bank_card_number'] = $this->userInfo['bank_card_number'];
            $inArr['account_info']['bank_user_name'] = $this->userInfo['bank_user_name'];
            $inArr['account_info']['bank_idcard_munber'] = $this->userInfo['bank_idcard_munber'];
        }elseif($inArr['account_type'] == 'wxbag'){
            $WeiXinUsersModel = new WeiXinUsersModel();
            $wxUser = $WeiXinUsersModel->where('user_id',$this->userInfo['user_id'])->find();
            if(!$wxUser){
                return '提现失败.请选择其他方式';
            }
            $inArr['account_info']['name'] = $wxUser['wx_nickname'];
            $inArr['account_info']['account'] = $wxUser['wx_openid'];
            $inArr['qrcode_file'] = $wxUser['wx_headimgurl'];
        }
        $inArr['account_info'] = json_encode($inArr['account_info'],JSON_UNESCAPED_UNICODE);
        $inArr['add_time'] = time();
        Db::startTrans();//开启事务
        $res = $WithdrawLogModel->save($inArr);
        if ($res < 1){
            Db::rollback();//回滚事务
            return '提现申请录入失败.';
        }
        $changedata['change_desc'] = '提现扣除';
        $changedata['change_type'] = 5;
        $changedata['by_id'] = $WithdrawLogModel->log_id;
        $changedata['balance_money'] = $inArr['real_money'] * -1;
        $changedata['withdraw_money_total'] = $money;
        $res = $this->change($changedata, $this->userInfo['user_id'], false);
        if ($res !== true) {
            Db::rollback();// 回滚事务
            return '提现扣除用户余额失败.';
        }
        Db::commit();// 提交事务
        return true;
    }
    /*------------------------------------------------------ */
    //-- 写入充值
    //-- $money float 充值金额
    //-- $type string 充值类型
    //-- $remark string 备注
    //-- $payInfo array 支付方式
    //-- $payCode string 支付方式
    //-- $fileList array 支付凭证
    /*------------------------------------------------------ */
    public function saveRecharge($money,$type,$remark,$payInfo,$payCode,$fileList){
        $time = time();
        $inArr['type'] = $type;
        $inArr['order_amount'] = $money;
        $inArr['remark'] = $remark;
        $inArr['add_time'] = $time;
        $inArr['user_id'] = $this->userInfo['user_id'];
        $inArr['user_pid'] = $this->userInfo['pid'];
        $inArr['order_sn'] = 'recharge'.$time.rand(10,99);
        $inArr['pay_code'] = $payCode;
        $inArr['pay_id'] = $payInfo['pay_id'];
        $inArr['pay_name'] = $payInfo['pay_name'];
        if($type == 'uplevelGoodsMoney'){
            $inArr['is_reward'] = 1;//需要执行奖项处理
        }
        if ($payCode == 'offline'){
            $file_path = config('config._upload_').'offline_pay/'.date('m');
            $offlineimg = [];
            $fileList = explode(',',$fileList);
            foreach ($fileList as $file){
                $offlineimg[] = copyFile($file,$file_path);
            }
            $inArr['imgs'] = join(',',$offlineimg);
            $inArr['pay_time'] = $time;
        }
        $RechargeLogModel = new RechargeLogModel();
        $res = $RechargeLogModel->save($inArr);
        if ($res < 1){
            if ($payCode == 'offline') {
                foreach ($offlineimg as $img) {
                    @unlink('.' . $img);
                }
            }
            return '充值录入失贁.';
        }
        if ($payCode == 'offline') {
            foreach ($fileList as $file) {
                @unlink('.' . $file);
            }
        }
        return $RechargeLogModel->order_id;
    }
    /*------------------------------------------------------ */
    //-- 汇总待到帐佣金
    //-- $user_id int 代理会员ID
    /*------------------------------------------------------ */
    public function getWaitReturned($user_id){
        $where[] = ['dividend_uid','=',$user_id];
        $where[] = ['status','IN',[1,2,3]];
        $DividendModel = new \app\distribution\model\DividendModel();
        $money = $DividendModel->where($where)->SUM('dividend_amount');
        return sprintf("%.2f",$money);
    }
}
