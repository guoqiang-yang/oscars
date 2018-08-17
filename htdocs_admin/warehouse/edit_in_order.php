
<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $oid;
	private $order;
	private $stockInLists;
    private $IsPaidstockIn = false;     // 是否支付入库单
    private $hadStockIn = false;        // 是否已经入库（部分入库也算已入库）
	private $cate1;
	private $cate2;
	private $searchConf;
	private $suppliers;
    private $warhouseList;
    private $existCommonInorder = false;    //是否存在普采

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
	}

	protected function checkPara()
	{
		// 商品搜索列表初始化
		$this->cate1 = Tool_Input::clean('c', '_last_cate1', TYPE_UINT);
		if (empty($this->cate1))
		{
			$this->cate1 = 1;
			$cate2List = Conf_Sku::$CATE2[$this->cate1];
			$this->cate2 = array_shift(array_keys($cate2List));
		}
		else
		{
			$this->cate2 = Tool_Input::clean('c', '_last_cate2', TYPE_UINT);
		}
	}

	protected function main()
	{
        // 取采购单详情
		$this->order = Warehouse_Api::getOrderInfo($this->oid);
		if (!empty($this->order['info']['buyer_uid']))
		{
			$this->buyerInfo = Admin_Api::getStaff($this->order['info']['buyer_uid']);
		}

        ksort($this->order['sources']);

        // 判断是否存在普采商品
        if (array_key_exists(Conf_In_Order::SRC_COMMON, $this->order['sources']))
        {
            $this->existCommonInorder = true;
        }
        
        // 取采购单对应的入库单
		$res = Warehouse_Api::getStockInLists(array('oid' => $this->oid));
		$this->stockInLists = $res['list'];
        
        foreach($this->stockInLists as $_stockIn)
        {
            $this->IsPaidstockIn = $this->IsPaidstockIn||$_stockIn['paid'];
            $this->hadStockIn = true;
        }
        
		//供应商列表
		$order = 'order by sid desc';
		$sres = Warehouse_Api::getSupplierList(array(), $order, 0, 1000);
		$this->suppliers = $sres['list'];
        
        $cityInfo = City_Api::getCity();
        $cityId = $cityInfo['city_id'];
        $wids = Conf_Warehouse::$WAREHOUSE_CITY[$cityId];
        $this->warhouseList = array();
        foreach ($wids as $wid)
        {
            $this->warhouseList[$wid] = Conf_Warehouse::$WAREHOUSES[$wid];
        }
        //获取账期日期
        if (!isset($this->order['info']['payment_days_date']) || $this->order['info']['payment_days_date'] == '0000-00-00 00:00:00'){
            //===========xujianping - begin===========
            //计算账期之后的时间
            $paymentDays = isset($this->order['supplier']['payment_days']) ? $this->order['supplier']['payment_days'] : 0;
            $paymentDaysDate = date("Y-m-d H:i:s",strtotime('+'.$paymentDays.' day',strtotime($this->order['info']['delivery_date'])));
            //计算账期时间
            $this->order['info']['payment_days_date'] = $paymentDaysDate;
            //===========xujianping - end===========
        }

		$this->addFootJs(array('js/apps/warehouse.js', 'js/apps/in_order.js', 'js/apps/show_product_common.js'));
	}

	protected function outputBody()
	{
		//$opButtonHtml = Warehouse_Step::getOrderButtonHtml($this->_user, $this->order['info']);
        
		// 添加商品的对话框
        $this->smarty->assign('oid', $this->oid);
		$this->smarty->assign('order', $this->order);
        $this->smarty->assign('exist_common_inorder', $this->existCommonInorder);
		$this->smarty->assign('stock_in_lists', $this->stockInLists);
		$this->smarty->assign('suppliers', $this->suppliers);
		$this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
		$this->smarty->assign('cate2_list_all', Conf_Sku::$CATE2);
		$this->smarty->assign('search_conf', $this->searchConf);
		$this->smarty->assign('order_steps', Conf_In_Order::$ORDER_STEPS);
		$this->smarty->assign('buyer', $this->buyerInfo);
		//$this->smarty->assign('op_button_html', $opButtonHtml);
		$this->smarty->assign('warehouses', Conf_Warehouse::$WAREHOUSES);
        $this->smarty->assign('paid_stockin', $this->IsPaidstockIn);
        $this->smarty->assign('had_stockin', $this->hadStockIn);
        $this->smarty->assign('warehouse_list', $this->warhouseList);
        $this->smarty->assign('paid_sources', Conf_Finance::$MONEY_OUT_PAID_TYPES);
        $this->smarty->assign('in_order_types', Conf_In_Order::$IN_ORDER_TYPES);

		$this->smarty->display('warehouse/edit_in_order.html');
	}
}

$app = new App('pri');
$app->run();
