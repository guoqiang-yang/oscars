<?php

/**
 * 订单显示相关接口
 */
class Order_View extends Base_Api
{
	public static function formatOrder(array &$order)
	{
		//阶段信息
		$order['_step'] = Conf_Order::getOrderStepName($order['step']);
		$order['_step_show'] = Conf_Order::getOrderStepShowName($order['step']);

		//日期信息
		if ($order['delivery_date'] == '0000-00-00')
		{
			$order['_delivery_date'] = '-';
		}else{
			$date = strtotime($order['delivery_date']);
			$hour = date('G', $date);
			if ($hour > 0)
			{
				$order['_delivery_date'] = date('Y年m月d日 ' . $hour . '点-' . ($hour + 1) . '点', strtotime($order['delivery_date']));
			}
			else
			{
				$order['_delivery_date'] = date('Y年m月d日', strtotime($order['delivery_date']));
			}
		}

		// 仓库名称
		$order['_warehouse_name'] = isset(Conf_Warehouse::$WAREHOUSES[$order['wid']])? Conf_Warehouse::$WAREHOUSES[$order['wid']]:'';
		
		//补充工地信息
//		$constructionId = $order['construction'];
//		if (!empty($constructionId))
//		{
//			$sc = new Crm_Construction();
//			$construction = $sc->get($constructionId);
//			$order['address'] = $construction['address'];
//			$order['city'] = $construction['city'];
//			$order['district'] = $construction['district'];
//			$order['area'] = $construction['area'];
//		}

		//司机电话——因为有的司机电话是在名字里面，所以要处理一下
		$order['_driver_phone'] = $order['driver_phone'];
		if (empty($order['_driver_phone']))
		{
			if (is_numeric($order['driver_name']) && strlen($order['driver_name']) == 11)
			{
				$order['_driver_phone'] = $order['driver_name'];
			}
		}

		$order['total'] = $order['price'] + $order['freight'] - $order['privilege'];
		if ($order['total'] < 0) $order['total'] = 0;
	}

	public static function formatOrders(array &$orders)
	{
		$constructionIds = Tool_Array::getFields($orders, 'construction');
		$sc = new Crm_Construction();
		$sites = $sc->getBulk($constructionIds);

		foreach ($orders as &$order)
		{
			//阶段信息
			$order['_step'] = Conf_Order::getOrderStepName($order['step']);
			if ($order['status'] != Conf_Base::STATUS_NORMAL)
			{
				$order['_step'] = Conf_Base::getStatus($order['status']);
			}
			$order['_step_show'] = Conf_Order::getOrderStepShowName($order['step']);

			//日期信息
			if ($order['delivery_date'] == '0000-00-00')
			{
				$order['_delivery_date'] = '-';
			}else{
				$date = strtotime($order['delivery_date']);
				$hour = date('G', $date);
				if ($hour > 0)
				{
					$order['_delivery_date'] = date('Y年m月d日 ' . $hour . '点-' . ($hour + 1) . '点', strtotime($order['delivery_date']));
				}
				else
				{
					$order['_delivery_date'] = date('Y年m月d日', strtotime($order['delivery_date']));
				}

			}

			//补充工地信息
//			$constructionId = $order['construction'];
//			if (!empty($sites[$constructionId]))
//			{
//				$construction = $sites[$constructionId];
//				$order['address'] = $construction['address'];
//			}
			
			//补充仓库信息
			$wid = !empty($order['wid'])? $order['wid']: Conf_Warehouse::WID_3;
			$order['_warehouse_name'] = Conf_Warehouse::$WAREHOUSES[$wid];

			//司机电话——因为有的司机电话是在名字里面，所以要处理一下
			$order['_driver_phone'] = $order['driver_phone'];
			if (empty($order['_driver_phone']))
			{
				if (is_numeric($order['driver_name']) && strlen($order['driver_name']) == 11)
				{
					$order['_driver_phone'] = $order['driver_name'];
				}
			}
		}
	}

	public static function formatRefund(array &$refund)
	{
		//阶段信息
		$refund['_step'] = Conf_Refund::getRefundStepName($refund['step']);

		//补充工地信息
		$constructionId = $refund['_order']['construction'];
		if (!empty($constructionId))
		{
			$sc = new Crm_Construction();
			$construction = $sc->get($constructionId);
			$refund['_order']['address'] = $construction['address'];
		}

		$refund['_order']['total'] = $refund['_order']['price'] + $refund['_order']['freight'] - $refund['_order']['privilege'];
		if ($refund['_order']['total'] < 0) $refund['_order']['total'] = 0;
	}

	public static function formatRefunds(array &$refunds)
	{
		$constructionIds = array();
		foreach ($refunds as $item)
		{
			$constructionIds[] = $item['_order']['construction'];
		}
		$sc = new Crm_Construction();
		$sites = $sc->getBulk($constructionIds);

		foreach ($refunds as &$refund)
		{
			//阶段信息
			$refund['_step'] = Conf_Refund::getRefundStepName($refund['step']);

			//补充工地信息
			$constructionId = $refund['_order']['construction'];
			if (!empty($sites[$constructionId]))
			{
				$construction = $sites[$constructionId];
				$refund['_order']['address'] = $construction['address'];
			}

			$refund['_order']['total'] = $refund['_order']['price'] + $refund['_order']['freight'] - $refund['_order']['privilege'];
			if ($refund['_order']['total'] < 0) $refund['_order']['total'] = 0;
		}

	}
}
