<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
	private $orders;
	private $searchConf;
	private $today;
	private $result;

	protected function getPara()
	{
		$this->searchConf = array(
			'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
			'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
			'driver_phone' => Tool_Input::clean('r', 'driver_phone', TYPE_STR),
			'from_date' => Tool_Input::clean('r', 'from_date', TYPE_STR),
			'end_date' => Tool_Input::clean('r', 'end_date', TYPE_STR),
			'delivery_date' => Tool_Input::clean('r', 'delivery_date', TYPE_STR),
			'construction' => Tool_Input::clean('r', 'construction', TYPE_STR),
			'wid' => $this->getWarehouseId(),
			'time_interval' => Tool_Input::clean('r', 'time_interval', TYPE_UINT),
		);

		$this->today = Tool_Input::clean('r', 'today', TYPE_UINT);
		$step = Tool_Input::clean('r', 'step', TYPE_UINT);

		$this->searchConf['status'] = Conf_Base::STATUS_NORMAL;
		$this->searchConf['step'] = empty($step) ? Conf_Order::ORDER_STEP_BOUGHT:$step;
	}

	protected function main()
	{
		if ($this->today)
		{
			$this->searchConf['delivery_date'] = date('Y-m-d');
		}

		$res = Order_Api::getOrderList($this->searchConf, array('delivery_date', 'asc'), 0, 0);

		$this->orders = $res['list'];

		$oids = Tool_Array::getFields($this->orders, 'oid');
		$products = Order_Api::getProductByOids($oids, array('oid', 'pid'));

		foreach ($this->orders as &$item)
		{
			$item['has_sand'] = false;
		}

		foreach ($products as $p)
		{
			$oid = $p['oid'];
			if (!$this->orders[$oid]['has_sand'] && in_array($p['pid'], Conf_Order::$SAND_CEMENT_BRICK_PIDS))
			{

				$this->orders[$oid]['has_sand'] = true;
			}
		}

		foreach ($this->orders as $order)
		{
			$this->result[] = array(
				'oid' => $order['oid'],
				'contact_name' => $order['contact_name'],
				'contact_phone' => $order['contact_phone'],
				'delivery_date' => $order['delivery_date'],
				'address' => $order['address'],
				'has_sand' => $order['has_sand'],
			);
		}

		$this->addFootJs('js/apps/map.js');
	}

	protected function outputBody()
	{
		$this->smarty->assign('order_list', Tool_Array::jsonEncode($this->result));
		$this->smarty->assign('key', Conf_Base::AMAP_KEY);
		$this->smarty->assign('searchConf', $this->searchConf);
		$this->smarty->display('logistics/map.html');
	}
}

$app = new App('pri');
$app->run();

