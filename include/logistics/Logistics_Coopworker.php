<?php

class Logistics_Coopworker extends Base_Func
{
	const TABLE = 't_coopworker_order';

	public function saveWorkerForOrder($data)
	{
		assert(!empty($data));
		assert(!empty($data['oid']) || !empty($data['obj_id']));
		assert(!empty($data['cuid']));

        $data['obj_id'] = $this->is($data['obj_id'])? $data['obj_id']: $data['oid'];
        $data['obj_type'] = $this->is($data['obj_type'])? $data['obj_type']: Conf_Coopworker::OBJ_TYPE_ORDER;

		//$data['occupied'] = 1;    //暂时不开启这项
		$data['ctime'] = date('Y-m-d H:i:s');
		$data['note'] = isset($data['note']) ? $data['note'] : '';
        $data['money_note'] = isset($data['money_note'])? $data['money_note']: '';

		$res = $this->one->insert(self::TABLE, $data);
		$id = $res['insertid'];

		return $id;
	}

	public function update($obj_id, $cuid, $type, $data, $obj_type = Conf_Coopworker::OBJ_TYPE_ORDER)
	{
		assert(!empty($obj_id));
		assert(!empty($cuid));
		assert(!empty($type));
		assert(!empty($data));
		$where['obj_id'] = $obj_id;
		$where['obj_type'] = $obj_type;
		$where['cuid'] = $cuid;
		$where['type'] = $type;

		$ret = $this->one->update(self::TABLE, $data, array(), $where);

		return $ret['affectedrows'];
	}
    
    public function updateById($id, $data)
    {
        assert(!empty($id));
        assert(!empty($data));
        
        $where = 'id='. $id;
        $ret = $this->one->update(self::TABLE, $data, array(), $where);
        
        return $ret['affectedrows'];
    }
    
    public function updateByWhere($where, $upData)
    {
        assert(!empty($where));

        if (!$this->is($where['obj_id']) && isset($where['oid']))
        {
            $where['obj_id'] = $where['oid'];
            $where['obj_type'] = Conf_Coopworker::OBJ_TYPE_ORDER;
        }

        if (!$this->is($upData['obj_id']) && isset($upData['oid']))
        {
            $upData['obj_id'] = $upData['oid'];
            $upData['obj_type'] = Conf_Coopworker::OBJ_TYPE_ORDER;
        }

        $affectRaw = $this->one->update(self::TABLE, $upData, array(), $where);
        
        return $affectRaw;
    }

	public function cancel($obj_id, $obj_type = Conf_Coopworker::OBJ_TYPE_ORDER)
	{
		assert(!empty($obj_id));

		$where['obj_id'] = $obj_id;
		$where['obj_type'] = $obj_type;
		$info = array(
			'status' => Conf_Base::STATUS_DELETED,
		);
		$ret = $this->one->update(self::TABLE, $info, array(), $where);

		return $ret['affectedrows'];
	}

    public function get($id, $field = array('*'))
    {
        $where = array('id' => $id);
        $ret = $this->one->select(self::TABLE, $field, $where);

        return $ret['data'][0];
    }

	public function getByIds($ids)
	{
		$where = 'status=0 and id in (' . implode(',', $ids) . ')';

		$order = 'order by obj_id desc';

		$ret = $this->one->select(self::TABLE, array('*'), $where, $order);

		return $ret['data'];
	}

	/**
	 * 取订单第三方工人.
	 *
	 * @param int $obj_id
	 * @param int $type {1:运费 2:搬运费}
	 * @param int $userType {1:司机 2:搬运工}
     * @param int $obj_type
	 */
	public function getByOid($obj_id, $cuid = 0, $type = 0, $userType = 0, $obj_type = Conf_Coopworker::OBJ_TYPE_ORDER)
	{
		$where = array(
			'obj_id' => $obj_id,
			'status' => 0,
            'obj_type' => $obj_type,
		);
		if (!empty($cuid))
		{
			$where['cuid'] = $cuid;
		}
		if (!empty($type))
		{
			$where['type'] = $type;
		}
		if (!empty($userType))
		{
			$where['user_type'] = $userType;
		}

		$ret = $this->one->select(self::TABLE, array('*'), $where);

		return $ret['data'];
	}

	public function getByOids($obj_ids, $type = 0, $obj_type = Conf_Coopworker::OBJ_TYPE_ORDER)
	{
		assert(!empty($obj_ids));

		$where = 'status=0 and obj_id in (' . implode(',', $obj_ids) . ') and obj_type = ' . $obj_type;

		if ($type != 0)
		{
			$where .= ' and type=' . $type;
		}

        $ret = $this->one->select(self::TABLE, array('*'), $where);

        return $ret['data'];
	}

