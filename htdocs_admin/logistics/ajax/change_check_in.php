<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $did;
	private $newType;
	private $wid;

	protected function getPara()
	{
		$this->did = Tool_Input::clean('r', 'did', TYPE_UINT);
		$this->newType = Tool_Input::clean('r', 'new_type', TYPE_INT);
		$this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
	}

	protected function checkPara()
	{
		if (empty($this->newType) || empty($this->did))
		{
			throw new Exception('common:params error');
		}

		if ($this->newType == 1 && empty($this->wid))
		{
			throw new Exception('common:params error');
		}
	}

	protected function main()
	{
		if ($this->newType == -1)
		{
			Logistics_Api::clearDriverQueue($this->did);
		}
		else
		{
			Logistics_Api::checkIn($this->did, $this->wid);
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

