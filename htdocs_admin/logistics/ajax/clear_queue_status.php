<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $did;
	private $lineId;

	protected function getPara()
	{
		$this->did = Tool_Input::clean('r', 'did', TYPE_UINT);
		$this->lineId = Tool_Input::clean('r', 'line_id', TYPE_UINT);
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
		    'line_id' => 0,
            'step' => Conf_Driver::STEP_EMPTY,
		);
		Logistics_Api::updateDriverInQueue($this->did, $update);
		Logistics_Api::addActionLog($this->_uid, $this->did, Conf_Base::COOPWORKER_DRIVER, Conf_Logistics_Action_Log::ACTION_CLEAR_QUEUE_STATUS, 0, array(), $this->lineId);
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

