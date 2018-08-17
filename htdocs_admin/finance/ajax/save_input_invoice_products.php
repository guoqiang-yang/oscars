<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $id;
    private $pid;
    private $method;
	private $productList;

    protected function checkAuth($permission = '')
    {
        parent::checkAuth('/finance/edit_input_invoice');
    }

    protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
        $this->method = Tool_Input::clean('r', 'method', TYPE_STR);
		$this->productList = Tool_Input::clean('r', 'products', TYPE_ARRAY);
	}

	protected function checkPara()
    {
        if(empty($this->method) || $this->method == '')
        {
            throw new Exception('common: param error!');
        }
        if (empty($this->id))
        {
            throw new Exception('common: param error!');
        }
        $isBuyer = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_BUYER_NEW);
        $info = Invoice_Api::getInputInvoiceInfo($this->id);
        if($isBuyer && $info['step'] > Conf_Invoice::INVOICE_STEP_NEW)
        {
            throw new Exception('财务已经确认发票，不允许采购再修改');
        }
        switch ($this->method)
        {
            case 'add':
                if (empty($this->productList))
                {
                    throw new Exception('common: param error!');
                }
                break;
            case 'delete':
                if (empty($this->pid))
                {
                    throw new Exception('common: param error!');
                }
                break;
            default:
                throw new Exception('common: param error!');
        }

	}

	protected function main()
	{
        switch ($this->method)
        {
            case 'add':
                Invoice_Api::addProduct2InputInvoice($this->id, $this->productList);
                break;
            case 'delete':
                Invoice_Api::delProduct2InputInvoice($this->id, $this->pid);
                break;
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

