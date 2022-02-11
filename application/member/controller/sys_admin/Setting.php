<?php
namespace app\member\controller\sys_admin;
use app\AdminController;
use app\mainadmin\model\SettingsModel;
/**
 * 设置
 * Class Index
 * @package app\store\controller
 */
class Setting extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new SettingsModel();		
    }
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
    public function index(){
        $setting = $this->Model->getRows();
        if (empty($setting['sign_constant']) == false){
            $setting['sign_constant'] = json_decode($setting['sign_constant'],true);
        }else{
            $setting['sign_constant'] = [];
        }
		$this->assign("setting", $setting);
        return $this->fetch();
    }
	/*------------------------------------------------------ */
	//-- 保存配置
	/*------------------------------------------------------ */
    public function save(){
        $set = input('post.');
        if (empty($set['sign_constant']) == false){
            $day = 0;
            $constants = $set['sign_constant'];
            unset($set['sign_constant']);
            foreach ($constants as $constant){
                if ($day >= $constant['day']){
                    return $this->error('连续签到天数设置错误，天数必须从上到下递增.'.$day.' - '.$constant['day']);
                }
                $day = $constant['day'];
                $set['sign_constant'][$day] = $constant;
            }
            $set['sign_constant'] = json_encode($set['sign_constant']);
        }
        $set['member_receive_alipay'] = input('member_receive_alipay',0,'intval');
        $set['member_receive_wxpay'] = input('member_receive_wxpay',0,'intval');
        $set['member_receive_bank'] = input('member_receive_bank',0,'intval');
		$res = $this->Model->editSave($set);
		if ($res == false) return $this->error();
		return $this->success('设置成功.');
    }

    public function testPdfContract(){
        $contract = settings('role_contract');
        $channel_contract_first_party = settings('role_contract_first_party');
        $channel_contract_seal = settings('role_contract_seal');
        $channel_contract_seal_x = settings('role_contract_seal_x');
        $channel_contract_seal_y = settings('role_contract_seal_y');
        $contract = str_replace('{甲方姓名}',$channel_contract_first_party,$contract);
        $contract = str_replace('{乙方姓名}','测试',$contract);
        $contract = str_replace('{乙方身份证号码}','440xxxxxxxxxxxxx',$contract);
        $this->assign("contract",$contract);
        $this->assign("first_party",$channel_contract_first_party);
        $this->assign("signingDate",date('Y年m月d日'));
        $this->assign("partyBSign",'/a_static/main/img/test_sign.png');
        $html = $this->fetch('pdf/tpl')->getContent();
        htmlToPdf($html,'测试','测试',$channel_contract_seal,$channel_contract_seal_x,$channel_contract_seal_y,"I");
    }

}
