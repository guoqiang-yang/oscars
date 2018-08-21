<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    // 中间结果
    private $staffs;
    private $leftUsers;
    private $searchConf;

    protected function getPara()
    {
        $this->searchConf = array(
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'mobile' => Tool_Input::clean('r', 'mobile', TYPE_STR),
            'role' => Tool_Input::clean('r', 'role', TYPE_UINT),
            'department' => Tool_Input::clean('r', 'department', TYPE_UINT),
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT),
        );
    }

    protected function main()
    {
        $staff = Admin_Api::getStaffsByWhere($this->searchConf, 0, 1000);
        
        $roleList = Permission_Api::getAll();
        
        foreach ($staff['list'] as &$item)
        {
            $staffRoles = array();
            
            $roleIds = explode(',', $item['roles']);
            foreach($roleIds as $_roleId)
            {
                if (array_key_exists($_roleId, $roleList['list']))
                {
                    $staffRoles[] = $roleList['list'][$_roleId]['role'];
                }
            }
            
            $item['_roles'] = !empty($staffRoles)? implode(',', $staffRoles): '-';
            
            if ($item['status'] == Conf_Base::STATUS_NORMAL)
            {
                $this->staffs[] = $item;
            }
            else
            {
                $this->leftUsers[] = $item;
            }
        }
        
		$this->addFootJs(array('js/apps/staff.js'));
		$this->addCss(array());
	}

    protected function outputBody()
    {
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('staffs', $this->staffs);
        $this->smarty->assign('left_users', $this->leftUsers);
        $this->smarty->assign('cities', Conf_City::getAllCities());
        $this->smarty->assign('department_list', Conf_Permission::getDeparement());
        
        
        $this->smarty->display('admin/staff_list.html');
    }
}

$app = new App('pri');
$app->run();

