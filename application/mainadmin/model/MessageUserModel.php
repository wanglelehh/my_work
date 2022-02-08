<?php

namespace app\mainadmin\model;

use app\BaseModel;

//*------------------------------------------------------ */
//-- 会员消息
/*------------------------------------------------------ */
class MessageUserModel extends BaseModel
{
	protected $table = 'main_message_user';
	public  $pk = 'rec_id';

    /*------------------------------------------------------ */
    //-- 通知发送
    //-- $user_id 用户ID
    //-- $message_type int 消息类型
    //-- $ext_id 用户ID
    //-- $title 标题
    //-- $content 内容
    //-- $send_time 发送时间，达到此时间才显示
    //-- $show_end_time 过期时间，非系统消息默认为3个月
    /*------------------------------------------------------ */
    public function sendMessage($user_id, $type = 0, $ext_id = 0, $title, $content = '',$send_time='', $show_end_time = '')
    {
        if (empty($show_end_time)) {
            $show_end_time = strtotime('+3 month', time());
        }
        if (empty($send_time)){
            $send_time = time();
        }
        $inArr['user_id'] = $user_id;
        $inArr['type'] = $type;
        $inArr['ext_id'] = $ext_id;
        $inArr['title'] = $title;
        $inArr['content'] = $content;
        $inArr['send_time'] = $send_time;
        $inArr['show_end_time'] = $show_end_time;
        $inArr['add_time'] = time();
        $res = (new MessageUserModel)->create($inArr);
        if ($res['rec_id'] < 1) return false;
        return true;
    }
}
