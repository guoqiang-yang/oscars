<?php

class Finance_Customer_Amount extends Base_Func
{
	private $_dao;

	public function __construct()
	{
		$this->_dao = new Data_Dao('t_customer_amount_history');

		parent::__construct();
	}


	public function save($data)
	{
		assert(!empty($data));
		assert(!empty($data['cid']));
		assert(!empty($data['type']));
		assert(!empty($data['suid']));

		// 获取当前用户的账面余额
		$bill = $this->getRecentOfUser($data['cid']);
		$data['amount'] = !empty($bill) ? intval(strval($bill['amount'])) + intval(strval($data['price'])) : $data['price'];
        
        // 写入city_id: [Rule] 如果data['city_id']为空，取客户的信息中的city_id; 否则使用data['city_id']
        if (empty($data['city_id']))
        {
            $cc = new Crm2_Customer();
            $customer = $cc->get($data['cid']);
            $data['city_id'] = $customer['city_id'];
        }
        
		// 余额不能小于 0
		assert($data['amount'] >= 0);

		$data['note'] = isset($data['note']) ? $data['note'] : '';
		$id = $this->_dao->add($data);

		return array('id' => $id, 'data' => $data);
	}

	public function getTotalByWhere($where)
	{
		return $this->_dao->getTotal($where);
	}

	public function updateByWhere($where, $info, $change = array())
	{
		return $this->_dao->updateWhere($where, $info, $change);
	}

	public function getList($search, $start = 0, $num = 20, $order = '')
	{
		$where = $this->_getWhereBySearch($search);
		$_order = !empty($order) ? $order : 'order by id desc';
        
		$total = $this->_dao->getTotal($where);

		$data = array();
		if ($total > 0)
		{
			$data = $this->_dao->order($_order)->limit($start, $num)->getListWhere($where);
		}

		return array('total' => $total, 'data' => $data);
	}

    /**
     * 查询list，without Total.
     */
    public function getListByWhere($where, $start=0, $num=20, $field=array('*'), $order='')
    {
        $where = is_array($where)? $this->_getWhereBySearch($where): $where;
		$_order = !empty($order) ? $order : 'order by id desc';
        
		return $this->_dao->setFields($field)
                          ->order($_order)
                          ->limit($start, $num)
                          ->getListWhere($where, false);
    }


    /**
	 * 取最近的一条记录.
	 * @param $cid
	 * @return array
	 */
	public function getRecentOfUser($cid)
	{
		$where = 'cid=' . $cid . ' and status=' . Conf_Base::STATUS_NORMAL;
		$order = 'order by id desc';

		$list = $this->_dao->order($order)->limit(0, 1)->getListWhere($where);

		$data = array();
		if (!empty($list))
		{
			$data = array_shift($list);
		}

		return $data;
	}


	public function openGet($where, $field = array('*'), $order = '', $start = 0, $num = 0)
	{
		$total = 0;
		if ($num)
		{
			$total = $this->_dao->getTotal($where);
		}

		$_order = !empty($order) ? $order : 'order by id desc';
		$data = $this->_dao->order($_order)->limit($start, $num)->setFields($field)->getListWhere($where, false);

		return array('total' => $total, 'data' => $data);
	}

	private function _getWhereBySearch($search)
	{
		$where = 'status=' . Conf_Base::STATUS_NORMAL;

        if ($this->is($search['objid']))
        {
            $where .= ' and objid =' . $search['objid'];
        }
		if ($this->is($search['cid']))
		{
			$where .= ' and cid=' . $search['cid'];
		}
		if ($this->is($search['type']))
		{
			$where .= ' and type=' . $search['type'];
		}
        if ($this->is($search['saler_suid']))
        {
            $where .= ' and saler_suid='. $search['saler_suid'];
        }
        
        if ($this->is($search['btime']))
        {
            $where .= ' and date(ctime) >= date("'.$search['btime'].' ")';
        }
        if ($this->is($search['etime']))
        {
            $where .= ' and date(ctime) <= date("'.$search['etime'].' ")';
        }
        
		return $where;
	}
    /**
     * 取最近的一条记录.
     * @return array
     */
    public function  getRecentOfBalance0($cid)
    {
        $where = 'cid=' . $cid .' and '.'amount=' . 0 . ' and status=' . Conf_Base::STATUS_NORMAL;
        $order = 'order by id desc';

        $list = $this->_dao->order($order)->limit(0, 1)->getListWhere($where);

        $data = array();
        if (!empty($list))
        {
            $data = array_shift($list);
        }

        return $data;
    }

}