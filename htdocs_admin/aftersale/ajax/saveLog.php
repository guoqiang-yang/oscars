<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/9/3
 * Time: 下午2:31
 */
include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    private $info;
    private $exec_role;
    private $exec_suid;
    private $action;
    private $method;
    private $join_suids;

    protected function getPara()
    {
        $this->method = Tool_Input::clean('r', 'assign', TYPE_UINT);
        $this->action = Tool_Input::clean('r', 'action', TYPE_UINT);
        $this->exec_suid = Tool_Input::clean('r', 'exec_suid', TYPE_UINT);
        $this->exec_role = Tool_Input::clean('r', 'exec_role', TYPE_UINT);
        $this->info = array(
            'sid' => Tool_Input::clean('r', 'sid', TYPE_UINT),
            'content' => Tool_Input::clean('r', 'content', TYPE_STR),
            'exec_suid' => $this->exec_suid,
        );
    }

    protected function checkAuth()
    {
        parent::checkAuth('/aftersale/deal');
    }

    protected function checkPara()
    {

        if (empty($this->info['sid']) || empty($this->action)) {
            throw new Exception('aftersale: not on rule');
        }
        if(!empty($this->exec_role))
        {
            $this->info['exec_department'] = $this->exec_role;
        }else{
            if($this->exec_suid>0){
                $user_info = Admin_Api::getStaff($this->exec_suid);
            }else{
                $user_info = Admin_Api::getStaff($this->_uid);
            }
            $this->info['exec_department'] = $user_info['role'];
        }
        $detail = Aftersale_Api::getDetail($this->info['sid']);
        if($this->info['exec_suid'] > 0 && !in_array($this->info['exec_suid'],explode(',',$detail['join_suids']))){
            $this->join_suids = $detail['join_suids'].','.$this->info['exec_suid'];
        }else{
            $this->join_suids = $detail['join_suids'];
        }
    }

    protected function main()
    {
        //未完成且未指派
        if ($this->action == 1 && $this->method == 3) {
            $this->info['action'] = Conf_Aftersale_Log::ACTION_UNASSIGN;
            $this->info['after_step'] = Conf_Aftersale::STATUS_UNDEAL;
            $this->info['exec_suid'] = $this->_uid;

            Aftersale_Log_Api::add($this->info);
            Aftersale_Api::update($this->info['sid'], array('exec_status'=>Conf_Aftersale::STATUS_UNDEAL,
                                                            'join_suids'=>$this->join_suids,
                                                            )
            );
        }
        //未完成且指派给其他组
        if (($this->action || $this->info['action'] == 3) && $this->method == 1) {
            $this->info['action'] = Conf_Aftersale_Log::ACTION_UNDEAL;
            $this->info['after_step'] = Conf_Aftersale::STATUS_NEW;

            Aftersale_Log_Api::add($this->info);
            Aftersale_Api::update($this->info['sid'], array('exec_status'=>Conf_Aftersale::STATUS_NEW,
                                                            'exec_suid'=>$this->exec_suid,
                                                            'duty_department'=>$this->exec_role,
                                                            'join_suids'=>$this->join_suids,
                                                            )
            );
            if($this->exec_suid>0 && $this->exec_suid != $this->_uid)
            {
                $after_info = Aftersale_Api::getDetail($this->info['sid']);
                $messageData = array(
                    'm_type' => 2,
                    'typeid' => $this->info['sid'],
                    'content' => '（【类型】'.$after_info['_type'].'；【状态】'.$after_info['_exec_status'].'）需要处理。',
                    'send_suid' => $this->_uid,
                    'receive_suid' => $this->exec_suid
                );
                Admin_Message_Api::create($messageData);
            }
        }
        //未完成且指派给组内其他人
        if (($this->action || $this->info['action'] == 3) && $this->method == 2) {
            $this->info['action'] = Conf_Aftersale_Log::ACTION_UNDEAL;
            $this->info['after_step'] = Conf_Aftersale::STATUS_NEW;

            Aftersale_Log_Api::add($this->info);
            Aftersale_Api::update($this->info['sid'], array('exec_status'=>Conf_Aftersale::STATUS_NEW,
                                                            'exec_suid'=>$this->exec_suid,
                                                            'join_suids'=>$this->join_suids,
                                                            )
            );
            if($this->exec_suid>0 && $this->exec_suid != $this->_uid)
            {
                $after_info = Aftersale_Api::getDetail($this->info['sid']);
                $messageData = array(
                    'm_type' => 2,
                    'typeid' => $this->info['sid'],
                    'content' => '（【类型】'.$after_info['_type'].'；【状态】'.$after_info['_exec_status'].'）需要处理。',
                    'send_suid' => $this->_uid,
                    'receive_suid' => $this->exec_suid
                );
                Admin_Message_Api::create($messageData);
            }
        }
        //处理完成
        if ($this->action == 2 ) {
            $this->info['action'] = Conf_Aftersale_Log::ACTION_DEAL;
            $this->info['after_step'] = Conf_Aftersale::STATUS_DEAL;
            $this->info['exec_suid'] = $this->_uid;
            $info = array('exec_status'=>Conf_Aftersale::STATUS_DEAL,
                'exec_suid'=>$this->_uid,
                'duty_department'=>Conf_Admin::ROLE_AFTER_SALE,
                'join_suids'=>$this->join_suids,
            );
            Aftersale_Log_Api::add($this->info);
            Aftersale_Api::update($this->info['sid'], $info);

            $after_info = Aftersale_Api::getDetail($this->info['sid']);
            $messageData = array(
                'm_type' => 2,
                'typeid' => $this->info['sid'],
                'content' => '（【类型】'.$after_info['_type'].'；【状态】'.$after_info['_exec_status'].'）已处理完成。',
                'send_suid' => $this->_uid,
                'receive_suid' => $after_info['create_suid']
            );
            Admin_Message_Api::create($messageData);
        }
        //关闭工单
        if ($this->action == 4 ) {
            //检查权限
            $isAftersale = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_AFTER_SALE_NEW);
            $isAdmin = Admin_Role_Api::isAdmin($this->_uid);
            $detail = Aftersale_Api::getDetail($this->info['sid']);
            if (!$isAftersale && !$isAdmin && ($this->_uid !=$detail['create_suid'] || ($this->_uid ==$detail['create_suid'] && in_array($detail['typeid'],array(12,13,14,15,16)))))
            {
                throw new Exception('aftersale: without authority');
            }
            $this->info['action'] = Conf_Aftersale_Log::ACTION_FINISH;
            $this->info['after_step'] = Conf_Aftersale::STATUS_FINISH;
            $this->info['exec_suid'] = $this->_uid;

            Aftersale_Log_Api::add($this->info);
            Aftersale_Api::update($this->info['sid'], array('exec_status'=>Conf_Aftersale::STATUS_FINISH,
                                                            'duty_department'=>Conf_Admin::ROLE_AFTER_SALE,
                                                            'join_suids'=>$this->join_suids,
                                                            )
            );
            $after_info = Aftersale_Api::getDetail($this->info['sid']);
            $messageData = array(
                'm_type' => 2,
                'typeid' => $this->info['sid'],
                'content' => '（【类型】'.$after_info['_type'].'；【状态】'.$after_info['_exec_status'].'）已关闭。',
                'send_suid' => $this->_uid,
                'receive_suid' => $after_info['create_suid']
            );
            Admin_Message_Api::create($messageData);
        }

    }

    protected function outputPage()
    {
        $result = array('id' => $this->action);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();
