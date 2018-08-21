<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $id;
    private $allPermissions = array();
    private $permissionList = array();
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
        $roleInfo = Permission_Api::get($this->id);
        
        if (!empty($roleInfo['permission']))
        {
            $this->permissionList = json_decode($roleInfo['permission'], true);
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
