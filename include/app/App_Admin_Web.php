<?php

/**
 * 管理运营后台 - Web基类
 */
class App_Admin_Web extends Base_App
{
    protected $lgmode;        //页面逻辑模式 -- pub (公开页面,不需登录); pri (私有页面,需要登录)
    protected $smarty;        //smarty 工具对象
    //统计接口执行时间使用
    protected $switch4ExecTime = FALSE;
    protected $Limit4ExecTime = 1;  //单位：秒
    //权限数据
    protected $permissions = array();
    /**
     * format: array('all'=>$arrayCont, 'xx'=>$arrayCont)
     *
     *  - all 必须填写
     *  - $arrayContent=array('btime'=>'xxxx', 'etime'=>'xxxx');
     */
    protected $detail4ExecTime = array('all' => array());

    function __construct($lgmode = 'pri', $tmplpath = ADMIN_TEMPLATE_PATH)
    {
        $this->lgmode = $lgmode;
        $this->smarty = new Tool_Smarty($tmplpath);
    }

    protected function checkPermission($permission = '')
    {
        //        return false;

        $this->_setPermission();
        
        if (in_array($this->_uid, Conf_Admin::$SUPER_ADMINER))
        {
            return FALSE;
        }

        empty($permission) && $permission = str_replace('.php', '', $_SERVER['SCRIPT_NAME']);

        if (is_array($permission))
        {
            $forbidden = TRUE;
            foreach ($permission as $_permission)
            {
                $forbidden = $forbidden && (array_key_exists($_permission, $this->permissions) ? FALSE : TRUE);
            }
        }
        else
        {
            $forbidden = array_key_exists($permission, $this->permissions) ? FALSE : TRUE;
        }

        return $forbidden;
    }

    private function _setPermission()
    {
        if (!empty($this->permissions))
            return;

        $permissions = array();
        
        // 超级管理员
        if (in_array($this->_uid, Conf_Admin::$SUPER_ADMINER))
        {
            foreach (Conf_Admin_Page::$MODULES as $fitem)
            {
                foreach ($fitem['pages'] as $sitem)
                {
                    if (!empty($sitem['buttons']))
                    {
                        foreach ($sitem['buttons'] as $titem)
                        {
                            $permissions[] = $titem['key'];
                        }
                    }
                }
            }
        }
        else if (!empty($this->_user['roles']))
        {
            $roleIds = explode(',', $this->_user['roles']);
//            $roles = Permission_Api::getBulk($roleIds);
//
//            foreach ($roles as $role)
//            {
//                $plist = json_decode($role['permission'], true);
//                foreach ($plist as $pkey => $keys)
//                {
//                    $permissions = array_merge($permissions, $keys);
//                }
//            }
            
            $permissions = Permission_Api::getAllPermission($roleIds, $pages);
        }
        
        $lockedRet = Conf_Warehouse::isLockedWarehouse($this->_user['wid']);
        if ($lockedRet['st'])
        {
            $permissions = array_diff($permissions, Conf_Permission::$Except_Pages_4_Locked_Warehouse);
        }
        
        foreach ($permissions as $p)
        {
            $this->permissions[$p] = 1;
        }
    }

    protected function checkAuth()
    {
        $this->_uid = $this->getLoginUid();
        if (in_array($this->_uid, array(1036, 1029, 1254)) && !empty($_REQUEST['test_suid']))
        {
            $this->_uid = $_REQUEST['test_suid'];
        }

        // 已登录状态处理
        if ($this->_uid)
        {
            //获取用户信息
            $this->_user = Admin_Api::getStaff($this->_uid);

            Tool_Log::addAccessLog($this->_uid);
        }

        $this->printLog();
        $this->detail4ExecTime['all'][] = microtime(TRUE);
    }

    protected function free()
    {
        Data_DB::free();
        $this->detail4ExecTime['all'][] = microtime(TRUE);

        //$this->_outputExecTime();
        
        $slowLog = '';
        global $HC_SQL_EXTIMES;
        
        if (!empty($HC_SQL_EXTIMES))
        {
            foreach($HC_SQL_EXTIMES as $item)
            {
                if (0 && $item['extime'] > 2)
                {
                    $slowLog .= "$this->_uid\t{$item['extime']}\n";
                    $slowLog .= "[sql] {$item['sql']}\n";
                    $slowLog .= "[page] {$_SERVER['REQUEST_URI']}\n\n";
                }
            }
            if (!empty($slowLog))
            {
                Tool_Log::addFileLog('slow_log', $slowLog);
            }

            if ($_REQUEST['debug'] == 2)
            {
                print_r($HC_SQL_EXTIMES);
            }
        }
    }

    protected function printLog()
    {
        $_uid = empty($this->_uid) ? 0 : $this->_uid;
        $info = sprintf("\n-------------admin query suid:$_uid\tparam: %s--------------\n", $_SERVER['REQUEST_URI']);
        $info .= "user-agent = " . $_SERVER['HTTP_USER_AGENT'] . "\n";
        $info .= "request = " . http_build_query($_REQUEST) . "\n";
        //Tool_Log::addFileLog('admin/query_params_'.date('Ymd'), $info);
    }

