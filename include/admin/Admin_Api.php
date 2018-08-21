<?php

/**
 * 后台管理 相关接口
 */
class Admin_Api extends Base_Api
{
    
    public static function getStaff($suid, $status=Conf_Base::STATUS_NORMAL, $nocache=false)
    {
        $staff = array();
        
        if (!$nocache)
        {
            $memKey = Conf_Memcache::getMemcacheKey(Conf_Memcache::MEMKEY_STAFF_INFO, $suid);
            $staff = Data_Memcache::getInstance()->get($memKey);
        }
        
        if (empty($staff) || !is_array($staff))
        {
            $as = new Admin_Staff();
            $staff = $as->get($suid, $status);
            
            Data_Memcache::getInstance()->set($memKey, $staff, 86400);
        }
        
        return $staff;
    }
    
    public static function getStaffsByWhere($where, $start=0, $num=100)
    {
        $as = new Admin_Staff();
        
        $list = array();
        $total = $as->getTotal($where);
        
        if (!empty($total))
        {
            $list = $as->getByWhere($where, $start, $num);
            
            foreach($list as &$oner)
            {
                $oner['_department'] = !empty($oner['department'])? Conf_Permission::getDeparement($oner['department']): '空';
                $oner['_cities_cn'] = implode(',', Conf_City::getCityCnamesByCityIds($oner['cities']));
            }
        }
        
        return array('list'=>$list, 'total'=>$total);
    }
    
    /**
     * 附加员工信息.
     */
    public static function appendStaffInfos(array &$list, $field='suid', $simpleInfo=true)
	{
		if (empty($list)) return ;
        
        $as = new Admin_Staff();
		$suids = Tool_Array::getFields($list, $field);
        $staffInfos = $as->getUsers($suids);
        
        foreach($list as &$item)
        {
            $_staffInfo = array();
            $_suid = $item[$field];
            if (!empty($staffInfos[$_suid]))
            {
                if ($simpleInfo)
                {
                    $_staffInfo = array('name'=>$staffInfos[$_suid]['name'], 'mobile'=>$staffInfos[$_suid]['mobile']);
                }
                else
                {
                    $_staffInfo = $staffInfos[$_suid];
                }
            }
            $item['_suser'] = $_staffInfo;
        }
	}        
    
    //@todo-del
//	public static function getStaffList($start = 0, $num = 1000, $searchConf='')
//	{
//		$as = new Admin_Staff();
//		$list = $as->getList($total, $start, $num, $searchConf);
//        $leaderSuids = Tool_Array::getFields($list, 'leader_suid');
//        if(!empty($leaderSuids))
//        {
//            $leaders = Tool_Array::list2Map($as->getUsers($leaderSuids, array('suid', 'name', 'mobile')), 'suid');
//        }else{
//            $leaders = array();
//        }
//
//        foreach ($list as &$v) 
//        {
//            if($v['leader_suid'])
//            {
//                $v['leader'] = $leaders[$v['leader_suid']]['name'];
//            }
//        }
//
//		$hasMore = $total > $start + $num;
//
//		foreach ($list as &$oner)
//		{
//			$oner['roles'] = explode(',', $oner['roles']);
//            foreach ($oner['roles'] as &$role) 
//            {
//                $tmp = explode(':', $role);
//                $role = $tmp[0];
//            }
//
//            $oner['_department'] = $oner['department'] ? Conf_Permission::$DEPAREMENT[$oner['department']] : '全部';
//		}
//        
//		return array('list' => $list, 'total' => $total, 'has_more' => $hasMore);
//	}

	public static function getSales4City($city_id)
    {
        $as = new Admin_Staff();
        $where = sprintf('department=%d and status=%d and (city_id=%d OR find_in_set("%d", cities))', Conf_Permission::DEPARTMENT_SELL,Conf_Base::STATUS_NORMAL,$city_id, $city_id);
        $list = $as->getByWhere($where, 0, 0);
        return $list;
    }

