<?php
/**
 * 返现log
 */
class Crm2_Cumulative_Log extends Base_Func
{
	private $_dao;

	public function __construct()
	{
		$this->_dao = new Data_Dao('t_cumulative_log');
		parent::__construct();
	}

	public function add(array $info)
	{
		return $this->_dao->add($info);
	}

	public function getByCids($cids)
	{
		$where = array('cid' => $cids);

		return $this->_dao->getListWhere($where);
	}

	public function getList($searchConf, $start = 0, $num = 20)
	{
		$where = ' 1=1 ';
		if (!empty($searchConf['cid']))
		{
			$where .= sprintf(' AND cid=%d', $searchConf['cid']);
		}
		if (!empty($searchConf['type']))
		{
			$where .= sprintf(' AND type=%d', $searchConf['type']);
		}

		$total = $this->_dao->getTotal($where);
		if ($total <= 0)
		{
			return array('total' => 0, 'list' => array());
		}

		$list = $this->_dao->limit($start, $num)->getListWhere($where);

		return array('total' => $total, 'list' => $list);
	}

	public function getTotal($searchConf)
	{
		$where = ' 1=1 ';
		if (!empty($searchConf['cid']))
		{
			$where .= sprintf(' AND cid=%d', $searchConf['cid']);
		}
		if (!empty($searchConf['type']))
		{
			$where .= sprintf(' AND type=%d', $searchConf['type']);
		}

		return $this->_dao->getTotal($where);
	}

	public function getAll()
	{
		return $this->_dao->getAll();
	}
}
