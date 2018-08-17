<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $cities;
    private $customer;

    protected function main()
    {
        //销售添加客户默认城市是销售所在城市
        $this->customer['city_id'] = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW) ? $this->_user['city_id'] : '';
        $this->customer['canIdentity'] = true;
        $this->cities = Conf_City::$CITY;
        unset($this->cities[Conf_City::XIANGHE]);

        $this->addFootJs(array(
                             'js/apps/crm2.js',
                             'js/core/area.js'
                         ));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        if ($this->checkPermission('crm2_update_customer_suser'))
        {
            $salesList = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW, 0);
            $this->smarty->assign('sales_list', $salesList);
        }

        $this->smarty->assign('customer', $this->customer);
        $this->smarty->assign('cities', $this->cities);
        $this->smarty->assign('city', Tool_Array::jsonEncode(Conf_Area::$CITY));
        $this->smarty->assign('distinct', Tool_Array::jsonEncode(Conf_Area::$DISTRICT));
        $this->smarty->assign('area', Tool_Array::jsonEncode(Conf_Area::$AREA));
        $this->smarty->assign('identitys', Conf_User::$Crm_Identity);
        $this->smarty->assign('province', Conf_Area::$Province);
        $this->smarty->assign('user_source', Conf_User::$Introduce_Source);
        $this->smarty->assign('rival_descs', Conf_User::$Desc_In_Rival);
        $this->smarty->assign('sys_levels', Conf_User::$Customer_Sys_Level_Descs);

        $this->smarty->display('crm2/new_customer.html');
    }
}

$app = new App('pri');
$app->run();
