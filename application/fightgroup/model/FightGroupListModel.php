<?php

namespace app\fightgroup\model;

use app\BaseModel;
use think\facade\Cache;
use app\shop\model\OrderModel;
use think\Db;

//*------------------------------------------------------ */
//-- 拼团列表
/*------------------------------------------------------ */

class FightGroupListModel extends BaseModel
{
    protected $table = 'fightgroup_list';
    public $pk = 'gid';
    protected $mkey = 'fightgroup_list_mkey';
    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache($gid = 0)
    {
        Cache::rm($this->mkey . $gid);
    }
    /*------------------------------------------------------ */
    //-- 获取拼团信息
    /*------------------------------------------------------ */
    public function info($gid)
    {
        $fgInfo = Cache::get($this->mkey . $gid);
        $OrderModel = new OrderModel;
        if (empty($fgInfo)) {
            $fgInfo = $this->where('gid', $gid)->find();
            if (empty($fgInfo) == true) return array();
            $fgInfo = $fgInfo->toArray();
            $fgInfo['_fail_time'] = date('Y-m-d H:i:s',$fgInfo['fail_time']);
            $where[] = ['o.order_type', '=', 2];
            $where[] = ['o.pid', '=', $gid];
            $where[] = ['o.order_status', 'in', [0, 1]];
            $fgInfo['order'] = $OrderModel->alias('o')->join("users u", "o.user_id=u.user_id", 'left')->where($where)->field('o.order_id,o.user_id,o.order_status,u.nick_name,u.headimgurl')->select()->toArray();
            $fgInfo['order_count'] = count($fgInfo['order']);
            $fgInfo['order_success_num'] = 0;
            foreach ($fgInfo['order'] as $order) {
                if ($order['order_status'] == 1) {
                    $fgInfo['order_success_num'] += 1;
                }
            }
            $fgInfo['last_num'] = $fgInfo['success_num'] - $fgInfo['order_success_num'];
            Cache::set($this->mkey . $gid, $fgInfo, 3);
        }

        $config = config('config.');
        $upData = [];
        if ($fgInfo['status'] == $config['FG_WAITPAY']) {//刚创建拼团待支付
            if ($fgInfo['fail_time'] < time() || $fgInfo['order_count'] < 1) {//超时取消||订单取消
                $fgInfo['status'] = $upData['status'] = $config['FG_FAIL'];
            } elseif ($fgInfo['order_success_num'] > 0) {
                $fgInfo['status'] = $upData['status'] = $config['FG_DOING'];
            }
        } elseif ($fgInfo['status'] ==  $config['FG_DOING'] || $fgInfo['status'] ==  $config['FG_FULL']) {//拼团中&满员
            if ($fgInfo['order_success_num'] >= $fgInfo['success_num']) {//成团处理
                $fgInfo['status'] = $upData['status'] = $config['FG_SEUCCESS'];
                $upData['success_time'] = time();
            } elseif ($fgInfo['order_count'] >= $fgInfo['success_num']) {//满员处理
                $fgInfo['status'] = $upData['status'] = $config['FG_FULL'];
            } elseif ($fgInfo['status'] == $config['FG_FULL'] && $fgInfo['order_count'] < $fgInfo['success_num']) {//满员状态，处理
                $fgInfo['status'] = $upData['status'] = $config['FG_DOING'];
            } elseif ($fgInfo['fail_time'] < time()) {//超时取消
                $fgInfo['status'] = $upData['status'] =$config['FG_FAIL'];
            }
        }
        if (empty($upData) == false) {
            $res = true;
            if ($upData['status'] == $config['FG_FAIL']) {//拼团失败更新订单
                $owhere[] = ['order_type', '=', 2];
                $owhere[] = ['pid', '=', $gid];
                $owhere[] = ['order_status', 'in', [0, 1]];
                $orderids = $OrderModel->where($owhere)->column('order_id');
                foreach ($orderids as $order_id) {
                    $orderUp['order_id'] = $order_id;
                    $orderUp['order_status'] = config('config.OS_CANCELED');//取消订单
                    $orderUp['cancel_time'] = time();
                    $_res = $OrderModel->upInfo($orderUp, 'sys');
                    if ($_res !== true){
                        $res = false;
                    }
                }
            } elseif ($upData['status'] == $config['FG_SEUCCESS']) {//拼团成功
                $oWhere = [];
                $oWhere[] = ['order_type', '=', 2];
                $oWhere[] = ['pid', '=', $gid];
                $oWhere[] = ['order_status', '=', 1];
                $_res = $OrderModel->where($oWhere)->update(['is_success' => 1]);
                if ($_res < 1) {
                    $res = false;
                }
            }
            if ($res == true){
                $this->where('gid', $gid)->update($upData);
                Cache::set($this->mkey . $gid, $fgInfo, 10);
            }
        }
        return $fgInfo;
    }

    /*------------------------------------------------------ */
    //-- 取消失效拼团
    /*------------------------------------------------------ */
    public function evalFail($user_id = 0)
    {
        $time = time();
        if ($user_id > 0) {
            $where[] = ['head_user_id', '=', $user_id];
        }
        $where[] = ['status', '<', 3];
        $where[] = ['fail_time', '<', $time];
        $gids = $this->where($where)->column('gid');
        if (empty($gids)) return true;
        foreach ($gids as $gid) {
            $this->info($gid);
        }
        return true;
    }
}
