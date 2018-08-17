<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $id;
    private $role;
    private $info;
    private $relRoleInfos = array();
    
    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->info = array(
            'role' => Tool_Input::clean('r', 'role', TYPE_STR),
            'department' => Tool_Input::clean('r', 'department', TYPE_UINT),
        );
    }

    protected function main()
    {
        if (!empty($this->id))
        {
            $this->role = Permission_Api::get($this->id);
            
            if (!empty($this->role['rel_role']))
            {
                $this->relRoleInfos = Permission_Api::getBulk(explode(',', $this->role['rel_role']));
            }
        }
        
        $this->addFootJs(array('js/apps/permission.js'));
    }

    protected function outputBody()
    {
        $this->smarty->assign('departments', Conf_Permission::$DEPAREMENT);
        $this->smarty->assign('role', $this->role);
        $this->smarty->assign('rel_roles', $this->relRoleInfos);

        $this->smarty->display('admin/edit_role.html');
    }
}

$app = new App('pri');
$app->run();
