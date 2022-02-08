<?php

namespace app\mainadmin\controller;

use app\AdminController;
use app\mainadmin\model\MessageModel;
use app\mainadmin\model\MessageUserModel;
use app\member\model\UsersModel;

/**
 * 站内信管理
 * Class Index
 * @package app\store\controller
 */
class Message extends AdminController
{
    public $_field = '';
    public $_pagesize = '';

    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new MessageModel();
    }
    /*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
    public function index()
    {
        $cid = input('id', 0, 'intval');
        $this->getList(true);
        $this->assign("cgOpt", arrToSel($this->cg_list, $cid));
        return $this->fetch('mainadmin@message/index');
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false)
    {
        $runJson = input('runJson', 0, 'intval');
        $where = [];
        $search['cid'] = input('cid', 0, 'intval');
        $search['keyword'] = input('keyword', '', 'trim');
        $search['type'] = input('type', '');
        if ($search['cid'] > 0) {
            $where[] = ['cid', 'in', $this->cg_list[$search['cid']]['children']];
        }
        $search['keyword'] = input('keyword', '', 'trim');
        if (empty($search['keyword']) == false) $where[] = ['title', 'like', "%" . $search['keyword'] . "%"];
        if ($search['type'] != '') $where[] = ['type', '=', $search['type']];

        $data = $this->getPageList($this->Model, $where, $this->_field, $this->_pagesize);

        $this->assign("messageType", $this->Model->getMessageType());//发送类型
        $this->assign("data", $data);
        $this->assign("search", $search);
        if ($runJson == 1) {
            return $this->success('', '', $data);
        } elseif ($runData == false) {
            $data['content'] = $this->fetch('list')->getContent();
            unset($data['list']);
            return $this->success('', '', $data);
        }
        return true;
    }

    /*------------------------------------------------------ */
    //-- 信息页调用
    //-- $data array 自动读取对应的数据
    /*------------------------------------------------------ */
    public function asInfo($data)
    {
        $messageType = $this->Model->getMessageType();
        $this->assign("messageType", $messageType);//发送类型
        $roleList = [];
        if (empty($messageType['distribution']) == false){
            $RoleModel = new \app\member\model\RoleModel();
            $roleList = $RoleModel->getRows();
        }
        $this->assign("roleList", $roleList);//分销身份
        $proxyList = [];


        if ($data['message_id'] > 0) {
            if ($data['type'] == 'byuser') {
                $where[] = ['user_id', 'IN', explode(',',$data['type_ext_id'])];
                $userList = (new UsersModel)->field('user_id,mobile,nick_name,real_name')->where($where)->select()->toArray();
                $this->assign('userList', $userList);
            }
        }
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 添加前调用
    /*------------------------------------------------------ */
    public function beforeAdd($row)
    {
        if (empty($row['send_time'])) return $this->error('请选择发送日期.');
        if (empty($row['show_end_time'])) return $this->error('请选择显示过期日期.');
        $row['send_time'] = strtotime($row['send_time']);
        $row['show_end_time'] = strtotime($row['show_end_time']);
        if ($row['send_time'] >= $row['show_end_time']) return $this->error('显示过期日期必须大于发送日期！');
        if ($row['send_time']+3*86400*30 < $row['show_end_time']) return $this->error('显示过期日期不能超过发送日期三个月！');

        if (isset($row['type']) == false) return $this->error('请选择通知发送类型.');
        if ($row['message_id'] < 1){
            if ($row['type'] == 'channel'){
                $row['type_ext_id'] = $row['proxy_id'] * 1;
            }elseif ($row['type'] == 'distribution'){
                $row['type_ext_id'] = $row['role_id'] * 1;
            }elseif ($row['type'] == 'byuser'){
                if (empty($row['user_id'])){
                    return $this->error('请选择发送会员.');
                }
                $row['type_ext_id'] = join(',',$row['user_id']);
            }
            $row['add_time'] = time();
        }
        $row['update_time'] = time();
        return $row;
    }
    //-- 添加后调用
    /*------------------------------------------------------ */
    public function afterAdd($row)
    {
        $user_ids = [];
        if ($row['type'] == 'channel') {//指定代理层级直接发放
            if ($row['proxy_id'] == 0) {
                $where[] = ['proxy_id', '>', 0];
            } else {
                $where[] = ['proxy_id', '=', $row['proxy_id']];
            }
            $ProxyUsersModel = new \app\channel\model\ProxyUsersModel();
            $user_ids = $ProxyUsersModel->where($where)->column('user_id');
        }elseif ($row['type'] == 'distribution') {//指定分销层级直接发放
            $where[] = ['role_id','=',$row['role_id']];
            $user_ids = (new UsersModel)->where($where)->column('user_id');
        }elseif ($row['type'] == 'byuser') {//指定会员直接发放
            $user_ids = $row['user_id'];
        }
        foreach ($user_ids as $user_id) {
            (new MessageUserModel)->sendMessage($user_id, 0, $row['message_id'],$row['title'],$row['content'],$row['send_time'], $row['show_end_time']);
        }
        return $this->success('添加成功.', url('index'));
    }
    /*------------------------------------------------------ */
    //-- 修改前调用
    /*------------------------------------------------------ */
    public function beforeEdit($row)
    {
        return $this->beforeAdd($row);
    }
    /*------------------------------------------------------ */
    //-- 修改后调用
    /*------------------------------------------------------ */
    public function afterEdit($row)
    {
        if ($row['type'] != 'all'){
            $where[] = ['type','=',0];
            $where[] = ['ext_id','=',$row['message_id']];
            $upData['title'] = $row['title'];
            $upData['content'] = $row['content'];
            $upData['status'] = $row['status'];
            $upData['send_time'] = $row['send_time'];
            $upData['show_end_time'] = $row['show_end_time'];
            (new MessageUserModel)->where($where)->update($upData);
        }
        return $this->success('修改成功.');
    }
}
