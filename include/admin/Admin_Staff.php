<?php
/**
 * 管理后台 - 员工相关
 */
class Admin_Staff extends Base_Func
{
	public function add(array $info)
	{
		assert( !empty($info) );

		$info['ctime'] = $info['mtime'] = date('Y-m-d H:i:s');
        $info['wids'] = !empty($info['wids'])? $info['wids']: '';
        $info['cities'] = !empty($info['cities'])? $info['cities']: '';
        
		$res = $this->one->insert('t_staff_user', $info);
		$suid = $res['insertid'];
		return $suid;
	}

	public function delete($suid)
	{
		$suid = intval($suid);
		assert($suid > 0);

		$where = array('suid' => $suid);
		$update = array('status' => Conf_Base::STATUS_DELETED);
		$ret = $this->one->update('t_staff_user', $update, array(), $where);
		return $ret['affectedrows'];
	}

	public function update($suid, array $info)
	{
		$suid = intval($suid);
		assert( $suid > 0 );
		assert( !empty($info) );

		$where = array('suid' => $suid);
		$ret = $this->one->update('t_staff_user', $info, array(), $where);
		return $ret['affectedrows'];
	}

	public function get($suid, $status=Conf_Base::STATUS_NORMAL)
	{
		$suid = intval($suid);
		assert($suid > 0);

        $where = 'suid='. $suid;
        if ($status != Conf_Base::STATUS_ALL)
        {
            $where .= ' and status = '. $status;
        }
        
        $data = $this->one->select('t_staff_user', array('*'), $where);
        $userInfo = $data['data'][0];
        
        if (!empty($userInfo))
        {
            $userInfo['team_member'] = array($suid);    //兼容之前数据结构
            $userInfo = $this->_parseCitiesAndWids($userInfo);
        }
        
        return $userInfo;
        
//        $where = "(suid=$suid or leader_suid=$suid)";
//        if ($status != Conf_Base::STATUS_ALL)
//        {
//            $where .= ' and status = '. $status;
//        }
//		$data = $this->one->select('t_staff_user', array('*'), $where);
//		if (empty($data['data']))
//		{
//			return array();
//		}
//        
//        $userInfo = array();
//        $teamMember = array();
//        foreach($data['data'] as $onerInfo)
//        {
//            if ($onerInfo['suid'] == $suid)
//            {
//                $userInfo = $onerInfo;
//            } 
//            $teamMember[] = intval($onerInfo['suid']);
//        }
//        
//        $userInfo['team_member'] = $teamMember;
//        $userInfo = $this->_parseCitiesAndWids($userInfo);
//
//		return $userInfo;
	}

	public function getByMobile($mobile)
	{
		$mobile = strval($mobile);
		assert(!empty($mobile));

		$where = array('mobile' => $mobile);
		$data = $this->one->select('t_staff_user', array('*'), $where);

		if (empty($data['data']))
		{
			return array();
		}
		return $data['data'][0];
	}

    public function getByCityAndRole($city, $role)
    {
        $city = intval($city);
        assert(!empty($city));

        $pr = new Permission_Role();
        $roleInfo = $pr->getByKeys($role);
        $roleId = $roleInfo[$role]['id'];

        $where = sprintf(' status=0 and city_id=%d AND find_in_set("%s", roles)', $city, $roleId);
        $data = $this->one->select('t_staff_user', array('*'), $where);
        if (empty($data['data']))
        {
            return array();
        }

        return $data['data'];
    }

    public function getByCityAndRoles($city, $roles)
    {
        $city = intval($city);
        assert(!empty($city));

        $pr = new Permission_Role();
        $roleList = $pr->getByKeys($roles);

        $where = sprintf(' status=0 and city_id=%d', $city);
        if(!empty($roleList))
        {
            $role_ids = Tool_Array::getFields($roleList, 'id');
            $_arr = array();
            foreach ($role_ids as $id)
            {
                $_arr[] = sprintf('find_in_set("%s", roles)', $id);
            }
            $where .= sprintf(' and (%s)', implode(' OR ', $_arr));
        }
        $data = $this->one->select('t_staff_user', array('*'), $where);
        if (empty($data['data']))
        {
            return array();
        }

        return $data['data'];
    }

