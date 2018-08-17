<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $oid;
	private $orderInfo;

    protected function checkAuth($permission = '')
    {
        parent::checkAuth('/activity/cpoint_product_exchange_record');
    }

    protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
		$this->orderInfo = array(
		    'express' => Tool_Input::clean('r', 'express', TYPE_UINT),
            'tracking_num' => Tool_Input::clean('r', 'tracking_num', TYPE_STR),
            'freight' => round(100 * Tool_Input::clean('r', 'freight', TYPE_STR)),
        );
	}

	protected function checkPara()
    {
        if(empty($this->oid) || empty($this->orderInfo['express']) || empty($this->orderInfo['tracking_num']))
        {
            throw new Exception('common:params error');
        }
    }

    protected function main()
	{
        $op = new Cpoint_Order();
        $op->updateOrder($this->oid, $this->orderInfo);
	}

	protected function outputPage()
	{
		$result = array('oid' => $this->oid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App('pri');
$app->run();

