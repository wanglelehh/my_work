<?php

namespace app\member\model;
use app\BaseModel;
//*------------------------------------------------------ */
//-- 会员升级日志
/*------------------------------------------------------ */
class LogUpLevelModel extends BaseModel
{
	protected $table = 'users_log_uplevel';
	public  $pk = 'log_id';

}
