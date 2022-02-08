<?php

namespace app\channel\model;
use app\BaseModel;
//*------------------------------------------------------ */
//-- 代理商品价格表
/*------------------------------------------------------ */
class ChannelGoodsPricesModel extends BaseModel
{
    protected $table = 'channel_goods_prices';
    public  $pk = 'goods_id';


}
