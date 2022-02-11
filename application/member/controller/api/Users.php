<?php

namespace app\member\controller\api;

use app\ApiController;
use app\member\model\UsersModel;
use app\member\model\UsersSignModel;
use app\weixin\model\WeiXinUsersModel;

/*------------------------------------------------------ */
//-- 会员相关API
/*------------------------------------------------------ */

class Users extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->checkLogin();//验证登陆
        $this->Model = new UsersModel();
    }
    /*------------------------------------------------------ */
    //-- 获取登陆会员信息
    /*------------------------------------------------------ */
    public function getInfo()
    {
        $data = $this->userInfo;
        $data['sign_in'] = settings('sign_in');
        $superior = $this->Model->getSuperior($this->userInfo['pid']);
        if ($superior) $data['reg_time'] = date('Y-m-d', $superior['reg_time']);
        $data['superior'] = $superior;
        $data['wx_auto_login'] = 0;


        $source = request()->header('source');
        if ($source == 'H5-WX' || $source == 'MP-WEIXIN'){
            $data['wx_auto_login'] = settings('wx_auto_login');
            $is_mp = $source == 'MP-WEIXIN' ? 1 : 0;
            if ($data['wx_auto_login'] == 1){ //如果开启微信自动登陆，判断当前会员是否已绑定微信号
                $where[] = ['user_id','=',$data['user_id']];
                $where[] = ['is_mp','=',$is_mp];
                $data['wx_nickname'] = (new WeiXinUsersModel)->where($where)->value('wx_nickname');
            }
        }

        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 修改会员信息
    /*------------------------------------------------------ */
    public function editInfo()
    {
        $upArr['real_name'] = input('real_name','','trim');
        $_from = input('_from','','trim');
        if (empty($upArr['real_name']) && $_from == 'channel'){
            return $this->error('请输入真实姓名.');
        }

        $region_code = input('region_code');
        if (empty($region_code)){
            return $this->error('请选择所在区域.');
        }
        $upArr['nick_name'] = input('nick_name', '', 'trim');
        if (empty($upArr['nick_name']) == true) {
            return $this->error('请填写昵称.');
        }
        $where[] = ['nick_name', '=', $upArr['nick_name']];
        $where[] = ['user_id', '<>', $this->userInfo['user_id']];
        $count = $this->Model->where($where)->count('user_id');
        if ($count > 0) {
            return $this->error('昵称：' . $upArr['nick_name'] . '，已存在.');
        }
        $edit_headimgurl = input('edit_headimgurl',0,'intval');
        if ($edit_headimgurl == 1){
            $headimgurl = input('headimgurl');
            $extend = getFileExtend($headimgurl);
            if ($extend == false){
                return $this->error('未能识别头像图片，请尝试重新上传.');
            }
            $file_path = config('config._upload_') . 'headimg/' . substr($this->userInfo['user_id'], -1) . '/';
            $file_name = random_str(12).$this->userInfo['user_id'].'.'.$extend[1];
            $res = base64ToImage($headimgurl,$file_path,$file_name);
            if ($res == false){
                return $this->error('头像保存失败，请重试.');
            }
            $upArr['headimgurl'] = trim($file_path.$file_name, '.');
        }
        $upArr['province'] = intval($region_code[0]);
        $upArr['city'] = intval($region_code[1]);
        $upArr['district'] = intval($region_code[2]);
        $upArr['sex'] = input('sex',0,'intval');
        //验证数据是否出现变化
        $dbarr = $this->Model->field(join(',',array_keys($upArr)))->where('user_id',$this->userInfo['user_id'])->find()->toArray();
        $this->checkUpData($dbarr,$upArr,true);

        $res = $this->Model->upInfo($this->userInfo['user_id'], $upArr);
        if ($res < 1) {
            if ($edit_headimgurl == 1){
                @unlink($file_name);
            }
            return $this->error('修改用户信息失败，请重试.');
        }
        if ($edit_headimgurl == 1){
            @unlink($dbarr['headimgurl']);
        }
        $this->_log($this->userInfo['user_id'], '用户修改个人信息.', 'member');
        $this->Model->cleanMemcache($this->userInfo['user_id']);
        return $this->success('修改成功.');
    }
    /*------------------------------------------------------ */
    //-- 修改用户密码
    /*------------------------------------------------------ */
    public function editPwd()
    {
        $res = $this->Model->editPwd(input(), $this);
        if ($res !== true) return $this->error($res);
        $this->Model->logout();
        return $this->success('密码已重置，请用新密码登陆.');
    }
    /*------------------------------------------------------ */
    //-- 修改收款信息
    /*------------------------------------------------------ */
    public function editReceivePay()
    {
        $file_path = config('config._upload_').'receive_pay/';
        $upArr['weixin_usd'] = input('weixin_usd',0,'intval');
        if ($upArr['weixin_usd'] == 1){
            $upArr['weixin_name'] = input('weixin_name','','trim');
            if (empty($upArr['weixin_name'])){
                return $this->error('﻿请输入微信昵称.');
            }
            $upArr['weixin_account'] = input('weixin_account','','trim');
            if (empty($upArr['weixin_account'])){
                return $this->error('﻿请输入微信号.');
            }
           /* $edit_weixin_qrcode = input('edit_weixin_qrcode',0,'intval');
            if ($edit_weixin_qrcode == 1){
                $weixin_qrcode = input('weixin_qrcode','','trim');
                $upArr['weixin_qrcode'] = copyFile($weixin_qrcode,$file_path);
                if ($upArr['weixin_qrcode'] === false){
                    return $this->error('微信收款二维码处理失败，请重试.');
                }
            }*/
        }
        $upArr['alipay_usd'] = input('alipay_usd',0,'intval');
        if ($upArr['alipay_usd'] == 1){
            $upArr['alipay_name'] = input('alipay_name','','trim');
            if (empty($upArr['alipay_name'])){
                return $this->error('请输入支付宝帐号姓名.');
            }
            $upArr['alipay_account'] = input('alipay_account','','trim');
            if (empty($upArr['alipay_account'])){
                return $this->error('请输入支付宝帐号.');
            }
           /* $edit_alipay_qrcode = input('edit_alipay_qrcode',0,'intval');
            if ($edit_alipay_qrcode == 1){
                $alipay_qrcode = input('alipay_qrcode','','trim');
                $upArr['alipay_qrcode'] = copyFile($alipay_qrcode,$file_path);
                if ($upArr['alipay_qrcode'] === false){
                    return $this->error('支付宝收款二维码处理失败，请重试.');
                }
            }*/
        }
        $upArr['bank_usd'] = input('bank_usd',0,'intval');
        if ($upArr['bank_usd'] == 1){
            $upArr['bank_name'] = input('bank_name','','trim');
            if (empty($upArr['bank_name'])){
                return $this->error('请输入开户银行.');
            }
            $upArr['bank_province'] = input('bank_province',0,'intval');
            $upArr['bank_city'] =  input('bank_city',0,'intval');
            if (empty($upArr['bank_city'])){
                return $this->error('﻿请输入所属省市.');
            }
            $upArr['bank_subbranch'] = input('bank_subbranch','','trim');
            if (empty($upArr['bank_subbranch'])){
                return $this->error('请输入开户支行.');
            }
            $upArr['bank_card_number'] =  input('bank_card_number','','trim');
            if (empty($upArr['bank_card_number'])){
                return $this->error('请输入银行卡号.');
            }
            $upArr['bank_user_name'] =  input('bank_user_name','','trim');
            if (empty($upArr['bank_user_name'])){
                return $this->error('请输入持卡人名.');
            }
            $upArr['bank_idcard_munber'] =  input('bank_idcard_munber','','trim');
            if (empty($upArr['bank_idcard_munber'])){
                return $this->error('请输入身份证号码.');
            }
        }

        //验证数据是否出现变化
        $dbarr = $this->Model->field(join(',',array_keys($upArr)))->where('user_id',$this->userInfo['user_id'])->find()->toArray();
        $this->checkUpData($dbarr,$upArr,true);

        $res = $this->Model->upInfo($this->userInfo['user_id'], $upArr);
        /*if ($res < 1) {
           if (empty($edit_weixin_qrcode) == false){
               @unlink('.'.$upArr['weixin_qrcode']);
           }
           if (empty($edit_alipay_qrcode) == false){
               @unlink('.'.$upArr['weixin_qrcode']);
           }
           return $this->error('修改用户收款信息失败，请重试.');
       }
      if (empty($edit_weixin_qrcode) == false){
           @unlink('.'.$dbarr['weixin_qrcode']);
           @unlink('.'.$weixin_qrcode);
       }
       if (empty($edit_alipay_qrcode) == false){
           @unlink('.'.$dbarr['alipay_qrcode']);
           @unlink('.'.$alipay_qrcode);
       }*/
        $this->_log($this->userInfo['user_id'], '用户修改收款信息.', 'member');
        return $this->success('保存成功.');
    }
    /*------------------------------------------------------ */
    //-- 修改用户支付密码
    /*------------------------------------------------------ */
    public function editPayPwd()
    {
        $pay_password = input('pay_password','','trim');
        if (empty($pay_password))  return $this->error('请输入新的支付密码.');
        $upData['pay_password'] = f_hash($pay_password);
        $res = $this->checkCode('edit_pay_pwd',$this->userInfo['mobile'],input('code'));//验证短信验证
        if ($res !== true){
            return $this->error($res);
        }
        $pay_password = $this->Model->where('user_id',$this->userInfo['user_id'])->value('pay_password');
        if ($upData['pay_password'] == $pay_password){
            return $this->error('新支付密码与当前一致，无须修改.');
        }
        $res = $this->Model->upInfo($this->userInfo['user_id'],$upData);
        if ($res < 1) {
            return $this->error('未知错误，处理失败.');
        }

        $this->_log($this->userInfo['user_id'], '用户通过手机短信修改支付密码.', 'member');
        return $this->success('支付密码修改成功.');
    }
    //*------------------------------------------------------ */
    //-- 绑定会员手机
    /*------------------------------------------------------ */
    public function bindMobile()
    {
        $code = input('code','','trim');
        $data['mobile'] = input('mobile','','trim');
        $data['password'] = input('password','','trim');
        $data['pay_password'] = input('pay_password','','trim');
        $res = $this->checkCode('bind_mobile', $data['mobile'], $code);//验证短信验证
        if ($res !== true){
            return $this->error($res);
        }
        $res = $this->Model->bindMobile($data, $this);
        if ($res !== true) return $this->error($res);
        return $this->success('绑定手机成功.');
    }

    //生成水印图
    public function dowaterimg($bgimage,$qrcode,$pathname,$type,$header_img,$user){
        // 已经生成过这个比例的图片就直接返回了
        if (is_file($pathname)){
            return  $pathname ;
        }
        if($type == 1){
            $location1 = \think\Image::WATER_CENTER; //二维码居中
            $location2 =  [50,50];
            $location3 =  [200,140];
            $location4 =  [200,90];
        }elseif($type == 2){
            $location1 = \think\Image::WATER_SOUTH;//二维码下居中
            $location2 =  [380,50];
            $location3 =  [360,250];
            $location4 =  [360,200];
        }else{
            $location1 = [600,700];
            $location2 =  [30,900];
            $location3 =  [180,990];
            $location4 =  [180,940];
        }
        $image = \think\Image::open($bgimage);

        $image->water($qrcode, $location1,100)->save($pathname);
        if($header_img){
            $image->water($header_img, $location2,100)->save($pathname);
        }
        $image->text("昵称 ".$user['nick_name'], "hgzb.ttf", 30,"#000",$location3)->save($pathname);
        $image->text("ID ".$user['user_id'], "hgzb.ttf", 30,"#000",$location4)->save($pathname);
        return $pathname;
    }


    /*------------------------------------------------------ */
    //-- 获取签到数据
    /*------------------------------------------------------ */
    public function getSignData(){

        $year = input('year',date('Y')) * 1;
        $month = input('month',date('n')) * 1;

        $data = (new UsersSignModel)->where(['user_id'=>$this->userInfo['user_id'],'year'=>$year,'month'=>$month])->find();
        if(!$data){
            $data['days'] = '';
        }
        $return['code'] = 1;
        $return['data'] = $data;
        return $this->ajaxReturn($return);
    }


    /*------------------------------------------------------ */
    //-- 提交验证身份证
    /*------------------------------------------------------ */
    function postIdCard(){
        if ($this->userInfo['check_id_card'] == 1){
            return $this->error('实名已认证，不能修改.');
        }
        if ($this->userInfo['check_id_card'] == 2){
            return $this->error('正在审核中，不能提交.');
        }
        $upArr['id_card_number'] = input('id_card_number','','trim');
        $id_card_positive = input('id_card_positive','','trim');
        $id_card_back = input('id_card_back','','trim');
        if (empty($upArr['id_card_number'])){
            return $this->error('请填写身份证号码.');
        }
        if (empty($id_card_positive)){
            return $this->error('请上传身份证【头像面】.');
        }
        if (empty($id_card_back)){
            return $this->error('请上传身份证【国微面】.');
        }

        $file_path = config('config._upload_').'id_card/';
        $upArr['id_card_positive'] = copyFile($id_card_positive,$file_path);
        if ($upArr['id_card_positive'] === false){
            return $this->error('身份证【头像面】处理失败，请重试.');
        }
        $upArr['id_card_back'] = copyFile($id_card_back,$file_path);
        if ($upArr['id_card_back'] === false){
            return $this->error('身份证【国微面】处理失败，请重试.');
        }
        $upArr['check_id_card'] = 2;
        $res = $this->Model->upInfo($this->userInfo['user_id'],$upArr);
        if ($res < 1){
            @unlink('.'.$upArr['id_card_positive']);
            @unlink('.'.$upArr['id_card_back']);
            return $this->error('提交失败，请重试.');
        }
        @unlink('.'.$id_card_positive);
        @unlink('.'.$id_card_back);
        return $this->success('﻿提交成功，等待审核.');
    }
    /*------------------------------------------------------ */
    //-- 分享信息
    /*------------------------------------------------------ */
    public function shareInfo()
    {
        $is_wxmp = input('is_wxmp',0,'intval');
        if ($this->userInfo['user_id'] < 1){
            return $this->error('请登录后再操作.');
        }

        if ($this->userInfo['role']['is_share'] == 0){
            return $this->error('请升级身份后再操作.');
        }
        $data['share_qrcode'] = $is_wxmp == 0 ? $this->Model->getMyQrCode('/pages/member/passport/register') : $this->Model->getUserMiniQrcode('pages/member/passport/register');
        $data['share_qrcode'] = trim($data['share_qrcode'],'.');
        $data['share_headimgurl'] = $this->Model->getHeadImg($this->userInfo['user_id'],$this->userInfo['headimgurl']);
        $data['share_headimgurl'] = trim($data['share_headimgurl'],'.');
        $data['share_nick_name'] = $this->userInfo['nick_name'];
        $data['share_hb_text'] = settings('share_hb_text');
        if (empty($data['share_hb_text']) == false){
            $site_name = settings('site_name');
            $data['share_hb_text'] = str_replace('{网站名称}',$site_name, $data['share_hb_text']);
            $data['share_hb_text'] = explode("\n",$data['share_hb_text']);
        }else{
            $data['share_hb_text'] = [];
        }


        $data['share_token'] = $this->userInfo['token'];
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 签约
    /*------------------------------------------------------ */
    public function signContract()
    {
        $signature = input('signature');
        if (empty($signature)){
            return $this->error('请签名后再提交.');
        }
        $extend = getFileExtend($signature);
        if ($extend == false){
            return $this->error('未能识别签名图片，请尝试重新上传.');
        }
        $file_path = config('config._upload_') . 'signature/';
        $file_name = random_str(15).rand(10,99).'.'.$extend[1];
        $res = base64ToImage($signature,$file_path,$file_name);
        if ($res == false){
            return $this->error('签名保存失败，请重试.');
        }
        $upArr['signature'] = trim($file_path.$file_name, '.');
        $res = $this->Model->where('user_id', $this->userInfo['user_id'])->update($upArr);
        if ($res < 1) {
            return $this->error('未知错误，处理失败.');
        }
        return $this->success('提交成功.');
    }


    /*------------------------------------------------------ */
    //-- role签约
    /*------------------------------------------------------ */
    public function signRoleContract()
    {
        $signature = input('signature');
        if (empty($signature)){
            return $this->error('请签名后再提交.');
        }
        $extend = getFileExtend($signature);
        if ($extend == false){
            return $this->error('未能识别签名图片，请尝试重新上传.');
        }
        $file_path = config('config._upload_') . 'signature/';
        $file_name = random_str(15).rand(10,99).'.'.$extend[1];
        $res = base64ToImage($signature,$file_path,$file_name);
        if ($res == false){
            return $this->error('签名保存失败，请重试.');
        }
        $upArr['signature'] = trim($file_path.$file_name, '.');
        if($res){
            if(empty($upArr['signature'])) return $this->error('请阅读合同并签名');
            // 合同
            $contract = settings('role_contract');
            $region_contract_first_party = settings('role_contract_first_party');
            $region_contract_seal = settings('role_contract_seal');
            $region_contract_seal_x = settings('role_contract_seal_x');
            $region_contract_seal_y = settings('role_contract_seal_y');
            $contract = str_replace('{甲方姓名}',$region_contract_first_party,$contract);
            $contract = str_replace('{乙方姓名}',$this->userInfo['nick_name'],$contract);
            $contract = str_replace('{乙方身份证号码}',$this->userInfo['id_card_number'],$contract);
//            $contract = str_replace('{收款人姓名}',$data['payee'],$contract);
//            $contract = str_replace('{收款账号}',$data['account'],$contract);
//            $contract = str_replace('{开户行}',$data['bank'],$contract);
//            $contract = str_replace('{合同时间}',date('Y年m月d日').'至'.date('Y年m月d日',strtotime('+12 month',time())),$contract);
            $this->assign("contract",$contract);
            $this->assign("first_party",$region_contract_first_party);
            $this->assign("signingDate",date('Y年m月d日'));
            $this->assign("partyBSign",$upArr['signature']);
            $this->assign("contract_seal",$region_contract_seal);
            $html = $this->fetch('pdf/tpl')->getContent();
            $fileName = $this->userInfo['user_id'].'_'.$this->userInfo['role_id']. '_' . rand(1000, 9999). '_' . time();
            $path = '/upload/pdf/';
//            htmlToPdf($html, '合同', $fileName, $region_contract_seal, $region_contract_seal_x, $region_contract_seal_y, "F");
            htmlToPdf($html, '合同', $fileName, '', '', '', "F");
            $upArr['contract_pdf'] = $path . $fileName . '.pdf';
        }
        $res = $this->Model->upInfo($this->userInfo['user_id'],$upArr);
        if ($res < 1) {
            return $this->error('未知错误，处理失败.');
        }
        return $this->success('提交成功.');
    }

}
