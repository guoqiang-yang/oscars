<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $did;
	private $newType;

	protected function getPara()
	{
		$this->did = Tool_Input::clean('r', 'did', TYPE_UINT);
		$this->newType = Tool_Input::clean('r', 'new_type', TYPE_INT);
	}

	protected function checkPara()
	{
		if (empty($this->newType) || empty($this->did))
		{
			throw new Exception('common:params error');
		}
	}

	protected function main()
	{
		$ret = Logistics_Api::acceptOrder($this->did);
		if (!$ret)
		{
			throw new Exception('driver: queue type error');
		}
	}

	protected function outputPage()
	{
		$result = array('did' => $this->did);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pri');
$app->run();

