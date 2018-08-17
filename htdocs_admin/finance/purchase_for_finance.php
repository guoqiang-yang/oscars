<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $start;
    private $total;
    private $sumPrice;
    private $searchConf;
    private $groupStockInLists;
    private $buyerList;
    private $num = 20;

    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/purchase_for_finance');
    }

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->searchConf = array(
            'id' => Tool_Input::clean('r', 'id', TYPE_UINT),
            'sid' => Tool_Input::clean('r', 'sid', TYPE_UINT),
            'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
            'wid' => $this->getWarehouseId(),
            'buyer_uid' => Tool_Input::clean('r', 'buyer_uid', TYPE_UINT),
            'payment_type' => Tool_Input::clean('r', 'payment_type', TYPE_UINT),
            'from_date' => Tool_Input::clean('r', 'from_date', TYPE_STR),
            'end_date' => Tool_Input::clean('r', 'end_date', TYPE_STR),
            'step' => Tool_Input::clean('r', 'step', TYPE_UINT),
            'statement_id' => Tool_Input::clean('r', 'statement_id', TYPE_UINT)
        );
        $this->searchConf['paid'] = isset($_REQUEST['paid'])? $_REQUEST['paid']: Conf_Base::STATUS_ALL;
    }

    protected function main()
    {
        if (!empty($this->searchConf['from_date']))
        {
            $this->searchConf['stime'] = $this->searchConf['from_date'] . ' 00:00:00';
        }
        if (!empty($this->searchConf['end_date']))
        {
            $this->searchConf['etime'] = $this->searchConf['end_date'] . ' 23:59:59';
        }

        $res = Warehouse_Api::getStockinList4Finance($this->searchConf, $this->start, $this->num);

        $this->groupStockInLists = $res['data'];
        $this->total = $res['total'];
        $this->sumPrice = $res['sum_price'];
        $this->buyerList = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_BUYER_NEW);

        $this->addFootJs(array('js/apps/stock.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $app = '/finance/purchase_for_finance.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('buyerList', $this->buyerList);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('sum', $this->sumPrice);
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('searchConf', $this->searchConf);
        $this->smarty->assign('grouped_stockin_list', $this->groupStockInLists);
        $this->smarty->assign('all_pay_types', Conf_Stock::$PAYMENT_TYPES);
        $this->smarty->assign('all_steps', Conf_Stock_In::$Step_Descs);
        $this->smarty->assign('inorder_source', Conf_In_Order::$In_Order_Source);
        $this->smarty->assign('warehouses', Conf_Warehouse::$WAREHOUSES);
        $this->smarty->assign('app', $app);
        unset($this->searchConf['paid']);
        $page_url = '/finance/purchase_for_finance.php?' . http_build_query($this->searchConf);
        $this->smarty->assign('page_url',$page_url);

        $this->smarty->display('warehouse/purchase_for_finance.html');
    }
}

$app = new App('pri');
$app->run();

