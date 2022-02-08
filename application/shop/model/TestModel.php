<?php

namespace app\shop\model;
use app\BaseModel;

//*------------------------------------------------------ */
//-- 测试使用
/*------------------------------------------------------ */

class TestModel extends BaseModel
{
    //测试异步
    public function asynRunTest($data){
        sleep(3);
        $rand = rand(1,6);
        if ($rand == 6){
             return false;//失败
        }
        if (in_array($rand,[3]) && empty($data['runag'])){
            sfsfdf();//致命错误
            return false;
        }
        $cache = \think\facade\Cache::init();
        $redis = $cache->handler();
        $data['run_time'] = date('Y-m-d H:i:s');
        $redis->lPush(config('cache.prefix').'asynRunTestLog',json_encode($data));
        return true;
    }
}