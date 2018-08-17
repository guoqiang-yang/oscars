<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $method;
	private $id;

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
		$this->method = Tool_Input::clean('r', 'method', TYPE_STR);
	}

	protected function checkPara()
    {
        if (empty($this->id))
        {
            throw new Exception('Invoice:empty id');
        }
        if (empty($this->method))
        {
            throw new Exception('Invoice:empty method');
        }
    }

	protected function main()
	{
		switch ($this->method)
        {
            case 'delete':
                Invoice_Api::delInputInvoice($this->id);
                break;
            case 'confirm':
                Invoice_Api::confirmInputInvoice($this->id);
                break;
            case 'finished':
                Invoice_Api::finishedInputInvoice($this->id, $this->_uid);
                break;
            default:
                throw new Exception('无效参数');
                break;
        }
	}

	protected function outputPage()
	{
		$result = array('pid' => $this->pid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pri');
$app->run();