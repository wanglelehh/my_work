<?php
namespace app\channel\model;
use app\BaseModel;
use think\Db;
//*------------------------------------------------------ */
//-- 拿货上级汇总表
/*------------------------------------------------------ */
class PurchaseSuperiorModel extends BaseModel
{
	protected $table = 'channel_purchase_superior';
	public  $pk = 'user_id';

    /**
     * 处理代理拿货上级汇总
     * @param $user_id 操作会员ID
     * @param $pid 上级ID
     * @param $is_edit 是否修改
     */
	public function treat($user_id = 0,$pid = 0,$is_edit = false){
        $data = $this->where('user_id',$user_id)->find();
        $pid = $pid * 1;
        if ($pid > 0){
            $superior = $this->where('user_id',$pid)->value('superior');
            if (empty($superior) == true) {
                $superior = $user_id.','.$pid;
            }else {
                $superior = $user_id.','.$superior;
            }
        }else{
            $superior = $user_id;
        }
        if (empty($data)){//不存在数据时执行
            $inData['user_id'] = $user_id;
            $inData['pid'] = $pid;
            $inData['superior'] = $superior;
            $res = $this->save($inData);
            if ($res < 1){
                return false;
            }
            return true;
        }
        $upData['pid'] = $pid;
        $upData['superior'] = $superior;
        $res = $this->where('user_id',$user_id)->update($upData);
        if ($res < 1){
            return false;
        }
        if ($is_edit == false) return true;//非修改不执行以下操作

        $where[] = ['','exp',Db::raw("FIND_IN_SET('".$user_id."',superior)")];
        $where[] = ['user_id','<>',$user_id];
        $allCount = $this->where($where)->count('user_id');

        if ($allCount < 1) return true;//没有下级不执行

        $upDataAll['superior'] = Db::raw("REPLACE(superior,'{$data['superior']}','{$superior}')");
        $res = $this->where($where)->update($upDataAll);
        if ($allCount != $res){
            return false;
        }

        return true;
    }
}
