<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    
    private $tid;
    private $devType;
    private $taskParams = array();
    private $wxImgMediaIds = array();   //存储通过微信上传的图片的mediaid
    private $picUrls = array();
    
    private $errno = 0;
    private $errmsg = '';


    protected function getPara()
    {
        $this->tid = Tool_Input::clean('r', 'tid', TYPE_UINT);
        $this->devType = Tool_Input::clean('r', 'dev_type', TYPE_STR);
                
        $this->taskParams = array(
            'objid' => Tool_Input::clean('r', 'objid', TYPE_UINT),
            'objtype' => Tool_Input::clean('r', 'objtype', TYPE_UINT),
            'short_desc' => Tool_Input::clean('r', 'short_desc', TYPE_UINT),
            'exec_role' => Tool_Input::clean('r', 'exec_role', TYPE_UINT),
            'exec_suid' => Tool_Input::clean('r', 'exec_suid', TYPE_UINT),
            'title' => Tool_Input::clean('r', 'title', TYPE_STR),
            'content' => Tool_Input::clean('r', 'content', TYPE_STR),
        );
        
        $this->wxImgMediaIds = json_decode(Tool_Input::clean('r', 'wx_img_mediaid', TYPE_STR));
        
        if ($this->devType == 'h5')
        {
            if (empty($this->taskParams['exec_suid']) && empty($this->taskParams['exec_role']))
            {
                $this->taskParams['exec_suid'] = $this->_getExecSuid();
            }
        }
        
        // 销售提单，完全采用【认领】方式
        if ($this->taskParams['objtype']==Conf_Admin_Task::OBJTYPE_CUSTOMER
            && $this->taskParams['short_desc'] == 1)
        {
            $this->taskParams['exec_role'] = Conf_Admin::ROLE_CS;
            $this->taskParams['exec_suid'] = 0;
        }
        
    }
    
    protected function checkPara()
    {
        if (empty($this->taskParams['objtype']) || empty($this->taskParams['short_desc'])
            || (empty($this->taskParams['exec_suid'])&&empty($this->taskParams['exec_role']) ))
        {
            $this->errno = 100;
            $this->errmsg = '参数错误，请检查！';
            return;
        }
        
    }
    
    protected function main()
    {
        if ($this->retSt != 0)
        {
            return;
        }
        
        if (!empty($this->wxImgMediaIds))
        {
            $this->_downImageFromWX2HC();
            $this->taskParams['pic_ids'] = implode(',', $this->picUrls);
        }
        
        $this->taskParams['create_suid'] = $this->_uid;
        $this->taskParams['exec_status'] = Conf_Admin_Task::ST_WAIT_DEAL;
        $this->taskParams['level'] = Conf_Admin_Task::TASK_LEVEL_NORMAL;

        Admin_Task_Api::create($this->taskParams, $this->_uid);
        
    }
    
    protected function outputBody()
    {
        $ret = array('st'=>$this->errno, 'msg'=>$this->errmsg);
        
        $response = new Response_Ajax();
        $response->setContent($ret);
        $response->send();
        exit;
    }
    
    private function _getExecSuid()
    {
        $execSuid = 0;
        $_df = Conf_Admin_Task::$Default_Exec_Suid;
        
        $role = max(array_keys($this->_user['level']));
        
        if (array_key_exists($this->taskParams['objtype'], $_df))
        {
            $df = $_df[$this->taskParams['objtype']];
            if (array_key_exists($role, $df))
            {
                $execSuid = $df[$role][array_rand($df[$role], 1)];
            }
            else
            {
                $execSuid = $df[0][array_rand($df[0], 1)];
            }
        }
        else
        {
            $execSuid = $_df['default'][array_rand($_df['efault'], 1)];
        }
        
        return $execSuid;
    }
    
    
    
    private function _downImageFromWX2HC()
    {
        foreach($this->wxImgMediaIds as $mediaId)
        {
            $this->picUrls[] = WeiXin_Api::downloadImageFromWX2HC($mediaId);
        }
    }
    
}

$app = new App();
$app->run();