<?php

namespace app\shop\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 物流信息
/*------------------------------------------------------ */
class ShippingLogModel extends BaseModel
{
	protected $table = 'shop_shipping_log';
	public  $pk = 'order_id';
	protected $mkey = 'shipping_log_list';
	/*---------------------------------------------------- */
	//-- 列表
	/*------------------------------------------------------ */
	public function getInfo(&$orderInfo,$type = ''){
		$shop_shippping_view_fun = settings('shop_shippping_view_fun');
		if (empty($shop_shippping_view_fun)) return [];
		$info = Cache::get($this->mkey.$type.$orderInfo['order_id']);
		if (empty($info) == false) return $info;
		$where['order_id'] = $orderInfo['order_id'];
        $where['type'] = $type;
		$info = $this->where($where)->find();
		if (empty($info) == false){
			$info = $info->toArray();	
		}

		$ShippingModel = new ShippingModel();
		$shipping = $ShippingModel->getRows();
		$shipping_type = $ShippingModel->codeType[$shop_shippping_view_fun];
		if(!$shipping_type){
			 return [];
		}
		$shipping_code = $shipping[$orderInfo['shipping_id']][$shipping_type];

		if ($orderInfo['shipping_status'] == 1){
			 $fun = str_replace('/','\\','/shipping/'.$shop_shippping_view_fun);
       	 	 $Class = new $fun();
			 $res = $Class->getLog($shipping_code,$orderInfo['invoice_no'],$orderInfo['mobile']);
			 if ($res['code'] == 0) return $res['msg']; 
			
			 if (empty($info)== false){
				 $info['data'] = $res['data'];
				 $this->where($where)->update(['data'=>json_encode($res['data'],JSON_UNESCAPED_UNICODE),'update_time'=>time()]);
			 }else{
				 $info['order_id'] = $orderInfo['order_id'];
				 $info['data'] = $res['data'];
				 $this->save(['order_id'=>$orderInfo['order_id'],'type'=>$type,'data'=>json_encode($res['data'],JSON_UNESCAPED_UNICODE),'update_time'=>time()]);
			 }			
		}else{
			$info['data'] = json_decode($info['data'],true);
		}
		Cache::set($this->mkey.$type.$orderInfo['order_id'],$info,300);
		return $info;
	}


}
