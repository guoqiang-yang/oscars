<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $sid;
    private $searchConf;
    private $stockInList;
    private $supplier;
    private $prices = array(
            'total' => 0,
            'refund' => 0,
            'had_paid' => 0,
            'final' => 0,
        );

    protected function getPara()
    {
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->searchConf = array(
            'btime' => Tool_Input::clean('r', 'btime', TYPE_STR),
            'etime' => Tool_Input::clean('r', 'etime', TYPE_STR),
            'paid' => Conf_Stock_In::FINANCE_ACCOUNT,
        );
    }

    protected function main()
    {
        if (empty($this->sid))
        {
            return;
        }

        $field = array('*');
        $order = 'order by id';
        $this->stockInList = Warehouse_Api::getSupplierProductList($this->sid, $this->searchConf, $field, $order, 0, 0);

        $this->supplier = Warehouse_Api::getSupplier($this->sid);

        foreach ($this->stockInList['data'] as $one)
        {
            $this->prices['total'] += $one['price'];
            $this->prices['refund'] += $one['refund_price'];
            $this->prices['had_paid'] += $one['real_amount'];
        }
        $this->prices['final'] = $this->prices['total'] - $this->prices['refund'] - $this->prices['had_paid'];

        $this->addFootJs(array('js/apps/stock.js'));
    }

    protected function outputBody()
    {

        $this->smarty->assign('sid', $this->sid);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('stock_list', $this->stockInList['data']);
        $this->smarty->assign('supplier', $this->supplier);
        $this->smarty->assign('prices', $this->prices);
        $this->smarty->assign('paid_sources', Conf_Finance::$MONEY_OUT_PAID_TYPES);

        $this->smarty->display('finance/supplier_bulk_payment.html');
    }
}

$app = new App();
$app->run();