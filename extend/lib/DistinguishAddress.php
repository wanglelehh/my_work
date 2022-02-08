<?php
//地址识别
namespace lib;
use think\facade\Cache;
class DistinguishAddress {
    /**
     * 类的入口方法
     * 传入地址信息自动识别，并返回最高匹配结果
     * 如果地址新增，则需要删除缓存文件重新缓存
     * @param $address
     **/
    function getAddressResult($address){
        // 优先第一种方法
        $result = $this->getAddressArrar($address);
        // 如果结果不理想，再模糊去匹配
        if($result['level'] != 3){
            $result_sub = $this->addressVague($address);
            // 只有全匹配对才替换，否则不做任何改变
            if($result_sub['level'] == 3){
                $result = $result_sub;
            }
        }
        // 联系方式-优先匹配电话
        if(preg_match('/1\d{10}/', $address, $mobiles)){ // 手机
            $result['mobile'] = $mobiles[0];
        } else if(preg_match('/(\d{3,4}-)?\d{7,8}/', $address, $mobiles)){ // 固定电话
            $result['mobile'] = $mobiles[0];
        }
        // 识别姓名-必须空格分享的--概率
        preg_match_all('/[\x{4e00}-\x{9fa5}]{2,}/iu', $address,$names);
        if($names){
            $name_where = '';
            foreach ($names[0] as $name){
                if (in_array($name,$result['region_arr'])){
                    continue;
                }
                // 必须是大于1个字符且小于5个字符的
                if(1 < mb_strlen($name,'utf-8') && mb_strlen($name, 'utf-8') < 5){
                    $sub_name = mb_substr($name, 0, 1, 'utf-8');
                    $name_where .= "name like '{$sub_name}%' or ";
                }
            }
            unset($result['region_arr']);
            if(!empty($name_where)){
                $SurNameModel = new \app\mainadmin\model\SurNameModel();
                $name_where = substr($name_where, 0, -3);
                $list = $SurNameModel->where($name_where)->order('id ASC')->select()->toArray();

                if($list) {
                    $name_first = $list[0]['name'];
                    foreach ($names[0] as $name){
                        $len = mb_strlen($name_first, 'utf-8');
                        if (mb_substr($name, 0, $len, 'utf-8') == $name_first){
                            $result['name'] = $name;
                        }
                    }
                }
            }
        }
        // 去掉详细里面的姓名和电话
        $result['info'] = str_replace($result['mobile'], '', $result['info']);
        $result['info'] = str_replace($result['name'], '', $result['info']);
        $result['info'] = $result['province']['region_name'] . $result['city']['region_name'] . $result['district']['region_name'] . $result['info'];
        return $result;
    }

