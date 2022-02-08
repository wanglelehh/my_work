<?php

namespace app\shop\controller\api;
use think\Db;
use app\ApiController;
use app\shop\model\AfterSaleModel;
use app\shop\model\OrderModel;
use app\shop\model\OrderGoodsModel;
use app\shop\model\ShippingModel;
/*------------------------------------------------------ */
//-- 售后相关
/*------------------------------------------------------ */

class AfterSale extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->checkLogin();//验证登陆
        $this->Model = new AfterSaleModel();
    }
    /*------------------------------------------------------ */
    //-- 获取订单商品信息
    /*------------------------------------------------------ */
    public function orderGoods()
    {
        $rec_id = input('rec_id',0,'intval');
        if ($rec_id < 1){
            return $this->error('传参失败.');
        }
        $OrderGoodsModel = new OrderGoodsModel();
        $ogInfo = $OrderGoodsModel->find($rec_id);
        if (empty($ogInfo)){
            return $this->error('没有找到相关订单商品.');
        }
        unset($ogInfo['settle_price']);
        $ogInfo['return_num'] = $ogInfo['goods_number'] - $ogInfo['after_sale_num'];
        if ($ogInfo['return_num'] < 1){
            return $this->error('此商品已全部申请售后，不能继续操作.');
        }
        $orderInfo = (new OrderModel)->info($ogInfo['order_id']);
        if ($orderInfo['user_id'] != $this->userInfo['user_id']){
            return $this->error('请求错误.');
        }
        if ($orderInfo['isAfterSale'] == 0){
            return $this->error('此订单不能申请售后，如果有问题请联系客服.');
        }
        $ogInfo = $ogInfo->toArray();

        //红包处理
        $ogInfo['one_bonus_price'] = bcsub($ogInfo['sale_price'],$ogInfo['bonus_after_price'],2);//每件商品占用红包金额
        $ogInfo['last_bonus_price'] = bcsub($ogInfo['usd_bonus_price'],bcmul($ogInfo['after_sale_num'],$ogInfo['one_bonus_price'],2),2);//剩余总红包金额
        //红包处理end

        //折扣处理
        $ogInfo['one_discount'] = $ogInfo['diy_discount'];
        if ($ogInfo['goods_number'] > 1){//如果购买商品多于1个时，计算折扣金额平摊额
            $ogInfo['one_discount'] = bcdiv($ogInfo['diy_discount'],$ogInfo['goods_number'],2);//每件商品占用折扣
        }
        $ogInfo['last_discount'] = bcsub($ogInfo['diy_discount'],bcmul($ogInfo['after_sale_num'],$ogInfo['one_discount'],2),2);//剩余总折扣金额
        //折扣处理end

        return $this->success($ogInfo);
    }
    /*------------------------------------------------------ */
    //-- 提交售申请
    /*------------------------------------------------------ */
    public function add()
    {
        $type = input('type',0,'intval');
        if ($type < 1){
            return $this->error('请选择售后类型.');
        }
        $inArr['rec_id'] = input('rec_id',0,'intval');
        if ($inArr['rec_id'] < 1){
            return $this->error('传参失败.');
        }

        if ($type == 1){
            $inArr['type'] = 'return_goods';
        }elseif ($type == 2){
            $inArr['type'] = 'change_goods';
        }else{
            $inArr['type'] = 'return_money';
        }
        $inArr['goods_number'] = input('goods_number',0,'intval');
        $inArr['return_desc'] = input('return_desc','','trim');

        $OrderModel = new OrderModel();
        $OrderGoodsModel = new OrderGoodsModel();
        $goods = $OrderGoodsModel->find($inArr['rec_id']);
        if (empty($goods)){
            return $this->error('没有找到相关商品.');
        }
        $last_num = $goods['goods_number'] -  $goods['after_sale_num'];
        if ($last_num < 1){
            return $this->error('此商品已全部申请售后，不能再申请.');
        }
        if ($inArr['goods_number'] > $last_num){
            return $this->error('商品申请售后数量大于可申请数量.');
        }
        $orderInfo = $OrderModel->info($goods['order_id']);
        if ($orderInfo['isAfterSale'] == 0){
            return $this->error('此订单不能申请售后，请联系客服.');
        }
        //处理图片
        $file_path = config('config._upload_').'aftersale/'.date('Ymd').'/';
        makeDir($file_path);
        $fileList = input('fileList','','trim');
        if (empty($fileList) == false){
            $fileList = explode(',',$fileList);
            $imgs = [];
            foreach ($fileList as $file){
                $imgs[] = copyFile($file,$file_path);
            }
            $inArr['imgs'] = join(',',$imgs);
        }

        if ($inArr['type'] != 'change_goods'){
            $inArr['return_settle_money'] = $goods['settle_price'] * $inArr['goods_number'];
            //计算实际可退金额
            $usd_bonus_price = $goods['usd_bonus_price'];//总数
            $one_bonus_price = 0;
            if ($goods['bonus_after_price'] > 0){
                $one_bonus_price = bcsub($goods['sale_price'],$goods['bonus_after_price'],2);
            }
            $return_bouns_money = $goods['after_sale_num'] * $one_bonus_price;
            if ($inArr['goods_number'] == $last_num){
                $inArr['return_money'] = $goods['sale_price'] * $last_num - ($usd_bonus_price - $return_bouns_money);
            }else{
                $inArr['return_money'] = ($goods['sale_price'] - $one_bonus_price) * $inArr['goods_number'];
            }
            //end
        }

        $inArr['user_id']  = $this->userInfo['user_id'];
        $inArr['add_time'] = $inArr['update_time'] = time();
        $inArr['goods_id'] = $goods['goods_id'];
        $inArr['goods_sn'] = $goods['goods_sn'];
        $inArr['supplyer_id'] = $goods['supplyer_id'];
        $inArr['order_id'] = $orderInfo['order_id'];
        $inArr['order_sn'] = $orderInfo['order_sn'];
        $inArr['as_sn']    = $this->Model->getSn();
        Db::startTrans();//启动事务
        $res = $this->Model->save($inArr);
        if ($res < 1){
            Db::rollback();// 回滚事务
            return $this->error('提交处理失败-1，请重试.');
        }
        $as_id = $this->Model->as_id;
        $upData['after_sale_num'] = ['INC',$inArr['goods_number']];
        $res = $OrderGoodsModel->where('rec_id',$inArr['rec_id'])->update($upData);
        if ($res < 1){
            Db::rollback();// 回滚事务
            return $this->error('提交处理失败-2，请重试.');
        }
        if ($orderInfo['is_after_sale'] != 1){//订单非售后中，执行
            unset($upData);
            $upData['is_after_sale'] = 1;
            $upData['update_time'] = time();
            $res = $OrderModel->where('order_id',$orderInfo['order_id'])->update($upData);
            if ($res < 1){
                Db::rollback();// 回滚事务
                return $this->error('提交处理失败-3，请重试.');
            }
            $OrderModel->_log($orderInfo, '用户申请售后');
            $OrderModel->cleanMemcache($orderInfo['order_id']);
        }
        Db::commit();// 提交事务
        $this->Model->_log($as_id, '用户申请售后',$this->Model->status[0],'user',$this->userInfo['user_id']);
        return $this->success('提交成功，我们将尽快处理.');
    }
    /*------------------------------------------------------ */
    //-- 售后详情
    /*------------------------------------------------------ */
    public function info(){
        $id = input('id',0,'intval');
        if ($id < 1) return $this->error('传参错误.');
        $AfterSaleModel = new AfterSaleModel();
        $data = $AfterSaleModel->find($id);
        if (empty($data)) return $this->error('没有找到相关信息.');
        if ($data['user_id'] != $this->userInfo['user_id']){
            return $this->error('你没有权限查相关信息.');
        }
        $data = $data->toArray();
        $goods = (new OrderGoodsModel)->find($data['rec_id']);
        $goods['exp_prcie'] = explode('.',$goods['sale_price']);
        $data['goods'] = $goods;
        $data['imgs'] = explode(',',$data['imgs']);
        $data['ostatus'] = $AfterSaleModel->status[$data['status']];
        $data['astype'] = $AfterSaleModel->type[$data['type']];
        if ($data['status'] >= 2){
            if ($data['supplyer_id']>0){
                $returnInfo = (new \app\supplyer\model\SupplyerModel)->find($data['supplyer_id'])->toArray();
            }else{
                $settings = settings();
                $returnInfo['return_consignee'] = $settings['return_consignee'];
                $returnInfo['return_mobile'] = $settings['return_mobile'];
                $returnInfo['return_address'] = $settings['return_address'];
                $returnInfo['return_desc'] = $settings['return_desc'];
            }
            $data['returnInfo'] = $returnInfo;

            $where[] = ['is_zt','=',0];
            $where[] = ['status','=',1];
            $where[] = ['is_front','=',1];
            $data['shippingList'] = (new ShippingModel)->where($where)->select()->toArray();
        }
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 提交售后退货物流信息
    /*------------------------------------------------------ */
    public function shipping()
    {
        $as_id = input('as_id',0,'intval');
        if ($as_id < 1) return $this->error('传参错误.');
        $shipping_id = input('shipping_id',0,'intval');
        $upData['shipping_no'] = input('shipping_no','','trim');
        if ($shipping_id < 1){
            return $this->error('请选择快递公司.');
        }
        if (empty($upData['shipping_no'])){
            return $this->error('请输入快递单号.');
        }
        $asInfo = $this->Model->find($as_id);
        if (empty($asInfo)){
            return $this->error('没有找到相关售后信息.');
        }
        if ($asInfo['user_id'] != $this->userInfo['user_id']){
            return $this->error('你无权操作.');
        }
        if ($asInfo['status'] != 2){
            return $this->error('售后状态不正确，无法操作.');
        }
        $shippingList = (new ShippingModel)->getRows();
        if (empty($shippingList[$shipping_id])){
            return $this->error('获取快递公司信息失败.');
        }
        $upData['shipping_name'] = $shippingList[$shipping_id]['shipping_name'];
        $upData['shipping_id'] = $shipping_id;
        $upData['status'] = 3;
        $upData['shipping_time'] = time();
        $upData['update_time'] = time();
        $res = $this->Model->where('as_id',$as_id)->update($upData);
        if ($res < 1){
            return $this->error('未知错误，处理失败，请重试。');
        }
        return $this->success('提交成功.');
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    /*------------------------------------------------------ */
    public function getList(){
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $type = input('type','','trim');
        switch ($type){
            case 'all':
                break;
            case 'waitCheck':
                $where[] = ['status', '=', 0];
                break;
            case 'waitShipping':
                $where[] = ['status', '=', 2];
                break;
            case 'waitSign':
                $where[] = ['status', '=', 3];
                break;
            case 'success':
                $where[] = ['status', '=', 9];
                break;
            default:
                break;
        }
        $data = $this->getPageList($this->Model, $where,'*',7);
        $OrderGoodsModel = new OrderGoodsModel();
        foreach ($data['list'] as $key=>$row){
            $row['goods'] = $OrderGoodsModel->find($row['rec_id']);
            unset($row['goods']['settle_price']);
            $row['goods']['exp_price'] = explode('.',$row['goods']['sale_price']);
            $row['add_time'] = dateTpl($row['add_time'],'Y-m-d H:i:s',true);
            $row['ostatus'] = $this->Model->status[$row['status']];
            $row['type_val'] = $this->Model->type[$row['type']];
            $data['list'][$key] = $row;
        }
        return $this->success($data);
    }
}
