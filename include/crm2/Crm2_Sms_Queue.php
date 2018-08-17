<?php
/**
 * 短信
 */
class Crm2_Sms_Queue extends Base_Func
{
	public function add(array $info)
	{
		assert( !empty($info) );

		$info['ctime'] = $info['mtime'] = date('Y-m-d H:i:s');
		$res = $this->one->insert('t_sms_queue', $info);
		$uid = $res['insertid'];
		return $uid;
	}

	public function delete($id)
	{
		$id = intval($id);
		assert($id > 0);

		$where = array('id' => $id);
		$update = array('status' => Conf_Base::STATUS_DELETED);
		$ret = $this->one->update('t_sms_queue', $update, array(), $where);
		return $ret['affectedrows'];
	}

	public function update($id, array $info)
	{
		$id = intval($id);
		assert( $id > 0 );
		assert( !empty($info) );

		$where = array('id' => $id);
		$ret = $this->one->update('t_sms_queue', $info, array(), $where);
		return $ret['affectedrows'];
	}

	public function get($id)
	{
		$id = intval($id);
		assert($id > 0);

		$where = array('id' => $id);
		$data = $this->one->select('t_sms_queue', array('*'), $where);
		if (empty($data['data']))
		{
			return array();
		}

		return $data['data'][0];
	}

	public function getList($sent=0)
	{
		$where = array( 'status'=>0, 'sent' => $sent ? 1:0 );
		$data = $this->one->select('t_sms_queue', array('*'), $where);
		if (empty($data['data']))
		{
			return array();
		}

		return $data['data'];
	}
}
