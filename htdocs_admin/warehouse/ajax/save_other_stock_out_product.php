<?php

/**
 * 添加移库单的商品.
 * 
 *  1 添加移库单商品
 *  2 分配货位，数量【不写占用】，并记录到t_stock_shift_product表中的from_location字段
 *  3 出库时，处理库存/货位库存
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $oid;
	private $products;
    private $ret;
    private $orderType;

	protected function getPara()
	{
		$products = Tool_Input::clean('r', 'products', TYPE_ARRAY);
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
		$this->orderType = Tool_Input::clean('r', 'order_type', TYPE_UINT);

		foreach($products as $one)
		{
			list($_sid, $_num) = explode(':', $one);
			$this->products[] = array(
				'sid' => $_sid,
				'num' => $_num,
			);
		}
	}

	protected function checkAuth()
    {
        if ($_REQUEST['order_type'] == Conf_Stock::OTHER_STOCK_ORDER_TYPE_OUT)
        {
            parent::checkAuth('/warehouse/ajax/save_other_stock_out_order');
        }
        else if ($_REQUEST['order_type'] == Conf_Stock::OTHER_STOCK_ORDER_TYPE_IN)
        {
            parent::checkAuth('/warehouse/ajax/save_other_stock_in_order');
        }

    }

    protected function main()
	{
        $this->ret = Warehouse_Api::saveOtherStockOutOrderProducts($this->oid, $this->products);
	}
	
	protected function outputBody()
	{
		$result = array('ret' => $this->ret, 'oid' => $this->oid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App();
$app->run();