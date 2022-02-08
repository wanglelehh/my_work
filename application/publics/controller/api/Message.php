<?php

namespace app\publics\controller\api;
use app\ApiController;
use app\mainadmin\model\MessageModel;
use app\mainadmin\model\MessageUserModel;

/*------------------------------------------------------ */
//-- 站内消息相关API
/*------------------------------------------------------ */

class Message extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
    }

    /*------------------------------------------------------ */
    //-- 获取消息列表
    /*------------------------------------------------------ */
    public function getList()
    {
        $state = input('state','waitCheck','trim');
        $MessageModel = new MessageModel();
        $MessageUserModel = new MessageUserModel();
        $this->sqlOrder = 'send_time DESC';
        if ($state == 'my'){
            $where = [];
            $where[] = ['user_id','=',$this->userInfo['user_id']];
            $where[] = ['status','=',1];
            $where[] = ['send_time','<',time()];
            $where[] = ['show_end_time','>',time()];
            $data = $this->getPageList($MessageUserModel, $where,'',8);
            foreach ($data['list'] as $key=>$row){
                $row['send_time'] = dateTpl($row['send_time']);
                if ($row['type'] == 0){
                    $message = $MessageModel->where('message_id',$row['ext_id'])->find();
                    if ($message['article_id'] > 0){
                        $row['ext_id'] = $message['article_id'];
                    }else{
                        $row['ext_id'] = 0;
                    }
                }
                $data['list'][$key] = $row;
            }
        }else{//系统公告
            $where = [];
            $where[] = ['type','=','all'];
            $where[] = ['status','=',0];
            $where[] = ['send_time','<',time()];
            $where[] = ['show_end_time','>',time()];
            $data = $this->getPageList($MessageModel, $where,'',8);
            foreach ($data['list'] as $key=>$row){
                $muWhere = [];
                $muWhere[] = ['type','=','all'];
                $muWhere[] = ['ext_id','=',$row['message_id']];
                $muWhere[] = ['user_id','=',$this->userInfo['user_id']];
                $row['is_see'] = $MessageUserModel->where($muWhere)->value('is_see');
                $row['send_time'] = dateTpl($row['send_time']);
                $data['list'][$key] = $row;
            }
        }
        return $this->success($data);
    }

    /*------------------------------------------------------ */
    //-- 设置为已查看
    /*------------------------------------------------------ */
    public function setSee()
    {
        $type = input('type', '', 'trim');
        $rec_id = input('rec_id', 0, 'intval');
        if ($rec_id < 1){
            return $this->success();
        }
        $MessageUserModel = new MessageUserModel();
        $where = [];
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $upData['is_see'] = 1;
        $upData['see_time'] = time();
        if ($type == 'all'){
            $where[] = ['type','=',$type];
            $where[] = ['ext_id','=',$rec_id];
            $rowObj = $MessageUserModel->where($where)->find();
            if (empty($rowObj)){
                $inData = [];
                $inData['type'] = $type;
                $inData['ext_id'] = $rec_id;
                $inData['user_id'] = $this->userInfo['user_id'];
                $inData['is_see'] = 1;
                $inData['see_time'] = time();
                $MessageUserModel->save($inData);
                return $this->success();
            }
            $MessageUserModel->where($where)->update($upData);
        }else{
            $where[] = ['rec_id','=',$rec_id];
            $rowObj = $MessageUserModel->where($where)->find();
            if (empty($rowObj)){
                return $this->success();
            }
            $MessageUserModel->where($where)->update($upData);
        }

        return $this->success();
    }

}

