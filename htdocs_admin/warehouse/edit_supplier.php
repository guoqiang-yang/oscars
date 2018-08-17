<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
	// cgi参数
	private $sid;

	// 中间结果
	private $supplier;
	private $execType;

	protected function getPara()
	{
		$this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
		$this->execType = Tool_Input::clean('r', 'exec_type', TYPE_STR);
	}

	protected function main()
	{
		$this->supplier = Warehouse_Api::getSupplier ($this->sid);

        if (!empty($this->supplier['public_bank']))
        {
            $publicBank = explode(',', $this->supplier['public_bank']);
            $this->supplier['public_bank'] = $publicBank;
        }

        $this->addFootJs(array('js/apps/supplier.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
	    $this->smarty->assign('exec_type', $this->execType);
		$this->smarty->assign('supplier', $this->supplier);
		$this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
		$this->smarty->assign('city_list', Conf_City::$CITY);
		$this->smarty->assign('supplier_city_list', explode(',', $this->supplier['city']));
		$this->smarty->assign('supplier_types', Conf_Supplier::getTypes());
        $this->smarty->assign('managing_modes', Conf_Base::getManagingModes());
        $this->smarty->assign('can_edit_finance_data', $this->_canEditFinanceData());
        
		$this->smarty->display('warehouse/edit_supplier.html');
	}
    
    private function _canEditFinanceData()
    {
        return $this->supplier['status']==Conf_Base::STATUS_UN_AUDIT||!empty($this->permissions['hc_supplier_finance_data'])? 1: 0;
    }
}

$app = new App('pri');
$app->run();