    private function _outputExecTime()
    {
        if (!$this->switch4ExecTime || !array_key_exists('all', $this->detail4ExecTime))
        {
            return;
        }

        $btime = isset($this->detail4ExecTime['all'][0]) ? $this->detail4ExecTime['all'][0] : 0;
        $etime = isset($this->detail4ExecTime['all'][1]) ? $this->detail4ExecTime['all'][1] : 0;
        if ($etime - $btime > $this->Limit4ExecTime && count($this->detail4ExecTime) > 1)
        {
            $log = '';

            if (isset($_SERVER['REQUEST_URI']))
            {
                $log .= "\n\t[path]\t" . $_SERVER['REQUEST_URI'] . "\n";
            }

            foreach ($this->detail4ExecTime as $desc => $times)
            {
                $_btime = isset($times[0]) ? $times[0] : 0;
                $_etime = isset($times[1]) ? $times[1] : 0;
                $_diffTimes = $_etime - $_btime;

                $log .= "\t[$desc]\t$_diffTimes\t$_btime\t$_etime\n";
            }

            Tool_Log::addFileLog('execlog/exec_time_' . date('Ym'), $log);
        }
    }

    protected function setCommonPara()
    {
        $this->smarty->assign('_imgHost', ADMIN_IMG_HOST);
        $this->smarty->assign("_wwwHost", ADMIN_HOST);
        $this->smarty->assign("_uid", $this->_uid);
        $this->smarty->assign("_user", $this->_user);

        if (defined("HIDE_USELESS") && HIDE_USELESS && in_array($this->_uid, Conf_Admin::$DEMO_SUIDS))
        {
            $this->smarty->assign("_hide_useless", 1);
        }
        
        if ($this->_uid)
        {
            $roleLevels = Admin_Role_Api::getRoleLevels($this->_uid, $this->_user);
            $this->smarty->assign("_isSales", isset($roleLevels[Conf_Admin::ROLE_SALES_NEW]));
            $this->smarty->assign("_isBuyer", isset($roleLevels[Conf_Admin::ROLE_BUYER_NEW]));
            $this->smarty->assign("_isLM", isset($roleLevels[Conf_Admin::ROLE_LM_NEW]));
            $this->smarty->assign("_isCS", isset($roleLevels[Conf_Admin::ROLE_CS_NEW]));
            $this->smarty->assign("_isOP", isset($roleLevels[Conf_Admin::ROLE_OP_NEW]));
            $this->smarty->assign("_isWarehouse", isset($roleLevels[Conf_Admin::ROLE_WAREHOUSE_NEW]));
            $this->smarty->assign("_isFinance", isset($roleLevels[Conf_Admin::ROLE_FINANCE_NEW]));
            $this->smarty->assign("_isAdmin", isset($roleLevels[Conf_Admin::ROLE_ADMIN_NEW]));
            $this->smarty->assign("_isSalesDirectorAssistant", (isset($roleLevels[Conf_Admin::ROLE_SALES_DIRECTOR]) || isset($roleLevels[Conf_Admin::ROLE_CITY_ADMIN_NEW])));
            $this->smarty->assign('_isCityAdmin', isset($roleLevels[Conf_Admin::ROLE_CITY_ADMIN_NEW]));
            $this->smarty->assign('_isAssisSaler', isset($roleLevels[Conf_Admin::ROLE_ASSIS_SALER_NEW]));
            $this->smarty->assign('_isYunniao', false);
            $this->smarty->assign('_isAfterSale', isset($roleLevels[Conf_Admin::ROLE_AFTER_SALE_NEW]));
            $this->smarty->assign('_permissions', $this->permissions);
            $this->smarty->assign('_isCommunityCS', false);
            $this->smarty->assign('_allowed_warehouses', $this->getAllowedWids4User(true));
            $this->smarty->assign('_all_warehouses', Appconf_Warehouse::wid4Show());
        }

        // conf 常量
        $this->smarty->assign('_warehouseList', Conf_Warehouse::$WAREHOUSES);
    }

    protected function showError($ex)
    {
        $error = "[" . $ex->getCode() . "]: " . $ex->getMessage();
        if ($ex->reason)
        {
            $error .= ' ' . $ex->reason;
        }
        echo $error . "\n";

        print_r($ex->getTrace());
        echo "\n";
    }

    protected function getLoginUid()
    {
        $verify = Tool_Input::clean('c', '_admin_session', TYPE_STR);
        $uid = Admin_Auth_Api::checkVerify($verify, Conf_Base::WEB_TOKEN_EXPIRED);
        if ($uid !== FALSE)
        {
            //if ($uid == 1004) $uid=1024;
            return $uid;
        }

        self::clearVerifyCookie();

        return FALSE;
    }

    protected function setSessionVerifyCookie($token, $expiredTime = 0)
    {
        $expiredTime = !empty($expiredTime) ? (time() + $expiredTime) : 0;
        setcookie("_admin_session", $token, $expiredTime, "/", Conf_Base::getAdminHost());
        setcookie('_admin_uid', $this->_uid, $expiredTime, '/', Conf_Base::getAdminHost());
    }

