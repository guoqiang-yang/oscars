<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
	private $search;
	private $locationList;
	private $total;

	protected function getPara()
	{
        $city = City_Api::getCity();
        $dfWid = Conf_Warehouse::$WAREHOUSE_CITY[$city['city_id']][0];
		$wid = $this->getWarehouseId();
		$this->search = array(
			'wid' => !empty($wid) ? $wid : $dfWid,
			'lstart' => strtoupper(Tool_Input::clean('r', 'lstart', TYPE_STR)), //按货区筛选
			'lend' => Tool_Input::clean('r', 'lend', TYPE_STR),          //按货架筛选
		);
	}

	protected function main()
	{
		$data = Warehouse_Location_Api::exportLocation($this->search);
		$this->locationList = $data['list'];
		$this->total = $data['total'];
	}

	protected function outputBody()
	{
		$this->smarty->assign('search', $this->search);
		$this->smarty->assign('total', $this->total);
		$this->smarty->assign('location_list', $this->locationList);

		$this->smarty->display('warehouse/location_export.html');
	}
}

$app = new App();
$app->run();