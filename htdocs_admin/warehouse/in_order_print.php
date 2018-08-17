<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
	private $oid;

	private $order;
	private $stockList;
	private $buyerInfo;
	protected $headTmpl = 'head/head_none.html';
	protected $tailTmpl = 'tail/tail_none.html';

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
	}

	protected function main()
	{
		$this->order = Warehouse_Api::getOrderInfo($this->oid);
		$this->stockList = Warehouse_Api::getStockInLists(array('oid' => $this->oid), '', 0, 1000);

		if (!empty($this->order['info']['buyer_uid']))
		{
			$this->buyerInfo = Admin_Api::getStaff($this->order['info']['buyer_uid']);
		}


		$this->addFootJs(array('js/apps/in_order_print.js'));
	}

	protected function outputBody()
	{

		$this->smarty->assign('order', $this->order);
		$this->smarty->assign('buyer', $this->buyerInfo);
		$this->smarty->assign('payment_types', Conf_Stock::$PAYMENT_TYPES);

		$this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
		$this->smarty->assign('cate2_list_all', Conf_Sku::$CATE2);

		$this->smarty->display('warehouse/in_order_print.html');
	}

}

$app = new App();
$app->run();