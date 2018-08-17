<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $ssid;
	private $sid;
	
	private $retSt;
	
	protected function getPara()
	{
		$this->ssid = Tool_Input::clean('r', 'ssid', TYPE_UINT);
		$this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
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
            throw new Exception('调拔单发起申请后不能再删除商品！');
        }
		$this->retSt = Warehouse_Api::delStockShiftProduct($this->ssid, $this->sid);
        //添加调拔单日志
        $info = array(
            'admin_id' => $this->_uid,
            'obj_id' => $this->ssid,
            'obj_type' => Conf_Admin_Log::OBJTYPE_SATOCK_SHIFT,
            'action_type' => 2,
            'wid' => $shiftInfos['src_wid'],
            'params' => json_encode(array('id' => $this->ssid,'json' => json_encode(array('type'=>'del', 'sid' =>$this->sid)))),
        );
        Admin_Common_Api::addAminLog($info);
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