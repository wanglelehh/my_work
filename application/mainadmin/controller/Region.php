<?php
namespace app\mainadmin\controller;
use app\AdminController;
use app\mainadmin\model\RegionModel;
use PHPExcel_IOFactory;

/**
 * 区域管理
 * Class Index
 * @package app\store\controller
 */
class Region extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new RegionModel();		
    }
	
	
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
    public function index(){
		$pid = input('pid') * 1;
		if ($pid == 0){
			$where = ['pid' => $pid];
		}else{
			$where = ['id' => $pid];
		}
		$region = $this->Model->field(['id','name','pid','merger_name'])->where($where)->find(); 	
		
		$this->assign("region", $region);		
		$list = $this->Model->field(['id','name','pid','level_type','status'])->where(['pid' => $region['id']])->select(); 
		$this->assign("list", $list);
        return $this->fetch();
    }
	/*------------------------------------------------------ */
	//-- 上传excel文件分析读取数据
	/*------------------------------------------------------ */
    public function upload()
    {
		set_time_limit(0);
     	$this->returnJson = true;
		if (empty($_FILES['file'])) return $this->error('请选择上传文件');
		$filePath = $_FILES['file']['tmp_name'];


		$reader = \PHPExcel_IOFactory::createReader('Excel2007');// Reader很关键，用来读excel文件
		if (!$reader->canRead($filePath)) { // 这里是用Reader尝试去读文件，07不行用05，05不行就报错。注意，这里的return是Yii框架的方式。
            $reader = PHPExcel_IOFactory::createReader('Excel5');
			if (!$reader->canRead($filePath)) {
					return $this->error('读取excel文件失败！');
			}
		}
		$PHPExcel = $reader->load($filePath); // Reader读出来后，加载给Excel实例

		$currentSheet = $PHPExcel->getSheet(0); // 拿到第一个sheet（工作簿？）
        $allColumn = \PHPExcel_Cell::columnIndexFromString($currentSheet->getHighestColumn());
		$allRow = $currentSheet->getHighestRow(); // 最大的行，比如12980. 行从0开始
		$keyarr = array();
		$time = time();
		 //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
		for ($currentRow = 1; $currentRow <= $allRow; $currentRow++) {
			$row = array();
			//从哪列开始，A表示第一列
            for ($currentColumn = 0; $currentColumn < $allColumn; $currentColumn++){
				//读取到的数据，保存到数组$arr中
                $cell = $currentSheet->getCellByColumnAndRow($currentColumn,$currentRow)->getValue();
				if($cell instanceof PHPExcel_RichText){
					$cell  = $cell->__toString();
				}					
				$row[] = empty($cell)?'':$cell;
			}
			if ($currentRow == 1){
				$keyarr = $row;
				continue;
			}
			$inall = array();
            $merger_name = [];

            foreach ($keyarr as $key=>$val){
			    if (in_array($val,['id','pid','level_type','name','first_char','city_code','zip_code','lng','lat'])){
                    $inall[$val] = 	$row[$key];
                }elseif (in_array($val,['Province','City','District','Town'])){
			        if (empty($row[$key]) == false){
                        $merger_name[] = $row[$key];
                    }
                }
			}
            $inall['merger_name'] = join(',',$merger_name);
			$inall['status'] = 	1;
			$inall['update_time'] = $time;
			$region = $this->Model->where(['id' => $inall['id']])->find();	
			if (empty($region) == false){
				$res = $this->Model->where('',$region['id'])->update($inall);
			}else{
				$res = $this->Model->create($inall);
                $res =$res->id;
			}
			if (!$res){			
				return $this->error('处理数据失败，请重试！');
			}
		}
		$delwhere[] = ['update_time','<',$time];
		$this->Model->where($delwhere)->update(['status'=>'0']);
		
		
		return $this->success('导入成功.');
    }
    /*------------------------------------------------------ */
    //-- 导出指定格式
    /*------------------------------------------------------ */
    public function exportTouView()
    {
        $provices = $this->Model->where('pid','100000')->field('id,name')->select();
        $proviceList = [];
        $cityList = [];
        $areaList = [];
        foreach ($provices as $pRow){
            $data['label'] = $pRow['name'];
            $data['value'] = $pRow['id'];
            $proviceList[] = $data;
            $citys = $this->Model->where('pid',$pRow['id'])->field('id as value,name as label')->select()->toArray();
            $cityList[] = $citys;
            $areaArr = [];
            foreach ($citys as $cRpw){
                $areaArr[] = $this->Model->where('pid',$cRpw['value'])->field('id as value,name as label')->select()->toArray();
            }
            $areaList[] = $areaArr;
        }
        $this->assign('proviceJson',json_encode($proviceList,JSON_UNESCAPED_UNICODE));
        $this->assign('cityJson',json_encode($cityList,JSON_UNESCAPED_UNICODE));
        $this->assign('areaJson',json_encode($areaList,JSON_UNESCAPED_UNICODE));
        return $this->fetch('exportTouView');
    }


}
