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
        parent::checkAuth('/warehouse/ajax/save_other_stock_out_order');
    }

    protected function main()
	{
        if (!empty($this->oid))
        {
            $wosoo = new Warehouse_Other_Stock_Out_Order();
            $order = $wosoo->get($this->oid);
            if ($order['order_type'] == Conf_Stock::OTHER_STOCK_ORDER_TYPE_OUT && $order['type'] == Conf_Stock::OTHER_STOCK_OUT_TYPE_BROKEN)
            {
                $this->order = Warehouse_Api::getOtherStockOutOrderBrokenDetail($this->oid);
            }
            else
            {
                $this->order = Warehouse_Api::getOtherStockOutOrderDetail($this->oid);
            }
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
		$this->smarty->assign('type_list', Conf_Stock::getOtherStockTypes());
		$this->smarty->assign('reason_list', Conf_Stock::getOtherStockOrderReasons());

		$this->smarty->display('warehouse/add_other_stock_out_order.html');
	}
}

$app = new App();
$app->run();
