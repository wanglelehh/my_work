<?php
namespace app\fightgroup\controller\api;
use app\ApiController;

use app\shop\model\OrderModel;
use app\fightgroup\model\FightGroupListModel;
/*---------------------------------------------------- */
//-- 订单相关API
/*------------------------------------------------------ */
class Order extends ApiController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->checkLogin();//验证登陆
        $this->Model = new OrderModel();
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    /*------------------------------------------------------ */
    public function getList(){
        $where[] = ['order_type','=',2];
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $where[] = ['is_del','=',0];
        $topType = input('topType',0,'intval');
        $type = input('type','','trim');
        if ($topType == 1){//我发起的
            $where[] = ['is_initiate','=',1];
        }else{//我参与的
            $where[] = ['is_initiate','=',0];
        }
         switch ($type){
             case 'waitPay':
                 $where[] = ['is_pay', '>', 0];
                 $where[] = ['order_status', '=', '0'];
                 $where[] = ['pay_status', '=', '0'];
             break;
             case 'fging':
                 $where[] = ['order_status', '=', '1'];
                 $where[] = ['is_success', '=', '0'];
                 break;
             case 'waitShipping':
                 $where[] = ['order_status', '=', '1'];
                 $where[] = ['is_success', '=', '1'];
                 $where[] = ['shipping_status', '=', '0'];
                 break;
            case 'waitSign':
                 $where[] = ['order_status', '=', '1'];
                 $where[] = ['shipping_status', '=', '1'];
                 break;
            case 'sign':
                 $where[] = ['order_status', '=', '1'];
                 $where[] = ['shipping_status', '=', '2'];
             break;
             default:
             break;
         }
        $data = $this->getPageList($this->Model, $where,'order_id',5);
        $config = config('config.');
         foreach ($data['list'] as $key=>$order){
            $order = $this->Model->info($order['order_id'],$config);
            $data['list'][$key] = $order;
         }
        return $this->success($data);
    }

/*------------------------------------------------------ */
    //-- 获取订单详细
    /*------------------------------------------------------ */
    public function info(){
        $order_id = input('order_id',0,'intval');
        if ($order_id < 1) return $this->error('传参错误.');
        $orderInfo = $this->Model->info($order_id);
        if (empty($orderInfo) || $orderInfo['order_type'] != 2){
            return $this->error('请求错误.');
        }
        if ($orderInfo['user_id'] != $this->userInfo['user_id']) return $this->error('无权访问.');
        $data['orderInfo'] = $orderInfo;
        $data['fgInfo'] = (new FightGroupListModel)->info($orderInfo['pid']);
        $data['downDate'] = '';
        if ($orderInfo['ostatus'] == '待付款'){
            $data['downDate'] = dateTpl($orderInfo['wait_pay_time'],'Y-m-d H:i:s',true);
        }elseif ($orderInfo['ostatus'] == '拼团中'){
            $data['downDate'] = dateTpl($data['fgInfo']['fail_time'],'Y-m-d H:i:s',true);
        }
        return $this->success($data);
    }

 	
}
