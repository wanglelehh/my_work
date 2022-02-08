<?php
namespace app\mainadmin\controller;

use app\AdminController;
use app\mainadmin\model\SettingsModel;
/**
 * 配置
 */
class Lzyl extends AdminController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize(){
        parent::initialize();
        $this->Model = new SettingsModel();
    }
    /*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index(){

        $this->assign("setting", $this->Model->getRows());
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 保存功能配置
    /*------------------------------------------------------ */
    public function save(){
        $set = input('post.setting');
        $res = $this->Model->editSave($set);
        if ($res == false) return $this->error();
        return $this->success('设置成功.');
    }
    /*------------------------------------------------------ */
    //-- 获取异步任务列表
    /*------------------------------------------------------ */
    public function getAsynRunList(){
        $cache = \think\facade\Cache::init();
        $redis = $cache->handler();
        $cachePrefix = config('cache.prefix');
        $keysdata = $redis->keys($cachePrefix.'*');
        $list = [];
        foreach ($keysdata as $key){
            if (!strstr($key,'asynRun') || !strstr($key,'Model')) {
                continue;
            }
            if (strstr($key,'ARunLook')){
                continue;
            }
            $key = str_replace($cachePrefix,'',$key);
            $keyArr = explode('ARun',$key);
            $list[$keyArr[0]]['key'] = md5($keyArr[0]);
            if (empty($keyArr[1])){
                $list[$keyArr[0]]['waitNum'] = $redis->llen($cachePrefix.$keyArr[0]);
                $list[$keyArr[0]]['waitList'] = [];
                if ($list[$keyArr[0]]['waitNum'] > 0){
                    $waitList = $redis->lRange($cachePrefix.$keyArr[0],0,1000);
                    $list[$keyArr[0]]['waitList'] = $waitList;
                }
            }elseif($keyArr[1] == 'List') {
                $list[$keyArr[0]]['runNum'] = $redis->llen($cachePrefix.$keyArr[0].'ARunList');
                $list[$keyArr[0]]['runList'] = [];
                if ($list[$keyArr[0]]['runNum'] > 0){
                    $runList = $redis->lRange($cachePrefix.$keyArr[0].'ARunList',0,-1);
                    $time = time();
                    foreach ($runList as $key=>$row){
                        $_row = json_decode($row,true);
                        $n_row['isOdd'] = 0;
                        if ($time - $_row['asynRunTime'] > 900){
                            $n_row['isOdd'] = 1;//异常
                        }
                        $n_row['asynRunTime'] = date('Y-m-d H:i:s',$_row['asynRunTime']);
                        $n_row['info'] = $row;
                        $runList[$key] = $n_row;
                    }
                    $list[$keyArr[0]]['runList'] = $runList;
                }

            }
        }
        $data['list'] = $list;
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 运行异步任务
    /*------------------------------------------------------ */
    public function runAsynRun(){
        $rule = input('rule','','trim');
        asynRuning($rule);
        return $this->success('请求成功.');
    }
}
