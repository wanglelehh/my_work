<?php

namespace app\shop\controller;

use phpmailer\PHPMailer;

class Test
{

    /*------------------------------------------------------ */
    //-- 测试发送邮件
    /*------------------------------------------------------ */
    public function send()
    {
        $toemail = '21114046@qq.com';//定义收件人的邮箱

        $mail = new PHPMailer();
        $mail->SMTPDebug = 2;

        $mail->isSMTP();// 使用SMTP服务
        $mail->CharSet = "utf-8";// 编码格式为utf8，不设置编码的话，中文会出现乱码
        $mail->Host = "smtp.163.com";// 发送方的SMTP服务器地址
        $mail->SMTPAuth = true;// 是否使用身份验证
        $mail->Username = "iqepgmy@163.com";//发送方的163邮箱用户名，就是你申请163的SMTP服务使用的163邮箱
        $mail->Password = "ZPPKNGJXQNJYMGHF";//发送方的邮箱密码，注意用163邮箱这里填写的是“客户端授权密码”而不是邮箱的登录密码
        $mail->SMTPSecure = "ssl";// 使用ssl协议方式
        $mail->Port = 465;// 163邮箱的ssl协议方式端口号是465/994,Gmail是465

        $mail->setFrom($mail->Username, "river stree");// 设置发件人信息，如邮件格式说明中的发件人，这里会显示为Mailer(xxxx@163.com），Mailer是当做名字显示
        $mail->addAddress($toemail, '');// 设置收件人信息，如邮件格式说明中的收件人，这里会显示为Liang(yyyy@163.com)
        $mail->addReplyTo($mail->Username, "Reply");// 设置回复人信息，指的是收件人收到邮件后，如果要回复，回复邮件将发送到的邮箱地址
        //$mail->addCC("xxx@163.com");// 设置邮件抄送人，可以只写地址，上述的设置也可以只写地址(这个人也能收到邮件)
        //$mail->addBCC("xxx@163.com");// 设置秘密抄送人(这个人也能收到邮件)
        //$mail->addAttachment("bug0.jpg");// 添加附件


        $mail->Subject = "这是一个测试邮件";// 邮件标题
        $body = "邮件内容是 <b>您的验证码是：123456</b>，哈哈哈！";// 邮件正文
        $mail->MsgHTML($body);
        //$mail->AltBody = "This is the plain text纯文本";// 这个是设置纯文本方式显示的正文内容，如果不支持Html方式，就会用到这个，基本无用

        if (!$mail->send()) {// 发送邮件
            echo "Message could not be sent.";
            echo "Mailer Error: " . $mail->ErrorInfo;// 输出错误信息
        } else {
            echo '发送成功';
        }

    }
    public function testAdd()
    {
        $DistinguishAddress = new \lib\DistinguishAddress();
        $add = '关先生15625077763广东省，广州市，黄x区，夏港街道丽江街美悦湾1栋1004室';
        $adds = $DistinguishAddress->getAddressResult($add);
        print_r($adds);
    }
    public function index()
    {
        $cachePrefix = config('cache.prefix');
        $cache = \think\facade\Cache::init();
        $redis = $cache->handler();
        $keysdata = $redis->keys($cachePrefix.'*');
        print_r($keysdata);
        $list = [];
        foreach ($keysdata as $key){
            if (!strstr($key,'asynRun') || !strstr($key,'Model')) {
                continue;
            }
            if (strstr($key,'ARunLook')){
                continue;
            }
            $key = str_replace($cachePrefix,'',$key);
            $keyArr = explode('ARun',$key);
            if (empty($keyArr[1])){
                $list[$keyArr[0]]['waitNum'] = $redis->llen($cachePrefix.$keyArr[0]);
            }elseif($keyArr[1] == 'List') {
                $list[$keyArr[0]]['runNum'] = $redis->llen($cachePrefix.$keyArr[0].'ARunList');
            }
        }
        print_r($list);

    }
    public function test()
    {
        $cachePrefix = config('cache.prefix');
        $cache = \think\facade\Cache::init();
        $redis = $cache->handler();
        $redis->del($cachePrefix.'asynRunTestLog');
        $redis->del($cachePrefix.'shop/TestModel/asynRunTestARunNum');
        $redis->del($cachePrefix.'shop/TestModel/asynRunTestARunList');
        $redis->del($cachePrefix.'shop/TestModel/asynRunTest');
        //业务场景不在此阐述
        for ( $i = 1 ; $i <= 10 ; $i++ ){
            $user = array('user_id'=>$i,'username'=>'demo'.$i);
            asynRun('shop/TestModel/asynRunTest',$user);
        }

    }
    public function testb()
    {
        $cachePrefix = config('cache.prefix');
        $cache = \think\facade\Cache::init();
        $redis = $cache->handler();

        echo $redis->llen($cachePrefix.'shop/TestModel/asynRunTest');
        dump($redis->lRange($cachePrefix.'shop/TestModel/asynRunTest',0,-1));
        dump($redis->lRange($cachePrefix.'shop/TestModel/asynRunTestARunList',0,-1));
        echo '<br><br><br>';
        echo $redis->llen($cachePrefix.'asynRunTestLog');
        dump($redis->lRange($cachePrefix.'asynRunTestLog',0,-1));

    }
    public function testc()
    {
        asynRuning('shop/TestModel/asynRunTest');
    }
    /*------------------------------------------------------ */
    //-- 转支付宝
    /*------------------------------------------------------ */
    public function oneTransfer()
    {
        $alipayMobile = new \payment\alipayMobile\alipayMobile();
        $transferInfo['order_sn'] = date('ymd').'0002';
        $transferInfo['arrival_money'] = "0.1";
        $transferInfo['alipay_account'] = '21114046@qq.com';
        $transferInfo['alipay_name'] = '关锡光';
        $res = $alipayMobile->oneTransfer($transferInfo);
        var_dump($res);
    }
}