    /**
     * 获取当前销售的直接上级领导
     * @param $suid
     */
    public static function getSaleLeaderBySuid($user,$order)
    {
        if(empty($user) || empty($order))
        {
            return array();
        }
        $data = array();

        if(Admin_Role_Api::isAdmin($user['suid']))
        {
            $_leader_info = self::getStaffByCityAndRoles($order['city_id'], Conf_Admin::baseRkeyOfSalesLeader());
            if(!empty($_leader_info) && !empty($order['saler_suid']))
            {
                $_info = self::getStaff($order['saler_suid']);
                foreach ($_leader_info as $item)
                {
                    if($item['suid'] == $order['saler_suid'] || $item['suid'] == $_info['leader_suid'])
                    {
                        $data[$item['suid']] = array(
                            'suid' => $item['suid'],
                            'name' => $item['name']
                        );
                    }
                }
            }
            $_city_info = self::getStaffByCityAndRole($order['city_id'], Conf_Admin::ROLE_CITY_ADMIN_NEW);
            if(!empty($_city_info))
            {
                foreach ($_city_info as $item)
                {
                    $data[$item['suid']] = array(
                        'suid' => $item['suid'],
                        'name' => $item['name']
                    );
                }
            }
        }elseif(Admin_Role_Api::hasRoles($user, array('DXZZ1', 'QDTZZG1', 'KAZG1',Conf_Admin::ROLE_CITY_ADMIN_NEW)))
        {
            $data[$user['suid']] = array(
                'suid' => $user['suid'],
                'name' => $user['name']
            );
        }

        return $data;
    }
    
    /**
     * 获取销售的领导.
     */
    public static function getSalesLeaders($suid=0, $cityId=array())
    {   
        $pr = new Permission_Role();
        $field = array('id', 'rkey', 'rel_role');
        $allRoles = Tool_Array::list2Map($pr->getByWhere('status=0', 0, 0, $field), 'id');
        
        //leader role_id
        $baseRoleIdOfSalesLeader = array();
        foreach($allRoles as $roleInfo)
        {
            if (in_array($roleInfo['rkey'], Conf_Admin::baseRkeyOfSalesLeader()))
            {
                $baseRoleIdOfSalesLeader[] = $roleInfo['id'];
            }
        }
        
        foreach($allRoles as &$roleInfo)
        {
            $roleInfo['_rel_role'] = explode(',', $roleInfo['rel_role']);
            
            $isBaseLeader = in_array($roleInfo['rkey'], Conf_Admin::baseRkeyOfSalesLeader())? true: false;
            $isInheritBaseLeader = array_intersect($roleInfo['_rel_role'], $baseRoleIdOfSalesLeader)? true: false;
            $roleInfo['is_sales_leader'] = $isBaseLeader || $isInheritBaseLeader;
        }
        
        $leaderRoleIds = array();
        if (empty($suid))
        {
            foreach($allRoles as $item)
            {
                if (!$item['is_sales_leader']) continue;
                
                $leaderRoleIds[] = $item['id'];
            }
        }
        else
        {
            
            $af = new Admin_Staff();
            $staff = $af->get($suid);
            $mysqlRoles = explode(',', $staff['roles']);
            
            //获取指定role的sales-leader：rel_role包含指定的role
            foreach($mysqlRoles as $myRoleId)
            {
                //if (!$allRoles[$myRoleId]['is_sales_leader']) continue;
                
                foreach($allRoles as $item)
                {
                    if (!in_array($myRoleId, $item['_rel_role'])) continue;

                    $leaderRoleIds[] = $item['id'];
                }
            }
        }
        
        if (empty($leaderRoleIds)) return array();
        
        $as = new Admin_Staff();
        $sfield = array('suid', 'name');
        
        $_where = array();
        foreach($leaderRoleIds as $_roleid)
        {
            $_where[] = "find_in_set($_roleid, roles)";
        }
        
        $where = 'status=0 and ('. implode(' OR ', $_where). ')'.
                (!empty($cityId)? ' and find_in_set('. $cityId. ', cities)':'');
        
        
        $staffs = $as->getByWhere($where, 0, 0, $sfield);
        
        return $staffs;
    }


    public static function _getStaff_Del($suid, $status=Conf_Base::STATUS_NORMAL)
	{
		$as = new Admin_Staff();
		$info = $as->get($suid, $status);

		// level信息

        //如果是城市经理,取出该城市下所有的销售及销售组长,添加到team_member中
        if (!empty($info['city_id']) && Admin_Role_Api::hasRole($info, Conf_Admin::ROLE_CITY_ADMIN_NEW))
        {
            $cityMembers = self::getStaffByCityAndRole($info['city_id'], Conf_Admin::ROLE_SALES_NEW);
            $teamMembers = Tool_Array::getFields($cityMembers, 'suid');
            $info['team_member'] = array_unique(array_merge($info['team_member'], Tool_Array::getFields($teamMembers, 'suid')));
        }
        
		return $info;
	}

