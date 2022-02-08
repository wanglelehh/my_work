<?php
namespace app\channel\controller\sys_admin;
use think\Db;
use app\AdminController;

use app\channel\model\ChannelGoodsModel;
use app\channel\model\GoodsUnitModel;
use app\channel\model\StockModel;
use app\channel\model\StockDetailModel;

use app\shop\model\GoodsModel;
use app\shop\model\GoodsSkuModel;

use app\member\model\UsersModel;
use app\member\model\RoleModel;
use think\Model;

/**
 * 库存管理
 * Class Index
 * @package app\store\controller
 */
class Stock extends AdminController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new UsersModel();
    }
    /*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index()
    {
        $this->getList(true);
        return $this->fetch();
    }

    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false)
    {
        $RoleModel = new RoleModel();
        $roleList = $RoleModel->getRows(1);
        $this->assign("roleList", $RoleModel->getRows(1));
        $this->search['keyword'] = input('keyword','','trim');
        $this->search['role_id'] = input('role_id','0','intval');
        $where = [];
        if ($this->search['role_id'] > 0){
            $where[] = "role_id = {$this->search['role_id']}";
        }else{
            $roleIds =  array_keys($roleList);
            $where[] = "role_id IN (".join(',',$roleIds).")";
        }
        if (empty($this->search['keyword']) == false) {
            if (is_numeric($this->search['keyword'])) {
                $where[] = " u.user_id = '" . ($this->search['keyword']) . "' or u.mobile like '" . $this->search['keyword'] . "%'";
            } else {
                $where[] = " ( u.real_name like '" . $this->search['keyword'] . "%' )";
            }
        }
        $viewObj = $this->Model->alias('u')->where(join(' AND ', $where));
        $data = $this->getPageList($this->Model, $viewObj);

        $StockModel = new StockModel();
        foreach ($data['list'] as $key=>$user){
            $stockTotal = $StockModel->where('user_id',$user['user_id'])->field('purchase_type,sum(goods_number) as StockTotal, sum(out_number) as outTotal')->group('purchase_type')->select()->toArray();
            $user['cloudStockTotal'] = 0;
            $user['cloudOutTotal'] = 0;
            $user['entityStockTotal'] = 0;
            $user['entityOutTotal'] = 0;
            foreach ($stockTotal as $stock){
                if ($stock['purchase_type'] == 1){
                    $user['cloudStockTotal'] = $stock['StockTotal'];
                    $user['cloudOutTotal'] = $stock['outTotal'];
                }else{
                    $user['entityStockTotal'] = $stock['StockTotal'];
                    $user['entityOutTotal'] = $stock['outTotal'];
                }
            }
            $data['list'][$key] = $user;
        }
        $this->assign("data", $data);
        $this->assign("search",$this->search);
        if ($runData == false) {
            $data['content'] = $this->fetch('list')->getContent();
            return $this->success('', '', $data);
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 详情
    /*------------------------------------------------------ */
    public function info()
    {
        $user_id = input('user_id',0,'intval');
        if ($user_id < 1){
            return $this->error('代理ID传参出错.');
        }
        $ChannelGoodsModel = new ChannelGoodsModel();
        $GoodsSkuModel = new GoodsSkuModel();
        $StockModel = new StockModel();
        $goodsList = $ChannelGoodsModel->alias('cg')->join("shop_goods sg", 'cg.goods_id=sg.goods_id', 'left')
            ->field('cg.id,cg.goods_id,cg.unit,sg.goods_name,sg.is_spec')->order('cg.id DESC')->select()->toArray();
        foreach ($goodsList as $key=>$goods){
            $whereStock = [];
            $whereStock[] = ['user_id','=',$user_id];
            $whereStock[] = ['goods_id','=',$goods['goods_id']];
            $stockTotal = $StockModel->where($whereStock)->select()->toArray();
            $goods['skuList'] = [];
            $goods['cloudStockTotal'] = 0;
            $goods['cloudOutTotal'] = 0;
            $goods['entityStockTotal'] = 0;
            $goods['entityOutTotal'] = 0;
            $goods['skuCount'] = 0;
            if ($goods['is_spec'] == 1){
                $skuList = [];
                foreach ($stockTotal as $stock){
                    if ($stock['purchase_type'] == 1){
                        $goods['cloudStockTotal'] += $stock['goods_number'];
                        $goods['cloudOutTotal'] += $stock['out_number'];
                        $skuList[$stock['sku_id']]['cloud_goods_number'] = $stock['goods_number'];
                        $skuList[$stock['sku_id']]['cloud_out_number'] = $stock['out_number'];
                    }else{
                        $goods['entityStockTotal'] += $stock['goods_number'];
                        $goods['entityOutTotal'] += $stock['out_number'];
                        $skuList[$stock['sku_id']]['entity_goods_number'] = $stock['goods_number'];
                        $skuList[$stock['sku_id']]['entity_out_number'] = $stock['out_number'];
                    }
                }
                $skuNames = $GoodsSkuModel->where('goods_id',$goods['goods_id'])->column('sku_name','sku_id');
                foreach ($skuNames as $sku_id=>$sku_name){
                    $sku = [];
                    if (empty($skuList[$sku_id]) == false){
                        $sku = $skuList[$sku_id];
                    }
                    if (isset($sku['cloud_goods_number']) == false) $sku['cloud_goods_number'] = 0;
                    if (isset($sku['cloud_out_number']) == false) $sku['cloud_out_number'] = 0;
                    if (isset($sku['entity_goods_number']) == false) $sku['entity_goods_number'] = 0;
                    if (isset($sku['entity_out_number']) == false) $sku['entity_out_number'] = 0;
                    $sku['sku_id'] = $sku_id;
                    $sku['sku_name'] = $sku_name;
                    $goods['skuList'][] = $sku;
                }
                $goods['skuCount'] = count($goods['skuList']);
            }else{
                foreach ($stockTotal as $stock){
                    if ($stock['purchase_type'] == 1){
                        $goods['cloudStockTotal'] = $stock['goods_number'];
                        $goods['cloudOutTotal'] = $stock['out_number'];
                    }else{
                        $goods['entityStockTotal'] = $stock['goods_number'];
                        $goods['entityOutTotal'] = $stock['out_number'];
                    }
                }
            }
            $goodsList[$key] = $goods;
        }
        $this->assign('user_id',$user_id);
        $this->assign('goodsList',$goodsList);
        $this->assign('goodsUnit',(new GoodsUnitModel)->getRows());
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 明细
    /*------------------------------------------------------ */
    public function detail()
    {
        $this->getDetailList(true);
        $goods_name = (new GoodsModel)->where('goods_id',$this->goods_id)->value('goods_name');
        $sku_name = '';
        if ($this->sku_id > 0){
            $sku_name = (new GoodsSkuModel)->where('sku_id',$this->sku_id)->value('sku_name');
        }
        $this->assign('goods_name',$goods_name);
        $this->assign('sku_name',$sku_name);
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 获取明细列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getDetailList($runData = false)
    {
        $user_id = input('user_id',0,'intval');
        if ($user_id < 1){
            return $this->error('代理ID传参出错.');
        }
        $this->goods_id = input('goods_id',0,'intval');
        if ($this->goods_id < 1){
            return $this->error('商品ID传参出错.');
        }
        $this->sku_id = input('sku_id',0,'intval');
        $purchase_type = input('purchase_type',1,'intval');
        $operate = input('operate',0,'intval');
        $where[] = ['user_id','=',$user_id];
        $where[] = ['goods_id','=', $this->goods_id];
        $where[] = ['sku_id','=', $this->sku_id];
        if ($purchase_type == 1){
            $where[] = ['purchase_type','in', [1,3]];
        }else{
            $where[] = ['purchase_type','=', $purchase_type];
        }

        if ($operate > 0){
            $where[] = ['operate','=', $operate];
        }
        $StockDetailModel = new StockDetailModel();
        $data = $this->getPageList($StockDetailModel, $where);
        $this->assign("data", $data);
        $this->assign("user_id", $user_id);
        $this->assign("goods_id", $this->goods_id);
        $this->assign("sku_id", $this->sku_id);
        $this->assign("purchase_type", $purchase_type);
        if ($runData == false) {
            $data['content'] = $this->fetch('detail_list')->getContent();
            return $this->success('', '', $data);
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 调整库存
    /*------------------------------------------------------ */
    public function edit()
    {
        $user_id = input('user_id',0,'intval');
        if ($user_id < 1){
            return $this->error('代理ID传参出错.');
        }
        $goods_id = input('goods_id',0,'intval');
        if ($goods_id < 1){
            return $this->error('商品ID传参出错.');
        }
        $sku_id = input('sku_id',0,'intval');
        $StockModel = new StockModel();

        if ($this->request->isPost()){
            $cloud_number = input('cloud_number',0,'intval');
            $cloud_type = input('cloud_type','add','trim');
            $entity_number = input('entity_number',0,'intval');
            $entity_type = input('entity_type','add','trim');
            $change_desc = input('change_desc','','trim');
            if (empty($change_desc)){
                return $this->error('请填写调整原因.');
            }
            $StockDetailModel = new StockDetailModel();
            $time = time();
            if ($cloud_number <= 0 && $entity_number<=0){
                return $this->error('请填写需要调整的库存数量.');
            }
            Db::startTrans();//启动事务
            if ($cloud_number > 0){
                $hash_code = md5($user_id.'_1_'.$goods_id.'_'.$sku_id);
                $where = [];
                $where[] = ['user_id','=',$user_id];
                $where[] = ['hash_code','=',$hash_code];
                $goodsStock = $StockModel->where($where)->find();

                if (empty($goodsStock) == false){
                    if ($cloud_type == 'add'){
                        $operate = 1;
                        $log = '后台调整，增加库存:'.$change_desc;
                        $upData['goods_number'] = ['INC', $cloud_number];
                    }else{
                        if ($goodsStock['goods_number'] < $cloud_number){
                            Db::rollback();// 回滚事务
                            return $this->error('扣除数量不能大于当前云仓库存失败.');
                        }
                        $operate = 2;
                        $log = '后台调整，扣减库存:'.$change_desc;
                        $upData['goods_number'] = ['DEC', $cloud_number];
                    }
                    $upData['update_time'] = $time;
                    $res = $StockModel->where($where)->update($upData);
                    if ($res < 1) {
                        Db::rollback();// 回滚事务
                        return $this->error('更新云仓库存失败.');//更新失败数据失败
                    }
                }else{
                    if ($cloud_type != 'add'){
                        Db::rollback();// 回滚事务
                        return $this->error('当前商品没有云仓库存可扣除.');//更新失败数据失败
                    }
                    $operate = 1;
                    $log = '后台调整，增加库存:'.$change_desc;
                    $inData = [];
                    $inData['hash_code'] = $hash_code;
                    $inData['purchase_type'] = 1;
                    $inData['user_id'] = $user_id;
                    $inData['goods_id'] = $goods_id;
                    $inData['sku_id'] = $sku_id;
                    $inData['goods_number'] = $cloud_number;
                    $inData['update_time'] = $time;
                    $res = $StockModel->create($inData);
                    if ($res->id < 1) {
                        Db::rollback();// 回滚事务
                        return $this->error('新增代理云仓库存失败.');//写入数据失败
                    }
                }
                $res = $StockDetailModel->saveLog($user_id,1,$goods_id,$sku_id,0,$operate,$cloud_number,$log,$time,AUID);
                if ($res !== true) {
                    Db::rollback();// 回滚事务
                    return $this->error('写入库存明细失败.');
                }
            }
            if ($entity_number > 0){
                $hash_code = md5($user_id.'_2_'.$goods_id.'_'.$sku_id);
                $where = [];
                $where[] = ['user_id','=',$user_id];
                $where[] = ['hash_code','=',$hash_code];
                $goodsStock = $StockModel->where($where)->find();
                if (empty($goodsStock) == false){
                    if ($entity_type == 'add'){
                        $operate = 1;
                        $log = '后台调整，增加库存:'.$change_desc;
                        $upData['goods_number'] = ['INC', $entity_number];
                    }else{
                        if ($goodsStock['goods_number'] < $entity_number){
                            Db::rollback();// 回滚事务
                            return $this->error('扣除数量不能大于当前实体仓库存失败.');
                        }
                        $operate = 2;
                        $log = '后台调整，扣减库存:'.$change_desc;
                        $upData['goods_number'] = ['DEC', $entity_number];
                    }
                    $upData['update_time'] = $time;
                    $res = $StockModel->where($where)->update($upData);
                    if ($res < 1) {
                        Db::rollback();// 回滚事务
                        return $this->error('更新实体仓库存失败.');//更新失败数据失败
                    }
                }else{
                    if ($cloud_type != 'add'){
                        Db::rollback();// 回滚事务
                        return $this->error('当前商品不能扣除实体仓库存.');//更新失败数据失败
                    }
                    $operate = 1;
                    $log = '后台调整，增加库存:'.$change_desc;
                    $inData = [];
                    $inData['hash_code'] = $hash_code;
                    $inData['purchase_type'] = 2;
                    $inData['user_id'] = $user_id;
                    $inData['goods_id'] = $goods_id;
                    $inData['sku_id'] = $sku_id;
                    $inData['goods_number'] = $entity_number;
                    $inData['update_time'] = $time;
                    $res = $StockModel->create($inData);
                    if ($res->id < 1) {
                        Db::rollback();// 回滚事务
                        return $this->error('新增代理实体仓库存失败.');//写入数据失败
                    }
                }
                $res = $StockDetailModel->saveLog($user_id,2,$goods_id,$sku_id,0,$operate,$entity_number,$log,$time,AUID);
                if ($res !== true) {
                    Db::rollback();// 回滚事务
                    return $this->error('写入库存明细失败.');
                }
            }
            Db::commit();// 提交事务
            return $this->success('操作成功','reload');
        }
        $goods_name = (new GoodsModel)->where('goods_id',$goods_id)->value('goods_name');
        $sku_name = '';
        if ($sku_id > 0){
            $sku_name = (new GoodsSkuModel)->where('sku_id',$sku_id)->value('sku_name');
        }

        $this->assign('goods_name',$goods_name);
        $this->assign('sku_name',$sku_name);
        $this->assign('user_id',$user_id);
        $this->assign('goods_id',$goods_id);
        $this->assign('sku_id',$sku_id);

        $hash_code = md5($user_id.'_1_'.$goods_id.'_'.$sku_id);
        $cloudNumber = $StockModel->where('hash_code',$hash_code)->value('goods_number');
        $hash_code = md5($user_id.'_2_'.$goods_id.'_'.$sku_id);
        $entityNumber = $StockModel->where('hash_code',$hash_code)->value('goods_number');
        $this->assign('cloudNumber',intval($cloudNumber));
        $this->assign('entityNumber',intval($entityNumber));
        return $this->fetch();
    }
}
