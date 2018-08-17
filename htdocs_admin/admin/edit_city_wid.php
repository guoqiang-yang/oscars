<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $suid;
    private $staff;


    protected function getPara()
    {
        $this->suid = Tool_Input::clean('r', 'suid', TYPE_UINT);
    }

    protected function checkAuth()
    {
        parent::checkAuth('/admin/edit_staff');
    }

    protected function main()
    {
        $this->staff = Admin_Api::getStaff($this->suid);
        $this->addFootJs(array('js/apps/staff.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $this->smarty->assign('staff', $this->staff);
        $this->smarty->assign('all_city_wids', Conf_Warehouse::$WAREHOUSE_CITY);
        $this->smarty->assign('cities', Conf_City::$CITY);
        $this->smarty->assign('wid_list', Conf_Warehouse::$WAREHOUSES);
        $this->smarty->display('admin/edit_city_wid.html');
    }
}

$app = new App('pri');
$app->run();
