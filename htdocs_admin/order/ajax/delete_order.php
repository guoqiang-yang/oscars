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
        $orderInfo = Order_Api::getOrderInfo($this->oid);
        if ($orderInfo['status'] >= Conf_Order::ORDER_STEP_SURE)
        {
            throw new Exception('common:permission denied');
        }

		Order_Api::deleteOrder($this->oid);

        //订单操作日志
        Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_DELETE_ORDER);
	}

	protected function outputPage()
	{
		if (strstr( $_SERVER["HTTP_REFERER"], 'order_list.php'))
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

