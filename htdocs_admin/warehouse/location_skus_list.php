<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
	private $num = 20;
	private $start;
	private $wid;
	private $locationList;
	private $total;

	protected function getPara()
	{
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);

		$wid = $this->getWarehouseId();
		$this->wid = !empty($wid) ? $wid : Conf_Warehouse::WID_3;
	}

	protected function main()
	{
		$data = Warehouse_Location_Api::oneLocationManySkus($this->wid, $this->start, $this->num);
		$this->locationList = $data['list'];
		$this->total = $data['total'];
	}

	protected function outputBody()
	{
		$app = '/warehouse/location_skus_list.php?' . http_build_query($this->search);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('wid', $this->wid);
		$this->smarty->assign('total', $this->total);
		$this->smarty->assign('location_list', $this->locationList);
		$this->smarty->assign('allowed_warehouses', $this->getAllowedWarehouses(1));
		$this->smarty->assign('all_warehouses', Conf_Warehouse::$WAREHOUSES);

		$this->smarty->display('warehouse/location_skus_list.html');
	}
}

$app = new App();
$app->run();