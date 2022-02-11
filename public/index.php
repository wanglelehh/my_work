<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// // [ 应用入口文件 ]
namespace think;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization,uiType,Token,source,appsessionid,language");
if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
    exit;
}

// 加载基础文件
require __DIR__ . '/../thinkphp/base.php';

// 支持事先使用静态方法设置Request对象和Config对象
define('INDEX', true);
define('VIEWPATH', '../template/default/');
define('EXTEND_PATH', '../extend/');
define('DATA_PATH', '../Data/');

if (empty($_SERVER['REQUEST_URI']) == false){
    if (strstr($_SERVER['REQUEST_URI'],'sys_admin.')){
        Header("HTTP/1.1 404 Not Found");
        exit;
    }
}
// 执行应用并响应
Container::get('app')->run()->send();

