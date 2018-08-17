<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $cid;
    private $start;
    private $num = 20;
    private $total;
    private $oid;
    private $invoice_type;
    private $bill_ids = array();

    protected function checkAuth($permission = '')
    {
        parent::checkAuth('/finance/edit_output_invoice');
    }

    protected function getPara()
	{
		$this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->invoice_type = Tool_Input::clean('r', 'invoice_type', TYPE_UINT);
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
	}

	protected function checkPara()
    {
        if (empty($this->cid)) {
            throw new Exception('Invoice:empty cid');
        }
        if (empty($this->invoice_type)) {
            throw new Exception('Invoice:empty invoice_type');
        }
    }

	protected function main()
	{
		$this->bill_ids = Invoice_Api::getUnOutputInvoiceBillsByCid($this->cid, $this->invoice_type, $this->oid, $this->total, $this->start, $this->num);
	}

	protected function outputPage()
	{
	    $pageHtml = Str_Html::getJsPagehtml2($this->start, $this->num, $this->total, '_j_search_invoice_output');
        $this->smarty->assign('total', $this->total);
        $this->smarty->assignRaw('pageHtml', $pageHtml);
	    $this->smarty->assign('bill_ids', $this->bill_ids);
        $this->smarty->assign('oid', $this->oid > 0 ? $this->oid : '');
        $html = $this->smarty->fetch('finance/get_output_bills.html');
		$result = array('html' =>$html );

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pri');
$app->run();