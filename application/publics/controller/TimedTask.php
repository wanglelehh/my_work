<?php
/*------------------------------------------------------ */
//-- 定时任务使用
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\publics\controller;
use app\BaseController;
use think\Db;

class TimedTask extends BaseController{
    /*------------------------------------------------------ */
    //-- 优先执行
    //-- cd /www/wwwroot/iqgmy/ws-xyt/public
    //-- php index.php /publics/timed_task/runCyj
    /*------------------------------------------------------ */
    public function initialize(){
        parent::initialize();
        if (empty($_SERVER['HTTP_HOST']) == false){
            exit;//定时任务只允许shell命令执行，不允许网址访问执行
        }
    }
	/*------------------------------------------------------ */
	//-- 执行创业金
	/*------------------------------------------------------ */
	public function runCyj(){
        Db::startTrans();
        echo "执行创业金：".date('Y-m-d H:i:s')."\n";
        $res = (new \app\channel\model\RewardLogModel)->runCyj();
        if ($res !== true){
            Db::rollback();
            echo "执行失败：{$res}\n";
            exit;
        }
        Db::commit();
        echo "执行成功\n";
	}

    /*------------------------------------------------------ */
    //-- 执行月分红
    /*------------------------------------------------------ */
    public function runYfh(){
        Db::startTrans();
        echo "执行月分红：".date('Y-m-d H:i:s')."\n";
        $res = (new \app\channel\model\RewardLogModel)->runYfh();
        if ($res !== true){
            Db::rollback();
            echo "执行失败：{$res}\n";
            exit;
        }
        Db::commit();
        echo "执行成功\n";
    }

	
}?>