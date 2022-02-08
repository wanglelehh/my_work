<?php

namespace app;

use think\facade\Cache;
use think\facade\Session;

include_once dirname(__DIR__) . '/application/commonAdmin.php';

/**
 * 后台控制器基类
 * Class BaseController
 * @package app\store\controller
 */
class AdminController extends BaseController
{
    /* @var array $admin 登录信息 */
    protected $admin;


    /* @var string $route 当前菜单组名 */
    /* @var array $allowAllAction 登录验证白名单 */
    protected $allowAllAction = [
        // 登录页面
        'passport/login',
        'passport/captcha',
    ];

    /* @var array $notLayoutAction 无需全局layout */
    protected $notLayoutAction = [
        // 登录页面
        'passport/login',
        'passport/captcha',
    ];
    public $isCheckPriv = true;
    public $store_id = 0;//当前默认为总后台，门店值默认为0

    public $supplyer_id = 0;//指定供应商
    public $is_supplyer = false;//是否查询供应商相关
    public $initialize_isretrun = true;

    /**
     * @var BaseModel
     */
    public $Model;

    /*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    protected function initialize()
    {
        parent::initialize();

        // 当前路由信息
        $this->getRouteinfo();
        if ($this->initialize_isretrun == false) {
            if (defined('AUID') == false) define('AUID', 0);
            return false;
        }
        // 商家登录信息
        $this->admin = Session::get('main_admin');
        // 验证登录
        $this->checkLogin();
        if (defined('AUID') == false) {
            define('AUID', $this->admin['info']['user_id']);
        }

        if ($this->isCheckPriv == true) {
            //自动验证权限
            $this->_priv($this->module, $this->controller, $this->action);
        }
        // 全局layout
        $this->layout();
    }
    /*------------------------------------------------------ */
    //-- 清理数据提示
    /*------------------------------------------------------ */
    public function isClearSetTip(){
        //清理数据提示
        $isClearSetTip = false;
        if ($this->admin['info']['role_action'] == 'all' && file_exists(DATA_PATH.$_SERVER['SERVER_NAME']) == false){
            $isClearSetTip = true;
        }
        $this->assign('isClearSetTip',$isClearSetTip);
    }
    /*------------------------------------------------------ */
    //-- 全局layout模板输出址
    /*------------------------------------------------------ */
    private function layout()
    {
        $this->assign('isWrapper',input('isWrapper',0,'intval'));
        // 验证当前请求是否在白名单,ajax调用也不执行
        if ($this->request->isAjax() == false || !in_array($this->routeUri, $this->notLayoutAction)) {
            if ($this->action == 'info' && $this->request->isPost() == true) return false;//如果是info页提交也不执行
            // 输出到view
            $this->assign([
                '_action' => $this->action,                    //方法名称
                '_module' => $this->module,                   // 模块名称
                'admin' => $this->admin,                       // 登录信息
                'baseUrl' => 'mainadmin@layouts/main_base',
            ]);
            //print_r($this->menus_list);exit;
        }
    }
    /*------------------------------------------------------ */
    //-- 权限验证
    /*------------------------------------------------------ */
    protected function _priv($module = '', $controller = '', $action = '', $isAll = true, $isReturn = false)
    {
        static $allPriv;
        if ($this->isCheckPriv == false && $isReturn == false) return true;
        if (in_array($module.'/'.$controller,['mainadmin/edit_pwd'])){
            return true;
        }
        $role_action = $this->admin['info']['role_action'];
        if ($role_action == 'all' ) {
            if ($isAll == true) return true;
            if ($isReturn == true) return false;
            $this->error('你无操作权限.');
        }
        if (isset($allPriv) == false) {
            $allPriv = (new \app\mainadmin\model\AdminPrivModel)->allPriv();
        }

        $privIds = [];
        $module_controller = str_replace(['_', 'sysadmin.'], '', $module . ':' . $controller . ':');
        foreach ($allPriv as $row) {
            foreach ($row['right'] as $right) {
                if (in_array($right, [$module_controller . $action, $module_controller . 'allpriv'])) {
                    $privIds[] = $row['id'];
                }
            }
        }
        $isTrue = empty($privIds) ? false : array_intersect($role_action, $privIds);
        if ($isReturn == true) return $isTrue;
        if ($isTrue == true) return true;
        $this->error('你无操作权限.');
    }
    /*------------------------------------------------------ */
    //-- 权限验证，用于判断返回真假
    /*------------------------------------------------------ */
    public function _privIf($module = '', $controller = '', $action = '', $isAll = true)
    {
        return $this->_priv($module, $controller, $action, $isAll, true);
    }
    /*------------------------------------------------------ */
    //-- 获取有权限的菜单
    /*------------------------------------------------------ */
    protected function getMenuList()
    {
        $mkey = 'main_menu_priv_list_' . AUID;
        $data = Cache::get($mkey);
        if (empty($data) == false) {
            return $data;
        }
        $rows = (new \app\mainadmin\model\MenuListModel)->where('status', 1)->order('level DESC,sort_order DESC')->select()->toArray();
        $settings = settings();
        //权限过滤
        foreach ($rows as $row) {
            if ($row['name'] == '优惠券管理' && empty($settings['sys_model_shop_bonus'])){
                continue;
            }
            if ($row['name'] == '拼团管理' && empty($settings['sys_model_shop_fightgroup'])){
                continue;
            }

            if ($row['name'] == '积分商品' && empty($settings['sys_model_shop_integral'])){
                continue;
            }
            if ($row['name'] == '限时优惠' && empty($settings['sys_model_shop_favour'])){
                continue;
            }
            if ($row['name'] == '公众号管理' && empty($settings['sys_model_plc'])){
                continue;
            }
            if ($row['name'] == '小程序管理' && empty($settings['sys_model_xcx'])){
                continue;
            }
            if (($row['name'] == '直播间' || $row['name'] == '直播商品库') && empty($settings['sys_model_xcx_live_room'])){
                continue;
            }
            if ($row['module'] == 'luckdraw' && empty($settings['luckdraw_all'])){
                continue;
            }
            if ($row['name'] == '大转盘' && empty($settings['luckdraw_turntable'])){
                continue;
            }
            if ($row['name'] == '多语言管理' && empty($settings['sys_model_more_language'])){
                continue;
            }
            if (empty($row['controller']) == false) {
                if (isset($settings['sys_model_'.$row['module']]) == true && $settings['sys_model_'.$row['module']] == 0){
                    continue;
                }
                if ($this->_privIf($row['module'], $row['controller'], $row['action']) == false) {
                    continue;
                }
            }
            if ($row['name'] == '待退货（供应商）' && empty($settings['sys_model_supplyer'])){
                continue;
            }
            $menus[] = $row;
        }
        $_data = [];
        foreach ($menus as $row) {
            $key = $row['id'];
            $row['url'] = '';
            if (empty($row['controller']) == false) {
                $row['url'] = url($row['module'].'/'. $row['controller'].'/'.$row['action']);
            }
            if ($row['pid'] > 0) {
                if (empty($_data[$row['id']]) == false) {
                    $row['submenu'] = $_data[$row['id']];
                    unset($_data[$row['id']]);
                }
                $_data[$row['pid']][$key] = $row;
            } else {
                $row['submenu'] = $_data[$row['id']];
                if (empty($row['submenu']) == false) {
                    $data['menus'][$key] = $row;
                }
            }
        }

        $menus = $data['menus'];
        foreach ($menus as $group => $menu) {
            foreach ($menu['submenu'] as $key=>$submenu){
                if (empty($submenu['controller']) == false) {
                    if (empty($data['top_menus'][$group])) {
                        $data['top_menus'][$group] = ['sel_id' => $submenu['id'],'id' => $menu['id'],'name' => $menu['name'], 'icon' => $menu['icon'],'module' => $submenu['module'], 'controller' => $submenu['controller'], 'action' => $submenu['action']];
                    }
                    continue;
                }
                foreach ($submenu['submenu'] as $keyB=>$submenuB){
                    if (empty($submenuB['controller']) == false) {
                        if (empty($data['top_menus'][$group])) {
                            $data['top_menus'][$group] = ['sel_id' => $submenuB['id'],'id' => $menu['id'],'name' => $menu['name'], 'icon' => $menu['icon'],'module' => $submenuB['module'], 'controller' => $submenuB['controller'], 'action' => $submenuB['action']];
                        }
                        continue;
                    }
                    if (empty($submenuB['submenu'])){
                        unset($data['menus'][$group]['submenu'][$key]['submenu'][$keyB]);
                        continue;
                    }
                    foreach ($submenuB['submenu'] as $keyC=>$submenuC){
                        if (empty($submenuC['controller']) == false) {
                            if (empty($data['top_menus'][$group])) {
                                $data['top_menus'][$group] = ['sel_id' => $submenuC['id'],'id' => $menu['id'],'name' => $menu['name'], 'icon' => $menu['icon'],'module' => $submenuC['module'], 'controller' => $submenuC['controller'], 'action' => $submenuC['action']];
                            }
                            continue;
                        }
                        if (empty($submenuC['submenu'])){
                            unset($submenu['submenu'][$keyB]);
                            continue;
                        }
                    }
                    if (empty($submenuB['submenu'])){
                        unset($data['menus'][$group]['submenu'][$key]['submenu']);
                        continue;
                    }
                }
                if (empty($submenu['submenu'])){
                    unset($data['menus'][$group]['submenu'][$key]);
                    continue;
                }
            }
            if (empty($data['top_menus'][$group])) {
                unset($data['menus'][$group]);
            }
        }
        $menus = $data['menus'];
        $data['menus'] = [];
        foreach ($menus as $key=>$menu){
            $data['menus'][$key] = $this->runSubmenu($menu);
        }
        Cache::set($mkey, $data, 300);
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 重新排序菜单
    /*------------------------------------------------------ */
    private function runSubmenu($menu){
        if (empty($menu['submenu']) == false){
            $submenus = $menu['submenu'];
            $menu['submenu'] = [];
            foreach ($submenus as $submenu){
                $submenu = $this->runSubmenu($submenu);
                $menu['submenu'][] = $submenu;
            }
        }
        return $menu;
    }
    /**
     * 验证登录状态
     */
    private function checkLogin()
    {
        // 验证当前请求是否在白名单
        if (in_array($this->routeUri, $this->allowAllAction)) {
            return true;
        }
        // 验证登录状态
        if (empty($this->admin) || (int)$this->admin['is_login'] !== 1) {
            if ($this->request->isAjax()) {
                return $this->error('登陆超时，请重新登陆.');
            }
            $this->redirect($_SERVER['SCRIPT_NAME'] . '/mainadmin/passport/login');
            return false;
        }
        return true;
    }

    //*------------------------------------------------------ */
    //-- 清理字典数据缓存
    /*------------------------------------------------------ */
    public function clearDict()
    {
        \app\mainadmin\model\PubDictModel::cleanMemcache(input('group'));
        echo '执行成功.';
        exit;
    }


    /*------------------------------------------------------ */
    //-- 添加/修改
    /*------------------------------------------------------ */
    public function info()
    {
        $pk = $this->Model->pk;
        if ($this->request->isPost()) {
            if (false === $data = $_POST) {
                $this->error($this->Model->getError());
            }
            if (empty($data[$pk])) {
                $this->_priv($this->module, $this->controller, 'add');
                if (method_exists($this, 'beforeAdd')) {
                    $data = $this->beforeAdd($data);
                }
                unset($data[$pk]);
                $res = $this->Model->allowField(true)->save($data);
                if ($res > 0) {
                    $data[$pk] = $this->Model->$pk;
                    if (method_exists($this->Model, 'cleanMemcache')) $this->Model->cleanMemcache($res);
                    if (method_exists($this, 'afterAdd')) {
                        $result = $this->afterAdd($data);
                        if (is_array($result)) return $this->ajaxReturn($result);
                    }
                    return $this->success('添加成功.', url('index'));
                }
            } else {
                $this->_priv($this->module, $this->controller, 'edit');
                if (method_exists($this, 'beforeEdit')) {
                    $data = $this->beforeEdit($data);
                }
                $res = $this->Model->allowField(true)->save($data, $data[$pk]);
                if ($res > 0) {
                    if (method_exists($this->Model, 'cleanMemcache')) $this->Model->cleanMemcache($data[$pk]);
                    if (method_exists($this, 'afterEdit')) {
                        $result = $this->afterEdit($data);
                        if (is_array($result)) return $this->ajaxReturn($result);
                    }
                    return $this->success('修改成功.', url('index'));
                }
            }
            return $this->error('操作失败.');
        }

        $id = input($pk, 0, 'intval');
        $row = ($id == 0) ? $this->Model->getField() : $this->Model->find($id);

        if ($id > 0 && empty($row) == false) {
            $row = $row->toArray();
        }
        if (method_exists($this, 'asInfo')) {
            $row = $this->asInfo($row);
        }

        $this->assign("row", $row);
        $ishtml = input('ishtml', 0, 'intval');
        if ($this->request->isAjax() && $ishtml == 0) {
            $result['code'] = 1;
            $result['data'] = $this->fetch('info')->getContent();
            return $this->ajaxReturn($result);
        }

        return $this->fetch('info');

    }

    /**
     * ajax修改单个字段值
     */
    public function ajaxEdit()
    {

        $pk = $this->Model->getPk();
        $id = input($pk, 0, 'intval');
        $field = input('field', '', 'trim');
        if ($id == '' || $field == '') return $this->error('缺少必要传参.');
        $data[$field] = input('value', '', 'trim');
        $toggle = input('toggle');
        if ($toggle) {
            $data[$field] = $toggle === 'true' || $toggle === 1 ? 1 : 0;
        }

        if (method_exists($this, 'beforeAjax')) {
            $data = $this->beforeAjax($id, $data);
        }
        $map[$pk] = $id;
        //允许异步修改的字段列表  放模型里面去 TODO
        if (false !== $this->Model->save($data, $map)) {
            if (method_exists($this, 'afterAjax')) {
                $this->afterAjax($id, $data);
            }
        }
        return $this->success();
    }


}
