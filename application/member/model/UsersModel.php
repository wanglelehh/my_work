<?php

namespace app\member\model;

use app\BaseModel;
use think\facade\Cache;
use think\Db;

use app\weixin\model\WeiXinUsersModel;
use app\member\model\RoleModel;

//*------------------------------------------------------ */
//-- 会员表
/*------------------------------------------------------ */

class UsersModel extends BaseModel
{
    protected $table = 'users';
    protected $mkey = 'user_info_mkey_';
    public $pk = 'user_id';
    public $userId = 0;
    public static $checkIDCardStatus = ['0'=>'未认证','1'=>'已认证','2'=>'待审核','3'=>'审核失败'];
    /*------------------------------------------------------ */
    //--  清除memcache
    /*------------------------------------------------------ */
    public function cleanMemcache($user_id)
    {
        Cache::rm($this->mkey . $user_id);
        Cache::rm($this->mkey . 'account_' . $user_id);
        Cache::rm('member_wallet_mkey_info' . $user_id);
    }

    /*------------------------------------------------------ */
    //-- 系统登陆后执行
    /*------------------------------------------------------ */
    public function doLogin($user_id,$upData=[])
    {
        if ($user_id < 1) return false;
        $share_token = input('share_token','','trim');
        if (empty($share_token) == false) {
            $userInfo = $this->info($user_id);
            if ($userInfo['is_bind'] == 0) {
                $pid = $this->getShareUser($share_token);
                $where[] = ['','exp',Db::raw("FIND_IN_SET('".$user_id."',superior)")];
                $where[] = ['user_id','=',$pid];
                $count = (new UsersBindSuperiorModel)->where($where)->count();
                if ($count < 1){
                    $upData['pid'] = $pid;
                }
                if ($pid == $user_id){
                    unset($upData['pid']);
                }
            }
        }
        $time = time();
        $upData['login_odd_num'] = 0;//登陆异常清空
        $upData['login_time'] = $time;
        $upData['login_ip'] = request()->ip();
        $upData['last_login_time'] = Db::raw('last_login_time=login_time');
        $upData['last_login_ip'] = Db::raw('last_login_ip=login_ip');
        $this->upInfo($user_id, $upData);
        $inLog['log_ip'] = $upData['login_ip'];
        $inLog['log_time'] = $time;
        $inLog['user_id'] = $user_id;
        $inLog['source'] = request()->header('source');
        (new LogLoginModel)->save($inLog);
        //判断订单模块是否存在
        if (class_exists('app\shop\model\OrderModel')) {
            //执行订单自动签收
            (new \app\shop\model\OrderModel)->autoSign($user_id);
            (new \app\shop\model\CartModel)->loginUpCart($user_id);//更新购物车
        }
        $token = getAppToken();
        $appTokenKey = getAppTokenKey($token);
        Cache::set('uniappLogin_' .$appTokenKey, $user_id, 86400);
        return $token;
    }
    /*------------------------------------------------------ */
    //-- 会员退出
    /*------------------------------------------------------ */
    public function logout()
    {
        $token = request()->header('Token');
        if (empty($token) == false){
            $appTokenKey = getAppTokenKey($token);
            Cache::rm('uniappLogin_' .$appTokenKey);
        }else{
            session('userId', null);
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 验证密码强度
    /*------------------------------------------------------ */
    private function checkPwd($pwd)
    {
        $pwd = trim($pwd);
        if (empty($pwd)) {
            return langMsg('密码不能为空.', 'member.checkpwd.empty_pwd');
        }
        if (strlen($pwd) < 8) {//必须大于8个字符
            return langMsg('密码必须大于八字符.', 'member.checkpwd.pwd_length_error');
        }
        if (preg_match("/^[0-9]+$/", $pwd)) { //必须含有特殊字符
            return langMsg('密码不能全是数字.', 'member.checkpwd.pwd_not_number');
        }
        if (preg_match("/^[a-zA-Z]+$/", $pwd)) {
            return langMsg('密码不能全是字母.', 'member.checkpwd.pwd_not_letter');
        }
        /*if (preg_match("/^[0-9A-Z]+$/", $pwd)) {
            return '请包含数字，字母大小写或者特殊字符';
        }
        if (preg_match("/^[0-9a-z]+$/", $pwd)) {
            return '请包含数字，字母大小写或者特殊字符';
        }*/
        return true;
    }
    /*------------------------------------------------------ */
    //-- 生成用户唯一标识,主要用于分享后身份识别
    /*------------------------------------------------------ */
    public function getToken()
    {
        $token = random_str(6,true);
        $count = $this->where('token', $token)->count('user_id');
        if ($count >= 1) return $this->getToken();
        return $token;
    }

    /**
     * 会员注册
     * @param array $inArr 写入数据
     *  * @param int $wxuid 微信会员ID
     * @param bool $is_admin 是否是后台添加的用户
     * @param string $obj
     * @return bool|string
     */
    public function register($inArr = array(), $wxuid = 0, $is_admin = false, &$obj = '',$isTrans=true)
    {
        if (empty($inArr['pid'])){
            $inArr['pid'] = 0;
        }
        $inArr['pid'] = $inArr['pid'] * 1;
        if (empty($inArr)) {
            return '获取注册数据失败.';
        }
        $WeiXinUsersModel = new WeiXinUsersModel();
        if (empty($inArr['encryptedData']) == false){
            $openid = $inArr['openid'];
            $wxUser = $WeiXinUsersModel->where('wx_openid',$openid)->find();
            $wxuid = $WeiXinUsersModel->where('wx_openid',$openid)->value('wxuid');
            if ($wxuid < 1){
                return '未找到相关微信会员信息.';
            }
            $dataObj = $WeiXinUsersModel->WXBizDataCrypt($inArr['openid'],$inArr['iv'],$inArr['encryptedData']);
            if (is_array($dataObj) == false){
                return $dataObj;
            }
            $inArr['mobile'] = $dataObj['phoneNumber'];
            $inArr['nick_name'] = $inArr['nick_name']?$inArr['nick_name']:$wxUser['wx_nickname'];
            $inArr['headimgurl'] = $this->getHeadImgBeuFen($wxUser['wx_headimgurl']);
            unset($inArr['openid'],$inArr['iv'],$inArr['encryptedData']);
        }elseif($wxuid < 1){
            if (empty($inArr['mobile'])) {
                return '请填写手机号码';
            }
            if (checkMobile($inArr['mobile']) == false) {
                return '手机号码不正确.';
            }
        }

        $count = $this->where('mobile', $inArr['mobile'])->count('user_id');
        if ($count > 0) return '手机号码：' . $inArr['mobile'] . '，已存在.';
        if (empty($inArr['nick_name']) == false) {//昵称不为空时，判断是否已存在
            $count = $this->where('nick_name', $inArr['nick_name'])->count('user_id');
            if ($count > 0) return '昵称：' . $inArr['nick_name'] . '，已存在.';
        }
        $res = $this->checkPwd($inArr['password']);//验证密码强度
        if ($res !== true) {
            return $res;
        }
        $inArr['password'] = f_hash($inArr['password']);
        $time = time();
        $inArr['reg_time'] = $time;
        $inArr['token'] = $this->getToken();
        if ($is_admin == false) {//非后台新增会员
            if ($inArr['pid'] == 0) {
                $register_invite_code = settings('register_invite_code');
                $register_must_invite = settings('register_must_invite');
                $share_user_id = 0;
                if ($register_invite_code > 0) {
                    if ($register_must_invite == 1 && empty($inArr['invite_code'])) {
                        return '需要填写邀请手机/邀请码才能注册.';
                    }
                    $share_user_id = $this->where('token', $inArr['invite_code'])->value('user_id');
                    if ($share_user_id < 1){
                        $share_user_id = $this->where('mobile', $inArr['invite_code'])->value('user_id');
                    }
                }

                if ($share_user_id < 1 && $register_invite_code > 0 && $register_must_invite == 1) {
                    return '邀请帐号不存在.';
                }
                $inArr['pid'] = $share_user_id * 1;
            }
            if ($inArr['pid'] < 1) {
                $inArr['pid'] = $this->returnPid(0,$openid,$share_user_id);
            }
            unset($inArr['invite_code']);
        }

        if ($inArr['role_id'] < 1){
            $inArr['role_id'] = (new RoleModel)->order('level DESC')->value('role_id');//获取当前最低级别
        }

        if ($isTrans == true){
            Db::startTrans();
        }
        $res = $this->create($inArr);
        $user_id = $res->user_id;
        if ($user_id < 1) {
            if ($isTrans == true) {
                Db::rollback();
            }
            return '未知错误-1，请尝试重新提交.';
        }
        if ($user_id < 29889) {
            $this->where('user_id', $user_id)->delete();
            $inArr['user_id'] = 29889;
            $res = $this->create($inArr);
            $user_id = $res->user_id;
            if ($user_id < 1) {
                if ($isTrans == true) {
                    Db::rollback();
                }
                return '未知错误-2，请尝试重新提交.';
            }
        }
        $res = (new AccountModel)->createData(['user_id' => $user_id, 'update_time' => time()]);
        if ($res < 1) {
            if ($isTrans == true) {
                Db::rollback();
            }
            return '创建会员帐户信息失败.';
        }
        if ($isTrans == true) {
            Db::commit();
        }
        //后台添加的用户，加日志
        if ($is_admin && !empty($obj)) {
            $log =  '后台手动新增会员-用户id:' . $user_id;
            if ($inArr['role_id'] > 0){
                $role_name = (new RoleModel)->where('role_id',$inArr['role_id'])->value('role_name');
                $log .= '，初始身份：'.$role_name;
            }
            $obj->_log($user_id,$log, 'member');
        }
        $this->userId = $user_id;

        //注册会员成功后异步执行
        asynRun('member/UsersModel/asynRunRegister', ['user_id' => $user_id, 'pid' => $inArr['pid']]);
        return true;
    }

    /*------------------------------------------------------ */
    //-- 注册异步处理
    /*------------------------------------------------------ */
    public function asynRunRegister($data)
    {
        $user_id = $data['user_id'];
        $pid = $data['pid'];
        $mkey = 'asynRunRegisterIng' . $user_id;
        $asynRunRegisterIng = Cache::get($mkey);
        if (empty($asynRunRegisterIng) == false) {
            return true;
        }
        Cache::set($mkey, 1, 60);

        $AccountModel = new AccountModel();

        //注册赠送积分
        $register_integral = settings('register_integral') * 1;
        if ($register_integral > 0) {
            $changedata['change_desc'] = '注册赠送积分';
            $changedata['change_type'] = 7;
            $changedata['by_id'] = $user_id;
            $changedata['use_integral'] = $register_integral;
            $changedata['total_integral'] = $register_integral;
            $res = $AccountModel->change($changedata, $user_id, false);
            if ($res < 1) {
                return '注册赠送积分失败.';
            }
        }
        //end

        $bind_pid_time = settings('bind_pid_time');
        if ($bind_pid_time < 1) {
            //写入关系链
            $this->regUserBind($user_id, $pid);
        }
        //红包模块存在执行
        if (class_exists('app\shop\model\BonusModel')) {
            //注册送红包
            (new \app\shop\model\BonusModel)->sendByReg($user_id);
        }
        return true;
    }

    /*------------------------------------------------------ */
    //-- 返上绑定上级ID
    /*------------------------------------------------------ */
    private function returnPid($user_id = 0,$openid ='',$share_user_id = 0)
    {
        if ($user_id > 0) {
            return $this->where('user_id', $user_id)->value('pid');
        }
        $wxuid = 0;
        if (empty($openid) == false){
            $wxuid = (new WeiXinUsersModel)->where('wx_openid', $openid)->value('wxuid');
        }
        $pid = 0;
        //分享注册
        if ($wxuid > 0) {//微信访问根据微信分享来源记录，执行
            $bind_share_rule = settings('bind_share_rule');
            if ($bind_share_rule == 0) {//按最先分享绑定
                $sort = 'id ASC';
            } else {//按最后分享绑定
                $sort = 'id DESC';
            }
            $pid = (new \app\weixin\model\WeiXinInviteLogModel)->where('wxuid', $wxuid)->order($sort)->value('user_id');
        } elseif (empty($share_user_id) == false) {
            $pid = $share_user_id;
        }
        return $pid * 1;
    }

    /*------------------------------------------------------ */
    //-- 找回用户密码
    /*------------------------------------------------------ */
    public function forgetPwd($data = array(), &$obj)
    {
        if (empty($data)) {
            return '获取数据失败.';
        }
        if (empty($data['mobile'])) {
            return '请填写手机号码';
        }
        if (checkMobile($data['mobile']) == false) {
            return '手机号码不正确.';
        }
        $res = $this->checkPwd($data['password']);//验证密码强度
        if ($res !== true) {
            return $res;
        }
        $user = $this->where('mobile', $data['mobile'])->find();
        if (f_hash($data['password']) == $user['password']) {
            return '新密码与旧密码一致,请核实.';
        }
        $upArr['password'] = f_hash($data['password']);
        $res = $this->where('user_id', $user['user_id'])->update($upArr);
        if ($res < 1) return '未知错误，修改会员密码失败.';
        $obj->_log($user['user_id'], '用户找回密码.', 'member');
        return true;
    }
    /*------------------------------------------------------ */
    //-- 修改用户密码
    /*------------------------------------------------------ */
    public function editPwd($data = array(), &$obj)
    {
        if (empty($data)) {
            return '获取数据失败.';
        }
        $res = $this->checkPwd($data['password']);//验证密码强度
        if ($res !== true) {
            return $res;
        }
        $user = $this->where('user_id', $this->userInfo['user_id'])->find();
        $oldPwd = f_hash($data['old_password']);
        if ($oldPwd != $user['password']) {
            return '旧密码错误.';
        }
        $upArr['password'] = f_hash($data['password']);
        if ($upArr['password'] == $user['password']) {
            return '新密码与旧密码一致无须修改.';
        }
        $res = $this->where('user_id', $user['user_id'])->update($upArr);
        if ($res < 1) return '未知错误，修改会员密码失败.';
        $obj->_log($user['user_id'], '用户修改密码.', 'member');
        return true;
    }
    /*------------------------------------------------------ */
    //-- 绑定会员手机
    /*------------------------------------------------------ */
    public function bindMobile($data = array(), &$obj)
    {
        if (empty($this->userInfo['mobile']) == false){
            return '当前用户已绑定手机：'.$this->userInfo['mobile'];
        }
        if (empty($data['mobile'])) {
            return '请输入需要绑定的手机号码.';
        }
        $res = $this->checkPwd($data['password']);//验证密码强度
        if ($res !== true) {
            return $res;
        }
        if (is_numeric($data['pay_password']) == false) {
            return '请填写6位数字的支付密码.';
        }
        $count = $this->where('mobile', $data['mobile'])->count('user_id');
        if ($count > 0) {
            return $data['mobile'] . '此手机号码已绑定其它帐号.';
        }
        $upArr['mobile'] = $data['mobile'];
        $upArr['password'] = f_hash($data['password']);
        $upArr['pay_password'] = f_hash($data['pay_password']);
        $res = $this->where('user_id', $this->userInfo['user_id'])->update($upArr);
        if ($res < 1) return '未知错误，绑定手机失败.';
        $obj->_log($this->userInfo['user_id'], '用户绑定手机号码.', 'member');
        return true;
    }
    /*------------------------------------------------------ */
    //-- 获取用户信息
    //-- val 查询值
    //-- type 查询类型
    //-- isCache 是否调用缓存
    /*------------------------------------------------------ */
    public function info($val, $type = 'user_id', $isCache = true)
    {
        if (empty($val)) return false;
        if ($isCache == true) $info = Cache::get($this->mkey . $val);
        if (empty($info) == false) return $info;
        if ($type == 'token') {
            $info = $this->where('token', $val)->find();
            if (empty($info)) {
                return [];
            }
            $info = $info->toArray();
        } else {
            $info = $this->where('user_id', $val)->find();
            if (empty($info)) {
                return [];
            }
            $info = $info->toArray();
            $AccountModel = new AccountModel();
            $account = $AccountModel->where('user_id', $val)->find();
            if (empty($account) == true) {
                //创建会员帐户信息
                $AccountModel->save(['user_id' => $val, 'update_time' => time()]);
                $account = $AccountModel->where('user_id', $val)->find();
            }
            $info['account'] = $account->toArray();
        }
        $info['is_has_paypwd'] = 0;//是否已设置支付密码
        if (empty($info['pay_password'])==false){
            $info['is_has_paypwd'] = 1;
        }
        unset($info['password'],$info['pay_password']);
        $info['region_text'] = '';
        $RegionModel = new \app\mainadmin\model\RegionModel();
        if ($info['district'] > 0){
            $region = $RegionModel->info($info['district']);
            $info['region_text'] = $region['merger_name'];
        }
        $info['shareUrl'] = config('config.host_path') . '/?share_token=' . $info['token'];//分享链接
        if ($info['role_id'] > 0) {
            $info['role'] = (new RoleModel)->info($info['role_id']);
        }
        if (empty($info['role'])){
            $info['role']['role_id'] = 0;
            $info['role']['role_name'] = '';
        }
        if ($info['bank_city'] > 0){
            $region = $RegionModel->info($info['bank_city']);
            $info['bank_region_text'] = $region['merger_name'];
        }
        //还没有执行绑定关系执行
        if ($info['is_bind'] == 0) {
            $bind_pid_time = settings('bind_pid_time');
            if ($bind_pid_time < 1 || $info['last_buy_time'] > 0) {
                $this->regUserBind($info['user_id'], $info['pid']);
            }
        }//end
        if (empty($info['real_name'])){
            $info['real_name'] = '未填写';
        }
        Cache::set($this->mkey . $val, $info, 30);
        return $info;
    }
    /*------------------------------------------------------ */
    //--获取上级信息
    /*------------------------------------------------------ */
    public function getSuperior($pid)
    {
        if ($pid < 1) return [];
        $info = $this->info($pid);
        unset($info['password']);//销毁不需要的字段
        return $info;
    }
    /*------------------------------------------------------ */
    //--获取会员帐户
    /*------------------------------------------------------ */
    public function getAccount($user_id, $isCache = true)
    {
        $user_id = $user_id * 1;
        if ($user_id < 1) return array();
        $mkey = $this->mkey . 'account_' . $user_id;
        if ($isCache == true) $info = Cache::get($mkey);
        if (empty($info) == false) return $info;
        $info = $this->where('u.user_id', $user_id)->alias('u')->field('u.user_id,u.mobile,ua.*')->join('users_account ua', 'u.user_id = ua.user_id', 'left')->find();
        if (empty($info) == false) {
            $info = $info->toArray();
        }
        Cache::set($mkey, $info, 60);
        return $info;
    }
    /*------------------------------------------------------ */
    //-- 更新会员信息
    /*------------------------------------------------------ */
    public function upInfo($user_id, $data)
    {
        $user_id = $user_id * 1;
        $res = $this->where('user_id', $user_id)->update($data);
        $this->cleanMemcache($user_id);
        return $res;
    }
    /*------------------------------------------------------ */
    //-- 根据token获取分享者进行关联
    //-- $val int/string 用户ID/分享token
    //-- $type string 类型
    /*------------------------------------------------------ */
    public function getShareUser($val = '', $type = 'token')
    {
        if (empty($val)) return 0;
        $DividendSatus = settings('DividendSatus');
        if ($DividendSatus == 0) return 0;//不开启推荐，不执行
        if ($type == 'token') {
            $pInfo = $this->where('token', $val)->find();
        } else {
            $pInfo = $this->where('user_id', $val)->find();
        }
        if (empty($pInfo)) return 0;
        if ($pInfo['is_ban'] == 1) {//如果用户被封禁，直接归被封禁用户的上级
            if ($pInfo['pid'] < 1) return 0;
            return $this->getShareUser($pInfo['pid'], 'user_id');
        }
        return $pInfo['user_id'];
    }
    /*------------------------------------------------------ */
    //-- 获取会员下级汇总
    /*------------------------------------------------------ */
    public function userShareStats($user_id = 0, $isCache = true)
    {
        $info = Cache::get($this->mkey . '_us_' . $user_id);
        if ($isCache == true && empty($info) == false) return $info;
        $user_id = $user_id * 1;
        $UsersBindSuperiorModel = new UsersBindSuperiorModel();
        $levelField = ['pid', 'pid_b', 'pid_c'];
        $info['all'] = 0;
        foreach ($levelField as $key => $field) {
            $where = [];
            $where[$field] = $user_id;
            $count = $UsersBindSuperiorModel->where($where)->count();
            $info[$key + 1] = $count;
            $info['all'] += $count;
        }
        Cache::set($this->mkey . '_us_' . $user_id, $info, 30);
        return $info;
    }
    /*------------------------------------------------------ */
    //-- 操作等级关联
    // -- user_id int 会员ID
    // -- pid  int  所属上级ID 值为-1时，执行查询来源
    // -- is_edit boolean 是否重新修改，不是修改发送绑定消息通知
    /*------------------------------------------------------ */
    public function regUserBind($user_id = 0, $pid = 0, $is_edit = false)
    {
        if ($user_id < 1) return true;
        if ($is_edit == false) {
            $DividendSatus = settings('DividendSatus');
            if ($DividendSatus == 0) return true;//不开启推荐，不执行
            $is_bind = $this->where('user_id', $user_id)->value('is_bind');
            if ($is_bind > 0) return true;//已执行绑定不再执行
            $bingKey = 'regUserBindIng' . $user_id;
            if (empty(Cache::get($bingKey)) == false) {
                return true;
            }
            Cache::set($bingKey, 1, 60);
        }
        if ($pid == -1) {
            $pid = $this->returnPid($user_id);
        }
        $UsersBindSuperiorModel = new UsersBindSuperiorModel();
        //会员上级汇总处理
        $res = $UsersBindSuperiorModel->treat($user_id, $pid, $is_edit);
        if ($res == false) {
            return false;
        }

        if ($is_edit == false) {
            $this->where('user_id', $user_id)->update(['is_bind' => 1]);
            if ($pid < 1) return true;
            //发送模板消息
            $WeiXinMsgTplModel = new \app\weixin\model\WeiXinMsgTplModel();
            $WeiXinUsersModel = new WeiXinUsersModel();
            $wxInfo = $WeiXinUsersModel->info($user_id);
            if (empty($wxInfo['wx_nickname']) == false){
                $data['user_id'] = $user_id;
                $data['nickname'] = $wxInfo['wx_nickname'];
                $data['sex'] = $wxInfo['sex'] == 1 ? '男' : '女';
                $data['region'] = $wxInfo['wx_province'] . $wxInfo['wx_city'];
                $data['send_scene'] = 'bind_user_msg';
                unset($wxInfo);

                $sendUids[$pid] = 1;
                $usersBind = $UsersBindSuperiorModel->where('user_id', $user_id)->find();
                if ($usersBind['pid_b'] > 0) {
                    $sendUids[$usersBind['pid_b']] = 2;
                }
                foreach ($sendUids as $uid => $val) {
                    $data['level'] = $val;
                    $data['openid'] = $WeiXinUsersModel->where('user_id', $uid)->value('wx_openid');
                    $WeiXinMsgTplModel->send($data);
                }
            }

        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 获取会员的上级关联链
    /*------------------------------------------------------ */
    public function getSuperiorList($user_id = 0)
    {
        if ($user_id < 1) return array();
        $chain = Cache::get('userSuperior_' . $user_id);
        if ($chain) return $chain;
        $dividendRole = (new RoleModel)->getRows();
        $i = 1;
        $user_id = $this->where('user_id', $user_id)->value('pid');
        if ($user_id < 1) return [];
        do {
            $info = $this->where('user_id', $user_id)->field('user_id,nick_name,pid,role_id,reg_time')->find();
            $chain[$i]['level'] = $i;
            $chain[$i]['user_id'] = $info['user_id'];
            $chain[$i]['reg_time'] = dateTpl($info['reg_time']);
            $chain[$i]['nick_name'] = empty($info['nick_name']) ? '未填写' : $info['nick_name'];
            $chain[$i]['role_name'] = $info['role_id'] > 0 ? $dividendRole[$info['role_id']]['role_name'] : '无身份';
            $user_id = $info['pid'];
            $i++;
        } while ($user_id > 0);

        Cache::set('userSuperior_' . $user_id, $chain, 300);
        return $chain;
    }

    /*------------------------------------------------------ */
    //-- 当前月签到记录
    /*------------------------------------------------------ */
    public function signInfo($user_id = 0)
    {
        $signInfo['signDay'] = [];
        $signInfo['constant'] = 0;
        $signInfo['isSign'] = 0;
        $signInfo['sign_constant'] = settings('sign_constant');

        $where[] = ['user_id','=',$user_id];
        $where[] = ['year','=',date('Y')];
        $where[] = ['month','=',date('m') * 1];
        $info = (new UsersSignModel)->where($where)->field('constant_num,month,days,sign_time')->find();
        if (empty($info)) return $signInfo;
        $days = explode(',',$info['days']);
        foreach ($days as $day){
            $signDay[] = $day * 1;
        }
        $signInfo['signDay'] = $signDay;
        $signInfo['constant'] = $info['constant_num'];
        if (date('d') == date('d',$info['sign_time'])){
            $signInfo['isSign'] = 1;
        }
        return $signInfo;
    }


    /*------------------------------------------------------ */
    //-- 签到赠积分
    /*------------------------------------------------------ */
    public function signIntegral()
    {
        $use_integral = settings('sign_integral');
        return $use_integral;
    }
    /*------------------------------------------------------ */
    //-- 签到赠抽奖次数
    /*------------------------------------------------------ */
    public function signLuckdrawNum()
    {
        $sign_luckdraw_num = 0;
        if (settings('luckdraw_all') == 1){
            $sign_luckdraw_num = settings('sign_luckdraw_num');
        }
        return $sign_luckdraw_num;
    }
    /*------------------------------------------------------ */
    //-- 签到记录
    /*------------------------------------------------------ */
    public function signLog($user_id,$yearMonth)
    {
        $yearMonth = explode('-',$yearMonth);
        $where[] = ['user_id','=',$user_id];
        $where[] = ['year','=',$yearMonth[0]];
        $where[] = ['month','=',$yearMonth[1] * 1];
        $logs = (new UsersSignModel)->where($where)->value('logs');
        if (empty($logs)){
            return [];
        }
        $logs = json_decode($logs,true);
        return $logs;
    }

    /*------------------------------------------------------ */
    //-- 签到
    /*------------------------------------------------------ */
    public function signIng($user_id = 0)
    {
        $year = date('Y');
        $month = date('n');
        $day = date('d');
        $sign_in = settings('sign_in');
        if ($sign_in == 0){
            return '签到功能未开启.';
        }
        $sign_integral = settings('sign_integral');

        $sign_luckdraw_num = $this->signLuckdrawNum();

        $sign_constant = settings('sign_constant');
        $mkey = 'SignIng_'.$user_id;
        $res = redisLook($mkey);
        if ($res == false){
            return '签到正在处理中...';
        }
        $UsersSignModel = new UsersSignModel();
        $data = $UsersSignModel->where(['user_id'=>$user_id,'year'=>$year,'month'=>$month])->find();

        Db::startTrans();
        if(empty($data) == false){
            $dates = explode(',',$data['days']);
            if(in_array($day, $dates)){
                Db::rollback();
                redisLook($mkey,-1);
                return '今天已经签到,请勿重复签到.';
            }
            $constant_integral = 0;
            $constant_luckdraw_num = 0;
            //判断是否连接签到
            if (end($dates) == $day - 1){
                $constant_num = $data['constant_num'] + 1;//连续签到天数+1
                if (isset($sign_constant[$constant_num])){
                    $constant_integral = $sign_constant[$constant_num]['integral'];
                    $constant_luckdraw_num = $sign_constant[$constant_num]['luckdraw_num'];
                }
            }else{
                $constant_num = 1;//断续后，重新计算
            }

            $logs = json_decode($data['logs'],true);
            $log['sign_integral'] = $sign_integral;
            $log['constant_integral'] = $constant_integral;
            $total_integral = $sign_integral + $log['constant_integral'];
            $log['total_integral'] = $total_integral;

            if ($sign_luckdraw_num > 0){
                $log['sign_luckdraw_num'] = $sign_luckdraw_num;
                $log['constant_luckdraw_num'] = $constant_luckdraw_num;
                $total_luckdraw_num = $sign_luckdraw_num + $constant_luckdraw_num;
                $log['total_luckdraw_num'] = $total_luckdraw_num;
            }

            $log['constant_num'] = $constant_num;
            $logs[$day] = $log;

            $dates[] = $day;
            //本月数据已有,直接更新
            $upData = ['days'=>join(',',$dates),'constant_num'=>$constant_num,'sign_time'=>time()];
            $upData['use_integral'] = ['INC',$log['total_integral']];
            $upData['logs'] = json_encode($logs);
            $res = $UsersSignModel->where(['sign_id'=>$data['sign_id'],'sign_time'=>$data['sign_time']])->update($upData);
        }else{
            //本月数据没有,进行插入
            $inData = [
                'user_id' => $user_id,
                'constant_num' => 1,
                'year'    => $year,
                'month'   => $month,
                'days'    => $day,
                'use_integral'   => $sign_integral,
                'sign_time' => time()
            ];
            $log['sign_integral'] = $sign_integral;
            $log['constant_integral'] = 0;
            $log['total_integral'] = $sign_integral;
            $total_integral = $sign_integral;

            if ($sign_luckdraw_num > 0) {
                $log['sign_luckdraw_num'] = $sign_luckdraw_num;
                $log['constant_luckdraw_num'] = 0;
                $total_luckdraw_num = $sign_luckdraw_num;
                $log['total_luckdraw_num'] = $total_luckdraw_num;
            }

            $log['constant_num'] = 1;

            $logs[$day] = $log;
            $inData['logs'] = json_encode($logs);
            //插入的,用户id,年月存在唯一索引所以不会重复插入
            $res = $UsersSignModel->insert($inData);
        }

        if($res < 1){
            Db::rollback();
            return '签到失败,请重试.';
        }

        //签到成功，加积分

        $change_desc = '签到';
        if ($total_integral > 0){
            $change_desc .= '，获得积分';
            $accData['use_integral'] = $total_integral;
        }

        if ($total_luckdraw_num > 0){
            $change_desc .= '，获得抽奖次数';
            $accData['luckdraw_num'] = $total_luckdraw_num;
        }
        if ($total_integral > 0 || $total_luckdraw_num > 0) {
            $accData['change_desc'] = $change_desc;
            $accData['change_type'] = 12;
            $accData['by_id'] = 0;
            $res = (new AccountModel)->change($accData, $user_id, false);

            if (!$res) {
                Db::rollback();
                return '签到失败-1,处理异常';
            }
        }
        Db::commit();
        $data = [];
        $data['total_integral'] = $total_integral;
        $data['total_luckdraw_num'] = $total_luckdraw_num;
        $this->cleanMemcache($user_id);
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 团队统计
    /*------------------------------------------------------ */
    public function teamCount($user_id = 0)
    {
        if ($user_id < 1) return 0;
        $where[] = ['', 'exp', Db::raw("FIND_IN_SET('" . $user_id . "',superior)")];
        $count = (new UsersBindSuperiorModel)->where($where)->count() - 1;
        return $count < 1 ? 0 : $count;
    }
    /*------------------------------------------------------ */
    //-- 获取远程会员头像到本地
    /*------------------------------------------------------ */
    public function getHeadImg($user_id,$headimgurl = '',$isUp = true)
    {
        if (empty($headimgurl) == false && strstr($headimgurl, 'http')) {
            $headimgurl = strstr($headimgurl, 'https') ? str_replace("https", "http", $headimgurl) : $headimgurl;
            $file_path = config('config._upload_') . 'headimg/' . substr($user_id, -1) . '/';
            makeDir($file_path);
            //图片文件
            $file_name = $file_path . random_str(12).$user_id;
            $extend = getFileExtend($headimgurl);
            $file_name .= '.' . $extend[1];
            downloadImage($headimgurl, $file_name);
            if ($isUp == true){
                $upArr['headimgurl'] = $headimgurl = trim($file_name, '.');
                $this->upInfo($user_id, $upArr);
            }
        }
        return '.' . $headimgurl;
    }


    /*------------------------------------------------------ */
    //-- 获取远程会员头像到本地
    /*------------------------------------------------------ */
    public function getHeadImgBeuFen($headimgurl = '')
    {
        if (empty($headimgurl) == false && strstr($headimgurl, 'http')) {
            $headimgurl = strstr($headimgurl, 'https') ? str_replace("https", "http", $headimgurl) : $headimgurl;
            $file_path = config('config._upload_') . 'headimg/' . substr(time(), -1) . '/';
            makeDir($file_path);
            //图片文件
            $file_name = $file_path . random_str(12);
            $extend = getFileExtend($headimgurl);
            $file_name .= '.' . $extend[1];
            downloadImage($headimgurl, $file_name);
            $upArr['headimgurl'] = $headimgurl = trim($file_name, '.');
//            $res = $this->upInfo($user_id, $upArr);
//            if (empty($res)) return false;
        }
        return $upArr['headimgurl'];
    }
    /*------------------------------------------------------ */
    //-- 获取分享二维码
    /*------------------------------------------------------ */
    public function getMyQrCode($url = '')
    {
        $file_path = config('config._upload_') . 'qrcode/h5/';
        makeDir($file_path);
        if (empty($url) == false){
            if (strstr($url,'?')){
                $value = config('config.host_path').$url.'&share_token='.$this->userInfo['token'];
            }else{
                $value = config('config.host_path').$url.'?share_token='.$this->userInfo['token'];
            }
        }else{
            $value = config('config.host_path').'?share_token='.$this->userInfo['token'];
        }
        $file = $file_path .md5($value).'_'.$this->userInfo['user_id'].'.png';
        if(file_exists($file) == true){
            return $file;
        }

        include EXTEND_PATH . 'phpqrcode/phpqrcode.php';//引入PHP QR库文件
        $QRcode = new \phpqrcode\QRcode();
        $QRcode::png($value, $file, "L", 10, 1, 2, true);
        return $file;
    }
    /*------------------------------------------------------ */
    //-- 获取用户小程序二维码
    /*------------------------------------------------------ */
    public function getUserMiniQrcode($page = '',$extid = 0){

        $file_path = config('config._upload_').'qrcode/wxmp/';
        $imageName = md5($page.'_'.$extid).'_'.$this->userInfo['user_id'].'.jpg';
        $imageSrc = $file_path."/". $imageName; //图片名字

        if(file_exists($imageSrc) == true){
            return $imageSrc;
        }

        $scene = $this->userInfo['token'].'_'.$extid;
        $mini = new \app\weixin\model\WeiXinMpModel();
        $qrcode = $mini->get_qrcode($page,$scene);
        if (strstr($qrcode,",")){
            $qrcode = explode(',',$qrcode);
            $qrcode = $qrcode[1];
        }
        makeDir($file_path);

        file_put_contents($imageSrc, base64_decode($qrcode));//返回的是字节数

        return $imageSrc;
    }
    /*------------------------------------------------------ */
    //-- 记录用户升级日志
    /*------------------------------------------------------ */
    public function _upLog($edit_id,$log_info,$new_level_id,$befor_level_id){
        $inData['edit_id'] = $edit_id;
        $inData['log_info'] = $log_info;
        $inData['log_time'] = time();
        $inData['log_time'] = time();
        $inData['new_level_id'] = $new_level_id;
        $inData['befor_level_id'] = $befor_level_id;
        $inData['user_id'] = 0;
        if (defined('AUID')){
            $inData['user_id'] =  AUID;
        }elseif (defined('SAUID')){
            $inData['user_id'] = SAUID;
        }
        (new LogUpLevelModel)->save($inData);
        return true;
    }
    /*------------------------------------------------------ */
    //-- 获取身份直属上级
    //-- $user_id int 用户ID
    //-- $roleList array 身份
    /*------------------------------------------------------ */
    public function getRolePid($user_id,&$roleList){
        $userInfo = $this->where('user_id',$user_id)->field('user_id,role_id,pid')->find();
        if ($userInfo['pid'] == 0){
            return 0;//平台
        }
        if ($userInfo['role_id'] == 0){
            $level = 999999;//无身份时，默认一个最大级别
        }else{
            $level = $roleList[$userInfo['role_id']]['level'];
        }
        $role_pid = 0;
        $pid = $userInfo['pid'];
        do{
            $pUserInfo = $this->where('user_id',$pid)->field('user_id,pid,role_id,is_ban')->find();
            if ($roleList[$pUserInfo['role_id']]['level'] < $level && $pUserInfo['is_ban'] == 0){
                $role_pid = $pUserInfo['user_id'];
                break;
            }
            $pid = $pUserInfo['pid'];
        }while($pid > 0);
        return $role_pid;
    }
    /*------------------------------------------------------ */
    //-- 重置身份直属关系，修改身份，上级，封禁，解封时，调用
    //-- $user_id int 用户ID
    /*------------------------------------------------------ */
    public function resetRolePid($user_id,&$obj){
        $UsersBindSuperiorModel = new UsersBindSuperiorModel();
        $where[] = ['','exp',Db::raw("FIND_IN_SET('".$user_id."',superior)")];
        $user_ids = $UsersBindSuperiorModel->where($where)->column('user_id');//获取所有下级
        $roleList =  (new RoleModel)->getRows();
        foreach ($user_ids as $uid){
            $role_pid = $this->where('user_id',$uid)->value('role_pid');
            $new_role_pid = $this->getRolePid($uid,$roleList);
            if ($role_pid == $new_role_pid){
                continue;//不需要修改
            }
            $upData['role_pid'] = $new_role_pid;
            $res = $this->where('user_id',$uid)->update($upData);
            if ($res < 1){
                return '重置身份关系失败-'.$uid;
            }
            $obj->_log($uid, '上级层级变化，重置身份关系.','member');
            $this->cleanMemcache($uid);
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 验证支付密码
    //-- $user_id int 用户ID
    /*------------------------------------------------------ */
    public function checkPayPwd($user_id,$pay_password){
        if (settings('sys_model_pay_password') != 1){
            return true;
        }
        if (empty($pay_password)){
            return '请输入支付密码.';
        }
        $pay_pwd = $this->where('user_id',$user_id)->value('pay_password');
        if (empty($pay_pwd)){
            return '未设置支付密码.';
        }
        if (f_hash($pay_password) !== $pay_pwd){
            return '支付密码错误.';
        }
        return true;
    }

    /*------------------------------------------------------ */
    //-- 团队(自己+直推+间推)
    //-- $user_id int 用户ID
    /*------------------------------------------------------ */
    public function teamUid($user_id){
        $userList = $user_id;
        $pids = $user_id;
        $teamids = $this->where([['pid','in',$pids]])->column('user_id');   //直推人
        if(count($teamids)>0){
            $userList .= ','.implode(',',$teamids);
            $teamIds = $this->where([['pid','in',$teamids]])->column('user_id');
            if(count($teamids)>0){
                $userList .= ','.implode(',',$teamIds);
            }
        }
        $userList=explode(',',rtrim($userList,','));
        return $userList;
    }

    public function teamAllUid($user_id){
        $UsersBindSuperiorModel=new UsersBindSuperiorModel();
        $where = [];
        $where[] = ['', 'exp', Db::raw("FIND_IN_SET('" . $user_id . "',superior)")];
        $userList = $UsersBindSuperiorModel->where($where)->column('user_id');
        return $userList;
    }
    /*------------------------------------------------------ */
    //-- 获取会员的上级关联链(备份)
    /*------------------------------------------------------ */
    public function getSuperiorSelf($user_id = 0)
    {
        if ($user_id < 1) return array();
        $chain = Cache::get('userSuperior_' . $user_id);
        if ($chain) return $chain;
        $dividendRole = (new RoleModel())->getRows();
        $i = 1;
//        $user_id = $this->where('user_id', $user_id)->value('pid');
        if ($user_id < 1) return [];
        do {
            $info = $this->where('user_id', $user_id)->field('user_id,nick_name,pid,role_id,reg_time,last_up_role_time')->find();
            $chain[$i]['weizhi'] = $i;
            $chain[$i]['level'] = $info['role_id'] > 0 ? $dividendRole[$info['role_id']]['level'] : '无等级';
            $chain[$i]['user_id'] = $info['user_id'];
            $chain[$i]['last_up_role_time'] = $info['last_up_role_time'];
            $chain[$i]['reg_time'] = dateTpl($info['reg_time']);
            $chain[$i]['nick_name'] = empty($info['nick_name']) ? '未填写' : $info['nick_name'];
            $chain[$i]['role_id'] = $info['role_id'];
            $chain[$i]['role_name'] = $info['role_id'] > 0 ? $dividendRole[$info['role_id']]['role_name'] : '无身份';
            $user_id = $info['pid'];
            $i++;
        } while ($user_id > 0);

        Cache::set('userSuperior_' . $user_id, $chain, 300);
        return $chain;
    }


    function isSameMonth($time1, $time2)
    {
        $m1 = date('Ym', $time1);
        $m2 = date('Ym', $time2);
        if($m1 == $m2){
            return true;
        }
        return false;
    }
}
