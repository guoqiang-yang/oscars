<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $sid;
    private $bill_ids = array();

    protected function checkAuth($permission = '')
    {
        parent::checkAuth('/finance/edit_input_invoice');
    }

    protected function getPara()
	{
		$this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
	}

	protected function checkPara()
    {
        if (empty($this->sid)) {
            throw new Exception('Invoice:empty sid');
        }
    }

	protected function main()
	{
		$this->bill_ids = Invoice_Api::getUnInputInvoiceBillsBySupplierId($this->sid);
	}

	protected function outputPage()
	{
	    $this->smarty->assign('bill_ids', $this->bill_ids);
        $html = $this->smarty->fetch('finance/get_input_bills.html');
		$result = array('html' =>$html );

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pri');
$app->run();