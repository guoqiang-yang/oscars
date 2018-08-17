<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    // 中间结果
    private $staffs;
    private $leftUsers;
    private $searchConf;
    private $cities;

    protected function getPara()
    {
        $this->searchConf = array(
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'mobile' => Tool_Input::clean('r', 'mobile', TYPE_STR),
            'role' => Tool_Input::clean('r', 'role', TYPE_UINT),
            'department' => Tool_Input::clean('r', 'department', TYPE_UINT),
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT),
            'status' => Conf_Base::STATUS_ALL,
            //'status' => !empty($_REQUEST['status'])&&$_REQUEST['status']==Conf_Base::STATUS_DELETED? 1: 0,
        );
    }

    protected function main()
    {
        $ret = Admin_Api::getStaffList(0, 1000, $this->searchConf);
        $roleList = Permission_Api::getAll();
        foreach ($roleList['list'] as $_role)
        {
            $this->roleMapping[$_role['department']][$_role['id']] = $_role['role'];
        }
        
        //城市列表unset掉香河
        $this->cities = Conf_City::$CITY;
        unset($this->cities[Conf_City::XIANGHE]);
        
        foreach ($ret['list'] as &$item)
        {
            $citysDesc = array();
            $citys = explode(',', $item['cities']);
            
            foreach($citys as $c)
            {
                $citysDesc[] = $this->cities[$c];
            }
            
            $item['city_desc'] = implode(',', $citysDesc);
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
        $roles = Permission_Api::getAll();
        $roles = $roles['list'];
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('staffs', $this->staffs);
        $this->smarty->assign('left_users', $this->leftUsers);
        $this->smarty->assign('role_list', $roles);
        $this->smarty->assign('cities', $this->cities);
        $this->smarty->assign('job_kinds', Conf_Admin::$JOB_KIND_DESC);
        $this->smarty->assign('department_list', Conf_Permission::$DEPAREMENT);
        $this->smarty->assign('role_mapping', json_encode($this->roleMapping));
        $this->smarty->display('admin/staff_list.html');
    }
}

$app = new App('pri');
$app->run();

