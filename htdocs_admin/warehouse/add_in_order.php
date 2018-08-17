<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $step;  //第1步: 保存基本信息； 第2步: 保存商品列表
	private $oid;
	private $sid;
	private $order;
	private $supplier;
	private $suppliers;
    private $warhouseList;

	protected function getPara()
	{
		$this->step = Tool_Input::clean('r', 'step', TYPE_UINT);
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
		$this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
	}

	protected function checkPara()
	{
		$this->step = empty($this->step) ? Conf_In_Order::ORDER_STEP_NEW : $this->step;

		// 订单id
		if (Conf_In_Order::ORDER_STEP_SURE == $this->step)
		{
			if (empty($this->oid))
			{
				throw new Exception('order:empty order id');
			}
		}
	}

	protected function main()
	{
		if ($this->sid)
		{
			$this->supplier = Warehouse_Api::getSupplier($this->sid);
		}

		if (Conf_In_Order::ORDER_STEP_SURE == $this->step)
		{
			$this->order = Warehouse_Api::getOrderInfo($this->oid);
		}
        
		//供应商列表
		$order = 'order by sid desc';
		$res = Warehouse_Api::getSupplierList(array(), $order, 0, 1000);
		$this->suppliers = $res['list'];

        $cityInfo = City_Api::getCity();
        $cityId = $cityInfo['city_id'];
        $wids = Conf_Warehouse::$WAREHOUSE_CITY[$cityId];
        $this->warhouseList = array();
        foreach ($wids as $wid)
        {
            $this->warhouseList[$wid] = Conf_Warehouse::$WAREHOUSES[$wid];
        }

		$this->addFootJs(array('js/apps/warehouse.js', 'js/apps/in_order.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
        $this->smarty->assign('warehouse_list', $this->warhouseList);

		if (Conf_In_Order::ORDER_STEP_NEW == $this->step)
		{
			$this->smarty->assign('in_order_types', Conf_In_Order::$IN_ORDER_TYPES);
			$this->smarty->assign('supplier', $this->supplier);
			$this->smarty->assign('suppliers', $this->suppliers);
			$html = 'warehouse/add_order_step1.html';
		}
		elseif (Conf_In_Order::ORDER_STEP_SURE == $this->step)
		{
			// 添加商品的对话框
			$this->smarty->assign('order', $this->order);
            $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
            $this->smarty->assign('cate2_list_all', Conf_Sku::$CATE2);

            $html = 'warehouse/add_order_step2.html';
		}
        
		$this->smarty->display($html);
	}
}

$app = new App('pri');
$app->run();
