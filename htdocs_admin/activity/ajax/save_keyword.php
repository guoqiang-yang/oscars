<?php

include_once('../../../global.php');

//保存热搜词？现在没用
exit;

class App extends App_Admin_Ajax
{
	private $keyword;
	private $sortby;
	private $cityId;

	protected function getPara()
	{
		$this->keyword = Tool_Input::clean('r', 'keyword', TYPE_STR);
		$this->sortby = Tool_Input::clean('r', 'sortby', TYPE_UINT);
		$this->cityId = Tool_Input::clean('r', 'city_id', TYPE_STR);
	}

	protected function main()
	{
		$info = array(
			'keyword' => $this->keyword,
			'sortby' => $this->sortby,
			'city_id' => $this->cityId,
		);

		Search_Api::addSearchKeyword($info);
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

