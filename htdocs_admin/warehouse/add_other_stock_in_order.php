<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
    private $oid;
    private $order;

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
    }

    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/ajax/save_other_stock_in_order');
    }

    protected function main()
	{
        if (!empty($this->oid))
        {
            $this->order = Warehouse_Api::getOtherStockOutOrderDetail($this->oid);
        }
		$this->addFootJs(array('js/apps/stock.js', 'js/apps/in_order.js', 'js/apps/show_product_common.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
        if (!empty($this->oid))
        {
            $this->smarty->assign('order', $this->order);
        }
		$this->smarty->assign('type_list', Conf_Stock::getOtherStockTypes(Conf_Stock::OTHER_STOCK_ORDER_TYPE_IN));
		$this->smarty->assign('reason_list', Conf_Stock::getOtherStockOrderReasons(Conf_Stock::OTHER_STOCK_ORDER_TYPE_IN));

		$this->smarty->display('warehouse/add_other_stock_in_order.html');
	}
}

$app = new App();
$app->run();
