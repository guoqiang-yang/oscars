<?php
include('../../global.php');

class App extends App_Admin_Page
{
	private $oid;
	private $wid;	//仓库id
	private $cate1;	//货区，商品的一级分类
	
	private $pickingPlist = array();	//拣货列表
	private $otherPlist = array();		//非拣货列表
	private $pickingInfos = array();
	
	
	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);		//将要显示的订单id
		$this->cate1 = Tool_Input::clean('r', 'cate1', TYPE_UINT);
		$this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
		
		$this->cate1 = !empty($this->cate1)? $this->cate1: 1;
		$this->wid = !empty($this->wid)? $this->wid: Conf_Warehouse::WID_3;
		
	}
	
	protected function main()
	{
		$this->pickingInfos = Warehouse_Api::getPickingOrderDetail($this->oid, $this->wid);
		
		$this->otherPlist = $this->pickingInfos['productInfos'];
		if (!empty($this->otherPlist))
		{
			if (array_key_exists($this->cate1, $this->otherPlist)){
				$this->pickingPlist = $this->otherPlist[$this->cate1];
				unset($this->otherPlist[$this->cate1]);
			}
		}
		
	}
	
	protected function outputBody()
	{
		$this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
		$this->smarty->assign('order_status_list', Conf_Order::$ORDER_STEPS);
		$this->smarty->assign('cate1', $this->cate1);
		$this->smarty->assign('picking_plist', $this->pickingPlist);
		$this->smarty->assign('other_plist', $this->otherPlist);
		$this->smarty->assign('orderInfo', $this->pickingInfos['orderInfo']);
		$this->smarty->assign('currOid', $this->pickingInfos['curOid']);
		$this->smarty->assign('preOid', $this->pickingInfos['preOid']);
		$this->smarty->assign('sufOid', $this->pickingInfos['sufOid']);
	//print_r($this);exit;
		$this->smarty->display('warehouse/order_picking.html');
		
	}
}

$app = new App('pri');
$app->run();
