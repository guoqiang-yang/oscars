<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $oid;
	private $sid;
    
    private $ret;

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
		$this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
	}

	protected function main()
	{
        $oorder = new Order_Order();
        $orderInfo = $oorder->get($this->oid);
        
        if ($orderInfo['step'] < Conf_Order::ORDER_STEP_SURE)
        {
            throw new Exception('订单未确认，不能再强制刷新！！');
        }
        if ($orderInfo['step'] >= Conf_Order::ORDER_STEP_PICKED)
        {
            throw new Exception('订单已经出库，不能再强制刷新！！');
        }
        
            //Order_Api::forceRefresh($this->sid, $this->oid);
            
            $wso = new Warehouse_Stock_Occupied();
            $this->ret = $wso->forceRefreshOccupied($this->oid, Warehouse_Stock_Occupied::OBJTYPE_ORDER, $this->sid);
            
            $param = array('sid' => $this->sid);
            Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_REFRESH_FORCE, $param);
	}

	protected function outputPage()
	{
		$result = array('oid' => $this->oid, 'errmsg'=>$this->ret['errmsg']);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pri');
$app->run();
