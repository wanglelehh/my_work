<?php
namespace app\shop\controller\api;
use app\ApiController;
use app\favour\controller\sys_admin\FavourGoods;
use app\member\model\UsersModel;
use app\shop\model\CartModel;
use app\shop\model\GoodsModel;
use app\weixin\model\MiniModel;
use app\shop\model\BonusModel;

/*------------------------------------------------------ */
//-- 商品相关API
/*------------------------------------------------------ */
class Goods extends ApiController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new GoodsModel();
    }
	/*------------------------------------------------------ */
    //-- 获取商品列表
    /*------------------------------------------------------ */
    public function getList(){
        $this->Model->autoSale();//自动上下架处理
        $where[] = ['is_delete','=',0];
        $where[] = ['is_alone_sale','=',1];
        $where[] = ['isputaway','=',1];

        $search['keyword'] =  input('keyword','','trim');
        if (empty($search['keyword']) == false){
            $where['and'][] = "( goods_name like '%".$search['keyword']."%')  OR ( keywords like '%".$search['keyword']."%')";
        }
        
        $search['cateId'] = input('cateId',0,'intval');
        if ($search['cateId'] > 0){
            $classList = $this->Model->getClassList();
            $where[] = ['cid','in',$classList[$search['cateId']]['children']];
        }
        $search['brand_id'] = input('brand_id',0,'intval');
        if ($search['brand_id'] > 0 ){
            $where[] = ['brand_id','=',$search['brand_id']];
        }

        $search['ids'] = input('ids','','trim');
        if (empty($search['ids']) == false){
            $where[] = ['goods_id','in',$search['ids']];
        }
        
        $sqlOrder = input('order','','trim');
        $sort_by = strtoupper(input('sort','DESC','trim'));
        if (in_array(strtoupper($sort_by), array('DESC', 'ASC')) == false) {
            $sort_by = 'DESC';
        }
        switch ($sqlOrder){
            case 'new':
                $this->sqlOrder = "is_new DESC,goods_id DESC";
                break;
            case 'sales':
                $this->sqlOrder = "virtual_sale $sort_by,goods_id DESC";
                break;
            case 'price':
                $this->sqlOrder = "shop_price $sort_by,goods_id DESC";
                break;
            default:
                $this->sqlOrder = "sort_order $sort_by,virtual_sale $sort_by,virtual_collect $sort_by,is_best $sort_by,goods_id DESC";
                break;
        }
        $page_size = input("page_size/d",10);
        $data = $this->getPageList($this->Model, $where,'goods_id',$page_size);
        $show_stock_num = settings('shop_goods_show_stock_num');
        $show_market_price = settings('shop_goods_show_market_price');
        foreach ($data['list'] as $key=>$goods){
            $_goods = [];
            $goods = $this->Model->info($goods['goods_id']);
            $_goods['goods_id'] = $goods['goods_id'];
            $_goods['goods_name'] = $goods['goods_name'];
            $_goods['short_name'] = $goods['short_name'];
            if (empty($_goods['short_name'])){
                $_goods['short_name'] = $goods['goods_name'];
            }
            $_goods['goods_name'] = $goods['goods_name'];
            $_goods['is_spec'] = $goods['is_spec'];
            $_goods['exp_price'] = $goods['exp_price'];
            $_goods['sale_price'] = $goods['sale_price'];
            $_goods['now_price'] = $goods['sale_price'];
            $_goods['market_price'] = $goods['market_price'];
            $_goods['sale_count'] = $goods['sale_count'];
            $_goods['collect_count'] = $goods['collect_count'];
            $_goods['goods_thumb'] = $goods['goods_thumb'];
            $_goods['goods_number'] = $goods['goods_number'];
            $_goods['show_stock_num'] = $show_stock_num;
            $_goods['show_market_price'] = $show_market_price;
            $data['list'][$key] = $_goods;
        }
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取商品信息
    /*------------------------------------------------------ */
    public function getGoodsInfo()
    {
        $goods_id = input('goods_id',0,'intval');
        if ($goods_id < 1){
            return $this->error('请传递商品ID.');
        }
        $data = $this->Model->info($goods_id);
        $data['show_sale_num'] = settings('shop_goods_show_sale_num');
        $data['show_collect_num'] = settings('shop_goods_show_collect_num');
        $data['show_market_price'] = settings('shop_goods_show_market_price');
        $data['show_stock_num'] = settings('shop_goods_show_stock_num');
        $data['shop_goods_comment'] = settings('shop_goods_comment');
        if (empty($data)){
            return $this->error('没有找到相关商品.');
        }
        $data['is_collect'] = 0;
        if (empty($this->userInfo['user_id']) == false){//已登陆， 查询用户是否有收藏此商品
            $data['is_collect'] = $this->Model->isCollect($goods_id,$this->userInfo['user_id']);
        }
//        $data['user_token'] = $this->userInfo['token'] ? $this->userInfo['token'] : '';
        return $this->success($data);
    }



    /*------------------------------------------------------ */
    //-- 获取商品品牌列表
    /*------------------------------------------------------ */
    public function getBrandList()
    {
        $cid = input('cid',0,'intval');
        $return['list'] = $this->Model->getBrandList($cid);
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }



    /*------------------------------------------------------ */
    //-- 添加/取消收藏商品
    /*------------------------------------------------------ */
    public function collect()
    {
        $this->checkLogin();//验证登陆
        $goods_id = input('goods_id',0,'intval');
        if ($goods_id < 1) return $this->error('传参失败.');
        $GoodsCollectModel = new \app\shop\model\GoodsCollectModel();
        $where['goods_id'] = $goods_id;
        $where['user_id'] = $this->userInfo['user_id'];
        $collect = $GoodsCollectModel->where($where)->find();
        if (empty($collect) == false){//存在,更新状态
            $upData['status'] = $collect['status'] == 1 ? 0 : 1;
			$upData['update_time'] = time();
            $res = $GoodsCollectModel->where($where)->update($upData);
        }else{
            $inData['status'] = 1;
            $inData['goods_id'] = $goods_id;
            $inData['user_id'] = $this->userInfo['user_id'];
            $inData['add_time'] = time();
			$inData['update_time'] = time();
            $res = $GoodsCollectModel->save($inData);
        }
        if ($res < 1) return $this->error('收藏商品失败.');
        return $this->success();
    }

	/*------------------------------------------------------ */
    //-- 获取商品收藏列表
    /*------------------------------------------------------ */
    public function getCollectlist()
    {
		$this->checkLogin();//验证登陆
		$GoodsCollectModel = new \app\shop\model\GoodsCollectModel();
        $where['user_id'] = $this->userInfo['user_id'];
        $where['status'] = 1;
		$rows = $GoodsCollectModel->where($where)->order('update_time DESC')->select();
        $return['list'] = [];
        $return['count'] = 0;
		foreach ($rows as $row){
			$goods = $this->Model->info($row['goods_id']);
			if ($goods['is_delete'] == 1){
				continue;
			}
            $_goods['goods_id'] = $goods['goods_id'];
            $_goods['goods_name'] = $goods['goods_name'];
            $_goods['short_name'] = $goods['short_name'];
            $_goods['is_spec'] = $goods['is_spec'];
            $_goods['exp_price'] = $goods['exp_price'];
            $_goods['sale_price'] = $goods['sale_price'];
            $_goods['market_price'] = $goods['market_price'];
            $_goods['sale_count'] = $goods['sale_count'];
            $_goods['collect_count'] = $goods['collect_count'];
            $_goods['goods_thumb'] = $goods['goods_thumb'];
            $_goods['is_promote'] = $goods['is_promote'];
            $return['list'][] = $_goods;
			$return['count'] += 1;
		}
        return $this->success($return);
	}
    /*------------------------------------------------------ */
    //-- 获取商品详情
    /*------------------------------------------------------ */
    public function info(){
        $goods_id = input('id',0,'intval');
        if ($goods_id < 1) return $this->error('传参错误.');
        $goods = $this->Model->info($goods_id);
        $list['title'] = $goods['goods_name'];
          
        $web_path = config('config.host_path');
        $goods['m_goods_desc'] = preg_replace('/<img src=\"/', '<img style="width:100%;height:auto;" src="' .$web_path,$goods['m_goods_desc']);
        $list['goods'] = $goods;
        $list['imgsList'] = $this->Model->getImgsList($goods_id);
        $list['skuImgs'] = $this->Model->getImgsList($goods_id,true,true);
        $list['isCollect'] = $this->Model->isCollect($goods_id,$this->userInfo['user_id']);     
        //获取sku图片
        
        //获取购物车信息
        $CartModel = new CartModel();
        $list['cartInfo'] = $CartModel->getCartInfo();
        
        $return['code'] = 1;
        $return['list'] = $list;
        return $this->ajaxReturn($return);
    }
    /*------------------------------------------------------ */
    //-- 获取搜索词，热搜之类的
    /*------------------------------------------------------ */
    public function get_keyword(){
        $return['default_keyword'] = settings('shop_index_search_text');
        $return['searchKeys'] = $this->Model->searchKeys();
        $return['hot_search'] = explode(' ',settings('hot_search'));
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }

    /*------------------------------------------------------ */
    //-- 检查商品活动
    /*------------------------------------------------------ */
    public function checkActivity(){
        if (class_exists('app\favour\model\FavourGoodsModel') == false) {
            return $this->success();
        }
        $goods_id = input('goods_id',0,'intval');
        $sku_id = input('sku_id',0,'intval');
        $goods = (new \app\favour\model\FavourGoodsModel)->checkIsFavour($goods_id,$sku_id);
        return $this->success($goods);
    }


    /*------------------------------------------------------ */
    //-- 商品分享信息
    /*------------------------------------------------------ */
    public function shareInfo()
    {
        $is_wxmp = input('is_wxmp',0,'intval');
        $goods_id = input('goods_id',0,'intval');
        if ($this->userInfo['user_id'] < 1){
            return $this->error('请登录后再操作.');
        }
        if ($this->userInfo['role']['is_share'] == 0){
            return $this->error('请升级身份后再操作.');
        }
        $UsersModel = new UsersModel();
        $data['share_qrcode'] = $is_wxmp == 0 ? $UsersModel->getMyQrCode('/pages/shop/goods/info?goods_id='.$goods_id) : $UsersModel->getUserMiniQrcode('pages/shop/goods/info',$goods_id);
        $data['share_qrcode'] = trim($data['share_qrcode'],'.');
        $data['share_headimgurl'] = $UsersModel->getHeadImg($this->userInfo['user_id'],$this->userInfo['headimgurl']);
        $data['share_headimgurl'] = trim($data['share_headimgurl'],'.');
        $data['share_nick_name'] = $this->userInfo['nick_name'];
        $data['share_token'] = $this->userInfo['token'];
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取商品分类
    /*------------------------------------------------------ */
    public function getCateList()
    {
        $list = returnRecArr($this->Model->getClassList(false));
        foreach ($list as $key=>$rows){
            $newRow = [];
            foreach ($rows as $row){
                $newRow[] = $row;
            }
            $list[$key] = $newRow;
        }
        $data['list']= $list;
        return $this->success($data);
    }
}
