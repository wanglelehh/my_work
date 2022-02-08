<?php

namespace app\luckdraw\controller\sys_admin;

use app\AdminController;
use app\luckdraw\model\LuckdrawModel;
use app\luckdraw\model\LuckdrawPrizeModel;
use app\luckdraw\model\LuckdrawLogModel;

use app\mainadmin\model\SettingsModel;
/**
 * 大转盘
 * Class Turntable
 */
class Turntable extends AdminController
{
    //*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new LuckdrawModel();
    }
    /*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index(){
        $row = $this->Model->where('type','turntable')->find();
        if (empty($row)){
            $inArr['type'] = 'turntable';
            $this->Model->save($inArr);
            $row = $this->Model->where('type','turntable')->find();
        }
        $row = $row->toArray();
        $this->assign('row',$row);
        $this->assign('integral_add_luckdrawnum',settings('integral_add_luckdrawnum'));
        $this->assign('prize_type',$this->Model->prize_type);
        $prize_list = [];
        $prize_list[] = [];
        $this->assign('prize_list',$prize_list);
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 修改
    /*------------------------------------------------------ */
    public function save(){
        $luck_id = input('luck_id',0,'intval');
        if ($luck_id < 1){
            return $this->error('抽奖ID传参失败.');
        }
        $setData['integral_add_luckdrawnum'] = input('integral_add_luckdrawnum',0,'intval');
        $res = (new SettingsModel)->editSave($setData);
        if ($res == false) return $this->error('保存兑换抽奖次数');

        $upArr['status'] = input('status',0,'intval');
        $upArr['rule'] = input('rule','','trim');
        $upArr['bg_img'] = input('bg_img','','trim');
        $upArr['bg_color'] = input('bg_color','','trim');
        $upArr['turntable_bg'] = input('turntable_bg','','trim');
        $upArr['turntable_btn'] = input('turntable_btn','','trim');
        $this->Model->where('luck_id',$luck_id)->update($upArr);
        return $this->success('修改成功.');
    }
    /*------------------------------------------------------ */
    //-- 获取奖项目列表
    /*------------------------------------------------------ */
    public function getPrize(){
        $luck_id = input('luck_id',0,'intval');
        $data['prize_list'] = (new LuckdrawPrizeModel)->where('luck_id',$luck_id)->select()->toArray();
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 添加奖项
    /*------------------------------------------------------ */
    public function addPrize(){
        $this->assign('prize_type',$this->Model->prize_type);
        $luck_id = input('luck_id',0,'intval');
        if ($luck_id < 1){
            return $this->error('抽奖ID传参失败.');
        }
        $LuckdrawPrizeModel = new LuckdrawPrizeModel();
        $count = $LuckdrawPrizeModel->where('luck_id',$luck_id)->count();
        if ($count >= 8){
            return $this->error('奖项最多只能设置'.$count.'个.');
        }
        $row = $LuckdrawPrizeModel->getField();
        $row['luck_id'] = $luck_id;
        $this->assign('row',$row);
        return $this->fetch('prize');
    }
    /*------------------------------------------------------ */
    //-- 修改奖项
    /*------------------------------------------------------ */
    public function editPrize(){
        $prize_id = input('prize_id',0,'intval');
        if ($prize_id < 1){
            return $this->error('奖项ID传参失败.');
        }
        $row = (new LuckdrawPrizeModel)->find($prize_id);
        if (empty($row)){
            return $this->error('没有找到相关奖项.');
        }

        if ($row['prize_type'] == 'entity'){
            $goods = (new \app\shop\model\GoodsModel)->where('goods_id',$row['relation_val'])->field('goods_id,goods_name')->find();
            $this->assign('goods',$goods);
        }elseif($row['prize_type'] == 'bonus') {
            $bonus = (new \app\shop\model\BonusModel)->where('type_id',$row['relation_val'])->field('type_id,type_name,type_money')->find();
            $this->assign('bonus',$bonus);
        }
        $this->assign('prize_type',$this->Model->prize_type);
        $this->assign('row',$row->toArray());
        return $this->fetch('prize');
    }
    /*------------------------------------------------------ */
    //-- 保存奖项
    /*------------------------------------------------------ */
    public function savePrize(){
        $prize_id = input('prize_id',0,'intval');
        $data['luck_id'] = input('luck_id',0,'intval');
        $data['prize_name'] = input('prize_name','','trim');
        $data['prize_img'] = input('prize_img','','trim');
        $data['prize_num'] = input('prize_num',0,'intval');
        $data['prize_pre'] = input('prize_pre',0,'float');
        $data['prize_limit'] = input('prize_limit',0,'intval');
        $data['prize_type'] = input('prize_type','','trim');
        if (empty($data['luck_id'])){
            return $this->error('抽奖ID传参失败.');
        }
        if (empty($data['prize_name'])){
            return $this->error('请输入奖品名称.');
        }
        if ($data['prize_num'] == 0){
            return $this->error('奖品数量不能为0.');
        }
        if ($data['prize_type'] == 'entity'){
            $goods_id = input('goods_id',0,'intval');
            if ($goods_id < 1){
                return $this->error('请选择商品.');
            }
            $data['relation_val'] = $goods_id;
        }elseif($data['prize_type'] == 'bonus'){
            $bonus_id = input('bonus_id',0,'intval');
            if ($bonus_id < 1){
                return $this->error('选择优惠券.');
            }
            $data['relation_val'] = $bonus_id;
        }elseif($data['prize_type'] == 'integral'){
            $give_integral = input('give_integral',0,'intval');
            if ($give_integral < 1){
                return $this->error('请输入赠送积分.');
            }
            $data['relation_val'] = $give_integral;
        }elseif($data['prize_type'] == 'luckdrawnum'){
            $give_luckdrawnum = input('give_luckdrawnum',0,'intval');
            if ($give_luckdrawnum < 1){
                return $this->error('请填写赠送抽奖次数.');
            }
            $data['relation_val'] = $give_luckdrawnum;
        }else{
            $data['relation_val'] = 0;
        }
        $data['update_time'] = time();
        $LuckdrawPrizeModel = new LuckdrawPrizeModel();
        if ($prize_id > 0){
            $res = $LuckdrawPrizeModel->where('prize_id',$prize_id)->update($data);
            if ($res < 1){
                return $this->error('修改失败.');
            }
            return $this->success('修改成功.');
        }else{
            $count = $LuckdrawPrizeModel->where('luck_id',$data['luck_id'])->count();
            if ($count >= 8){
                return $this->error('奖项最多只能设置'.$count.'个.');
            }
            $data['add_time'] = time();
            $res = $LuckdrawPrizeModel->save($data);
            if ($res < 1){
                return $this->error('添加失败.');
            }
            return $this->success('添加成功.');

        }
    }
    /*------------------------------------------------------ */
    //-- 中奖记录
    /*------------------------------------------------------ */
    public function getLogList()
    {
        $luck_id = input('luck_id',0,'intval');
        $prize_type = input('prize_type','','trim');
        $where[] = ['luck_id','=',$luck_id];
        if (empty($prize_type) == false){
            $where[] = ['prize_type','=',$prize_type];
        }
        $LuckdrawLogModel = new LuckdrawLogModel();
        $data = $this->getPageList($LuckdrawLogModel,$where);
        foreach($data['list'] as $key=>$row){
            $row['user_name'] = userInfo($row['user_id']);
            $row['add_time'] = dateTpl($row['add_time']);
            if ($row['prize_type'] == 'entity'){
                if ($row['status'] == 0){
                    $row['_status'] = '待领奖';
                }else{
                    $row['_status'] = '已领奖';
                }
            }else{
                $row['_status'] = '已发放';
            }

            $row['prize_type'] = $this->Model->prize_type[$row['prize_type']];
            $data['list'][$key] = $row;
        }
        $AjaxPage = new \lib\AjaxPage($data['total_count'],$data['page_size'],[]);
        $data['page_content'] = $AjaxPage->show();
        return $this->success($data);
    }
}
