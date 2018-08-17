<?php

/**
 * 小区费用，距离.
 */

class Order_Community_Fee extends Base_Func
{
	private $_dao;


	public function __construct()
	{
		$this->_dao = new Data_Dao('t_community_distance_fee');
	}

	public function add($info)
	{
		return $this->_dao->add($info);
	}

	public function getCommunityInfo($cmid, $wid)
	{
		$distance = 0;
		$feeList = array();
		$status = Conf_Base::STATUS_NORMAL;
		$note = '';

		$where = sprintf(' cmid=%d AND (status=%d OR status=%d) AND wid=%d', $cmid, Conf_Base::STATUS_NORMAL, Conf_Base::STATUS_WAIT_AUDIT, $wid);
		$list = $this->_dao->getListWhere($where);
		if (!empty($list))
		{
			foreach ($list as $item)
			{
				if ($item['car_model'] == 0)
				{
					$distance = $item['distance'];
					$status = $item['status'];
					$note = $item['note'];
				}
				else
				{
					$feeList[] = $item;
				}
			}
		}

		return array('distance' => $distance, 'fee_list' => $feeList, 'status' => $status, 'note' => $note);
	}

	public function updateWhere($where, $info, $change = array())
	{
		return $this->_dao->updateWhere($where, $info, $change);
	}

	public function update($id, $info, $change = array())
	{
		return $this->_dao->update($id, $info, $change);
	}

	public function getListWhere($where)
	{
		return $this->_dao->getListWhere($where, 0, 0);
	}
}