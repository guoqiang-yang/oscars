<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $start;
    private $searchConf;
    private $num = 20;
    private $total;
    private $billList;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->searchConf = array(
            'bid' => Tool_Input::clean('r', 'bid', TYPE_UINT),
            'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
        );
    }

    protected function checkPara()
    {

    }

    protected function main()
    {
        $sellBillDao = new Finance_Seller_Bill();
        $this->billList = $sellBillDao->getList($this->searchConf, $this->total ,$this->start, $this->num);

        $this->addFootJs(array('js/apps/seller_bill.js'));
    }

    protected function outputBody()
    {
        $app = '/finance/seller_bill_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('bill_list', $this->billList);
        $this->smarty->assign('warehouse', Conf_Warehouse::getSellerWarehouse());
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('payment_list', Conf_Base::$PAYMENT_TYPES);
        $this->smarty->display('finance/seller_bill_list.html');
    }
}

$app = new App('pri');
$app->run();