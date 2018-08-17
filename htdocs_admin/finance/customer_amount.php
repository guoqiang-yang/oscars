<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private static $num = 20;
    private $start;
    private $search;
    private $amountList;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->search = array(
            'mobile' => Tool_Input::clean('r', 'mobile', TYPE_UINT),
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'sales_suid' => Tool_Input::clean('r', 'sales_suid', TYPE_UINT),
        );
    }

    protected function main()
    {
        $order = 'account_amount';
        $this->amountList = Crm2_Api::getCustomerListForAdmin($this->search, $this->_user, $order, $this->start, self::$num);
    }

    protected function outputBody()
    {
        $app = '/finance/customer_amount.php?' . http_build_query($this->search);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->amountList['total'], $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('search', $this->search);
        $this->smarty->assign('customer_list', $this->amountList['data']);
        $this->smarty->assign('total', $this->list['total']);
        $this->smarty->assign('sum', $this->list['sum']);
        $this->smarty->assign('salesman_list', Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW));
        $this->smarty->assign('business', $this->business);

        $this->smarty->display('finance/customer_amount.html');
    }
}

$app = new App();
$app->run();