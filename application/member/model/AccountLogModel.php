<?php
namespace app\member\model;
use app\BaseModel;
use app\member\model\UsersModel;
use app\member\model\AccountModel;

use think\Db;
/*------------------------------------------------------ */
//-- 会员帐户变动明细
//-- Author: iqgmy
/*------------------------------------------------------ */
class AccountLogModel extends BaseModel
{
	protected $table = 'users_account_log';
	public  $pk = 'log_id';



}
