<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $suid;
    private $roles;
    private $staff;
    private $departmentId;

    protected function getPara()
    {
        $this->suid = Tool_Input::clean('r', 'suid', TYPE_UINT);
        $this->departmentId = Tool_Input::clean('r', 'suid', TYPE_UINT);
    }

    protected function main()
    {
        $roles = Permission_Api::getAll();

        $this->staff = Admin_Api::getStaff($this->suid);
        
        $rids = array();
        if (!empty($this->staff['roles']))
        {
            $rids = array_unique(explode(',', $this->staff['roles']));
        }
        
        $roleList = array();
        foreach($roles['list'] as $role)
        {
            $deprtment = $role['department'];
            $deprtmentName = $deprtment ? Conf_Permission::$DEPAREMENT[$deprtment] : '通用角色';
            
            //自己部门权限
            if ($deprtment==0 || $deprtment==$this->staff['department'])
            {
                $this->roles['self'][$deprtment]['department_name'] = $deprtmentName;
                $this->roles['self'][$deprtment]['role_list'][] = array(
                    'role_id' => $role['id'],
                    'role_name' => $role['role'],
                    'has_role' => in_array($role['id'], $rids) ? TRUE : FALSE,
                );
            }
            else    //其他部门权限
            {
                $this->roles['other'][$deprtment]['department_name'] = $deprtmentName;
                $this->roles['other'][$deprtment]['role_list'][] = array(
                    'role_id' => $role['id'],
                    'role_name' => $role['role'],
                    'has_role' => in_array($role['id'], $rids) ? TRUE : FALSE,
                );
            }
            
        }
        
        $this->addFootJs(array('js/apps/permission.js'));
    }

    protected function outputBody()
    {
        $this->smarty->assign('departments', Conf_Permission::$DEPAREMENT);
        $this->smarty->assign('roles', $this->roles);
        $this->smarty->assign('staff', $this->staff);

        $this->smarty->display('admin/edit_staff_role.html');
    }
}

$app = new App('pri');
$app->run();
