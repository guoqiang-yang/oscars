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
    private $roleId;
    private $permissionList;

    protected function checkAuth($permission='')
    {
        parent::checkAuth('/admin/edit_permission');
    }

    protected function getPara()
    {
        $this->roleId = Tool_Input::clean('r', 'role_id', TYPE_UINT);
        $this->permissionList = Tool_Input::clean('r', 'permission_list', TYPE_STR);
    }

    protected function checkPara()
    {
        if (empty($this->roleId) || empty($this->permissionList))
        {
            throw new Exception('common:params error');
        }
    }

    protected function main()
    {
//        $modules = Conf_Admin_Page::$MODULES;
//        $permissionArr = array();

//        foreach ($modules as $fkey => $fitem)
//        {
//            foreach ($fitem['pages'] as $skey => $sitem)
//            {
//                if (!empty($sitem['buttons']))
//                {
//                    $pkey = $sitem['key'];
//                    foreach ($sitem['buttons'] as $tkey => $titem)
//                    {
//                        if (in_array($titem['key'], $this->permissionList))
//                        {
//                            $permissionArr[$pkey][] = $titem['key'];
//                        }
//                    }
//                }
//            }
//        }
        
        
        $this->_calNeedUpPermission();
        
        $update = array(
            'permission' => $this->permissionList,
        );
        Permission_Api::update($this->roleId, $update);
    }

    protected function outputPage()
    {
        $result = array('role_id' => $this->roleId);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
    
    private function _calNeedUpPermission()
    {
        $relPermissionList = Permission_Api::getRelRolePermission($this->roleId);
        
        $waitUpPermissionList = json_decode($this->permissionList, true);
        
        foreach($waitUpPermissionList as $key => &$_pr)
        {
            if (!array_key_exists($key, $relPermissionList)) continue;
            
            $_pr = array_diff($_pr, $relPermissionList[$key]);
            
            if (empty($_pr))
            {
                unset($waitUpPermissionList[$key]);
            }
        }
        
        $this->permissionList = json_encode($waitUpPermissionList);
    }
}

$app = new App('pri');
$app->run();