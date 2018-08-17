<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $id;
	private $oid;
	private $stockInDetail = array();
	private $order;

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
	}

	protected function main()
	{
		if ($this->id)
		{
			$this->stockInDetail = Warehouse_Api::getStockInDetail($this->id);
			$oid = $this->stockInDetail['info']['oid'];
		}
		else if($this->oid)
		{
			$oid = $this->oid;
		}
		if ($oid)
		{
			$this->order = Warehouse_Api::getOrderInfo($oid);
			if ($this->order['info']['in_order_type'] == Conf_In_Order::IN_ORDER_TYPE_GIFT)
            {
                foreach ($this->order['products'][1] as &$order)
                {
                    $order['price'] = 0;
                }
            }
		}
        
		$this->addFootJs(array('js/apps/stock.js'));
		$this->addCss(array());
	}
    
	protected function outputBody()
	{
		$this->smarty->assign('order', $this->order);
		$this->smarty->assign('stock_in', $this->stockInDetail);
		$this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
		$this->smarty->assign('cate2_list_all', Conf_Sku::$CATE2);
		$this->smarty->assign('payment_types', Conf_Stock::$PAYMENT_TYPES);
        $this->smarty->assign('paid_sources', Conf_Finance::$MONEY_OUT_PAID_TYPES);
        $this->smarty->assign('managing_modes', Conf_Base::getManagingModes());
        
        $this->smarty->assign('is_upgrade_wid', Conf_Warehouse::isUpgradeWarehouse($this->stockInDetail['info']['wid']));

		$this->smarty->display('warehouse/edit_stock_in.html');
	}
}

$app = new App('pri');
$app->run();