    protected static function clearVerifyCookie()
    {
        setcookie('_admin_session', '', -86400, '/', Conf_Base::getAdminHost());
        setcookie('_admin_uid', '', -86400, '/', Conf_Base::getAdminHost());
        setcookie('city_id', '', -86400, '/', Conf_Base::getAdminHost());

        setcookie('_admin_session', '', -86400, '/', Conf_Base::getBaseHost());
        setcookie('_admin_uid', '', -86400, '/', Conf_Base::getBaseHost());
        setcookie('city_id', '', -86400, '/', Conf_Base::getBaseHost());
    }

    protected function delegateTo($path)
    {
        chdir(ADMIN_HTDOCS_PATH . "/" . dirname($path));

        require_once ADMIN_HTDOCS_PATH . "/" . $path;
    }

    // 取仓库id
    protected function getWarehouseId()
    {
        $_wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $curCity = City_Api::getCity();
        if (empty($_wid))
        {
            if (empty($this->_user['city_wid_map'][$curCity['city_id']]))
            {
                $_wid = -1;
            }
            else
            {
                //$_wid = $this->_user['city_wid_map'][$curCity['city_id']][0];
                $_wid = count($this->_user['city_wid_map'][$curCity['city_id']])>1? 0: 
                            $this->_user['city_wid_map'][$curCity['city_id']][0];
            }
        }

        return $_wid;
    }

    protected function getDefaultWarehouse()
    {
        $cityInfo = City_Api::getCity();
        $widsOfCity = Conf_Warehouse::$WAREHOUSE_CITY[$cityInfo['city_id']];

        return !empty($widsOfCity) ? $widsOfCity[0] : 0;
    }

    /**
     * 获取可以查看的城市列表
     */
    protected function getAllowedCities4User()
    {
        $cityList = Conf_City::$CITY;
        $city_ids = explode(',', $this->_user['cities']);
        foreach ($cityList as $key => $item)
        {
            if(!in_array($key, $city_ids))
            {
                unset($cityList[$key]);
            }
        }
        return $cityList;
    }

    /**
     * 获取可以查看的所有仓库列表
     */
    protected function getAllAllowedWids4User($city = 0, $getOffLineWids=false)
    {
        $cityList = self::getAllowedCities4User();
        $wids = array();
        foreach ($cityList as $city_id => $city_name)
        {
            if($city > 0 && $city != $city_id)
            {
                continue;
            }
            if (array_key_exists($city_id, $this->_user['city_wid_map']))
            {
                $myWids = $this->_user['city_wid_map'][$city_id];

                $allWidInfos  = $getOffLineWids? Appconf_Warehouse::wid4Show(): Appconf_Warehouse::wid4OnLine();

                foreach($myWids as $_wid)
                {
                    if (!isset($allWidInfos[$_wid])) continue;

                    $wids[] = $_wid;
                }
            }
        }
        return $wids;
    }
    
    /**
     * 获取可以账号的可使用仓库.
     */
    protected function getAllowedWids4User($getOffLineWids=false)
    {
        $cityInfo = City_Api::getCity();
        
        $wids = array();
        if (array_key_exists($cityInfo['city_id'], $this->_user['city_wid_map']))
        {
            $myWids = $this->_user['city_wid_map'][$cityInfo['city_id']];
            
            $allWidInfos  = $getOffLineWids? Appconf_Warehouse::wid4Show(): Appconf_Warehouse::wid4OnLine();
            
            foreach($myWids as $_wid)
            {
                if (!isset($allWidInfos[$_wid])) continue;
                
                $wids[$_wid] = $allWidInfos[$_wid];
            }
        }
        
        return $wids;
    }

    /**
     * 获取可以查看的仓库列表.
     *
     * 根据当前登录者的权限，以及城市.
     *
     * @para int $type {0:allowed_all  1:allowed_new}
     *
     */
    protected function getAllowedWarehouses($type = 0)
    {
        $warehouses = array();

        if (empty($this->_user['wid']))
        {
            $wids = array();
            $city = City_Api::getCity();
            if (array_key_exists($city['city_id'], Conf_Warehouse::$WAREHOUSE_CITY))
            {
                $wids = Conf_Warehouse::$WAREHOUSE_CITY[$city['city_id']];
            }
            else
            {
                $wids = Conf_Warehouse::$WAREHOUSE_CITY[Conf_City::BEIJING];
            }

            foreach ($wids as $wid)
            {
                $warehouses[$wid] = Conf_Warehouse::$WAREHOUSES[$wid];
            }
        }
        else
        {
            $warehouses[$this->_user['wid']] = Conf_Warehouse::$WAREHOUSES[$this->_user['wid']];
        }

        if ($type == 1)
        {
            foreach ($warehouses as $wid => $name)
            {
                if (!Conf_Warehouse::isUpgradeWarehouse($wid))
                {
                    unset($warehouses[$wid]);
                }
            }
        }

        return $warehouses;
    }
}
