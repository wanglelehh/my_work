<?php

namespace app\channel\model;
use app\BaseModel;
use think\facade\Cache;

//*------------------------------------------------------ */
//-- 奖励设置表
/*------------------------------------------------------ */
class RewardModel extends BaseModel
{
	protected $table = 'channel_reward';
	public  $pk = 'id';
    public  $mkey= 'channel_reward_list';
    public  $rewardList = ['tzsjjl'=>'直推升级奖励','tzsjjlb'=>'市场补贴','purchase'=>'出货收益','cyj'=>'创业金','yfh'=>'月分红','qyj'=>'区域奖'];
    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache(){
        Cache::rm($this->mkey);
    }
    /*------------------------------------------------------ */
    //-- 获取代理参与的奖项
    //-- $proxy_id int 代理层级
    //-- $region_proxy int 区域代理
    /*------------------------------------------------------ */
    public function getJoinReward($proxy_id,$region_proxy){
        $_rewardList = $this->getList();
        foreach ($this->rewardList as $key=>$reward){
            if ($key == 'qyj'){
                if ($region_proxy < 1){
                    continue;
                }
            }elseif (isset($_rewardList[$key][$proxy_id]['is_join'])){
                if ($_rewardList[$key][$proxy_id]['is_join'] == 0){
                    continue;
                }
            }
            $list[] = ['id'=>$key,'text'=>$reward];
        }
        return $list;
    }
    /*------------------------------------------------------ */
    //-- 获取所有奖项
    /*------------------------------------------------------ */
    public function getList(){
        $list = Cache::get($this->mkey);
        if (empty($list) == false){
            return $list;
        }
        $rows = $this->select();
        $list = [];
        foreach ($rows as $row){
            $info = json_decode($row['info'],true);
            $info['ext_count'] = $row['ext_count'];
            $list[$row['reward_type']] = $info;
        }
        Cache::set($this->mkey,$list,600);
        return $list;
    }

    /*------------------------------------------------------ */
    //-- 保存奖项
    /*------------------------------------------------------ */
    public function _save($reward_type,$info){
        $id = $this->where('reward_type',$reward_type)->value('id');
        if (empty($info['ext_count']) == false){
            if ($id > 0) {
                $data['ext_count'] = ['INC', $info['ext_count']];
            }else{
                $data['ext_count'] = $info['ext_count'];
            }
            unset($info['ext_count']);
        }
        $data['info'] = json_encode($info,JSON_UNESCAPED_UNICODE);
        if ($id > 0){
            $res = $this->where('id',$id)->update($data);
        }else{
            $data['reward_type'] = $reward_type;
            $res = $this->save($data);
        }
        if ($res < 1) return false;
        $this->cleanMemcache();
        return true;
    }
    public function RewardModel(){

    }
}
