<?php

namespace app\member\controller\sys_admin;

use app\AdminController;
use app\member\model\UsersModel;
use app\member\model\UserAddressModel;
use app\member\model\UsersBindSuperiorModel;
use app\member\model\LogSysModel;
use app\member\model\LogUpLevelModel;

use app\member\model\RoleModel;
use app\distribution\model\DividendModel;
use app\shop\model\OrderModel;
use app\weixin\model\WeiXinUsersModel;

use think\Db;
use think\facade\Cache;

/**
 * 会员管理
 * Class Index
 * @package app\store\controller
 */
class Users extends AdminController
{
    public $is_ban = 0;
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new UsersModel();
    }
    /*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index()
    {
        $this->assign('role_id',input('role_id', 0, 'intval'));
        $this->assign("start_date", date('Y/m/01', strtotime("-1 months")));
        $this->assign("end_date", date('Y/m/d'));
        $this->getList(true);

        //首页跳转时间
        $start_date = input('start_time', '0', 'trim');
        $end_date = input('end_time', '0', 'trim');
        if( $start_date || $end_date){

            $this->assign("start_date",str_replace('_', '/', $start_date));
            $this->assign("end_date",str_replace('_', '/', $end_date));
        }
        $this->assign("roleOpt", arrToSel($this->roleList, $this->search['roleId']));
        return $this->fetch('sys_admin/users/index');
    }

    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false, $searchType = '')
    {
        $this->search['roleId'] = input('role_id', -1);
        $this->search['keyword'] = input("keyword",'','trim');
        $this->search['time_type'] = input("time_type");

        $this->assign("is_ban", $this->is_ban);
        $this->assign("search", $this->search);
        $RoleModel = new RoleModel();
        $this->roleList = $RoleModel->getRows();
        $this->assign("roleList", $this->roleList);

        $where[] = ' is_ban = ' . $this->is_ban;
        $reportrange = input('reportrange');
        $search['start_time'] = input('start_time', '', 'trim');
        $search['end_time'] = input('end_time', '', 'trim');
        if (empty($search['start_time']) == false) {
            $dtime[0] = str_replace('_', '-', $search['start_time']);
            $dtime[1] = str_replace('_', '-', $search['end_time']);
        } elseif (empty($reportrange) == false) {
            $dtime = explode('-', $reportrange);
        }
        if (empty($dtime) == false){
            $dtime[0] = strtotime($dtime[0]);
            $dtime[1] = strtotime($dtime[1]) + 86399;
        }
        switch ($this->search['time_type']) {
            case 'reg_time':
                $where[] = ' u.reg_time between ' . $dtime[0]  . ' AND ' . $dtime[1];
                break;
            case 'login_time':
                $where[] = ' u.login_time between ' . $dtime[0]  . ' AND ' .$dtime[1];
                break;
            case 'buy_time':
                $where[] = ' u.last_buy_time between ' . $dtime[0]  . ' AND ' .$dtime[1];
                break;
            default:
                break;
        }

        if ($this->search['roleId'] == 'all_channel') {
            $role_ids = $RoleModel->where('role_type',1)->column('role_id');
            $where[] = ' u.role_id in ('.join(",",$role_ids).')';
        }elseif ($this->search['roleId'] >= 0) {
            $where[] = ' u.role_id = ' . $this->search['roleId'] * 1;
        }


        if (empty($this->search['keyword']) == false) {
            if (is_numeric($this->search['keyword'])) {
                $where[] = " ( u.user_id = '" . ($this->search['keyword']) . "' or u.mobile like '" . $this->search['keyword'] . "%' )";
            } else {
                $where[] = " ( u.user_name like '" . $this->search['keyword'] . "%' or u.nick_name like '" . $this->search['keyword'] . "%' )";
            }
        }
        $export = input('export', 0, 'intval');
        if ($export > 0) {
            return $this->exportList($where);
        }
        $sort_by = input("sort_by", 'DESC', 'trim');
        $order_by = 'u.user_id';
        $viewObj = $this->Model->alias('u')->join("users_account uc", 'u.user_id=uc.user_id', 'left')->where(join(' AND ', $where))->field('u.*,uc.balance_money,uc.use_integral,uc.total_integral')->order($order_by . ' ' . $sort_by);

        $data = $this->getPageList($this->Model, $viewObj);
        $data['order_by'] = $order_by;
        $data['sort_by'] = $sort_by;
        $this->assign("data", $data);
        $this->assign("search",$this->search);

        if ($runData == false) {
            $data['content'] = $this->fetch('sys_admin/users/list')->getContent();
            return $this->success('', '', $data);
        }
        return true;
    }

    /*------------------------------------------------------ */
    //-- 导出会员
    /*------------------------------------------------------ */
    public function exportList($where)
    {

        $count = $this->Model->alias('u')->where(join(' AND ', $where))->count('u.user_id');

        if ($count < 1) return $this->error('没有找到可导出的日志资料！');
        $filename = '会员列表资料_' . date("YmdHis") . '.xls';
        $export_arr['UID'] = 'user_id';
        $export_arr['注册手机'] = 'mobile';
        $export_arr['真实姓名'] = 'real_name';
        $export_arr['昵称'] = 'nick_name';
        $export_arr['身份'] = 'userRole';
        $export_arr['注册时间'] = 'reg_time';
        $export_arr['最近登陆'] = 'login_time';
        $export_arr['推荐人ID'] = 'pid';
        $export_arr['推荐人手机'] = 'p_mobile';
        $export_arr['推荐人姓名'] = 'p_real_name';
        $export_arr['推荐人呢称'] = 'p_nick_name';
        $page = 0;
        $page_size = 1000;
        $page_count = 500;

        $title = "<tr><td>".join("</td><td>", array_keys($export_arr)) . "</td></tr>";

        $RoleModel = new RoleModel();
        $roleList = $RoleModel->getRows();
        $data = '';
        do {
            $rows = $this->Model->alias('u')->where(join(' AND ', $where))->order('u.user_id DESC')->limit($page * $page_size, $page_size)->select();
            if (empty($rows))return;
            foreach ($rows as $row) {
                if ($row['pid'] > 0){
                    $pUserInfo =  $this->Model->where('user_id',$row['pid'])->find();
                }
                $data .= "<tr>";
                foreach ($export_arr as $val) {
                    $data .= "<td>";
                    if (strstr($val, '_time')) {
                        $data .= dateTpl($row[$val]);
                    }  elseif($val == 'userRole') {
                        $data .= ($row['role_id'] == 0 ? '无身份' : $roleList[$row['role_id']]['role_name']);
                    } else {
                        if ($val == 'p_mobile') {
                            $data .= ($row['pid'] > 0 ? $pUserInfo['mobile'] : '');
                        }elseif ($val == 'p_real_name') {
                            $data .= ($row['pid'] > 0 ? $pUserInfo['real_name'] : '');
                        }elseif ($val == 'p_nick_name') {
                            $data .= ($row['pid'] > 0 ? $pUserInfo['nick_name'] : '');
                        }else{
                            $data .= strip_tags($row[$val]);
                        }
                    }
                    $data .= "</td>";
                }
                $data .= "</tr>";
            }

            $page++;
        } while ($page <= $page_count);

        $filename = iconv('utf-8', 'GBK//IGNORE', $filename);
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=$filename");
        $str = "<html xmlns:o=\"urn:schemas-microsoft-com:office:office\"\r\nxmlns:x=\"urn:schemas-microsoft-com:office:excel\"\r\nxmlns=\"http://www.w3.org/TR/REC-html40\">\r\n<head>\r\n<meta http-equiv=Content-Type content=\"text/html; charset=utf-8\">\r\n</head>\r\n<body>";
        $str .="<style>tr,td,th{text-align: center;height: 22px;line-height: 22px;}</style>";
        $str .="<table border=1>";
        echo $str.$title . $data. "</table></body></html>";
        exit;
    }
    /*------------------------------------------------------ */
    //-- 重置密码
    /*------------------------------------------------------ */
    public function restPassword()
    {
        $user_id = input("user_id/d");
        if ($user_id < 0) {
            return $this->error('获取用户ID传值失败！');
        }
        $data['password'] = f_hash('Abc123456');
        $oldpassword = $this->Model->where('user_id', $user_id)->value('password');
        if ($data['password'] == $oldpassword) {
            return $this->error('当前用户密码为系统默认：Abc123456，无须修改.');
        }
        $res = $this->Model->where('user_id', $user_id)->update($data);
        if ($res < 1) {
            return $this->error('未知错误，处理失败.');
        }
        $this->_log($user_id, '重置用户密码.', 'member');
        return $this->success('操作成功.');
    }
    /*------------------------------------------------------ */
    //-- 重置支付密码
    /*------------------------------------------------------ */
    public function restPayPassword()
    {
        $user_id = input("user_id/d");
        if ($user_id < 0) {
            return $this->error('获取用户ID传值失败！');
        }
        $pay_password = rand(100000,999999);
        $data['pay_password'] = f_hash($pay_password);
        $res = $this->Model->where('user_id', $user_id)->update($data);
        if ($res < 1) {
            return $this->error('未知错误，处理失败.');
        }
        $this->_log($user_id, '重置用户支付密码.', 'member');
        return $this->success('操作成功,新支付密码：'.$pay_password,'',['alert'=>1]);
    }
    /*------------------------------------------------------ */
    //-- 会员管理
    /*------------------------------------------------------ */
    public function info()
    {
        $user_id = input('user_id/d');
        if ($user_id < 1) return $this->error('获取用户ID传值失败！');
        $row = $this->Model->info($user_id);
        if (empty($row)) return $this->error('用户不存在！');
        $row['wx'] = (new WeiXinUsersModel)->where('user_id', $user_id)->find();

        //代理处理
        if ($row['role']['role_type'] == 1){
            if (class_exists('app\channel\model\ProxyUsersModel')) {
                $row['channelInfo'] = (new \app\channel\model\ProxyUsersModel)->returnInfo($user_id);
            }
        }

        $this->assign("userShareStats", $this->Model->userShareStats($user_id));
        $row['user_address'] = (new UserAddressModel)->where('user_id', $user_id)->select();
        $Twhere = [];
        $getIds=$this->Model->teamUid($user_id);
        $Twhere[]=['user_id','in',$getIds];
        $Twhere[]=['order_status','=',1];
        $row['teamConsume'] = (new OrderModel())->where($Twhere)->sum('order_amount');
        $this->assign('row', $row);
        $this->assign('d_level', config('config.dividend_level'));
        $RoleModel = new RoleModel();
        $this->assign("roleList", $RoleModel->getRows());
        $this->assign("teamCount",$this->Model->teamCount($user_id));
        $where[] = ['dividend_uid', '=', $user_id];
        $where[] = ['status', 'in', [1,2, 3]];
        $DividendModel = new DividendModel();
        $dividend_amount = $DividendModel->where($where)->sum('dividend_amount');
        $this->assign("wait_money", $dividend_amount );
        $has_order = 0;
        //判断订单模块是否存在
        if (class_exists('app\shop\model\OrderModel')) {
            $has_order = 1;
            $this->assign("start_date", date('Y/m/01', strtotime("-1 months")));
            $this->assign("end_date", date('Y/m/d'));
        }
        $this->assign('has_order',$has_order);

        $this->assign('checkIDCardStatus',$this->Model::$checkIDCardStatus);
        return $this->fetch('sys_admin/users/info');
    }
    /*------------------------------------------------------ */
    //-- 修改会员身份
    /*------------------------------------------------------ */
    public function editRole()
    {
        $user_id = input('user_id', 0, 'intval');
        $row = $this->Model->info($user_id);
        $RoleModel = new RoleModel();
        $roleList = $RoleModel->getRows();
        if ($this->request->isPost()) {
            $data['role_id'] = input('role_id', 0, 'intval');
            $this->checkUpData($row, $data);
            $data['last_up_role_time'] = time();
            Db::startTrans();//开启事务
            $data['is_channel'] = 0;
            if ($roleList[$data['role_id']]['role_type'] == 1){
                $data['is_channel'] = 1;//设为代理
            }
            $res = $this->Model->upInfo($user_id, $data);
            if ($res < 1){
                Db::rollback();
                return $this->error('操作失败,请重试.');
            }
            $res = $this->Model->resetRolePid($user_id,$this);//重置身份上级
            if ($res !== true){
                Db::rollback();
                return $this->error($res);
            }
            $_log = '后台手工操作由【' . ($row['role_id'] == 0 ? '无身份' : $roleList[$row['role_id']]['role_name']) . '】调整为【' .($data['role_id'] < 1 ? '无身份' : $roleList[$data['role_id']]['role_name']) . '】';
            $this->Model->_upLog($user_id, $_log, $data['role_id'],$row['role_id']);
            Db::commit();//提交事务
            return $this->success('修改身份成功！', 'reload');
        }
        $this->assign("roleList", $roleList);
        $this->assign("row", $row);

        return $this->fetch('sys_admin/users/edit_role');
    }
    /*------------------------------------------------------ */
    //-- 封禁会员
    /*------------------------------------------------------ */
    public function evelBan()
    {
        $user_id = input('user_id', 0, 'intval');
        $row = $this->Model->info($user_id);
        if ($row['is_ban'] == 1) return $this->error('会员已被封禁，无须重复操作.');
        $data['is_ban'] = 1;
        Db::startTrans();//开启事务
        $res = $this->Model->upInfo($user_id, $data);
        if ($res < 1){
            Db::rollback();
            return $this->error();
        }
        $res = $this->Model->resetRolePid($user_id,$this);//重置身份上级
        if ($res !== true){
            Db::rollback();
            return $this->error($res);
        }
        $this->_log($user_id, '后台封禁会员.', 'member');
        Db::commit();//提交事务
        return $this->success('禁封成功.', 'reload');
    }
    /*------------------------------------------------------ */
    //-- 解禁会员
    /*------------------------------------------------------ */
    public function reBan()
    {
        $user_id = input('user_id', 0, 'intval');
        $row = $this->Model->info($user_id);
        if ($row['is_ban'] == 0) return $this->error('会员已被解禁，无须重复操作.');
        $data['is_ban'] = 0;
        Db::startTrans();//开启事务
        $res = $this->Model->upInfo($user_id, $data);
        if ($res < 1){
            Db::rollback();
            return $this->error('操作失败,请重试.');
        }
        $res = $this->Model->resetRolePid($user_id,$this);//重置身份上级
        if ($res !== true){
            Db::rollback();
            return $this->error($res);
        }
        $this->_log($user_id, '后台解禁会员.', 'member');
        Db::commit();//提交事务
        return $this->success('解禁成功.', 'reload');
    }
    /*------------------------------------------------------ */
    //-- 根据关键字查询
    /*------------------------------------------------------ */
    public function pubSearch()
    {
        $keyword = input('keyword', '', 'trim');
        $user_level = input('user_level', '', 'trim');

        $where="user_id > 0";
        if (!empty($keyword)) {
            $where .= " AND  ( mobile LIKE '%" . $keyword . "%' OR user_id = '" . $keyword . "' OR nick_name LIKE '%" . $keyword . "%' OR mobile LIKE '%" . $keyword . "%')";
        }
        if($user_level>0){
            $level = $this->levelList[$this->search['levelId']];
            $where.=' AND total_integral between ' . $level['min'] . ' AND ' . $level['max'];
        }
        $_list = $this->Model->where($where)->field("user_id,mobile,nick_name,user_name")->limit(20)->select();
        foreach ($_list as $key => $row) {
            $_list[$key] = $row;
        }
        $result['list'] = $_list;
        $result['code'] = 1;
        return $this->ajaxReturn($result);
    }
    /*------------------------------------------------------ */
    //-- 下级名单
    /*------------------------------------------------------ */
    public function getChainList()
    {
        $user_id = input('user_id', 0, 'intval');
        if ($user_id < 1) {
            $result['list'] = [];
            return $this->ajaxReturn($result);
        }
        $DividendRole = (new RoleModel)->getRows();
        $rows = $this->Model->field('user_id,nick_name,role_id')->where('pid', $user_id)->select();
        foreach ($rows as $key => $row) {
            $row['role_name'] = $DividendRole[$row['role_id']]['role_name'];
            $row['teamCount'] = $this->Model->teamCount($row['user_id']) + 1;
            $rows[$key] = $row;
        }
        $result['list'] = $rows;
        return $this->ajaxReturn($result);
    }
    /*------------------------------------------------------ */
    //-- 上级名单
    /*------------------------------------------------------ */
    public function getSuperiorList()
    {
        $user_id = input('user_id', 0, 'intval');
        $result['code'] = 1;
        $result['list'] = $this->Model->getSuperiorList($user_id);
        return $this->ajaxReturn($result);
    }

    /*------------------------------------------------------ */
    //-- 执行统计
    /*------------------------------------------------------ */
    public function evalStat()
    {
        $reportrange = input('reportrange', '2019/01/01 - 2019/03/21', 'trim');
        $user_id = input('user_id', '0', 'intval');

        $dtime = explode('-', $reportrange);
        $UsersBindSuperiorModel = new UsersBindSuperiorModel();
        $where[] = ['','exp',Db::raw("FIND_IN_SET('".$user_id."',superior)")];
        $user_ids = $UsersBindSuperiorModel->where($where)->column('user_id');

        $oWhere[] = ['o.user_id','in',$user_ids];
        $oWhere[] = ['o.order_status','=',1];
        $oWhere[] = ['o.add_time','between',[strtotime($dtime[0]),strtotime($dtime[1]) + 86399]];
        $viewObj = (new \app\shop\model\OrderModel)->alias('o')->field('o.user_id,o.order_id,o.user_id,o.order_amount,o.dividend_amount,og.goods_name,og.goods_id,og.goods_name,og.goods_number,og.shop_price,og.sale_price');
        $viewObj->join("shop_order_goods og", 'og.order_id=o.order_id', 'left');
        $rows = $viewObj->where($oWhere)->select()->toArray();
        $result['buyGoods'] = [];
        $nowUser = [];

        $order_ids = $user_order_ids = [];
        $buy_ser_ids = [];
        $team_amount = [];
        $user_amount = [];
        foreach ($rows as $row) {
            if ($row['goods_id'] < 1)  continue;
            $order_ids[$row['order_id']] = 1;
            $buy_ser_ids[$row['user_id']] = 1;
            $result['buyGoods'][$row['goods_id']]['goods_name'] = $row['goods_name'];
            $result['buyGoods'][$row['goods_id']]['num'] += $row['goods_number'];
            $result['buyGoods'][$row['goods_id']]['price'] += ($row['sale_price']*$row['goods_number']);
            $team_amount[$row['order_id']]['dividend_amount'] = $row['dividend_amount'];
            $team_amount[$row['order_id']]['order_amount'] = $row['order_amount'];
            if ($row['user_id'] == $user_id){
                $nowUser['buyGoods'][$row['goods_id']]['goods_name'] = $row['goods_name'];
                $nowUser['buyGoods'][$row['goods_id']]['num'] += $row['goods_number'];
                $nowUser['buyGoods'][$row['goods_id']]['price'] += ($row['sale_price']*$row['goods_number']);
                $user_order_ids[$row['order_id']] = 1;
                $user_amount[$row['order_id']]['dividend_amount'] = $row['dividend_amount'];
                $user_amount[$row['order_id']]['order_amount'] = $row['order_amount'];
            }
        }

        $result['dividend_amount'] = 0;
        $result['order_amount'] = 0;
        foreach ($team_amount as $tarr){
            $result['dividend_amount'] += $tarr['dividend_amount'];
            $result['order_amount'] += $tarr['order_amount'];
        }
        $nowUser['dividend_amount'] = 0;
        $nowUser['order_amount'] = 0;
        foreach ($user_amount as $uarr){
            $nowUser['dividend_amount'] += $uarr['dividend_amount'];
            $nowUser['order_amount'] += $uarr['order_amount'];
        }

        $result['code'] = 1;
        $result['reportrange'] = $reportrange;
        $result['order_num'] = count($order_ids);
        $result['buy_user_num'] = count($buy_ser_ids);
        $nowUser['order_num'] = count($user_order_ids);
        $result['nowUser'] = $nowUser;
        return $this->ajaxReturn($result);
    }
     /*------------------------------------------------------ */
    //-- 修改所属上级
    /*------------------------------------------------------ */
    public function editSuperior()
    {
        $user_id = input('user_id', 0, 'intval');
        $userInfo = $this->Model->info($user_id);
        if ($this->request->isPost()) {
            $mkey = 'evaleditSuperior';
            $cache = Cache::get($mkey);
            if (empty($cache) == false){
                return $this->error('当前正在有人操作调整，请稍后再操作.');
            }
            Cache::set($mkey,true,60);
            $setTopUser = input('setTopUser', 0, 'intval');
            if ($setTopUser == 1){
                $select_user_id = 0;
            }else{
                $select_user_id = input('select_user', 0, 'intval');
                if ($select_user_id < 1){
                    return $this->error('请选择需要修改的上级.');
                }
                if ($select_user_id == $userInfo['pid']){
                    return $this->error('当前选择与当前会员上级一致，请核实.');
                }
                if ($select_user_id == $userInfo['user_id']){
                    return $this->error('不能选择自己作为自己的上级.');
                }
                $where[] = ['','exp',Db::raw("FIND_IN_SET('".$user_id."',superior)")];
                $where[] = ['user_id','=',$select_user_id];
                $count = (new UsersBindSuperiorModel)->where($where)->count();
                if ($count > 0){
                    return $this->error('不能选择自己的下级作为上级.');
                }
            }
            Db::startTrans();//启动事务

            $res = $this->Model->upInfo($user_id,['pid'=>$select_user_id]);

            if ($res < 1){
                Db::rollback();
                return $this->error('修改会员所属上级失败.');
            }

            //会员上级汇总处理end
            $res = $this->Model->regUserBind($user_id,$select_user_id,true);//重新绑定当前用户的关系链
            if ($res == false){
                Db::rollback();
                return $this->error('绑定当前会员关系链失败.');
            }

            $res = $this->Model->resetRolePid($user_id,$this);//重置身份上级
            if ($res !== true){
                Db::rollback();
                return $this->error($res);
            }
            Db::commit();//事务，提交
            Cache::rm($mkey);
            $this->_log($user_id, '调整会员所属上级，原所属上级ID：'.$userInfo['pid'], 'member');
            return $this->success('修改所属上级成功！', 'reload');
        }
        if ($userInfo['pid'] > 0){
            $userInfo['puser'] = $this->Model->info($userInfo['pid']);
        }
        $this->assign("row", $userInfo);
        return $this->fetch('sys_admin/users/edit_superior');
    }


    /*------------------------------------------------------ */
    //-- 搜索会员
    /*------------------------------------------------------ */
    public function searchBox(){
        $this->assign('rode_id',input('rode_id', 0, 'intval'));
        $this->assign("start_date", date('Y/m/01', strtotime("-1 months")));
        $this->assign("end_date", date('Y/m/d'));
        $this->getList(true);

        //首页跳转时间
        $start_date = input('start_time', '0', 'trim');
        $end_date = input('end_time', '0', 'trim');
        if( $start_date || $end_date){

            $this->assign("start_date",str_replace('_', '/', $start_date));
            $this->assign("end_date",str_replace('_', '/', $end_date));
        }

        return $this->fetch('sys_admin/users/search_box');
    }

    /*------------------------------------------------------ */
    //-- 搜索会员
    /*------------------------------------------------------ */
    public function getSearchList(){
        $this->getList(true);
        $this->data['content'] = $this->fetch('sys_admin/users/search_list')->getContent();
        return $this->success('', '', $this->data);
    }

    /*------------------------------------------------------ */
    //-- 新增会员
    /*------------------------------------------------------ */
    public function addUser(){
        if ($this->request->isPost()) {
            $inArr = input('post.');
            $inArr['nick_name'] = input('nick_name','','trim');
            $inArr['mobile'] = input('mobile','','trim');
            $inArr['role_id'] = input('role_id',0,'intval');
            $inArr['password'] = input('password','','trim');
            $password_again = input('password_again','','trim');
            if(!$inArr['password'] || !$password_again){
                return $this->error('请输入密码');
            }
            if($inArr['password'] != $password_again){
                return $this->error('两次密码不一致，请重新输入');
            }
            $res = $this->Model->register($inArr,0,true,$this);
            if($res === true){
                return $this->success('添加成功.');
            }
            return $this->error($res);
        }
        $roleList = (new RoleModel)->getRows();
        $this->assign("roleList", $roleList);
        return $this->fetch('sys_admin/users/add_user');
    }
    /*------------------------------------------------------ */
    //-- 修改手机号码
    /*------------------------------------------------------ */
    public function upMobile(){
        $user_id = input('user_id');
        $user = $this->Model->find($user_id);
        if (empty($user)){
            return $this->error('用户不存在.');
        }
        if ($this->request->isPost()) {
            $mobile = input('mobile');
            if (checkMobile($mobile) == false) {
                // return '手机号码不正确.';
                return $this->error('手机号码不正确');
            }
            $count = $this->Model->where('mobile',$mobile)->count('user_id');
            if ($count > 0) {
                return $this->error('手机号已存在');
            }
            $upDate['mobile'] = $mobile;
            $res = $this->Model->where('user_id',$user_id)->update($upDate);
            if ($res < 1) {
                return $this->error('未知错误，处理失败.');
            }
            $this->Model->cleanMemcache($user_id);
            $text = '原手机号:'.$user['mobile'].'改为'.$mobile;
            $this->_log($user_id,$text);
            return $this->success('修改成功.');
        }
        $this->assign('user',$user);
        return $this->fetch('sys_admin/users/up_mobile');
    }
    /*------------------------------------------------------ */
    //-- 获取用户操作日志
    /*------------------------------------------------------ */
    public function getOptLogList()
    {
        $user_id = input('user_id', 0, 'intval');
        if ($user_id < 1){
            return $this->error('ID不能为空');
        }
        $logs = (new LogSysModel)->where('edit_id',$user_id)->order('log_id DESC')->select()->toArray();
        foreach ($logs as $key=>$log){
            $log['admin_info'] = $log['user_id'].'-'.adminInfo($log['user_id']);
            $log['log_time'] = dateTpl($log['log_time']);
            $logs[$key] = $log;
        }
        $data['list'] = $logs;
        return $this->success('ok','',$data);
    }
    /*------------------------------------------------------ */
    //-- 获取用户升级日志
    /*------------------------------------------------------ */
    public function getUpLevelLogList()
    {
        $user_id = input('user_id', 0, 'intval');
        if ($user_id < 1){
            return $this->error('ID不能为空');
        }
        $logs = (new LogUpLevelModel)->where('edit_id',$user_id)->order('log_id DESC')->select()->toArray();
        foreach ($logs as $key=>$log){
            $log['admin_info'] = $log['admin_id'].'-'.adminInfo($log['admin_id']);
            $log['log_time'] = dateTpl($log['log_time']);
            $logs[$key] = $log;
        }
        $data['list'] = $logs;
        return $this->success('ok','',$data);
    }

}
