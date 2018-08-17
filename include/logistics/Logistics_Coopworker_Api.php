<?php

/**
 * 合作工人类 - 第三方的司机，搬运工.
 *
 */
class Logistics_Coopworker_Api
{
	///////////////////// 工人的用户信息 /////////////////////

	public static function getCoopworkInfoById($cuid, $userType)
	{
		assert(!empty($cuid));
		assert(!empty($userType));

		$userInfo = array();

		if ($userType == Conf_Base::COOPWORKER_DRIVER)
		{
			$ld = new Logistics_Driver();
			$userInfo = $ld->get($cuid);
			$userInfo['uid'] = isset($userInfo['did']) ? $userInfo['did'] : 0;
		}
		else if ($userType == Conf_Base::COOPWORKER_CARRIER)
		{
			$lc = new Logistics_Carrier();
			$userInfo = $lc->get($cuid);
			$userInfo['uid'] = isset($userInfo['cid']) ? $userInfo['cid'] : 0;
		}

		return $userInfo;
	}

	public static function updateCoopworkerInfo($cuid, $userType, $upData)
	{
		assert(!empty($cuid));
		assert(!empty($userType));
		assert(!empty($upData));

		if ($userType == Conf_Base::COOPWORKER_DRIVER)
		{
			$ld = new Logistics_Driver();
			$affectRow = $ld->update($cuid, $upData);
		}
		else if ($userType == Conf_Base::COOPWORKER_CARRIER)
		{
			$lc = new Logistics_Carrier();
			$affectRow = $lc->update($cuid, $upData);
		}

		return $affectRow;
	}

	///////////////////// 工人的订单 ///////////////////////

	/**
	 * 获取工人的订单的详情.
	 *
	 * @param int $cuid
	 * @param int $oid
	 * @param int $userType {1司机 2搬运工}
	 * @param int $type {0全部 1运费 2搬运费}
	 * @param bool $needMoreInfo 更多信息 订单商品，销售等
	 */
	public static function getWorkerOrderDetail($cuid, $oid, $userType, $type = 0, $needMoreInfo = TRUE)
	{
		// 工人订单
		$lc = new Logistics_Coopworker();
		$orders = $lc->getByOid($oid, 0, $type, $userType);

		// 删除不属于司机的订单
		foreach ($orders as $key => $one)
		{
			if ($one['cuid'] != $cuid)
			{
				unset($orders[$key]);
			}
		}

		// 订单信息
		$oo = new Order_Order();
		$orderDetail = $oo->get($oid);
		Order_Helper::formatOrder($orderDetail);
		$orderDetail['total_price'] = Order_Helper::calOrderNeedToPay($orderDetail);

		if (!($orderDetail['step'] >= Conf_Order::ORDER_STEP_SURE && $orderDetail['step'] <= Conf_Order::ORDER_STEP_HAS_DRIVER) && empty($orders))
		{
			$orderDetail = array();
		}

		$ret = array(
			'worker_order' => $orders,
			'order_info' => $orderDetail,
			'order_product' => array(),
			'saler' => array(),
		);

		if (!empty($orderDetail) && $needMoreInfo)
		{

			// 补充信息：订单商品
			$ret['order_product'] = Order_Api::getOrderProducts($oid);

			//补充销售信息
			$as = new Admin_Staff();

			if (!empty($ret['order_info']['saler_suid']))
			{
				$salerInfo = $as->get($ret['order_info']['saler_suid']);

				if ($salerInfo['status'] == Conf_Base::STATUS_NORMAL)
				{
					$ret['saler'] = $salerInfo;
				}
			}
			if (empty($ret['saler']))
			{
				$salerAssis = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_ASSIS_SALER_NEW);
				$salerAssisCount = count($salerAssis);

				$ret['saler'] = $salerAssis[$oid % $salerAssisCount];
			}

		}

