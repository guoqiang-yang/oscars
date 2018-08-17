<?php

/**
 * 角色相关
 */
class Admin_Role_Api extends Base_Api
{
    /**
     * 是否可以执行某操作
     * @param $suser
     * @param $action
     *
     * @return bool
     */
    public static function canDo($suser, $action)
    {
        $permissions = array();

        // 超级管理员
        if (in_array($suser['suid'], Conf_Admin::$SUPER_ADMINER))
        {
            return true;
        }
        else if (!empty($suser['roles']))
        {
            $roleIds = explode(',', $suser['roles']);
            $roles = Permission_Api::getBulk($roleIds);
            foreach ($roles as $role)
            {
                $permissionArr = json_decode($role['permission'], true);
                foreach ($permissionArr as $key => $arr)
                {
                    $permissions = array_merge($permissions, $arr);
                }
            }

            return in_array($action, $permissions);
        }

        return false;
    }

    /**
     * 管理员是否有指定的角色
     * @param $suser
     * @param $role
     *
     * @return bool
     */
    public static function hasRole($suser, $role)
    {
        if (empty($suser))
        {
            return FALSE;
        }

        $pr = new Permission_Role();
        $roleInfos = $pr->getByKeys($role);
        
        // suser的全部roles，含关联角色 rel_role
        $_roleIds = array_unique(explode(',', $suser['roles']));
        $allRoleIds = Permission_Api::getAllRoles($_roleIds);
        
        return in_array($roleInfos[$role]['id'], $allRoleIds);
    }
    
    public static function hasRoles($suser, $roles)
    {
        if (empty($suser) || !is_array($roles)) return false;
        
        $pr = new Permission_Role();
        $roleInfos = $pr->getByKeys($roles, array('id', 'rkey'));
        
        // suser的全部roles，含关联角色 rel_role
        $_roleIds = array_unique(explode(',', $suser['roles']));
        $suserRoleIds = Permission_Api::getAllRoles($_roleIds);
        
        $hasRole = false;
        foreach($roleInfos as $item)
        {
            if (in_array($item['id'], $suserRoleIds))
            {
                $hasRole = true; break;
            }
        }
        
        return $hasRole;
    }

    /**
     * 根据给定的权限获取管理员列表
     * @param            $role
     * @param bool|FALSE $status
     * @param int        $kind
     * @param int        $cityId
     *
     * @return mixed
     */
    public static function getStaffOfRole($role, $status = FALSE, $kind = 0, $cityId = 0)
    {
        $as = new Admin_Staff();
        $list = $as->getByRole($role, $status, $kind, $cityId);

        return $list;
    }

    /**
     * 检测给定的suid是否管理员
     * @param       $suid
     * @param array $suser
     *
     * @return bool
     */
    public static function isAdmin($suid, $suser = array())
    {
        if (empty($suser))
        {
            if (empty($suid))
            {
                return FALSE;
            }
            $as = new Admin_Staff();
            $suser = $as->get($suid);
        }

        $pr = new Permission_Role();
        $roleInfos = $pr->getByKeys(Conf_Admin::ROLE_ADMIN_NEW);
        $roles = explode(',', $suser['roles']);
        if (!in_array($roleInfos[Conf_Admin::ROLE_ADMIN_NEW]['id'], $roles))
        {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * 检测指定的管理员是否销售总监
     * @param $suser
     *
     * @return int
     */
    public static function isChiefSaler($suser)
    {
        //return self::hasRole($suser, Conf_Admin::ROLE_CHIEF_SALES_NEW);
        return false;
    }

    /**
     * 以部门为维度获取管理员列表
     * @return array
     */
    public static function getDepartmentOfStaff()
    {
        $as = new Admin_Staff();
        $allStaffs = $as->getAll();
        $departments = array();

        foreach ($allStaffs as $staff)
        {
            if ($staff['suid'] == Conf_Admin::ADMINOR_AUTO)
            {
                continue;
            }

            $department = $staff['department'];
            $departments[$department][] = array(
                'suid' => $staff['suid'],
                'name' => $staff['name'],
            );
        }

        return $departments;
    }

    public static function getRoleLevels($suid, $suser = array())
    {
        if (empty($suser))
        {
            if (empty($suid)) return array();
            
            $as = new Admin_Staff();
            $suser = $as->get($suid);
        }

        $roleIds = explode(',', $suser['roles']);
        $_rKeys = Permission_Api::getRolesRkey($roleIds);
        
        $rKeys = array();
        foreach($_rKeys as $rkey)
        {
            $rKeys[$rkey] = 1;
        }
        
        return $rKeys;
        
//        $pr = new Permission_Role();
//        $permissions = $pr->getAll();
//        
//        $roleStr = explode(',', $suser['roles']);
//        $rolesLevels = array();
//        foreach ($roleStr as $roleItem)
//        {
//            $role = $permissions[$roleItem]['rkey'];
//            $rolesLevels[$role] = 1;
//        }
//        
//        return $rolesLevels;
    }
    
    /**
     * 获取指定组销售专员
     * @author wangxuemin
     * @param string $role 权限
     * @param boo $status 状态
     * @param int $kind 职位状态
     * @param int $cityId 城市ID
     * @param int $leaderSuid 直属上级suid
     * @return array
     */
    public static function getStaffOfGroupRole($role, $status = FALSE, $kind = 0, $cityId = 0, $leaderSuid = 0)
    {
        $as = new Admin_Staff();
        return $as->getGroupStaffoByRole($role, $status, $kind, $cityId, $leaderSuid);
    }
    
    /**
     * 获取全部下属
     * @param string $role 权限
     * @param string $status 状态
     * @param int $kind 职位状态
     * @param int $cityId 城市ID
     * @param int $leaderSuid 直属上级suid
     * @return array
     */
    public static function getTeamMembersById($role, $status = FALSE, $kind = 0, $cityId = 0, $leaderSuid = 0)
    {
        $as = new Admin_Staff();
        return $as->getTeamMembersById($role, $status, $kind, $cityId, $leaderSuid);
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
    public static function getTeamMembers($role, $status = false, $kind = 0, $cityId = 0, $suids = array(0))
    {
        $as = new Admin_Staff();
        return $as->getTeamMembers($role, $status, $kind, $cityId, $suids);
    }
    
}
