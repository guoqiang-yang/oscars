<?php
include_once('../../../global.php');

class CApp extends App_Admin_Ajax
{
    protected $messageCount;
	protected function getPara()
	{
	}

	protected function checkPara()
	{
		if (!$this->_uid)
		{
            $result = array('count' => 0);
            $response = new Response_Ajax();
            $response->setContent($result);
            $response->send();
            exit;
		}
	}

	protected function main()
	{
	    $seven_time = date('Y-m-d H:i:s',strtotime('-7 days'));
        $this->messageCount = Admin_Message_Api::getTotal('receive_suid='.$this->_uid.' AND has_read=0 AND ctime>="'.$seven_time.'"');
	}

	protected function outputPage()
	{
		$result = array('count' => $this->messageCount);
		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new CApp("");
$app->run();
