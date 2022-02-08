<?php
namespace app\member\controller\sys_admin;

use app\AdminController;
use app\member\model\UsersModel;
use app\member\model\RoleModel;
/**
 * 实名验证
 * Class Index
 * @package app\store\controller
 */
class CheckIdCard extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new UsersModel();
    }
    /*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index()
    {
        $this->assign("checkIDCardStatus", $this->Model::$checkIDCardStatus);
        $this->getList(true);
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false)
    {
        $this->search['roleId'] = input('role_id', -1);
        $this->search['keyword'] = input("keyword");
        $this->search['check_id_card_status'] = input("check_id_card_status", '2', 'intval');
        $RoleModel = new RoleModel();
        $this->roleList = $RoleModel->getRows();
        $this->assign("roleList", $this->roleList);
        $sort_by = input("sort_by", 'DESC', 'trim');
        $order_by = 'user_id';
        $where[] = ' is_ban = 0';

        $where[] = ' check_id_card = '.$this->search['check_id_card_status'];
        if (empty($this->search['keyword']) == false) {
            if (is_numeric($this->search['keyword'])) {
                $where[] = " ( user_id = '" . ($this->search['keyword']) . "' or mobile like '" . $this->search['keyword'] . "%' )";
            } else {
                $where[] = " ( user_name like '" . $this->search['keyword'] . "%' or nick_name like '" . $this->search['keyword'] . "%' )";
            }
        }
        $where = join(' AND ', $where);
        $data = $this->getPageList($this->Model, $where);
        $data['order_by'] = $order_by;
        $data['sort_by'] = $sort_by;
        $this->assign("data", $data);
        $this->assign("search",$this->search);
        if ($runData == false) {
            $data['content'] = $this->fetch('list')->getContent();
            return $this->success('', '', $data);
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 实名审核
    /*------------------------------------------------------ */
    public function check()
    {
        $user_id = input('user_id', 0, 'intval');
        $userInfo = $this->Model->info($user_id);
        $checkIDCardStatus = $this->Model::$checkIDCardStatus;
        if ($this->request->isPost()) {
            $check_id_card = input('check_id_card',0,'intval');
            $this->Model->upInfo($user_id,['check_id_card'=>$check_id_card]);
            $this->_log($user_id, '实名认证：'.$checkIDCardStatus[$check_id_card], 'member');
            $this->Model->cleanMemcache($user_id);
            return $this->success('操作成功.');
        }
        $this->assign('checkIDCardStatus',$checkIDCardStatus);
        $this->assign('userInfo',$userInfo);
        return $this->fetch();
    }
}
