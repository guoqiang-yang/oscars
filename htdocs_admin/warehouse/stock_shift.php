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
	
	protected function main()
	{
		if ($this->ssid)
		{
			$this->shiftInfos = Warehouse_Api::getStockShiftInfo($this->ssid);
            if($this->shiftInfos['status'] == Conf_Base::STATUS_NORMAL)
            {
                throw new Exception('调拔单发起申请后不能再修改！');
            }
		}
		
		$this->addFootJs(array('js/apps/stock.js', 'js/apps/show_product_common.js'));
		$this->addCss(array());
	}
	
	protected function outputBody()
	{
	    $cityId = $_COOKIE['city_id'];
        $widCity = Conf_Warehouse::$WAREHOUSE_CITY_MAPPING;
        $allWarehouses = Conf_Warehouse::$WAREHOUSES;

        $widInfo = array();
        foreach ($widCity as $wid => $city)
        {
            if ($cityId == $city)
            {
                $widInfo[$wid] = $allWarehouses[$wid];
            }

            if (Conf_Warehouse::isCoopWid($wid))
            {
                unset($widCity[$wid]);
            }
        }

		$this->smarty->assign('step_descs', Conf_Stock_Shift::$Step_Descs);
		$this->smarty->assign('warehouses', $widInfo);
		$this->smarty->assign('ssid', $this->ssid);
		$this->smarty->assign('shift_info', $this->shiftInfos);
		
		$this->smarty->display('warehouse/stock_shift.html');
	}
}

$app = new App('pri');
$app->run();