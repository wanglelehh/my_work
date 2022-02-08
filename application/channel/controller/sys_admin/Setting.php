<?php
namespace app\channel\controller\sys_admin;
use app\AdminController;

use app\mainadmin\model\SettingsModel;
use app\mainadmin\model\PaymentModel;
/**
 * 渠道设置
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
	//-- 主页
	/*------------------------------------------------------ */
    public function index(){
        $this->assign("pay_list",(new PaymentModel)->getRows());
        $setting = $this->Model->getRows();
        $setting['channel_order_payment'] = explode(',',$setting['channel_order_payment']);
        $setting['channel_recharge_payment'] = explode(',',$setting['channel_recharge_payment']);
		$this->assign("setting",$setting);
		return $this->fetch();
	}
	/*------------------------------------------------------ */
	//-- 保存配置
	/*------------------------------------------------------ */
    public function save(){
        $set = input('post.');
        if (empty($set['channel_sms_register'])){
            $set['channel_sms_register'] = 0;
        }
        if (empty($set['channel_sms_login'])){
            $set['channel_sms_login'] = 0;
        }
        if (empty($set['channel_sms_forget_pw'])){
            $set['channel_sms_forget_pw'] = 0;
        }
        //支付相关处理
        if (empty($set['channel_order_payment'])){
            $set['channel_order_payment'] = '';
        }else{
            $set['channel_order_payment'] = join(',',$set['channel_order_payment']);
        }
        if (empty($set['channel_recharge_payment'])){
            $set['channel_recharge_payment'] = '';
        }else{
            $set['channel_recharge_payment'] = join(',',$set['channel_recharge_payment']);
        }
        //支付相关处理
		$res = $this->Model->editSave($set);
		if ($res == false) return $this->error();
		return $this->success('设置成功.');
    }
    /*------------------------------------------------------ */
    //-- 生成测试合同
    /*------------------------------------------------------ */
    public function testPdfContract(){
        $contract = settings('channel_contract');
        $channel_contract_first_party = settings('channel_contract_first_party');
        $channel_contract_seal = settings('channel_contract_seal');
        $channel_contract_seal_x = settings('channel_contract_seal_x');
        $channel_contract_seal_y = settings('channel_contract_seal_y');
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
