<?php
/*------------------------------------------------------ */
//-- 支付相关
/*------------------------------------------------------ */

namespace app\publics\controller;
use think\Db;

use app\BaseController;
use app\mainadmin\model\PaymentModel;

use app\member\model\RechargeLogModel;
use app\member\model\AccountLogModel;
use app\member\model\AccountModel;

use app\shop\model\OrderModel;
use app\distribution\model\RoleOrderModel;
use app\weixin\model\WeiXinUsersModel;


class Payment extends BaseController
{
    public $payment; //  具体的支付类
    public $pay_code; //  具体的支付code

    /**
     * 析构流函数
     */
    public function __construct()
    {
        parent::__construct();

        // 获取支付类型
        $this->pay_code = input('pay_code');
        unset($_GET['pay_code']); // 用完之后删除, 以免进入签名判断里面去 导致错误

        // 获取通知的数据
        if (empty($this->pay_code)) {
            exit('pay_code 不能为空');
        }
        if (in_array($this->pay_code,['balance','integral','offline']) == false) {
            define('SITE_URL',config('config.host_path'));
            // 导入具体的支付类文件
            $code = str_replace('/', '\\', "/payment/{$this->pay_code}/{$this->pay_code}");
            $this->payment = new $code();
        }

    }

    /**
     * 支付异步回调处理
     */
    public function notifyUrl()
    {
        $this->payment->response();
        exit();
    }


    /**
     * 支付同步回调地址
     * @return mixed
     */
    public function returnUrl()
    {
        $result = $this->payment->respond2(); // $result['order_sn'] = '201512241425288593';

        if (stripos($result['order_sn'], 'recharge') !== false) {
            $RechargeLogModel = new RechargeLogModel();
            $orderInfo = $RechargeLogModel->where("order_sn", $result['order_sn'])->find();
            $this->assign('orderInfo', $orderInfo);
            if ($result['status'] == 9)
                return $this->fetch('recharge_success');
            else
                return $this->fetch('recharge_error');
        }
        $this->assign('title','支付结果');
        $OrderModel = new OrderModel();
        $orderInfo = $OrderModel->where("order_sn", $result['order_sn'])->find();
        $this->assign('orderInfo', $orderInfo);
        if ($result['status'] == 1)
            return $this->fetch('success');
        else
            return $this->fetch('error');
    }

}