	public function getUsers(array $uids, $field=array('*'))
	{
		assert(!empty($uids));

		$where = array('suid' => $uids);
		$data = $this->one->select('t_staff_user', $field, $where);
		if (empty($data['data']))
		{
			return array();
		}

		return $data['data'];
	}

	public function getByWhere($where, $start=0, $num=0, $field=array('*'), $order='')
    {
        assert(!empty($where));
        $data = $this->one->select('t_staff_user', $field, $where);

        return $data['data'];
    }

    // 通过角色获取人员（new）
    public function getByRole($role, $status=false, $kind=0, $cityId=0)
    {
        $where = '1=1';
        $where .= ($status !== false)? ' and status='. $status: '';
		$where .= (!empty($kind)&&is_numeric($kind))? ' and kind='.$kind :
                    (!empty($kind)&&is_array($kind)? ' and kind in ('. implode(',', $kind). ')': '');
        $where .= !empty($cityId)? ' and city_id='. $cityId: '';
        
        $_staffs = $this->one->select('t_staff_user', array('*'), $where);
        $staffList = $_staffs['data'];
        
        if (empty($staffList)) return array();
        
        // 角色
        $roleInfos = Permission_Api::getRolesByRkey($role, array('id', 'role', 'rkey'));
        $alloRoleIds = Tool_Array::getFields($roleInfos, 'id');

        foreach($staffList as $index => $info)
        {
            $staffRoleIds = explode(',', $info['roles']);
            $comRoles = array_intersect($staffRoleIds, $alloRoleIds);
            
            if (empty($comRoles))
            {
                unset($staffList[$index]);
            }
        }
        
        return $staffList;
    }
    
    // 方法下线 addby: guoqiang/20170927
	public function getByRole_Del_($role, $status=false, $kind=0, $cityId=0)
	{
		$where = '1=1';
		if (false !== $status)
		{
			$where .= sprintf(' and status=%d', $status);
		}
		if ($kind && is_numeric($kind))
		{
			$where .= sprintf(' and kind=%d', $kind);
		}else if (!empty($kind) && is_array($kind))
		{
			$where .= sprintf(' and kind in (%s)', implode(',',$kind));
		}
		if ($cityId)
		{
			$where .= sprintf(' and city_id=%d', $cityId);
		}

        $pr = new Permission_Role();
        $roleInfos = $pr->getByKeys($role);

		$data = $this->one->select('t_staff_user', array('*'), $where);
		if (empty($data['data']))
		{
			return array();
		}
		$list = $data['data'];
		foreach ($list as $idx => $item)
		{
			$rolesOfItem = explode(',', $item['roles']);
			if (!is_array($role))
			{
				if (!in_array($roleInfos[$role]['id'], $rolesOfItem))
				{
					unset($list[$idx]);
				}
			}
			else
			{
				$find = false;
				for($i=0,$len=count($role); $i<$len; $i++)
				{
					if (in_array($roleInfos[$role[$i]]['id'], $rolesOfItem))
					{
						$find = true;
						break;
					}
				}
				if (!$find)
				{
					unset($list[$idx]);
				}
			}
		}

		return $list;
	}

	public function getList(&$total, $start=0, $num=20, $searchConf='')
	{
        !empty($searchConf['status'])&&$searchConf['status']==Conf_Base::STATUS_ALL? $where='1=1 ': $where='status=0 ';
        empty($searchConf['wid'])?'':$where .= " AND wid = $searchConf[wid] ";
        empty($searchConf['name'])?'':$where .= " AND name like '%{$searchConf['name']}%' ";
        empty($searchConf['mobile'])?'':$where .= " AND mobile like '%{$searchConf['mobile']}%' ";
        empty($searchConf['role'])?'':$where .= " AND find_in_set({$searchConf['role']}, roles)";
        empty($searchConf['department'])?'':$where .= " AND department = {$searchConf['department']}";
        empty($searchConf['city_id'])? '': $where .= " AND find_in_set({$searchConf['city_id']}, cities)";
        
		// 查询数量
		$data = $this->one->select('t_staff_user', array('count(1)'), $where);
		$total = intval($data['data'][0]['count(1)']);
		if (empty($total))
		{
			return array();
		}
		// 查询结果
		$order = 'order by suid desc';
		$data = $this->one->select('t_staff_user', array('*'), $where, $order, $start, $num);

		return $data['data'];
	}

