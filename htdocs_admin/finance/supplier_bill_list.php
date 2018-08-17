<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $num = 20;
    private $start;
    private $searchConf;
    private $billList = array();

    protected function getPara()
    {
        $this->searchConf = array(
            'sid' => Tool_Input::clean('r', 'sid', TYPE_UINT),
            'from_date' => Tool_Input::clean('r', 'from_date', TYPE_STR),
            'end_date' => Tool_Input::clean('r', 'end_date', TYPE_STR),
            'payment_type' => Tool_Input::clean('r', 'payment_type', TYPE_UINT),
            'paid_source' => Tool_Input::clean('r', 'paid_source', TYPE_UINT),
        );
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
    }

    protected function main()
    {
        $this->billList = Finance_Api::getSupplierBillList($this->searchConf, $this->start, $this->num);
        $this->addFootJs(array(
                             'js/apps/order.js',
                             'js/apps/finance.js'
                         ));
    }

    protected function outputBody()
    {
        $queryStr = http_build_query($this->searchConf);
        $app = '/finance/supplier_bill_list.php?' . $queryStr;
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->billList['total'], $app);

        $this->smarty->assign('queryStr', $queryStr);
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('bill_list', $this->billList['list']);
        $this->smarty->assign('total', $this->billList['total']);
        $this->smarty->assign('prices', $this->billList['prices']);
        $this->smarty->assign('sid', $this->searchConf['sid']);
        $this->smarty->assign('from_date', $this->searchConf['from_date']);
        $this->smarty->assign('end_date', $this->searchConf['end_date']);
        $this->smarty->assign('payment_type', $this->searchConf['payment_type']);
        $this->smarty->assign('paid_source', $this->searchConf['paid_source']);
        $this->smarty->assign('st_desc', Conf_Money_Out::$STATUS_DESC);
        $this->smarty->assign('paid_sources', Conf_Finance::$MONEY_OUT_PAID_TYPES);
        $this->smarty->assign('payment_types', Conf_Stock::$PAYMENT_TYPES);

        $this->smarty->display('finance/supplier_bill_list.html');
    }
}

$app = new App('pri');
$app->run();