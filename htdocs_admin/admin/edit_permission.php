<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $id;
    private $allPermissions = array();
    private $permissionList = array();
    private $relPermissionList = array();   //关联权限，不能编辑
    private $isNew;

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->isNew = Tool_Input::clean('r', 'is_new', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->id))
        {
            header('Location: /admin/role_list.php');
        }
    }

    protected function main()
    {
        $role = Permission_Api::get($this->id);
        
        if ($this->isNew)
        {
            $defaultPermissionList = Conf_Permission::$DEFAULT_PERMISSION[$role['department']];
            if (!empty($defaultPermissionList))
            {
                foreach ($defaultPermissionList as $fkey => $fval)
                {
                    if ($fval == '*')
                    {
                        foreach (Conf_Admin_Page::$MODULES[$fkey]['pages'] as $flist)
                        {
                            foreach ($flist['buttons'] as $fitem)
                            {
                                $this->permissionList[] = $fitem['key'];
                            }
                        }
                    }
                    else
                    {
                        foreach ($fval as $skey => $sval)
                        {
                            if ($sval == '*')
                            {
                                foreach (Conf_Admin_Page::$MODULES[$fkey]['pages'] as $flist)
                                {
                                    if ($flist['key'] == $skey)
                                    {
                                        foreach ($flist['buttons'] as $sitem)
                                        {
                                            $this->permissionList[] = $sitem['key'];
                                        }
                                    }
                                }
                            }
                            else
                            {
                                foreach ($sval as $p)
                                {
                                    $this->permissionList[] = $p;
                                }
                            }
                        }
                    }
                }
            }
        }
        else
        {
            if (!empty($role['permission']))
            {
                $this->permissionList = json_decode($role['permission'], true);
            }
        }
        
        $relPermissionList = Permission_Api::getRelRolePermission($this->id);
        
        // 将已选择的权限，关联权限标记到全部权限列表
        $this->allPermissions = Conf_Admin_Page::$MODULES;
        
        foreach($this->allPermissions as  &$module)
        {
            foreach($module['pages'] as &$page)
            {
                $_key = $page['key'];
                
                foreach($page['buttons'] as &$buttion)
                {
                    // 自有权限
                    $buttion['is_owned'] = isset($this->permissionList[$_key]) && in_array($buttion['key'], $this->permissionList[$_key])? 1: 0;
                    
                    // 关联权限
                    $buttion['is_rel'] = isset($relPermissionList[$_key]) && in_array($buttion['key'], $relPermissionList[$_key])? 1: 0;
                }
            }
        }
        
        $this->addFootJs(array('js/apps/permission.js'));
    }

    protected function outputBody()
    {
        $this->smarty->assign('list', $this->allPermissions);
        $this->smarty->assign('id', $this->id);
        $this->smarty->assign('permission_list', $this->permissionList);

        $this->smarty->display('admin/edit_permission.html');
    }
    
    
}

$app = new App('pri');
$app->run();
