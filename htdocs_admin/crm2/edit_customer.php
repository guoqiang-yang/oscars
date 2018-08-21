<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $cid;
    
    private $customer;
    private $users;
    private $cities;

    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
    }

    protected function main()
    {
        $ret = Crm2_Api::getCustomerInfo($this->cid);
        $this->customer = $ret['customer'];
        $this->users = $ret['users'];

        $this->addFootJs(array(
                             'js/apps/crm2.js',
                             'js/core/area.js'
                         ));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $salesList = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW, 0);
        
        $this->smarty->assign('cid', $this->cid);
        $this->smarty->assign('sources', Conf_User::getCustomerSource());
        $this->smarty->assign('sales_list', $salesList);
        $this->smarty->assign('cities', Conf_City::getAllCities('cn'));
        $this->smarty->assign('identitys', Conf_User::$Crm_Identity);
        $this->smarty->assign('customer', $this->customer);
        $this->smarty->assign('users', $this->users);
        $this->smarty->assign('sys_levels', Conf_User::$Customer_Sys_Level_Descs);
        $this->smarty->assign('province', Conf_Area::$Province);
        
        $isMyCustomer = Crm2_Api::isMyCustomer($this->customer, $this->_user);
        $this->smarty->assign('can_edit', $isMyCustomer['can_edit']);
        
        $this->smarty->display('crm2/edit_customer.html');
    }
}

$app = new App('pri');
$app->run();
