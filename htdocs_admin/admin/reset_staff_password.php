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
        $randNum = mt_rand(100000, 999999);
        $randSalt = mt_rand(1000, 9999);

        if (BASE_HOST != '.haocaisong.cn')
        {
            $randNum = 123456;
        }

        $password = md5($randSalt . ':' . $randNum);
        $update = array(
            'password' => $password,
            'salt' => $randSalt,
            'verify' => ''
        );
        Admin_Api::updateStaff($this->suid, $update);

        $staffInfo = Admin_Api::getStaff($this->suid);
        $words = '您的后台登录新密码是：' . $randNum;
        //发信息
        Data_Sms::send($staffInfo['mobile'], $words, 'verifycode');

        header('Location: /admin/staff_list.php');
    }
}

$app = new App('pri');
$app->run();
