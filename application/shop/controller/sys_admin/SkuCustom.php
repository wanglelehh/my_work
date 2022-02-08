<?php
namespace app\shop\controller\sys_admin;
use app\AdminController;
use app\shop\model\SkuCustomModel;
use app\shop\model\GoodsModelModel;
/**
 * 自定义的商品规格
 * Class Index
 * @package app\store\controller
 */
class SkuCustom extends AdminController
{	
	/*------------------------------------------------------ */
	//-- 优先自动执行
	/*------------------------------------------------------ */ 
	public function initialize(){
        parent::initialize();
		$this->Model = new SkuCustomModel();
	}

    //*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index()
    {
        $model_id = input('model_id',0,'intval');
        if ($model_id < 1){
            return $this->error('缺少商品模型ID.');
        }
        $goodsModel = (new GoodsModelModel)->where('id',$model_id)->find();
        if (empty($goodsModel)){
            return $this->error('没有找到相关商品模型.');
        }
        $this->assign("goodsModel", $goodsModel->toArray());
        $where[] = ['model_id','=',$model_id];
        $where[] = ['supplyer_id','=', $this->supplyer_id * 1];
        $rows = $this->Model->where($where)->field('*,speid as pid')->order('speid ASC,sort_order DESC,id ASC')->select()->toArray();
        $list = returnRecArr($rows);
        if (empty($list)) $list[0] = array();
        $this->assign('list',$list);
        $this->assign('imgType',$this->Model->imgType);
        return $this->fetch('index');
    }
    //*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function asInfo($data)
    {
        if ($data['id'] < 1){
            $data['speid'] = input('speid',0,'intval');
            if ($data['speid'] < 1){
                $data['model_id'] = input('model_id',0,'intval');
            }
        }else{
            $data['link_spec'] = empty($data['link_spec'])?[]:json_decode($data['link_spec'],true);
        }
        if ($data['speid'] > 0){
            $psku = $this->Model->where('id',$data['speid'])->find();
            if (empty($psku)){
                return $this->error('没有找到相关规格.');
            }
            $this->assign('psku',$psku->toArray());
            $data['model_id'] = $psku['model_id'];

            $where[] = ['model_id','=', $data['model_id']];
            $where[] = ['speid','=', 0];
            $where[] = ['id','<>',$data['speid']];
            $modelRows = $this->Model->where($where)->select()->toArray();
            $modelList = [];
            foreach ($modelRows as $key=>$sku){
                $sku['all_val'] = $this->Model->where('speid',$sku['id'])->order('speid ASC,sort_order DESC,id ASC')->column('val','id');;
                $modelList[$sku['id']] = $sku;
            }
            $this->assign('modelList',$modelList);
        }
        $this->assign('imgType',$this->Model->imgType);
        return $data;
    }
    //*------------------------------------------------------ */
    //-- 添加前
    /*------------------------------------------------------ */
    public function beforeAdd($data){
        $link_spec = input('link_spec');
        $data['link_spec'] = json_encode($link_spec);
        return $data;
    }
    //*------------------------------------------------------ */
    //-- 添加后
    /*------------------------------------------------------ */
    public function afterAdd($data){
        $this->Model->cleanMemcache($data['model_id'],$this->supplyer_id);
        return $this->success('添加成功.', url('index',['model_id'=>$data['model_id']]));
    }
    //*------------------------------------------------------ */
    //-- 修改前
    /*------------------------------------------------------ */
    public function beforeEdit($data){
        $link_spec = input('link_spec');
        $data['link_spec'] = json_encode($link_spec);
        return $data;
    }
    //*------------------------------------------------------ */
    //-- 修改后
    /*------------------------------------------------------ */
    public function afterEdit($data){
        $this->Model->cleanMemcache($data['model_id'],$this->supplyer_id);
        return $this->success('修改成功.', url('index',['model_id'=>$data['model_id']]));
    }
	/*------------------------------------------------------ */
	//-- 获取商品规格
	/*------------------------------------------------------ */
	public function skuByCategory(){
		$skuModelId = input('skuModelId',0,'intval');
		$skuList = $this->Model->getRows($skuModelId);
		return $this->ajaxReturn($skuList);	
	}


}
