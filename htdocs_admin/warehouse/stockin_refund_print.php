<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
	private $srid;

	private $refund;
	protected $headTmpl = 'head/head_none.html';
	protected $tailTmpl = 'tail/tail_none.html';

	protected function getPara()
	{
		$this->srid = Tool_Input::clean('r', 'srid', TYPE_UINT);
	}

	protected function main()
	{
        $wsir = new Warehouse_Stock_In_Refund();
        $this->refund = $wsir->get($this->srid);
        $this->refund['products'] = Warehouse_Api::getRefundProductsBySrid($this->srid);
        $this->refund['supplier'] = Warehouse_Api::getSupplier($this->refund['supplier_id']);
        $this->refund['create_user'] = Admin_Api::getStaff($this->refund['suid']);
	}

	protected function outputBody()
	{
		$this->smarty->assign('refund', $this->refund);

		$this->smarty->display('warehouse/stockin_refund_print.html');
	}

}

$app = new App('pub');
$app->run();