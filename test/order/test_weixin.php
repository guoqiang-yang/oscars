<?php
include_once ('../../global.php');

class App extends App_Cli
{

	protected function main()
	{
		$this->_testWeixinPay();
	}

	private function _testWeixinPay()
	{
		$oid = 17070;
		$orderInfo = Order_Api::getOrderInfo($oid);
		Order_Api::updateOrderByFinanceModify($oid, $orderInfo['freight'],
			$orderInfo['privilege'] + 20, $orderInfo['customer_carriage']);
		exit;

		$id = 28806;
		$amount = 150400;
		Finance_Api::modifyCustomerSinglePaidRecord($id, $amount, Conf_Base::PT_WEIXIN_ONLINE);
	}
}

$app = new App();
$app->run();