	public static function getStaffByMobile($mobile)
	{
		$as = new Admin_Staff();
		$userInfo = $as->getByMobile($mobile);

		return $userInfo;
	}

	public static function getStaffs($suids)
	{
		$as = new Admin_Staff();
		$infos = $as->getUsers($suids);

		return $infos;
	}

    public static function getStaffByCityAndRole($city, $role)
    {
        $as = new Admin_Staff();
        $adminList = $as->getByCityAndRole($city, $role);

        return $adminList;
    }

    public static function getStaffByCityAndRoles($city, $roles)
    {
        $as = new Admin_Staff();
        $adminList = $as->getByCityAndRoles($city, $roles);

        return $adminList;
    }

    /**
     * 通过后台系统的各种列表页，返回操作人的姓名,手机号.
     * 
     * @param array $list
     * @param array $fieldNames  array(suid, sales_suid,...)
     * 
     *   filename = {filename}_info
     */
    public static function appendStaffSimpleInfo(&$list, $fieldNames)
    {
        $simpleInfos = array();
        
        $suid = array();
        foreach ($list as $one)
        {
            foreach($fieldNames as $field)
            {
                if (isset($one[$field]) && $one[$field]!=0)
                {
                    $suid[] = $one[$field];
                }
            }
        }
        if (!empty($suid))
        {
	        $staffs = Tool_Array::list2Map(self::getStaffs(array_unique($suid)), 'suid');
        }

        foreach ($list as &$one)
        {
            foreach($fieldNames as $field)
            {
                $suid = $one[$field];
                if (array_key_exists($suid, $staffs))
                {
                    $one[$field.'_info'] = array(
                        'name' => $staffs[$suid]['name'],
                        'mobile' => $staffs[$suid]['mobile'],
                    );
                }
            }
        }
    }
    
	public static function addStaff($info)
	{
		$as = new Admin_Staff();
		$suid = $as->add($info);
        
		return array('suid' => $suid);
	}

	public static function updateStaff($suid, $info)
	{
		$as = new Admin_Staff();
        $staffInfo = $as->get($suid);
        
		$as->update($suid, $info);
        
        if ($_SERVER['SERVER_ADDR'] != '127.0.0.1')
        {
            $memKey = Conf_Memcache::getMemcacheKey(Conf_Memcache::MEMKEY_STAFF_INFO, $suid);
            Data_Memcache::getInstance()->delete($memKey);
            
            if ($staffInfo['leader_suid']!=0 && $staffInfo['leader_suid']!=$suid)
            {
                $memKey = Conf_Memcache::getMemcacheKey(Conf_Memcache::MEMKEY_STAFF_INFO, $staffInfo['leader_suid']);
                Data_Memcache::getInstance()->delete($memKey);
            }
        }

		return TRUE;
	}

	public static function deleteStaff($suid)
	{
		$as = new Admin_Staff();
		$as->delete($suid);
        
        
        if ($_SERVER['SERVER_ADDR'] != '127.0.0.1')
        {
            $memKey = Conf_Memcache::getMemcacheKey(Conf_Memcache::MEMKEY_STAFF_INFO, $suid);
            Data_Memcache::getInstance()->delete($memKey);
        }
        
		return TRUE;
	}

	public static function addActionLog($adminId, $actionType, $params = array())
	{
		$al = new Admin_Log();

		$info = array(
			'admin_id' => $adminId,
			'action_type' => $actionType,
			'params' => json_encode($params),
		);

		return $al->add($info);
	}

	public static function getAdminLogList($searchConf, $start = 0, $num = 0)
	{
		$al = new Admin_Log();
		$total = 0;
		$list = $al->getList($searchConf, $total, $start, $num);

		$as = new Admin_Staff();
		$adminList = $as->getList($t, 0, 0);
		$adminListMap = Tool_Array::list2Map($adminList, 'suid');
		foreach ($list as &$info)
		{
			$desc = Conf_Admin_Log::$ACTION_DESC[$info['action_type']];
			if (!empty($info['params']))
			{
				$params = json_decode($info['params']);
				if (!empty($params))
				{
					foreach ($params as $k => $v)
					{
						$desc = str_replace('{' . $k . '}', $v, $desc);
					}
				}
			}


			$info['desc'] = $desc;
			$info['admin_name'] = $adminListMap[$info['admin_id']]['name'];
			$info['action'] = Conf_Admin_Log::$ACTION_TYPE[$info['action_type']];
		}

		return array('list' => $list, 'total' => $total);
	}

