<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
	// cgi参数
	private $start;
	private $mobile;
	private $searchConf;

	// 中间结果
	private $orders;
    private $coopworkerOfOrder;
	private $num = 20;
	private $total;
	private $business;

	protected function getPara()
	{
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
		$this->mobile = Tool_Input::clean('r', 'mobile', TYPE_STR);
		$this->searchConf = array(
			'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
			'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
			'driver_phone' => Tool_Input::clean('r', 'driver_phone', TYPE_STR),
			'from_date' => Tool_Input::clean('r', 'from_date', TYPE_STR),
			'end_date' => Tool_Input::clean('r', 'end_date', TYPE_STR),
			'delivery_date' => Tool_Input::clean('r', 'delivery_date', TYPE_STR),
			'construction' => Tool_Input::clean('r', 'construction', TYPE_STR),
			'wid' => $this->getWarehouseId(),
		);

		$this->searchConf['status'] = Conf_Base::STATUS_NORMAL;
		$this->searchConf['step'] = Conf_Order::ORDER_STEP_BOUGHT;
	}

	protected function main()
	{
		if (!empty($this->mobile))
		{
			$c = Crm2_Api::getByMobile($this->mobile);
			if (!empty($c))
			{
				$this->searchConf['cid'] = $c['cid'];
			}
		}

		$res = Order_Api::getOrderList( $this->searchConf, array('delivery_date', 'asc'), $this->start, $this->num);
		$this->orders = $res['list'];
		$this->total = $res['total'];

        // 获取订单的司机搬运工信息
        $this->coopworkerOfOrder = Logistics_Coopworker_Api::getOrdersOfWorkers(Tool_Array::getFields($this->orders, 'oid'));

		$this->addFootJs(array('js/apps/order.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$app = '/logistics/need_send_order_list.php?' . http_build_query($this->searchConf);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('total', $this->total);
		$this->smarty->assign('searchConf', $this->searchConf);
		$this->smarty->assign('order_steps', Conf_Order::getOrderStepNames());
		$this->smarty->assign('order_status', Conf_Base::getOrderStatusList());
		$this->smarty->assign('orders', $this->orders);
        $this->smarty->assign('coopworker_of_order', $this->coopworkerOfOrder);

		$this->smarty->display('logistics/need_send_order_list.html');
	}
}

$app = new App('pri');
$app->run();

