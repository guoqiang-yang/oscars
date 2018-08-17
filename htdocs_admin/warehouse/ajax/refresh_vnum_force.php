<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $ssid;
	private $sid;

	protected function getPara()
	{
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->ssid = Tool_Input::clean('r', 'ssid', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->ssid) || empty($this->sid))
        {
            throw new Exception('参数错误！');
        }
    }

    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/ajax/refresh_vnum_force');
    }

    protected function main()
	{
		$wso = new Warehouse_Stock_Occupied();
		$this->ret = $wso->forceRefreshOccupied($this->ssid, Warehouse_Stock_Occupied::OBJTYPE_STOCK_SHIFT, $this->sid);
	}

	protected function outputPage()
	{
		$result = array('sid' => $this->sid, 'errmsg' => $this->ret['errmsg']);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App();
$app->run();

