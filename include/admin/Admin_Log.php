<?php
/**
 * 管理员操作日志
 */
class Admin_Log extends Base_Func
{
	public function add(array $info)
	{
		assert( !empty($info) );

		$res = $this->one->insert('t_admin_log', $info);
		return $res['insertid'];
	}

	public function get($suid)
	{
		$suid = intval($suid);
		assert($suid > 0);

		//$where = array('suid' => $suid);
        $where = "suid=$suid or leader_suid=$suid";
		$data = $this->one->select('t_staff_user', array('*'), $where);
		if (empty($data['data']))
		{
			return array();
		}
        
        $userInfo = array();
        $teamMember = array();
        foreach($data['data'] as $onerInfo)
        {
            if ($onerInfo['suid'] == $suid)
            {
                $userInfo = $onerInfo;
            } 
            $teamMember[] = intval($onerInfo['suid']);
        }
        
        $userInfo['team_member'] = $teamMember;
        
		return $userInfo;
	}

	public function getList($searchConf, &$total, $start=0, $num=20)
	{
		$where = ' 1=1 ';
		if (!empty($searchConf['admin_id']))
		{
			$where .= sprintf(' AND admin_id="%d"', $searchConf['admin_id']);
		}
		if (!empty($searchConf['action_type']))
		{
			$where .= sprintf(' AND action_type="%d"', $searchConf['action_type']);
		}
		if (!empty($searchConf['start_time']))
		{
			$where .= sprintf(' AND mtime>="%s"', $searchConf['start_time']);
		}
		if (!empty($searchConf['end_time']))
		{
			$where .= sprintf(' AND mtime<="%s"', $searchConf['end_time']);
		}

		// 查询数量
		$data = $this->one->select('t_admin_log', array('count(1)'), $where);
		$total = intval($data['data'][0]['count(1)']);
		if (empty($total))
		{
			return array();
		}

		// 查询结果
		$order = 'order by lid desc';
		$data = $this->one->select('t_admin_log', array('*'), $where, $order, $start, $num);
		if (empty($data['data']))
		{
			return array();
		}

		return $data['data'];
	}
}
