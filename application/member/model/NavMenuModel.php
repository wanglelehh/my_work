<?php
namespace app\member\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 会员中心导航菜单
/*------------------------------------------------------ */
class NavMenuModel extends BaseModel
{
	protected $table = 'users_nav_menu';
	public $pk = 'id';
    protected static $mkey = 'member_nav_menu_list';
    public $type = 1;
    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache($type = 1){
        Cache::rm(self::$mkey);
        if ($type) {
            Cache::rm(self::$mkey . '_' . $type);
        }
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    /*------------------------------------------------------ */
    public static function getRows($type = 1){
        $mkey = self::$mkey . '_' . $type;
        $rows = Cache::get($mkey);
        if (empty($rows) == false) return $rows;
        $where[] = ['status', '=', 1];
        $where[] = ['type', '=', $type];
        $rows = self::where($where)->order('sort_order DESC id ASC')->select()->toArray();
        Cache::set($mkey, $rows, 60);
		return $rows;
	}
}
