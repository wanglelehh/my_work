<?php
namespace app\mainadmin\controller;

use app\BaseController;

/**
 * 异步执行
 */
class AsynRun extends BaseController
{
	/*------------------------------------------------------ */
	//-- 运行
    //-- 异步处理必须返回return true,否则都视为执行错误，返回字符串则作为错误内容记录
    //-- 方法名必须包含asynRun，否则不执行
    //-- 注意传参，不要传递过多的数据
    //-- 调用方法 synRun('shop/orderModel/asynRunPaySuccessEval',['order_id'=>$info['order_id']]);
	/*------------------------------------------------------ */
    public function run()
    {
        ignore_user_abort(true);  //忽略客户端中断
        set_time_limit(900);//最大执行15分钟
        $post = $this->checkPost();

        $rule = $post['rule'];
        $modelArr = explode('/',$rule);
        $model = str_replace('/', '\\', "/app/{$modelArr[0]}/model/{$modelArr[1]}");
        $fun = $modelArr[2];
        if (strstr($fun,'asynRun') == false){
            $this->log("执行失败:方法中没有asynRun",$rule);
            exit;//方法名不包含asynRun的不执行
        }
        $cachePrefix = config('cache.prefix');
        $cache = \think\facade\Cache::init();
        $redis = $cache->handler();
        $runNum = $redis->incr($cachePrefix.$rule.'ARunNum');//获取当前的进程数
        $asynRunTime = time();
        $postData = $redis->rPop($cachePrefix.$rule);//取出最先添加队列的数据
        if (empty($postData)){//没有待处理
            $runData = $redis->rPop($cachePrefix.$rule.'ARunList');
            if (empty($runData)){
                redisLook($cachePrefix.$rule.'ARunLook',-1);//销毁锁
                exit;
            }
            $dataArr = json_decode($runData,true);
            if ($asynRunTime - $dataArr['asynRunTime'] > 900){
                $postData = $runData;//超过15分钟，继续执行
            }else{
                if ($runNum == $redis->get($cachePrefix.$rule.'ARunNum')){
                    $redis->del($cachePrefix.$rule.'ARunNum');
                    redisLook($cachePrefix.$rule.'ARunLook',-1);//销毁锁
                }
                $redis->lPush($cachePrefix.$rule.'ARunList',$runData);//写回运行中队列
                exit;
            }
        }

        $runData = json_decode($postData,true);
        $runData['runNum'] = $runNum;
        $runData['asynRunTime'] = $asynRunTime;
        $runData['rule'] = $rule;
        $redis->lPush($cachePrefix.$rule.'ARunList',json_encode($runData));//写入运行中队列
        unset($runData['asynRunTime']);

        $res = (new $model)->$fun($runData);
        if ($res !== true){
            $redis->lPush($cachePrefix.$rule,$postData);//处理失败，写回队列
            $this->log("执行失败:".$res,$rule,$runData);
        }
        //重新写入执行失败的异步
        $runLen = $redis->llen($cachePrefix.$rule.'ARunList');
        do{
            $runLen--;
            $data = $redis->rPop($cachePrefix.$rule.'ARunList');
            if (empty($data)){
                break;
            }
            $dataArr = json_decode($data,true);
            if ($dataArr['asynRunTime'] == $asynRunTime && $dataArr['runNum'] == $runNum){
                continue;//当前执行的已成功，已从队列中排除，不作处理
            }
            if ($asynRunTime - $dataArr['asynRunTime'] > 900){
                $dataArr['runag'] = empty($dataArr['runag'])?1:$dataArr['runag']+1;
                $redis->lPush($cachePrefix.$rule,json_encode($dataArr));//超过15分钟，重新写回待执行队列
            }else{
                $redis->lPush($cachePrefix.$rule.'ARunList',$data);//写回运行中队列
            }
        }while($runLen > 0);

        if ($runNum == $redis->get($cachePrefix.$rule.'ARunNum')) {
            redisLook($cachePrefix.$rule . 'ARunLook', -1);//销毁锁
            asynRuning($rule);//继续执行
        }
        exit;
    }

    /*------------------------------------------------------ */
    //-- 验证请求
    /*------------------------------------------------------ */
    private function checkPost(){
        global $argv;
        if (empty($argv) == false){//建议使用此方法，需加载lzasyn.so扩展
            $_argv = $argv;
            unset($_argv[0]);
            $_argv = join('&',$_argv);
            parse_str($_argv,$post);
        }else{
            $post = $_POST;
        }
        //验证请求是否有效
        $sign = $post['sign'];
        if (empty($sign)){
           $this->log("验证失败：没有签名.",$post['rule'],$post);
           exit;
        }
        unset($post['sign']);
        if ($post['postsigntime'] < time() - 10){
            $this->log("验证失败：接口超时.",$post['rule'],$post);
            exit;
        }
        $query = http_build_query($post);
        $_sign = md5($query.config('config.apikey'));
        if ($_sign != $sign){
            $this->log("验证失败：签名错误.",$post['rule'],$post);
            exit;
        }
        unset($post['postsigntime']);
        return $post;
    }
    /*------------------------------------------------------ */
    //-- 记录日志
    /*------------------------------------------------------ */
    private function log($str,$rule='',$param=[]){

        $inArr['rule'] = $rule;
        $inArr['param'] = json_encode($param,JSON_UNESCAPED_UNICODE);
        $inArr['add_time'] = time();
        $inArr['log_info'] = $str;
        (new \app\mainadmin\model\AsynRunErrorLog)->save($inArr);
    }
}
