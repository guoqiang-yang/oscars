<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
	private $id;
    private $info;
    protected $headTmpl = 'head/head_none.html';
    protected $tailTmpl = 'tail/tail_none.html';

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
    }

	protected function checkPara()
    {
        if (empty($this->id))
        {
            throw new Exception('common: param error');
        }
    }

    protected function main()
	{
	    if($this->id){
            $this->info = Invoice_Api::getOutputInvoiceInfo($this->id);
        }
        if(empty($this->info) || $this->info['status'] == Conf_Base::STATUS_DELETED)
        {
            throw new Exception('Empty Input Invoice!');
        }
	}

	protected function outputBody()
	{
        $this->smarty->assign('info', $this->info);
        $this->smarty->assign('city_list', Conf_City::$CITY);
        $this->smarty->assign('invoice_types', Conf_Invoice::$INVOICE_TYPES);
        $this->smarty->assign('order_num', count($this->info['bill_orders'])+2);
        $this->smarty->assign('product_num', count($this->info['products'])+2);
        $this->smarty->display('finance/output_invoice_print.html');
	}
}

$app = new App('pri');
$app->run();
