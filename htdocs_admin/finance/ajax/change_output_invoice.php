<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $method;
	private $id;

    protected function checkAuth($permission = '')
    {
        $chgSt = isset($_REQUEST['method'])? $_REQUEST['method']: '';

        switch($chgSt)
        {
            case 'delete':
                parent::checkAuth('/crm2/ajax/delete_output_invoice'); break;
            case 'rebut':
                parent::checkAuth('/crm2/ajax/rebut_output_invoice'); break;
            case 'sale_audit':
                parent::checkAuth('/crm2/ajax/audit_output_invoice'); break;
            case 'finance_confirm':
                parent::checkAuth('/finance/ajax/confirm_output_invoice'); break;
            case 'finished':
                parent::checkAuth('/finance/ajax/finished_output_invoice'); break;
            default:
                throw new Exception('common:permission denied');
        }
    }

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
                Invoice_Api::delOutputInvoice($this->id);
                break;
            case 'rebut':
                Invoice_Api::rebutOutputInvoice($this->id, $this->_uid);
                break;
            case 'sale_audit':
                Invoice_Api::auditOutputInvoiceBySale($this->id, $this->_uid);
                break;
            case 'finance_confirm':
                Invoice_Api::confirmOutputInvoiceByFinance($this->id, $this->_uid);
                break;
            case 'finished':
                Invoice_Api::finishedOutputInvoice($this->id, $this->_uid);
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