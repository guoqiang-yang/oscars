<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $oid;
	private $service;
	private $floorNum;

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
		$this->service = Tool_Input::clean('r', 'service', TYPE_UINT);
		$this->floorNum = Tool_Input::clean('r', 'floor_num', TYPE_UINT);
	}


	protected function main()
	{
		if ($this->service == 0)
		{
			$info = array('service' => $this->service, 'floor_num' => 0);
			$orderChange = array('上楼方式' => '不上楼');
		}
		else if ($this->service == 1)
		{
			$info = array('service' => $this->service, 'floor_num' => 0);
			$orderChange = array('上楼方式' => '电梯上楼');
		}
		else
		{
			$info = array('service' => $this->service, 'floor_num' => $this->floorNum);
			$orderChange = array('上楼方式' => '楼梯上楼', '楼层' => $this->floorNum);
		}

		Order_Api::updateOrderInfo($this->oid, $info);

		//订单操作日志
		Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_CHANGE_INFO, $orderChange);
	}

	protected function outputPage()
	{
		if (strstr($_SERVER["HTTP_REFERER"], 'order_list.php'))
		{
			$url = $_SERVER["HTTP_REFERER"];
		}
		else
		{
			$url = '/order/order_list.php';
		}
		$result = array('oid' => $this->oid, 'url' => $url);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pri');
$app->run();

