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
        $id = isset($_REQUEST['id'])? $_REQUEST['id']: '';
        if($id>0){
            $info = Invoice_Api::getOutputInvoiceInfo($id);
            if($info['step'] > Conf_Invoice::INVOICE_OUTPUT_STEP_SALES_AUDIT)
            {
                parent::checkAuth('/finance/edit_input_invoice');
            }else{
                parent::checkAuth('/crm2/edit_invoice');
            }
        }else{
            parent::checkAuth('/crm2/edit_invoice');
        }
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
        $isFinance = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_FINANCE_NEW);
        $info = Invoice_Api::getInputInvoiceInfo($this->id);
        if(!$isFinance && $info['step'] > Conf_Invoice::INVOICE_OUTPUT_STEP_SALES_AUDIT)
        {
            throw new Exception('销售助理已审核通过发票，不允许销售再修改');
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
                Invoice_Api::addProduct2OutputInvoice($this->id, $this->productList);
                break;
            case 'delete':
                Invoice_Api::delProduct2OutputInvoice($this->id, $this->pid);
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

