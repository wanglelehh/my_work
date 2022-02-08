<?php
namespace app\shop\controller\api;
use think\Db;
use app\ApiController;
use app\shop\model\GoodsModel;
use app\shop\model\OrderGoodsModel;
use app\shop\model\GoodsCommentModel;
use app\shop\model\GoodsCommentImagesModel;
use app\member\model\UsersModel;
use app\member\model\AvatarUserModel;
/*------------------------------------------------------ */
//-- 评论相关API
/*------------------------------------------------------ */
class Comment extends ApiController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	/*public function initialize(){
        parent::initialize();
    }*/
	/*------------------------------------------------------ */
	//-- 获取会员评论列表
	/*------------------------------------------------------ */
 	public function getList(){
		 $this->checkLogin();//验证登陆
		$this->Model = new OrderGoodsModel();
        $where[] = ['user_id','=',$this->userInfo['user_id']];
		$type = input('type','wait','trim');
     
        switch ($type){
            case 'wait'://待评论
               $where[] = ['is_evaluate','=',1];
                break;
            default://已评论
				$where[] = ['is_evaluate','=',2];
                break;
        }
        $data = $this->getPageList($this->Model, $where,'rec_id,pic,goods_id,goods_name,sku_name,shop_price,sale_price,is_evaluate',5);
        return $this->success($data);
	}
	
	/*------------------------------------------------------ */
	//-- 获取会员评论相关数据
	/*------------------------------------------------------ */
 	public function getInfo(){
		$this->checkLogin();//验证登陆
		$rec_id = input('rec_id',0,'intval');
		if ($rec_id < 1) return $this->error('传参失败.');
		$OrderGoodsModel = new OrderGoodsModel();
		$row = $OrderGoodsModel->find($rec_id);
		if ($row['user_id'] != $this->userInfo['user_id']){
			return $this->error('无权获取数据.');
		}
		$row['exp_price'] = explode('.',$row['sale_price']);
		if ($row['is_evaluate'] == 2){//如果已评论返回评论内容
			$GoodsCommentModel = new GoodsCommentModel();
			$where[] = ['order_id','=',$row['order_id']];
			$where[] = ['goods_id','=',$row['goods_id']];		
			$row['comment'] = $GoodsCommentModel->where($where)->find();
			if (empty($row['comment']) == false){
				$row['comment'] = $row['comment']->toArray();
				$row['_time'] = date('Y-m-d',$row['comment']['add_time']);
				$GoodsCommentImagesModel = new GoodsCommentImagesModel();
				$row['imgs'] = $GoodsCommentImagesModel->where('comment_id',$row['comment']['id'])->select()->toArray();
			}
		}
        return $this->success($row->toArray());
	}
	/*------------------------------------------------------ */
	//-- 提交评论
	/*------------------------------------------------------ */
 	public function post(){
		$this->checkLogin();//验证登陆
		$shop_goods_comment = settings('shop_goods_comment');
		if ($shop_goods_comment < 1){
			return $this->error('暂不开放评论.');
		}		
		$rec_id = input('rec_id',0,'intval');
		if ($rec_id < 1) return $this->error('传参失败.');
		$OrderGoodsModel = new OrderGoodsModel();		 
		$ogoods = $OrderGoodsModel->find($rec_id);
		if ($ogoods['user_id'] != $this->userInfo['user_id']){
			return $this->error('无权评论此商品.');
		}
		if ($ogoods['is_evaluate'] != 1){
			return $this->error('此商品不能评论.');
		}
		$inArr['content'] = input('content','','trim');
		if (empty($inArr['content'])){
			return $this->error('请填写评论.');
		}
		if (strlen($inArr['content']) < 15){
			return $this->error('评论长度不能小于15.');
		}
		Db::startTrans();//启动事务
		//更新订单商品为已评论
		$OrderGoodsModel = new OrderGoodsModel();
		$res = $OrderGoodsModel->where('rec_id',$rec_id)->update(['is_evaluate'=>2]);
		if ($res < 1){
			Db::rollback();// 回滚事务
            return $this->error('未知原因，写入失败-1.');
		}
		$imgfile = input('fileList');
		
		//写入评论
		$inArr['rec_id'] = $rec_id;
		$inArr['type'] = 'goods';
		$inArr['goods_id'] = $ogoods['goods_id'];
		if (empty($imgfile) == false){
			$inArr['is_imgs'] = 1;
		}
		$inArr['sku_val'] = $ogoods['sku_val'];
		$inArr['by_name'] = $ogoods['goods_name'];
		$inArr['order_id'] = $ogoods['order_id'];
		$inArr['user_id'] = $this->userInfo['user_id'];
		$inArr['add_time'] = time();
		$GoodsCommentModel = new GoodsCommentModel();
		$res = $GoodsCommentModel->save($inArr);
		if ($res < 1){
			Db::rollback();// 回滚事务
            return $this->error('未知原因，写入失败-2.');
		}
		$comment_id = $GoodsCommentModel->id;
		//处理图片		
		if (empty($imgfile) == false){
			$file_path = config('config._upload_').'comment/'.date('Ymd').'/';
			makeDir($file_path);
            $imgfile = explode(',',$imgfile);
            $GoodsCommentImagesModel = new GoodsCommentImagesModel();
            foreach ($imgfile as $file){
                $file_name = copyFile($file,$file_path);
                $imgInArr['comment_id'] = $comment_id;
				$imgInArr['image'] = trim($file_name,'.');
				$res = $GoodsCommentImagesModel->create($imgInArr);
                if ($res->id < 1){
					@unlink($file_name);
					Db::rollback();// 回滚事务
					return $this->error('未知原因，写入失败-3.');
				}				
			}
		}
		 Db::commit();// 提交事务
		$this->success('评论成功.');
	}
	/*------------------------------------------------------ */
	//-- 评论列表，商品调用
	/*------------------------------------------------------ */
 	public function getListByGoods(){
		$goods_id = input('goods_id',0,'intval');
		$limit = input('limit',6,'intval');
		$goodsCid = (new GoodsModel)->where('goods_id',$goods_id)->value('cid');
		$GoodsCommentModel = new GoodsCommentModel();
		$GoodsCommentImagesModel = new GoodsCommentImagesModel();
		$where['and'][] = "goods_id = '".$goods_id."' AND type = 'goods' AND status = 2";
		$where['or'][] = "cat_id = '".$goodsCid."' AND type = 'goods_category'";
		$this->sqlOrder = 'goods_id DESC,id DESC';
		$data = $this->getPageList($GoodsCommentModel, $where,'id,user_id,avatar_user,content,sku_val,sku_name,add_time',$limit);
		$UsersModel = new UsersModel();
        $AvatarUserModel = new AvatarUserModel();
		foreach ($data['list'] as $key=>$row){
            $row['imgs'] = $GoodsCommentImagesModel->where('comment_id',$row['id'])->column('image');
            if ($row['avatar_user'] > 0){
                $avatarUser = $AvatarUserModel->find($row['avatar_user']);
                $row['user_name'] = $avatarUser['user_name'];
                $row['headimgurl'] = $avatarUser['headimgurl'];
            }else{
                $userInfo = $UsersModel->where('user_id',$row['user_id'])->field('nick_name,headimgurl')->find();
                $row['user_name'] = $userInfo['nick_name'];
                $row['headimgurl'] = $userInfo['headimgurl'];
            }
            unset($row['avatar_user'],$row['user_id']);
			$row['_time'] = date('Y-m-d',$row['add_time']);
			$data['list'][$key] = $row;
		}
        return $this->success($data);
	}
   
}