	public function getByCuids($cuids, $type = 0)
	{
		assert(!empty($cuids));

		$where = 'status=0 and cuid in (' . implode(',', $cuids) . ')';

		if ($type != 0)
		{
			$where .= ' and type=' . $type;
		}

		$ret = $this->one->select(self::TABLE, array('*'), $where);

		return $ret['data'];
	}

	public function getByWorker($cuid, $searchConf, $start = 0, $num = 20, $order = '')
	{
		$where = 'status=0';

		if (!empty($cuid))
		{
			if (is_array($cuid))
			{
				$where .= ' and cuid in (' . implode(',', $cuid) . ')';
			}
			else
			{
				$where .= ' and cuid=' . $cuid;
			}
		}

		if ($this->is($searchConf['user_type']))
		{
			$where .= ' and user_type=' . $searchConf['user_type'];
		}

		if (isset($searchConf['type']) && !empty($searchConf['type']))
		{
			$where .= ' and type = ' . $searchConf['type'];
		}
		if (isset($searchConf['paid']) && $searchConf['paid'] != Conf_Base::STATUS_ALL)
		{
			$where .= ' and paid=' . $searchConf['paid'];
		}
		if ($this->is($searchConf['oid']))
		{
            $where .= ' and obj_id='. $searchConf['oid']. ' and obj_type='. Conf_Coopworker::OBJ_TYPE_ORDER;
		}
        if ($this->is($searchConf['obj_id'])) {
            $where .= ' and obj_id = ' . $searchConf['obj_id'];
        }
        if ($this->is($searchConf['obj_type'])) {
            $where .= ' and obj_type = ' . $searchConf['obj_type'];
        }
		if (isset($searchConf['btime']) && !empty($searchConf['btime']))
		{
			$where .= ' and date(ctime)>=date("' . $searchConf['btime'] . '")';
		}
		if (isset($searchConf['etime']) && !empty($searchConf['etime']))
		{
			$where .= ' and date(ctime)<=date("' . $searchConf['etime'] . '")';
		}
		if ($searchConf['has_finish'] == 2) {
			$where .= ' AND finish_time<=0';
		} else if ($searchConf['has_finish'] == 1) {
			$where .= ' AND finish_time>0';
		}

		$ret = array('total' => 0, 'data' => array());
		$cRet = $this->one->select(self::TABLE, array('count(1)'), $where);

		if ($cRet['data'][0]['count(1)'] == 0)
		{
			return $ret;
		}

//		$totalAmount = $this->one->select(self::TABLE, array('sum(price)'), $where);


		$order = !empty($order) ? $order : 'order by id desc';
		$dRet = $this->one->select(self::TABLE, array('*'), $where, $order, $start, $num);
		$ret['total'] = $cRet['data'][0]['count(1)'];
		$ret['data'] = $dRet['data'];
//		$ret['total_amount'] = $totalAmount['data'][0]['sum(price)'];

		return $ret;
	}

	/**
	 * 按照条件筛选[角色=司机]的费用（运费/搬运费）.
	 *
	 * @param array $searchConf
	 * @param int $start
	 * @param int $num
	 * @param string $order
     *
     * @return array
	 */
	public function getDriverOrderList($searchConf, $start = 0, $num = 20, $order = '')
	{
		$where = $this->_genWhere4CoopworkerOrderList($searchConf, Conf_Base::COOPWORKER_DRIVER);
        
		$counterRet = $this->one->select(self::TABLE, array('count(1)'), $where);
		$total = $counterRet['data'][0]['count(1)'];
        
		$list = array();
		if ($total > 0)
		{
			$order = 'order by obj_id desc';
			$ret = $this->one->select(self::TABLE, array('*'), $where, $order, $start, $num);
			$list = $ret['data'];

//			$orderWhere = sprintf('step>=5 and oid in (select obj_id from %s where %s and obj_type = %d)', self::TABLE, $where, Conf_Coopworker::OBJ_TYPE_ORDER);
//			$orderRet = $this->one->select('t_order', array('sum(price)'), $orderWhere);
//			$orderProductsPrice = $orderRet['data'][0]['sum(price)'] / 100;
//            
//            $refundWhere = sprintf('rid in (select obj_id from %s where %s and obj_type = %d)', self::TABLE, $where, Conf_Coopworker::OBJ_TYPE_REFUND_ORDER);
//            $refundRet = $this->one->select('t_refund', array('sum(price)'), $refundWhere);
//            $refundProductsPrice = $refundRet['data'][0]['sum(price)'] / 100;
		}
        
		return array('total' => $total, 'list' => $list, 'products_price' => 0);
	}

