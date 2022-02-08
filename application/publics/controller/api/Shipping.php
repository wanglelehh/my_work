<?php
namespace app\publics\controller\api;
use app\ApiController;

use app\shop\model\OrderModel;
use app\shop\model\ShippingLogModel;
use app\shop\model\ShippingModel;
/*------------------------------------------------------ */
//-- 快递相关API
/*------------------------------------------------------ */
class Shipping extends ApiController
{
	
 	 /*------------------------------------------------------ */
    //-- 获取物流信息
    /*------------------------------------------------------ */
    public function getLog(){
		$order_id = input('order_id',0,'intval');
        $type = input('type','shop','trim');
		if ($order_id < 1) return $this->error('传参错误.');
		if ($type == 'channel'){
            $OrderModel = new \app\channel\model\OrderModel();
            $orderInfo = $OrderModel->info($order_id);
        }else{
            $OrderModel = new OrderModel();
            $orderInfo = $OrderModel->info($order_id);
        }

		if (empty($orderInfo)) return $this->error('订单不存在.');
		if ($orderInfo['shipping_status'] < 1) return $this->success();
		
		$ShippingLogModel = new ShippingLogModel();
		$res = $ShippingLogModel->getInfo($orderInfo,$type);

		if (is_array($res) == false) return $this->error($res);	
		foreach ($res['data'] as $key=>$row){
			$row['_time'] = explode(' ',$row['time']);
			$row['isend'] = strstr($row['context'],'签收')?1:0;
			$res['data'][$key] = $row;
		}

        $data['data'] = $res['data'];
        $data['shipping_name'] = $orderInfo['shipping_name'];
        $data['invoice_no'] = $orderInfo['invoice_no'];
        return $this->success($data);
		
	}
}
