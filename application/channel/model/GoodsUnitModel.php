<?php

namespace app\channel\model;

use app\BaseModel;
use think\facade\Cache;

//*------------------------------------------------------ */
//-- 商品单位
/*------------------------------------------------------ */

class GoodsUnitModel extends BaseModel
{
    protected $table = 'channel_goods_unit';
    public $pk = 'id';
    protected $mkey = 'channel_goods_unit_mkey';
    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache()
    {
        Cache::rm($this->mkey);
    }
    /*------------------------------------------------------ */
    //-- 获取分类列表
    /*------------------------------------------------------ */
    public function getRows()
    {
        //$list = Cache::get($this->mkey);
        if (empty($list) == true) {
            $rows = $this->order('id ASC')->select()->toArray();
            foreach ($rows as $row){
                $list[$row['id']] = $row;
            }
            Cache::set($this->mkey, $list, 3600);
        }
        return $list;
    }


}
