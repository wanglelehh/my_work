<?php
namespace app\mainadmin\controller;

use app\AdminController;
use app\mainadmin\model\AdminUserModel;

/**
 * 后台首页
 * Class Index
 * @package app\store\controller
 */
class Index extends AdminController
{
    protected function initialize()
    {
        $this->isCheckPriv = false;
        parent::initialize();
    }
    /*------------------------------------------------------ */
    //-- 默认首页，负责跳转
    /*------------------------------------------------------ */
    public function index()
    {
        $menus = $this->getMenuList();
        $isLzyl = input('isLzyl',0,'intval');
        if ($isLzyl == 1){
            $menus['menus'][9]['submenu'][9998] = ['id'=>9998,'name'=>'测试专用','url'=>url('byTest/index')];
            $menus['menus'][9]['submenu'][9999] = ['id'=>9999,'name'=>'开发专用','url'=>url('lzyl/index')];
            $menus['menus'][9]['submenu'][10000] = ['id'=>10000,'name'=>'菜单管理','url'=>url('menus/index')];
        }
        $this->assign('menus',$menus);// 后台菜单
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 欢迎页
    /*------------------------------------------------------ */
    public function welcome()
    {
        $this->isClearSetTip();//清理数据提示
        $adminInfo = (new AdminUserModel)->info(AUID);
        $this->assign('adminInfo',$adminInfo);// 管理员信息
        $islzasyn = 0;
        if (function_exists('lzasyn')){
            $islzasyn = 1;
        }
        $this->assign('islzasyn',$islzasyn);
        $helloInfo = '';
        $h = date("H");
        if($h<11){
            $helloInfo = "早上好!";
        }else if($h<13){
            $helloInfo = "中午好!";
        }else if($h<17){
            $helloInfo = "下午好!";
        }else{
            $helloInfo = "晚上好！";
        }
        $this->assign('helloInfo',$helloInfo);
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 清理全部缓存
    /*------------------------------------------------------ */
    public function clearCache()
    {
        $redis = new \think\cache\driver\Redis();
        $keysdata = $redis->keys(config('cache.prefix') . '*');
        foreach ($keysdata as $key => $val) {
            if (stripos($val, config('cache.prefix').'devlogin_') !== 0 && stripos($val, config('cache.prefix').'uniappLogin_') !== 0) {
                $redis->rm($val);
            }
        }
        return $this->success('清理完成.','/lzadmin.php');
    }

}
