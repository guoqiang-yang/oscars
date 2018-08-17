<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $did;

	protected function getPara()
	{
		$this->did = Tool_Input::clean('r', 'did', TYPE_UINT);
	}

	protected function checkPara()
	{
		if (empty($this->did))
		{
			throw new Exception('common:params error');
		}
	}

	protected function main()
	{
		$update = array(
			'refuse_num' => 0,
            'step' => Conf_Driver::STEP_EMPTY,
		);
		Logistics_Api::updateDriverInQueue($this->did, $update);
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

