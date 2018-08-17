<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $oid;   //销售单id

	//订单详情
	private $order;

	//供应商初始化
	private $suppliers;
	private $total;

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
	}

	protected function checkPara()
	{
		if (empty($this->oid))
		{
			throw new Exception('order:empty order id');
		}
	}

	protected function main()
	{
		//销售订单信息
		$this->order = Order_Api::getOrderInfo($this->oid, false);

		//供应商列表
		$order = 'order by sid desc';
		$res = Warehouse_Api::getSupplierList(array(), $order, 0, 1000);
		$this->suppliers = $res['list'];
		$this->total = $res['total'];

		//css/js
		$this->addFootJs(array('js/apps/in_order.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
		// 添加商品的对话框
		$this->smarty->assign('order', $this->order);
		$this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
		$this->smarty->assign('cate2_list_all', Conf_Sku::$CATE2);
		$this->smarty->assign('suppliers', $this->suppliers);
		$html = 'warehouse/add_in_order_quick.html';
		$this->smarty->display($html);
	}
}

$app = new App('pri');
$app->run();
