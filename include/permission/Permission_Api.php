<?php
/**
 * 权限管理.
 * 
 */


class Permission_Api extends Base_Api
{
    public static function add($role, $department, $rkey, $suid, $relRole='')
    {
        $pr = new Permission_Role();

        $info = array(
            'role' => $role,
            'rkey' => $rkey,
            'department' => $department,
            'suid' => $suid,
            'rel_role' => $relRole,
            'permission' => '',
        );

        return $pr->add($info);
    }

    public static function update($id, $update, $change = array())
    {
        $pr = new Permission_Role();

        return $pr->update($id, $update, $change);
    }
    
    public static function get($id)
    {
        $pr = new Permission_Role();

        return $pr->get($id);
    }

    public static function getBulk($ids)
    {
        $pr = new Permission_Role();

        return $pr->getBulk($ids);
    }

    public static function getAll()
    {
        $pr = new Permission_Role();
        $data = $pr->getList(array(), 0, 0);

        return $data;
    }
    
    /**
     * 获取全部的角色 (含关联角色).
     */
    public static function getAllRoles($roleIds)
    {
        $allRoleIds = array();
        
        $relRoleInfos = self::getBulk($roleIds);
        
        foreach($relRoleInfos as $item)
        {
            $rRoleId = empty($item['rel_role'])? array($item['id']): 
                     array_merge(array($item['id']), explode(',', $item['rel_role']));
            
            $allRoleIds = array_merge($allRoleIds, $rRoleId);
        }
        
        return array_unique($allRoleIds);
    }
    
    /**
     * 获取全部的权限（含关联角色）.
     */
    public static function getAllPermission($roleIds, &$pages)
    {
        $allPermission = array();
        
        if (empty($roleIds)) return $allPermission;
        
        $pr = new Permission_Role();
        $roleInfos = $pr->getBulk($roleIds);
        
        // own permission
        $relRoleIds = array();
        foreach($roleInfos as $item)
        {
            $plist = json_decode($item['permission'], true);
            
            foreach ($plist as $_page => $_permission)
            {
                $pages[] = $_page;
                $allPermission = array_merge($allPermission, $_permission);
            }
            
            if (!empty($item['rel_role']))
            {
                $relRoleIds = array_merge($relRoleIds, explode(',', $item['rel_role']));
            }
        }
        unset($item);
        
        // rel permission
        if (empty($relRoleIds)) return array_unique($allPermission);
        
        $relRoleInofs = $pr->getBulk($relRoleIds);
        foreach($relRoleInofs as $item)
        {
            $plist = json_decode($item['permission'], true);
            foreach ($plist as $_page => $_permission)
            {
                $pages[] = $_page;
                $allPermission = array_merge($allPermission, $_permission);
            }
        }
        
        $pages = array_unique($pages);
        
        return array_unique($allPermission);
    }
    
    /**
     * 获取角色的标识 rkey （含关联角色）.
     */
    public static function getRolesRkey($roleIds)
    {
        $rKeys = array();
        
        if (empty($roleIds)) return $rKeys;
        
        $pr = new Permission_Role();
        $roleInfos = $pr->getBulk($roleIds);
        
        $relRoleIds = array();
        foreach ($roleInfos as $item)
        {
            $rKeys[] = $item['rkey'];
            
            if (!empty($item['rel_role']))
            {
                $relRoleIds = array_merge($relRoleIds, explode(',', $item['rel_role']));
            }
        }
        unset($item);
        
        if (!empty($relRoleIds))
        {
            $relRoleInfos = $pr->getBulk($relRoleIds);
            foreach($relRoleInfos as $item)
            {
                $rKeys[] = $item['rkey'];
            }
        }
        
        return array_unique($rKeys);
    }

    /**
     * 包含rkey（角色标识）角色信息.
     * 
     * @param string $rkey 角色标识
     * @param array $field
     */
    public static function getRolesByRkey($rkey, $field=array('*'))
    {
        $pr = new Permission_Role();
        $roleInfos = $pr->getByKeys($rkey, $field);
        
        if (empty($roleInfos)) return array();
        
        $where = array();
        foreach($roleInfos as $item)
        {
            $where[] = 'find_in_set('. $item['id'].', rel_role)';
        }
        
        $relRoleInfos = $pr->getByWhere(implode(' or ', $where), 0, 0, $field);
        
        $roleInfos = Tool_Array::list2Map($roleInfos, 'id');
        
        foreach($relRoleInfos as $item)
        {
            $roleInfos[$item['id']] = $item;
        }
        
        return $roleInfos;
    }
    
    /**
     * 获取角色的关联权限.
     */
    public static function getRelRolePermission($id)
    {
        $pr = new Permission_Role();
        $role = $pr->get($id);
        
        if (empty($role['rel_role'])) return array();
        
        $relRoles = Permission_Api::getBulk(explode(',', $role['rel_role']));
        
        $relPermissionList = array();
        foreach($relRoles as $item)
        {
            $permission = json_decode($item['permission'], true);
            
            foreach($permission as $k => $val)
            {
                if (! array_key_exists($k, $relPermissionList))
                {
                    $relPermissionList[$k] = $val;
                }
                else
                {
                    $relPermissionList[$k] = array_unique(array_merge($relPermissionList[$k], $val));
                }
            }
        }
        
        return $relPermissionList;
    }
    
    public static function getList($searchConf, $start = 0, $num = 20)
    {
        $pr = new Permission_Role();
        $data = $pr->getList($searchConf, $start, $num);
        
        if (!empty($data['list']))
        {
            foreach ($data['list'] as &$role)
            {
                $role['_department'] = $role['department'] ? Conf_Permission::getDeparement($role['department']) : '全部';
            }
            
            Admin_Api::appendStaffInfos($data['list']);
        }
        
        return $data;
    }

    public static function getListByWhere($where, $field, $start = 0, $num = 20)
    {
        $pr = new Permission_Role();
        $ad = new Admin_Staff();

        $admins = $ad->getAll(true);
        $adminMap = Tool_Array::list2Map($admins, 'suid');

        $ret = $pr->getListWhere($where . ' and status = ' . Conf_Base::STATUS_NORMAL, array('id'), 0, 0);
        $ids = Tool_Array::getFields($ret['list'], 'id');
        $relWhere = '';
        if (!empty($ids))
        {
            foreach ($ids as $id)
            {
                if (empty($relWhere))
                {
                    $relWhere .= sprintf(' rel_role regexp "(^|,)%d($|,)" ', $id);
                }
                else
                {
                    $relWhere .= sprintf(' or rel_role regexp "(^|,)%d($|,)" ', $id);
                }
            }

            $where = '(' . $where . ' or (' . $relWhere . ')) and status = ' . Conf_Base::STATUS_NORMAL;
        }

        $data = $pr->getListWhere($where, $field, $start, $num);
        if (!empty($data['list']))
        {
            foreach ($data['list'] as &$role)
            {
                $role['_department'] = $role['department'] ? Conf_Permission::$DEPAREMENT[$role['department']] : '全部';
                $role['_suser'] = $adminMap[$role['suid']]['name'];
            }
        }

        return $data;
    }
}