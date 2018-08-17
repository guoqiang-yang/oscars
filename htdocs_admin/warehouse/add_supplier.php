<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
	protected function main()
	{
		$this->addFootJs(array('js/apps/supplier.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('city_list', Conf_City::$CITY);
		$this->smarty->assign('supplier_types', Conf_Supplier::getTypes());
        $this->smarty->assign('managing_modes', Conf_Base::getManagingModes());
        $this->smarty->assign('can_edit_finance_data', 1);
        
		$this->smarty->display('warehouse/add_supplier.html');
	}
}

$app = new App('pri');
$app->run();
