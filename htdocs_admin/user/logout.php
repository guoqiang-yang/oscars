<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    protected function getPara()
    {
    }

    protected function checkPara()
    {
    }

    protected function main()
    {
        self::clearVerifyCookie();
        header('Location: /user/login.php');
        exit;
    }
}

$app = new App('pub');
$app->run();
