<?php
namespace app\shop\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- sku类目
/*------------------------------------------------------ */
class SkuCustomModel extends BaseModel
{
	protected $table = 'shop_goods_sku_custom';
	public  $pk = 'id';
	protected $mkey = 'goods_sku_custom_mkey_';
    public $imgType = ['0'=>'不使用图片','1'=>'选项图片','2'=>'商品图片（组合）'];
	/*------------------------------------------------------ */
	//-- 清除缓存
    //-- $model_id int 模型ID
    //-- $supplyer_id  int 供应商ID
	/*------------------------------------------------------ */ 
	public function cleanMemcache($model_id = 0,$supplyer_id = 0){
		Cache::rm($this->mkey.$model_id.'_'.$supplyer_id);
        Cache::rm($this->mkey.'_SkuName');
	}

    /*------------------------------------------------------ */
	//-- 根据模型ID获取相关sku类目
    //-- $model_id int 模型ID
    //-- $supplyer_id  int 供应商ID
	/*------------------------------------------------------ */
    public function getRows($model_id = 0,$supplyer_id = 0){
		$_list = Cache::get($this->mkey.$model_id.'_'.$supplyer_id);
		if ($_list['data']) return $_list;
		$_list['data'] = array();
        $where[] = ['model_id','=',$model_id];
        $where[] = ['speid','=',0];
        $where[] = ['status','=',1];
		$where[] = ['supplyer_id','=',$supplyer_id];
		$rows = $this->field('id,val as name,img_type')->where($where)->order('sort_order DESC,id ASC')->select()->toArray();
		foreach ($rows as $row){
			$row['custom'] = true;
			$whereb = [];
			$whereb[] = ['speid','=',$row['id']];
			$whereb[] = ['status','=',1];
			$row['all_val'] = $this->field('id as `key`,val,status,code,link_spec')->where($whereb)->order('speid ASC,sort_order DESC,id ASC')->select()->toArray();
			foreach ($row['all_val'] as $key=>$val){
                $link_spec = json_decode($val['link_spec'],true);
                $specs = [];
                foreach ($link_spec as $spec){
                    $specs = array_merge($specs,$spec);
                }
                $val['link_spec'] = $specs;
                $row['all_val'][$key] = $val;
            }
			$_list['data'][] = $row;
		}
		Cache::set($this->mkey.$model_id.'_'.$supplyer_id,$_list,3600);
		return $_list;
	}
    /*------------------------------------------------------ */
    //-- 根据规格值值获取相应的规格名称
    /*------------------------------------------------------ */
    public function getSkuName($sub_goods = array()){
        if (empty($sub_goods)) return false;
        $arrKey = $sub_goods['sku'].':'.$sub_goods['sku_val'];
        $skuval = explode(':',$arrKey);
        $_mkey = $this->mkey.'_SkuName';
        $sku = Cache::get($_mkey);
        if (empty($sku[$arrKey]) == false) return $sku[$arrKey];
        $where[] = ['id','IN',$skuval];
        $rows = $this->field('id,speid,val')->where($where)->order('id ASC')->select();
        foreach ($rows as $row){
            if ($row['speid'] == 0){
                $skurows[$row['id']]['sku'] = $row['val'];
            }else{
                $skurows[$row['speid']]['val'] = $row['val'];
            }
        }
        $skuName = array();
        foreach ($skurows as $row){
            $skuName[] = $row['sku'].':'.$row['val'];
        }
        $sku[$arrKey] = join(',',$skuName);
        Cache::set($_mkey,$sku,3600);
        return  $sku[$arrKey];
    }
}
