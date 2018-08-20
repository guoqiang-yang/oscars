<?php

/**
 * 管理运营后台 - Web基类
 */
class App_Admin_Web extends Base_App
{
    
    protected $lgmode;        //页面逻辑模式 -- pub (公开页面,不需登录); pri (私有页面,需要登录)
    protected $smarty;        //smarty 工具对象
   
    //权限数据
    protected $permissions = array();
    
    
    function __construct($lgmode = 'pri', $tmplpath = ADMIN_TEMPLATE_PATH)
    {
        $this->lgmode = $lgmode;
        
        $this->smarty = new Tool_Smarty($tmplpath);
    }

    protected function checkAuth()
    {
        //check Login
        $verify = Tool_Input::clean('c', Conf_Base::COKEY_VERIFY_SA, TYPE_STR);
        
        $checkRet = Admin_Auth_Api::checkVerify($verify);
        
        if (!empty($checkRet['uid']))
        {
            $this->setCurUid($checkRet['uid']);
            $this->setCurUser($checkRet['user']);
        }
        else
        {
            self::clearVerifyCookie();
            
            return false;
        }

        //选择的城市
        $currCityId = Tool_Input::clean('c', Conf_Base::COKEY_CITY_SA, TYPE_INT);
        if (empty($currCityId))
        {
            $currCityId = $this->_user['_city_ids'][0];
            setcookie(Conf_Base::COKEY_CITY_SA, $currCityId, 86400, '/', Conf_Base::getAdminHost());
        }
        
        $this->setCurCityId($currCityId);
    }
    
    protected function setCommonPara()
    {
        $this->smarty->assign('_wwwHost', ADMIN_HOST);
        $this->smarty->assign('_uid', $this->_uid);
        $this->smarty->assign('_user', $this->_user);

        if ($this->_uid)
        {
            $this->smarty->assign('_permissions', $this->permissions);
        }
        
    }

    protected function free()
    {
        Data_DB::free();
        
        $this->_showDBLog();
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
    
    protected function checkPermission($permission = '')
    {
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
        if (!empty($this->permissions)) return;

        $permissions = array();
        
        // 超级管理员
        if (1 || in_array($this->_uid, Conf_Admin::$SUPER_ADMINER))
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
            $permissions = Permission_Api::getAllPermission($roleIds, $pages);
        }
        
        //锁库
//        $lockedRet = Conf_Warehouse::isLockedWarehouse($this->_user['wid']);
//        if ($lockedRet['st'])
//        {
//            $permissions = array_diff($permissions, Conf_Permission::$Except_Pages_4_Locked_Warehouse);
//        }
        
        foreach ($permissions as $p)
        {
            $this->permissions[$p] = 1;
        }
    }
    
    protected function setCookie4LoginSucc($cookies, $expiredTime=0)
    {
        foreach($cookies as $coKey => $coVal)
        {
            setcookie($coKey, $coVal,   time()+$expiredTime, '/', Conf_Base::getAdminHost());
        }
    }


    protected static function clearVerifyCookie()
    {
        setcookie(Conf_Base::COKEY_VERIFY_SA, '', -86400, '/', Conf_Base::getAdminHost());
        setcookie(Conf_Base::COKEY_SUID_SA,   '', -86400, '/', Conf_Base::getAdminHost());
        setcookie(Conf_Base::COKEY_CITY_SA,   '', -86400, '/', Conf_Base::getAdminHost());
    }

    protected function delegateTo($path)
    {
        chdir(ADMIN_HTDOCS_PATH . "/" . dirname($path));

        require_once ADMIN_HTDOCS_PATH . "/" . $path;
    }
    
    /**
     * 慢查询跟踪.
     */
    private function _showDBLog()
    {
        global $HC_SQL_EXTIMES;
        
        if (empty($HC_SQL_EXTIMES)) return;
        
        $slowLog = '';
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

        if (!empty($_REQUEST['debug']) && $_REQUEST['debug'] == 2)
        {
            print_r($HC_SQL_EXTIMES);
        }
    }

}
