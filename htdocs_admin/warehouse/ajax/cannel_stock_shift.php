<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $ssid;
	
	private $retSt;
	
	protected function getPara()
	{
		$this->ssid = Tool_Input::clean('r', 'ssid', TYPE_UINT);
	}
	
	protected function checkAuth()
    {
        parent::checkAuth('/warehouse/stock_shift');
    }
	
	protected function main()		
	{
        $shiftInfos = Warehouse_Api::getStockShiftInfo($this->ssid);
        if($shiftInfos['status'] == Conf_Base::STATUS_NORMAL)
        {
            throw new Exception('调拔单发起申请后不能再取消！');
        }
		$this->retSt = Warehouse_Api::cannelStockShift($this->ssid);
	}
	
	protected function outputBody()
	{
		$result = array('st' => $this->retSt);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App('pri');
$app->run();