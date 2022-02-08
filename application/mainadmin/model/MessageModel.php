<?php

namespace app\mainadmin\model;

use app\BaseModel;
use think\facade\Cache;

//*------------------------------------------------------ */
//-- 站内信
/*------------------------------------------------------ */

class MessageModel extends BaseModel
{
    protected $table = 'main_message';
    public $pk = 'message_id';
    protected $mkey = 'message_info_mkey_';
    protected $messageType = ['all'=>'系统公告','distribution'=>'指定身份','byuser'=>'指定会员'];

    public function getMessageType(){
        $settings = settings();
        $messageType = $this->messageType;
        if ($settings['sys_model_distribution'] != 1){
            unset($messageType['distribution']);
        }
        if ($settings['sys_model_channel'] != 1){
            unset($messageType['channel']);
        }
        return $messageType;
    }
    /*------------------------------------------------------ */
    //-- 获取未读消息数
    /*------------------------------------------------------ */
    public function getMessageUnCount($user_id){
        $where = [];
        $where[] = ['type','=','all'];
        $where[] = ['status','=',0];
        $where[] = ['send_time','<',time()];
        $where[] = ['show_end_time','>',time()];
        $message_id = $this->where($where)->column('message_id');
        $sysNum = count($message_id);
        $MessageUserModel = new MessageUserModel();
        if ($sysNum > 0){
            $suwhere = [];
            $suwhere[] = ['type','=','all'];
            $suwhere[] = ['ext_id','in',$message_id];
            $suwhere[] = ['user_id','=',$user_id];
            $readNum = $MessageUserModel->where($suwhere)->count('rec_id');
            $sysNum -= $readNum;
        }
        $uwhere = [];
        $uwhere[] = ['user_id','=',$user_id];
        $uwhere[] = ['status','=',1];
        $uwhere[] = ['send_time','<',time()];
        $uwhere[] = ['show_end_time','>',time()];
        $uwhere[] = ['is_see','=',0];
        $unreadNum = $MessageUserModel->where($uwhere)->count('rec_id');
        return $sysNum + $unreadNum;
    }

}
