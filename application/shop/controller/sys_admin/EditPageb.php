<?php
namespace app\shop\controller\sys_admin;
use app\AdminController;
use think\facade\Cache;
use app\publics\model\LinksModel;
use app\shop\model\ShopPageTheme;
/*------------------------------------------------------ */
//-- 商城装修
/*------------------------------------------------------ */
class EditPageb extends AdminController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new ShopPageTheme();
    }
    /*------------------------------------------------------ */
    //-- 主页编辑
    /*------------------------------------------------------ */
    public function index()
    {
        $this->assign('title', '魔幻装修');
        $this->getList(true);
        return $this->fetch('sys_admin/edit_pageb/index');
    }
    /*------------------------------------------------------ */
    //-- 自定义错误提示
    /*------------------------------------------------------ */
    public function _error($message = '')
    {
        $result['status'] = 0;
        $result['result']['message'] = $message;
        return $this->ajaxReturn($result);
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false)
    {
        $where = [];
        $this->sqlOrder = "is_index DESC";
        $data = $this->getPageList($this->Model,$where);
        $this->assign("data", $data);
        if ($runData == false) {
            $data['content'] = $this->fetch('list')->getContent();
            unset($data['list']);
            return $this->success('', '', $data);
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 添加/修改页面
    /*------------------------------------------------------ */
    public function info()
    {
        $this->assign('title', '魔幻装修');
        $id = input('id',0,'intval');
        $this->assign('id', $id);
        return $this->fetch('sys_admin/edit_pageb/info');
    }
    /*------------------------------------------------------ */
    //-- 编辑
    /*------------------------------------------------------ */
    public function edit()
    {
        $id = input('id',0,'intval');
        if ($id > 0){
            $info = $this->Model->find($id);
            $data = empty($info['page'])?'null':$info['page'];
            $data = json_decode($data,true);
            $data['page']['isindex'] = $info['is_index'];
            $name = $info['theme_name'];
        }else{
            $data = 'null';
            $name = '商城';
        }
        $this->assign('id', $id);
        $this->assign('name', $name);
        $this->assign('data', $data);
        $this->assign('title', '魔幻装修');
        $this->assign('sys_model_shop_fightgroup',  settings('sys_model_shop_fightgroup'));
        return $this->fetch('sys_admin/edit_pageb/edit');
    }
    /*------------------------------------------------------ */
    //-- 保存页面
    /*------------------------------------------------------ */
    public function save()
    {
        $id = input('id',0,'intval');
        $data = input('data','','trim');
        $data = json_decode($data,true);
        if (empty($data['items'])){
           return $this->_error('请设置排版内容后再操作.');
        }
        $tmpData['is_new'] = 1;//默认设置为新版
        $tmpData['theme_name'] = $data['page']['name'];
        $tmpData['is_index'] = $data['page']['isindex'] * 1;
        $tmpData['theme_type'] = 'index';
        $tmpData['page'] = json_encode($data,JSON_UNESCAPED_UNICODE);
        unset($data);
        if ($id < 1){
            $tmpData['add_time'] = time();
            $tmpData['update_time'] = time();
            $res = $this->Model->save($tmpData);
            $id = $this->Model->st_id;
        }else{
            $tmpData['update_time'] = time();
            $res = $this->Model->where('st_id',$id)->update($tmpData);
        }
        if ($res < 1){
            return $this->_error('操作失败，请重试.');
        }
        if ($tmpData['is_index'] == 1){
            $upwhere[] = ['st_id','<>',$id];
            $this->Model->where($upwhere)->update(['is_index'=>0]);
        }
        Cache::rm('shopIndex_diy_'.$id);
        Cache::rm('shopIndex_diy_'.$id.'topfixed');
        $result['status'] = 1;
        $result['result']['id'] = $id;
        $result['result']['jump'] = url('edit',['id'=>$id]);
        $this->_log($id, '保存装修页面');
        return $this->ajaxReturn($result);
    }

    /*------------------------------------------------------ */
    //-- 预览
    /*------------------------------------------------------ */
    public function preview()
    {
        $id = input('id',0,'intval');
        $this->assign('id', $id);
        return $this->fetch('sys_admin/edit_pageb/preview');
    }

    /*------------------------------------------------------ */
    //-- 自定义请求相关方法
    /*------------------------------------------------------ */
    public function diyFun()
    {
        $_fun = input('_fun','','trim');
        if(method_exists($this,$_fun)){
            return $this->$_fun();
        }
        return $this->_error('请求错误.');
    }
    /*------------------------------------------------------ */
    //-- 选定链接
    /*------------------------------------------------------ */
    public function links(){
        $this->assign('links', (new LinksModel)->links());
        $CategoryModel = new \app\shop\model\CategoryModel();
        $this->assign('CategoryList', $CategoryModel->getRows());

        return $this->fetch('sys_admin/edit_pageb/links');
    }

    /*------------------------------------------------------ */
    //-- 搜索
    /*------------------------------------------------------ */
    public function search()
    {
        $type = input('type','','trim');
        $kw = input('kw','','trim');
        if (in_array($type,['good','article','diypage']) == false){
            return $this->error('请求错误.');
        }
        if ($type == 'good'){
            $GoodsModel = new \app\shop\model\GoodsModel();
            $where[] = ['is_delete','=',0];
            $where[] = ['goods_name','like','%'.$kw.'%'];
            $ids = $GoodsModel->where($where)->limit(20)->column('goods_id');
            foreach ($ids as $id){
                $list[] = $GoodsModel->info($id);
            }
        }elseif($type == 'article'){
            $ArticleModel = new \app\mainadmin\model\ArticleModel();
            $where[] = ['title','like','%'.$kw.'%'];
            $list = $ArticleModel->where($where)->limit(20)->select()->toArray();
        }elseif($type == 'diypage'){
            $where[] = ['theme_type','=','index'];
            $where[] = ['theme_name','like','%'.$kw.'%'];
            $list = $this->Model->where($where)->limit(20)->select()->toArray();
        }
        $this->assign('list',$list);
        $this->assign('kw',$kw);
        return $this->fetch('sys_admin/edit_pageb/search_'.$type);
    }

    /*------------------------------------------------------ */
    //-- 搜索 暂只用于商品模块
    /*------------------------------------------------------ */
    public function query()
    {
        $type = input('type','','trim');
        $keyword = input('keyword','','trim');
        if (in_array($type,['good','category','coupon','integralgood']) == false){
            return $this->error('请求错误.');
        }
        if ($type == 'good') {
            $GoodsModel = new \app\shop\model\GoodsModel();
            $where[] = ['goods_name', 'like', '%' . $keyword . '%'];
            $ids = $GoodsModel->where($where)->limit(20)->column('goods_id');
            foreach ($ids as $id) {
                $good = $GoodsModel->info($id);
                $ginfo['thumb'] = $good['goods_thumb'];
                $ginfo['title'] = $good['goods_name'];
                $ginfo['subtitle'] = $good['short_name'];
                $ginfo['price'] = $good['shop_price'];
                $ginfo['gid'] = $good['goods_id'];
                $ginfo['total'] = $good['goods_number'];
                $ginfo['productprice'] = $good['market_price'];
                $ginfo['sales'] = $good['sale_num'];
                $ginfo['bargain'] = 0;
                $ginfo['credit'] = null;
                $ginfo['ctype'] = null;
                $ginfo['gtype'] = null;
                $list[] = $ginfo;
            }
        }elseif ($type == 'integralgood'){
            $IntegralGoodsModel = new \app\integral\model\IntegralGoodsModel();
            $viewObj = $IntegralGoodsModel->alias('ig')->join("shop_goods g", 'ig.goods_id=g.goods_id', 'left');
            $time = time();
            $where[] = ['ig.start_date','<',$time];
            $where[] = ['ig.end_date','>',$time];
            $where[] = ['g.goods_name', 'like', '%' . $keyword . '%'];
            $goods = $viewObj->where($where)->field('ig.*,g.goods_thumb,g.goods_name,g.short_name')->order('ig_id DESC')->select();
            foreach ($goods as $good){
                $ginfo['thumb'] = $good['goods_thumb'];
                $ginfo['title'] = $good['goods_name'];
                $ginfo['subtitle'] = $good['short_name'];
                $ginfo['credit'] = $good['show_integral'];
                $ginfo['gid'] = $good['ig_id'];
                $ginfo['sales'] = $good['sale_num'];
                $ginfo['bargain'] = 0;
                $ginfo['price'] = 0;
                $ginfo['ctype'] = null;
                $ginfo['gtype'] = null;
                $list[] = $ginfo;
            }
        }elseif($type == 'category'){
            $CategoryModel = new \app\shop\model\CategoryModel();
            $rows = $CategoryModel->getRows();
            foreach ($rows as $row){
                $_info['id'] = $row['id'];
                $_info['name'] = $row['name'];
                $_info['level'] = $row['level'];
                $_info['parentid'] = $row['pid'];
                $_info['displayorder'] = $row['sort_order'];
                $_info['enabled'] = $row['status'];
                $_info['ishome'] = $row['is_index'];
                $_info['advurl'] = '';
                $_info['isrecommand'] = "0";
                $_info['description'] = "";

                if (empty($row['pic'])){
                    $_info['thumb'] = config('config.host_path').'/static/main/img/def_img.jpg';
                }else{
                    $_info['thumb'] = $row['pic'];
                }
                if (empty($row['cover'])){
                    $_info['advimg'] = '';
                }else{
                    $_info['advimg'] = config('config.host_path').$row['cover'];
                }
                $list[] = $_info;
            }
        }elseif($type == 'coupon'){
            $BonusModel = new \app\shop\model\BonusModel();
            $rows = $BonusModel->getListDiy();
            foreach ($rows as $row){
                $_info['id'] = $row['type_id'];
                $_info['couponname'] = $row['type_name'];
                $_info['enough'] = $row['min_amount'];
                $_info['deduct'] = $row['type_money'];
                $_info['values'] = $row['type_money'];
                $_info['uselimit'] = '满'.$row['min_amount'].'元可用';
                $list[] = $_info;
            }
        }
        $this->assign('is_xcx',$this->is_xcx);
        $this->assign('list',$list);
        $this->assign('keyword',$keyword);
        return $this->fetch('sys_admin/edit_pageb/query_'.$type);
    }
    /*------------------------------------------------------ */
    //-- 选择图标
    /*------------------------------------------------------ */
    public function selecticon()
    {
        return $this->fetch('sys_admin/edit_pageb/selecticon');
    }
    /*------------------------------------------------------ */
    //-- 快速修改
    /*------------------------------------------------------ */
    public function afterAjax($st_id,$data){
        if (isset($data['is_index'])){
            $log = '快速自定义首页';
            if ($data['is_index'] == 1){
                $upwhere[] = ['is_xcx','=',$this->is_xcx];
                $upwhere[] = ['st_id','<>',$st_id];
                $this->Model->where($upwhere)->update(['is_index'=>0]);
            }
            $this->_log($st_id, $log);
        }
        return $this->success('修改成功');
    }
    /*------------------------------------------------------ */
    //-- 删除
    /*------------------------------------------------------ */
    public function del(){
        $st_id = input('id',0,'intval');
        if ($st_id < 1){
            return $this->error('请求错误.');
        }
        $count = $this->Model->count('st_id');
        if ($count <= 1){
            return $this->error('至少保留一个模板.');
        }
        $info = $this->Model->where('st_id',$st_id)->find();
        if (empty($info)){
            return $this->error('没有找到相关模板.');
        }
        if ($info['is_index'] == 1){
            return $this->error('首页显示的不能删除.');
        }
        $info->delete();
        $this->_log($st_id, '删除自定义模板:'.$st_id);
        return $this->success('删除成功.');
    }
}
