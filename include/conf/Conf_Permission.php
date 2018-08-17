<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/1/10
 * Time: 09:38
 */
class Conf_Permission
{
    const DEPARTMENT_CS = 1;
    const DEPARTMENT_RD = 2;
    const DEPARTMENT_SELL = 3;
    const DEPARTMENT_OP = 4;
    const DEPARTMENT_STORAGE = 5;
    const DEPARTMENT_SCHEDULING = 6;
    const DEPARTMENT_PURCHASE = 7;
    const DEPARTMENT_EDITOR = 8;
    const DEPARTMENT_FINANCE = 9;
    const DEPARTMENT_PERSONNEL = 10;
    const DEPARTMENT_WAREHOUSE = 11;
    const DEPARTMENT_AFTER_SALE = 12;

    //研发部，仓储部，调度部，运营部，客服部，采购部，销售部，编辑部，财务部，人事部
    public static $DEPAREMENT = array(
        self::DEPARTMENT_CS => '客服部',
        self::DEPARTMENT_RD => '研发部',
        self::DEPARTMENT_SELL => '销售部',
        self::DEPARTMENT_OP => '运营部',
        self::DEPARTMENT_WAREHOUSE => '仓储部',
        self::DEPARTMENT_STORAGE => '调度部',
        self::DEPARTMENT_PURCHASE => '采购部',
        self::DEPARTMENT_EDITOR => '编辑部',
        self::DEPARTMENT_FINANCE => '财务部',
        self::DEPARTMENT_PERSONNEL => '人事部',
        self::DEPARTMENT_AFTER_SALE => '售后部',
    );

    // 锁库，需要排除的页面
    public static $Except_Pages_4_Locked_Warehouse = array(
        '/warehouse/stock_list', 
        '/warehouse/location_list', 
        '/warehouse/stock_history', 
        '/warehouse/location_export', 
        '/warehouse/stock_warning',
    );
    
    //部门默认权限
    //如果某个tab下的权限都有，格式：tab_name => '*'
    //如果某个tab下，某个菜单的权限都有，格式：tab_name => array(menu_name => '*'),
    //如果要定义某个tab下，某个菜单的某些权限，格式： tab_name => array(menu_name => array(button1, button2...)),
    public static $DEFAULT_PERMISSION = array(
        self::DEPARTMENT_CS => array(
            'crm2' => '*',
            'order' => array(
                'quick_order_list' => '*',
                'order_list' => array(
                    'order_detail', 'order_delete',
                )),
        ),
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