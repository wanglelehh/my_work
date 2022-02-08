<?php
namespace app\distribution\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 分销身份表
/*------------------------------------------------------ */
class DividendAwardModel extends BaseModel
{
	protected $table = 'distribution_dividend_award';
	public  $pk = 'award_id';
	protected static $mkey = 'distribution_dividend_award_list';
	
	 /*------------------------------------------------------ */
	//-- 清除缓存
	/*------------------------------------------------------ */ 
	public function cleanMemcache(){
		Cache::rm(self::$mkey);
	}
	/*------------------------------------------------------ */
	//-- 获取列表
	/*------------------------------------------------------ */ 
	public  function getRows(){
		$data = Cache::get(self::$mkey);
		if (empty($data) == false){
			return $data;
		}
		$rows = $this->field('*,award_id as id,award_name as name')->order('award_id ASC')->select()->toArray();		
		foreach ($rows as $row){
			$data[$row['award_id']] = $row;
		}
		Cache::set(self::$mkey,$data,600);
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 获取角色信息
	/*------------------------------------------------------ */ 
	public function info($award_id,$returnName = false){
		$rows = $this->getRows();
		if ($returnName == true){
			return $rows[$award_id]['award_name'];
		}
		return $rows[$award_id];
	}
    /*------------------------------------------------------ */
    //-- 获取当前用户参与的奖项
    //-- $role_id int 分销身份ID
    /*------------------------------------------------------ */
    public function getJoinReward($role_id){
        $rewardList = $this->getRows();
        $list = [];
        foreach ($rewardList as $reward){
            $limit_role = explode(',',$reward['limit_role']);
            if (in_array($role_id,$limit_role)){
                $list[] = ['id'=>$reward['award_id'],'text'=>$reward['award_name']];
            }
        }
        return $list;
    }
}
