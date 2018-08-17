<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $id;

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
	}

	protected function checkPara()
	{
        if (empty($this->id))
        {
            throw new Exception('返点ID不能为空');
        }
	}

	protected function main()
	{
        Agent_Api::payAgentBillCashback($this->id, $this->_uid);
	}

	protected function outputPage()
	{
		$result = array('id' => $this->id);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App('pri');
$app->run();

