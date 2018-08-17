<?php


include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $oid;
	private $duplidateOid;

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
	}
    
    protected function checkAuth()
    {
        parent::checkAuth('/order/edit_order');
    }

    protected function main()
	{
		$this->duplidateOid = Order_Api::checkDuplicate($this->oid);
	}

	protected function outputPage()
	{
		$result = array('duplicate_oid' => $this->duplidateOid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pub');
$app->run();