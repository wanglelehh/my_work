<?php

namespace app\channel\model;
use think\facade\Cache;
use think\Db;

use app\member\model\UsersModel;
use app\member\model\RoleModel;
use app\member\model\UsersBindSuperiorModel;
//*------------------------------------------------------ */
//-- 代理会员表
/*------------------------------------------------------ */

class ProxyUsersModel extends UsersModel
{
    protected $mkey = 'channel_proxy_user_mkey_';
    /*------------------------------------------------------ */
    //--  清除memcache
    /*------------------------------------------------------ */
    public function cleanMemcache($user_id)
    {
        Cache::rm($this->mkey . $user_id);
    }
    /*------------------------------------------------------ */
    //-- 获取用户信息
    //-- $user_id 查询值
    //-- $type 查询类型
    //-- $isCache 是否调用缓存
    /*------------------------------------------------------ */
    public function info($user_id,  $isCache = true)
    {
        if (empty($user_id)) return [];
        if ($isCache == true) $info = Cache::get($this->mkey.$user_id);
        if (empty($info) == true){
            $info = (new UsersModel)->info($user_id,'user_id',false);
            if (empty($info)) {
                return [];
            }
            $RegionModel = new \app\mainadmin\model\RegionModel();
            $info['region_text'] = '';
            if ($info['district'] > 0){
                $region = $RegionModel->info($info['district']);
                $info['region_text'] = $region['merger_name'];
            }
            $info['bank_region_text'] = '';
            if ($info['bank_city'] > 0){
                $region = $RegionModel->info($info['bank_city']);
                $info['bank_region_text'] = $region['merger_name'];
            }
            if ($info['role_pid'] == 0){
                $info['supply_name'] = '厂家';
                $info['parent_role'] = '厂家';
                $info['supply_headimgurl'] = settings('logo');
                $info['supply_mobile'] = settings('channel_mobile');
            }else{
                $parentUser = $this->info($info['role_pid']);
                $info['supply_name'] = $parentUser['real_name'];
                $info['parent_role'] = $parentUser['role']['role_name'];
                $info['supply_headimgurl'] = $parentUser['headimgurl'];
                $info['supply_mobile'] = $parentUser['mobile'];
            }
            Cache::set($this->mkey.$user_id,$info, 30);
        }
        $info['proxy_account'] = (new WalletModel)->getWalletInfo($user_id,false);//获取代理会员帐户信息
        return $info;
    }
    /*------------------------------------------------------ */
    //-- 后端会员详细调用
    //-- $user_id 查询值
    /*------------------------------------------------------ */
    public function returnInfo($user_id){
        $stockTotal = (new StockModel)->where('user_id',$user_id)->field('purchase_type,sum(goods_number) as StockTotal')->group('purchase_type')->select()->toArray();
        $stockStat['cloudStockTotal'] = 0;
        $stockStat['entityStockTotal'] = 0;
        foreach ($stockTotal as $stock){
            if ($stock['purchase_type'] == 1){
                $stockStat['cloudStockTotal'] = $stock['StockTotal'];
            }else{
                $stockStat['entityStockTotal'] = $stock['StockTotal'];
            }
        }
        $data['stockStat'] = $stockStat;
        $data['account'] = (new WalletModel)->getWalletInfo($user_id,false);//获取代理会员帐户信息
        return $data;
    }

    /*------------------------------------------------------ */
    //-- 获取用户id
    //-- $field string 字段
    //-- $value string 查询内容
    /*------------------------------------------------------ */
    public function getUserIdByField($field,$value)
    {
        $where[] = ['is_channel','=',1];
        if ($field == 'mobile'){
            $where[] = ['mobile','=',$value];
            $user_id = $this->where($where)->value('user_id');
            if ($user_id < 1){
                return '没找到相关"手机号码：'.$value.'"代理信息.';
            }
        }elseif ($field == 'id_card_number'){
            $where[] = ['id_card_number','=',$value];
            $user_id = $this->where($where)->value('user_id');
            if ($user_id < 1){
                return '没找到相关"身份证号码：'.$value.'"代理信息.';
            }
        }else{
            return '发生错误，没有相关查询定义.';
        }
        return $user_id;
    }



