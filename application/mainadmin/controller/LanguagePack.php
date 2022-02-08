<?php
namespace app\mainadmin\controller;

use app\AdminController;
use app\mainadmin\model\LanguagePackModel;
/**
 * 多语言包
 */
class LanguagePack extends AdminController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize(){
        parent::initialize();
        $this->Model = new LanguagePackModel();
    }
    /*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index(){
        $this->getList(true);
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false)
    {
        $type = input('type',0,'intval');
        $keyword = input('keyword','','trim');
        $where[] = ['type','=',$type];
        if (empty($keyword) == false){
            $where[] = ['keyword','like','%'.$keyword.'%'];
        }
        $this->sqlOrder = "id DESC";
        $data = $this->getPageList($this->Model, $where);
        $this->assign("data", $data);
        if ($runData == false) {
            $this->data['content'] = $this->fetch('list')->getContent();
            unset($this->data['list']);
            return $this->success('', '', $this->data);
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 信息页调用
    //-- $data array 自动读取对应的数据
    /*------------------------------------------------------ */
    public function asInfo($data){
        if ($data['id'] > 0){
            $data['replace_val'] = json_decode($data['replace_val'],true);
        }
        $this->assign('packLang', $this->Model->packLang);
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 添加前处理
    /*------------------------------------------------------ */
    public function beforeAdd($data) {
        if (empty($data['keyword']) ) return $this->error('关键词(中文)不能为空.');
        $where[] = ['type','=',$data['type']];
        $where[] = ['keyword','=',$data['keyword']];
        $count = $this->Model->where($where)->count();
        if ($count > 0) return $this->error('已存在相同的关键词(中文)，不允许重复添加.');
        $data['replace_val'] = json_encode($data['replace_val'],JSON_UNESCAPED_UNICODE);
        $data['strlen'] = strlen($data['keyword']);
        $this->Model->cleanMemcache();
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 修改前处理
    /*------------------------------------------------------ */
    public function beforeEdit($data){
        if (empty($data['keyword']) ) return $this->error('关键词(中文)不能为空.');
        $where[] = ['id','<>',$data['id']];
        $where[] = ['type','=',$data['type']];
        $where[] = ['keyword','=',$data['keyword']];
        $count = $this->Model->where($where)->count();
        if ($count > 0) return $this->error('已存在相同的关键词(中文)，不允许重复添加.');
        $data['replace_val'] = json_encode($data['replace_val'],JSON_UNESCAPED_UNICODE);
        $data['strlen'] = strlen($data['keyword']);
        $this->Model->cleanMemcache();
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 删除
    /*------------------------------------------------------ */
    public function del(){
        $where['id'] = input('id',0,'intval');
        if ($where['id']<1) return $this->error('传递参数失败！');
        $res = $this->Model->where($where)->delete();
        if ($res < 1)  return $this->error();
        return $this->success('操作成功.');
    }
}
