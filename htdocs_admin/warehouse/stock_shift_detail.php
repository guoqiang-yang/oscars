<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
	//选择商品的初始参数
	private $ssid;
	
	private $shiftInfos = array();
	
	protected function getPara()
	{
		$this->ssid = Tool_Input::clean('r', 'ssid', TYPE_UINT);
	}
	
	protected function checkPara()
	{
		if (empty($this->ssid))
		{
			throw new Exception('stock shift id is empty');
		}
	}
	
	protected function main()
	{
		$this->shiftInfos = Warehouse_Api::getStockShiftInfo($this->ssid);
		
		$this->addFootJs(array('js/apps/stock.js'));
		$this->addCss(array());
	}
	
	protected function outputBody()
	{
		$this->smarty->assign('step_descs', Conf_Stock_Shift::$Step_Descs);
		$this->smarty->assign('warehouses', Conf_Warehouse::$WAREHOUSES);
		$this->smarty->assign('ssid', $this->ssid);
		$this->smarty->assign('shift_info', $this->shiftInfos);
        
        $this->smarty->assign('is_upgrade_wid', Conf_Warehouse::isUpgradeWarehouse($this->shiftInfos['des_wid']));
		
		$this->smarty->display('warehouse/stock_shift_detail.html');
	}
}

$app = new App('pri');
$app->run();