    /*------------------------------------------------------ */
    //-- 获取邀请海报，每张海报小时重新生成
    //-- $user_id int 代理会员ID
    //-- $role_id int 邀请的层级
    /*------------------------------------------------------ */
    public function getInvitePoster($user_id,$role_id,$is_wxmp){
        $file_path = config('config._upload_') .'snap_file/invite/'.date('d').'/';
        makeDir($file_path);
        $userInfo = $this->info($user_id);
        $file =  $file_path.$user_id.'_'.$role_id.'_'.date('H').'.jpg';
        $return['file'] = trim($file,'.');
        $return['url'] = settings("channel_web_url") . '/pages/member/passport/register?share_token=' . $userInfo['token'] . '&role_id=' . $role_id;
        if (file_exists($file) == false) {
            $temp = (new LicenseTempModel)->where('type', 2)->find();
            if (empty($temp)) {
                return '未设置邀请海报.';
            }
            $UsersModel = new UsersModel();
            $qrfile = $is_wxmp == 0 ? $UsersModel->getMyQrCode('/pages/member/passport/register?role_id='.$role_id) : $UsersModel->getUserMiniQrcode('/pages/member/passport/register',$role_id);

            $content = htmlspecialchars_decode($temp['content']);
            $content = json_decode($content, true);
            $select_dom['avatar'] = ['title' => '头像', 'img' => '.' . $userInfo['headimgurl']];
            $select_dom['qrcode'] = ['title' => '二维码', 'img' => $qrfile];
            $select_dom['real_name'] = ['title' => '姓名', 'text' => $userInfo['real_name']];
            $select_dom['mobile'] = ['title' => '手机号码', 'text' => $userInfo['mobile']];
            $select_dom['id_card_number'] = ['title' => '身份证号码', 'text' => $userInfo['id_card_number']];
            $select_dom['proxy_level'] = ['title' => '授权级别', 'text' => $userInfo['role']['role_name']];
            $roleList = (new RoleModel)->getRows();
            $select_dom['invite_level'] = ['title' => '邀请您成为', 'text' => $roleList[$role_id]['role_name']];
            foreach ($content as $key => $arr) {
                $arr['info'] = $select_dom[$key];
                $content[$key] = $arr;
            }
            $data['content'] = $content;
            $data['temp_bg'] = $temp['temp_bg'];
            $MergeImg = new \lib\MergeImg();
            $MergeImg->licenseImg($data, $file);
            @unlink($qrfile);
        }
        return $return;
    }
    /*------------------------------------------------------ */
    //-- 获取授权证书，每张海报每小时重新生成
    //-- $userInfo array 代理会员信息
    /*------------------------------------------------------ */
    public function getAuthorizedImg(&$userInfo){
        $file_path = config('config._upload_') .'snap_file/authorized/'.date('d').'/';
        makeDir($file_path);
        $file =  $file_path.$userInfo['user_id'].'_'.date('H').'.jpg';
        $return['file'] = trim($file,'.');
        $return['url'] = settings("channel_web_url") . '/pagesB/channel/center/authorizedShow?uid=' . $userInfo['user_id'];
        if (file_exists($file) == false) {
            $where = [];
            $where[] = ['type','=',1];
            $where[] = ['is_default','=',1];
            $temp = (new LicenseTempModel)->where($where)->find();
            if (empty($temp)) {
                return false;
            }
            include EXTEND_PATH . 'phpqrcode/phpqrcode.php';//引入PHP QR库文件
            $QRcode = new \phpqrcode\QRcode();
            $qrfile = $file_path .$userInfo['user_id'].'_'.$userInfo['role_id'].'.png';
            $QRcode::png($return['url'], $qrfile, "L", 10, 1, 2, true);
            $content = htmlspecialchars_decode($temp['content']);
            $content = json_decode($content, true);
            $select_dom['avatar'] = ['title' => '头像', 'img' => '.' . $userInfo['headimgurl']];
            $select_dom['qrcode'] = ['title' => '二维码', 'img' => $qrfile];
            $select_dom['real_name'] = ['title' => '姓名', 'text' => $userInfo['real_name']];
            $select_dom['mobile'] = ['title' => '手机号码', 'text' => $userInfo['mobile']];
            $select_dom['id_card_number'] = ['title' => '身份证号码', 'text' => $userInfo['id_card_number']];
            $select_dom['proxy_level'] = ['title' => '授权级别', 'text' => $userInfo['role']['role_name']];
            foreach ($content as $key => $arr) {
                $arr['info'] = $select_dom[$key];
                $content[$key] = $arr;
            }
            $data['content'] = $content;
            $data['temp_bg'] = $temp['temp_bg'];
            $MergeImg = new \lib\MergeImg();
            $MergeImg->licenseImg($data, $file);
            @unlink($qrfile);
        }
        return $return;
    }




}
