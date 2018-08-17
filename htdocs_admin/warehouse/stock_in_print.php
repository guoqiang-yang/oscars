<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $id;
	private $oid;
	private $type;
	private $stockInDetail = array();
	private $order;
	protected $headTmpl = 'head/head_none.html';
	protected $tailTmpl = 'tail/tail_none.html';

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
		$this->type = Tool_Input::clean('r', 'type', TYPE_UINT);
	}

	protected function main()
	{
		if ($this->id)
		{
			$this->stockInDetail = Warehouse_Api::getStockInDetail($this->id);
			$oid = $this->stockInDetail['info']['oid'];
		}
		else if($this->oid)
		{
			$oid = $this->oid;
		}
		if ($oid)
		{
			$this->order = Warehouse_Api::getOrderInfo($oid);
		}
        
		$this->addFootJs(array('js/apps/stock.js'));
		$this->addCss(array());
	}
    
	protected function outputBody()
	{
		$this->smarty->assign('order', $this->order);
		$this->smarty->assign('type', $this->type);
		$this->smarty->assign('stock_in', $this->stockInDetail);
		$this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
		$this->smarty->assign('cate2_list_all', Conf_Sku::$CATE2);
		$this->smarty->assign('payment_types', Conf_Stock::$PAYMENT_TYPES);
        $this->smarty->assign('paid_sources', Conf_Finance::$MONEY_OUT_PAID_TYPES);

		$this->smarty->display('warehouse/stock_in_print.html');
	}
}

$app = new App('pri');
$app->run();
