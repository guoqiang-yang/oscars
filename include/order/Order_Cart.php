<?php
/**
 * 购物车相关业务逻辑
 */
class Order_Cart extends Base_Func
{
	public function add($uid, $childId, $words, $voiceId, array $picIds, $userRelation, $userTitle)
	{
		if (!empty($picIds))
		{
			$picIds = array_values(array_filter(array_map('intval', $picIds)));
		}
		$picIdStr = implode(',', $picIds);

		$now = date('Y-m-d H:i:s');
		$arr = array('uid' => $uid, 'child_id' => $childId, 'words' => $words,
					'voice' => $voiceId, 'pictures' => $picIdStr,
					'user_title' => $userTitle, 'user_relation' => $userRelation,
					'created_time' => $now, 'updated_time' => $now);
		$ret = $this->one->insert('m_tree_feed', $arr);
		$arr['id'] = $ret['insertid'];
		return $arr;
	}

	public function delete($feedId)
	{
		$where = array('id' => $feedId);
		$update = array('deleted' => 1);
		$ret = $this->one->update('m_tree_feed', $update, array(), $where);
		return $ret['affectedrows'];
	}

	public function get($id)
	{
		$where = array('id' => $id);
		$data = $this->one->select('m_tree_feed', array('*'), $where);
		if (empty($data['data']))
		{
			return array();
		}

		return $data['data'][0];
	}

	public function getFeedsByIds($ids)
	{
		$where = array('id' => $ids);
		$data = $this->one->select('m_tree_feed', array('*'), $where);
		if (empty($data['data']))
		{
			return array();
		}

		return $data['data'];
	}
}
