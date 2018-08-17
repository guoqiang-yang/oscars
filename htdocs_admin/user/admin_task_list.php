<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $num = 20;
    private $start;
    private $searchConf;
    
    private $taskList;
    private $staffs;
    
    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        
        $this->searchConf = array(
            'suid' => Tool_Input::clean('r', 'suid', TYPE_UINT),
            'objtype' => Tool_Input::clean('r', 'objtype', TYPE_UINT),
            'exec_status' => Tool_Input::clean('r', 'exec_status', TYPE_UINT),
            'objid' => Tool_Input::clean('r', 'objid', TYPE_UINT),
            'short_desc' => Tool_Input::clean('r', 'short_desc', TYPE_UINT),
        );
        
        if (!isset($_REQUEST['exec_status']))
        {
            $this->searchConf['exec_status'] = Conf_Admin_Task::ST_WAIT_DEAL;
        }
        
        $roles = array_keys($this->_user['level']);
        $this->searchConf['exec_role'] = $roles[0];
        
//        if (!array_key_exists(Conf_Admin::ROLE_ADMIN, $this->_user['level'])
//            &&!in_array($this->_uid, array(1098, 1099)))
//        {
//            $this->searchConf['suid'] = $this->_uid;
//        }
    }
    
    protected function main()
    {
        $this->taskList = Admin_Task_Api::getList($this->searchConf, $this->start, $this->num);
        
        $this->staffs = Admin_Api::getStaffList(0, 0);
        
        $this->addFootJs(array('js/apps/admin_task.js',));
		$this->addCss(array());
    }
    
    protected function outputBody()
    {
        
        $app = '/user/admin_task_list.php?'. http_build_query($this->searchConf);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->taskList['total'], $app);
        
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('all_exec_status', Conf_Admin_Task::$Exec_Task_Desc);
        $this->smarty->assign('all_objtype', Conf_Admin_Task::$Objtype_Desc);
        $this->smarty->assign('all_short_desc', Conf_Admin_Task::getShortDescOfObjtype());
        $this->smarty->assign('all_staffs', $this->staffs['list']);
        $this->smarty->assign('_uid', $this->_uid);
        $this->smarty->assign('_all_roles', Conf_Admin::$Role_Descs);
        
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('taskList', $this->taskList['data']);
        $this->smarty->assign('total', $this->taskList['total']);
        
        $this->smarty->display('user/admin_task_list.html');
    }
}

$app = new App();
$app->run();