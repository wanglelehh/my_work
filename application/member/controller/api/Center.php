<?php
namespace app\member\controller\api;
use app\ApiController;

use app\member\model\UsersModel;
use app\member\model\NavMenuModel;
use app\member\model\FansModel;
use app\shop\model\OrderModel;

/*------------------------------------------------------ */
//-- 会员中心相关API
/*------------------------------------------------------ */

class Center extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        if (in_array($this->request->action(),['getcenterinfo','getcenternavmenu']) == false){
            $this->checkLogin();//验证登陆
        }
        $this->Model = new UsersModel();
    }
    /*------------------------------------------------------ */
    //-- 获取会员中心首页所需数据
    /*------------------------------------------------------ */
    public function getCenterInfo()
    {
        if (empty($this->userInfo['user_id'])){
            return $this->success();
        }
        //判断订单模块是否存在
        if(class_exists('app\shop\model\OrderModel')) {
            $OrderModel = new \app\shop\model\OrderModel();
            $data['orderStats'] = $OrderModel->userOrderStats($this->userInfo['user_id']);
            $where = [];
            $where[] = ['user_id','=',$this->userInfo['user_id']];
            $where[] = ['status','=',1];
            $collectNum = (new \app\shop\model\GoodsCollectModel)->where($where)->cache(true, 60)->count('collect_id');
            $data['collectNum'] = $collectNum;
            //$BonusModel = new \app\shop\model\BonusModel();
            //$bonus = $BonusModel->getListByUser();
            //$data['unusedNum'] = $bonus['unusedNum'];//未使用优惠券
        }
        $data['userInfo'] = $this->userInfo;
//        if($this->userInfo['user_id']>0){
//            if(empty($this->userInfo['signature']) && $this->userInfo['role_id']>12){
//                $return=[
//                    'code'=>400,
//                    'msg'=>'成为代理需签署合同,是否前往签署.'
//                ];
//                return $this->ajaxReturn($return);
////                return $this->error('您还未签署代理合同,请前往签署.');
//            }
//        }
        $where = [];
        $getIds=$this->Model->teamUid($this->userInfo['user_id']);
        $where[]=['user_id','in',$getIds];
        $where[]=['order_status','=',1];
        $where[]=['is_type','=',1];
        $data['teamConsume'] =sprintf("%.2f",(new OrderModel())->where($where)->sum('order_amount'));
        $data['teamCount'] = (new FansModel)->getFansCountToCenter($this->userInfo['user_id']);
        $data['role_contract_open'] = settings('role_contract_open');

        $data['unReadMsgNum'] = (new \app\mainadmin\model\MessageModel)->getMessageUnCount($this->userInfo['user_id']);
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 获取会员中心首页-我的工具
    /*------------------------------------------------------ */
    public function getCenterNavMenu()
    {
        $data['navMenu'] = (new NavMenuModel)->getRows(3);//我的工具
        return $this->success($data);
    }
    /*------------------------------------------------------ */
    //-- 我的签到
    /*------------------------------------------------------ */
    /*------------------------------------------------------ */
    public function signIndex()
    {
        $signInfo = $this->Model->signInfo($this->userInfo['user_id']);
        $signInfo['my_integral'] = $this->userInfo['account']['use_integral'] * 1;
        $signInfo['sign_integral'] = $this->Model->signIntegral();
        $signInfo['luckdraw_num'] = $this->Model->signLuckdrawNum();
        $signInfo['timeData'] = time();
        $constant_today = $signInfo['constant'];
        $constant_morn = $constant_today+1;
        $signInfo['sign_integral_morn'] = $signInfo['sign_integral'];
        $signInfo['luckdraw_num_morn'] = $signInfo['luckdraw_num'];
        foreach ($signInfo['sign_constant'] as $arr){
            if ($arr['day'] == $constant_today){
                $signInfo['sign_integral'] += $arr['integral'];
                $signInfo['luckdraw_num'] += $arr['luckdraw_num'];
            }elseif($arr['day'] == $constant_morn){
                $signInfo['sign_integral_morn'] += $arr['integral'];
                $signInfo['luckdraw_num_morn'] += $arr['luckdraw_num'];
            }
        }
        $signInfo['img_user_sign_top_bg'] = settings('img_user_sign_top_bg');
        $signInfo['img_user_sign_ok'] = settings('img_user_sign_ok');
        $signInfo['img_icon_integral'] = settings('img_icon_integral');
        return $this->success($signInfo);
    }
    /*------------------------------------------------------ */
    //-- 我的签到记录
    /*------------------------------------------------------ */
    /*------------------------------------------------------ */
    public function signLog()
    {
        $yearMonth = input('yearMonth','','trim');
        if (empty($yearMonth)){
            $yearMonth = date('Y-m');
        }
        $signLog = $this->Model->signLog($this->userInfo['user_id'],$yearMonth);
        return $this->success($signLog);
    }
    /*------------------------------------------------------ */
    //-- 签到
    /*------------------------------------------------------ */
    public function signIng()
    {
        $res = $this->Model->signIng($this->userInfo['user_id']);
        if (is_array($res) == false) {
            return $this->error($res);
        }
        return $this->success('签到成功.','',$res);
    }
}
