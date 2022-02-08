<?php
/*------------------------------------------------------ */
//-- 快递100接口
//-- @author iqgmy
/*------------------------------------------------------ */
namespace shipping;
$i = (isset($modules)) ? count($modules) : 0;
$modules[$i]["name"] = "快递100";
class Kd100{
    /*------------------------------------------------------ */
    //--  获取物流信息
    /*------------------------------------------------------ */
    public static function getLog($shipping_code,$invoice_no,$mobile=''){
		
        $return['code'] = 0;
        if (empty($shipping_code)==true){
            $return['data'] = [];
            return $return;
        }
        //参数设置
        $key = settings('kd100_key');					//客户授权key
        $customer = settings('kd100_customer');			//客户授权customer
        $param = array (
            'com' =>$shipping_code,             //快递100的快递公司编码
            'num' =>$invoice_no,     //快递单号
            'phone' => substr($mobile,-4),                //手机号
        );

        //请求参数
        $post_data = array();
        $post_data["customer"] = $customer;
        $post_data["param"] = json_encode($param);
        $sign = md5($post_data["param"].$key.$post_data["customer"]);
        $post_data["sign"] = strtoupper($sign);

        $url = 'http://poll.kuaidi100.com/poll/query.do';    //实时查询请求地址

        $params = "";
        foreach ($post_data as $k=>$v) {
            $params .= "$k=".urlencode($v)."&";              //默认UTF-8编码格式
        }
        $post_data = substr($params, 0, -1);

        //发送post请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch); // 关闭CURL会话

        $res = json_decode($result,true);

        if ($res['message'] != 'ok'){
            $return['msg'] = $res['message'];
            return $return;
        }

        $return['code'] = 1;
        $return['data'] = $res['data'];
        return $return;
    }

}


?> 