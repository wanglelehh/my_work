<?php

namespace app\mainadmin\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 多语言包
/*------------------------------------------------------ */
class LanguagePackModel extends BaseModel
{
	protected $table = 'main_language_pack';
	public  $pk = 'id';
	public  $mkey = 'LanguagePackList_';
    public  $packLang = ['en'=>'英文'];
    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache()
    {
        foreach ($this->packLang as $key=>$val){
            Cache::rm($this->mkey.'0_'.$key);
            Cache::rm($this->mkey.'1_'.$key);
        }
    }
    //*------------------------------------------------------ */
    //-- 获取语言包
    /*------------------------------------------------------ */
    public function getLangPack($lang = '',$type = 0){
        $mkey = $this->mkey.$type.'_'.$lang;
        $langData = Cache::get($mkey);
        if (empty($langData) == false) return $langData;
        $rows = $this->where('type',$type)->order('strlen DESC')->select();
        $langData = [];
        foreach ($rows as $row){
            $row['replace_val'] = json_decode($row['replace_val'],true);
            if (empty($row['replace_val'][$lang]) == false){
                if ($type == 1){
                    $langData['search'][] = $row['keyword'];
                    $langData['replace'][] = $row['replace_val'][$lang];
                }else{
                    $langData[$row['keyword']] = $row['replace_val'][$lang];
                }
            }
        }
        Cache::set($mkey,$langData,300);
        return $langData;
    }
    //*------------------------------------------------------ */
    //-- 替换语言
    /*------------------------------------------------------ */
    public function langReplace($str = ''){
        $language = request()->header('language');
        if (empty($language) || $language == 'zh-CN'){
            return $str;
        }
        $langData = $this->getLangPack($language,1);
        if (empty($langData) == false){
            $str = str_replace($langData['search'],$langData['replace'],$str);
        }
        return $str;
    }
}
