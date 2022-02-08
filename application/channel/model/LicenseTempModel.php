<?php

namespace app\channel\model;
use app\BaseModel;
//*------------------------------------------------------ */
//-- 授权模板
/*------------------------------------------------------ */
class LicenseTempModel extends BaseModel
{
    protected $table = 'channel_license_temp';
    public  $pk = 'id';
    public  $select_dom = [
        'avatar'=>['title'=>'头像','img'=>'./a_static/share/avatar.jpg'],
        'qrcode'=>['title'=>'二维码','img'=>'./a_static/share/qrcode.png'],
        'real_name'=>['title'=>'姓名','text'=>'测试姓名'],
        'mobile'=>['title'=>'手机号码','text'=>'15625077777'],
        'id_card_number'=>['title'=>'身份证号码','text'=>'440782202006202111'],
        'proxy_level'=>['title'=>'授权级别','text'=>'金牌代理']
    ];


}
