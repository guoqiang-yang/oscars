<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $oid;
    private $order;
    private $staffList;

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
    }

    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/other_stock_in_order');
    }

    protected function main()
    {
        $this->order = Warehouse_Api::getOtherStockOutOrderDetail($this->oid);
        $staffList = Admin_Api::getStaffList();
        $this->staffList = Tool_Array::list2Map($staffList['list'], 'suid', 'name');
        $this->addFootJs('js/apps/stock.js');
    }

    protected function outputBody()
    {
        $this->smarty->assign('order', $this->order);
        $this->smarty->assign('staff_list', $this->staffList);
        $this->smarty->assign('reason_list', Conf_Stock::getOtherStockOrderReasons(Conf_Stock::OTHER_STOCK_ORDER_TYPE_IN));
        $this->smarty->assign('type_list', Conf_Stock::getOtherStockTypes(Conf_Stock::OTHER_STOCK_ORDER_TYPE_IN));
        $this->smarty->assign('step_list', Conf_Stock::$OTHER_STOCK_OUT_ORDER_STEPS);
        $this->smarty->display('warehouse/other_stock_in_order_detail.html');
    }
}

$app = new App();
$app->run();