    /**
     * 附加操作员信息.
     * 
     * @param array $list
     * @param string $field
     * @param string $field2
     * @param bool $withRawKey
     */
    public function appendOperators(&$list, $field='suid', $field2='', $withRawKey=false)
    {
        if (empty($list)) return;
        
        $hcStaffIds = $franchiseeIds = array();
        foreach($list as $item)
        {
            if (!empty($item[$field]))
            {
                if ($item[$field] <= Conf_Admin::SELF_STAFF_SUID)
                {
                    $hcStaffIds[] = $item[$field];
                }
                else
                {
                    $franchiseeIds[] = $item[$field];
                }
            }
            if (!empty($field2) && !empty($item[$field2]))
            {
                if ($item[$field2] <= Conf_Admin::SELF_STAFF_SUID)
                {
                    $hcStaffIds[] = $item[$field2];
                }
                else
                {
                    $franchiseeIds[] = $item[$field2];
                }
            }
        }
        unset($item);
        
        //获取数据
        $staffInfos = array();
        if (!empty($hcStaffIds))
        {
            $hcStaffField = array('suid', 'name', 'mobile');
            $staffInfos = Tool_Array::list2Map($this->getUsers($hcStaffIds, $hcStaffField), 'suid');
        }
        if (!empty($franchiseeIds))
        {
            $fu = new Franchisee_User();
            $franchiseeField = array('fid', 'name', 'mobile');
            $franchiseeInfos = $fu->getUsers($franchiseeIds, $franchiseeField);
            foreach($franchiseeInfos as $fItem)
            {
                $staffInfos[$fItem['fid']] = $fItem;
                $staffInfos[$fItem['fid']]['suid'] = $fItem['fid'];
            }
        }
        
        //append operator info
        $fieldKey = $withRawKey? '_'.$field: '_suser';
        $fieldKey2 = $withRawKey? '_'.$field2: '_suser2';
        
		foreach ($list as &$item)
		{
			$suidField = $item[$field];
			if (!empty($staffInfos[$suidField]) )
            {
                $item[$fieldKey] = $staffInfos[$suidField];
            }
            
            if (empty($field2)) continue;
            
            $suidField2 = $item[$field2];
            if (!empty($staffInfos[$suidField2]))
            {
                $item[$fieldKey2] = $staffInfos[$suidField2];
            }
		}
    }
    
	public function appendSuers(array &$list, $field='suid', $field2 = '', $withRawKey=false)
	{
		if (empty($list)) return ;
        
		$suids = Tool_Array::getFields($list, $field);
		$suids2 = array();
		if (!empty($field2))
		{
			$suids2 = Tool_Array::getFields($list, $field2);
			$suids = array_merge($suids, $suids2);
		}
		$suers = $this->getUsers($suids);
		$suers = Tool_Array::list2Map($suers, 'suid');

        $fieldKey = $withRawKey? '_'.$field: '_suser';
        $fieldKey2 = $withRawKey? '_'.$field2: '_suser2';
        
		foreach ($list as $idx => $item)
		{
			$suid = $item[$field];
			if (!empty($suers[$suid]) )
            {
                $list[$idx][$fieldKey] = $suers[$suid];
            }
            
			if (!empty($field2))
			{
				$suid2 = $item[$field2];
				if (!empty($suers[$suid2]))
				{
					$list[$idx][$fieldKey2] = $suers[$suid2];
				}
			}
		}
	}

	public function getAll($all=false)
	{
		$where = $all ? array():array('status' => 0);

		// 查询结果
		$order = 'order by suid desc';
		$data = $this->one->select('t_staff_user', array('*'), $where, $order, 0, 0);
		if (empty($data['data']))
		{
			return array();
		}

		return $data['data'];
	}

