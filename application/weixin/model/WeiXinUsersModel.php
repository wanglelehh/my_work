<?php

namespace app\weixin\model;
use app\BaseModel;
use think\facade\Cache;
use think\Db;

use app\member\model\UsersModel;
//*------------------------------------------------------ */
//-- 微信会员
/*------------------------------------------------------ */
class WeiXinUsersModel extends BaseModel
{
	protected $table = 'weixin_users';
	public  $pk = 'wxuid';
	protected $mkey = 'weixin_users_mkey_';
	/*------------------------------------------------------ */
    //--  清除memcache
    /*------------------------------------------------------ */
    public function cleanMemcache($wxuid=0){
        Cache::rm($this->mkey.'_'.$wxuid);
    }

	/*------------------------------------------------------ */
	//-- 获取微信用户信息
	/*------------------------------------------------------ */
    public function info($id=0,$type='user'){
		if (empty($id)) return false;

		$info = Cache::get($this->mkey.$type.$id);
		if ($info) return $info;
		if ($type == 'user'){
            $where[] = ['user_id','=',$id];
        }else{
            $where[] = ['wxuid','=',$id];
        }
		$info = $this->where($where)->find();
		if (empty($info)) return [];
		$info = $info->toArray();
		Cache::set($this->mkey.$type.$id,$info,60);
		return $info;//如何存直接返回
	}
	/*------------------------------------------------------ */
	//-- 微信关联用户ID
	/*------------------------------------------------------ */
    public function bindUserId($wxuid,$user_id = 0){
		if ($user_id < 1 || $wxuid < 1) return false;
		$uparr['update_time'] = time();
		$uparr['user_id'] = $user_id;
		return $this->where('wxuid',$wxuid)->update($uparr);
	}
    /*------------------------------------------------------ */
    //-- 微信授权解密
    /*------------------------------------------------------ */
    public function WXBizDataCrypt($openid,$iv,$encryptedData){
        if (empty($openid)){
            return '缺少openId传参.';
        }
        if (empty($iv) || empty($encryptedData)){
            return '缺少授权数据传参.';
        }
        $sessionKey = $this->where('wx_openid',$openid)->value('session_key');
        if (strlen($sessionKey) != 24) {
            return 'encodingAesKey 非法';
        }
        $aesKey=base64_decode($sessionKey);
        if (strlen($iv) != 24) {
            return 'iv 非法';
        }
        $aesIV=base64_decode($iv);
        $aesCipher=base64_decode($encryptedData);
        $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $dataObj=json_decode( $result,true );
        if( empty($dataObj))
        {
            return 'aes 解密失败';
        }
        if( $dataObj['watermark']['appid'] != settings('xcx_appid'))
        {
            return 'aes 解密失败';
        }
        return $dataObj;
    }
}