    /**
     * 获取所有地址递归列表
     **/
    function getRegionTreeList(){
        $mkey = 'regionListByDistinguishAddress';
        $regions = Cache::get($mkey);
        if (empty($regions)){
            $RegionModel = new \app\mainadmin\model\RegionModel();
            $where = [];
            $where[] = ['level_type','in',[1,2,3]];
            $regions = $RegionModel->field('id as region_id,name as region_name,pid as parent_id')->where($where)->select()->toArray();
            $regions = $this->arrayKey($regions);
            Cache::set($mkey,$regions);
        }
        return $regions;
    }
    /**
     * 第一种方法
     * 根据地址列表递归查找准确地址
     * @param $address
     * @return array
     **/
    function getAddressArrar($address){
        // 获取所有地址递归列表
        $regions = $this->getRegionTreeList();
        // 初始化数据
        $province = $city = $district = array();
        // 先查找省份-第一级地区
        $province = $this->checkAddress($address, $regions);
        if($province){
            // 查找城市-第二级地区
            $city = $this->checkAddress($address, $province['list']);
            if($city){
                // 查找地区-第三级地区
                // 西藏自治区那曲市色尼区辽宁南路西藏公路 第三个参数因为这个地址冲突取消强制
                $district = $this->checkAddress($address, $city['list']);
            }
        }
        return $this->getAddressInfo($address, $province, $city, $district);
    }
    /**
     * 第二种方法
     * 地址模糊查找
     **/
    function addressVague($address){
        $res = preg_match_all('/\S{2}[自市区镇县乡岛州]/iu', $address,$arr);
        if(!$res) return false;
        $where = ' ';
        foreach ($arr[0] as $value){
            if(strpos($value, '小区') === false && strpos($value, '开发区') === false){
                $where .= "name like '%{$value}' or ";
            }
        }
        $where = substr($where,0,-3);
        $RegionModel = new \app\mainadmin\model\RegionModel();
        $citys = $RegionModel->field('level_type,id as region_id,name as region_name,pid as parent_id')->where($where)->select();
        // 匹配所有地址
        $result = array();
        foreach ($citys as &$city){
            // 所有相关联的地区id
            $city_ids = array();
            if($city['level_type'] == 2) {
                $city_ids = array($city['parent_id'], $city['region_id']);
                // 尝试能不能匹配第三级
                $areas = $RegionModel->field('level_type,id as region_id,name as region_name,pid as parent_id,left(name,2) as ab_name')->where('pid',$city['region_id'])->select()->toArray();
                foreach ($areas as $row){
                    if(mb_strpos($address,$row['ab_name'])){
                        $city_ids[] = $row['region_id'];
                    }
                }
            } else if($city['level_type'] == 3){
                $city['province_id'] = $RegionModel->where('id',$city['parent_id'])->value('pid');
                $city_ids = array($city['parent_id'], $city['region_id'], $city['province_id']);
            }
            // 查找该单词所有相关的地区记录
            $where = [];
            $where[] = ['id','in',$city_ids];
            $city_list = $RegionModel->field('id as region_id,name as region_name,pid as parent_id,left(name,2) as ab_name')->where($where)->order('id asc')->select()->toArray();

            sort($city_ids);
            $key = array_pop($city_ids);
            $result[$key] = $city_list;
            sort($result);
        }
        if($result){
            list($province, $city, $area) = $result[0];
            return $this->getAddressInfo($address, $province, $city, $area);
        }
        return false;
    }
    /**
     * 匹配正确的城市地址
     * @param $address
     * @param $city_list
     * @param int $force
     * @param int $str_len
     * @return array
     **/
    function checkAddress($address, $city_list, $force=false, $str_len=2){
        $num = 0;
        $list = array();
        $result = array();
        // 遍历所有可能存在的城市
        foreach ($city_list as $city_key=>$city){
            $city_name = mb_substr($city['region_name'], 0, $str_len,'utf-8');
            // 判断是否存包含当前地址字符
            $city_arr = explode($city_name, $address);
            // 如果存在相关字眼，保存该地址的所有子地址
            if(count($city_arr) >= 2){
                // 必须名称长度同时达到当前比对长度
                if(strlen($city['region_name']) < $str_len){
                    continue;
                }
                $num ++;
                $list = $list + $city['childs'];

                $result[] = array(
                    'region_id' => $city['region_id'],
                    'region_name' => $city['region_name'],
                    'list' =>$list,
                );
            }
        }
        // 如果有多个存在，则加大字符匹配长度
        if($num > 1 || $force){
            $region_name1 = $result[0]['region_name'];
            $region_name2 = $result[1]['region_name'];

            if(strlen($region_name1) == strlen($region_name2) && strlen($region_name1) == $str_len){
                $region_id1 = $result[0]['region_id'];
                $region_id2 = $result[1]['region_id'];
                $index = $region_id1 > $region_id2 ? 1 : 0;
                $result = $result[$index];
                return $result;
            }
            return $this->checkAddress($address, $city_list, $force, $str_len+1);
        } else {
            $result[0]['list'] = $list;
            return $result[0];
        }
    }
    /**
     * 根据原地址返回详细信息
     * @param $address
     * @param $province
     * @param $city
     * @param $area
     * @return array
     **/
    function getAddressInfo($address, $province, $city, $district){
        // 查找最后出现的地址 - 截取详细信息
        $find_str = '';
        if($province['region_name']){
            $find_str = $province['region_name'];
            if($city['region_name']){
                $find_str = $city['region_name'];
                if($district['region_name']){
                    $find_str = $district['region_name'];
                }
            }
        }
        // 截取详细的信息
        $find_str_len = mb_strlen($find_str,'utf-8');
        for($i=0; $i<$find_str_len-1; $i++){
            $substr = mb_substr($find_str,0,$find_str_len - $i, 'utf-8');
            $end_index = mb_strpos($address, $substr);
            if ($end_index){
                $address = mb_substr($address, $end_index + mb_strlen($substr) , mb_strlen($address) - $end_index);
            }
        }
        !empty($find_str) && $find_str = '|\S*' . $find_str;
        $area['info'] = preg_replace("/\s*|,|，|:|：{$find_str}/i", '', $address);
        $level = 0;
        if($district['region_name']){
            $level = 3;
        } else if($city['region_name']){
            $level = 2;
        } else if ($province['region_name']) {
            $level = 1;
        }
        $region_arr = [];
        $region_arr[] = $province['region_name'];
        $region_arr[] = $city['region_name'];
        $region_arr[] = $district['region_name'];
        return array(
            'region_arr' => $region_arr,
            'province' => array('region_id'=>$province['region_id'], 'region_name'=>$province['region_name']),
            'city'  => array('region_id'=>$city['region_id'], 'region_name'=>$city['region_name']),
            'district'  => array('region_id'=>$district['region_id'], 'region_name'=>$district['region_name']),
            'info'  => $area['info'],
            'level'  => $level,
        );
    }
    /**
     * 递归所有地址成无限分类数组
     * @param $data
     * @param int $region_id
     * @return array
     **/
    function arrayKey($data, $region_id=100000){
        $result = array();
        foreach ($data as $row){
            if($region_id == $row['parent_id']){
                $key = $row['region_id'];
                $row['childs'] = $this->arrayKey($data, $row['region_id']);
                $result[$key] = $row;
            }
        }
        return $result;
    }
}
?>