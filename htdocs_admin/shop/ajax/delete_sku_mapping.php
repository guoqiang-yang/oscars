<?php

include_once ('../../../global.php');

//删除sku映射关系，没用
exit;

class App extends App_Admin_Ajax
{
	private $id;

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
	}

	protected function main()
	{
		Merchant_Api::delete($this->id);
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