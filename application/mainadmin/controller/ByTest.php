<?php
namespace app\mainadmin\controller;

use app\AdminController;
use think\Db;
/**
 * 测试使用
 */
class ByTest extends AdminController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize(){
        parent::initialize();
    }
    protected function checkCode(){
        $check_code = input('check_code');
        if (empty($check_code)){
            return $this->error('请输入校验码.');
        }
        if ($check_code != 'lz'.date('Ymd')){
            return $this->error('校验码错误.');
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index(){
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 批量生成会员，默认生成30个会员，由1至30
    /*------------------------------------------------------ */
    public function batchAddUser()
    {
        $UsersModel = new \app\member\model\UsersModel();
        $count = $UsersModel->where('user_id',1)->count();
        if ($count > 0){
            return $this->error('已执行过自动生成，不能重复操作.');
        }
        $isProxyUsersModel = false;
        if (class_exists('app\channel\model\ProxyUsersModel') == true) {
            $isProxyUsersModel = true;
            $ProxyUsersModel = new \app\channel\model\ProxyUsersModel();
            $WalletModel = new \app\channel\model\WalletModel();
        }
        $RoleModel = new \app\member\model\RoleModel();
        $role_id = $RoleModel->order('level DESC')->value('role_id');//获取当前最低级别
        for ($i=1;$i<=30;$i++){
            $inArr = [];
            $inArr['user_id'] = $i;
            $inArr['mobile'] = $i + 13000000000;
            $inArr['token'] = $inArr['mobile'];
            $inArr['password'] = 'a65412a430afb7a2590ede4f25a92c22';
            $inArr['is_channel'] = 1;
            $inArr['role_id'] = $role_id;
            $UsersModel->create($inArr);

            if ($isProxyUsersModel == true){
                $inArr = [];
                $inArr['user_id'] = $i;
                $inArr['status'] = 1;
                $ProxyUsersModel->create($inArr);
                $inArr = [];
                $inArr['user_id'] = $i;
                $WalletModel->create($inArr);
            }
        }
        return $this->success('成功批量生成30个会员.');
    }

    /*------------------------------------------------------ */
    //-- 测试创业金
    /*------------------------------------------------------ */
    public function runCyj(){
        $this->checkCode();
        Db::startTrans();
        $res = (new \app\channel\model\RewardLogModel)->runCyj(true);
        if ($res !== true){
            Db::rollback();
            return $this->error($res);
        }
        Db::commit();
        return $this->success('执行创业金成功');
    }
    /*------------------------------------------------------ */
    //-- 测试月分红
    /*------------------------------------------------------ */
    public function runYfh(){
        $this->checkCode();
        Db::startTrans();
        $res = (new \app\channel\model\RewardLogModel)->runYfh(true);
        if ($res !== true){
            Db::rollback();
            return $this->error($res);
        }
        Db::commit();
        return $this->success('执行月分红成功');
    }
}
