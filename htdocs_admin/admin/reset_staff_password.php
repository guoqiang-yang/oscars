<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $suid;

    protected function getPara()
    {
        $this->suid = Tool_Input::clean('r', 'suid', TYPE_UINT);
    }

    protected function main()
    {
        $dfPasswd = '123456';
        $randSalt = mt_rand(1000, 9999);

        $password = md5($randSalt . ':' . $dfPasswd);
        $update = array(
            'password' => $password,
            'salt' => $randSalt,
            'verify' => ''
        );
        Admin_Api::updateStaff($this->suid, $update);

        header('Location: /admin/staff_list.php');
    }
}

$app = new App('pri');
$app->run();
