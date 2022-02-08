<?php

namespace app\mainadmin\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 异步测试
/*------------------------------------------------------ */
class AsynTestModel extends BaseModel
{
    //排序
    public function paixu($arr)
    {
         $len = count($arr);
         for ($i = 0; $i < $len - 1; $i++) {//循环比对的轮数
             for ($j = $i + 1; $j < $len; $j++) {//从第二个开始循环，循环到最后一个，逐一和第一个比较
                if ($arr[$i] > $arr[$j]) {//前边大于后边的则交换
                    $tmp = $arr[$i];
                     $arr[$i] = $arr[$j];
                     $arr[$j] = $tmp;
                 }
             }
         }
        return $arr;
    }
    public function asynRunTesta()
    {
        $arr = [];
        for($i=0;$i<=100;$i++){
            $arr[] = rand(10000,99999);
        }
        return paixu($arr);
    }

    public function asynRunTestb($param)
    {
        for($i=1;$i<=$param['num'];$i++) {
            $this->asynRunTesta();
        }
        return true;
    }
    public function asynRunTest($param)
    {
        $r = rand(10,20);
        sleep($r);
        $txt = date('Y-m-d H:i:s').'--'.PHP_BINDIR."\n";
        $txt .= json_encode($param)."\n";
        if (function_exists('lzasyn')){
            $txt .= "异步扩展加载：成功\n";
        }else{
            $txt .= "异步扩展加载：失败\n";
        }
        file_put_contents('asynRunLog.txt', $txt,FILE_APPEND);
        return true;
    }

}
