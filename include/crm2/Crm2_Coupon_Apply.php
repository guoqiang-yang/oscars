<?php
/**
 * 优惠券
 */
class Crm2_Coupon_Apply extends Base_Func
{
	const TABLE = 't_coupon_apply';

	const STATUS_WAIT = 1,
		STATUS_DELETED = 2,
		STATUS_REJECTED = 3,
		STATUS_PASSED = 4;
	
	public function add(array $info)
	{
		assert( !empty($info) );
		assert( isset($info['cid']) );
		assert( isset($info['sales_suid']) );

		if (empty($info['note'])) $info['note'] = '';
		$info['ctime'] = $info['mtime'] = date('Y-m-d H:i:s');
		$res = $this->one->insert(self::TABLE, $info);
		$uid = $res['insertid'];
		return $uid;
	}

	public function delete($id)
	{
		$id = intval($id);
		assert($id > 0);

		$where = array('id' => $id);
		$update = array('status' => self::STATUS_DELETED);
		$ret = $this->one->update(self::TABLE, $update, array(), $where);
		return $ret['affectedrows'];
	}

	public function update($id, array $info)
	{
		assert( !empty($id) );
		assert( !empty($info) );

		$where = array('id' => $id);
		$ret = $this->one->update(self::TABLE, $info, array(), $where);
		return $ret['affectedrows'];
	}

	public function get($id)
	{
		$id = intval($id);
		assert($id > 0);

		$where = array('id' => $id);
		$data = $this->one->select(self::TABLE, array('*'), $where);
		if (empty($data['data']))
		{
			return array();
		}

		return $data['data'][0];
	}

	public function getList($suid, $status, &$total, $start=0, $num=20)
	{
		$where = array();
		if ($suid)
		{
			$where['sales_suid'] = $suid;
		}
		if ($status)
		{
			$where['status'] = $status;
		}

		// 查询数量
		$data = $this->one->select(self::TABLE, array('count(1)'), $where);
		$total = intval($data['data'][0]['count(1)']);
		if (empty($total))
		{
			return array();
		}

		// 查询结果
		$order = 'order by id desc';
		$data = $this->one->select(self::TABLE, array('*'), $where, $order, $start, $num);
		if (empty($data['data']))
		{
			return array();
		}

		return $data['data'];
	}

}
