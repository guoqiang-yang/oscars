<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $method;
	private $pid;

	protected function getPara()
	{
		$this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
		$this->method = Tool_Input::clean('r', 'method', TYPE_STR);
	}

	protected function checkPara()
    {
        if (empty($this->pid))
        {
            throw new Exception('InvoiceProduct:empty pid');
        }
        if (empty($this->method))
        {
            throw new Exception('InvoiceProduct:empty method');
        }
    }

	protected function main()
	{
		switch ($this->method)
        {
            case 'delete':
                Invoice_Api::updateProduct($this->pid, array('status' => Conf_Base::STATUS_DELETED));
                break;
            case 'restore':
                Invoice_Api::updateProduct($this->pid, array('status' => Conf_Base::STATUS_NORMAL));
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