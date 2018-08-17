<?php

/**
 * Sku相关业务逻辑
 */
class Shop_Often_Buy extends Base_Func
{
	private $_dao;

	public function __construct()
	{
		$this->_dao = new Data_Dao('t_user_often_buy');

		parent::__construct();
	}

	public function addRecord($cid, $uid, $sid, $pid, $cityId, $num)
	{
		$where = array('cid' => $cid, 'uid' => $uid, 'pid' => $pid);
		$total = $this->_dao->getTotal($where);
		if ($total <= 0) {
			$info = array(
				'cid' => $cid,
				'uid' => $uid,
				'sid' => $sid,
				'pid' => $pid,
				'city_id' => $cityId,
				'total' => $num,
			);

			return $this->_dao->add($info);
		} else {
			return $this->_dao->updateWhere($where, array(), array('total' => $num));
		}
	}

	public function getList($uid, $start, $num)
	{
		$where = sprintf('uid=%d AND total>0', $uid);
		$total = $this->_dao->getTotal($where);
		if ($total <= 0) {
			return array('total' => 0, 'list' => array());
		}

		$list = $this->_dao->limit($start, $num)->getListWhere($where);

		return array('total' => $total, 'list' => $list);
	}

	public function getListByPids($uid, $pids)
	{
		$where = array('uid' => $uid, 'pid' => $pids);

		return $this->_dao->getListWhere($where);
	}
}
