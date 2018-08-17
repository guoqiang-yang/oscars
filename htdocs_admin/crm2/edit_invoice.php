<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
	private $id;
    private $cid;
    private $info;
    private $isSale;
    private $isFinance;
    private $isAdmin;

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
    }

	protected function checkPara()
    {
        if (empty($this->id) && empty($this->cid))
        {
            throw new Exception('common: param error');
        }
    }

    protected function main()
	{
        $this->isSale = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW);
        $this->isFinance = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_FINANCE_NEW);
        $this->isAdmin = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_ADMIN_NEW);
	    if($this->id){
            $this->info = Invoice_Api::getOutputInvoiceInfo($this->id);
        }
        if($this->info)
        {
            $this->cid = $this->info['cid'];
            if($this->info['step'] >= Conf_Invoice::INVOICE_OUTPUT_STEP_SALES_AUDIT)
            {
                header('Location: /crm2/show_invoice.php?id='.$this->id);
                exit;
            }
            if($this->info['status'] == Conf_Base::STATUS_DELETED)
            {
                header('Location: /crm2/invoice_list.php');
                exit;
            }
        }
		$this->addFootJs(array('js/apps/output_invoice.js'));
	}

	protected function outputBody()
	{
	    $this->smarty->assign('cid', $this->cid);
        $this->smarty->assign('info', $this->info);
        $this->smarty->assign('city_list', Conf_City::$CITY);
        $this->smarty->assign('is_sale', $this->isSale);
        $this->smarty->assign('is_finance', $this->isFinance);
        $this->smarty->assign('is_admin', $this->isAdmin);
        $this->smarty->assign('invoice_types', Conf_Invoice::$INVOICE_TYPES);

        $this->smarty->display('finance/edit_output_invoice.html');
	}
}

$app = new App('pri');
$app->run();
