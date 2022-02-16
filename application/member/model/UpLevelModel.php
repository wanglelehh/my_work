<?php

namespace app\member\model;
use app\BaseModel;
use app\shop\model\OrderModel;
use think\Db;
use app\shop\model\OrderGoodsModel as ShopOrderGoodsModel;
use app\channel\model\OrderGoodsModel as ChannelOrderGoodsModel;
//*------------------------------------------------------ */
//-- 升级处理
/*------------------------------------------------------ */

class UpLevelModel extends BaseModel
{
    /*------------------------------------------------------ */
    //-- 执行升级方案
    //-- $orderInfo array 订单信息
    /*------------------------------------------------------ */
    public function evalLevelUp(&$orderInfo)
    {
        //执行分销身份升级处理
        $roleList = (new RoleModel)->getRows();
        $oldFun = '';
        $DividendInfo = settings('DividendInfo');
        $UsersBindSuperiorModel = new UsersBindSuperiorModel();
        $user_id = $orderInfo['user_id'];
        if (empty($orderInfo['channel_order']) == false){
            $goodsList = (new ChannelOrderGoodsModel)->where('order_id', $orderInfo['order_id'])->select();
        }else{
            $goodsList = (new ShopOrderGoodsModel)->where('order_id', $orderInfo['order_id'])->select();
        }
        $UsersModel = new UsersModel();
        $OrderModel = new OrderModel();
        do {
            $usersInfo = $UsersModel->info($user_id);//获取会员信息

            $userRoleLevel = $roleList[$usersInfo['role_id']]['level'];//获取当前会员身份等级
            $stats = [];
            $stats['subRoleCount'] = [];
            //汇总直推身份的会员数
//            foreach ($roleList as $role) {
//                $where = [];
//                $where['pid'] = $user_id;
//                $where['role_id'] = $role['role_id'];
//                $stats['subRoleCount'][$role['role_id']] = $UsersModel->where($where)->count();
//            }
            //汇总直推身份的会员数end

            //团队总人数（包含自己）
            $where = [];
            $where[] = ['', 'exp', Db::raw("FIND_IN_SET('" . $user_id . "',superior)")];
            $stats['teamCount'] = $UsersBindSuperiorModel->where($where)->count();
            //团队总人数（包含自己）end


            //团队业绩（包含自己）
            $where = [];
            $getIds=$UsersModel->teamUid($user_id);
            $where[]=['user_id','in',$getIds];
            $where[]=['order_status','=',1];
            $where[]=['is_type','=',1];
            $stats['teamConsume'] = $OrderModel->where($where)->sum('order_amount');
            //团队业绩（包含自己）end

//            trace($stats,'debug');
            $upRole = [];
            $roleleve=10;
            foreach ($roleList as $role) {
                if ($role['level'] >= $userRoleLevel ) {//当前分销身份低于等于用户现身份，跳过
                    continue;
                }
                if(!empty($upRole)){
                    if ($role['level'] >= $upRole['level'] ) {//已经获取一个身份，重新循环的身份低于已有的，跳过
                        continue;
                    }
                }
                if ($role['is_auto'] == 9) {//手动调整,跳过
                    continue;
                }

                $upLeveValue = $role['upleve_value'];

                //购买会员额外处理
                if ($user_id == $orderInfo['user_id']) {
                    //指定购买商品
                    if (empty($upLeveValue['buy_goods']) == false) {
                        $checkGoodsLimit = [];
                        foreach ($upLeveValue['buy_goods'] as $gid=>$num){
                            $checkGoodsLimit[$gid] = 0;
                        }

                        foreach ($goodsList as $goods) {
                            if (empty($upLeveValue['buy_goods'][$goods['goods_id']]) == false) {
                                if ($goods['goods_number'] >= $upLeveValue['buy_goods'][$goods['goods_id']]) {
                                    $checkGoodsLimit[$goods['goods_id']] = 1;
                                }
                            }
                        }
                        $is_up = true;
                        foreach ($checkGoodsLimit as $is_ok) {
                            if ($is_ok == 1) {
                                $is_up = true;
                                break;
                            }
                            $is_up = false;
                        }
                        if ($is_up == true) {
                            $upRole = $role;
                            if ($DividendInfo['level_up_type'] == 1){
                                continue;//可跨级升级时调用
                            }
                        }
                    }
                    if (empty($upRole) == false) {
                        $orderInfo['d_type'] = 'role_order';
                        continue;
                    }
                }
                //购买会员额外处理end

                $fun = 'distribution\base\\'.$role['upleve_function'];
                if ($oldFun != $fun) {
                    $oldFun = $fun;
                    $Class = new $fun();
                }

                $arr=['1'=>'达到直推条件','2'=>'购买身份产品','3'=>'团队业绩达标'];
                $res = $Class->judgeIsUp($usersInfo,$orderInfo, $stats, $role);//判断是否能升级
                if ($res == false) {//当前会员不执行升级，终止
                    if ($DividendInfo['level_up_type'] == 1){
                        continue;//可跨级升级时调用
                    }
                    break;
                }
                if($res==1){
                    $level=$userRoleLevel-$role['level'];  //通过直推条件升级，必须逐级升
                    if($level!=1) continue;
                }
                $uplog=$arr[$res].',';
                $upRole = $role;
                if ($DividendInfo['level_up_type'] == 0) {//逐级升时调用
                    break;//跳出循环进行升级操作
                }
            }

            if (empty($upRole) == false) {
                if($upRole['role_id']>19){ //是城市合伙人级别或以上
                    $findorder=$OrderModel->where('user_id',$user_id)->where('is_up',1)->find();
                    if(empty($findorder)){
                        $OrderModel->where('order_id',$orderInfo['order_id'])->update(['is_up'=>1]);
                    }
                }
                $upData['last_up_role_time'] = time();
                $upData['role_id'] = $upRole['role_id'];
                $upData['is_channel'] = 0;
                if ($roleList[$upData['role_id']]['role_type'] == 1){
                    $upData['is_channel'] = 1;//设为代理
                }
                $res = $UsersModel->upInfo($user_id, $upData);
                if ($res < 1) {
                    return false;
                }
                $log_info = '';
                $log_info .=$uplog;
                if ($orderInfo['d_type'] == 'role_order' && $user_id == $orderInfo['user_id']) {
//                    $log_info .= '购买指定商品，';
                }
                $log_info .= '【' .  $roleList[$usersInfo['role_id']]['role_name'] . '】升级为【' . $upRole['role_name'] . '】';
                $UsersModel->_upLog($user_id, $log_info,$upRole['role_id'], $usersInfo['role_id']);
            }
            $user_id = $usersInfo['pid'];//继续处理上级id
        } while ($user_id > 0);
        return true;
    }

}
