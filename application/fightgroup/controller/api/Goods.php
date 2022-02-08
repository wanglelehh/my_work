<?php

namespace app\fightgroup\controller\api;

use app\ApiController;

use app\fightgroup\model\FightGroupModel;
use app\fightgroup\model\FightGroupListModel;
use app\shop\model\GoodsModel;

/*------------------------------------------------------ */
//-- 拼团相关API
/*------------------------------------------------------ */

class Goods extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new FightGroupModel();
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    /*------------------------------------------------------ */
    public function getList()
    {
        $type = input('type','pging','trim');
        $time = time();
        $where[] = ['status', '=', 1];
        if ($type == 'pgwait'){
            $where[] = ['start_date', '>', $time];
        }else{
            $where[] = ['start_date', '<', $time];
            $where[] = ['end_date', '>', $time];
        }
        $this->sqlOrder = "sort_order DESC, fg_id DESC";
        $data = $this->getPageList($this->Model, $where, '*', 10);
        $GoodsModel = new GoodsModel();
        foreach ($data['list'] as $key => $_goods) {
            $goods = $GoodsModel->info($_goods['goods_id']);
            if ($goods['is_on_sale'] == 0) {//下架的商品显示
                continue;
            }

            $_goods['goods_id'] = $goods['goods_id'];
            $_goods['goods_name'] = $goods['goods_name'];
            $_goods['short_name'] = $goods['short_name'];
            $_goods['goods_thumb'] = $goods['goods_thumb'];
            $_goods['is_spec'] = $goods['is_spec'];
            $_goods['exp_price'] = explode('.', $_goods['show_price']);
            $_goods['market_price'] = $goods['market_price'];
            $_goods['shop_price'] = $goods['shop_price'];
            if ($type == 'pging'){
                $_goods['downDate'] = date('Y-m-d H:i:s',$_goods['end_date']);
            }else{
                $_goods['downDate'] = date('Y-m-d H:i:s',$_goods['start_date']);
            }
            $data['list'][$key] = $_goods;
        }
        return $this->success($data);
    }

    /*------------------------------------------------------ */
    //-- 获取详情
    /*------------------------------------------------------ */
    public function info()
    {
        $fg_id = input('fg_id', '', 'intval');
        if (empty($fg_id)) return $this->error('传参失败.');
        $data = $this->Model->info($fg_id);
        if (empty($data)) return $this->error('没有找到相关商品.');
        $data['shop_goods_comment'] = settings('shop_goods_comment');
        $data['show_stock_num'] = settings('shop_goods_show_stock_num');
        if (empty($this->userInfo['user_id']) == false) {//已登陆，查询用户是否有收藏此商品
            $data['goods']['is_collect'] = (new GoodsModel)->isCollect($data['goods_id'], $this->userInfo['user_id']);
        }
        return $this->success($data);

    }
    /*------------------------------------------------------ */
    //-- 正在拼团数据
    /*------------------------------------------------------ */
    public function getFGing()
    {
        $fg_id = input('fg_id', '', 'intval');
        if (empty($fg_id)) return $this->error('传参失败.');
        $data = $this->Model->getFGing($fg_id);
        return $this->success($data);
    }

    /*------------------------------------------------------ */
    //-- 参与拼团页面
    /*------------------------------------------------------ */
    public function fgjoin()
    {
        $join_id = input('join_id', '', 'intval');
        $data['fgJoinInfo'] = (new FightGroupListModel)->info($join_id);
        $data['fgInfo'] = $this->Model->info($data['fgJoinInfo']['fg_id']);
        $data['show_stock_num'] = settings('shop_goods_show_stock_num');
        return $this->success($data);
    }

    /*------------------------------------------------------ */
    //-- 获取分享拼团商品二维码
    /*------------------------------------------------------ */
    public function goodsCode()
    {
        $fg_id = input('fg_id', 0, 'intval');
        $file_path = config('config._upload_') . 'goods_qrcode/fg_' . $fg_id . '/';
        $file = $file_path . $this->userInfo['token'] . '.png';
        if (file_exists($file) == false) {
            include EXTEND_PATH . 'phpqrcode/phpqrcode.php';//引入PHP QR库文件
            $QRcode = new \phpqrcode\QRcode();
            $value = config('config.host_path') . url('fightgroup/index/info', ['fg_id' => $fg_id, 'share_token' => $this->userInfo['token']]);
            makeDir($file_path);
            $png = $QRcode::png($value, $file, "L", 10, 1, 2, true);
        }
        $return['file'] = config('config.host_path') . '/' . trim($file, '.');
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
}
