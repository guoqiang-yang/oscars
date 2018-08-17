<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $cid;

	protected function getPara()
	{
		$this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
	}

	protected function checkPara()
	{
		if (empty($this->cid))
		{
			throw new Exception('cid empty');
		}
	}

	protected function checkAuth()
	{
		parent::checkAuth();

		if (!in_array($this->_uid, array(1036, 1067, 1078)))
		{
			throw new Exception('common:permission denied');
		}
	}

	protected function main()
	{
		Coupon_Api::sendPackageCoupon($this->cid, '', '2016-08-16 00:00:00');
	}

	protected function outputBody()
	{
		$result = array('cid' => $this->cid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App();
$app->run();