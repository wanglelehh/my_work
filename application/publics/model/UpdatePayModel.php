<?php

namespace app\publics\model;

//*------------------------------------------------------ */
//-- 支付处理
/*------------------------------------------------------ */

class UpdatePayModel
{
    /**
     * 更新相关支付状态
     */
    public function update($data)
    {
        $order_sn = $data['order_sn'];
        if (stripos($order_sn, 'wsrecharge') !== false) {//微商代理在线充值
            if (strlen($order_sn) > 22) {
                $order_sn = substr($order_sn, 0, 22);
            }
            $RechargeLogModel = new \app\channel\model\RechargeLogModel();
            $orderInfo = $RechargeLogModel->where('order_sn', "$order_sn")->field('order_id,order_amount,user_id,status')->find();
            if (empty($orderInfo)) return false;
            $orderInfo = $orderInfo->toArray();
            if ($orderInfo['status'] >= 8) return true;
            if ((string)($orderInfo['order_amount'] * 100) != (string)$data['total_fee']) {
                return false; //验证失败
            }
            return $RechargeLogModel->updatePay($orderInfo['order_id'],'在线支付成功',$data["transaction_id"]);// 修改订单支付状态
        }elseif (stripos($order_sn, 'ws') !== false) {//微商订单在线支付
            if (strlen($order_sn) > 15) {
                $order_sn = substr($order_sn, 0, 15);
            }
            $OrderModel = new \app\channel\model\OrderModel();
            $orderInfo = $OrderModel->where('order_sn', "$order_sn")->field('order_id,order_amount,pay_status')->find();
            if (empty($orderInfo)) {
                return false;
            }
            if ($orderInfo['pay_status'] == 1) return true;
            if ((string)($orderInfo['order_amount'] * 100) != (string)$data['total_fee']) {
                return false; //验证失败
            }
            return $OrderModel->updatePay(array('order_id' => $orderInfo['order_id'], 'money_paid' => $orderInfo['order_amount'], 'transaction_id' => $data["transaction_id"]), '支付成功，流水号：' . $data["transaction_id"]);// 修改订单支付状态

        }elseif (stripos($order_sn, 'recharge') !== false) {//用户在线充值
            if (strlen($order_sn) > 20) {
                $order_sn = substr($order_sn, 0, 20);
            }
            $RechargeLogModel = new \app\member\model\RechargeLogModel();
            $orderInfo = $RechargeLogModel->where('order_sn', "$order_sn")->field('order_id,order_amount,user_id,status')->find();
            if (empty($orderInfo)) return false;
            $orderInfo = $orderInfo->toArray();
            if ($orderInfo['status'] == 9) return true;
            if ((string)($orderInfo['order_amount'] * 100) != (string)$data['total_fee']) {
                return false; //验证失败
            }
            $orderInfo['transaction_id'] = $data["transaction_id"];
            return $RechargeLogModel->updatePay($orderInfo);// 修改订单支付状态
        } else {
            if (strlen($order_sn) > 13) {
                $order_sn = substr($order_sn, 0, 13);
            }
            $OrderModel = new \app\shop\model\OrderModel();
            $orderInfo = $OrderModel->where('order_sn', "$order_sn")->field('order_id,order_amount,pay_status')->find();
            if (empty($orderInfo)) {
                return false;
            }
            if ($orderInfo['pay_status'] == 1) return true;
            if ((string)($orderInfo['order_amount'] * 100) != (string)$data['total_fee']) {
                return false; //验证失败
            }
            return $OrderModel->updatePay(array('order_id' => $orderInfo['order_id'], 'money_paid' => $orderInfo['order_amount'], 'transaction_id' => $data["transaction_id"]), '支付成功，流水号：' . $data["transaction_id"]);// 修改订单支付状态
        }
        return false;
    }
}
