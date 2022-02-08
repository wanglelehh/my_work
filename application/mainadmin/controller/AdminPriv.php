<?php

namespace app\mainadmin\controller;

use app\AdminController;
use app\mainadmin\model\AdminPrivModel;
use app\mainadmin\model\MenuListModel;

//*------------------------------------------------------ */
//-- 管理员权限管理
/*------------------------------------------------------ */

class AdminPriv extends AdminController
{
    //*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new AdminPrivModel();
    }
    /*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
    public function index()
    {
        $this->getList(true);
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false)
    {
        $where[] = ['is_del', '=', 0];
        $this->sqlOrder = 'id DESC';
        $data = $this->getPageList($this->Model, $where);
        $this->assign("data", $data);
        $menuList = $this->getMenuList();
        $this->assign("menu", $menuList['top_menus']);
        if ($runData == false) {
            $data['content'] = $this->fetch('list')->getContent();
            unset($data['list']);
            return $this->success('', '', $data);
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 调用详细页
    /*------------------------------------------------------ */
    public function asInfo($data)
    {
        $where[] = ['pid', '=', 0];
        $where[] = ['status', '=', 1];
        $menuList = (new MenuListModel)->where($where)->order('sort_order DESC')->select()->toArray();
        $this->assign("menuList", $menuList);

        if ($data['id'] > 0) {
            $data['right'] = explode(',', $data['right']);
        } else {
            $data['module'] = 0;
            $data['right'] = [];
        }
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 获取关联主菜单的子菜单
    /*------------------------------------------------------ */
    public function getMenus()
    {
        $menu_id = input('menu_id',0,'intval');
        $menuList = $this->getMenuList();
        $menus = $menuList['menus'][$menu_id];
        $modules = [];
        $data['modules'] = $this->getMenuSub($menus,$modules);
        if ($menus['name'] == '用户'){
            $data['modules'][] = 'member:AccountLog';
        }
        return $this->success('', '', $data);
    }
    public function getMenuSub($menus,&$modules)
    {
        if (empty($menus['module']) == false){
            $controller = str_replace(['sys_admin.','_'],['',' '],$menus['controller']);
            $controller = str_replace(' ','',ucwords($controller));
            $controller = $menus['module'].':'.$controller;
            if (in_array($controller,$modules) == false){
                $modules[] = $controller;
            }
        }
        if (empty($menus['submenu']) == false){
            foreach ($menus['submenu'] as $menus){
                $this->getMenuSub($menus,$modules);
            }
        }
        return $modules;
    }
    /*------------------------------------------------------ */
    //-- 获取类中的方法
    /*------------------------------------------------------ */
    public function getClassAction()
    {
        $control = input('controller');
        if (empty($control)) return $this->error('请选择控制器.');
        $control = explode(':', $control);
        $file_path = 'app\\' . $control[0] . '\\controller\\';
        if ($control[0] != 'mainadmin') {
            $file_path .= 'sys_admin\\';
        }
        $advContrl = get_class_methods($file_path . str_replace('.','\\',$control[1]));
        $baseContrl = get_class_methods('app\BaseController');
        $diffArray = array_diff($advContrl, $baseContrl);
        $html = '';
        $diffFun = ['_priv', '_privIf', 'clearDict', 'beforeAdd', 'afterAdd', 'beforeEdit', 'afterEdit', 'afterEdit',  'asInfo'];
        foreach ($diffArray as $val) {
            if (in_array($val, $diffFun) == false) {
                $html .= "<option value='" . $val . "'>" . $val . "</option>";
            }
        }
        $html .= "<option value='add'>add - 默认，设置未必有用</option>";
        $html .= "<option value='eidt'>edit - 默认，设置未必有用</option>";
        $data['html'] = $html;
        return $this->success('', '', $data);
    }
    /*------------------------------------------------------ */
    //-- 添加前处理
    /*------------------------------------------------------ */
    public function beforeAdd($data)
    {
        if (empty($data['name'])) return $this->error('权限名称不能为空.');
        $data['right'] = join(',', $data['right']);
        $map['name'] = $data['name'];
        $map['menu_id'] = $data['menu_id'];
        $count = $this->Model->where($map)->count();
        if ($count > 0) return $this->error('已存在相同的权限名称，不允许重复添加.');
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 添加后处理
    /*------------------------------------------------------ */
    public function afterAdd($data)
    {
        $this->_Log($data['id'], '添加权限:' . $data['name']);
    }
    /*------------------------------------------------------ */
    //-- 修改前处理
    /*------------------------------------------------------ */
    public function beforeEdit($data)
    {
        $map['id'] = $data['id'];
        if (empty($data['name'])) return $this->error('权限名称不能为空.');
        $data['right'] = join(',', $data['right']);
        //验证数据是否出现变化
        $dbarr = $this->Model->field(join(',', array_keys($data)))->where($map)->find()->toArray();
        $this->checkUpData($dbarr, $data);
        $where[] = ['id', '<>', $map['id']];
        $where[] = ['name', '=', $data['name']];
        $where[] = ['menu_id', '=', $data['menu_id']];
        $count = $this->Model->where($where)->count();
        if ($count > 0) return $this->error('已存在相同的权限名称，不允许重复添加.');
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 删除
    /*------------------------------------------------------ */
    public function delete()
    {
        $id = input('id', 0, 'intval');
        if ($id < 1) return $this->error('请选择需要删除的权限.');
        $res = $this->Model->where(['id' => $id])->update(['is_del' => 1]);
        if ($res < 1) return $this->error('删除失败，请重试.');
        $this->_Log($id, '删除权限:' . $id);
        return $this->success('操作成功.');
    }
}
