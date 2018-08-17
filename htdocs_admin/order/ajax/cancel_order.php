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
        throw new Exception('操作异常：请先回滚订单，然后删除');
        
		$orderInfo = Order_Api::getOrderInfo($this->oid);
		if ($orderInfo['status'] != Conf_Base::STATUS_NORMAL || $orderInfo['step'] < Conf_Order::ORDER_STEP_SURE || $orderInfo['step'] >= Conf_Order::ORDER_STEP_PICKED)
		{
			throw new Exception('common:permission denied');
		}

		if ($orderInfo['paid'] == Conf_Order::UN_PAID)
		{
			Order_Api::deleteOrder($this->oid);
		}
		else
		{
			Order_Api::refundAndDelete($this->oid, $this->_user);
		}

		Logistics_Coopworker_Api::cancalOrderCoopworker($this->oid);

		//订单操作日志
		Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_ADMIN_CANCEL_ORDER);
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

