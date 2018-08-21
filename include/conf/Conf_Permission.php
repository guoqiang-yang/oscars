<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/1/10
 * Time: 09:38
 */
class Conf_Permission
{
    const DEPARTMENT_CS     = 1;
    const DEPARTMENT_RD     = 2;
    const DEPARTMENT_SELL   = 3;
    const DEPARTMENT_OP     = 4;
    const DEPARTMENT_WAREHOUSE  = 5;
    const DEPARTMENT_LOGISTICS  = 6;
    const DEPARTMENT_PURCHASE   = 7;
    const DEPARTMENT_FINANCE    = 8;
    const DEPARTMENT_PERSONNEL  = 9;
    const DEPARTMENT_AFTER_SALE = 10;

    //研发部，仓储部，调度部，运营部，客服部，采购部，销售部，编辑部，财务部，人事部
    public static function getDeparement($id=0)
    {
        $all = array(
            self::DEPARTMENT_CS         => '客服部',
            self::DEPARTMENT_RD         => '研发部',
            self::DEPARTMENT_SELL       => '销售部',
            self::DEPARTMENT_OP         => '运营部',
            self::DEPARTMENT_WAREHOUSE  => '仓储部',
            self::DEPARTMENT_LOGISTICS  => '调度部',
            self::DEPARTMENT_PURCHASE   => '采购部',
            self::DEPARTMENT_FINANCE    => '财务部',
            self::DEPARTMENT_PERSONNEL  => '人事部',
            self::DEPARTMENT_AFTER_SALE => '售后部',
        );
        
        return array_key_exists($id, $all)? $all[$id]: $all;
    }
    

    // 锁库，需要排除的页面
    public static $Except_Pages_4_Locked_Warehouse = array(
        '/warehouse/stock_list', 
        '/warehouse/location_list', 
        '/warehouse/stock_history', 
        '/warehouse/location_export', 
        '/warehouse/stock_warning',
    );
    
    public static function getModules($suer, $modules)
    {
        $newModules = array();
        $permissions = array();
        
        if (in_array($suer['suid'], Conf_Admin::$SUPER_ADMINER))
        {
            return $modules;
        }

        $roleIds = explode(',', $suer['roles']);
        $permissions = Permission_Api::getAllPermission($roleIds, $pages);
        
//        $roles = Permission_Api::getBulk($roleIds);
//        $pages= array();
//        foreach ($roles as $role)
//        {
//            $plist = json_decode($role['permission'], true);
//            foreach ($plist as $pkey => $keys)
//            {
//                $pages[] = $pkey;
//                $permissions = array_merge($permissions, $keys);
//            }
//        }
//        $permissions = array_filter(array_unique($permissions));   
        
        foreach ($modules as $fkey => $fitem)
        {
            foreach ($fitem['pages'] as $skey => $sitem)
            {
                if (!is_array($pages) || !in_array($sitem['key'], $pages))
                {
                    continue;
                }
                if (!empty($sitem['buttons']))
                {
                    foreach ($sitem['buttons'] as $tkey => $titem)
                    {
                        if (in_array($titem['key'], $permissions))
                        {
                            if (empty($newModules[$fkey]))
                            {
                                $newModules[$fkey]['name'] = $fitem['name'];
                                $newModules[$fkey]['pages'][$skey] = $sitem;
                                $newModules[$fkey]['display'] = $fitem['display'];
                            }
                            else
                            {
                                $newModules[$fkey]['pages'][$skey] = $sitem;
                            }
                        }
                    }
                }
            }
        }
        
        $lockedRet = Conf_Warehouse::isLockedWarehouse($suer['wid']);
        
        foreach ($newModules as $fkey => &$info)
        {
            foreach ($info['pages'] as $kk=>&$page)
            {
                if ($lockedRet['st'])
                {
                    if (in_array($page['key'], self::$Except_Pages_4_Locked_Warehouse))
                    {
                        unset($newModules[$fkey]['pages'][$kk]);
                    }
                }
            }

            $info['pages'] = array_values($info['pages']);
        }
        
        return $newModules;
    }
}