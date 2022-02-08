<?php
/*------------------------------------------------------ */
//-- 提成处理
//-- Author: iqgmy
/*------------------------------------------------------ */

namespace distribution\base;

use app\BaseModel;
use app\member\model\UsersModel;
use app\shop\model\OrderModel;
use app\shop\model\OrderGoodsModel;
use app\distribution\model\DividendAwardModel;
use app\member\model\RoleModel;
use app\member\model\UpLevelModel;

class Dividend extends BaseModel
{
    public $UsersModel;

    public function __construct($Model)
    {
        parent::__construct();
        $this->UsersModel = new UsersModel();
        $this->Model = $Model;
    }

    /*------------------------------------------------------ */
    //-- 计算提成并记录或更新
    //-- $orderInfo array 订单数据
    //-- $type string 操作类型
    //-- $status int 分佣状态，操作类型为add时，根据传值设置默认状态
    //-- return bool 如果$type为add或订单为身份订单则返回数组
    /*------------------------------------------------------ */
    public function _eval(&$orderInfo, $type = '', $status = 0)
    {

        if ($orderInfo['is_split'] > 0) return true;//需要拆单的不执行
        $upData = [];//更新分佣记录状态
        $OrderModel = new OrderModel();
        $order_operating = '';
        $send_msg = false;
        $UpLevelModel = new UpLevelModel();
        //先计算佣金
        if ($type == 'add' || $type == 'change') {//写入分佣，普通订单下单时执行
            $goodsList = (new OrderGoodsModel)->where('order_id', $orderInfo['order_id'])->select()->toArray();
            $upData = $this->saveLog($orderInfo, $goodsList, $status);//佣金计算
            if (is_array($upData) == false) return false;
            if ($status == $OrderModel->config['DD_PAYED']) {
                $res = $UpLevelModel->evalLevelUp($orderInfo);//升级处理
                if ($res == false) return false;
                if ($orderInfo['is_dividend'] == 0) {//第一次生成时才发送模板消息
                    $this->Model->sendMsg('pay', $orderInfo['order_id']);//支付模板消息
                }
            }
            return $upData;//返回数组
        }

        if ($type == 'pay') {//订单支付成功
            $res = $UpLevelModel->evalLevelUp($orderInfo);//升级处理
            if ($res == false) return false;
            $upData['status'] = $OrderModel->config['DD_PAYED'];
            $send_msg = true;
        } elseif ($type == 'cancel') {//订单取消
            $upData['status'] = $OrderModel->config['DD_CANCELED'];
            $order_operating = '订单取消';
            $send_msg = true;
        } elseif ($type == 'unpayed') {//未付款
            if ($orderInfo['order_status'] == $OrderModel->config['OS_CANCELED']) {
                $upData['status'] = $OrderModel->config['DD_CANCELED'];
            } elseif ($orderInfo['order_status'] == $OrderModel->config['OS_RETURNED']) {
                $upData['status'] = $OrderModel->config['DD_RETURNED'];
            } else {
                $upData['status'] = $OrderModel->config['DD_UNCONFIRMED'];
            }
        } elseif ($type == 'shipping') {//发货
            $upData['status'] = $OrderModel->config['DD_SHIPPED'];
        } elseif ($type == 'unshipping') {//未发货
            $upData['status'] = $OrderModel->config['DD_PAYED'];
        } elseif ($type == 'sign') {//签收
            $upData['status'] = $OrderModel->config['DD_SIGN'];
        } elseif ($type == 'unsign') {//撤销签收
            return $this->Model->returnArrival($orderInfo['order_id'], 'unsign', $orderInfo['user_id']);
        } elseif ($type == 'returned') {//退货
            return $this->Model->returnArrival($orderInfo['order_id'], 'returned', $orderInfo['user_id']);
        }

        if (empty($upData) == false) {//更新分佣状态
            $upWhere[] = ['order_id', '=', $orderInfo['order_id']];
            //如果已经返佣了。不能继续修改分佣状态 2020-9-10 11:36:03-xgh
            $upWhere[] = ['status','<>',$OrderModel->config['DD_DIVVIDEND']];
            $count = $this->Model->where($upWhere)->count();
            if ($count < 1) return true;//如果没有佣金记录不执行
            $upData['update_time'] = time();
            $res = $this->Model->where($upWhere)->update($upData);
            if ($res < 1) return false;
        }

        if ($send_msg == true) {
            $this->Model->sendMsg($type, $orderInfo['order_id'], $order_operating);//发送模板消息
        }

        if ($type == 'sign') {//签收,执行佣金到帐
            $shop_after_sale_limit = settings('shop_after_sale_limit');
            if ($shop_after_sale_limit == 0) {
                return $this->Model->evalArrival($orderInfo['order_id'], 'order');
            }
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 计算提成并记录或更新
    /*------------------------------------------------------ */
    public function saveLog(&$orderInfo, &$goodsList, $status = 0)
    {
        $returnArr['dividend_amount'] = 0;
        $awardList = (new DividendAwardModel)->order('award_type DESC')->select();//获取全部奖项目,按类型倒序，先处理管理奖
        if (empty($awardList)) return $returnArr;

        $nowLevel = 0;//当前处理级别
        $nowLevelOrdinary = [];//普通分销当前处理级别，普通分销有逐级计算和无限级计算，如果无限级，不满条件将一直最后的上级
        $assignAwardNum = [];//记录已分出去的管理奖金额
        $assignAwardUser = [];//记录已领管理奖的用户

        $buyUserInfo = $this->UsersModel->info($orderInfo['user_id']);//获取购买会员信息



        //获取旧的分佣记录的分佣者的身份
        $userDividendRole = [];
        if ($orderInfo['is_dividend'] > 0){
            $delWhere[] = ['order_id', '=', $orderInfo['order_id']];
            $delWhere[] = ['order_type', '=', $orderInfo['d_type']];
            $userDividendRole = $this->Model->where($delWhere)->column('role_id','dividend_uid');
            $this->Model->where($delWhere)->delete();//清理旧的提成记录，重新计算
        }
        //end

        $parentId = $buyUserInfo['pid'];//获取购买会员直属上级ID
        //普通订单奖项处理
        $roleList = (new RoleModel)->getRows();
        $lastRole = $roleList[$orderInfo['dividend_role_id']]['level'];//下单会员下单时身份级别

        if ($parentId < 1) return $returnArr;//没有上级不执行

        //参与分佣商品处理
        $dividend_goods_ids = [];//所有分佣商品id
        $dividend_goods = [];//所有分佣商品
        $dividend_goods_total = 0;//所有分佣商品金额
        foreach ($goodsList as $goods) {
            if ($goods['is_dividend'] == 1){
                $dividend_goods_ids[] = $goods['goods_id'];
                $diy_discount = 0;
                if($orderInfo['diy_discount'] > 0){//后台改价优惠
                    $total_price = $goods['sale_price'] * $goods['goods_number'];
                    $diy_discount = priceFormat($total_price / $orderInfo['goods_amount'] * $orderInfo['diy_discount']);
                }
                if ($goods['return_goods_number'] > 0){//有退货处理
                    $usd_bonus_pre = 0;
                    if ($goods['usd_bonus_price'] > 0){//有使用优惠券
                        $usd_bonus_pre = $goods['usd_bonus_price'] / $goods['goods_number'];
                    }
                    $goods['goods_number'] = $goods['goods_number'] - $goods['return_goods_number'];//购买数量减去已退货数量
                    $goods['usd_bonus_price'] = $goods['goods_number'] * $usd_bonus_pre;
                }
                $goods['goods_total'] = priceFormat($goods['sale_price'] * $goods['goods_number'] - $goods['usd_bonus_price'] - $diy_discount);//商品总价小计
                $dividend_goods_total += $goods['goods_total'];
                $dividend_goods[$goods['goods_id']] = $goods;
            }
        }//参与分佣商品处理end

        do {
            $nowLevel += 1;
            $userInfo = $this->UsersModel->info($parentId);//获取会员信息
            if (empty($userDividendRole) == false){
                $userInfo['role_id'] = $userDividendRole[$userInfo['user_id']];
                $userInfo['role'] = $roleList[$userInfo['role_id']];
            }
            $parentId = $userInfo['pid'];//优先记录下次循环用户ID
            foreach ($awardList as $key => $award) {
                $amount = 0;//用于计算佣金的金额
                if ($award['goods_limit'] == 3) {//身份分销的跳过
                    continue;
                }
                if (isset($nowLevelOrdinary[$key]) == false) {
                    $nowLevelOrdinary[$key] = 0;
                }
                $nowLevelOrdinary[$key] += 1;
                $awardValue = json_decode($award['award_value'], true);    //奖项内容
                if ($award['goods_limit'] == 1) {//购买任意分销商品
                    $amount = $dividend_goods_total;
                }elseif ($award['goods_limit'] == 2) {//购买指定分销商品
                    $award_limit_buy_goods = explode(',', $award['buy_goods_id']);
                    $isOk = false;
                    foreach ($award_limit_buy_goods as $goods_id) {
                        if (in_array($goods_id, $dividend_goods_ids) == true) {//限制商品存在购买中，成功跳出
                            $isOk = true;
                        }
                        $amount += $dividend_goods[$goods_id]['goods_total'];
                    }
                    if ($isOk == false) {//不满足购买限制，跳出
                        continue;
                    }
                }
                if ($amount <= 0){
                    continue;
                }

                //判断身份是否满足条件
                $limit_role = explode(',', $award['limit_role']);
                if (in_array($userInfo['role_id'], $limit_role) == false) {
                    if ($award['award_type'] == 1 && $award['ordinary_type'] == 1) {//普通分销奖，无限级计算时执行
                        $nowLevelOrdinary[$key] -= 1;
                    }
                    continue;
                }

                if ($award['award_type'] == 2) {//判断管理奖是否享受
                    if ($userInfo['role']['level'] >= $lastRole) {//上级身份低于下级身份或平级时跳出
                        continue;
                    }
                    if (empty($awardValue[$userInfo['role_id']])) {//没有找到相应奖项级别跳出
                        continue;
                    }
                    if (isset($assignAwardNum[$award['award_id']]) == false) {//未定义附值为0
                        $assignAwardNum[$award['award_id']] = 0;
                    }
                    $endAward = end($awardValue);//获取最后奖项
                    if ($assignAwardNum[$award['award_id']] >= $endAward['num']) {
                        unset($awardList[$key]);//管理奖已达最大分配值，终止，跳出
                        continue;
                    }
                    $awardVal = $awardValue[$userInfo['role_id']];//获取对应角色奖项
                    $lastRole = $userInfo['role']['level'];
                    $award_num = $awardVal['num'] - $assignAwardNum[$award['award_id']];//计算当前可分值
                    if ($award_num <= 0) {//已分完终止
                        unset($awardList[$key]);//移除已结束的奖项
                        continue;
                    }
                } else {
                    if ($award['award_type'] == 1 && $award['ordinary_type'] == 1) {//普通分销，无限级计算时，会员判断级别方式不一样
                        if (empty($awardValue[$nowLevelOrdinary[$key]])) {//没有找到相应奖项级别跳出，并移除奖项
                            unset($awardList[$key]);//移除奖项
                            continue;
                        }
                        $awardVal = $awardValue[$nowLevelOrdinary[$key]];
                    } else {
                        if (empty($awardValue[$nowLevel])) {//没有找到相应奖项级别跳出，并移除奖项
                            unset($awardList[$key]);//移除奖项
                            continue;
                        }
                        $awardVal = $awardValue[$nowLevel];
                    }
                }

                //执行奖项处理
                $inArr = [];
                if ($award['award_type'] == 1) {//普通分销奖
                    if ($awardVal['num_type'] == 'money') {//固定金额
                        $inArr['dividend_amount'] = $awardVal['num'];
                    } else {//订单百分比，扣除运费和退款后计算
                        $inArr['dividend_amount'] = priceFormat($amount / 100 * $awardVal['num']);
                    }
                } elseif ($award['award_type'] == 2) {//管理奖
                    $assignAwardUser[] = $userInfo['user_id'];
                    $assignAwardNum[$award['award_id']] += $award_num;
                    if ($awardVal['num_type'] == 'money') {//固定金额
                        $inArr['dividend_amount'] = $award_num;
                    } else {//订单百分比，扣除运费和退款后计算
                        $inArr['dividend_amount'] = priceFormat($amount / 100 * $awardVal['num']);
                    }
                }
                if ($inArr['dividend_amount'] > 0) {//佣金大于0执行
                    $returnArr['dividend_amount'] += $inArr['dividend_amount'];//计算总佣金
                    $inArr['order_type'] = $orderInfo['d_type'];
                    $inArr['status'] = $status;
                    $inArr['order_id'] = $orderInfo['order_id'];
                    $inArr['order_sn'] = $orderInfo['order_sn'];
                    $inArr['buy_uid'] = $orderInfo['user_id'];
                    $inArr['order_amount'] = $amount;
                    $inArr['dividend_uid'] = $userInfo['user_id'];
                    $inArr['role_id'] = $userInfo['role_id'];
                    $inArr['role_name'] = $userInfo['role']['role_name'];
                    $inArr['level'] = $nowLevel;
                    $inArr['award_id'] = $award['award_id'];
                    $inArr['award_name'] = $award['award_name'];
                    $inArr['level_award_name'] = $awardVal['name'];
                    $inArr['add_time'] = $inArr['update_time'] = time();
                    $res = $this->Model->create($inArr);
                    if ($res->log_id < 1) return false;
                }
            }

            if (empty($awardList) == true) {//没有奖项可分了，终止
                $parentId = 0;
            }
        } while ($parentId > 0);
        return $returnArr;
    }


}
