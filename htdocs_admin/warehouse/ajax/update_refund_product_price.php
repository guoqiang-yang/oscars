<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $sid;
	private $srid;
	private $price;

	protected function getPara()
	{
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->srid = Tool_Input::clean('r', 'srid', TYPE_UINT);
        $this->price = Tool_Input::clean('r', 'price', TYPE_NUM) * 100;
    }

    protected function checkPara()
    {
        if (empty($this->srid) || empty($this->sid) || empty($this->price))
        {
            throw new Exception('参数错误！');
        }
    }

    protected function main()
	{
        Warehouse_Api::updateSupplierRefundProductPrice($this->srid, $this->sid, $this->price);
	}

	protected function outputPage()
	{
		$result = array('srid' => $this->srid, 'sid' => $this->sid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App();
$app->run();

