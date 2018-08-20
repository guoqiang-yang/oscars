<?php
/**
 * 管理后台 - 员工相关
 */
class Admin_Staff extends Base_Func
{
    private $_staffDao = null;
    
    function __construct()
    {
        $this->_staffDao = new Data_Dao('t_staff_user');
    }
    
	public function add(array $info)
	{
		assert( !empty($info) );
        
        $info['wids'] = !empty($info['wids'])? $info['wids']: '';
        $info['cities'] = !empty($info['cities'])? $info['cities']: '';
        
        $suid = $this->_staffDao->add($info);
        
		return $suid;
	}

	public function delete($suid)
	{
		assert(intval($suid) > 0);

        return $this->_staffDao->delete($suid);
	}

	public function update($suid, array $info)
	{
		assert( intval($suid) > 0 );
		assert( !empty($info) );
        
        return $this->_staffDao->update($suid, $info);
	}

	public function get($suid, $status=Conf_Base::STATUS_NORMAL)
	{
		assert(intval($suid) > 0);
        
        $where = 'suid='. $suid;
        
        if ($status != Conf_Base::STATUS_ALL)
        {
            $where .= ' and status = '. $status;
        }
        
        $data = $this->_staffDao->getListWhere($where);
        
        $userInfo = current($data);
        
        if (!empty($userInfo))
        {
            $userInfo['team_member'] = array($suid);    //兼容之前数据结构
            $userInfo['_city_ids'] = explode(',', $userInfo['cities']);
            $userInfo['_wids'] = explode(',', $userInfo['wids']);
        }
        
        return $userInfo;
	}
    
    public function getUsers(array $uids, $field=array('*'))
	{
		assert(!empty($uids));

        return $this->_staffDao->setFields($field)
                               ->getList($uids);
	}

	public function getByMobile($mobile)
	{
		assert(!empty($mobile));

		$where = array('mobile' => $mobile);
        
        $_userInfo = $this->_staffDao->getListWhere($where);
        $userInfo = current($_userInfo);
        
        if (!empty($userInfo))
        {
            $userInfo['team_member'] = array($userInfo['suid']);    //兼容之前数据结构
            $userInfo['_city_ids'] = explode(',', $userInfo['cities']);
            $userInfo['_wids'] = explode(',', $userInfo['wids']);
        }
        
        return $userInfo;
	}

	public function getByWhere($where, $start=0, $num=0, $field=array('*'), $order='')
    {
        assert(!empty($where));
        
        return $this->_staffDao->setFields($field)
                               ->limit($start, $num)
                               ->order($order)
                               ->getListWhere($where, false);
    }
    
    public function getTotal($where)
    {
        return $this->_staffDao->getTotal($where);
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

    
}
