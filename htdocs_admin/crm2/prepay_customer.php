<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $num = 50;
    private $start;
    private $stat;
    private $customerList;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
    }

    protected function main()
    {
        $ret = Finance_Api::statPrebuyCustomer($this->start, $this->num);
        $this->stat = $ret['stat'];
        $this->customerList = $ret['customer'];
    }

    protected function outputBody()
    {
        $app = '/crm2/prepay_customer.php';
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->stat['total_customers'], $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('stat', $this->stat);
        $this->smarty->assign('customer_list', $this->customerList);
        $this->smarty->assign('sysLevels', Conf_User::$Customer_Sys_Level_Descs);
        $this->smarty->assign('total', $this->stat['total_customers']);

        $this->smarty->display('crm2/prepay_customer.html');
    }
}

$app = new App();
$app->run();