<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    protected $title = '下单成功';

    protected function getPara()
    {

    }

    protected function checkAuth()
    {
        parent::checkAuth('/order/add_order_logistics_h5');
    }
    
    protected function main()
    {

    }

    protected function outputBody()
    {
        $this->smarty->display('order/add_order_succ_h5.html');
    }
}

$app = new App('pri');
$app->run();
