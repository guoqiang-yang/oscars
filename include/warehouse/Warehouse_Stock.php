<?php

/**
 * 商品相关业务
 */
class Warehouse_Stock extends Base_Func
{
	private $_dao;

	public function __construct()
	{
		$this->_dao = new Data_Dao('t_stock');

		parent::__construct();
	}

	public function save($wid, $sid, array $info, array $change = array())
	{
		assert(!empty($wid));
		assert(!empty($sid));

		$ret = $this->get($wid, $sid);
		if (empty($ret))
		{
			if (empty($info))
			{
				$info = $change;
			}

			$this->add($wid, $sid, $info);
		}
		else
		{
			$this->update($wid, $sid, $info, $change);
		}

		return TRUE;
	}

	public function add($wid, $sid, array $info)
	{
		assert(!empty($wid));
		assert(!empty($sid));
		$info['wid'] = $wid;
		$info['sid'] = $sid;

		return $this->_dao->add($info);
	}

	public function delete($wid, $sid)
	{
		$sid = intval($sid);
		$wid = intval($wid);
		assert($sid > 0);
		assert($wid > 0);

		$where = array('sid' => $sid, 'wid' => $wid);

		return $this->_dao->deleteWhere($where);
	}

	public function update($wid, $sid, array $info, array $change=array())
	{
		$sid = intval($sid);
		$wid = intval($wid);
		assert($sid > 0);
		assert($wid > 0);
        assert(!empty($info)||!empty($change));

		$where = array('sid' => $sid, 'wid' => $wid);

		return $this->_dao->updateWhere($where, $info, $change);
	}

	public function get($wid, $sid)
	{
		$sid = intval($sid);
		$wid = intval($wid);
		assert($sid > 0);
		assert($wid > 0);

		$where = array('sid' => $sid, 'wid' => $wid);
		$list = $this->_dao->getListWhere($where);
		if (empty($list))
		{
			return array();
		}

		return array_shift($list);
	}

	// 取商品对应的全部库存
	public function getAll($sid)
	{
		assert($sid > 0);
		$where = array('sid' => $sid);

		return $this->_dao->getListWhere($where);
	}

    // 取商品对应的全部库存
    public function getAllBySids($sids)
    {
        assert(!empty($sids));
        $where = 'sid IN('.implode(',',$sids).')';

        return $this->_dao->getListWhere($where);
    }

	public function getBulk($wid, array $sids, $fields=array('*'))
	{
		if (empty($sids)) return array();

		$where['sid'] = $sids;
		if (!empty($wid))
		{
			$where['wid'] = $wid;
		}

		return $this->_dao->setFields($fields)->getListWhere($where);
	}

	public function appendStock($wid, &$list, $filed = 'sid')
	{
		$sids = Tool_Array::getFields($list, $filed);
		if (empty($sids))
		{
			return;
		}

		$stockList = $this->getBulk($wid, $sids);
		$stockList = Tool_Array::list2Map($stockList, 'sid');
		foreach ($list as &$item)
		{
			$sid = $item[$filed];
			if (!empty($stockList[$sid]))
			{
				$item['_stock'] = $stockList[$sid];
			}
		}
	}

    /**
     * 获取总数.
     */
    public function getTotalByWhere($where)
    {
        $where = !empty($where) ? $where : 'status='. Conf_Base::STATUS_NORMAL;
        
        return $this->_dao->getTotal($where);
    }
    
    /**
     * 获取库存列表.
     */
    public function getListByWhere($where, $field=array('*'), $start=0, $num=20)
    {
        $where = !empty($where) ? $where : 'status='. Conf_Base::STATUS_NORMAL;
        
        $order = 'order by sid';
        
        $data = $this->_dao->setFields($field)
                           ->order($order)
                           ->limit($start, $num)
                           ->getListWhere($where);
        
        return $data;
    }
    
	public function getList($where, $fields = array('*'), $order = '', $start = 0, $num = 20)
	{
		$where = !empty($where) ? $where : '1=1';
		//$where .= sprintf(' and alert_threshold>=0 and status=%d', Conf_Base::STATUS_NORMAL);
        $where .= sprintf(' and status=%d', Conf_Base::STATUS_NORMAL);

		//查询数量
		$total = $this->_dao->getTotal($where);
		if (empty($total))
		{
			return array('total' => 0, 'data' => array());
		}

		//查询结果 list
		if (empty($order))
		{
			$order = 'order by sid';
		}
		if (empty($fields) || !is_array($fields))
		{
			$fields = array('*');
		}

		$list = $this->_dao->order($order)->setFields($fields)->limit($start, $num)->getListWhere($where);

		return array('total' => $total, 'data' => $list);
	}

	// 取商品对应的全部库存
	public function getAllStock($fields = array('*'))
	{
		$where = array('status' => Conf_Base::STATUS_NORMAL);
		$data = $this->_dao->setFields($fields)->getListWhere($where);
		$retMap = array();
		foreach ($data as $item)
		{
			$wid = $item['wid'];
			$sid = $item['sid'];
			$retMap[$wid][$sid] = $item;
		}

		return $retMap;
	}

    /**
     * 取大库库存和货位库存不一致的列表.
     * 
     * @param int $wid
     */
    public function getDiffListStock2Location($wid)
    {
        $kind = 't_sku as sk, t_stock as s, '.
                '(select sid, sum(num) as loc_num from t_sku_2_location where status=0 and location not like "'.Conf_Warehouse::VFLAG_PREFIX.'%"'. 
                ' and wid='.$wid.' group by sid ) as ll';
        $fields = array('s.sid', 's.num', 'll.loc_num', 'sk.title', 'sk.alias', 'sk.cate1', 'sk.cate2', 'sk.unit');
        $where = 's.status=0 and s.wid='.$wid.' and s.sid=ll.sid and s.num != ll.loc_num and s.sid =sk.sid';
        
        $ret = $this->one->setDBMode()->select($kind, $fields, $where);
        
        return $ret['data'];
    }
    
    public function getByWhere($where, $field=array('*'), $start=0, $num=20 , $order='')
    {
        assert(!empty($where));
        
        $order = !empty($order)? $order: 'order by sid';
        $ret = $this->_dao->setFields($field)
                    ->order($order)
                    ->limit($start, $num)
                    ->getListWhere($where);
        
        return $ret;
    }
}
