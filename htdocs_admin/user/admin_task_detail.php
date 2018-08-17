<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $tid;
    private $taskDetail;
    private $taskHistory;

    protected function getPara()
    {
        $this->tid = Tool_Input::clean('r', 'tid', TYPE_UINT);
    }

    protected function main()
    {
        $this->taskDetail = Admin_Task_Api::getDetail($this->tid);
        $this->taskHistory = Admin_Task_Api::getHistory($this->tid);
        $this->taskDetail['s_content'] = str_replace(array(',', '，', "\n"), array("<br>", "<br>", "<br>"), $this->taskDetail['content']);

        $this->addFootJs(array('js/apps/admin_task.js',));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $this->smarty->assign('all_exec_status', Conf_Admin_Task::$Exec_Task_Desc);
        $this->smarty->assign('all_objtype', Conf_Admin_Task::$Objtype_Desc);
        $this->smarty->assign('all_short_desc', Conf_Admin_Task::getShortDescOfObjtype());

        $allStaffs = Admin_Api::getStaffList(0, 0);
        $this->smarty->assign('all_staffs', $allStaffs['list']);

        $this->smarty->assignRaw('task_detail', $this->taskDetail);
        $this->smarty->assign('task_history', $this->taskHistory);

        $this->smarty->display('user/admin_task_detail.html');
    }
}

$app = new App();
$app->run();