<?php

namespace app\shop\controller\sys_admin;

use app\AdminController;
use app\shop\model\OrderModel;
use app\shop\model\OrderGoodsModel;
use app\shop\model\OrderLogModel;
use app\shop\model\ShippingModel;
use app\mainadmin\model\SettingsModel;
use app\distribution\model\DividendModel;
use app\shop\model\PrintTemplateModel;
use app\member\model\RoleModel;
use think\facade\Cache;
use think\Db;


/**
 * 订单相关
 * Class Index
 * @package app\store\controller
 */
class Order extends AdminController
{

    private $shipping_model = null;
    private $setting_model = null;
    public $order_type = false;

    //*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    public function initialize($isretrun = true)
    {
        parent::initialize($isretrun);
        $this->Model = new OrderModel();
        $this->shipping_model = new ShippingModel();
        $this->setting_model = new SettingsModel();
    }

    //*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index()
    {
        $reportrange = input('reportrange', '', 'trim');
        if (empty($reportrange) == false) {
            $reportrange = str_replace('_', '/', $reportrange);
            $dtime = explode('-', $reportrange);
            $this->assign("start_date", $dtime[0]);
            $this->assign("end_date", $dtime[1]);
        } else {
            $this->assign("start_date", date('Y/m/01', strtotime("-1 year")));
            $this->assign("end_date", date('Y/m/d'));
        }

        //首页跳转时间
        $start_date = input('start_time', '0', 'trim');
        $end_date = input('end_time', '0', 'trim');
        if( $start_date || $end_date){

            $this->assign("start_date",str_replace('_', '/', $start_date));
            $this->assign("end_date",str_replace('_', '/', $end_date));
        }
        //限时优惠活动ID
        $favour_id = input('favour_id',0,'intval');
        $this->assign("favour_id",$favour_id);

        $this->getList(true);
        return $this->fetch('index');
    }


    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false, $is_cancel = false)
    {
        $search['user_id'] = input('user_id', 0, 'intval');
        if ($this->order_type == 'fg_order') {
            $where[] = ['order_type', '=', 2];
        }elseif($this->order_type == 'integral_order') {
            $where[] = ['order_type', '=', 1];
        }else{
            $where[] = ['is_success', '=', 1];
            if ($this->store_id > 0) {
                $where[] = ['store_id', '=', $this->store_id];
            } elseif ($this->supplyer_id > 0) {
                $where[] = ['supplyer_id', '=', $this->supplyer_id];
            } elseif ($this->is_supplyer == true) {
                $where[] = ['supplyer_id', '>', 0];
            }else{
                $where[] = ['is_split', '<', 2];//不查询拆单的
                if ($search['user_id']==0){
                    $where[] = ['supplyer_id', '=', 0];
                }
                $where[] = ['store_id', '=', 0];
            }
        }
        $search['keyword'] = input('keyword', '', 'trim');
        $time_type = input('time_type', '', 'trim');
        $def_start_time = strtotime(date('Y/m/01', strtotime("-1 year")));
        if (empty($time_type) == false) {
            $is_search = input('is_search',0,'intval');
            $search['start_time'] = input('start_time', '', 'trim');
            $search['end_time'] = input('end_time', '', 'trim');
            $search['start_time'] = str_replace('_', '-', $search['start_time']);
            $search['end_time'] = str_replace('_', '-', $search['end_time']);
            $start_time = $search['start_time'] ? strtotime($search['start_time']) : $def_start_time;
            $end_time = $search['end_time'] ? strtotime($search['end_time']) : time();
            if ($start_time == $end_time) $end_time += 86399;
            if ($is_search == 1){
                if (empty($search['start_time']) == false){
                    $where[] = [$time_type, 'between', array($start_time, $end_time)];
                }
            }else{
                $where[] = [$time_type, 'between', array($start_time, $end_time)];
            }
        } else {
            $search['state'] = input('state', 0, 'intval');
            $reportrange = input('reportrange', '', 'trim');
            if (empty($reportrange) == false) {
                $reportrange = str_replace('_', '/', $reportrange);
                $dtime = explode('-', $reportrange);
                $where[] = ['add_time', 'between', [strtotime($dtime[0]), strtotime($dtime[1]) + 86399]];
            } else{
                $where[] = ['add_time', 'between', [$def_start_time, time()]];
            }
            if (empty($search['keyword'])) {
                if ($is_cancel == true) {
                    $where[] = ['order_status', '=', config('config.OS_CANCELED')];
                } else if (in_array($search['state'], [7, 8]) == false) {
                    $where[] = ['order_status', '<>', config('config.OS_CANCELED')];
                }
            }
        }

        $search['order_sn'] = input('order_sn', '', 'trim');
        if ($search['order_sn']) $where[] = ['order_sn', '=', $search['order_sn']];
        if ($search['user_id'] > 0) $where[] = ['user_id', '=', $search['user_id']];

        $search['consignee'] = input('consignee', '', 'trim');
        if ($search['consignee']) $where[] = ['consignee', 'like', '%' . $search['consignee'] . '%'];
        $search['address'] = input('address', '', 'trim');
        if ($search['address']) $where[] = ['address', 'like', '%' . $search['address'] . '%'];

        $search['tel'] = input('tel', '', 'trim');
        if ($search['tel']) {
            $where[] = ['tel', '=', $search['tel']];
        }
        $search['mobile'] = input('mobile', '', 'trim');
        if ($search['mobile']) {
            $where[] = ['mobile', '=', $search['mobile']];
        }
        $search['favour_id'] = input('favour_id', 0, 'intval');
        if ($search['favour_id']) {
            $where['and'][] = "find_in_set(".$search['favour_id'].",favour_id)";
        }

        //省市区
        $search['province'] = input('province', 0, 'intval');
        $search['city'] = input('city', 0, 'intval');
        $search['district'] = input('district', 0, 'intval');
        if ($search['district'] > 0) {
            $where[] = ['district', '=', $search['district']];
        } elseif ($search['city'] > 0) {
            $where[] = ['city', '=', $search['city']];
        } elseif ($search['province'] > 0) {
            $where[] = ['province', '=', $search['province']];
        }//省市区end
        $search['shipping_id'] = input('shipping_id', 0, 'intval');
        if ($search['shipping_id']) $where[] = ['shipping_id', '=', $search['shipping_id']];
        $search['pay_id'] = input('pay_id', 0, 'intval');
        if ($search['pay_id']) $where[] = ['pay_id', '=', $search['pay_id']];

        $search['order_status'] = input('order_status', -1, 'intval');
        if ($search['order_status'] >= 0) $where[] = ['order_status', '=', $search['order_status']];
        $search['pay_status'] = input('pay_status', 0, 'intval');
        if ($search['pay_status'] > 0) $where[] = ['pay_status', '=', $search['pay_status']];
        $search['shipping_status'] = input('shipping_status', 0, 'intval');
        if ($search['shipping_status'] > 0) $where[] = ['shipping_status', '=', $search['shipping_status']];



        if (!empty($search['keyword'])) {
            $search['searchBy'] = input('searchBy', '', 'trim');
            //综合状态
            switch ($search['searchBy']) {
                case 'consignee':
                    $where[] = ['consignee', 'like', $search['keyword'] . '%'];
                    break;
                case 'goods_sn':
                    $where[] = ['','exp',Db::raw("FIND_IN_SET('".$search['keyword']."',buy_goods_sn)")];
                    break;
                case 'mobile':
                    $where[] = ['mobile', 'like', $search['keyword'] . '%'];
                    break;
                case 'order_sn':
                    $where[] = ['order_sn', 'like', $search['keyword'] . '%'];
                    break;
                case 'user_id':
                    $where[] = ['user_id', '=', $search['keyword']];
                    break;
                default:
                    break;
            }
        }
        $config = config('config.');
        //综合状态
        switch ($search['state']) {

            /**无须付款,待确认**/
            case "1" :
                $where[] = ['order_status', '=', $config['OS_UNCONFIRMED']];
                $where[] = ['is_pay', '=', 0];
                break;
            /**,待支付**/
            case "2" :
                $where[] = ['order_status', 'in', [$config['OS_CONFIRMED'], $config['OS_UNCONFIRMED']]];
                $where[] = ['pay_status', '=', $config['PS_UNPAYED']];
                $where[] = ['is_pay', 'in', [1, 2]];
                break;
            /**,待发货**/
            case "3" :
                $where[] = ['order_status', '=', $config['OS_CONFIRMED']];
                $where[] = ['shipping_status', '=', $config['SS_UNSHIPPED']];
                if ($this->order_type == 'fg_order') {
                    $where[] = ['is_success', '=', 1];
                }
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
            /**已付款待发货**/
            case "9" :
                $where[] = ['order_status', '=', $config['OS_CONFIRMED']];
                $where[] = ['pay_status', '=', $config['PS_PAYED']];
                $where[] = ['shipping_status', '=', $config['SS_UNSHIPPED']];
                break;
            /**线下付款待审核**/
            case "10" :
                $where[] = ['pay_status', '=', $config['PS_WAITCHACK']];
                break;
            default:
                break;
        }
        $export = input('export', 0, 'intval');
        if ($export > 0) {
            return $this->exportOrder($where);
        }
        $data = $this->getPageList($this->Model, $where, 'order_id');
        $this->assign("is_cancel", $is_cancel);
        foreach ($data['list'] as $key => $row) {
            $data['list'][$key] = $this->Model->info($row['order_id']);
        }
        $this->assign("orderType", config('config.order_type'));
        $this->assign("orderLang", lang('order'));
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
        if ($this->supplyer_id > 0) {
            if ($this->supplyer_id != $orderInfo['supplyer_id']) {
                return $this->error("您无权操作此订单.");
            }
        }
        if ($orderInfo['is_pay_eval'] == 1){
            asynRun('shop/OrderModel/asynRunPaySuccessEval',['order_id'=>$orderInfo['order_id']]);//异步执行
        }
        $orderLog = (new OrderLogModel)->where('order_id', $order_id)->order('log_id DESC')->select()->toArray();
        $this->assign("orderLog", $orderLog);
        $this->assign("orderLang", lang('order'));
        $operating = $this->Model->operating($orderInfo);//订单操作权限
        $this->assign("operating", $operating);
        $orderInfo['dividend_role_name'] = (new RoleModel)->info($orderInfo['dividend_role_id'], true);
        $this->assign('orderInfo', $orderInfo);
        $logWhere[] = ['order_type', 'in', ['order','order_buy_back']];
        $logWhere[] = ['order_id', '=', $order_id];
        $dividend_log = (new DividendModel)->where($logWhere)->order('award_id,level ASC')->select()->toArray();
        $this->assign('dividend_log', $dividend_log);
        return $this->fetch('info');
    }
    /*------------------------------------------------------ */
    //-- 发货管理
    /*------------------------------------------------------ */
    public function shipping()
    {
        $order_id = input('id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        if ($this->supplyer_id > 0) {
            if ($this->supplyer_id != $orderInfo['supplyer_id']) {
                return $this->error("您无权操作此订单.");
            }
        }
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
            $this->Model->_log($orderInfo, '操作发货');
            return $this->success('操作发货成功！','reload');
        }
        $this->assign('sys_model_kdn_print', settings('sys_model_kdn_print'));
        $this->assign('shippingOpt', arrToSel($shipping, $orderInfo['shipping_id']));
        $kdnpingopt = $ShippingModel->getRows('kdn_code');
        $this->assign('kdnpingopt', arrToSel($kdnpingopt, $orderInfo['shipping_id']));
        $this->assign('orderInfo', $orderInfo);
        return $this->fetch('shop@sys_admin/order/shipping');
    }


    /**
     * 快递鸟打单页面
     */
    public function printPage(){
        $order_id = input('id');
        if(!$order_id){
            return $this->error('订单号异常');
        }
        $print_type = settings('kdn_print_type');
        $this->assign('print_type',$print_type);
        $this->assign('order_ids',$order_id);
        return $this->fetch();
    }

    /*------------------------------------------------------ */
    //-- 快递鸟批量打单数据出口
    /*------------------------------------------------------ */
    public function printOrder(){
        $order_id = input('id');
        if(!$order_id){
            return $this->error('订单号异常');
        }
        $id_arr = explode(',',$order_id);
        $orderModel = new OrderModel();
        $PrintModel = new PrintTemplateModel();
        $result = [];
        foreach ($id_arr as $k => $v) {
            $order = $orderModel->where(['order_id'=>$v])->find();
            $order_devc = $PrintModel->where('order_id',$v)->find();
            $arr = [];
            $arr['order_sn'] = $order['order_sn'];
            if(!$order_devc){
                $arr['code'] = 0;
                $arr['msg'] = '该订单未在快递鸟下单';
                $arr['html'] = '';
            }else{
                $arr['code'] = 1;
                $arr['msg'] = '打印成功';
                $arr['html'] = $order_devc['temp_html'];
                $orderModel->where(['order_id'=>$v])->update(['print_state'=>1]);
                $orderModel->cleanMemcache($v);
            }
            $result[] = $arr;
        }
        return json($result);
    }

    /*------------------------------------------------------ */
    //-- 批量发货管理
    /*------------------------------------------------------ */
    public function batchShipping()
    {
        if ($this->request->isPost()) {
            $order_ids = input('order_ids', '', 'trim');
            if (empty($order_ids)) {
                return $this->error("请选择需要发货的订单.");
            }
            $order_ids = explode(',', $order_ids);
            $kd_type = input('post.kd_type', '3', 'intval');
            $kdn_shipping_id = input('post.kdn_shipping_id', '', 'intval');
            $ShippingModel = new ShippingModel();
            $config = config('config.');
            $error = [];
            $okNum = 0;
            $shipping = $ShippingModel->getRows();
            foreach ($order_ids as $order_id) {
                $Arr = [];
                $orderInfo = $this->Model->find($order_id);
                if ($this->supplyer_id > 0) {
                    if ($this->supplyer_id != $orderInfo['supplyer_id']) {
                        $error[] = '订单' . $orderInfo['order_sn'] . ',您无权操作此订单';
                        continue;
                    }
                }
                $operating = $this->Model->operating($orderInfo);//订单操作权限
                if ($operating['shipping'] !== true) {
                    $error[] = '订单' . $orderInfo['order_sn'] . ',操作失败';
                    continue;
                }
                if ($kd_type == 3) {
                    $ptModel = new PrintTemplateModel();
                    $res = $ShippingModel->kdnShipping($shipping[$kdn_shipping_id], $orderInfo);
                    if (is_array($res) == false) {
                        $error[] = '订单' . $orderInfo['order_sn'] . ':' . $res;
                        continue;
                    }
                    $upData['shipping_id'] = $res[0]['shipping_id'];
                    $upData['invoice_no'] = $res[1];
                    $upData['shipping_name'] = $res[0]['shipping_name'];
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
                }
                $upData['order_id'] = $order_id;
                $upData['shipping_status'] = $config['SS_SHIPPED'];
                $upData['shipping_time'] = time();
                $res = $this->Model->upInfo($upData);
                if ($res != true) {
                    $error[] = '订单' . $orderInfo['order_sn'] . ',操作失败.';
                    continue;
                }
                $orderInfo['shipping_status'] = $upData['shipping_status'];
                $this->Model->_log($orderInfo, '批量发货');
                $okNum += 1;
            }

            return $this->success('提交' . count($order_ids) . '张订单,共成功' . $okNum . '张订单<br>' . join('<br>', $error));
        }
        $ShippingModel = new ShippingModel();
        $kdnpingopt = $ShippingModel->getRows('kdn_code');
        $this->assign('kdnpingopt', arrToSel($kdnpingopt));
        return $this->fetch('shop@sys_admin/order/batch_shipping');
    }
    /*------------------------------------------------------ */
    //-- 改价
    /*------------------------------------------------------ */
    public function changePrice()
    {
        if ($this->supplyer_id > 0) {
            return $this->error("您无权操作.");
        }
        $order_id = input('id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        if ($this->request->isPost()) {
            $operating = $this->Model->operating($orderInfo);//订单操作权限
            if ($operating['changePrice'] !== true) return $this->error('订单当前状态不能操作改价.');
            $data['shipping_fee'] = input('fee', 0, 'intval');
            $order_amount = input('amount', 0, 'floatval');
            $data['order_amount'] = $order_amount + $data['shipping_fee'];
            $data['diy_discount'] = $orderInfo['goods_amount'] - $order_amount - $orderInfo['discount'] - $orderInfo['integral_money'] - $orderInfo['buy_again_discount'] - $orderInfo['use_bonus'];
            $data['is_dividend'] = 0;
            $data['money_paid'] = 0;
            $data['order_id'] = $order_id;

            $goodsDiscount = [];
            $goodsDiscount['totalMoney'] = 0;
            foreach ($orderInfo['goodsList'] as $og) {
                $goodskey = $og['goods_id'].'_'.$og['sku_id'];
                $goodsDiscount['goods_end'] = $goodskey; // 记录最后一个ID
                $goodsDiscount['goods'][$goodskey] = $og['sale_price'] * $og['goods_number']; // 当前记录商品总金额
                $goodsDiscount['totalMoney'] += $og['sale_price'] * $og['goods_number']; // 记录订单总金额
            }

            $diy_discount = $data['diy_discount'];//总优惠金额
            $OrderGoodsModel = new \app\shop\model\OrderGoodsModel();
            foreach ($orderInfo['goodsList'] as $og) {
                $goodskey = $og['goods_id'].'_'.$og['sku_id'];
                $usd_discount_price = 0;
                if ($diy_discount > 0) {
                    $per = $goodsDiscount['goods'][$goodskey] / $goodsDiscount['totalMoney']; // 计算占比
                    if ($goodsDiscount['goods_end'] == $goodskey) { // 最后一个商品优惠金额为剩下的所有
                        $usd_discount_price = $diy_discount;
                    } else {
                        $usd_discount_price = bcmul($diy_discount, $per, 2); // 通过占比计算当前商品优惠金额
                        $diy_discount -= $usd_discount_price ; // 总优惠金额 - 当前商品优惠金额
                    }
                }
                if ($usd_discount_price > 0) {
                    $res = $OrderGoodsModel->where('rec_id',$og['rec_id'])->update(['diy_discount'=>$usd_discount_price]);
                    if (!$res) return $this->error('系统错误，请重试 -1');
                }

            }

            $res = $this->Model->upInfo($data, 'changePrice');
            if ($res < 1) return $this->error();
            $this->Model->_log($orderInfo, '修改价格为：' . $order_amount);
            return $this->success('修改价格成功！','reload');
        }
        $this->assign('orderInfo', $orderInfo);
        return $this->fetch('shop@sys_admin/order/change_price');
    }
    /*------------------------------------------------------ */
    //-- 修改收货信息
    /*------------------------------------------------------ */
    public function editConsignee()
    {
        $order_id = input('id', 0, 'intval');
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
        return $this->fetch('shop@sys_admin/order/edit_consignee');
    }

    /*------------------------------------------------------ */
    //-- 线下支付收款确认
    /*------------------------------------------------------ */
    public function cfmCodPay()
    {
        $order_id = input('id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        if ($this->request->isPost()) {
            $config = config('config.');
            if ($orderInfo['is_pay'] != 2) return $this->error('非线下支付订单，不允许操作.');
            $operating = $this->Model->operating($orderInfo);//订单操作权限
            if ($operating['cfmCodPay'] !== true) return $this->error('订单当前状态不能操作线下打款确认.');
            $operate = input('operate', 0, 'intval');
            if ($operate < 1){
                return $this->error('请选择需要执行的操作.');
            }
            $upData['order_id'] = $order_id;
            $upData['cfmpay_user'] = AUID;
            $upData['transaction_id'] = input('transaction_id', '', 'trim');
            if ($operate == 1){
                $upData['order_status'] = $config['OS_CONFIRMED'];
                $upData['pay_status'] = $config['PS_PAYED'];
                $upData['pay_time'] = time();
                $upData['money_paid'] = $orderInfo['order_amount'];
                $upData['is_pay_eval'] = 1;//设为待执行支付成功后的相关处理
                if ($orderInfo['confirm_time'] < 1) {
                    $upData['confirm_time'] = time();
                }
                $orderInfo['order_status'] = $upData['order_status'];
                $log = '线下支付收款确认：';
            }else{
                if (empty($upData['transaction_id'])){
                    return $this->error('请在备注栏，填写失败原因.');
                }
                $upData['pay_status'] = $config['PS_CHACKERROR'];
                $log = '线下支付审核失败：';
            }

            $res = $this->Model->upInfo($upData);
            if ($res !== true) return $this->error($res);
            $orderInfo['pay_status'] = $upData['pay_status'];
            $this->Model->_log($orderInfo, $log . $upData['transaction_id']);
            $this->Model->asynRunPaySuccessEval($orderInfo);
            return $this->success('操作成功.','reload');
        }
        $this->assign('orderInfo', $orderInfo);
        return $this->fetch('shop@sys_admin/order/cfm_cod_pay');
    }

    /*------------------------------------------------------ */
    //-- 取消订单
    /*------------------------------------------------------ */
    public function cancel()
    {
        $order_id = input('id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        $config = config('config.');
        if ($orderInfo['store_id'] > 0) return $this->error('此订单只有门店有操作权限.');
        $operating = $this->Model->operating($orderInfo);//订单操作权限
        if ($operating['isCancel'] !== true) return $this->error('订单当前状态不能操作取消.');
        $upData['order_id'] = $order_id;
        $upData['order_status'] = $config['OS_CANCELED'];
        $upData['cancel_time'] = time();
        $res = $this->Model->upInfo($upData);
        if ($res !== true) return $this->error($res);
        $orderInfo['order_status'] = $upData['order_status'];
        $this->Model->_log($orderInfo, '取消订单');
        if ($orderInfo['pid'] > 0){//如果是子订单取消，判断主订单下属所有子订单是澡都已取消，是则主订单也取消
            $where['pid'] = $orderInfo['pid'];
            $where['order_status'] = $config['OS_CONFIRMED'];
            $count = $this->Model->where($where)->count('order_id');
            if ($count < 1){
                $upData = [];
                $upData['order_id'] = $orderInfo['pid'];
                $upData['order_status'] = $config['OS_CANCELED'];
                $upData['cancel_time'] = time();
                $this->Model->upInfo($upData);
                $this->Model->_log($orderInfo, '子订单全部取消，主订单执行取消');
            }
        }
        return $this->success('取消订单成功.','reload');
    }

    /*------------------------------------------------------ */
    //-- 未发货
    /*------------------------------------------------------ */
    public function unshipping()
    {
        $order_id = input('id', 0, 'intval');
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
        $order_id = input('id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        $config = config('config.');
        $operating = $this->Model->operating($orderInfo);//订单操作权限
        if ($operating['sign'] !== true) return $this->error('订单当前状态不能操作签收.');
        $data['order_id'] = $order_id;
        $data['shipping_status'] = $config['SS_SIGN'];
        $data['sign_time'] = time();
        $res = $this->Model->upInfo($data);
        if ($res !== true) return $this->error($res);
        $orderInfo['shipping_status'] = $data['shipping_status'];
        $this->Model->_log($orderInfo, '设为签收');
        return $this->success('设为签收成功！','reload');
    }

    /*------------------------------------------------------ */
    //-- 设置为未签收
    /*------------------------------------------------------ */
    public function unsign()
    {
        $order_id = input('id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        $config = config('config.');
        if ($orderInfo['shipping_status'] != $config['SS_SIGN']) return $this->error('订单不是签收状态，无法设为未签收！');
        //判断是否已分成 已分成订单不能设为未签收
        $data['order_id'] = $order_id;
        $data['shipping_status'] = $config['SS_SHIPPED'];
        $data['sign_time'] = 0;
        $res = $this->Model->upInfo($data, 'unsign');
        if ($res !== true) return $this->error($res);
        $orderInfo['shipping_status'] = $data['shipping_status'];
        $this->Model->_log($orderInfo, '设为未签收');
        return $this->success('设为未签收成功！','reload');
    }

    /*------------------------------------------------------ */
    //-- 设置为退货
    /*------------------------------------------------------ */
    public function returned()
    {
        $order_id = input('id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        $config = config('config.');
        if ($orderInfo['shipping_status'] != $config['SS_SHIPPED'] && $orderInfo['shipping_status'] != $config['SS_SIGN']) return $this->error('订单不是发货/签收状态，无法设为退货！');

        $data['order_id'] = $order_id;
        $data['order_status'] = $config['OS_RETURNED'];
        $data['returned_time'] = time();
        $res = $this->Model->upInfo($data);
        if ($res !== true) return $this->error($res);
        $orderInfo['order_status'] = $data['order_status'];
        $this->Model->_log($orderInfo, '设为退货');
        return $this->success('设为退货成功！', 'reload');
    }

    /*------------------------------------------------------ */
    //-- 设置为未付款
    /*------------------------------------------------------ */
    public function setUnPay()
    {
        $order_id = input('id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        $config = config('config.');
        if ($orderInfo['pay_status'] != $config['PS_PAYED']) return $this->error('订单不是付款状态，无法设为未付款！');
        if ($orderInfo['is_pay'] == 2){//线下支付，撤销付款状态，确定状态也撤销
            $upData['order_status'] = 0;
            $orderInfo['order_status'] = $upData['order_status'];
        }
        $upData['order_id'] = $order_id;
        $upData['pay_status'] = $config['PS_WAITCHACK'];
        $upData['pay_time'] = 0;
        $upData['money_paid'] = 0;
        $res = $this->Model->upInfo($upData);
        if ($res !== true) return $this->error($res);
        $orderInfo['pay_status'] = $upData['pay_status'];
        $this->Model->_log($orderInfo, '设为未付款');
        return $this->success('设为未付款成功！','reload');
    }

    /*------------------------------------------------------ */
    //-- 设置为退款
    /*------------------------------------------------------ */
    public function returnPay()
    {
        $order_id = input('id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        $config = config('config.');
        if ($orderInfo['pay_status'] != $config['PS_PAYED']) return $this->error('订单不是付款状态，无法设为退款.');

        $data['order_id'] = $order_id;
        $data['pay_status'] = $config['PS_RUNPAYED'];
        $data['tuikuan_money'] = $orderInfo['money_paid'];
        $data['tuikuan_time'] = time();
        $res = $this->Model->upInfo($data);
        if ($res !== true) return $this->error($res);
        $orderInfo['pay_status'] = $data['pay_status'];
        $this->Model->_log($orderInfo, '设为退款');
        return $this->success('设为退款成功！','reload');
    }


    /*------------------------------------------------------ */
    //-- 恢复订单
    /*------------------------------------------------------ */
    public function recover()
    {
        $order_id = input('id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        $config = config('config.');
        if (in_array($orderInfo['order_status'],[$config['OS_CANCELED'],$config['OS_RETURNED']]) == false) return $this->error('非取消/非退货订单不能恢复.');
        if ($orderInfo['order_status'] == $config['OS_RETURNED']){
            $log = '恢复退货订单';
            $data['returned_time'] = 0;
        }else{
            $log = '恢复取消订单';
            $data['cancel_time'] = 0;
        }
        $data['order_id'] = $order_id;
        if ($orderInfo['pay_status'] == $config['PS_PAYED']) {
            $data['order_status'] = $config['OS_CONFIRMED'];
        }else{
            $data['order_status'] = $config['OS_UNCONFIRMED'];
        }
        $shop_reduce_stock = settings('shop_reduce_stock');
        $data['is_stock'] = ($shop_reduce_stock == 0) ? 1 : 0;
        $res = $this->Model->upInfo($data,'recover');
        if ($res !== true) return $this->error($res);

        $orderInfo['order_status'] = $data['order_status'];
        $this->Model->_log($orderInfo, $log);

        return $this->success('恢复成功.','reload');
    }
    /*------------------------------------------------------ */
    //-- 重新计算分佣
    /*------------------------------------------------------ */
    public function upDividend()
    {
        $order_id = input('id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        $config = config('config.');
        if ($orderInfo['order_status'] == $config['OS_CANCELED']){
            return $this->error('已取消订单不能操作！');
        }
        if ($orderInfo['shipping_status'] == $config['SS_SIGN']) return $this->error('订单已签收后不能操作！');
        $orderInfo['is_dividend'] = 2;
        $res = $this->Model->runDistribution($orderInfo);//提成处理
        if ($res == false) {
            $this->Model->_log($orderInfo,'重新计算佣金失败.');
            return $this->error('重新计算佣金失败');
        }
        $this->Model->_log($orderInfo, '重新计算分佣');
        return $this->success('重新计算分佣成功！', 'reload');
    }

    /*------------------------------------------------------ */
    //-- 查询主页
    /*------------------------------------------------------ */
    public function search()
    {
        $ShippingModel = new ShippingModel();
        $this->assign("shippingList", $ShippingModel->getRows());
        $PaymentModel = new \app\mainadmin\model\PaymentModel();
        $this->assign("paymentList", $PaymentModel->getRows());
        $this->assign("orderLang", lang('order'));
        return $this->fetch('search');
    }

    /*------------------------------------------------------ */
    //-- 回收站
    /*------------------------------------------------------ */
    public function trash()
    {
        $this->assign("start_date", date('Y/m/01', strtotime("-1 months")));
        $this->assign("end_date", date('Y/m/d'));
        $this->trashList(true, true);
        return $this->fetch('index');
    }

    /*------------------------------------------------------ */
    //-- 商品回收站查询
    /*------------------------------------------------------ */
    public function trashList($runData = false)
    {
        return $this->getList($runData, 1);
    }

    /**
     * 自动签收
     */
    public function autoSign(){
        $this->Model->autoSign();
        return $this->success('完成.');
    }
    /**
     * [import 导入发货]
     * @return [type] [description]
     */
    public function import()
    {

        return $this->fetch('import');
    }
    // 导入发货订单
    public function importFile()
    {
        ini_set('max_execution_time', '0');
        require'../extend/phpexcel/PHPExcel.php';
        header("content-type:text/html;charset=utf-8");
        //上传excel文件
        $file = request()->file('excel');
        if (empty($file) == true) {
            return $this->error('请上传格式为xls或xlsx的文件.');
        }
        $files = $file->getInfo();
        $type_arr = pathinfo($files['name']);
        $suffix = $type_arr['extension'];
        if($suffix=="xlsx"){
            $reader = \PHPExcel_IOFactory::createReader('Excel2007');
        }else{
            $reader = \PHPExcel_IOFactory::createReader('Excel5');
        }
        // echo $files['tmp_name'];
        $objContent = $reader->load($files['tmp_name']);
        $sheetContent = $objContent -> getSheet(0) -> toArray();


        $export_arr['order_sn'] = '订单编号';
        $export_arr['user_id'] = '会员ID';
        $export_arr['order_status'] = '订单状态';
        $export_arr['shipping_status'] = '物流状态';
        $export_arr['pay_status'] = '支付状态';
        $export_arr['consignee'] = '收货人';
        $export_arr['province'] = '省';
        $export_arr['city'] = '城市';
        $export_arr['district'] = '区域';
        $export_arr['merger_name'] = '省市区';
        $export_arr['address'] = '地址';
        $export_arr['mobile'] = '手机号码';
        $export_arr['best_time'] = '送货时间';
        $export_arr['buyer_message'] = '买家留言';
        $export_arr['shipping_name'] = '快递名称';
        $export_arr['invoice_no'] = '发货单号';
        $export_arr['pay_name'] = '支付名称';
        $export_arr['goods_amount'] = '商品总金额';
        $export_arr['shipping_fee'] = '运费';
        $export_arr['discount'] = '折扣金额';
        $export_arr['diy_discount'] = '额外折扣';
        $export_arr['dividend_amount'] = '分成总金额';
        $export_arr['order_amount'] = '订单金额';
        $export_arr['add_time'] = '添加时间';
        $export_arr['confirm_time'] = '确定时间';
        $export_arr['cancel_time'] = '取消时间';
        $export_arr['pay_time'] = '支付时间';
        $export_arr['shipping_time'] = '发货时间';
        $export_arr['sign_time'] = '签收时间';
        $export_arr['returned_time'] = '退货时间';
        $export_arr['goods_list'] = '商品明细';
        $export_arr['remarks'] = '备注';

        foreach ($sheetContent[0] as $key => $value) {
            if ($value == '订单编号') {
                $order_no_key = $key;
            }
            if ($value == '快递名称') {
                $shipping_name_key = $key;
            }
            if ($value == '发货单号') {
                $invoice_no_key = $key;
            }


        }
        $heads_arr = [];
        $x = 0;
        foreach ($export_arr as $item => $export) {
            foreach ($sheetContent[0] as $index => $name) {
                if ($name == $export) {
                    $heads_arr[$item] = $index;
                }else{
                    $heads_arr[$item] = $x;
                }
            }
            $x ++;
        }
        unset($sheetContent[0]);

        // print_r($heads_arr);die;

        $config = config('config.');
        $unique_no_arr = [];
        $error_arr = [];
        $up_arr = [];
        $ShippingModel = new ShippingModel();
        $i = 0;
        $time = time();
        foreach ($sheetContent as $row) {
            // 订单编号验证
            $order_sn = $row[$order_no_key] = $row[$order_no_key];
            if (empty($order_sn)) {
                $row[31] = '订单编号不能为空';
                $error_arr[] = $row;
                continue;
            }
            if (in_array($order_sn,$unique_no_arr)) {
                $row[31] = '订单编号重复';
                $error_arr[] = $row;
                continue;
            }

            array_push($unique_no_arr, $order_sn);
            $orderInfo = $this->Model->where('order_sn',$order_sn)->find();
            if (empty($orderInfo)) {
                $row[31] = '订单不存在';
                $error_arr[] = $row;
                continue;
            }

            if ($orderInfo['order_status'] != $config['OS_CONFIRMED'] || $orderInfo['shipping_status'] != $config['SS_UNSHIPPED']) {

                $row[31] = '订单当前状态不能操作发货';
                $error_arr[] = $row;
                continue;
            }

            if ($this->supplyer_id > 0) {
                if ($this->supplyer_id != $orderInfo['supplyer_id']) {
                    $row[31] = '您无权操作此订单';
                    $error_arr[] = $row;
                    continue;
                }
            }


            // 快递名称验证
            $shipping_name = trim($row[$shipping_name_key]);
            if (empty($shipping_name)) {
                $row[31] = '快递名称不能为空';
                $error_arr[] = $row;
                continue;
            }

            $shipping = $ShippingModel->where('shipping_name', $shipping_name)->find();
            if (empty($shipping)) {
                $row[31] = '快递名称不正确';
                $error_arr[] = $row;
                continue;
            }

            // 快递单号验证
            $invoice_no = $row[$invoice_no_key];
            if (empty($invoice_no)) {
                $row[31] = '快递单号不能为空';
                $error_arr[] = $row;
                continue;
            }

            $up_arr[$i]['order_id'] = $orderInfo['order_id'];
            $up_arr[$i]['shipping_id'] = $shipping['shipping_id'];
            $up_arr[$i]['shipping_name'] = $shipping['shipping_name'];
            $up_arr[$i]['invoice_no'] = $invoice_no;
            $up_arr[$i]['shipping_status'] = $config['SS_SHIPPED'];
            $up_arr[$i]['shipping_time'] = $time;
            $up_arr[$i]['order_status'] = $orderInfo['order_status'];
            $up_arr[$i]['pay_status'] = $orderInfo['pay_status'];
            $i ++ ;
        }
        $error_count = count($error_arr);
        $error_list = Cache::get($this->mkey . $this->supplyer_id);
        $error_data = [];
        foreach ($error_arr as $kk => $error) {
            foreach ($heads_arr as $names => $inx) {
                $error_data[$kk][$names] = $error[$inx];
            }
        }
        if (empty($error_list) == false) {
            // echo 123;
            foreach ($error_data as $errors) {
                array_push($error_list,$errors);
            }
        }else{
            $error_list =  $error_data;
        }
        Cache::set($this->mkey . $this->supplyer_id, $error_list);
        foreach ($up_arr as $data) {
            $res = $this->Model->upInfo($data,'sys');
            if ($res !== true) return $this->error($res);
            $this->Model->_log($data, '操作导入发货');
        }
        return $this->success('操作完成：导入成功 '.count($up_arr) .'，导入失败 '.$error_count);

    }
    /**
     * [errorExport 导出失败数据]
     */
    public function errorExport()
    {
        $list = Cache::get($this->mkey . $this->supplyer_id);
        if (empty($list) == true) return $this->error('暂无数据.');
        $export_arr['order_sn'] = '订单编号';
        $export_arr['user_id'] = '会员ID';
        $export_arr['order_status'] = '订单状态';
        $export_arr['shipping_status'] = '物流状态';
        $export_arr['pay_status'] = '支付状态';
        $export_arr['consignee'] = '收货人';
        $export_arr['province'] = '省';
        $export_arr['city'] = '城市';
        $export_arr['district'] = '区域';
        $export_arr['merger_name'] = '省市区';
        $export_arr['address'] = '地址';
        $export_arr['mobile'] = '手机号码';
        $export_arr['best_time'] = '送货时间';
        $export_arr['buyer_message'] = '买家留言';
        $export_arr['shipping_name'] = '快递名称';
        $export_arr['invoice_no'] = '发货单号';
        $export_arr['pay_name'] = '支付名称';
        $export_arr['goods_amount'] = '商品总金额';
        $export_arr['shipping_fee'] = '运费';
        $export_arr['discount'] = '折扣金额';
        $export_arr['diy_discount'] = '额外折扣';
        $export_arr['dividend_amount'] = '分成总金额';
        $export_arr['order_amount'] = '订单金额';
        $export_arr['add_time'] = '添加时间';
        $export_arr['confirm_time'] = '确定时间';
        $export_arr['cancel_time'] = '取消时间';
        $export_arr['pay_time'] = '支付时间';
        $export_arr['shipping_time'] = '发货时间';
        $export_arr['sign_time'] = '签收时间';
        $export_arr['returned_time'] = '退货时间';
        $export_arr['goods_list'] = '商品明细';
        $export_arr['remarks'] = '备注';
        $ColumnDimension['A'] = 18;
        $ColumnDimension['F'] = 18;
        $ColumnDimension['J'] = 20;
        $ColumnDimension['K'] = 18;
        $ColumnDimension['L'] = 18;
        $ColumnDimension['AF'] = 40;
        $ColumnDimension['AE'] = 40;
        Cache::rm($this->mkey . $this->supplyer_id);
        exportExcel($list,$export_arr,$ColumnDimension,'订单导入失败列表_');

    }
    /*------------------------------------------------------ */
    //-- 导出订单
    /*------------------------------------------------------ */
    public function exportOrder($where)
    {
        $count = $this->Model->where($where)->count('order_id');

        if ($count < 1) return $this->error('没有找到可导出的订单资料！');
        // $filename = '订单资料_' . date("YmdHis") . '.xls';
        $export_arr['order_sn'] = '订单编号';
        $export_arr['user_id'] = '会员ID';
        $export_arr['order_status'] = '订单状态';
        $export_arr['shipping_status'] = '物流状态';
        $export_arr['pay_status'] = '支付状态';
        $export_arr['consignee'] = '收货人';
        $export_arr['province'] = '省';
        $export_arr['city'] = '城市';
        $export_arr['district'] = '区域';
        $export_arr['merger_name'] = '省市区';
        $export_arr['address'] = '地址';
        $export_arr['mobile'] = '手机号码';
        $export_arr['best_time'] = '送货时间';
        $export_arr['buyer_message'] = '买家留言';
        $export_arr['shipping_name'] = '快递名称';
        $export_arr['invoice_no'] = '发货单号';
        $export_arr['pay_name'] = '支付名称';
        $export_arr['goods_amount'] = '商品总金额';
        $export_arr['shipping_fee'] = '运费';
        $export_arr['discount'] = '折扣金额';
        $export_arr['diy_discount'] = '额外折扣';
        $export_arr['dividend_amount'] = '分成总金额';
        $export_arr['order_amount'] = '订单金额';
        $export_arr['add_time'] = '添加时间';
        $export_arr['confirm_time'] = '确定时间';
        $export_arr['cancel_time'] = '取消时间';
        $export_arr['pay_time'] = '支付时间';
        $export_arr['shipping_time'] = '发货时间';
        $export_arr['sign_time'] = '签收时间';
        $export_arr['returned_time'] = '退货时间';
        $export_arr['goods_list'] = '商品明细';

        $OrderGoodsModel = new OrderGoodsModel();
        $orderLang = lang('order');
        $os = $orderLang['os'];
        $ss = $orderLang['ss'];
        $ps = $orderLang['ps'];
        $rows = $this->Model->where($where)->select();
        foreach ($rows as $row) {
            // print_r($row);
            $merger_name = explode(',', $row['merger_name']);
            foreach ($export_arr as $key => $val) {
                if (strstr($key, '_time')) {
                    $row->$key = $row->$key?date('Y-m-d H:i:s',$row->$key):'没有记录';
                }elseif ($key == 'order_status') {
                    $row->$key = strip_tags($os[$row->$key]);
                } elseif ($key == 'shipping_status') {
                    $row->$key = strip_tags($ss[$row->$key]);
                } elseif ($key == 'pay_status') {
                    $row->$key = strip_tags($ps[$row->$key]);
                } elseif ($key == 'province') {
                    $row->$key = $merger_name[0];
                } elseif ($key == 'city') {
                    $row->$key = $merger_name[1] ;
                } elseif ($key == 'district') {
                    $row->$key = $merger_name[2];
                } elseif ($key == 'goods_list') {
                    $grows = $OrderGoodsModel->field('goods_name,sku_name,goods_sn,goods_number')->where(['order_id' => $row['order_id']])->select();
                    $row->$key = '';
                    foreach ($grows as $grow) {
                        $row->$key .= $grow['goods_name'] . '_' . $grow['sku_name'] . '(' . $grow['goods_sn'] . ') * ' . $grow['goods_number'] . ' || ';
                    }
                }
            }

        }
        $ColumnDimension['A'] = 18;
        $ColumnDimension['F'] = 18;
        $ColumnDimension['J'] = 20;
        $ColumnDimension['K'] = 18;
        $ColumnDimension['L'] = 18;
        $ColumnDimension['AE'] = 40;
        exportExcel($rows,$export_arr,$ColumnDimension);
    }
}
