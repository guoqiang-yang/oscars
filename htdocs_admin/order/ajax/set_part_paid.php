<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $oid;

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
	}

//	protected function checkAuth()
//	{
//		parent::checkAuth();
//		if (!Admin_Role_Api::hasAuthority('w_edit_order', $this->_uid, $this->_user))
//		{
//			throw new Exception('common:permission denied');
//		}
//	}

	protected function main()
	{
        $orderInfo = Order_Api::getOrderInfo($this->oid);
        if ($orderInfo['paid'] != Conf_Order::HAD_PAID || $orderInfo['user_need_to_pay'] <= 0)
        {
            throw new Exception('order:invalid status');
        }

		Order_Api::updateOrderInfo($this->oid, array('paid' => Conf_Order::PART_PAID));

        //订单操作日志
        Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_SET_PART_PAID);
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

