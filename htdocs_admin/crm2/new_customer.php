<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $cities;
    private $customer;

    protected function main()
    {
        //销售添加客户默认城市是销售所在城市
        $this->customer['city_id'] = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW) ? $this->_user['cities'] : '';
        $this->cities = Conf_City::getAllCities('cn');

        $this->addFootJs(array(
                             'js/apps/crm2.js',
                             'js/core/area.js'
                         ));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $salesList = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW, 0);
        $this->smarty->assign('sales_list', Tool_Array::list2Map($salesList, 'suid'));
        
        $this->smarty->assign('cid', 0);
        $this->smarty->assign('customer', $this->customer);
        $this->smarty->assign('cities', $this->cities);
        $this->smarty->assign('identitys', Conf_User::$Crm_Identity);
        $this->smarty->assign('sources', Conf_User::getCustomerSource());
        $this->smarty->assign('sys_levels', Conf_User::$Customer_Sys_Level_Descs);

        $this->smarty->display('crm2/edit_customer.html');
    }
}

$app = new App('pri');
$app->run();
