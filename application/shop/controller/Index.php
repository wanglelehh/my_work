<?php

namespace app\shop\controller;

class Index
{
    public function index(){
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        exit;
    }
    public function test()
    {
       asynRun('shop/orderModel/asynRunPaySuccessEval',['order_id'=>1]);

    }
}