<?php

namespace app\mainadmin\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 支付相关
/*------------------------------------------------------ */
class PaymentModel extends BaseModel
{
	protected $table = 'main_payment';
	public  $pk = 'pay_id';
	protected $mkey = 'main_payment_';
   /*------------------------------------------------------ */
    //--  清除memcache
    /*------------------------------------------------------ */
    public function cleanMemcache(){
        Cache::rm($this->mkey.'pay_id');
		Cache::rm($this->mkey.'pay_code');
    }
	/*------------------------------------------------------ */
	//-- 列表
	/*------------------------------------------------------ */
	public function getRows($platform = false,$type='pay_id',$is_channel = false){
		$data = Cache::get($this->mkey.$type);
		if (empty($data)){
		    $where[] = ['is_used','=',1];
			$rows = $this->where($where)->field('*,pay_id AS id,pay_name AS name')->order('is_pay DESC,sort_order DESC')->select()->toArray();
			foreach ($rows as $row){
				if ($type == 'pay_id'){
					$data[$row['pay_id']] = $row;
				}else{
					$data[$row['pay_code']] = $row;
				}
			}
			Cache::set($this->mkey.$type,$data,600);
		}
		if (empty($platform) == false){
			foreach ($data as $key=>$row){
                //停用的移除
				if ($row['status'] == 0 && $is_channel == false) {
                    unset($data[$key]);
                    continue;
                }
                if ($row['pay_id'] <= 3){
                    continue;
                }
                //非微信小程序请求移除
                if ($platform == 'MP-WEIXIN'){
                    if ($row['pay_code'] != 'miniAppPay'){
                        unset($data[$key]);
                    }
                    continue;
                }
                if ($row['pay_code'] == 'weixin'){
                   $pay_config = json_decode(urldecode($row['pay_config']),true);
                   if (in_array($platform,['H5-WX','H5']) == false){
                       unset($data[$key]);
                   }elseif ($platform == 'H5-WX' && in_array('JSAPI',$pay_config['support']) == false){
                        unset($data[$key]);
                   }elseif ($platform == 'H5' && in_array('H5',$pay_config['support']) == false){
                        unset($data[$key]);
                   }
                   continue;
                }
                //非app请求，移除
                if ($platform == 'android' || $platform == 'ios'){
                    if (!strstr(strtoupper($row['pay_code']),'APP') || $row['pay_code'] == 'miniAppPay'){
                        unset($data[$key]);
                        continue;
                    }
                }elseif (strstr(strtoupper($row['pay_code']),'APP')){
                    unset($data[$key]);
                    continue;
                }
			}
		}
		return $data;
	}
	

}
