<?php
/**
 *  支付宝插件
 */

namespace payment\alipayMobile;


require_once("aop/AopClient.php");
require_once("aop/AopCertClient.php");
require_once ('aop/request/AlipayTradeAppPayRequest.php');
require_once ('aop/request/AlipayTradeWapPayRequest.php');

use think\facade\Env;

use app\mainadmin\model\PaymentModel;
use app\publics\model\UpdatePayModel;

error_reporting(E_ERROR);

/**
 * 支付 逻辑定义
 * Class AlipayPayment
 * @package Home\Payment
 */
class alipayMobile
{
    public $alipay_config = array();// 支付宝支付配置参数
    protected $isCert = false;
    protected $aopClient;
    /**
     * 析构流函数
     */
    public function __construct()
    {

        unset($_GET['pay_code']);   // 删除掉 以免被进入签名
        unset($_REQUEST['pay_code']);// 删除掉 以免被进入签名

        $payment = (new PaymentModel)->where('pay_code', 'alipayMobile')->find(); // 找到支付插件的配置
        $config_value = json_decode(urldecode($payment['pay_config']), true);
        if (empty($config_value['alipay_public_key']) == false){
            $aop = new \AopClient();//设置了支付宝公钥，不使用证书模式
            $aop->alipayrsaPublicKey = $config_value['alipay_public_key'];
        }else{
            $aop = new \AopCertClient();
            $this->isCert = true;
            //调用getPublicKey从支付宝公钥证书中提取公钥
            $aop->alipayrsaPublicKey = $aop->getPublicKey($config_value['alipay_cert_public']);
        }

        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";

        $aop->appId = $config_value['alipay_partner'];//合作身份者id，以2088开头的16位纯数字
        //设置私钥和公钥
        $aop->rsaPrivateKey = $config_value['alipay_private_key'];//应用私钥

        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $this->aopClient = $aop;

        $this->alipay_config['alipay_public_key'] = $config_value['alipay_public_key'];//查看支付宝公钥

        $this->alipay_config['alipay_cert_public'] = $config_value['alipay_cert_public'];//支付宝公钥证书
        $this->alipay_config['alipay_app_cert'] = $config_value['alipay_app_cert'];//支付宝应用证书
        $this->alipay_config['alipay_root_cert'] = $config_value['alipay_root_cert'];//支付宝根证书
        $this->alipay_config['website'] = $config_value['website'];//指定使用的网站域名

        $this->alipay_config['transport'] = 'http';//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http

    }

    /**
     * 生成支付代码
     * @param   array $order 订单信息
     * @param   array $config_value 支付方式信息
     */
    function get_code($order, $config_value)
    {
        $webSite = explode('|',$this->alipay_config['website']);
        if (in_array($_SERVER['SERVER_NAME'],$webSite) == false){
            return false;
        }

        if ( $this->isCert == true){
            //是否校验自动下载的支付宝公钥证书，如果开启校验要保证支付宝根证书在有效期内
            $this->aopClient->isCheckAlipayPublicCert = true;
            //调用getCertSN获取证书序列号
            $this->aopClient->appCertSN = $this->aopClient->getCertSN($this->alipay_config['alipay_app_cert']);
            //调用getRootCertSN获取支付宝根证书序列号
            $this->aopClient->alipayRootCertSN = $this->aopClient->getRootCertSN($this->alipay_config['alipay_root_cert']);
        }

        $request = new \AlipayTradeWapPayRequest();
        $notify_url = SITE_URL.'/index.php/publics/Payment/notifyUrl/pay_code/alipayMobile';
        //服务器异步通知页面路径 //必填，不能修改
        $request->setNotifyUrl($notify_url);
        $shop_info = settings();
        $store_name = $shop_info['site_name'] . '订单';
        $bizParameters = [
            'subject'         => $store_name,
            'body'            => '商品订单',
            'out_trade_no'    => $order['order_sn'],
            'total_amount'    => $order['order_amount'],// 单位元
            'timeout_express' => '5m',// 单位转换成分钟
            'product_code'    => 'QUICK_MSECURITY_PAY',
        ];
        $request->setBizContent(json_encode($bizParameters));
        return  $this->aopClient->pageExecute($request,'GET');
    }

