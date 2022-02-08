<?php

namespace app\luckdraw\model;
use app\BaseModel;

//*------------------------------------------------------ */
//-- 抽奖奖品
/*------------------------------------------------------ */
class LuckdrawPrizeModel extends BaseModel
{
	protected $table = 'luck_draw_prize';
	public  $pk = 'prize_id';

}
