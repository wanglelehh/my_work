<?php
/*------------------------------------------------------ */
//-- 微信
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\weixin\controller\api;
use app\ApiController;

use app\weixin\model\WeiXinUsersModel;
use app\weixin\model\WeiXinModel;
use app\weixin\model\WeiXinMpModel;
use app\weixin\model\WeiXinInviteLogModel;
use app\member\model\UsersModel;
use think\Db;


class Index  extends ApiController{

    public function initialize(){
        parent::initialize();
        $this->Model = new WeiXinUsersModel();
    }

    /*------------------------------------------------------ */
    //--  获取微信小程序/公众号的openId
    /*------------------------------------------------------ */
    public function getOpenId(){
        $code = input('code','','trim');
        $type = input('type','','trim');
        $wx_nickname = filterEmoji(input('wx_nickname','','trim'));
        $wx_headimgurl = input('wx_headimgurl','','trim');
        $auto_login = input('auto_login',0,'intval');
        if ($type == 'MP'){
            $is_mp = 1;
            $res = file_get_contents('https://api.weixin.qq.com/sns/jscode2session?appid='.settings('xcx_appid').'&secret='.settings('xcx_appsecret').'&js_code='.$code.'&grant_type=authorization_co');
            $wxModel = new WeiXinMpModel();
        }else{
            $is_mp = 0;
            $res = file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid=" .settings('weixin_appid') . "&secret=" .settings('weixin_appsecret') . "&code=" . $code . "&grant_type=authorization_code");
            $wxModel = new WeiXinModel();
        }
        $data = json_decode($res,true);
        if (empty($data['openid'])){
            return $this->error($data['errcode'].'-'.$data['errmsg']);
        }

        $where[] = ['wx_openid','=',$data['openid']];
        $where[] = ['is_mp','=',$is_mp];
        $wxuInfo = $this->Model->where($where)->find();

        $getWxInfo = $wxModel->getWxUserInfo($data['openid'],$data['access_token']);


        if (empty($wxuInfo)){//数据不存在，写入
            $inData['is_mp'] = $is_mp;
            $inData['wx_openid'] = $data['openid'];
            $inData['wx_nickname'] = $type == 'MP' ? $wx_nickname : $getWxInfo['nickname'];
            $inData['wx_headimgurl'] = $type == 'MP' ? $wx_headimgurl : $getWxInfo['headimgurl'];
            if (empty($data['unionid']) == false){
                $inData['unionid'] = $data['unionid'];
            }
            $inData['add_time'] = time();

            $this->Model->save($inData);
            $wxuInfo = $this->Model->where($where)->find();
        }else{
            //未知是否有坑，理论上只会从无到冇，不应该存在A变B
            if ($wxuInfo['unionid'] != $data['unionid']){
                $upData['unionid'] = $data['unionid'];
            }
            if ($type == 'MP'){
                if ($wxuInfo['wx_nickname'] != $wx_nickname ) $upData['wx_nickname'] = $wx_nickname;
                if ($wxuInfo['wx_headimgurl'] != $wx_headimgurl) $upData['wx_headimgurl'] = $wx_headimgurl;
            }else{
                if ($wxuInfo['wx_nickname'] != $getWxInfo['nickname']) $upData['wx_nickname'] = $getWxInfo['nickname'];
                if ($wxuInfo['wx_headimgurl'] != $getWxInfo['headimgurl']) $upData['wx_headimgurl'] = $getWxInfo['headimgurl'];
            }

        }

        if ($wxuInfo['update_time'] < time() - 86400 ){
            //获取关注信息
            $wxArr = $wxModel->getWxUserInfoSubscribe($wxuInfo['wx_openid']);

            if ($wxArr['subscribe'] == 1){
                $upData['sex'] = $wxArr['sex'];
                $upData['subscribe'] = $wxArr['subscribe'] * 1;
                if ((empty($wxuInfo['wx_nickname']) || empty($wxuInfo['wx_headimgurl'])) && (empty($wxArr['nickname']) || empty($wxArr['headimgurl']))){
                    if(isset($data['access_token'])){
                        $wxUserInfo = $wxModel->getAccWxUserInfo($wxuInfo['wx_openid'],$data['access_token']);
                        $wxArr['nickname'] = $wxUserInfo['nickname'];
                        $wxArr['headimgurl'] = $wxUserInfo['headimgurl'];
                    }
                }
                if (empty($wxuInfo['wx_nickname'])) $upData['wx_nickname'] = $wxArr['nickname'];
                if (empty($wxuInfo['wx_headimgurl'])) $upData['wx_headimgurl'] = $wxArr['headimgurl'];
                $upData['wx_city'] = $wxArr['city'];
                $upData['wx_province'] = $wxArr['province'];
            }else{
                if(isset($data['access_token'])){
                    $wxUserInfo = $wxModel->getAccWxUserInfo($wxuInfo['wx_openid'],$data['access_token']);
                    $upData['wx_nickname'] = $wxUserInfo['nickname'];
                    $upData['wx_headimgurl'] = $wxUserInfo['headimgurl'];
                }
                $upData['subscribe'] = 0;
            }
            $upData['update_time'] = time();
        }

        if (empty($data['session_key']) == false){
            $upData['session_key'] = $data['session_key'];
            unset($data['session_key']);
        }

        if (empty($upData) == false){
            $this->Model->where('wxuid',$wxuInfo['wxuid'])->update($upData);
        }

        if ($wxuInfo['user_id'] < 1){
            $share_token = input('share_token', '', 'trim');
            if (empty($share_token) == false){
                $WeiXinInviteLogModel = new WeiXinInviteLogModel();
                $_share_token = $WeiXinInviteLogModel->where('wxuid',$wxuInfo['wxuid'])->order('id DESC')->value('share_token');
                if ($_share_token != $share_token){
                    $wxlog['wxuid'] = $wxuInfo['wxuid'];
                    $wxlog['user_id'] =  (new UsersModel)->getShareUser($share_token);
                    $wxlog['share_token'] = $share_token;
                    $wxlog['add_time'] = time();
                    $WeiXinInviteLogModel->save($wxlog);
                }
            }
        }else{
//            if ($auto_login == 1 && settings('wx_auto_login') == 1){ //公众号自动登陆
                $data['token'] = (new UsersModel)->doLogin($wxuInfo['user_id']);
//            }
        }
        return $this->success($data);
    }

