<?php

namespace app\channel\model;
use app\BaseModel;
//*------------------------------------------------------ */
//-- 会员操作日志
/*------------------------------------------------------ */
class LogSysModel extends BaseModel
{
	protected $table = 'channel_log_sys';
	public  $pk = 'log_id';
    

}
