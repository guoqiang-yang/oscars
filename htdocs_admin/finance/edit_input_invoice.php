<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
	private $id;
    private $sid;
    private $info;
    private $isBuyer;
    private $isFinance;
    private $isAdmin;

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->sid = Tool_Input::clean('r', 'supplier_id', TYPE_UINT);
    }

	protected function checkPara()
    {
        if (empty($this->id) && empty($this->sid))
        {
            throw new Exception('common: param error');
        }
    }

    protected function main()
	{
        $this->isBuyer = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_BUYER_NEW);
        $this->isFinance = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_FINANCE_NEW);
        $this->isAdmin = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_ADMIN_NEW);
	    if($this->id){
            $this->info = Invoice_Api::getInputInvoiceInfo($this->id);
            if(empty($this->info))
            {
                header('Location: /finance/input_invoice_list.php');
                exit;
            }
        }
        if(!empty($this->info))
        {
            $this->sid = $this->info['supplier_id'];
            if($this->info['step'] == Conf_Invoice::INVOICE_STEP_FINISHED)
            {
                header('Location: /finance/show_input_invoice.php?id='.$this->id);
                exit;
            }
            if($this->info['status'] == Conf_Base::STATUS_DELETED)
            {
                header('Location: /finance/input_invoice_list.php');
                exit;
            }
            if($this->info['step'] == Conf_Invoice::INVOICE_STEP_HANDLING && $this->isBuyer)
            {
                header('Location: /finance/input_invoice_list.php');
                exit;
            }
        }
		$this->addFootJs(array('js/apps/input_invoice.js'));
	}

	protected function outputBody()
	{
	    $this->smarty->assign('sid', $this->sid);
        $this->smarty->assign('info', $this->info);
        $this->smarty->assign('city_list', Conf_City::$CITY);
        $this->smarty->assign('is_buyer', $this->isBuyer);
        $this->smarty->assign('is_finance', $this->isFinance);
        $this->smarty->assign('is_admin', $this->isAdmin);
        $this->smarty->assign('invoice_types', Conf_Invoice::$INVOICE_TYPES);

        $this->smarty->display('finance/edit_input_invoice.html');
	}
}

$app = new App('pri');
$app->run();
