<?php
namespace app\channel\controller\sys_admin;
use app\AdminController;
use app\channel\model\GoodsUnitModel;
/**
 * 商品计量单位
 * Class Index
 * @package app\store\controller
 */
class GoodsUnit extends AdminController
{	
	//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize()
   {	
   		parent::initialize();
		$this->Model = new GoodsUnitModel();
    }
	//*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
    public function index()
    {
        $list = $this->Model->getRows();

		$this->assign('list',$list);		
        return $this->fetch('index');
    }


	/*------------------------------------------------------ */
	//-- 添加前处理
	/*------------------------------------------------------ */
    public function beforeAdd($data) {
		if (empty($data['name']) ) return $this->error('计量单位名称不能为空.');
        $where[] = ['name','=',$data['name']];
		$count = $this->Model->where($where)->count();
		if ($count > 0) return $this->error('已存在相同的计量单位名称，不允许重复添加.');
        $data['add_time'] = time();
		return $data;
	}

	/*------------------------------------------------------ */
	//-- 修改前处理
	/*------------------------------------------------------ */
    public function beforeEdit($data){
		if (empty($data['name']) ) return $this->error('计量单位名称不能为空.');
		//验证数据是否出现变化
		$dbarr = $this->Model->field(join(',',array_keys($data)))->where('id',$data['id'])->find()->toArray();
		$this->checkUpData($dbarr,$data);
		$where[] = ['id','<>',$data['id']];
		$where[] = ['name','=',$data['name']];
		$count = $this->Model->where($where)->count();
		if ($count > 0) return $this->error('已存在相同的计量单位名称，不允许重复添加！');

		return $data;		
	}
	/*------------------------------------------------------ */
	//-- 删除
	/*------------------------------------------------------ */
	public function del(){
		$map['id'] = input('id',0,'intval');
		if ($map['id']<1) return $this->error('传递参数失败！'); 
		$res = $this->Model->where($map)->delete();
		if ($res < 1)  return $this->error(); 
		$this->Model->cleanMemcache();
		return $this->success('操作成功.');
	}

}
