<?php
/**
 * 订单信息相关业务逻辑
 */
class Warehouse_In_Order extends Base_Func
{
	private $_dao;

	public function __construct()
	{
		$this->_dao = new Data_Dao('t_in_order');

		parent::__construct();
	}

	public function add(array $info)
	{
		if (empty($info['note']))
		{
			$info['note'] = '';
		}

		return $this->_dao->add($info);
	}

	public function delete($oid)
	{
		return $this->_dao->delete($oid);
	}

	public function update($oid, array $info)
	{
		return $this->_dao->update($oid, $info);
	}

	public function get($oid)
	{
		return $this->_dao->get($oid);
	}

	public function getBulk(array $oids)
	{
		return $this->_dao->getList($oids);
	}
    
    public function getVaildOrder($oid)
    {
        $where = 'status='.Conf_Base::STATUS_NORMAL;
        $where .= ' and oid='. $oid;
        
        $data = $this->_dao->getListWhere($where, false);
        
        return !empty($data)? $data[0]: array();
    }

	public function getList(array $conf, &$total, $order, $start = 0, $num = 20)
	{
		if (empty($order))
		{
			$order = 'order by oid desc';
		}

		$where = $this->_getWhereByConf($conf);
        
        if (!$this->is($conf['oid']) && $this->is($conf['sku_id']))
        {
            $subWhere = sprintf('select distinct(oid) from t_in_order_product where status=0 and sid=%d', $conf['sku_id']);
            $where .= ' and oid in ('. $subWhere. ')';
        }

		// 查询数量
		$total = $this->_dao->getTotal($where);
		if (empty($total))
		{
			return array();
		}

		// 查询结果
		return $this->_dao->order($order)->limit($start, $num)->getListWhere($where);
	}

	public function getListWhere($where, &$total, $order, $start = 0, $num = 20)
	{
		if (empty($order))
		{
			$order = 'order by oid desc';
		}

		// 查询数量
		$total = $this->_dao->getTotal($where);
		if (empty($total))
		{
			return array();
		}

		// 查询结果
		return $this->_dao->order($order)->limit($start, $num)->getListWhere($where);
	}

	public function getOrdersOfSupplier($sid, &$total, $order = '', $start = 0, $num = 20)
	{
		$sid = intval($sid);
		assert($sid > 0);

		$where = sprintf('sid=%d and status=%d and step!=%d',  $sid, Conf_Base::STATUS_NORMAL, Conf_Order::ORDER_STEP_EMPTY);
		if (empty($order))
		{
			$order = 'order by oid desc';
		}

		// 查询数量
		$total = $this->_dao->getTotal($where);
		if (empty($total))
		{
			return array();
		}

		// 查询结果
		return $this->_dao->order($order)->limit($start, $num)->getListWhere($where);
	}

	public function getSumByConf($conf, $field)
	{
		$where = $this->_getWhereByConf($conf);

		return $this->_dao->getSum($field, $where);
	}

	/*
	 * 获取单个供应商所有可开票的采购单
	 */
	public function getAllCanBillOrdersOfSupplier($sid)
    {
        $getByTime = date('Y-m-d H:i:s', time()-90*24*3600);
        $where = sprintf('sid=%d and status=%d and step=%d and payment_type=%d and invoice_ids="" and ctime>="%s"',
                $sid, Conf_Base::STATUS_NORMAL, Conf_In_Order::ORDER_STEP_RECEIVED, Conf_Stock::PAYMENT_MONEY_FIRST, $getByTime);
        
        return $this->_dao->limit(0,0)->getListWhere($where);
    }

    /**
     * 获取 所有的供应商总采购金额
     *
     *
     */
    public function getAllSupplierAmount()
    {
        $fields = array('sum(price) as amount', 'sid');
        $where = ' status=0 AND step=4 GROUP BY sid';
        $order = 'order by sid desc';
        $list = $this->one->select('t_in_order', $fields, $where, $order);
        return Tool_Array::list2Map($list['data'], 'sid');
    }

	private function _getWhereByConf($conf)
	{
		// 解析 conf 到 条件 $where
        if (isset($conf['status']) && $conf['status'] != Conf_Base::STATUS_ALL)
        {

            $where = sprintf('status="%d"', $conf['status']);
        }
        else
        {
            $where = 'status!=' . Conf_Base::STATUS_DELETED;
        }

		if ($this->is($conf['oid']))
		{
			$where .= sprintf(' and oid=%d', $conf['oid']);
		}
		if (!empty($conf['step']) && $conf['status']==Conf_Base::STATUS_NORMAL)
		{
			$where .= sprintf(' and step="%d"', $conf['step']);
		}
		if (!empty($conf['sid']))
		{
			$where .= sprintf(' and sid="%d"', $conf['sid']);
		}
		if (!empty($conf['from_date']))
		{
			$where .= sprintf(' and delivery_date>="%s"', mysql_escape_string($conf['from_date']));
		}
		if (!empty($conf['end_date']))
		{
			$where .= sprintf(' and delivery_date<="%s"', mysql_escape_string($conf['end_date']));
		}
		if (!empty($conf['buyer_uid']))
		{
			$where .= sprintf(' and buyer_uid="%d"', $conf['buyer_uid']);
		}
		if (!empty($conf['payment_type']))
		{
			$where .= sprintf(' and payment_type="%d"', $conf['payment_type']);
		}
        if (isset($conf['in_order_type']) && !empty($conf['in_order_type']))
        {
            $where .= sprintf(' and in_order_type="%d"', $conf['in_order_type']);
        }
        if (isset($conf['managing_mode']) && !empty($conf['managing_mode']) && $conf['managing_mode'] != Conf_Base::STATUS_ALL)
        {
            $where .= sprintf(' and managing_mode="%d"', $conf['managing_mode']);
        }
        if (isset($conf['is_timeout']) && $conf['is_timeout'] != Conf_Base::STATUS_ALL)
        {
            $where .= sprintf(' and is_timeout="%d"', $conf['is_timeout']);
        }
        if (isset($conf['wid']) && !$this->is($conf['oid']) && !$this->is($conf['sid']))
        {
            if (empty($conf['wid']))
            {
                $city = City_Api::getCity();
                $cityId = $city['city_id'];
                $where .= sprintf(' and wid in(%s)', implode(',', Conf_Warehouse::$WAREHOUSE_CITY[$cityId]));
            }
            else
            {
                if (is_array($conf['wid']))
                {
                    $where .= sprintf(' and wid in (%s)', implode(',', $conf['wid']));
                }
                else
                {
                    $where .= sprintf(' and wid=%d', $conf['wid']);
                }
            }
        }
        
        
		if (isset($conf['wid']) && !empty($conf['wid']))
		{
		    if (is_array($conf['wid']))
            {
                $str = implode(',', $conf['wid']);
                $where .= sprintf(' and wid in(%s)', $str);
            }
            else{
                $where .= sprintf(' and wid=%d', $conf['wid']);
            }
		}


		return $where;
	}
}
