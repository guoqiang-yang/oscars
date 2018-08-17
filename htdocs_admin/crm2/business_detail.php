<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $bid;
    private $businessInfo;

    protected function getPara()
    {
        $this->bid = Tool_Input::clean('r', 'bid', TYPE_UINT);
    }

    protected function main()
    {
        $this->businessInfo = Business_Api::getBusinessInfoByBid($this->bid);
    }

    protected function outputBody()
    {

        $this->smarty->assign('business', $this->businessInfo['business']);
        $this->smarty->assign('customer_num', $this->businessInfo['customers']['total']);
        $this->smarty->assign('customer_list', $this->businessInfo['customers']['data']);
        $this->smarty->assign('sys_levels', Conf_User::$Business_Sys_Level_Descs);
        $this->smarty->display('crm2/business_detail.html');
    }
}

$app = new App();
$app->run();