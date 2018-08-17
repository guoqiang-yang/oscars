<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/11/17
 * Time: 上午10:43
 */
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $checkInUrl;
    protected $headTmpl = 'head/head_none.html';
    protected $tailTmpl = 'tail/tail_none.html';

    protected function main()
    {
        $wid = $this->_user['wid'];
       /* $isAdmin = Admin_Role_Api::isAdmin($this->_uid);
        if ($isAdmin)
        {
            $wid = 5;
        }*/
        $code = md5(date('Y-m-d').Conf_Base::APP_SECRET);
        $code = $wid.$code;
        $this->checkInUrl = $code;
        $this->checkInUrl = urlencode($this->checkInUrl);
    }

    protected function outputBody()
    {
        $this->smarty->assign('url', $this->checkInUrl);

        $this->smarty->display('logistics/print_qrcode_app.html');
    }
}

$app = new App('pri');
$app->run();