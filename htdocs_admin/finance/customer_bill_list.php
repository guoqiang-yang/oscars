<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $num = 20;
    private $start;
    private $searchConf;
    private $billList = array();
    private $accountBalance = 0;
    private $hasRole = FALSE;
    private static $EX_SUIDS = array(
            1018,
            1022,
            1037,
            1153,
            1294
        );

    protected function getPara()
    {
        $this->searchConf = array(
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'from_date' => Tool_Input::clean('r', 'from_date', TYPE_STR),
            'end_date' => Tool_Input::clean('r', 'end_date', TYPE_STR),
            'payment_type' => Tool_Input::clean('r', 'payment_type', TYPE_UINT),
            'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
        );
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);

        if (!isset($_REQUEST['type']))
        {
            $this->searchConf['type'] = Conf_Base::STATUS_ALL;
        }
        else
        {
            $this->searchConf['type'] = Tool_Input::clean('r', 'type', TYPE_UINT);
        }
    }

    protected function main()
    {
        $this->billList = Finance_Api::getCustomBillList($this->searchConf, $this->start, $this->num);
        $this->addFootJs(array('js/apps/finance.js'));

        $this->accountBalance = $this->billList['list'][0]['_customer']['account_balance'];

        if (in_array($this->_uid, self::$EX_SUIDS))
        {
            $this->hasRole = TRUE;
        }
    }

    protected function outputBody()
    {
        $app = '/finance/customer_bill_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->billList['total'], $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('bill_list', $this->billList['list']);
        $this->smarty->assign('customer', $this->billList['customer']);
        $this->smarty->assign('total', $this->billList['total']);
        $this->smarty->assign('cid', $this->searchConf['cid']);
        $this->smarty->assign('from_date', $this->searchConf['from_date']);
        $this->smarty->assign('end_date', $this->searchConf['end_date']);
        $this->smarty->assign('payment_type', $this->searchConf['payment_type']);
        $this->smarty->assign('type', $this->searchConf['type']);
        $this->smarty->assign('wid', $this->searchConf['wid']);
        $this->smarty->assign('st_desc', Conf_Money_In::$STATUS_DESC);
        $this->smarty->assign('payment_types', Conf_Base::getPaymentTypes());
        $this->smarty->assign('cashback_rate', Conf_Money_In::CUSTOMER_CASHBACK_RATE);
        $this->smarty->assign('account_balance', $this->accountBalance);
        $this->smarty->assign('has_role', $this->hasRole);
        $this->smarty->assign('allowed_warehouses', Conf_Warehouse::$WAREHOUSES);

        $this->smarty->display('finance/customer_bill_list.html');
    }
}

$app = new App('pri');
$app->run();