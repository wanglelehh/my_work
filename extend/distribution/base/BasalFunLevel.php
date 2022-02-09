<?php
namespace distribution\base;

/*------------------------------------------------------ */
//-- 基础升级方案
//-- @author iqgmy
/*------------------------------------------------------ */
use app\member\model\RoleModel;
use app\member\model\UsersModel;

$i = (isset($modules)) ? count($modules) : 0;
$modules[$i]["name"] = "基础升级方案";
$modules[$i]["explain"] = "基础升级方案.";
$modules[$i]["val"][] = ['name' => 'referral', 'text' => '直推', 'input' => 'sel_role', 'tip' => '个'];
//$modules[$i]["val"][] = ['name' => 'team_total', 'text' => '团队总人数', 'rule' => 'integer', 'tip' => '个,包括自己'];
$modules[$i]["val"][] = ['name' => 'team_consume', 'text' => '团队业绩', 'input' => 'number', 'rule' => 'ismoney', 'tip' => '元'];
$modules[$i]["val"][] = ['name' => 'any_product', 'text' => '购买身份产品', 'input' => 'any_product', 'rule' => 'any_product', 'tip' => '元'];


class BasalFunLevel
{
    /*------------------------------------------------------ */
    //-- 执行级别更新
    // $usersInfo array 会员信息
    // $orderInfo array 订单信息
    // $stats array 汇总信息
    // $role array 当前准备提升的分销身份
    /*------------------------------------------------------ */
    public function judgeIsUp(&$usersInfo, &$orderInfo, &$stats, &$role)
    {
        $stats['user_id']=$usersInfo['user_id'];
        trace($stats,'debug');
        $upLeveValue = $role['upleve_value'];
        //直推条件判断
        $status = false;
        if(!empty($upLeveValue['referral'])){
            foreach ($upLeveValue['referral'] as $rid => $val) {
                if ($val > 0) {//有设置值执行，没有设置即不限制
                    $count=$this->daiNum($rid,$usersInfo['user_id'],1);
                    if ($val > $count) {//设置的值大于当前直推身份人数，即不满足条件
                        $status = false;
                        break;
                    }
                    $status = true;
                }
            }
            if ($role['up_condition'] == 2 && $status == false) {//不满足，失败
                return false;
            }
            if ($role['up_condition'] == 1 && $status == true) {//只需满足任一条件即可升级
                return true;
            }
        }
        //直推条件判断end

        // 购买身份产品商品
        if(isset($upLeveValue['any_product'])){
            if ($usersInfo['user_id'] == $orderInfo['user_id'] && $upLeveValue['any_product'] == 1 && $orderInfo['is_type']==1) {
                if ($role['up_condition'] == 1) {//只需满足任一条件即可升级
                    return true;
                }
            }
        }
        // 购买身份产品商品end

        //团队总人数
//        if ($upLeveValue['team_total'] > 0){
//            if ($upLeveValue['team_total'] > $stats['teamCount']){
//                if ($role['up_condition'] == 2) {//不满足，失败
//                    return false;
//                }
//            }elseif ($role['up_condition'] == 1) {//只需满足任一条件即可升级
//                return true;
//            }
//        }
        //团队总人数end


        //团队业绩
        if ( isset($upLeveValue['team_consume']) && $upLeveValue['team_consume'] > 0 ){
            if ($upLeveValue['team_consume'] > $stats['teamConsume']){
                if ($role['up_condition'] == 2) {//不满足，失败
                    return false;
                }
            }elseif ($role['up_condition'] == 1) {//只需满足任一条件即可升级
                return true;
            }
        }
        //团队业绩end

        //单次消费(针对购买者)
//        if ($usersInfo['user_id'] == $orderInfo['user_id'] && empty($upLeveValue['team_consume']) == false) {
//            if ($upLeveValue['team_consume'] > $orderInfo['order_amount']) {
//                if ($role['up_condition'] == 2) {//不满足，失败
//                    return false;
//                }
//            }elseif ($role['up_condition'] == 1) {//只需满足任一条件即可升级
//                return true;
//            }
//        }
        //单次消费end


        return false;
    }

    public function daiNum($rid,$user_id,$num = 1)
    {
        //直推
        $UsersModel = new UsersModel();
        $DividendRoleModel = new RoleModel();
        $level = $DividendRoleModel->where('role_id',$rid)->value('level');
        $role_ids = $DividendRoleModel->where('level','<=',$level)->column('role_id');
        if ($num == 1){
            $where = [];
            $where[] = ['role_id','in',$role_ids];
            $where[] = ['pid','=',$user_id];
            $count = $UsersModel->where($where)->count('user_id');
        }else{
            $field = $num == 2 ? 'b.pid_b' : 'b.pid_c';
            $where = [];
            $where[] = ['u.role_id','in',$role_ids];
            $where[] = [$field,'=',$user_id];
            $count = $UsersModel->alias('u')->leftJoin('users_bind_superior b','b.user_id = u.user_id')->where($where)->count('u.user_id');
        }
        return $count;
    }
}

?>