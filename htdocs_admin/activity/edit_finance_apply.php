<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $id;
    private $info;

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
    }

    protected function checkAuth()
    {
        parent::checkAuth('/activity/edit_finance_apply');
    }

    protected function main()
    {
        $this->info = Tpfinance_Api::get($this->id);

        $this->addFootJs(array('js/apps/tp_finance.js'));
    }

    protected function outputBody()
    {
        $this->smarty->assign('id', $this->id);
        $this->smarty->assign('info', $this->info);

        $this->smarty->display('activity/edit_finance_apply.html');
    }
}

$app = new App('pri');
$app->run();