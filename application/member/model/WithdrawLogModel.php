<?php

namespace app\member\model;
use app\BaseModel;
//*------------------------------------------------------ */
//-- 提现日志
/*------------------------------------------------------ */
class WithdrawLogModel extends BaseModel
{
	protected $table = 'users_withdraw_log';
	public  $pk = 'log_id';
    public $status = ['0'=>'待审核','1'=>'审核失败','9'=>'已完成'];

}
