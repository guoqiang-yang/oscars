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
            'role' => Tool_Input::clean('r', 'role', TYPE_UINT),
            'mobile' => Tool_Input::clean('r', 'mobile', TYPE_STR),
            'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
            'kind' => Tool_Input::clean('r', 'kind', TYPE_UINT),
            'leader_suid' => Tool_Input::clean('r', 'leader_suid', TYPE_UINT),
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT),
            'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
            'department' => Tool_Input::clean('r', 'department', TYPE_UINT),
            'ding_id' => Tool_Input::clean('r', 'ding_id', TYPE_STR),
            'ce_agent_num' => Tool_Input::clean('r', 'ce_agent_num', TYPE_STR),
            'ce_agent_pass' => Tool_Input::clean('r', 'ce_agent_pass', TYPE_STR),
            'ce_agent_phone' => Tool_Input::clean('r', 'ce_agent_phone', TYPE_STR),
        );
        
        $this->nocache = Tool_Input::clean('r', 'nocache', TYPE_UINT);
    }

    protected function main()
    {
        if ($this->submit)
        {
            if (!empty($this->suid))
            {
                $this->staff = Admin_Api::getStaff($this->suid, Conf_Base::STATUS_ALL);
            }
            //其他角色不保存
            if (!Admin_Role_Api::hasRole($this->staff, Conf_Admin::ROLE_SALES_NEW))
            {
                unset($this->info['kind']);
            }

            //获取用户的姓名拼音
            $py = Str_Chinese::hz2py2($this->info['name'], true);
            $this->info['pinyin'] = str_replace(' ', '', $py);
            if (empty($this->suid))
            {
                $staff = Admin_Api::getStaffByMobile($this->info['mobile']);
                if (!empty($staff))
                {
                    echo 'mobile exists!!';
                    exit;
                }

                $this->info['password'] = 'e0b55821fecb1aed721cbc6e15b85854';
                $this->info['salt'] = 2754;
                Admin_Api::addStaff($this->info);
            }
            else
            {
                Admin_Api::updateStaff($this->suid, $this->info);
            }

            header('Location: /admin/staff_list.php');
        }

        if (!empty($this->suid))
        {
            $this->staff = Admin_Api::getStaff($this->suid, Conf_Base::STATUS_ALL, $this->nocache);
        }
        $this->cities = Conf_City::$CITY;
        //城市列表unset掉香河
        unset($this->cities[200]);

        $this->addFootJs(array('js/apps/staff.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $leaders = Admin_Api::getSalesLeaders($this->suid, $this->info['city_id']);
        
        $this->smarty->assign('cities', $this->cities);
        $this->smarty->assign('leaders', $leaders);
        $this->smarty->assign('staff', $this->staff);
        $this->smarty->assign('role_list', Conf_Admin::$Role_Descs);
        $this->smarty->assign('kind_list', Conf_Admin::$JOB_KIND_DESC);
        $this->smarty->assign('wareHouses', Conf_Warehouse::$WAREHOUSES);
        $this->smarty->assign('departments', Conf_Permission::$DEPAREMENT);

        $this->smarty->display('admin/edit_staff.html');
    }
}

$app = new App('pri');
$app->run();
