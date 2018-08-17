<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $oid;
	private $orderInfo;
	private $stockList;
	private $buyerInfo = array();
    private $warhouseList;
    
    private $canStockin = true;
	
	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
	}
	
	protected function checkPara ()
	{
		if (empty($this->oid))
		{
			throw new Exception('Order ID is not Empty');
		}
	}

	protected function main ()
	{
		$this->orderInfo = Warehouse_Api::getOrderInfo($this->oid);
		$this->stockList = Warehouse_Api::getStockInLists(array('oid' => $this->oid));
        
		if (!empty($this->orderInfo['info']['buyer_uid']))
		{
			$this->buyerInfo = Admin_Api::getStaff($this->orderInfo['info']['buyer_uid'], Conf_Base::STATUS_ALL);
		}

        if ($this->orderInfo['info']['in_order_type']==Conf_In_Order::IN_ORDER_TYPE_ORDER
            && $this->orderInfo['info']['source']==Conf_In_Order::SRC_COMMON
            && $this->orderInfo['info']['payment_type']!=Conf_Stock::PAYMENT_MONEY_FIRST
            && !empty($this->stockList['list']))
        {
            $this->canStockin = false;
        }
        
        $this->addFootJs(array('js/apps/in_order.js'));
	}
	
	protected function outputBody()
	{
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('cate2_list_all', Conf_Sku::$CATE2);
		$this->smarty->assign('order', $this->orderInfo);
		$this->smarty->assign('stock_in_lists', $this->stockList['list']);
		$this->smarty->assign('buyer', $this->buyerInfo);
        $this->smarty->assign('warehouse_list', $this->warhouseList);
        $this->smarty->assign('paid_sources', Conf_Finance::$MONEY_OUT_PAID_TYPES);
        $this->smarty->assign('payment_types', Conf_Stock::$PAYMENT_TYPES);
        $this->smarty->assign('in_order_types', Conf_In_Order::$IN_ORDER_TYPES);
        $this->smarty->assign('managing_modes', Conf_Base::getManagingModes());
        
        $this->smarty->assign('can_stock_in', $this->canStockin);
        
        $this->smarty->assign('is_upgrade_warehouse', Conf_Warehouse::isUpgradeWarehouse($this->orderInfo['info']['wid']));
        
		$this->smarty->display('warehouse/detail_in_order.html');
	}
}

$app = new App('pri');
$app->run();
