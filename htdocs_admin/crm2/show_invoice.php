<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
	private $id;
    private $info;

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
        if(empty($this->info))
        {
            throw new Exception('Empty Input Invoice!');
        }
	}

	protected function outputBody()
	{
        $this->smarty->assign('info', $this->info);
        $this->smarty->assign('city_list', Conf_City::$CITY);
        $this->smarty->assign('invoice_types', Conf_Invoice::$INVOICE_TYPES);
        $this->smarty->display('finance/show_output_invoice.html');
	}
}

$app = new App('pri');
$app->run();
