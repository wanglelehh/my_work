<?php
namespace app\publics\controller\api;
use app\ApiController;
use app\mainadmin\model\ArticleModel;
/*------------------------------------------------------ */
//-- 文章
/*------------------------------------------------------ */
class Article extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new ArticleModel();
    }

    /*------------------------------------------------------ */
    //-- 获取文章列表
    /*------------------------------------------------------ */
    public function getInfo(){
        $id = input('id','0','intval');
        if ($id < 1){
            return $this->error('传参错误.');
        }
        $row = $this->Model->where('id',$id)->find();
        if (empty($row)){
            return $this->error('没有找相关数据.');
        }
        $row = $row->toArray();
        $row['add_time'] = dateTpl($row['add_time']);
        $web_path = config('config.host_path');
        $row['content'] = htmlspecialchars_decode($row['content']);
        $row['content'] = preg_replace('/<img src=\"/', '<img style="display:block;width:100%;height:auto;" src="' .$web_path,$row['content']);
        $this->success($row);
    }
    /*------------------------------------------------------ */
    //-- 获取注册协议
    /*------------------------------------------------------ */
    public function regagreement(){
        $web_path = config('config.host_path');
        $content = settings('register_agreement');
        $content = htmlspecialchars_decode($content);
        $data['content'] = preg_replace('/<img src=\"/', '<img style="width:100%;height:auto;" src="' .$web_path,$content);
        $this->success($data);
    }

}
