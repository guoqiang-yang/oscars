<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $time;

	protected function main()
	{
		$this->time = Order_Api::getLatestSureTime($this->_user['wid']);
	}

	protected function outputPage()
	{
		$result = array('time' => $this->time);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pub');
$app->run();

