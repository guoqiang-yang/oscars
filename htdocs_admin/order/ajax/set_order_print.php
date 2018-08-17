<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $oid;
	private $print;

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
		$this->print = Tool_Input::clean('r', 'print', TYPE_UINT);
		if (!$this->print)
		{
			$this->print = -1;
		}
	}

	protected function checkPara()
	{
		if ($this->print!= 1 && $this->print!=-1)
		{
			throw new Exception('common:params error');
		}
	}

	protected function main()
	{
		$info = array('has_print' => $this->print);
		Order_Api::updateOrderInfo($this->oid, $info);
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

