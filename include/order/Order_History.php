<?php
/**
 * 订单的事件历史
 */
class Order_History extends Base_Func
{

	public function add($oid, $type, $note='')
	{
		$oid = intval($oid);
		$type = intval($type);
		assert( $oid > 0 );

		$now = date('Y-m-d H:i:s');
		$info = array('oid'=> $oid,
			'type'=> $type,
			'note'=> $note,
			'ctime' => $now,
			'mtime' => $now,
		);
		$res = $this->one->insert('t_order_history', $info);
		return $res['affectedrows'];
	}

	public function update($id, array $info)
	{
		$id = intval($id);
		assert( $id > 0 );
		assert( !empty($info) );

		$where = array('id' => $id);
		$ret = $this->one->update('t_order_history', $info, array(), $where);
		return $ret['affectedrows'];
	}

	public function getHistoryOfOrder($oid)
	{
		$oid = intval($oid);
		assert( $oid > 0 );

		$where = array('oid' => $oid);
		$data = $this->one->select('t_order_history', array('*'), $where);
		if (empty($data['data']))
		{
			return array();
		}

		return $data['data'];
	}

}
