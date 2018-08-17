<?php

/**
 * 订单显示相关接口
 */
class Warehouse_View extends Base_Api
{
	public static function formatOrder(array &$order)
	{
		//阶段信息
        if (in_array($order['status'], array(Conf_Base::STATUS_WAIT_AUDIT, Conf_Base::STATUS_UN_AUDIT)))
        {
            $order['_step'] = Conf_Base::getInOrderStatusList($order['status']);
        }
        else
        {
            $order['_step'] = Conf_In_Order::getOrderStepName($order['step']);
        }

		//日期信息
		if ($order['delivery_date'] == '0000-00-00')
		{
			$order['_delivery_date'] = '-';
		} else
		{
			$date = strtotime($order['delivery_date']);
			$order['_delivery_date'] = date('m月d日 (', $date) . Str_Time::getWeekHZ(date('N', $date)) . ')';
		}

		$order['total'] = $order['price'] + $order['freight'];
		if ($order['total'] < 0)
		{
			$order['total'] = 0;
		}
		
		$order['_warehouse_name'] = empty($order['wid'])?'':Conf_Warehouse::$WAREHOUSES[$order['wid']];
        $order['_source'] = Conf_In_Order::$In_Order_Source[$order['source']];
	}

	public static function formatOrders(array &$orders)
	{
		foreach ($orders as &$order)
		{
			//阶段信息
            if (in_array($order['status'], array(Conf_Base::STATUS_WAIT_AUDIT, Conf_Base::STATUS_UN_AUDIT)))
            {
                $order['_step'] = Conf_Base::getInOrderStatusList($order['status']);
            }
            else
            {
                $order['_step'] = Conf_In_Order::getOrderStepName($order['step']);
            }
			//日期信息
			if ($order['delivery_date'] == '0000-00-00')
			{
				$order['_delivery_date'] = '-';
			}else{
				$date = strtotime($order['delivery_date']);
				$order['_delivery_date'] = date('m月d日 (', $date) . Str_Time::getWeekHZ(date('N', $date)) . ')';
			}
		}
	}

	public static function formatStockIn(array &$stockIn)
	{
		//阶段信息
		$stockIn['_payment_type'] = Conf_Stock::getPaymentName($stockIn['payment_type']);

		//日期信息
		if ($stockIn['delivery_date'] == '0000-00-00')
		{
			$stockIn['_delivery_date'] = '-';
		}
		else
		{
			$date = strtotime($stockIn['delivery_date']);
			$stockIn['_delivery_date'] = date('m月d日 (', strtotime($date)) . Str_Time::getWeekHZ(date('N', $date)) . ')';
		}
	}

	public static function formatStockIns(array &$stockIns)
	{
		foreach ($stockIns as &$stockIn)
		{
			$stockIn['_payment_type'] = Conf_Stock::getPaymentName($stockIn['payment_type']);

			//日期信息
			if ($stockIn['delivery_date'] == '0000-00-00')
			{
				$stockIn['_delivery_date'] = '-';
			}else{
				$date = strtotime($stockIn['delivery_date']);
				$stockIn['_delivery_date'] = date('m月d日 (', $date) . Str_Time::getWeekHZ(date('N', $date)) . ')';
			}
			
			//仓库名称
			$wid = !empty($stockIn['wid'])? $stockIn['wid']: Conf_Warehouse::WID_3;
			$stockIn['_warehouse_name'] = Conf_Warehouse::$WAREHOUSES[$wid];
            $stockIn['_is_upgrade_wid'] = Conf_Warehouse::isUpgradeWarehouse($wid);
            
            //入库单不能删除: 经销商仓库 || 未支付未对账 || 未上架
            $stockIn['_can_del'] = 1;
            if ($stockIn['paid']>Conf_Stock_In::UN_PAID || $stockIn['step']>=Conf_Stock_In::STEP_PART_SHELVED
                || Conf_Warehouse::isAgentWid($wid))
            {
                $stockIn['_can_del'] = 0;
            }
		}
	}
}