	/**
	 * 按条件筛选[角色=搬运工]的费用（搬运费）.
	 *
	 * @param array $searchConf
	 * @param int $start
	 * @param int $num
	 * @param string $order
     *
     * @return array
	 */
	public function getCarrierOrderList($searchConf, $start = 0, $num = 20, $order = '')
	{
		$where = $this->_genWhere4CoopworkerOrderList($searchConf, Conf_Base::COOPWORKER_CARRIER);

		$counterRet = $this->one->select(self::TABLE, array('count(1)'), $where);
		$total = $counterRet['data'][0]['count(1)'];

		$list = array();
		if ($total > 0)
		{
			$order = 'order by id desc';
			$ret = $this->one->select(self::TABLE, array('*'), $where, $order, $start, $num);
			$list = $ret['data'];
		}

		return array('total' => $total, 'list' => $list);
	}

	public function getListByWhere($where, $fields = array('*'))
	{
		$ret = $this->one->select(self::TABLE, $fields, $where);

		return $ret['data'];
	}

	private function _genWhere4CoopworkerOrderList($search, $user_type)
	{
	    $where = '1 = 1';
	    if ($this->is($search['statement_id']) && $this->is($search['user_type']))
        {
            $where .= sprintf(' and statement_id = %d', $search['statement_id']);
            $user_type = $search['user_type'];
        }
        if ($search['driver_unpaid'] == 'on' || $search['carrier_unpaid'] == 'on' || $search['generate_statement'] == 'on')
        {
            $where .= ' and statement_id = 0';
        }
		$where .= ' and status=0 and user_type=' . $user_type;
		if ($user_type == Conf_Base::COOPWORKER_DRIVER)
		{
			$typeTable = 't_driver';
			$typeId = 'did';
		}
		else
		{
			$typeTable = 't_carrier';
			$typeId = 'cid';
		}

		if ($this->is($search['type']))
		{
			$where .= ' and type=' . $search['type'];
		}

		if (isset($search['paid']))
		{
			$where .= ' and paid=' . $search['paid'];
		}

		if ($this->is($search['cuid']))
		{
			$where .= ' and cuid=' . $search['cuid'];
		}

		// 联合查询 - t_driver
		$driverWhere = '1=1';
		if ($this->is($search['mobile']))
		{
			$driverWhere .= sprintf(' and mobile like ("%%%s%%")', $search['mobile']);
		}
		if ($this->is($search['name']))
		{
			$driverWhere .= sprintf(' and name like ("%%%s%%")', $search['name']);
		}
		if ($this->is($search['source']))
		{
			$driverWhere .= sprintf(' and source=%d', $search['source']);
		}
		if ($this->is($search['wid']))
		{
            if (is_array($search['wid']))
            {
                $driverWhere .= sprintf(' AND wid in(%s) ', join(',', $search['wid']));
            } else {
                $driverWhere .= sprintf(' AND wid=%d ', $search['wid']);
            }
		}

		// 联合查询 - t_order
		$orderWhere = 'status=0';
		if ($this->is($search['btime']))
		{
			$orderWhere .= sprintf(' and delivery_date>="%s 00:00:00"', $search['btime']);
		}
		if ($this->is($search['etime']))
		{
			$orderWhere .= sprintf(' and delivery_date<="%s 23:59:59"', $search['etime']);
		}

		if ($this->is($search['by_order'])) // 按订单类型
		{
			if ($search['by_order'] == 'order_return_unpaid')  // 回单未收款
			{
				$orderWhere .= sprintf(' and step=%d and paid!=%d', Conf_Order::ORDER_STEP_FINISHED, Conf_Order::HAD_PAID);
			}
		}

		// 拼装sql
		if ($driverWhere != '1=1')
		{
			$where .= sprintf(' and cuid in ( select %s from %s where %s)', $typeId, $typeTable, $driverWhere);
		}

		if ($orderWhere != 'status=0')
		{
			$where .= sprintf(' and obj_id in (select oid from t_order where %s)', $orderWhere);
		}
        
		return $where;
	}

    public function getStatementDetail($statement_ids, $field=array('*'))
    {
        if (is_array($statement_ids))
        {
            $statement_ids = implode(',', $statement_ids);
            $where = sprintf('status = %d and statement_id in (%s)',Conf_Base::STATUS_NORMAL, $statement_ids);
        }
        else
        {
            $where = sprintf('status = %d and statement_id = %d',Conf_Base::STATUS_NORMAL, $statement_ids);
        }
        $data = $this->getListByWhere($where, $field);

        return $data;
    }
}