<?php
/**
 * Created by PhpStorm.
 * User: libaolong
 * Date: 2018/6/29
 * Time: 上午10:09
 */
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
        $staffInfo = Admin_Api::getStaff($this->suid);
        $mem = Data_Memcache::getInstance();
        $times = $mem->get('lgin_lmt'.$staffInfo['mobile']);

        if ($times >= 5)
        {
            $mem->delete('lgin_lmt'.$staffInfo['mobile']);
        }

        header('Location: /admin/staff_list.php');
    }
}

$app = new App('pri');
$app->run();
