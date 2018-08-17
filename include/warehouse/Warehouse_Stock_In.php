<?php

/**
 * 订单信息相关业务逻辑
 */
class Warehouse_Stock_In extends Base_Func
{
	private $_dao;

	public function __construct()
	{
		$this->_dao = new Data_Dao('t_stock_in');

		parent::__construct();
	}


	public function add(array $info)
	{
		return $this->_dao->add($info);
	}

	public function delete($id)
	{
		return $this->_dao->delete($id);
	}

	public function update($id, array $info, $change = array())
	{
		return $this->_dao->update($id, $info, $change);
	}

	public function updateByOid($oid, array $info)
	{
		$where = array('oid' => $oid, 'status'=>Conf_Base::STATUS_NORMAL);

		return $this->_dao->updateWhere($where, $info);
	}

	public function get($id)
	{
		return $this->_dao->get($id);
	}

	public function getBulk(array $ids)
	{
		return $this->_dao->getList($ids);
	}

	public function getList(array $conf, &$total, $order, $start = 0, $num = 20)
	{
		if (empty($order))
		{
			$order = 'order by id desc';
		}

		$where = $this->_getWhereByConf($conf);

		// 查询数量
		$total = $this->_dao->getTotal($where);
		if (empty($total))
		{
			return array();
		}

		// 查询结果
		return $this->_dao->order($order)->limit($start, $num)->getListWhere($where);
	}

	public function getTotalByWhere($where)
    {
        return $this->_dao->getTotal($where);
    }

	public function getListByWhere($where, &$total, $start = 0, $num = 20)
	{
		// 查询数量
		$total = $this->_dao->getTotal($where);
		if (empty($total))
		{
			return array();
		}

		// 查询结果
		return $this->_dao->limit($start, $num)->getListWhere($where);
	}

    // 财务兑账列表
    public function getListForFinance($conf, $start=0, $num=20, $order='')
    {
        if (empty($order))
		{
			$order = 'order by id desc';
		}
        
        $where = $this->_getWhereByConf($conf);
        
        $total = $this->_dao->getTotal($where);
        
        $sumPrice = 0;
        if(!empty($conf['sid']))
        {
            $sumPrice = $this->_dao->getSum('price', $where);
        }
        
        $stockinRet = array();
        if (!empty($total))
        {
            $stockinRet = $this->_dao->order($order)->limit($start, $num)->getListWhere($where);
            
            // 查询综合采购单的所有入库单
            $inorderIds = Tool_Array::getFields($stockinRet, 'oid');
            $_where = sprintf('status=0 and oid in (select oid from t_in_order where status=0 and source=%d and oid in (%s) )',
                                Conf_In_Order::SRC_COMPOSITIVE, implode(',', $inorderIds));
            $stockIn4Compositive = $this->_dao->getListWhere($_where);
           
            if (!empty($stockIn4Compositive))
            {
                foreach($stockIn4Compositive as $stockinId => $info)
                {
                    if (!array_key_exists($stockinId, $stockinRet))
                    {
                        $stockinRet[$stockinId] = $info;
                    }
                }
            }
        }
        
        return array('total'=>$total, 'data'=>$stockinRet, 'sum_price'=>$sumPrice);
    }
    
    
	public function getOrdersOfSupplier($sid, &$total, $order = '', $start = 0, $num = 20)
	{
		$sid = intval($sid);
		assert($sid > 0);

		$where = sprintf('sid=%d and status=%d', $sid, Conf_Base::STATUS_NORMAL);
		if (empty($order))
		{
			$order = 'order by id desc';
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
    
    public function getSupplierOrderByConf($sid, $conf, $field=array('*'), $order='', $start=0, $num=0)
    {
        assert($sid>0);
        
        $where = sprintf('sid=%d and status=%d', $sid, Conf_Base::STATUS_NORMAL);
        
        if ($this->is($conf['btime']))
        {
            $where .= sprintf(' and ctime >= "%s 00:00:00"', $conf['btime']);
        }
        if ($this->is($conf['etime']))
        {
            $where .= sprintf(' and ctime <= "%s 23:59:59"', $conf['etime']);
        }
        if ($this->is($conf['paid']))
        {
            $where .= ' and paid='. $conf['paid'];
        }
        if ($this->is($conf['statement_id']))
        {
            $where .= ' and statement_id='.$conf['statement_id'];
        }

        if ($this->is($conf['id']))
        {
            $where .= ' and id='.$conf['id'];
        }
        
        $order = !empty($order)? $order: 'order by id desc';
        
        $ret = array('total'=>0, 'data'=>array());
        if (!empty($num))
        {
            $ret['total'] = $this->_dao->getTotal($where);
        }
        
        $ret['data'] = $this->_dao->setFields($field)
                ->order($order)->limit($start, $num)->getListWhere($where);
        
        return $ret;
    }


	public function getListOfOrder($oid, $order = '')
	{
		assert(!empty($oid));

		$where['status'] = Conf_Base::STATUS_NORMAL;
		$where['oid'] = $oid;

		if (empty($order))
		{
			$order = 'order by oid desc';
		}

		return $this->_dao->order($order)->getListWhere($where);
	}

	public function getSumByConf($conf, $field)
	{
		$where = $this->_getWhereByConf($conf);

		return $this->_dao->getSum($field, $where);
	}

	private function _getWhereByConf($conf)
	{
		// 解析 conf 到 条件 $where
		$where = 'status=' . Conf_Base::STATUS_NORMAL;

		if ($this->is($conf['id']))
		{
			$where .= sprintf(' and id=%d', $conf['id']);
		}
		if (isset($conf['step']) && !empty($conf['step']))
		{
            if ($conf['step'] == Conf_Stock_In::STEP_PART_SHELVED)  //=2 查询部分上架，和入库未上架
            {
                $where .= sprintf(' and step in (%d, %d)', 
                        Conf_Stock_In::STEP_STOCKIN, Conf_Stock_In::STEP_PART_SHELVED);
            }
            else
            {
                $where .= sprintf(' and step=%d', $conf['step']);
            }
            $where .= ' and source!='. Conf_In_Order::SRC_TEMPORARY;
		}
		if (!empty($conf['sid']))
		{
			$where .= sprintf(' and sid="%d"', $conf['sid']);
		}
		if (!empty($conf['oid']))
		{
			$where .= sprintf(' and oid="%d"', $conf['oid']);
		}
		if (!empty($conf['buyer_uid']))
		{
			$where .= sprintf(' and buyer_uid="%d"', $conf['buyer_uid']);
		}
		if (!empty($conf['payment_type']))
		{
			$where .= sprintf(' and payment_type="%d"', $conf['payment_type']);
		}
		if (isset($conf['wid']) && !empty($conf['wid']))
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
		if (isset($conf['paid']) && $conf['paid'] != Conf_Base::STATUS_ALL)
		{
			$where .= sprintf(' and paid=%d', $conf['paid']);
		}
		if (!empty($conf['stime']))
		{
			$where .= sprintf(' AND ctime>="%s"', $conf['stime']);
		}
		if (!empty($conf['etime']))
		{
			$where .= sprintf(' AND ctime<="%s"', $conf['etime']);
		}

		if (!empty($conf['statement_id']))
        {
            $where .= sprintf(' AND statement_id="%d"', $conf['statement_id']);
        }

        if (!empty($conf['source']))
        {
            $where .= sprintf(' AND source="%d"', $conf['source']);
        }

		return $where;
	}

}
