<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $start;
    private $searchConf;
    private $order;
    private $num = 20;
    private $total;
    private $customers;
    private $salesmanList;
    private $allSalerList;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);

        $this->searchConf = array(
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'mobile' => Tool_Input::clean('r', 'mobile', TYPE_STR),
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'sale_status' => Tool_Input::clean('r', 'sale_status', TYPE_UINT),
            'sales_suid' => Tool_Input::clean('r', 'sales_suid', TYPE_UINT),
        );

        if (Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW) && empty($this->searchConf['sales_suid']))
        {
            $this->searchConf['sales_suid'] = $this->_uid;
        }

        if (!Str_Check::checkMobile($this->searchConf['mobile']))
        {
            $this->searchConf['mobile'] = '';
        }
    }

    protected function main()
    {
        $customerList = Crm2_Api::getCustomerList($this->searchConf, $this->_user, $this->order, $this->start, $this->num);
        
        $this->total = $customerList['total'];
        $this->customers = $customerList['data'];
        
        $this->allSalerList = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW, 0);
        foreach ($this->allSalerList as $saler)
        {
            if ($saler['status'] == 0)
            {
                $this->salesmanList[] = $saler;
            }
        }
        
        $this->addFootJs(array('js/apps/crm2.js',));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $baseUrl = '/crm2/customer_list.php?' . http_build_query($this->searchConf);
        $app = $baseUrl . '&order=' . $this->order;
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('base_url', $baseUrl);
        $this->smarty->assign('pageHtml', $pageHtml);

        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('customers', $this->customers);
        $this->smarty->assign('salesman_list', $this->salesmanList);
        $this->smarty->assign('sale_status', Conf_User::$Customer_Sale_Status);
        $this->smarty->assign('crm_identity', Conf_User::$Crm_Identity);
        $this->smarty->assign('status_list', Conf_Base::getCustomerStatus());
        $this->smarty->assign('sys_levels', Conf_User::$Customer_Sys_Level_Descs);
        
        $this->smarty->assign('city_list', Conf_City::getAllCities('cn'));

        $this->smarty->display('crm2/customer_list.html');
    }

}

$app = new App('pri');
$app->run();

