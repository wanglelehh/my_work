<?php
namespace app\member\model;
use app\BaseModel;
use think\Db;
use think\facade\Cache;

//*------------------------------------------------------ */
//-- 身份表
/*------------------------------------------------------ */
class RoleModel extends BaseModel
{
	protected $table = 'users_role_list';
	public  $pk = 'role_id';
	protected static $mkey = 'users_role_list';
	
	 /*------------------------------------------------------ */
	//-- 清除缓存
	/*------------------------------------------------------ */ 
	public function cleanMemcache(){
		Cache::rm(self::$mkey);
	}
	/*------------------------------------------------------ */
	//-- 获取列表
	/*------------------------------------------------------ */ 
	public  function getRows($role_type = -1){
        $rows = Cache::get(self::$mkey);
		if (empty($rows)){
		    $rows = $this->field('*,role_id as id,role_name as name')->order('level ASC')->select()->toArray();
            Cache::set(self::$mkey,$rows,600);
        }
		foreach ($rows as $row){
		    if ($role_type >= 0){
		        if ($row['role_type'] !== $role_type){
                    continue;
                }
            }
            $row['upleve_value'] = json_decode($row['upleve_value'],true);
			$data[$row['role_id']] = $row;
		}
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 获取角色信息
	/*------------------------------------------------------ */ 
	public function info($role_id,$returnName = false){
		$rows = $this->getRows();
		if ($returnName == true){
			return $rows[$role_id]['role_name'];
		}
		return $rows[$role_id];
	}


    /*------------------------------------------------------ */
    //-- 身份汇总
    /*------------------------------------------------------ */
    public function roleStatc(){
        $statc = Cache::get($this::$mkey . 'statc');
        if (empty($statc) == true){
            $where[] = ['role_id','>',0];
            $statc = (new UsersModel)->where($where)->group('role_id')->column('count(user_id) as count','role_id');
            Cache::set($this::$mkey . 'statc', $statc, 30);
        }
        $data['allNum'] = 0;
        foreach ($statc as $val){
            $data['allNum'] += $val;
        }
        $list = $this->getRows();
        foreach ($list as $row){
            $num = 0;
            if (empty($statc[$row['role_id']]) == false){
                $num = $statc[$row['role_id']];
            }
            $data['statc'][$row['role_id']]['num'] = $num;
            $data['statc'][$row['role_id']]['pre'] = priceFormat($num / $data['allNum'] * 100);
        }
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 获取可邀请层级
    //-- $role_id intval 邀请人层级
    //-- $invite_proxy_id int 邀请的层级
    /*------------------------------------------------------ */
    public function getInvitelist($role_id,$invite_role_id = 0){
        $rows = $this->getRows(1);
        $list = [];
        foreach ($rows as $key=>$row) {
            if ($invite_role_id > 0 && $invite_role_id != $row['role_id']){
                continue;
            }
            if ($row['is_auto'] == 9){//手动调整
                continue;
            }
            $data['value'] = $row['role_id'];
            $data['label'] = $row['role_name'];
            $data['limit_tip'] = '';
            $limit = [];
            $data['limit_tip'] = join('，',$limit);
            $list[] = $data;
        }
        return $list;
    }
    /*------------------------------------------------------ */
    //-- 获取可升级层级
    //-- $user_id intval 会员ID
    //-- $role_id intval 会员代理层级
    /*------------------------------------------------------ */
    public function getUpgradeList($user_id,$role_id){
        $roleList = $this->getRows();
        $myLevel = 0;
        if (empty($roleList[$role_id]) == false){
            $myLevel = $roleList[$role_id]['level'];
        }
        $data = [];
        $account = (new \app\channel\Model\WalletModel)->where('user_id',$user_id)->find();
        $referralTotal = (new UsersModel)->where('pid',$user_id)->group('role_id')->column('count(user_id)','role_id');
        $where = [];
        $where[] = ['', 'exp', Db::raw("FIND_IN_SET('" . $user_id . "',superior)")];
        $teamCount = (new UsersBindSuperiorModel)->where($where)->count();
        $goodsMolde = (new \app\shop\model\GoodsModel);
        foreach ($roleList as $role){
            if ($myLevel > 0){
                if ($role['level'] >= $myLevel){
                    continue;
                }
            }
            $arr = [];
            $arr['role_id'] = $role['role_id'];
            $arr['role_name'] = $role['role_name'];
            $arr['is_auto'] = $role['is_auto'];
            $arr['value'] = $role['role_id'];
            $arr['label'] = $role['role_name'];
            $arr['up_condition'] = $role['is_auto'] == 9 ? 9: $role['up_condition'];
            $arr['condition_list'] = [];
            if ($role['is_auto'] == 1){
                foreach ($role['upleve_value'] as $upKey=>$upleve_value){
                    if ($upKey == 'earnest_money'){ //保证金
                        if (empty($upleve_value)){
                            continue;
                        }
                        $condition = '保证金(当前数量/升级要求)：'.$account['earnest_money'].'/'.$upleve_value;
                        $arr['condition_list'][] = ['type'=>'earnest_money','text'=>$condition];
                    }elseif($upKey == 'referral'){
                        $roleLimit = [];
                        foreach ($upleve_value as $rdkey=>$referral){
                            if ($referral > 0){
                                $roleLimit[] = $roleList[$rdkey]['name'].'：'.intval($referralTotal[$rdkey]).'/'.$referral;
                            }
                        }
                        $condition = '直推会员(当前数量/升级要求)：'.join('，',$roleLimit);
                        $arr['condition_list'][] = ['type'=>'referral','text'=>$condition];
                    }elseif($upKey == 'team_total'){
                        if (empty($upleve_value)){
                            continue;
                        }
                        $arr['condition_list'][] = ['type'=>'team_total','text'=>'团队总数(当前数量/升级要求)：'.$teamCount.'/'.$role['upleve_value']['team_total']];
                    }elseif($upKey == 'one_consume'){
                        if (empty($upleve_value)){
                            continue;
                        }
                        $arr['condition_list'][] = ['type'=>'one_consume','text'=>'个人单次下单：￥'.$upleve_value.'元'];
                    }elseif($upKey == 'buy_goods'){
                        if (empty($upleve_value)){
                            continue;
                        }
                        $roleLimit = [];
                        foreach ($upleve_value as $goods_id=>$buy_num){
                            $goods_name = $goodsMolde->where('goods_id',$goods_id)->value('goods_name');
                            $roleLimit[] = $goods_name.' * '.$buy_num;
                        }
                        $condition = '购买指定商品：'.join('，',$roleLimit);
                        $arr['condition_list'][] = ['type'=>'buy_goods','text'=>$condition];
                    }

                }
            }
            $data[] = $arr;
        }
        $data = array_reverse($data);
        return $data;
    }
}
