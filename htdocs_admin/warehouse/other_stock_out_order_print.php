<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $oid;
    private $order;
    private $staffList;

    protected $headTmpl = 'head/head_none.html';
    protected $tailTmpl = 'tail/tail_none.html';

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
    }

    protected function main()
    {
        $this->order = Warehouse_Api::getOtherStockOutOrderDetail($this->oid);
        $staffList = Admin_Api::getStaffList();
        $this->staffList = Tool_Array::list2Map($staffList['list'], 'suid', 'name');
    }

    protected function outputBody()
    {
        $this->smarty->assign('order', $this->order);
        $this->smarty->assign('staff_list', $this->staffList);
        $this->smarty->assign('type_list', Conf_Stock::getOtherStockTypes());
        $this->smarty->display('warehouse/other_stock_out_order_print.html');
    }
}

$app = new App('pub');
$app->run();