    /*------------------------------------------------------ */
    //--  微信小程序手机号码登陆
    /*------------------------------------------------------ */
    public function loginByMpPhone(){
        $openid = input('openid','','trim');
        $iv = input('iv','','trim');
        $share_token = input('share_token','','trim');
        $encryptedData = input('encryptedData','','trim');
        $dataObj = $this->Model->WXBizDataCrypt($openid,$iv,$encryptedData);
        if (is_array($dataObj) == false){
            return $this->error($dataObj);
        }
        $UsersModel = new UsersModel();
        $pid = $UsersModel->getShareUser($share_token);
        $userInfo = $UsersModel->where('mobile',$dataObj['phoneNumber'])->find();
        if (empty($userInfo) == false){
            if ($userInfo['is_ban'] == 1) {
                return $this->error(langMsg('用户已被禁用.', 'member.login.is_ban'));
            }
            $data['share_token'] = $userInfo['token'];
            $data['token'] = $UsersModel->doLogin($userInfo['user_id']);
        }else{
            $inArr['openid']=$openid;
            $inArr['iv']=$iv;
            $inArr['encryptedData']=$encryptedData;
            $inArr['mobile']=$dataObj['phoneNumber'];
            $inArr['openid']=$openid;
            $inArr['pid']=$pid;
            $inArr['password'] = 'Abc123456'; //系统默认密码
            $res=$UsersModel->register($inArr,0);
            if($res!=true){
                return $this->error(langMsg($res));
            }
            $userInfo = $UsersModel->where('mobile',$inArr['mobile'])->find();
            $data['share_token'] = $userInfo['token'];
            $data['token'] = $UsersModel->doLogin($userInfo['user_id']);
        }
        $WeiXinUsersModel=new WeiXinUsersModel();
        $wxuInfo=$WeiXinUsersModel->where('wx_openid',$openid)->find();
        Db::startTrans();
        if($wxuInfo['user_id']<1 || $wxuInfo['user_id']!=$userInfo['user_id']){
            $res=$WeiXinUsersModel->where('wx_openid',$openid)->update(['user_id'=>$userInfo['user_id'],'update_time'=>time()]);
            if(!$res){
                Db::rollback();
                return $this->error(langMsg('用户绑定失败,请稍后再试'));
            }
        }
        if(empty($userInfo['nick_name']) && empty($userInfo['headimgurl']) && !empty($wxuInfo['wx_headimgurl']) && !empty($wxuInfo['wx_nickname'])){
            $img=$UsersModel->getHeadImgBeuFen($wxuInfo['wx_headimgurl']);
            $nick_name=filterEmoji($wxuInfo['wx_nickname']);
            $res=$UsersModel->where('user_id',$userInfo['user_id'])->update(['nick_name'=>$nick_name,'headimgurl'=>$img]);
            if(!$res){
                Db::rollback();
                return $this->error(langMsg('头像、昵称更新有误,请稍后再试'));
            }
        }
        Db::commit();
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //--  微信公众号分享
    /*------------------------------------------------------ */
    public function getShareSign(){
        $url = input('url','','trim');
        $data = (new WeiXinModel)->getSignPackage($url);
        $data['debug'] = 0;//是否开启调试
        $data['user_token'] = $this->userInfo['token'];
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //--  绑定微信
    /*------------------------------------------------------ */
    public function bindWxUser(){
        $openid = input('openid','','trim');
        if (empty($openid)){
            return $this->error('获取openid失败.');
        }
        $source = request()->header('source');
        if ($source != 'H5-WX' && $source != 'MP-WEIXIN'){
            return $this->error('非微信访问，无法操作.');
        }
        $is_mp = $source == 'MP-WEIXIN' ? 1 : 0;
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $where[] = ['is_mp','=',$is_mp];
        $wxInfo = $this->Model->where($where)->find();
        if (empty($wxInfo) == false){
            if ($wxInfo['wx_openid'] == $openid){
                return $this->error('当前绑定的微信一致，无须修改.');
            }
        }
        $where = [];
        $where[] = ['wx_openid','=',$openid];
        $where[] = ['is_mp','=',$is_mp];
        $res = $this->Model->where($where)->update(['user_id'=>$this->userInfo['user_id']]);
        if ($res < 1){
            return $this->error('绑定失败，请重试.');
        }
        $data['wx_nickname'] = $this->Model->where($where)->value('wx_nickname');
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //--  解绑微信
    /*------------------------------------------------------ */
    public function unbindWxUser(){
        $source = request()->header('source');
        if ($source != 'H5-WX' && $source != 'MP-WEIXIN'){
            return $this->error('非微信访问，无法操作.');
        }
        $is_mp = $source == 'MP-WEIXIN' ? 1 : 0;
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $where[] = ['is_mp','=',$is_mp];
        $wxInfo = $this->Model->where($where)->find();
        if (empty($wxInfo)){
            return $this->error('当前没有绑定微信.');
        }
        $res = $this->Model->where($where)->update(['user_id'=>0]);
        if ($res < 1){
            return $this->error('绑定失败，请重试.');
        }
        return $this->success();
    }
    /**
     * 公众号手机号绑定微信会员信息
     */
    public function goBindWxUser(){
        $openid = input('openid','','trim');
        $share_token = input('share_token','','trim');
        $code = input('code','','trim');
        $mobile = input('mobile','','intval');
        $is_login = input('is_login','0','intval');
        if (empty($openid)){
            return $this->error('获取openid失败.');
        }
        $UsersModel = new UsersModel();
        $where = [];
        $where[] = ['wx_openid','=',$openid];
        $where[] = ['is_mp','=',0];
        $wxInfo = $this->Model->where($where)->find();
        //是登录时，如果微信会员信息不存在则提示绑定
        if ($is_login == 1 && empty($wxInfo['user_id'])){
            $data['is_bind'] = 1;
            return $this->success($data);
        }
        //有绑定会员直接登录
        if ($wxInfo['user_id'] > 0 && $is_login == 1){
            $data['token'] = $UsersModel->doLogin($wxInfo['user_id']);
            return $this->success($data);
        }
        //下面出现情况是第一次提示绑定手机时用到的
        if (empty($code)){
            return $this->error('请填写短信验证码.');
        }
        $res = $this->checkCode('register',$mobile,$code);//验证短信验证
        if ($res !== true){
            return $this->error($res);
        }
        if (empty($wxInfo)) return $this->error('微信会员不存在.');
        if (!checkMobile($mobile)) return $this->error('请填写正确手机号.');
        $userInfo = $UsersModel->where('mobile',$mobile)->find();
        //不存在直接注册
        Db::startTrans();
        $updata = [];
        if (empty($userInfo)){
            $inArr['invite_code'] = $share_token;
            $inArr['mobile'] = $mobile;
            $inArr['nick_name'] = $wxInfo['wx_nickname'];
            $inArr['headimgurl'] = $UsersModel->getHeadImgBeuFen($wxInfo['wx_headimgurl']);
            $inArr['password'] = 'Abc123456'; //系统默认密码
            $res = $UsersModel->register($inArr);
            if($res !== true){
                Db::rollback();
                return $this->error('绑定失败.');
            }
            $user = $UsersModel->where('mobile',$inArr['mobile'])->field('user_id,is_ban,token')->find();
            $updata['user_id'] = $user['user_id'];
        }else if($wxInfo['user_id'] != $userInfo['user_id']) { //存在会员并且user_id不同 - 进行绑定
            $updata['user_id'] = $userInfo['user_id'];
            if(empty($userInfo['nick_name']) && empty($userInfo['headimgurl']) && !empty($wxuInfo['wx_headimgurl']) && !empty($wxuInfo['wx_nickname'])){
                $img=$UsersModel->getHeadImgBeuFen($wxuInfo['wx_headimgurl']);
                $nick_name=filterEmoji($wxuInfo['wx_nickname']);
                $res=$UsersModel->where('user_id',$userInfo['user_id'])->update(['nick_name'=>$nick_name,'headimgurl'=>$img]);
                if(!$res){
                    Db::rollback();
                    return $this->error(langMsg('头像、昵称更新有误,请稍后再试'));
                }
            }
        }
        if(empty($updata)==false){
            $res = $this->Model->where($where)->update($updata);
            if (empty($res)){
                Db::rollback();
                return $this->error('绑定失败1.');
            }
        }
        $data['token'] = $UsersModel->doLogin($userInfo['user_id']);
        //直接登录
        Db::commit();
        return $this->success($data);
    }
}?>