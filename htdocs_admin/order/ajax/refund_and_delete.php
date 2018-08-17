<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $oid;

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
	}

	protected function main()
	{
		Order_Api::refundAndDelete($this->oid, $this->_user);

        //订单操作日志
        Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_REFUND_AND_DELETE);
	}

	protected function outputPage()
	{
		$result = array('oid' => $this->oid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pri');
$app->run();

