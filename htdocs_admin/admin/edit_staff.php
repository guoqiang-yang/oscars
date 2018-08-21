<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $suid;
    private $info;
    private $submit;
    private $staff;
    private $cities;
    private $nocache;

    protected function getPara()
    {
        $this->suid = Tool_Input::clean('r', 'suid', TYPE_UINT);
        $this->submit = Tool_Input::clean('r', 'submit', TYPE_UINT);
        $this->info = array(
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'mobile' => Tool_Input::clean('r', 'mobile', TYPE_STR),
            'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
            'leader_suid' => Tool_Input::clean('r', 'leader_suid', TYPE_UINT),
            'cities' => Tool_Input::clean('r', 'cities', TYPE_UINT),
            'wids' => Tool_Input::clean('r', 'wids', TYPE_UINT),
            'department' => Tool_Input::clean('r', 'department', TYPE_UINT),
            'roles' => Tool_Input::clean('r', 'roles', TYPE_UINT),
        );
        
        $this->nocache = Tool_Input::clean('r', 'nocache', TYPE_UINT);
    }

    protected function main()
    {
        if ($this->submit)
        {
            if (empty($this->suid))
            {
                $staff = Admin_Api::getStaffByMobile($this->info['mobile']);
                
                if (!empty($staff))
                {
                    echo 'mobile exists!!'; exit;
                }

                $dfPasswd = '123456';
                $this->info['salt'] = rand(1000,9999);
                $this->info['password'] = Admin_Auth_Api::createPasswdMd5($dfPasswd, $this->info['salt']);
                
                $py = Str_Chinese::hz2py2($this->info['name'], true);
                $this->info['pinyin'] = str_replace(' ', '', $py);
                
                Admin_Api::addStaff($this->info);
            }
            else
            {
                Admin_Api::updateStaff($this->suid, $this->info);
            }

            header('Location: /admin/staff_list.php');
        }
        else
        {
            if (!empty($this->suid))
            {
                $this->staff = Admin_Api::getStaff($this->suid, Conf_Base::STATUS_ALL, $this->nocache);
            }
        }
        $this->cities = Conf_City::getAllCities('cn');
        
        $this->addFootJs(array('js/apps/staff.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $this->smarty->assign('cities', $this->cities);
        $this->smarty->assign('staff', $this->staff);
        $this->smarty->assign('warehouses', Conf_Warehouse::getNameOfWarehouses());
        $this->smarty->assign('departments', Conf_Permission::getDeparement());
        
        $pr = new Permission_Role();
        $roleList = $pr->getListWhere('status=0', array('id','role'), 0,100);
        $this->smarty->assign('role_list', $roleList['list']);
        
        $this->smarty->display('admin/edit_staff.html');
    }
}

$app = new App('pri');
$app->run();
