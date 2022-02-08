<?php

namespace app\luckdraw\model;
use app\BaseModel;

//*------------------------------------------------------ */
//-- 抽奖记录
/*------------------------------------------------------ */
class LuckdrawLogModel extends BaseModel
{
	protected $table = 'luck_draw_log';
	public  $pk = 'log_id';

}
