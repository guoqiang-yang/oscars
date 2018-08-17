<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
	private $start;
	private $searchConf;
	private $total;
	private $orders;
	private $buyerList;
	private $num = 40;
	private $sum;
	private $staffList;

	protected function getPara()
	{
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
		$this->searchConf = array(
			'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
			'sid' => Tool_Input::clean('r', 'sid', TYPE_UINT),
			'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
			'step' => Tool_Input::clean('r', 'step', TYPE_UINT),
			'buyer_uid' => Tool_Input::clean('r', 'buyer_uid', TYPE_UINT),
			'payment_type' => Tool_Input::clean('r', 'payment_type', TYPE_UINT),
            'in_order_type' => Tool_Input::clean('r', 'in_order_type', TYPE_UINT),
            'is_timeout' => Tool_Input::clean('r', 'is_timeout', TYPE_UINT),
            'sku_id' => Tool_Input::clean('r', 'sku_id', TYPE_UINT),
            'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
            'managing_mode' => Tool_Input::clean('r', 'managing_mode', TYPE_UINT),
		);
	}

	protected function checkPara()
    {
        if (!isset($_REQUEST['is_timeout']))
        {
            $this->searchConf['is_timeout'] = Conf_Base::STATUS_ALL;
        }
        if (empty($_REQUEST['status']) && $_REQUEST['status'] !== '0' && empty($_REQUEST['step']))
        {
            $this->searchConf['status'] = Conf_Base::STATUS_ALL;
        }
        else
        {
            $this->searchConf['status'] = Tool_Input::clean('r', 'status', TYPE_STR);
        }
        if (empty($_REQUEST['step']))
        {
            $this->searchConf['step'] = Conf_Base::STATUS_ALL;
        }

        $curCity = City_Api::getCity();
        if (empty($this->searchConf['wid']))
        {
            if (empty($this->_user['city_wid_map'][$curCity['city_id']]))
            {
                $this->searchConf['wid'] = -1;
            }
            else
            {
                $this->searchConf['wid'] = $this->_user['city_wid_map'][$curCity['city_id']];
            }
        }
    }

    protected function main()
	{
		$order = '';
		$res = Warehouse_Api::getOrderList($this->searchConf, $order, $this->start, $this->num);
		$this->orders = $res['list'];
		$this->total = $res['total'];
		$this->sum = $res['sum'];
        $this->buyerList = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_BUYER_NEW);

		// 查看【待收货】采购单是否有部分入库
		$oids = Tool_Array::getFields($this->orders, 'oid');
		$hadStockInOids = array();
		if (!empty($oids))
		{
			$wsi = new Warehouse_Stock_In();
			$stockInList = $wsi->getListOfOrder($oids);
			$hadStockInOids = Tool_Array::getFields($stockInList, 'oid');
		}

		foreach ($this->orders as &$oinfo)
		{
			$oinfo['hadStocked'] = in_array($oinfo['oid'], $hadStockInOids) ? TRUE : FALSE;
            //获取账期日期
            if (!isset($oinfo['payment_days_date']) || $oinfo['payment_days_date'] == '0000-00-00 00:00:00'){
                //===========xujianping - begin===========
                //计算账期之后的时间
                $paymentDays = isset($oinfo['_supplier']['payment_days']) ? $oinfo['_supplier']['payment_days'] : 0;
                $paymentDaysDate = date("Y-m-d H:i:s",strtotime('+'.$paymentDays.' day',strtotime($oinfo['delivery_date'])));
                //计算账期时间
                $oinfo['payment_days_date'] = $paymentDaysDate;
                //===========xujianping - end===========
            }
		}

		$staffList = Admin_Api::getStaffList();
		$this->staffList = Tool_Array::list2Map($staffList['list'], 'suid', 'name');

		$this->addFootJs(array('js/apps/warehouse.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
        if (is_array($this->searchConf['wid']))
        {
            unset($this->searchConf['wid']);
        }
		$app = '/warehouse/in_order_list.php?' . http_build_query($this->searchConf);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

		$this->smarty->assign('buyerList', $this->buyerList);
		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('searchConf', $this->searchConf);
		$this->smarty->assign('order_steps', Conf_In_Order::getOrderStepNames());
		$this->smarty->assign('status_list', Conf_Base::getInOrderStatusList());
		$this->smarty->assign('orders', $this->orders);
		$this->smarty->assign('total', $this->total);
		$this->smarty->assign('payment_types', Conf_Stock::$PAYMENT_TYPES);
		$this->smarty->assign('sum', $this->sum);
        $this->smarty->assign('in_order_types', Conf_In_Order::$IN_ORDER_TYPES);
        $this->smarty->assign('staff_list', $this->staffList);
        $this->smarty->assign('managing_modes', Conf_Base::getManagingModes());
        
		$this->smarty->display('warehouse/in_order_list.html');
	}
}

$app = new App('pri');
$app->run();

