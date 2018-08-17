<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $modifyUsers;
    protected function main()
    {
        $this->modifyUsers = Admin_Api::updateAllStaff4DingTalk();
        $this->addFootJs(array());
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $this->smarty->assign('modify_users', $this->modifyUsers);
        $this->smarty->display('admin/update_staff_ding.html');
    }
}

$app = new App('pri');
$app->run();
