<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    
    private $tid;
    private $otype;
    private $exec_status;
    private $exec_suid;
    private $exec_role;
    private $content;
    
    private $taskInfo;
    private $upData;
    private $note;

    protected function getPara()
    {
        $this->tid = Tool_Input::clean('r', 'tid', TYPE_UINT);
        $this->otype = Tool_Input::clean('r', 'otype', TYPE_STR);
        $this->exec_status = Tool_Input::clean('r', 'exec_status', TYPE_UINT);
        $this->exec_suid = Tool_Input::clean('r', 'exec_suid', TYPE_UINT);
        $this->exec_role = Tool_Input::clean('r', 'exec_role', TYPE_UINT);
        $this->content = Tool_Input::clean('r', 'content', TYPE_STR);
        $this->note = Tool_Input::clean('r', 'note', TYPE_STR);
        
        $this->taskInfo = Admin_Task_Api::getDetail($this->tid);
    }
    
    protected function checkPara()
    {
        if (empty($this->taskInfo))
        {
            throw new Exception('任务不存在，不能修改！');
        }
        
        switch ($this->otype)
        {
            case 'modify_exec_status':
                $this->_chkExecStatus();
                $this->upData['exec_status'] = $this->exec_status;
                $this->note = !empty($this->note)? $this->note: '更新状态';
                break;
            case 'modify_more_info':
                $this->_chkExecSuid();
                
                // 销售提单不能修改执行人
                if (!($this->taskInfo['objtype']==Conf_Admin_Task::OBJTYPE_CUSTOMER && $this->taskInfo['short_desc']==1)
                    ||Admin_Role_Api::isAdmin($this->_uid, $this->_user) )
                {
                    $this->upData['exec_suid'] = $this->exec_suid;
                }
                // 销售提单
                else if ($this->taskInfo['objtype']==Conf_Admin_Task::OBJTYPE_CUSTOMER && $this->taskInfo['short_desc']==1)
                {
                    if ($this->taskInfo['exec_suid']!=$this->exec_suid)
                    {
                        throw new Exception('销售提单，不能修改[执行人]，如果修改请联系管理员！');
                    }
                    if (!empty($this->taskInfo['exec_suid']) && $this->taskInfo['content']!=$this->content)
                    {
                        throw new Exception('已经被认领，可备注清楚，并请联系对应客服修改！');
                    }
                }
                
                if (!empty($this->content))
                {
                    $this->upData['content'] = $this->content;
                }
                
                if ($this->taskInfo['exec_suid']!=$this->exec_suid)
                {
                    $this->note .= !empty($this->note)? ' ; 更新执行人': '更新执行人';
                }
                if ($this->taskInfo['content']!=$this->content)
                {
                    $note = '[更新内容]：[src] '.$this->taskInfo['content'].' [desc] '.  $this->content;
                    $this->note .= !empty($this->note)? ' ; '.$note: $note;
                }
                
                break;
            case 'modify_exec_suid':
                $this->_chkExecSuid();
                $this->upData['exec_suid'] = $this->exec_suid;
                
                if (empty($this->exec_role))
                {
                    $staffInfo = Admin_Api::getStaff($this->exec_suid);
                    if (!empty($staffInfo))
                    {
                        $roles = array_keys($staffInfo['level']);
                        $this->upData['exec_role'] = $roles[0];
                    }
                    else
                    {
                        throw new Exception('执行人不存在，请重新选择！');
                    }
                }
                
                $this->note = !empty($this->note)? $this->note: '更新执行人';
                break;
            case 'get_task_tome':
                if (!empty($this->taskInfo['exec_suid']))
                {
                    throw new Exception('任务已经被认领，请刷新！');
                }

				$conf = array(
					'suid' => $this->_uid,
					'objtype' => 2,
					'exec_status' => Conf_Admin_Task::ST_WAIT_DEAL,
				);
				$check = Admin_Task_Api::getList($conf, 0, 1);
				if ($check['total'] > 0)
				{
					throw new Exception('尚有未入完单子，请完成后再认领其他任务！');
				}
                
                $this->upData['exec_suid'] = $this->_uid;
                
                $roles = array_keys($this->_user['level']);
                $this->upData['exec_role'] = $roles[0];
            case 'save_note';
                
                break;
            default:
                throw new Exception('该操作类型不存在！');
        }
    }
    
    protected function main()
    {
        Admin_Task_Api::update($this->taskInfo, $this->upData, $this->_uid, $this->note);
    }
    
    protected function outputBody()
    {
        $ret = array(
            'st'=>$this->errno, 
            'msg'=>$this->errmsg,
            'data'=>array(
                'tid' => $this->tid,
            )
        );
        
        $response = new Response_Ajax();
        $response->setContent($ret);
        $response->send();
        exit;
    }
    
    
    /**
     * 是否可以修改任务状态.
     */
    private function _chkExecStatus()
    {
        // 管理员
        if (Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_ADMIN_NEW))
        {
            return;
        }
        
        // 非管理员
        switch($this->exec_status)
        {
            case Conf_Admin_Task::ST_COMPLETE:
                if ($this->taskInfo['exec_status']!=Conf_Admin_Task::ST_WAIT_DEAL){
                    throw new Exception('任务不能修改！');
                }
                if ($this->taskInfo['exec_suid']!=$this->_uid){
                    throw new Exception('只有执行人才可以处理！');
                }
                break;
            case Conf_Admin_Task::ST_CLOSE:
            case Conf_Admin_Task::ST_DELETE:
                if ($this->taskInfo['create_suid']!=$this->_uid){
                    throw new Exception('只有创建人才可以操作！');
                }
                break;
            case Conf_Admin_Task::ST_WAIT_DEAL: //重新打开
                if ($this->taskInfo['exec_status']!=Conf_Admin_Task::ST_COMPLETE
                    || $this->taskInfo['exec_status']!=Conf_Admin_Task::ST_DELETE
                    || $this->taskInfo['exec_suid']!=$this->_uid)
                {
                    throw new Exception('不能重新打开！只有创建人可以打开！');
                }
                break;
                
            default:
                throw new Exception('非法操作！');
        }
        
    }
    
    private function _chkExecSuid()
    {
        // 管理员
        if (Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_ADMIN_NEW))
        {
            return;
        }
        
        if ($this->taskInfo['exec_status']!=Conf_Admin_Task::ST_WAIT_DEAL
            || ($this->taskInfo['exec_suid']!=$this->_uid
                && $this->taskInfo['create_suid']!=$this->_uid) )
        {
            throw new Exception('不能修改！');
        }
    }
}

$app = new App();
$app->run();