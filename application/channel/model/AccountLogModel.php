<?php
namespace app\channel\model;
use app\BaseModel;


use think\Db;
/*------------------------------------------------------ */
//-- 会员帐户变动明细
//-- Author: iqgmy
/*------------------------------------------------------ */
class AccountLogModel extends BaseModel
{
	protected $table = 'channel_proxy_users_account_log';
	public  $pk = 'log_id';



}
