<?php

namespace app\channel\model;
use app\BaseModel;
//*------------------------------------------------------ */
//-- 库存明细表
/*------------------------------------------------------ */
class StockDetailModel extends BaseModel
{
	protected $table = 'channel_proxy_stock_detail';
	public  $pk = 'id';

    /*------------------------------------------------------ */
    //-- 写库明细记录
    //-- $user_id int 代理会员ID
    //-- $purchase_type int 仓库类型
    //-- $goods_id int 商品ID
    //-- $sku_id int 规格ID
    //-- $order_id int 订单ID，后台操作为0
    //-- $operate int 1入库，2出库
    //-- $goods_number int 库存数量
    //-- $log str 操作备注
    //-- $time int 操作时间
    //-- $admin_id int 管理员ID，后台操作才传递
    /*------------------------------------------------------ */
    public function saveLog($user_id,$purchase_type,$goods_id,$sku_id,$order_id,$operate,$goods_number,$log,$time,$admin_id=0){
        $inData = [];
        $inData['hash_code'] = md5($user_id.'_'.$purchase_type.'_'.$goods_id.'_'.$sku_id);
        $inData['purchase_type'] = $purchase_type;
        $inData['order_id'] = $order_id;
        $inData['user_id'] = $user_id;
        $inData['goods_id'] = $goods_id;
        $inData['sku_id'] = $sku_id;
        $inData['goods_number'] = $goods_number;
        $inData['add_time'] = $time;
        $inData['log'] = $log;
        $inData['operate'] = $operate;
        $inData['admin_id'] = $admin_id;
        $res = $this->create($inData);
        if ($res->id < 1) {
            return false;
        }
        return true;
    }
}
