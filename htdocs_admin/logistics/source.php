<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $sourceList;

	protected function getPara()
	{
	}

	protected function main()
	{
		$this->sourceList = Logistics_Api::getSourceList();

		$this->addFootJs(array('js/apps/role.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$this->smarty->assign('source_list', $this->sourceList['list']);
		$this->smarty->assign('total', $this->sourceList['total']);
		$this->smarty->assign('warehouse', Conf_Warehouse::$WAREHOUSES);

		$this->smarty->display('logistics/source.html');
	}
}

$app = new App('pri');
$app->run();