	public static function addOrderActionLog($adminId, $oid, $actionType, $params = array())
	{
		$aol = new Admin_Order_Log();

		$info = array(
			'admin_id' => $adminId,
			'oid' => $oid,
			'action_type' => $actionType,
			'params' => json_encode($params),
		);

		return $aol->add($info);
	}

	public static function getOrderActionLogList($searchConf, $start = 0, $num = 0)
	{
		$aol = new Admin_Order_Log();
		$data = $aol->getList($searchConf, $start, $num);
		$total = $data['total'];
		$list = $data['list'];
        
		foreach ($list as &$info)
		{
            $desc = Conf_Order_Action_Log::$ACTION_DESC[$info['action_type']];
			$info['action'] = Conf_Order_Action_Log::$ACTION_TYPE[$info['action_type']];
            
            $params = json_decode($info['params']);
            if (empty($info['params']) || empty($params)) continue;
            
            foreach ($params as $k => $v)
            {
                if ($info['action_type'] == Conf_Order_Action_Log::ACTION_CHANGE_FEE)
                {
                    $desc .= $k . '：' . $v;
                    $desc .= '；';
                }
                else if ($info['action_type'] == Conf_Order_Action_Log::ACTION_CHANGE_INFO)
                {
                    $desc .= $k . '：' . $v;
                    $desc .= "；";
                }
                else if ($info['action_type'] == Conf_Order_Action_Log::ACTION_CHANGE_PRODUCTS)
                {
                    $desc .= $k . '：' . $v;
                    $desc .= "；";
                }
                else
                {
                    $desc = str_replace('{' . $k . '}', $v, $desc);
                }
            }
			$info['desc'] = $desc;
		}
        
        $as = new Admin_Staff();
        $as->appendOperators($list, 'admin_id');
        foreach($list as &$item)
        {
            $item['admin_name'] = $item['_suser']['name'];
        }
        
		return array('list' => $list, 'total' => $total);
	}

    public static function getAllStaff($all = false)
    {
        $af = new Admin_Staff();

        return $af->getAll($all);
    }

    /**
     * 获取销售人员信息
     * @return array
     */
    public static function getSaleStaff()
    {
        $as = new Admin_Staff();
        $where = sprintf('status=%d AND leader_suid>0', Conf_Base::STATUS_NORMAL);
        $result = $as->getByWhere($where);
        $data = array();
        foreach ($result as $k => $v) {
            $data[$v['leader_suid']][] = $v;
        }
        return $data;
    }

    public static function updateAllStaff4DingTalk()
    {
        $userInfos = self::getAllStaff(true);
        $userInfos = Tool_Array::list2Map($userInfos, 'mobile');
        $departments = Tool_DingTalk::getDepartments();
        $data = array();
        if(!empty($departments))
        {
            foreach ($departments as $department)
            {
                $users = Tool_DingTalk::getDepartmentUsersByDepartmentID($department['id']);
                if(!empty($users))
                {
                    foreach ($users as $user)
                    {
                        if(isset($userInfos[$user['mobile']]) && $userInfos[$user['mobile']]['ding_id'] == '')
                        {
                            self::updateStaff($userInfos[$user['mobile']]['suid'], array('ding_id' => $user['userid']));
                            $userInfos[$user['mobile']]['ding_id'] = $user['userid'];
                            $data[] = $userInfos[$user['mobile']];
                        }
                    }
                }
            }
        }
        return $data;
    }

    public static function addLoginLog($suid, $ip, $source, $agent = '')
    {
        $all = new Admin_Login_Log();

        $info = array(
            'suid' => $suid,
            'source' => $source,
            'ip' => ip2long($ip),
            'agent' => $agent,
        );

        return $all->add($info);
    }

    public static function getLoginLogList($searchConf, $start = 0, $num = 20)
    {
        $dao = new Admin_Login_Log();
        $list = $dao->limit($start, $num)->order('order by id desc')->getList($searchConf);

        $as = new Admin_Staff();
        $adminList = $as->getAll();
        $adminListMap = Tool_Array::list2Map($adminList, 'suid');
        foreach ($list as &$info)
        {
            $info['ip'] = long2ip($info['ip']);
            $info['_suer'] = $adminListMap[$info['suid']];
        }

        $total = $dao->getTotal($searchConf);

        return array('list' => $list, 'total' => $total);
    }
}
