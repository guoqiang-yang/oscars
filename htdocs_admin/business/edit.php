<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $bid;
	private $business;
	private $salesList;
	private $isSale;

	protected function getPara()
	{
		$this->bid = Tool_Input::clean('r', 'bid', TYPE_UINT);
	}

	protected function main()
	{
		$this->business = Crm2_Api::getBusiness($this->bid);
		$this->salesList = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW, 0);

		$this->isSale = false;
		if (Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW) && !in_array($this->_uid, Conf_Admin::$SUPER_SALES))
		{
			$this->isSale = true;
		}

		$this->addFootJs(array('js/apps/business.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$this->smarty->assign('is_sale', $this->isSale);
		$this->smarty->assign('sales_list', $this->salesList);
		$this->smarty->assign('business', $this->business);
		$this->smarty->display('business/edit.html');
	}
}

$app = new App('pri');
$app->run();
