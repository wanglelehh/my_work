<?php
namespace app\channel\controller\sys_admin;
use think\Db;

use app\AdminController;
use app\channel\model\OrderModel;
use app\channel\model\OrderLogModel;
use app\channel\model\StockModel;
use app\channel\model\RewardLogModel;

use app\shop\model\ShippingModel;
use app\shop\model\PrintTemplateModel;

use app\member\model\UsersModel;
use app\member\model\RoleModel;


/**
 * 代理订单管理
 * Class Index
 * @package app\store\controller
 */
class Order extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new OrderModel();
    }
	/*------------------------------------------------------ */
	//-- 云仓订单列表
	/*------------------------------------------------------ */
    public function cloud(){
        $this->assign("start_date", date('Y/m/01', strtotime("-1 months")));
        $this->assign("end_date", date('Y/m/d'));
        $this->purchaseType = 1;
        $this->getList(true);
		return $this->fetch('index');
	}

    /*------------------------------------------------------ */
    //-- 提货订单列表
    /*------------------------------------------------------ */
    public function pickup(){
        $this->assign("start_date", date('Y/m/01', strtotime("-1 months")));
        $this->assign("end_date", date('Y/m/d'));
        $this->purchaseType = 3;
        $this->getList(true);
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false)
    {
        $RoleModel = new RoleModel();
        $this->assign("roleList", $RoleModel->getRows(1));
        $this->assign("purchaseTypeList", config('config.purchaseTypeList'));
        $search['state'] = input('state', -1, 'intval');
        $search['purchaseType'] = input('purchaseType', $this->purchaseType, 'intval');
        $search['role_id'] = input('role_id', 0, 'intval');
        $search['user_id'] = input('user_id', 0, 'intval');
        $where[] = ['purchase_type','=',$search['purchaseType']];
        if ($search['user_id'] > 0){
            $where[] = ['user_id','=',$search['user_id']];
        }
        if ($search['role_id'] > 0){
            $where[] = ['user_role_id','=',$search['role_id']];
        }
        $time_type = input('time_type', '', 'trim');
        $def_start_time = strtotime(date('Y/m/01', strtotime("-1 months")));
        if (empty($time_type) == false) {
            $search['start_time'] = input('start_time', '', 'trim');
            $search['end_time'] = input('end_time', '', 'trim');
            $search['start_time'] = str_replace('_', '-', $search['start_time']);
            $search['end_time'] = str_replace('_', '-', $search['end_time']);
            $start_time = $search['start_time'] ? strtotime($search['start_time']) : $def_start_time;
            $end_time = $search['end_time'] ? strtotime($search['end_time']) : time();
            if ($start_time == $end_time) $end_time += 86399;
            $where[] = [$time_type, 'between', array($start_time, $end_time)];
        } else {
            $search['state'] = input('state', 0, 'intval');
            $reportrange = input('reportrange', '', 'trim');
            if (empty($reportrange) == false) {
                $reportrange = str_replace('_', '/', $reportrange);
                $dtime = explode('-', $reportrange);
                $where[] = ['add_time', 'between', [strtotime($dtime[0]), strtotime($dtime[1]) + 86399]];
            } else {
                $where[] = ['add_time', 'between', [$def_start_time, time()]];
            }
        }
        $search['keyword'] = input('keyword', '', 'trim');
        if (!empty($search['keyword'])) {
            $search['searchBy'] = input('searchBy', '', 'trim');
            $UsersModel = new UsersModel();
            //综合状态
            switch ($search['searchBy']) {
                case 'user_name':
                    $u_where = [];
                    $u_where[] = ['real_name', 'like', $search['keyword'] . '%'];
                    $user_ids = $UsersModel->where($u_where)->column('user_id');
                    $where[] = ['user_id', 'IN', $user_ids];
                    break;
                case 'user_mobile':
                    $u_where = [];
                    $u_where[] = ['mobile', '=', $search['keyword']];
                    $user_ids = $UsersModel->where($u_where)->column('user_id');
                    $where[] = ['user_id', 'IN', $user_ids];
                    break;
                case 'user_id':
                    $where[] = ['user_id', '=', $search['keyword'] * 1];
                    break;
                case 'goods_sn':
                    $where[] = ['', 'exp', Db::raw("FIND_IN_SET('" . $search['keyword'] . "',buy_goods_sn)")];
                    break;
                case 'order_sn':
                    $where[] = ['order_sn', 'like', $search['keyword'] . '%'];
                    break;
                default:
                    break;
            }
        }
        $config = config('config.');
        //综合状态
        switch ($search['state']) {
            /**待付款**/
            case "1" :
                $where[] = ['order_status', 'in', [$config['OS_CONFIRMED'], $config['OS_UNCONFIRMED']]];
                $where[] = ['pay_status', '=', $config['PS_UNPAYED']];
                $where[] = ['payee_uid', '=', 0];
                break;
            /**待审核**/
            case "2" :
                $where[] = ['pay_status', '=', $config['PS_WAITCHACK']];
                $where[] = ['payee_uid', '=', 0];
                break;
            /**待发货**/
            case "3" :
                $where[] = ['order_status', '=', $config['OS_CONFIRMED']];
                $where[] = ['shipping_status', '=', $config['SS_UNSHIPPED']];
                break;
            /**已发货**/
            case "4" :
                $where[] = ['order_status', '=', $config['OS_CONFIRMED']];
                $where[] = ['shipping_status', '=', $config['SS_SHIPPED']];
                break;
            /**已完成**/
            case "5" :
                $where[] = ['order_status', '=', $config['OS_CONFIRMED']];
                $where[] = ['shipping_status', '=', $config['SS_SIGN']];
                break;
            /**已退货**/
            case "6" :
                $where[] = ['order_status', '=', $config['OS_RETURNED']];
                break;
            /**待退款**/
            case "7" :
                $where[] = ['order_status', 'in', [$config['OS_CANCELED'], $config['OS_RETURNED']]];
                $where[] = ['pay_status', '=', $config['PS_PAYED']];
                break;
            /**已退款**/
            case "8" :
                $where[] = ['pay_status', '=', $config['PS_RUNPAYED']];
                break;
            /**已取消**/
            case "99" :
                $where[] = ['order_status', '=',$config['OS_CANCELED']];
                break;
                break;
            default:
                break;
        }

        $export = input('export', 0, 'intval');
        if ($export > 0) {
            return $this->exportOrder($where);
        }

        $data = $this->getPageList($this->Model, $where,'order_id');
        foreach ($data['list'] as $key => $row) {
            $data['list'][$key] = $this->Model->info($row['order_id']);
        }
        $this->assign("data", $data);
        $this->assign("search", $search);

        if ($runData == false) {
            $data['content'] = $this->fetch('list')->getContent();
            unset($data['list']);
            return $this->success('', '', $data);
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 订单详细页
    /*------------------------------------------------------ */
    public function info()
    {
        $order_id = input('order_id', 0, 'intval');
        if ($order_id < 1) return $this->error('传参错误.');
        $orderInfo = $this->Model->info($order_id);

        $orderLog = (new OrderLogModel)->where('order_id', $order_id)->order('log_id DESC')->select()->toArray();
        $this->assign("orderLog", $orderLog);
        $this->assign("orderLang", lang('order'));
        $operating = $this->Model->operating($orderInfo);//订单操作权限
        $this->assign("operating", $operating);
        $this->assign('orderInfo', $orderInfo);
        $this->assign("roleList", (new RoleModel)->getRows());
        $this->assign("purchaseTypeList", config('config.purchaseTypeList'));
        return $this->fetch('info');
    }
    /*------------------------------------------------------ */
    //-- 云仓订单详细页
    /*------------------------------------------------------ */
    public function cloudInfo()
    {
        return $this->info();
    }
    /*------------------------------------------------------ */
    //-- 现货订单详细页
    /*------------------------------------------------------ */
    public function spotInfo()
    {
        return $this->info();
    }
    /*------------------------------------------------------ */
    //-- 提货订单详细页
    /*------------------------------------------------------ */
    public function pickupInfo()
    {
        return $this->info();
    }

    /*------------------------------------------------------ */
    //-- 修改收货信息
    /*------------------------------------------------------ */
    public function editConsignee()
    {
        $order_id = input('order_id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        if ($this->request->isPost()) {
            $operating = $this->Model->operating($orderInfo);//订单操作权限
            if ($operating['editConsignee'] !== true) return $this->error('订单当前状态不能操作收货信息.');
            $data['consignee'] = input('consignee', '', 'trim');
            $data['mobile'] = input('mobile', '', 'trim');
            $data['province'] = input('province', 0, 'intval');
            $data['city'] = input('city', 0, 'intval');
            $data['district'] = input('district', 0, 'intval');
            $data['address'] = input('address', '', 'trim');
            if (empty($data['consignee']) == true) {
                return $this->error('请填写收货人.');
            }
            if (empty($data['mobile']) == true) {
                return $this->error('请填写联系手机.');
            }
            if (checkMobile($data['mobile']) == false) {
                return $this->error('联系手机格式不正确.');
            }
            if ($data['district'] < 1) {
                return $this->error('请选择区域地址.');
            }
            if (empty($data['address']) == true) {
                return $this->error('请填写详细地址.');
            }
            $regionInfo = (new \app\mainadmin\model\RegionModel)->info($data['district']);
            $data['merger_name'] = $regionInfo['merger_name'];
            $data['order_id'] = $order_id;
            $res = $this->Model->upInfo($data);
            if ($res < 1) return $this->error();
            $this->Model->_log($orderInfo, '修改收货信息，原信息：' . $orderInfo['consignee'] . '- ' . $orderInfo['mobile'] . '- ' . $orderInfo['merger_name'] . '-' . $orderInfo['address']);
            return $this->success('修改收货信息成功！','reload');
        }
        $this->assign('orderInfo', $orderInfo);
        return $this->fetch('edit_consignee');
    }

    /*------------------------------------------------------ */
    //-- 线下支付收款确认
    /*------------------------------------------------------ */
    public function cfmCodPay()
    {
        $order_id = input('order_id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        if ($this->request->isPost()) {
            $operating = $this->Model->operating($orderInfo);//订单操作权限
            if ($operating['cfmCodPay'] !== true) return $this->error('订单当前状态不能操作线下打款审核.');
            $operate = input('operate', 0, 'intval');
            $transaction_id = input('transaction_id', '', 'trim');
            if ($operate < 1){
                return $this->error('请选择需要执行的操作.');
            }
            $res = $this->Model->cfmCodPay($orderInfo,$operate,$transaction_id,0);
            if ($res !== true) return $this->error($res);
            return $this->success('操作成功.','reload');
        }
        $this->assign('orderInfo', $orderInfo);
        return $this->fetch('sys_admin/order/cfm_cod_pay');
    }
    /*------------------------------------------------------ */
    //-- 设置为未付款
    /*------------------------------------------------------ */
    public function setUnPay()
    {
        $order_id = input('order_id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        $operating = $this->Model->operating($orderInfo);//订单操作权限
        if ($operating['setUnPay'] !== true) return $this->error('订单当前状态不能进行此操作.');
        $res = $this->Model->setUnPay($orderInfo);
        if ($res !== true) return $this->error($res);

        return $this->success('撤回付款审核成功.','reload');
    }
    /*------------------------------------------------------ */
    //-- 取消订单
    /*------------------------------------------------------ */
    public function cancel()
    {
        $order_id = input('order_id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        $operating = $this->Model->operating($orderInfo);//订单操作权限
        if ($operating['isCancel'] !== true) return $this->error('订单当前状态不能进行此操作.');
        $res = $this->Model->cancel($orderInfo);
        if ($res !== true){
            return $this->error($res);
        }
        return $this->success('取消订单成功.','reload');
    }
    /*------------------------------------------------------ */
    //-- 设置为退款
    /*------------------------------------------------------ */
    public function returnPay()
    {
        $order_id = input('order_id', 0, 'intval');
        $res = $this->Model->returnPay($order_id);
        if ($res !== true){
            return $this->error($res);
        }
        return $this->success('设为退款成功！','reload');
    }
    /*------------------------------------------------------ */
    //-- 发货管理
    /*------------------------------------------------------ */
    public function shipping()
    {
        $order_id = input('order_id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);

        $ShippingModel = new ShippingModel();
        $shipping = $ShippingModel->getRows();
        if ($this->request->isPost()) {
            $config = config('config.');
            $operating = $this->Model->operating($orderInfo);//订单操作权限
            if ($operating['shipping'] !== true) return $this->error('订单当前状态不能操作发货.');
            $data['order_id'] = $order_id;
            $kd_type = input('post.kd_type', '1', 'intval');
            $kdn_shipping_id = input('post.kdn_shipping_id', '', 'intval');
            if ($kd_type == 3) {
                $ptModel = new PrintTemplateModel();
                $res = $ShippingModel->kdnShipping($shipping[$kdn_shipping_id], $orderInfo);
                if (is_array($res) == false) return $this->error($res);
                $data['shipping_id'] = $res[0]['shipping_id'];
                $data['invoice_no'] = $res[1];
                $data['shipping_name'] = $res[0]['shipping_name'];
                if($res[2]){
                    $Arr['temp_html'] = $res[2];
                    $pt_row = $ptModel->where(['order_id'=>$order_id])->find();
                    if($pt_row){
                        $ptModel->where(['order_id'=>$order_id])->update($Arr);
                    }else{
                        $Arr['order_id'] = $order_id;
                        $Arr['order_sn'] = $orderInfo['order_sn'];
                        $ptModel->insert($Arr);
                    }
                }
            } elseif ($kd_type == 1) {
                $shipping_id = input('post.shipping_id', 0, 'intval');
                $invoice_no = input('post.invoice_no', '', 'trim');
                if ($shipping_id < 1) return $this->error("请选择快递公司");
                if (empty($invoice_no)) return $this->error("请输入快递单号");
                $data['shipping_id'] = $shipping_id;
                $data['shipping_name'] = $shipping[$data['shipping_id']]['shipping_name'];
                $data['invoice_no'] = $invoice_no;
            }

            $data['shipping_status'] = $config['SS_SHIPPED'];
            $data['shipping_time'] = time();
            $res = $this->Model->upInfo($data,'sys');
            if ($res !== true) return $this->error($res);
            $orderInfo['shipping_status'] = $data['shipping_status'];
            $this->Model->_log($orderInfo, '后台操作发货');
            return $this->success('操作发货成功！', 'reload');
        }
        $this->assign('shippingOpt', arrToSel($shipping, $orderInfo['shipping_id']));
        $kdnpingopt = $ShippingModel->getRows('kdn_code');
        $this->assign('kdnpingopt', arrToSel($kdnpingopt, $orderInfo['shipping_id']));
        $this->assign('orderInfo', $orderInfo);
        return $this->fetch('shipping');
    }
    /*------------------------------------------------------ */
    //-- 未发货
    /*------------------------------------------------------ */
    public function unshipping()
    {
        $order_id = input('order_id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        $config = config('config.');
        $operating = $this->Model->operating($orderInfo);//订单操作权限
        if ($operating['unshipping'] !== true) return $this->error('订单当前状态不能操作未发货.');
        $data['order_id'] = $order_id;
        $data['shipping_status'] = $config['SS_UNSHIPPED'];
        $data['shipping_time'] = 0;
        $data['invoice_no'] = '';
        $data['shipping_name'] = '';
        $data['shipping_id'] = 0;
        $res = $this->Model->upInfo($data);
        if ($res !== true) return $this->error($res);
        $orderInfo['shipping_status'] = $config['SS_UNSHIPPED'];
        $this->Model->_log($orderInfo, '设为未发货,原发货信息：' . $orderInfo['shipping_name'] . '，单号：' . $orderInfo['invoice_no']);
        return $this->success('设为未发货成功！', 'reload');
    }
    /*------------------------------------------------------ */
    //-- 设置为已签收
    /*------------------------------------------------------ */
    public function sign()
    {
        $order_id = input('order_id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        $operating = $this->Model->operating($orderInfo);//订单操作权限
        if ($operating['sign'] !== true) return $this->error('订单当前状态不能操作签收');
        $res = $this->Model->sign($orderInfo);
        if ($res !== true) return $this->error($res);
        return $this->success('设为签收成功！', 'reload');
    }
    /*------------------------------------------------------ */
    //-- 获取奖励明细
    /*------------------------------------------------------ */
    public function getRewardLog()
    {
        $order_id = input('order_id','0','intval');
        if ($order_id < 1){
            return $this->error('传参错误.');
        }
        $RewardLogModel = new RewardLogModel();
        $list = $RewardLogModel->getOrderRewardLog($order_id);
        $rewardTotal = 0;
        foreach ($list as $v){
            $rewardTotal += $v['reward_money'];
        }
        $this->assign('list',$list);
        $this->assign('rewardTotal',$rewardTotal);
        $this->assign('status',$RewardLogModel->status);
        $this->assign("roleList", (new RoleModel)->getRows(1));
        $data['content'] = $this->fetch('reward_log')->getContent();
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 设置为未签收
    /*------------------------------------------------------ */
    public function unSign()
    {
        $order_id = input('order_id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        $operating = $this->Model->operating($orderInfo);//订单操作权限
        if ($operating['unsign'] !== true) return $this->error('订单当前状态不能进行此操作.');
        $res = $this->Model->setUnSign($orderInfo);
        if ($res !== true) return $this->error($res);

        return $this->success('设为未签收成功.','reload');
    }
    /*------------------------------------------------------ */
    //-- 重新计算奖励明细
    /*------------------------------------------------------ */
    public function refreshReward()
    {
        $order_id = input('order_id','0','intval');
        $mkey = 'refreshRewardByOrder_'.$order_id;
        $res = redisLook($mkey,60);//redis锁
        if ($res == false) return '正在执行中，请销后再试.';
        $orderInfo = $this->Model->info($order_id,false);
        //判断能否重新计算
        $RewardLogModel = new RewardLogModel();
        $where[] = ['from_type','=','order'];
        $where[] = ['from_id','=',$order_id];
        $where[] = ['status','=',2];
        $count = $RewardLogModel->where($where)->count();
        if ($count > 0){
            return $this->error('已有奖励发放到帐，不能重新计算.');
        }
        Db::startTrans();//开启事务
        $where = [];
        $where[] = ['from_type','=','order'];
        $where[] = ['from_id','=',$order_id];
        $RewardLogModel->where($where)->delete();//删除旧记录
        $res = $RewardLogModel->runByOrder($orderInfo);
        if ($res !== true) {
            Db::rollback();// 回滚事务
            return $this->error($res);
        }
        Db::commit();// 提交事务
        redisLook($mkey,-1);//销毁锁
        return $this->success();
    }
    /**
     * [exportOrder 订单导出]
     * @param  string $where [查询条件]
     */
    public function exportOrder($where='')
    {
        $list = $this->Model->where($where)->field('order_id')->select()->toArray();
        if (empty($list)) return $this->error('没有相关数据！');
        $exportData['order_sn'] = '订单编号';
        $exportData['user'] = '下单人';
        $exportData['proxy_name'] = '代理层级';
        $exportData['supply'] = '拿货上级';
        $exportData['purchase_type_name'] = '订单类型';
        $exportData['ostatus'] = '订单状态';

        $exportData['consignee'] = '收货人';
        $exportData['province'] = '省';
        $exportData['city'] = '城市';
        $exportData['district'] = '区域';
        $exportData['merger_name'] = '省市区';
        $exportData['address'] = '地址';
        $exportData['mobile'] = '手机号码';
        $exportData['buyer_message'] = '买家留言';
        $exportData['shipping_name'] = '快递名称';
        $exportData['invoice_no'] = '发货单号';
        $exportData['pay_name'] = '支付名称';
        $exportData['goods_amount'] = '商品总金额';
        $exportData['shipping_fee'] = '运费';

        $exportData['_add_time'] = '下单时间';
        $exportData['_pay_time'] = '支付时间';
        $exportData['_shipping_time'] = '发货时间';
        $exportData['_cancel_time'] = '取消时间';
        $exportData['order_amount'] = '实收款';

        $exportData['goodsInfo'] = '商品信息';
        foreach ($list as $k=>$row) {
            $info = $this->Model->info($row['order_id']);
            $merger_name = explode(',', $info['merger_name']);
            foreach ($exportData as $key => $val) {
                if ($key == 'user') {
                    $info[$key] = $info['user_id'] . '-' .userInfo($info['user_id'],true,'real_name');
                }elseif ($key == 'supply') {
                    $info[$key] = $info['supply_uid'] . '-' .$info['supply_name'];
                } elseif ($key == 'province') {
                    $info[$key] = $merger_name[0];
                } elseif ($key == 'city') {
                    $info[$key] = $merger_name[1] ;
                } elseif ($key == 'district') {
                    $info[$key] = $merger_name[2];
                } elseif ($key == 'goodsInfo') {
                    $info[$key] = '';

                    foreach ($info['goodsList'] as $grow) {
                        $info[$key] .= $grow['goods_name'] . '_' . $grow['sku_name'] . '(' . $grow['goods_sn'] . ') * ' . $grow['goods_number'] . ' || ';
                    }
                }
            }
            $list[$k] = $info;
        }
        $col = [];
        exportExcel($list,$exportData,$col,'云仓订单列表');
    }
}
