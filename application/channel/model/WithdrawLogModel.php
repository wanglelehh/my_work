<?php

namespace app\channel\model;
use app\BaseModel;
//*------------------------------------------------------ */
//-- 提现日志
/*------------------------------------------------------ */
class WithdrawLogModel extends BaseModel
{
	protected $table = 'channel_proxy_users_withdraw_log';
	public $pk = 'log_id';
	public $status = ['0'=>'待审核','1'=>'审核失败','9'=>'已完成'];

}