    /**
     * 服务器点对点响应操作给支付接口方调用
     *
     */
    function response()
    {
        $path = Env::get('runtime_path') . "/alipay/";
        makeDir($path);
        $fp = fopen($path. date('Y-m-d') . '.txt', "a");
        flock($fp, LOCK_EX);
        fwrite($fp, "记录时间：" . strftime("%Y-%m-%d %H:%M:%S", time()) . "\n" . json_encode($_POST) . "\n\n");
        flock($fp, LOCK_UN);
        fclose($fp);

        //验签
        $res = $this->aopClient->rsaCheckV1($_POST,'','RSA2');
        if(!$res){
            echo 'fail';die;
        }
        if ($_POST['refund_status'] == 'REFUND_SUCCESS'){
            echo "success"; // 退款回调不作处理，告诉支付宝处理成功
            exit;
        }
        if ($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
            $data['order_sn'] =  $_POST['out_trade_no']; //商户订单号
            $data['total_fee'] = $_POST['total_amount'] * 100;//支付金额
            $data['transaction_id'] = $_POST['trade_no'];//支付宝交易号
            $res = (new UpdatePayModel)->update($data);
        }

        if ($res == true) {
            echo "success"; // 告诉支付宝处理成功
        } else {
            echo "fail"; //验证失败
        }
    }


    /**
     * 退款
     */
    public function refund($orderInfo = [])
    {
        require_once("aop/request/AlipayTradeRefundRequest.php");

        $request = new \AlipayTradeRefundRequest();
        $array = array(
            'out_trade_no' => $orderInfo['order_sn'],//订单支付时传入的商户订单号,不能和 trade_no同时为空。
            'trade_no' => $orderInfo['transaction_id'],//支付宝交易号，和商户订单号不能同时为空
            'refund_amount' => $orderInfo['refund_amount'],//需要退款的金额，该金额不能大于订单金额,单位为元，支持两位小数
            'refund_reason' => '退款',//退款的原因说明
            'out_request_no' => $orderInfo['order_sn'],//标识一次退款请求，同一笔交易多次退款需要保证唯一，如需部分退款，则此参数必传。
            'operator_id' => AUID,//商户的操作员编号

        );

        if ( $this->isCert == true){
            //是否校验自动下载的支付宝公钥证书，如果开启校验要保证支付宝根证书在有效期内
            $this->aopClient->isCheckAlipayPublicCert = true;
            //调用getCertSN获取证书序列号
            $this->aopClient->appCertSN = $this->aopClient->getCertSN($this->alipay_config['alipay_app_cert']);
            //调用getRootCertSN获取支付宝根证书序列号
            $this->aopClient->alipayRootCertSN = $this->aopClient->getRootCertSN($this->alipay_config['alipay_root_cert']);
        }
        
        $list = json_encode($array);
        $request->setBizContent($list);
        $result = $this->aopClient->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;

        if (!empty($resultCode) && $resultCode == 10000) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * 单笔转帐到支付宝用户
     */
    public function oneTransfer($transferInfo = [])
    {
        $webSite = explode('|',$this->alipay_config['website']);
        if (in_array($_SERVER['SERVER_NAME'],$webSite) == false){
            return false;
        }
        if ($this->isCert == false){//必须使用证书模式才能操作
            return false;
        }
        require_once("aop/request/AlipayFundTransUniTransferRequest.php");

        //调用getCertSN获取证书序列号
        $this->aopClient->appCertSN = $this->aopClient->getCertSN($this->alipay_config['alipay_app_cert']);
        //调用getRootCertSN获取支付宝根证书序列号
        $this->aopClient->alipayRootCertSN = $this->aopClient->getRootCertSN($this->alipay_config['alipay_root_cert']);

        //是否校验自动下载的支付宝公钥证书，如果开启校验要保证支付宝根证书在有效期内
        $this->aopClient->isCheckAlipayPublicCert = true;

        $request = new \AlipayFundTransUniTransferRequest();
        $array = [
            'out_biz_no' => $transferInfo['order_sn'],//商家侧唯一订单号，由商家自定义。对于不同转账请求，商家需保证该订单号在自身系统唯一。
            'trans_amount' => $transferInfo['arrival_money'],//订单总金额，单位为元，精确到小数点后两位，
            'product_code' => 'TRANS_ACCOUNT_NO_PWD',//业务产品码，单笔无密转账到支付宝账户固定为:TRANS_ACCOUNT_NO_PWD；
            'biz_scene' => 'DIRECT_TRANSFER',//描述特定的业务场景，可传的参数如下：DIRECT_TRANSFER：
            'order_title'=> '提现到帐',//转账标题
            'payee_info'=> [
                'identity'=> $transferInfo['alipay_account'],//参与方的唯一标识,支付宝帐号
                'identity_type'=> 'ALIPAY_LOGON_ID',//支付宝登录号，支持邮箱和手机号格式
                'name'=>$transferInfo['alipay_name'],//参与方真实姓名，如果非空，将校验收款支付宝账号姓名一致性。当identity_type=ALIPAY_LOGON_ID时，本字段必填
            ],
            'remark'=>'单笔转账'
        ];
        $request->setBizContent(json_encode($array));
        $result = $this->aopClient->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            return true;
        }
        return $result->$responseNode;
    }
}