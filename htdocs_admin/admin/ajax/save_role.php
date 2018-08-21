<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 16/7/13
 * Time: 16:35
 */
include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $id;
    private $role;
    private $department;
    private $rkey;
    private $relRoles;
    
    private $isNew;

    protected function checkAuth($permission='')
    {
        parent::checkAuth('/admin/edit_role');
    }

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->role = Tool_Input::clean('r', 'role', TYPE_STR);
        $this->rkey = Tool_Input::clean('r', 'rkey', TYPE_STR);
        $this->department = Tool_Input::clean('r', 'department', TYPE_UINT);
        $this->relRoles = Tool_Input::clean('r', 'rel_role', TYPE_STR);
    }

    protected function main()
    {
        $this->_calRelRoles();
        
        if (empty($this->id))
        {
            $this->id = Permission_Api::add($this->role, $this->department, $this->rkey, $this->_uid, $this->relRoles);
            
            $this->isNew = TRUE;
        }
        else
        {
            $update = array('role' => $this->role, 'department' => $this->department, 'rkey' => $this->rkey, 'rel_role' => $this->relRoles);
            Permission_Api::update($this->id, $update);
            
            $this->isNew = FALSE;
        }
    }

    protected function outputPage()
    {
        $result = array('id' => $this->id, 'is_new' => $this->isNew);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
    
    /**
     * 计算关联的角色.
     * 
     */
    private function _calRelRoles()
    {
        // 去除空格等字符，转换分隔符
        $this->relRoles = preg_replace('#\s#', '', $this->relRoles);
        $this->relRoles = str_replace('，', ',', $this->relRoles);
        
        if (empty($this->relRoles)) return;
       
        $relRoleIds = Permission_Api::getAllRoles(explode(',', $this->relRoles));
        
        $this->relRoles = implode(',', array_diff($relRoleIds, array($this->id)));
    }
}

$app = new App('pri');
$app->run();