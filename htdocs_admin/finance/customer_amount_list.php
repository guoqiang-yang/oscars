<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private static $num = 20;
    private $start;
    private $search;
    private $amountList;
    private $customer;

    protected function getPara()
    {
        $this->search = array(
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
            'saler_suid' => Tool_Input::clean('r', 'saler_suid', TYPE_UINT),
            'btime' => Tool_Input::clean('r', 'btime', TYPE_STR),
            'etime' => Tool_Input::clean('r', 'etime', TYPE_STR),
        );

        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
    }

    protected function main()
    {
        $this->amountList = Finance_Api::getCustomerAmountList($this->search, $this->start, self::$num);
        $cc = new Crm2_Customer();

        if (!empty($this->search['cid']))
        {
            $this->customer = $cc->get($this->search['cid']);
        }

        $this->addFootJs(array('js/apps/finance.js'));
        $this->addCss(array());

    }

    protected function outputBody()
    {

        $app = '/finance/customer_amount_list.php?' . http_build_query($this->search);
        $pageHtml = Str_Html::getSimplePage($this->start, self::$num, $this->amountList['total'], $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('amount_list', $this->amountList['data']);
        $this->smarty->assign('total', $this->amountList['total']);
        $this->smarty->assign('search', $this->search);
        $this->smarty->assign('payment_types', Conf_Base::getPaymentTypes());
        $this->smarty->assign('cashback_rate', Conf_Finance::CUSTOMER_CASHBACK_RATE);
        $this->smarty->assign('type_descs', Conf_Finance::$Crm_AMOUNT_TYPE_DESCS);
        $this->smarty->assign('customer', $this->customer);

        $this->smarty->display('finance/customer_amount_list.html');
    }
}

$app = new App();
$app->run();