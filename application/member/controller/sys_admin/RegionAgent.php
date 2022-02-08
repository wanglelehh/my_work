<?php
namespace app\member\controller\sys_admin;
use app\AdminController;
use app\member\model\RegionAgentModel;
use app\mainadmin\model\RegionModel;
use app\member\model\RoleModel;

use think\Db;

/**
 * 区域代理管理
 */
class RegionAgent extends AdminController
{
	//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
    public function initialize()
    {
   		parent::initialize();
		$this->Model = new RegionAgentModel();
    }
	/*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
    public function index()
    {
		$this->getList(true);
        $this->assign("roleOpt", arrToSel($this->roleList));
        $this->assign("d_region", $this->selRegion());
        return $this->fetch('index');
    }  
	/*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
        $RoleModel = new RoleModel();
        $this->roleList = $RoleModel->getRows();
        $this->assign("roleList", $this->roleList);
        $role_id = input('role_id', 0,'intval');
        $keyword = input("keyword",'','trim');
        $where = [];
        if ($role_id > 0){
            $where[] = 'u.role_id = '.$role_id;
        }
        if (empty($keyword) == false) {
            if (is_numeric($keyword)) {
                $where[] = " ( u.user_id = '" . $keyword . "' or u.mobile like '" . $keyword . "%' )";
            } else {
                $where[] = " ( u.user_name like '" . $keyword . "%' or u.nick_name like '" . $keyword . "%' )";
            }
        }

        $viewObj = $this->Model->alias('ur')->join("users u", 'ur.user_id=u.user_id', 'left')->where(join(' AND ', $where))->field('ur.*,u.mobile,u.real_name,u.nick_name,u.role_id')->order('ur.user_id DESC');
        $data = $this->getPageList($this->Model,$viewObj);
        $RegionModel = new RegionModel();
        foreach ($data['list'] as $key=>$row){
            $where = [];
            $where[] = ['id','in',$row['region_ids']];
            $row['region_name'] = $RegionModel->where($where)->column('name');
            $row['region_name'] = join(',',$row['region_name']);
            $data['list'][$key] = $row;
        }
		$this->assign("data", $data);
		if ($runData == false){
			$data['content']= $this->fetch('list')->getContent();
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }
    /*------------------------------------------------------ */
    //-- 选择区域
    /*------------------------------------------------------ */
    public function selRegion(){
        $d_region['def'] = [
            ['name'=>'华东','ids'=>[310000,320000,330000,340000,350000,360000,370000]],
            ['name'=>'华北','ids'=>[150000,140000,130000,120000,110000]],
            ['name'=>'华中','ids'=>[410000,420000,430000]],
            ['name'=>'华南','ids'=>[440000,450000,460000]],
            ['name'=>'东北','ids'=>[210000,220000,230000]],
            ['name'=>'西北','ids'=>[610000,620000,630000,640000,650000]],
            ['name'=>'西南','ids'=>[500000,510000,520000,530000,540000]],
        ];
        $RegionModel = new RegionModel();
        foreach ($d_region['def'] as $key=>$region){
            $rows = $RegionModel->where([['id','in',$region['ids']]])->field('id,name,pid')->select()->toArray();
            $d_region['plist'][$key] = $rows;
        }
        $rows = $RegionModel->where('level_type',2)->field('id,name,pid')->select()->toArray();
        foreach ($rows as $row){
            $d_region['clist'][$row['pid']][] = $row;
        }
        $rows = $RegionModel->where('level_type',3)->field('id,name,pid')->select()->toArray();
        foreach ($rows as $row){
            $d_region['dlist'][$row['pid']][] = $row;
        }
        unset($rows,$row);
        return $d_region;
    }
    /*------------------------------------------------------ */
    //--添加
    /*------------------------------------------------------ */
    public function add()
    {
        if ($this->request->isPost()){
            $mkey = 'set_region_agent';
            $res = redisLook($mkey);
            if ($res == false){
                return $this->error('正在执行中，请稍后再试.');
            }
            $user_id = input('select_user',0,'intval');
            if ($user_id < 1){
                redisLook($mkey,-1);
                return $this->error('请选择会员.');
            }
            $count = $this->Model->where('user_id',$user_id)->count();
            if ($count > 0){
                redisLook($mkey,-1);
                return $this->error('会员已存在，不能重复设置.');
            }
            $sel_region = input('sel_region');
            if (empty($sel_region)){
                redisLook($mkey,-1);
                return $this->error('请选择区域.');
            }
            $RegionModel = new RegionModel();
            foreach ($sel_region as $region_id){
                $where = [];
                $where[] = ['','exp',Db::raw("FIND_IN_SET('".$region_id."',region_ids)")];
                $count = $this->Model->where($where)->count();
                if ($count > 0){
                    $region_name = $RegionModel->where('region_id',$region_id)->value('name');
                    return $this->error($region_name.'：已被分配，不能重复分配.');
                }
            }
            $inArr['user_id'] = $user_id;
            $inArr['region_ids'] = join(',',$sel_region);
            $res = $this->Model->save($inArr);
            redisLook($mkey,-1);
            if ($res < 1) {
                return $this->error('未知原因，操作失败.');
            }
            return $this->success('操作成功.');
        }
        $other_sel_region = $this->Model->column('region_ids');
        $this->assign("sel_region", '');
        $this->assign("other_sel_region", join(',',$other_sel_region));
        $this->assign("user_id", 0);

        return $this->fetch('selRegion');
    }
    /*------------------------------------------------------ */
    //--修改
    /*------------------------------------------------------ */
    public function edit()
    {
        if ($res == false){
            return $this->error('正在执行中，请稍后再试.');
        }
        $user_id = input('user_id',0,'intval');
        if ($user_id < 1){
            return $this->error('请选择会员.');
        }
        $sel_region = $this->Model->where('user_id',$user_id)->find();
        if (empty($sel_region)){
            return $this->error('没有找到相关区域代理记录.');
        }
        if ($this->request->isPost()){
            $mkey = 'set_region_agent';
            $res = redisLook($mkey);
            if ($res == false){
                return $this->error('正在执行中，请稍后再试.');
            }

            $sel_region = input('sel_region');
            if (empty($sel_region) == false){
                $RegionModel = new RegionModel();
                foreach ($sel_region as $region_id){
                    $where = [];
                    $where[] = ['user_id','<>',$user_id];
                    $where[] = ['','exp',Db::raw("FIND_IN_SET('".$region_id."',region_ids)")];
                    $count = $this->Model->where($where)->count();
                    if ($count > 0){
                        $region_name = $RegionModel->where('region_id',$region_id)->value('name');
                        return $this->error($region_name.'：已被分配，不能重复分配.');
                    }
                }
            }
            $upArr['region_ids'] = join(',',$sel_region);
            $res = $this->Model->where('user_id',$user_id)->update($upArr);
            redisLook($mkey,-1);
            if ($res < 1) {
                return $this->error('未知原因，操作失败.');
            }
            return $this->success('操作成功.');
        }

        $this->assign("sel_region", $sel_region['region_ids']);
        $where = [];
        $where[] = ['user_id','<>',$user_id];
        $other_sel_region = $this->Model->where($where)->column('region_ids');
        $this->assign("other_sel_region", join(',',$other_sel_region));
        $this->assign("user_id", $user_id);
        return $this->fetch('selRegion');
    }
}
