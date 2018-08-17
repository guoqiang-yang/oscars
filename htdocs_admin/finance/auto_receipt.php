<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
    private $oid;
    private $orderInfo;

    protected function checkAuth()
    {
        parent::checkAuth('/order/auto_receipt');
    }

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
    }

    protected function main()
    {
        if ($this->oid)
        {
            $this->orderInfo = Order_Api::getOrderInfo($this->oid);
        }
        $this->addFootJs('js/apps/auto_receipt.js');
    }

    protected function outputBody()
    {
        if ($this->oid)
        {
            $this->smarty->assign('pay_status', Conf_Order::$PAY_STATUS);
            $this->smarty->assign('step_list', Conf_Order::$ORDER_STEPS);
            $this->smarty->assign('search_oid', $this->oid);
            $this->smarty->assign('order', $this->orderInfo);
            $this->smarty->assign('warehouse_list', Conf_Warehouse::$WAREHOUSES);
        }

        $this->smarty->display('order/auto_receipt.html');
    }
}

$app = new App('pri');
$app->run();