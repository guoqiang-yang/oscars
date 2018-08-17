<?php

include_once('../../../global.php');


//老的方法，已废弃
exit;

class App extends App_Admin_Ajax
{
	private $id;
	private $info;

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
		$this->info = array(
			'stime' => Tool_Input::clean('r', 'stime', TYPE_STR),
			'etime' => Tool_Input::clean('r', 'etime', TYPE_STR),
			'is_sand' => Tool_Input::clean('r', 'is_sand', TYPE_UINT),
			'is_vip' => Tool_Input::clean('r', 'is_vip', TYPE_UINT),
			'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
			'conf' => Tool_Input::clean('r', 'conf', TYPE_STR),
		);
	}

	protected function main()
	{
		$this->info['suid'] = $this->_uid;
		if (empty($this->id))
		{
			Activity_Api::addManjian($this->info);
		}
		else
		{
			Activity_Api::updateManjian($this->id, $this->info);
		}
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