		return $ret;
	}

	/**
	 * 取工人的订单列表.
	 *
	 * @param int $cuid
	 * @param array $searchConf
	 * @param int $start
	 * @param int $num
	 * @param string $order
	 *
	 */
	public static function getOrderList4Worker($cuid, $searchConf, $start = 0, $num = 20, $order = '')
	{
		$lc = new Logistics_Coopworker();

		$order = !empty($order) ? $order : 'order by oid desc';
		$orderList = $lc->getByWorker($cuid, $searchConf, $start, $num, $order);

		if (!empty($orderList['data']))
		{
			// 补充信息：订单信息
			$oo = new Order_Order();
			$oo->appendInfos($orderList['data'], 'oid');

			// 补充信息：销售人员
			$salerIds = array();

			foreach ($orderList['data'] as $order)
			{
				$salerIds[] = $order['_order']['saler_suid'];
			}

			$as = new Admin_Staff();
			$salerInfos = Tool_Array::list2Map($as->getUsers(array_unique($salerIds)), 'suid');
			$salerAssis = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_ASSIS_SALER_NEW);
			$salerAssisCount = count($salerAssis);

			foreach ($orderList['data'] as $key => &$_order)
			{
				if ($_order['_order']['status'] != Conf_Base::STATUS_NORMAL || $_order['_order']['step'] < 1) {
					unset($orderList['data'][$key]);
					continue;
				}
				$saler = $_order['_order']['saler_suid'];

				$_order['_saler'] = array();
				if (array_key_exists($saler, $salerInfos) && $salerInfos[$saler]['status'] == Conf_Base::STATUS_NORMAL)
				{
					$_order['_saler'] = $salerInfos[$saler];
				}
				else if ($salerAssisCount > 0)
				{
					$_order['_saler'] = $salerAssis[$_order['oid'] % $salerAssisCount];
				}

				$_order['_order']['total_price'] = Order_Helper::calOrderTotalPrice($_order['_order']);
			}
		}

		return $orderList;
	}

	/**
	 * 绑定司机和订单 - 司机领单.
	 *
	 * @param int $cuid 工人id
	 * @param array $orderInfo 订单信息
	 * @param int $type 费用类型{1运费 2搬运费}
	 * @param int $userType 工人身份{1司机 2搬运工}
	 * @param int $adminId 管理员账号
	 */
	public static function bindWorkerAndOrder($cuid, $orderInfo, $type, $userType, $adminId)
	{
		assert(!empty($cuid) && !empty($type) && !empty($userType));
		assert(!empty($orderInfo));

		$data = array(
			'cuid' => $cuid,
			'oid' => $orderInfo['oid'],
			'wid' => $orderInfo['wid'],
			'type' => $type,
			'suid' => $adminId,
			'user_type' => $userType,
			'confirm_time' => date('Y-m-d H:i:s'),
		);

		$oc = new Logistics_Coopworker();
		$oc->saveWorkerForOrder($data);

		// 客服确认 && 司机领单 订单更新为已安排司机
		if (($orderInfo['step'] == Conf_Order::ORDER_STEP_SURE || $orderInfo['step'] == Conf_Order::ORDER_STEP_BOUGHT) && $type == Conf_Base::COOPWORKER_DRIVER)
		{
			$oo = new Order_Order();
			$oo->update($orderInfo['oid'], array('step' => Conf_Order::ORDER_STEP_HAS_DRIVER));
            
            // 安排司机的日志
            $param = array('newStep' => Conf_Order::$ORDER_STEPS[Conf_Order::ORDER_STEP_HAS_DRIVER]);
			Admin_Api::addOrderActionLog($adminId, $orderInfo['oid'], Conf_Order_Action_Log::ACTION_CHANGE_STEP, $param);
		}

	}

	/**
	 * 解绑司机和订单 - 司机取消领单.
	 *
	 * @param int $cuid 工人id
	 * @param array $orderInfo 订单信息
	 * @param int $type 费用类型{1运费 2搬运费}
	 * @param int $adminId 管理员账号
	 */
	public static function unBindWorkerAndOrder($cuid, $orderInfo, $type, $adminId)
	{
		assert(!empty($cuid) && !empty($type));
		assert(!empty($orderInfo));

		$oc = new Logistics_Coopworker();
		$upData = array(
			'status' => Conf_Base::STATUS_DELETED,
			'suid' => $adminId,
		);
		$oc->update($orderInfo['oid'], $cuid, $type, $upData);

		// 已安排司机 && 司机取消领单
		if (($orderInfo['step'] == Conf_Order::ORDER_STEP_HAS_DRIVER || $orderInfo['step'] == Conf_Order::ORDER_STEP_BOUGHT) && $type == Conf_Base::COOPWORKER_DRIVER)
		{
			$drivers = $oc->getByOid($orderInfo['oid'], 0, Conf_Base::COOPWORKER_DRIVER);
			if (empty($drivers))
			{
				$oo = new Order_Order();
				$oo->update($orderInfo['oid'], array('step' => Conf_Order::ORDER_STEP_BOUGHT));
			}
		}

	}

	/**
	 * 更新工人的订单.
	 */
	public static function updateOrderOfWorker($cuid, $oid, $type, $data)
	{
		assert(!empty($cuid) && !empty($oid) && !empty($data));

		$oc = new Logistics_Coopworker();
		$oc->update($oid, $cuid, $type, $data);

	}

	/**
	 * 获取订单已经安排的合作工人.
	 *
	 * @param int $obj_id
	 * @param int $type
	 * @param bool $needWorkerInfo
     * @param int $objType
     * @return array
	 */
	public static function getOrderOfWorkers($obj_id, $type = 0, $needWorkerInfo = FALSE, $objType = Conf_Coopworker::OBJ_TYPE_ORDER)
	{
		$oc = new Logistics_Coopworker();

		$ret = $oc->getByOid($obj_id, 0, $type, 0, $objType);

		if (!empty($ret) && $needWorkerInfo)
		{
			self::_supplyCoopworkerInfos($ret);
		}

		return $ret;

	}

	public static function getOrdersOfWorkers($obj_ids, $type = 0, $objType = Conf_Coopworker::OBJ_TYPE_ORDER)
	{
		if (empty($obj_ids))
			return array();

		$oc = new Logistics_Coopworker();

        $_ret = $oc->getByOids($obj_ids, $type, $objType);

		$ret = array();
		if (!empty($_ret))
		{
			self::_supplyCoopworkerInfos($_ret);

			foreach ($_ret as $one)
			{
				$ret[$one['obj_id']][] = $one;
			}
		}

		return $ret;
	}

	public static function getOrdersByType($obj_ids, $type = 0, $objType = Conf_Coopworker::OBJ_TYPE_ORDER)
	{
		if (empty($obj_ids))
			return array();

		$oc = new Logistics_Coopworker();
		$_ret = $oc->getByOids($obj_ids, $type, $objType);

		return $_ret;
	}

	public static function updateOrderCoopworker($objId, $cuid, $type, $data, $objType = Conf_Coopworker::OBJ_TYPE_ORDER)
	{
		$oc = new Logistics_Coopworker();
		$ret = $oc->update($objId, $cuid, $type, $data, $objType);

		return $ret;
	}

    public static function updateOrderCoopworkerById($id, $data)
    {
        $oc = new Logistics_Coopworker();
        $ret = $oc->updateById($id, $data);

        return $ret;
    }

	public static function finishOrderCoopworker($oid)
	{
		$oc = new Logistics_Coopworker();
		$where = array('oid' => $oid);
		$update = array('finish_time' => date('Y-m-d H:i:s'));
		$ret = $oc->updateByWhere($where, $update);

		return $ret;
	}

	public static function cancalOrderCoopworker($oid)
	{
		$lc = new Logistics_Coopworker();

		return $lc->cancel($oid);
	}


	/**
	 * 第三方工人的批量支付.
	 *
	 * @param array /json $blukPayList
	 *  {oid-订单id, cuid-第三方工人的id, type-费用类型, user_type-工人身份}
	 * @param array $adminInfo
	 * @param int $paymentType 支付类型
	 *
	 */
	public static function blukPayCoopworker($blukPayList, $adminInfo, $paymentType = 2, $statementIds = array(), $byBatch = false)
	{
		$blukPayList = is_string($blukPayList) ? json_decode($blukPayList, TRUE) : $blukPayList;

		if (!is_array($blukPayList) || empty($blukPayList))
		{
			throw new Exception('工人订单列表错误!');
		}

		$oids = array_unique(Tool_Array::getFields($blukPayList, 'oid'));
		$cuids = Tool_Array::getFields($blukPayList, 'cuid');

        if ($byBatch)
        {
            $statementIds = array_unique(Tool_Array::getFields($blukPayList, 'statement_id'));
            $lcs = new Logistics_Coopworker_Statement();
            $statementInfos = $lcs->getById($statementIds);
            $batchs = array_unique(Tool_Array::getFields($statementInfos, 'batch'));
            if (count($batchs) != 1)
            {
                throw new Exception('不是一个批次的结算单,不能一起结算');
            }
        }
        else
        {
            if (count(array_unique($cuids)) != 1)
            {
                throw new Exception('订单数据属于多个工人，不能使用批量结账');
            }
        }

		$lc = new Logistics_Coopworker();
		$coopworkerOrderInfos = Tool_Array::list2Map($lc->getByOid($oids), 'id');

		// check: 是否含有已经支付的费用，是否有没有设置费用的订单
		foreach ($blukPayList as $one)
		{
			$_desc = $one['type'] == 1 ? '运费' : '搬运费';

			if ($coopworkerOrderInfos[$one['id']]['paid'] == 1)
			{
				throw new Exception('[错误] 订单：' . $one['oid'] . ' ' . $_desc . ' 已经支付！');
			}
			if ($coopworkerOrderInfos[$one['id']]['price'] == 0)
			{
				throw new Exception('[错误] 订单：' . $one['oid'] . ' ' . $_desc . ' 费用为0元，不能支付！');
			}
		}

		// 支付
		foreach ($blukPayList as $_order)
		{
			// 写支付状态
			$lc->update($_order['oid'], $_order['cuid'], $_order['type'], array('paid' => 1));

			// 写支出记录 t_coopworker_money_out_history 表
			Finance_Api::paidCoopworker($_order['oid'], $_order['cuid'], $_order['type'], $_order['user_type'], $adminInfo, $paymentType);

		}

		//更新结算单状态
        $where = array('id' => $statementIds, 'step' => Conf_Coopworker::STATEMENT_STEP_CHECK);
        $upData = array(
            'step' => Conf_Coopworker::STATEMENT_STEP_PAID,
            'pay_time' => date('Y-m-d H:i:s'),
            'payment_type' => $paymentType,
        );
        $lcs = new Logistics_Coopworker_Statement();
        $lcs->updateByWhere($where, $upData);
	}

    /**
     * 结算单批量结款.
     * 
     * @param type $statementIds
     * @param type $paymentType
     * @param type $suid
     * @throws Exception
     */
    public static function blukPayStatements($statementIds, $paymentType, $suid)
    {
        if (empty($statementIds)||empty($paymentType) || empty($suid)||!is_array($statementIds))
        {
            throw new Exception('参数错误！');
        }
        
        $lcs = new Logistics_Coopworker_Statement();
        $lc = new Logistics_Coopworker();
        
        //检测是否属于同一批次
        $lcsField = array('id', 'batch');
        $statementInfos = $lcs->getById($statementIds, $lcsField);
        $batchs = array_unique(Tool_Array::getFields($statementInfos, 'batch'));
        if (count($batchs) != 1)
        {
            throw new Exception('不是一个批次的结算单,不能一起结算');
        }
        
        // 写司机财务流水, 每次结款的数量$dealNum;
        $dealNum = 50;
        
        $dbProxy = Data_DB::getInstance();
        for($i=0; ; $i=$i+$dealNum)
        {
            $dealStatementIds = array_slice($statementIds, $i, $dealNum);
            if (empty($dealStatementIds))
            {
                break;
            }
            
            $doField = array('cuid','oid','wid','price','type','user_type');
            $driverOrders = $lc->getStatementDetail($dealStatementIds, $doField);
            if (empty($driverOrders))
            {
                continue;
            }
            
            // 数据量过大，直接写原声sql插入
            $sql = 'insert into t_coopworker_money_out_history (cuid,oid,wid,price,type,user_type,suid,payment_type,note,ctime) values ';
            $sqlData = array();
            foreach($driverOrders as $one)
            {
                $note = '';
                $ctime = date('Y-m-d H:i:s');
                $sqlData[] = "({$one['cuid']},{$one['oid']},{$one['wid']},{$one['price']},{$one['type']},{$one['user_type']},$suid,$paymentType,'$note','$ctime')";
            }
            $sql .= ' '.implode(',', $sqlData);
            $dbProxy->sQuery('t_coopworker_money_out_history', 1, $sql);
        }
        
        // 更新结算单
        $where = array('id' => $statementIds);
        $upData = array(
            'step' => Conf_Coopworker::STATEMENT_STEP_PAID,
            'pay_time' => date('Y-m-d H:i:s'),
            'payment_type' => $paymentType,
        );
        $lcs->updateByWhere($where, $upData);
        
        // 更新司机订单
        $_where = array('statement_id' => $statementIds);
        $_upDate = array('paid' => 1);
        $lc->updateByWhere($_where, $_upDate);
        
    }
    
	public static function statementBlukPayCoopworker($statementIds, $adminInfo, $paymentType = 2, $byBatch = false)
    {
        $lc = new Logistics_Coopworker();
        $blukPayList = $lc->getStatementDetail($statementIds);
        
        self::blukPayCoopworker($blukPayList, $adminInfo, $paymentType, $statementIds, $byBatch);
    }

	private static function _supplyCoopworkerInfos(&$orderOfCoopworker)
	{
		$driverIds = $carrierIds = array();
		$driverInfos = $carrierInfos = array();

		foreach ($orderOfCoopworker as $oner)
		{
			if ($oner['user_type'] == Conf_Base::COOPWORKER_DRIVER)
			{
				$driverIds[] = $oner['cuid'];
			}
			else if ($oner['user_type'] == Conf_Base::COOPWORKER_CARRIER)
			{
				$carrierIds[] = $oner['cuid'];
			}
		}

		// 司机
		if (!empty($driverIds))
		{
			$ld = new Logistics_Driver();
			$driverInfos = Tool_Array::list2Map($ld->getByDids($driverIds), 'did');
		}
		// 搬运工
		if (!empty($carrierIds))
		{
			$lc = new Logistics_Carrier();

			$carrierInfos = Tool_Array::list2Map($lc->getByCids($carrierIds), 'cid');
		}

		foreach ($orderOfCoopworker as &$oner)
		{
			$oner['info'] = $oner['user_type'] == Conf_Base::COOPWORKER_DRIVER ? $driverInfos[$oner['cuid']] : $carrierInfos[$oner['cuid']];
		}

	}

	public static function getWorkerOrderList($cuid, $type, $start, $num, $order, $startTime, $endTime)
	{
		return array();

		$total = 0;
		$oc = new Logistics_Coopworker();
		$searchConf = array(
			'type' => $type,
			'btime' => $startTime,
			'etime' => $endTime,
		);
		$ret = $oc->getByWorkerWithOrder($cuid, $searchConf, $start, $num, $order, $total);

		if ($ret['total'] == 0)
		{
			return array('list' => array(), 'total' => 0);
		}

		$list = $ret['data'];

		//客户cid转成客户信息customer
		$cc = new Crm2_Customer();
		$cc->appendInfos($list);

		//把录单人suid转换成名称信息
		$as = new Admin_Staff();
		$as->appendSuers($list);

		//格式化订单信息 日期, 状态
		Order_Helper::formatOrders($list);

		Tool_Array::sortByField($list, 'oid');

		return array('list' => $list, 'total' => $ret['total']);
	}

	/**
	 * 按照条件筛选[角色=司机]的费用（运费/搬运费）.
	 *
	 * @param array $searchConf
	 * @param int $start
	 * @param int $num
	 * @param string $order
	 */
	public static function getDriverOrderList($searchConf, $start = 0, $num = 20, $order = '')
	{
		$lc = new Logistics_Coopworker();
		$workerOrderList = $lc->getDriverOrderList($searchConf, $start, $num, $order);

		if (empty($workerOrderList['list']))
		{
			return $workerOrderList;
		}

		$oids = Tool_Array::getFields($workerOrderList['list'], 'oid');
		$cuids = Tool_Array::getFields($workerOrderList['list'], 'cuid');

		// 补充订单信息
		$oo = new Order_Order();
		$orderInfos = Tool_Array::list2Map($oo->getBulk($oids), 'oid');

        $cids = array_unique(Tool_Array::getFields($orderInfos, 'cid'));

        //补充客户信息
        $cc = new Crm2_Customer();
        $customerInfo =  Tool_Array::list2Map($cc->getBulk($cids), 'cid');

        //客户账期,查询第一笔未支付订单的下单时间
        $cids = array_unique(array_keys($customerInfo));
        $kind = 't_order';
        $where = sprintf('status = %d and step >= %d and cid in(%s) and paid=0 group by cid', Conf_Base::STATUS_NORMAL, Conf_Order::ORDER_STEP_SURE, implode(',', $cids));
        $order = ' order by cid asc,oid asc';
        $field = array('cid', 'pay_time');
        $firstUnpaidOrders = $oo->getByRawWhere($kind, $where, $field, $order);
        $payTimeList = Tool_Array::list2Map($firstUnpaidOrders, 'cid', 'pay_time');
        foreach ($customerInfo as $cid => &$customer)
        {
            $customer['first_unpaid_order_pay_time'] = $payTimeList[$cid];
        }

		// 补充司机信息
		$ld = new Logistics_Driver();
		$driverInfos = Tool_Array::list2Map($ld->getByDids(array_unique($cuids)), 'did');

		foreach ($workerOrderList['list'] as &$_order)
		{
			$_order['_order'] = $orderInfos[$_order['oid']];
            $_order['_customer'] = $customerInfo[$_order['_order']['cid']];
			$_order['_worker'] = $driverInfos[$_order['cuid']];
			$_order['can_paid'] = $_order['paid'] == 0 && $_order['price'] != 0  && !$_order['statement_id']
                        && ( ($_order['type'] == 1 && $_order['_order']['step'] == 7) //回单后 可支付运费
                             || ($_order['type'] == 2 && $_order['_order']['step'] >= 5)) //出库后 可支付司机搬运费
				? 1 : 0;
            
		}
        
		return $workerOrderList;
	}

	/**
	 * 按照条件筛选[角色=搬运工]的费用（搬运费）.
	 *
	 * @param array $searchConf
	 * @param int $start
	 * @param int $num
	 * @param string $order
	 */
	public static function getCarrierOrderList($searchConf, $start = 0, $num = 20, $order = '')
	{
		$lc = new Logistics_Coopworker();
		$workerOrderList = $lc->getCarrierOrderList($searchConf, $start, $num, $order);

		if (empty($workerOrderList['list']))
		{
			return $workerOrderList;
		}

		$oids = Tool_Array::getFields($workerOrderList['list'], 'oid');
		$cuids = Tool_Array::getFields($workerOrderList['list'], 'cuid');

		// 补充订单信息
		$oo = new Order_Order();
		$orderInfos = Tool_Array::list2Map($oo->getBulk($oids), 'oid');

		// 补充司机信息
		$lcr = new Logistics_Carrier();
		$carrierInfos = Tool_Array::list2Map($lcr->getByCids(array_unique($cuids)), 'cid');

		foreach ($workerOrderList['list'] as &$_order)
		{
			$_order['_order'] = $orderInfos[$_order['oid']];
			$_order['_worker'] = $carrierInfos[$_order['cuid']];
			$_order['can_paid'] = $_order['paid'] == 0 && !$_order['statement_id'] && $_order['price'] != 0 && (($_order['type'] == 2 && $_order['_order']['step'] >= 5)) //出库后 可支付搬运费
				? 1 : 0;
		}

		return $workerOrderList;
	}


	public static function getDriverOrdersByConf($conf, $start = 0, $num = 20)
	{
		$rd = new Logistics_Driver();

		$total = 0;
		$driverList = $rd->getList($total, $conf, 0, 0, TRUE);

		if (empty($driverList))
		{
			return array();
		}
		$driverList = Tool_Array::list2Map($driverList, 'did');
		$dids = Tool_Array::getFields($driverList, 'did');
		$dids = array_diff($dids, array(641));

		$oc = new Logistics_Coopworker();
		$driverOrderList = $oc->getByCuids($dids, 1);
		if (empty($driverOrderList))
		{
			return array();
		}

		$oids = Tool_Array::getFields($driverOrderList, 'oid');
		if (empty($oids))
		{
			return array();
		}

		$oids = array_filter(array_unique($oids));
		rsort($oids);
		$oidsPart = array_slice($oids, $start, $num);
		$total = count($oids);

		$oo = new Order_Order();
		$orders = $oo->getBulk($oidsPart);

		$data = array();
		foreach ($driverOrderList as $order)
		{
			$oid = $order['oid'];
			$did = $order['cuid'];
			if (empty($orders[$oid]))
			{
				continue;
			}

			$driverList[$did]['_source'] = Conf_Driver::$DRIVER_SOURCE[$driverList[$did]['source']];
			$driverList[$did]['warehouse'] = Conf_Warehouse::$WAREHOUSES[$driverList[$did]['wid']];
			$orders[$oid]['delivery_date'] = date('Y年m月d日', strtotime($orders[$oid]['delivery_date']));

			$tmp = array(
				'order' => $orders[$oid],
				'driver' => $driverList[$did],
				'info' => $order,
			);
			$data[] = $tmp;
		}

		return array('list' => $data, 'total' => $total);
	}

	public static function getCarrierOrdersByConf($conf, $start = 0, $num = 20)
	{
		$rd = new Logistics_Carrier();

		$total = 0;
		$driverList = $rd->getList($total, $conf, 0, 0, TRUE);

		if (empty($driverList))
		{
			return array();
		}
		$driverList = Tool_Array::list2Map($driverList, 'cid');
		$dids = Tool_Array::getFields($driverList, 'cid');
		$dids = array_diff($dids, array(641));

		$oc = new Logistics_Coopworker();
		$driverOrderList = $oc->getByCuids($dids, 1);
		if (empty($driverOrderList))
		{
			return array();
		}

		$oids = Tool_Array::getFields($driverOrderList, 'oid');
		if (empty($oids))
		{
			return array();
		}

		$oids = array_filter(array_unique($oids));
		rsort($oids);
		$oidsPart = array_slice($oids, $start, $num);
		$total = count($oids);

		$oo = new Order_Order();
		$orders = $oo->getBulk($oidsPart);

		$data = array();
		foreach ($driverOrderList as $order)
		{
			$oid = $order['oid'];
			$did = $order['cuid'];
			if (empty($orders[$oid]))
			{
				continue;
			}

			$driverList[$did]['warehouse'] = Conf_Warehouse::$WAREHOUSES[$driverList[$did]['wid']];
			$orders[$oid]['delivery_date'] = date('Y年m月d日', strtotime($orders[$oid]['delivery_date']));

			$tmp = array(
				'order' => $orders[$oid],
				'driver' => $driverList[$did],
				'info' => $order,
			);
			$data[] = $tmp;
		}

		return array('list' => $data, 'total' => $total);
	}

	public static function getNotBackList($cuid, $start = 0, $num = 20)
	{
		$lc = new Logistics_Coopworker();

		$order = 'order by oid desc';
		$orderList = $lc->getByWorker($cuid, array('type' => Conf_Base::COOPWORKER_DRIVER), $start, $num, $order);

		if (!empty($orderList['data']))
		{
			// 补充信息：订单信息
			$oo = new Order_Order();
			$oo->appendInfos($orderList['data'], 'oid');

			foreach ($orderList['data'] as $key => $item)
			{
				if ($item['_order']['step'] >= Conf_Order::ORDER_STEP_FINISHED)
				{
					unset($orderList['data'][$key]);
					$orderList['total']--;
				}
			}

			// 补充信息：销售人员
			$salerIds = array();
			foreach ($orderList['data'] as $order)
			{
				$salerIds[] = $order['_order']['saler_suid'];
			}

			$salerInfos = array();
			$salerAssis = array();
			$salerAssisCount = 0;
			if (!empty($salerIds))
			{
				$as = new Admin_Staff();
				$salerInfos = Tool_Array::list2Map($as->getUsers(array_unique($salerIds)), 'suid');
				$salerAssis = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_ASSIS_SALER_NEW);
				$salerAssisCount = count($salerAssis);
			}


			foreach ($orderList['data'] as &$_order)
			{
				$saler = $_order['_order']['saler_suid'];

				$_order['_saler'] = array();
				if (array_key_exists($saler, $salerInfos) && $salerInfos[$saler]['status'] == Conf_Base::STATUS_NORMAL)
				{
					$_order['_saler'] = $salerInfos[$saler];
				}
				else if ($salerAssisCount > 0)
				{
					$_order['_saler'] = $salerAssis[$_order['oid'] % $salerAssisCount];
				}

				$_order['_order']['total_price'] = Order_Helper::calOrderTotalPrice($_order['_order']);
			}
		}

		return $orderList;
	}

	public static function getByOids($oids)
	{
		if (empty($oids)) return array();

		$lc = new Logistics_Coopworker();

		return $lc->getByOids($oids);
	}

	//生成结算单
    public static function generateStatement($ids, $payment_type, $adminId, $wid)
    {
        $lc = new Logistics_Coopworker;
        $coopworkerOderInfo = $lc->getByIds($ids);


        //检查是否包含多个人的订单
        $cuids = array_unique(Tool_Array::getFields($coopworkerOderInfo, 'cuid'));

        if (count($cuids) != 1)
        {
            throw new Exception('一个结算单只能包含一个人的订单');
        }
        else
        {
            $cuid = $cuids[0];
        }

        $price = array_sum(Tool_Array::getFields($coopworkerOderInfo, 'price'));

        $info = array(
            'cuid' => $cuid,
            'user_type' => $coopworkerOderInfo[0]['user_type'],
            'suid' => $adminId,
            'wid' => $wid,
            'payment_type' => $payment_type,
            'price' => $price,
            'step' => Conf_Coopworker::STATEMENT_STEP_CREATE,
        );
        $lcs = new Logistics_Coopworker_Statement();
        $statement_id = $lcs->add($info);
        if ($statement_id)
        {
            $where = array('id' => $ids);
            $upData = array(
                'statement_id ' => $statement_id,
            );
            $lc->updateByWhere($where, $upData);
        }

        return $statement_id;
    }

    //获取结算单列表
    public static function getStatementList($searchConf)
    {
        $statementStepList = Conf_Coopworker::$Statement_Step;
        unset($statementStepList[Conf_Coopworker::STATEMENT_STEP_CREATE]);
        $where = sprintf('status = %d', Conf_Base::STATUS_NORMAL);
        if ($searchConf['id'])
        {
            $where .= sprintf(' AND id = %d', $searchConf['id']);
        }
        if ($searchConf['cuid'])
        {
            $where .= sprintf(' AND cuid = %d', $searchConf['cuid']);
        }
        if ($searchConf['wid']) {
            if (is_array($searchConf['wid']))
            {
                $where .= sprintf(' AND wid in(%s) ', join(',', $searchConf['wid']));
            } else {
                $where .= sprintf(' AND wid=%d ', $searchConf['wid']);
            }
        }
        if ($searchConf['step'])
        {
            $where .= sprintf(' AND step = %d', $searchConf['step']);
        }
        else
        {
            $where .= sprintf(' AND step in (%s)', implode(',', array_keys($statementStepList)));
        }
        if ($searchConf['start_ctime'])
        {
            $where .= sprintf(' AND ctime >= "%s"', $searchConf['start_ctime']);
        }
        if ($searchConf['end_ctime'])
        {
            $where .= sprintf(' AND ctime <= "%s"', $searchConf['end_ctime']);
        }
        if ($searchConf['start_pay_time'])
        {
            $where .= sprintf(' AND pay_time >= "%s"', $searchConf['start_pay_time']);
        }
        if ($searchConf['end_pay_time'])
        {
            $where .= sprintf(' AND pay_time <= "%s"', $searchConf['end_pay_time']);
        }
        if ($searchConf['user_type'])
        {
            $where .= sprintf(' AND user_type = %d', $searchConf['user_type']);
        }
        if ($searchConf['batch'])
        {
            if (is_array($searchConf['batch']))
            {
                $where .= sprintf(' AND batch in (%s)', implode(',', $searchConf['batch']));
            }
            else
            {
                $where .= sprintf(' AND batch = "%s"', $searchConf['batch']);
            }
        }
        $where .= ' order by id desc';
        $lcs = new Logistics_Coopworker_Statement();
        $data = $lcs->getByWhere($where);
        return $data;
    }

    //获取结算单详情
    public function getStatementDetail($statement_id)
    {
        $lc = new Logistics_Coopworker();
        $workerOrderList = $lc->getStatementDetail($statement_id);

        return $workerOrderList;
    }

    public function cancelStatement($statement_id)
    {
        $statementWhere = array(
            'id' => $statement_id,
            'status' => Conf_Base::STATUS_NORMAL,
            'step' => Conf_Coopworker::STATEMENT_STEP_CREATE,
        );
        $statementUpData = array(
            'status' => Conf_Base::STATUS_DELETED,
        );
        $lcs = new Logistics_Coopworker_Statement();
        $statementRet = $lcs->updateByWhere($statementWhere, $statementUpData);
        if ($statementRet)
        {
            $orderWhere = array(
                'statement_id' => $statement_id,
            );
            $orderUpData = array(
                'statement_id' => 0,
            );
            $lc = new Logistics_Coopworker();
            $orderRet = $lc->updateByWhere($orderWhere, $orderUpData);
            return $orderRet['affectedrows'];
        }
        else
        {
            return false;
        }
    }

    public function sureStatement($statement_id, $suid)
    {
        $where = array(
            'id' => $statement_id,
            'status' => Conf_Base::STATUS_NORMAL,
            'step' => array(
                Conf_Coopworker::STATEMENT_STEP_CREATE,
            ),
        );
        $upData = array(
            'step' => Conf_Coopworker::STATEMENT_STEP_SURE,
            'sure_suid' => $suid,
        );
        $lcs = new Logistics_Coopworker_Statement();
        $affectRaw = $lcs->updateByWhere($where, $upData);
        if ($affectRaw)
        {
            return $affectRaw;
        }
        else
        {
            return false;
        }
    }

    public function checkStatement($statementId, $suid, $totalPirce)
    {
        $where = array(
            'id' => $statementId,
            'status' => Conf_Base::STATUS_NORMAL,
            'step' => array(
                Conf_Coopworker::STATEMENT_STEP_SURE,
            ),
        );
        $upData = array(
            'step' => Conf_Coopworker::STATEMENT_STEP_CHECK,
            'check_suid' => $suid,
        );
        $lcs = new Logistics_Coopworker_Statement();
        $statementInfo = $lcs->getById($statementId);

        if ($totalPirce != $statementInfo['price'])
        {
            throw new Exception('结算单金额有改动,请重新审核');
        }

        $affectRaw = $lcs->updateByWhere($where, $upData);
        if ($affectRaw)
        {
            return $affectRaw;
        }
        else
        {
            return false;
        }
    }

    public function generateBatch($statementIds)
    {
        $where = array(
            'id' => $statementIds,
            'status' => Conf_Base::STATUS_NORMAL,
            'step' => array(
                Conf_Coopworker::STATEMENT_STEP_CHECK,
            ),
            'batch' => '',
        );
        $upData = array(
            'batch' => date('YmdHis') . rand(1000,9999),
        );

        $lcs = new Logistics_Coopworker_Statement();
        $affectRaw = $lcs->updateByWhere($where, $upData);
        if ($affectRaw)
        {
            return $affectRaw;
        }
        else
        {
            return false;
        }
    }

    //获得司机的订单总数，已结款、未结款收入的总数
    public static function getDriverFanceInfo($cuid)
    {
        $lc = new Logistics_Coopworker();
        $conf = array(
            'user_type' => Conf_Base::COOPWORKER_DRIVER,
        );
        $data = $lc->getByWorker($cuid, $conf, 0, 0);
        $total = $data['total'];
        $unpaid = 0;
        $paid = 0;

        foreach ($data['data'] as $item)
        {
            if ($item['paid'] == 1)
            {
                $paid += $item['price'];
            }
            else if ($item['paid'] == 0 && $item['finish_time'] > 0)
            {
                $unpaid += $item['price'];
            }
        }
        $total = round($total/100, 2);
        $unpaid = round($unpaid/100, 2);
        $paid = round($paid/100, 2);
        return array('total' => $total, 'unpaid' => $unpaid, 'paid' => $paid);
    }

    public static function getDriverOrdersByWhere($where)
    {
        $lc = new Logistics_Coopworker();
        $data = $lc->getListByWhere($where);

        return $data;
    }

	/**
	 * 更新结算单的总价
	 * @param $id,结算单id
	 */
	public static function updateStatementPrice($id)
	{
		if ($id)
		{
			$lc = new Logistics_Coopworker();
			$lcs = new Logistics_Coopworker_Statement();

			$list = $lc->getStatementDetail($id, array('id', 'price'));
			$totalPrice = array_sum(Tool_Array::getFields($list, 'price'));
			$where = array('id'=> $id);
			$updata = array('price' => $totalPrice);
			$lcs->updateByWhere($where, $updata);
		}
	}
}