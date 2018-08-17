<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $start;
    private $orders;
    private $num = 200;
    private $total;
    private $searchConf;
    private $sourceList;
    private $canBulkPay = FALSE;
    private $wid;

    protected function checkAuth()
    {
        parent::checkAuth('/order/carrier_order_list');

        if (Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_YUNNIAO))
        {
            $this->sourceList['list'] = array(3 => '云鸟');
            $this->searchConf['source'] = 3;
        }
        else
        {
            $this->sourceList = Logistics_Api::getSourceList();
        }
    }

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->wid = $this->getWarehouseId();
        $this->searchConf = array(
            'wid' => $this->wid,
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'mobile' => Tool_Input::clean('r', 'mobile', TYPE_STR),
            'source' => Tool_Input::clean('r', 'source', TYPE_UINT),
            'cuid' => Tool_Input::clean('r', 'cuid', TYPE_UINT),

            'btime' => Tool_Input::clean('r', 'btime', TYPE_STR) ? : date('Y-m-d', time() - 30 * 24 * 3600),
            'etime' => Tool_Input::clean('r', 'etime', TYPE_STR) ? : date('Y-m-d'),
            'carrier_unpaid' => Tool_Input::clean('r', 'carrier_unpaid', TYPE_STR),
            'generate_statement' => Tool_Input::clean('r', 'generate_statement', TYPE_STR),
        );

        if ($this->searchConf['carrier'] == 'on' || $this->searchConf['generate_statement'] == 'on')
        {
            $this->searchConf['paid'] = 0;
        }
        if (empty($this->searchConf['wid']))
        {
            $this->searchConf['wid'] = array_keys(App_Admin_Web::getAllowedWids4User());
        }
    }

    protected function main()
    {
        $data = Logistics_Coopworker_Api::getCarrierOrderList($this->searchConf, $this->start, $this->num);
        $this->orders = $data['list'];
        $this->total = $data['total'];

        $cuids = Tool_Array::getFields($this->orders, 'cuid');
        $this->canBulkPay = count(array_unique($cuids)) == 1 ? TRUE : FALSE;

        $this->addFootJs(array('js/apps/coopworker.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        if (empty($this->wid))
        {
            $this->searchConf['wid'] = 0;
        }
        $app = '/logistics/carrier_order_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('orders', $this->orders);
        $this->smarty->assign('can_bluk_pay', $this->canBulkPay);

        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('source_list', $this->sourceList['list']);
        $this->smarty->assign('warehouse', App_Admin_Web::getAllowedWids4User());
        
        $cityInfo = City_Api::getCity();
        $this->smarty->assign('warehouse', Appconf_Warehouse::wid4City($cityInfo['city_id'], 'online'));
        $this->smarty->assign('order_steps', Conf_Order::$ORDER_STEPS);
        $this->smarty->assign('paid_status', Conf_Order::$PAY_STATUS);
        $this->smarty->assign('payment_types', Conf_Base::getCoopWorkerPayentTypes($this->searchConf['wid']));
        $this->smarty->assign('user_wid', $this->_user['wid']);
        $this->smarty->assign('user_wids', explode(',', $this->_user['wids']));

        $this->smarty->display('order/carrier_order_list.html');
    }
}

$app = new App('pri');
$app->run();

