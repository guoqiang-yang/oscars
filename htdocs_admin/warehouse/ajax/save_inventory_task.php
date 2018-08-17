<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $tid;
	private $suid;

	protected function getPara()
	{
		$this->tid = Tool_Input::clean('r', 'tid', TYPE_UINT);
		$this->suid = Tool_Input::clean('r', 'suid', TYPE_UINT);
	}

	protected function main()
	{
        $update = array(
            'alloc_suid' => $this->suid,
            'step' => Conf_Stock::STOCKTAKING_TASK_STEP_ALLOCATED,
        );
        $where = array(
            'tid' => $this->tid,
            'alloc_suid' => 0,
        );
        Warehouse_Api::updateInventoryTask($where, $update);
	}

	protected function outputPage()
	{
		$result = array('tid' => $this->tid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App('pri');
$app->run();

