<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $cid;
    private $start;
    private $num = 20;
    private $total;
    private $salesList = array();

    protected function checkAuth($permission = '')
    {
    }

    protected function getPara()
	{
		$this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
	}

	protected function checkPara()
    {
        if (empty($this->cid)) {
            throw new Exception('Invoice:empty cid');
        }
    }

	protected function main()
	{
		$salesList = Crm2_Api::getCustoemrTransHistory($this->cid, $this->start, $this->num);
        $this->total = $salesList['total'];
        $this->salesList = $salesList['data'];
	}

	protected function outputPage()
	{
	    $pageHtml = Str_Html::getJsPagehtml2($this->start, $this->num, $this->total, '_j_search_sale_history');
        $this->smarty->assign('total', $this->total);
        $this->smarty->assignRaw('pageHtml', $pageHtml);
	    $this->smarty->assign('sale_list', $this->salesList);
        $html = $this->smarty->fetch('crm2/block/get_customer_sales_changed_history.html');
		$result = array('html' =>$html );

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pri');
$app->run();