<?php

/**
 * 商品相关业务
 */
class Warehouse_Stock_History extends Base_Func
{
	private $_dao;

	public function __construct()
	{
		$this->_dao = new Data_Dao('t_stock_history');

		parent::__construct();
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

	public function update($wid, $sid, array $info)
	{
		$sid = intval($sid);
		$wid = intval($wid);
		assert($sid > 0);
		assert($wid > 0);

		$where = array('sid' => $sid, 'wid' => $wid);
		return $this->_dao->updateWhere($where, $info);
	}

	public function updateById($id, $info, $change = array())
	{
		return $this->_dao->update($id, $info, $change);
	}

	public function getHistoryList($where, $fields = array('*'), $order = '', $start = 0, $num = 20)
	{
		$total = $this->_dao->getTotal($where);
		if ($total == 0)
		{
			return array('total' => 0, 'data' => array());
		}

		if (empty($order))
		{
			$order = 'order by id desc';
		}
		$list = $this->_dao->setFields($fields)->order($order)->limit($start, $num)->getListWhere($where);

		return array('total' => $total, 'data' => $list);
	}

	public function getHistoryBetween($from, $end, $wid=0)
	{
		$wid = intval($wid);

		$where = sprintf('ctime>"%s" and ctime<"%s" and status=0',
			date("Y-m-d 00:00:00", strtotime($from)), date("Y-m-d 00:00:00", strtotime($end)));
		if ($wid)
		{
			$where .= sprintf(' and wid=%d', $wid);
		}

		$list = $this->_dao->getListWhere($where);
		return $list;
	}


	public function getLatestStockHistoryOf($date, $wid, $sid)
	{
		//获取一个月的history缓存
		static $lastHistoryMap;
		if (empty($lastHistoryMap[$date]))
		{
			$lastHistoryMap[$date] = array();
			$from = date('Y-m-d', strtotime($date)-86400*30);
			$historyList = $this->getHistoryBetween($from, $date);
			foreach ($historyList as $item)
			{
				$_sid = $item['sid'];
				$_wid = $item['wid'];
				if (empty($lastHistoryMap[$date][$_sid][$_wid])
					|| $lastHistoryMap[$date][$_sid][$_wid]['ctime']<$item['ctime'])
				{
					$lastHistoryMap[$date][$_sid][$_wid] = $item;
				}
			}
		}

		if (empty($lastHistoryMap[$date][$sid][$wid]))
		{
			$where = sprintf('sid=%d and wid=%d and status=0 and ctime<="%s"', $sid, $wid, date('Y-m-d 23:59:59', strtotime($date)));
			$order = 'order by ctime desc';
			$res = $this->getHistoryList($where, array('*'), $order, 0, 1);
			if (!empty($res['data']))
			{
				$lastHistoryMap[$date][$sid][$wid] = array_shift($res['data']);
			}
		}

		return (array)$lastHistoryMap[$date][$sid][$wid];
	}
}
