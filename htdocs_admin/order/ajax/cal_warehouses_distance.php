<?php


include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $communityId;
	private $data;

	protected function getPara()
	{
		$this->communityId = Tool_Input::clean('r', 'community_id', TYPE_UINT);
	}

    protected function checkAuth()
    {
        parent::checkAuth('/order/edit_order');
    }
    
	protected function main()
	{
		if (empty($this->communityId))
		{
			throw new Exception("请选择小区！");
		}
		$this->data = Order_Community_Api::calCommunityWarehousesDis($this->communityId);
	}

	protected function outputPage()
	{
		$result = array('data' => $this->data);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pri');
$app->run();