	private function _parseCitiesAndWids($userInfo)
    {
        $cities = explode(',', $userInfo['cities']);
        $wids = explode(',', $userInfo['wids']);
        $mapping = Conf_Warehouse::$WAREHOUSE_CITY_MAPPING;

        foreach ($cities as $city)
        {
            $userInfo['city_wid_map'][$city] = array();
            foreach ($wids as $wid)
            {
                if ($city == $mapping[$wid])
                {
                    $userInfo['city_wid_map'][$city][] = $wid;
                }
            }
        }

        return $userInfo;
    }
    
    /**
     * 获取指定城市下属
     * @author wangxuemin
     * @param string $role 权限
     * @param string $status 状态
     * @param int $kind 职位状态
     * @param int $cityId 城市ID
     * @param int $leaderSuid 直属上级suid
     * @return array
     */
    public function getGroupStaffoByRole($role, $status = false, $kind = 0, $cityId = 0, $leaderSuid = 0)
    {
        $where = '1=1';
        $where .= ($status !== false)? ' and status='. $status: '';
        $where .= (!empty($kind)&&is_numeric($kind))? ' and kind='.$kind :
        (!empty($kind)&&is_array($kind)? ' and kind in ('. implode(',', $kind). ')': '');
        $where .= !empty($cityId)? ' and city_id='. $cityId: '';
        $where .= !empty($leaderSuid)? ' and leader_suid='. $leaderSuid : '';
        $_staffs = $this->one->select('t_staff_user', array('*'), $where);
        $staffList = $_staffs['data'];
        if (empty($staffList)) return array();
        // 角色
        $roleInfos = Permission_Api::getRolesByRkey($role, array('id', 'role', 'rkey'));
        $alloRoleIds = Tool_Array::getFields($roleInfos, 'id');
        foreach($staffList as $index => $info)
        {
            $staffRoleIds = explode(',', $info['roles']);
            $comRoles = array_intersect($staffRoleIds, $alloRoleIds);
    
            if (empty($comRoles))
            {
                unset($staffList[$index]);
            }
        }
        return $staffList;
    }
    
    /**
     * 获取全部下属（递归所有下级）
     * @author wangxuemin
     * @param string $role 权限
     * @param string $status 状态
     * @param int $kind 职位状态
     * @param int $cityId 城市ID
     * @param int $leaderSuid 直属上级suid
     * @return array
     */
    public function getTeamMembersById($role, $status = false, $kind = 0, $cityId = 0, $leaderSuid = 0)
    {
        $result = array();
        $memberList = $this->getGroupStaffoByRole($role, $status, $kind, $cityId, $leaderSuid);
        foreach ($memberList as $key => $val) {
            $result[] = $val;
            if ($val['suid'] !== $leaderSuid) {
               $res = $this->getTeamMembersById($role, $status, $kind, $cityId, $val['suid']);
               $result = array_merge($result, $res);
            }
        }
        return $result;
    }
    
    /**
     * 获取团队人员信息
     * @author wangxuenmin
     * @param string $role 权限
     * @param string $status 状态
     * @param int $kind 职位状态
     * @param int $cityId 城市ID
     * @param array $suids
     * @return array
     */
    public function getTeamMembers($role, $status = false, $kind = 0, $cityId = 0, $suids = array(0))
    {
        $where = '1=1';
        $where .= ($status !== false)? ' and status='. $status: '';
        $where .= (!empty($kind)&&is_numeric($kind))? ' and kind='.$kind :
        (!empty($kind)&&is_array($kind)? ' and kind in ('. implode(',', $kind). ')': '');
        $where .= !empty($cityId)? ' and city_id='. $cityId: '';
        $where .= ' and suid in ('.implode(',', $suids).')';
        $_staffs = $this->one->select('t_staff_user', array('*'), $where);
        $staffList = $_staffs['data'];
        if (empty($staffList)) return array();
        // 角色
        $roleInfos = Permission_Api::getRolesByRkey($role, array('id', 'role', 'rkey'));
        $alloRoleIds = Tool_Array::getFields($roleInfos, 'id');
        foreach($staffList as $index => $info)
        {
            $staffRoleIds = explode(',', $info['roles']);
            $comRoles = array_intersect($staffRoleIds, $alloRoleIds);
        
            if (empty($comRoles))
            {
                unset($staffList[$index]);
            }
        }
        return $staffList;
    }
    
}
