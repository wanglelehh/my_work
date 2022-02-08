<?php
/*------------------------------------------------------ */
//-- 文件上传管理程序
//-- @author iqgmy
/*------------------------------------------------------ */
namespace app\mainadmin\controller;
use app\AdminController;
use think\facade\Request;
use app\shop\model\GoodsImgsModel;

class Attachment extends AdminController{

    protected $_root_;
    public $supplyer_id = 0;
    //*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    public function initialize(){
        $this->isCheckPriv = false;
        parent::initialize();
        $this->_root_ = Request::root();
    }

    /**
     * 商品上传
     */
    public function goodsUpload() {

            //缩略图兼容小程序直播商品图片，使用小程序直播请勿修改
            $thumb['width'] = 300;
            $thumb['height'] = 300;
            //缩略图兼容小程序直播商品图片，使用小程序直播请勿修改

            if ($this->supplyer_id > 0){
                $dir = 'supplyer/'.$this->supplyer_id.'/gimg';
            }else{
                $dir = 'gimg/';
            }
            $result = $this->_upload($_FILES['file'],$dir,$thumb);
            if ($result['error']) {
                $data['code'] = 0;
                $data['msg'] = $result['info'];
                return $this->ajaxReturn($data);
            }
            $addarr['goods_id'] = input('post.gid',0,'intval');
            $addarr['sku_val'] = input('post.sku','','trim');

            if ($this->store_id > 0){
                $where[] = ['store_id','=',$this->store_id ];
            }elseif ($this->supplyer_id > 0){//供应商相关
                $addarr['supplyer_id'] = $this->supplyer_id;
                $addarr['admin_id'] = 0;
                $where[] = ['supplyer_id','=',$this->supplyer_id];
            }else{
                $addarr['admin_id'] = AUID;
                $where[] = ['admin_id','=',AUID];
            }
            $savepath = trim($result['info'][0]['savepath'],'.');

            $addarr['store_id'] = $this->store_id;
            $addarr['goods_img'] = $file_url = $savepath.$result['info'][0]['savename'];
            $addarr['goods_thumb'] = str_replace('.','_thumb.',$addarr['goods_img']);
            $GoodsImgsModel =  new GoodsImgsModel();
            //如果sku不为空，查询之前是否已上传过,则删除
            if (empty($addarr['sku_val']) == false){
                $where[] = ['goods_id','=',$addarr['goods_id']];
                $where[] = ['sku_val','=',$addarr['sku_val']];
                $imgObj = $GoodsImgsModel->where($where)->find();
                if (empty($imgObj) == false){
                    unlink('.'.$imgObj['goods_thumb']);
                    unlink( '.'.$imgObj['goods_img']);
                    $imgObj->delete();
                }
            }
            $GoodsImgsModel->save($addarr);
            $img_id = $GoodsImgsModel->img_id;
            if ($img_id < 1){
                $this->removeImg($file_url);//删除刚刚上传的
                $data['code'] = 0;
                $data['msg'] = '商品图片写入数据库失败！';
                return $this->ajaxReturn($data);
            }
            $data['code'] = 1;
            $data['msg'] = "上传成功";
            $data['image_id'] = $img_id;
            $data['filename'] = end(explode('/',$result['info'][0]['savename']));
            $data['attachment'] = $file_url;
            $data['url'] = $file_url;
            $data['image'] = array('id'=>$img_id,'thumbnail'=>$file_url,'path'=>$file_url);
            return $this->ajaxReturn($data);

    }

    /**
     * 删除商品图片
     */
    public function removeImg($file='') {
        $img_id = input('post.id',0,'intval');
        if ($img_id > 0){
            $GoodsImgsModel = new GoodsImgsModel();
            $img = $GoodsImgsModel->find($img_id);
            if (empty($img)){
                return $this->error('没有找到相关图片.');
            }
            $file = $img->goods_img;
            $res = $img->delete();
            if ($res < 1){
                return $this->error('删除图片失败.');
            }
        }
        if (empty($file))  return $this->error('传值错误.');
        unlink('.'.$file);
        unlink('.'.str_replace('.','_thumb.',$file));
        return $this->success('删除图片成功.');
    }


    /*------------------------------------------------------ */
    //--webuploader组件
    /*------------------------------------------------------ */
    public function webUploadByEdit()
    {
        $type = input('get.type', 'image', 'trim');
        if (in_array($type, array('image','audio','video')) == false) {
            $result['code'] = 0;
            $result['msg'] = '不支持上传类型.';
            return $this->ajaxReturn($result);
        }

        if ($type == 'image'){
            if($_FILES['imgFile']['size'] > 2000000){
                $result['code'] = 0;
                $result['msg'] = '上传文件过大，大小不能超过2Mb.';
                return $this->ajaxReturn($result);
            }
            if (strstr( $_FILES["file"]['type'],'image') == false) {
                $result['code'] = 0;
                $result['msg'] = '未能识别图片，请核实.';
                return $this->ajaxReturn($result);
            }
            $result['is_image'] = 1;
            if ($this->supplyer_id > 0) {
                $dir ='supplyer/' . $this->supplyer_id .'/image/';
            }else{
                $dir = 'image/';
            }
            $res = $this->_upload($_FILES["file"],$dir);
            if ($res['error']) {
                $data['code'] = 0;
                $data['msg'] = $res['info'];
                return $this->ajaxReturn($data);
            }
            $result['name'] = $res['info'][0]['name'];
            $result['ext'] = $res['info'][0]['extension'];
            $result['filesize'] = $res['info'][0]['size'];
            $newfile =  $res['info'][0]['savepath'].$res['info'][0]['savename'];
            $result['filename'] = trim($newfile,'.');
            $result['attachment'] = $result['filename'];
            $result['url'] = $result['filename'];
            list( $result['width'],  $result['height']) = getimagesize($newfile);
        }elseif($type == 'audio'){
            if($_FILES['file']['size'] > 6000000){
                $result['code'] = 0;
                $result['msg'] = '最大支持 6MB 以内的语音.';
                return $this->ajaxReturn($result);
            }
            if (strstr( $_FILES["file"]['type'],'audio') == false) {
                $result['code'] = 0;
                $result['msg'] = '未能识别音频文件，请核实.';
                return $this->ajaxReturn($result);
            }
            $file_type = end(explode('.',$_FILES['file']['name']));
            if (in_array($file_type,['mp3','wma','wav','amr']) == false){
                $result['code'] = 0;
                $result['msg'] = '格式不对，只支持 (mp3,wma,wav,amr 格式)，请核实.';
                return $this->ajaxReturn($result);
            }
            if ($this->supplyer_id > 0) {
                $dir = config('config._upload_').'supplyer/' . $this->supplyer_id .'/audio/';
            }else{
                $dir = config('config._upload_').'audio/';
            }
            makeDir($dir);
            $file_name = random_str(32).'.'.$file_type;
            move_uploaded_file($_FILES['file']['tmp_name'],$dir.$file_name);

            $result['filename'] = trim($dir.$file_name,'.');
            $result['attachment'] = $result['filename'];
            $result['url'] = $result['filename'];
        }elseif($type == 'video'){
            $sys_mode_upfile_limit = settings('sys_mode_upfile_limit');
            if($_FILES['file']['size'] > ($sys_mode_upfile_limit * 1024 * 1024)){
                $result['code'] = 0;
                $result['msg'] = '最大支持 '.$sys_mode_upfile_limit.'Mb 以内的视频.';
                return $this->ajaxReturn($result);
            }

            $file_type = end(explode('.',$_FILES['file']['name']));
            if (in_array($file_type,['mp4']) == false){
                $result['code'] = 0;
                $result['msg'] = '格式不对，只支持 (mp4 格式)，请核实.';
                return $this->ajaxReturn($result);
            }
            if ($this->supplyer_id > 0) {
                $dir = config('config._upload_').'supplyer/' . $this->supplyer_id .'/video/';
            }else{
                $dir = config('config._upload_').'video/';
            }
            makeDir($dir);
            $file_name = random_str(32).'.'.$file_type;
            move_uploaded_file($_FILES['file']['tmp_name'],$dir.$file_name);

            $result['filename'] = trim($dir.$file_name,'.');
            $result['attachment'] = $result['filename'];
            $result['url'] = $result['filename'];
        }
        $result['code'] = 1;
        return $this->ajaxReturn($result);
    }
    /*------------------------------------------------------ */
    //--获取网络图片
    /*------------------------------------------------------ */
    public function fetchWebImg()
    {
        $url = input('url','','trim');
        if (empty($url)){
            $data['error'] = 1;
            $data['message'] = '请求填写网络图片地址.';
            return $this->ajaxReturn($data);
        }
        $file_path = config('config._upload_').'image/'.date('Y').'/'.date('m') .'/';
        makeDir($file_path);
        $extension = end(explode('.',$url));
        $extension = strtolower($extension);
        if (in_array($extension,['jpg','jpeg','png']) == false){
            $data['error'] = 1;
            $data['message'] = '图片格式错误.';
            return $this->ajaxReturn($data);
        }
        $file_name = $file_path.random_str(15).'.'.$extension;
        downloadImage($url,$file_name);
        $result['name'] = end(explode('/',$url));
        $result['ext'] = $extension;
        $result['filename'] = trim($file_name,'.');
        $result['attachment'] = $result['filename'];
        $result['url'] = $result['filename'];
        return $this->ajaxReturn($result);
    }
    /**
     * 编辑器图片空间
     */
    public function webUploadByManager() {
        $year = input('year',date('Y'),'intval');
        $month = input('month',date('m'),'intval');
        $type = input('type','','trim');

        if ($this->supplyer_id > 0){
            $root_path = config('config._upload_').'supplyer/'.$this->supplyer_id.'/';
        }else{
            $root_path = config('config._upload_');
        }

        if ($type == 'audio') {
           $current_path = $root_path . 'audio/';
        }elseif ($type == 'video') {
            $current_path = $root_path . 'video/';
        }elseif($type == 'icon'){
            $current_path = './a_static/menuimg/';
        }else{
            if ($month < 10 ){
                $current_path = $root_path.'image/'.$year.'/0'.$month*1;
            }else{
                $current_path = $root_path.'image/'.$year.'/'.$month;
            }
            $current_path .= '/';
        }

        //遍历目录取得文件信息
        $data = array();
        $i = 0;
        //目录不存在或不是目录
        if (file_exists($current_path) && is_dir($current_path)) {
            if ($handle = opendir($current_path)) {
                while (false !== ($filename = readdir($handle))) {
                    if ($filename{0} == '.') continue;
                    $file = trim($current_path ,'.'). $filename;
                    $data[$i]['id'] = $i;
                    $data[$i]['filename'] = $filename;
                    $data[$i]['attachment'] = $file;
                    $data[$i]['type'] = 1;
                    $data[$i]['url'] = $file;
                    $i++;
                }
                closedir($handle);
            }
        }
        $pshow = '';
        if ($i > 0){
            $count = count($data);
            // 页数参数，默认第一页
            $page = input('page',1,'intval');
            // 每页数目
            $step = 24;
            // 每次获取起始位置
            $start = ($page-1)*$step;
            // 获取数组中当前页的数据
            $data = array_slice($data,$start,$step);
            $totalPages = intval(($count + $step - 1) / $step);
            $pshow = $this->pshow($totalPages,$page);
        }


        $result['message']['errno'] = 0;
        $result['message']['message']['page'] = $pshow;
        $result['message']['message']['items'] = $data;
        return $this->ajaxReturn($result);

    }
    /**
     * 分页显示输出
     * @access public
     */
    public function pshow($totalPages,$nowPage=1,$rollPage=5) {
        if(1 == $totalPages) return '';
        $middle         =   ceil($rollPage/2); //中间位置
        //上下翻页字符串
        $upRow          =   $nowPage-1;
        $downRow        =   $nowPage+1;
        if ($upRow>0){
            $upPage     =   '<li><a href="javascript:;" page="'.$upRow.'" class="pager-nav">&laquo;上一页</a></li>';
        }else{
            $upPage     =   '';
        }
        if ($downRow <= $totalPages){
            $downPage   =   '<li ><a href="javascript:;" page="'.$downRow.'"  class="pager-nav">下一页&raquo;</a></li>';
        }else{
            $downPage   =   '';
        }
        // 1 2 3 4 5
        $linkPage = "";
        if ($totalPages != 1) {
            if ($nowPage < $middle) { //刚开始
                $start = 1;
                $end = $rollPage;
            } elseif ($totalPages < $nowPage + $middle - 1) {
                $start = $totalPages - $rollPage + 1;
                $end = $totalPages;
            } else {
                $start = $nowPage - $middle + 1;
                $end = $nowPage + $middle - 1;
            }
            $start < 1 && $start = 1;
            $end > $totalPages && $end = $totalPages;

            for ($page = $start; $page <= $end; $page++) {
                if ($page != $nowPage) {
                    $linkPage .= " <li><a href='javascript:;' page='".$page."' >".$page."</a></li>";
                } else {
                    $linkPage .= "<li class='active'><a href='javascript:;'>".$page."</a></li>";
                }
            }
        }else{
            $linkPage .= "<li class='active'><a href='javascript:;'>1</a></li>";
        }
        $pageStr = str_replace(
            array('%nowPage%','%totalPage%','%upPage%','%downPage%','%linkPage%','%end%'),
            array($nowPage,$totalPages,$upPage,$downPage,$linkPage),'<div><ul class="pagination pagination-centered">%upPage%%linkPage%%downPage%</ul></div>');

        return $pageStr;
    }
    //-- 上传视频
    /*------------------------------------------------------ */
    public function uploadVideo(){
        $this->returnJson = true;//统一返回json
        $sys_mode_upfile_limit = settings('sys_mode_upfile_limit');
        if($_FILES['file']['size'] > ($sys_mode_upfile_limit * 1024 * 1024)){
            return $this->error('最大支持 '.$sys_mode_upfile_limit.'Mb 上传.');
        }
        if (strstr($_FILES["file"]['type'],'video/mp4') == false) {
            return $this->error('未能识别文件格式，请核实.');
        }
        $file_type = end(explode('.',$_FILES['file']['name']));
        $file_type = strtolower($file_type);
        if (in_array($file_type,['mp4']) == false){
            return $this->error('格式不对，只支持 (mp4格式)，请核实.');
        }
        $dir = config('config._upload_').'snap_file/';
        makeDir($dir);
        $file_name = random_str(16).'.'.$file_type;
        move_uploaded_file($_FILES['file']['tmp_name'],$dir.$file_name);
        $data['filename'] = trim($dir.$file_name,'.');
        return $this->success($data);
    }
}
?>