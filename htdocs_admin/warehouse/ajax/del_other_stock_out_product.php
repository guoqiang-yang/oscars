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
	private $sid;
	private $ret;

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
		$this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
	}

	protected function checkAuth()
    {
        parent::checkAuth('/warehouse/ajax/save_other_stock_out_order');
    }

    protected function main()
	{
        $this->ret = Warehouse_Api::delOtherStockOutProduct($this->oid, $this->sid);
	}
	
	protected function outputBody()
	{
		$result = array('ret' => $this->ret, 'oid' => $this->sid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App();
$app->run();