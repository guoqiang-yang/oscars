<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $cid;

	protected function getPara()
	{
		$this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
	}

	protected function checkPara()
	{
	}

	protected function main()
	{
		Logistics_Api::deleteCarrier($this->cid);
	}

	protected function outputPage()
	{
		$result = array('cid' => $this->cid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App('pri');
$app->run();

