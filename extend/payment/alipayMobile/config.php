<?php
return array(
    'code'=> 'alipayMobile',
    'name' => '手机网站支付宝',
    'version' => '1.0',
    'author' => 'iqgmy',
    'desc' => '手机端网站支付宝 ',
    'icon' => 'logo.jpg',
    'scene' =>1,  // 使用场景 0 PC+手机 1 手机 2 PC
    'config' => array(
        array('name' => 'alipay_account','label'=>'支付宝帐户','type'=>'text','value'=>'','is_must'=>1,'tip'=>''),
        array('name' => 'alipay_partner','label'=>'appID',           'type' => 'text',   'value' => ''),
        array('name' => 'alipay_private_key','label'=>'应用私钥',           'type' => 'textarea',   'value' => ''),
        array('name' => 'alipay_public_key','label'=>'支付宝公钥',           'type' => 'textarea',   'value' => '','tip'=>'<br>使用公钥方式时填写，证书模式时不要填写'),
        array('name' => 'alipay_cert_public','label'=>'支付宝公钥证书',           'type' => 'textarea',   'value' => '','tip'=>'<br>alipayCertPublicKey_RSA2.cer，需使用公钥证书模式，上传CSR文件，才能获取'),
        array('name' => 'alipay_app_cert','label'=>'支付宝应用证书','type' => 'textarea','value' => '','is_must'=>0,'tip'=>'<br>appCertPublicKey_【appID】.cer，同上'),
        array('name' => 'alipay_root_cert','label'=>'支付宝根证书','type' => 'textarea','value' => '','is_must'=>0,'tip'=>'<br>alipayRootCert.cer，同上'),
        array('name'=>'website','label'=>'指定使用的网站域名','type' =>'text','value'=>'','is_must'=>1,
            'tip'=>'只填写域名，格式如：www.xxx.com，多个用|坚线隔开<br><b class="red">注：修改此项必需核实支付宝信息是否正确</b>')
    ),
);