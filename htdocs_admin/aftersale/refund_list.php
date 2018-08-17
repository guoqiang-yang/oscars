<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $start;
    private $mobile;
    private $searchConf;
    private $action;
    private $refunds;
    private $num = 20;
    private $total;
    private $totalPrice;
    private $status = '';

    protected function checkAuth()
    {
        $this->action = Tool_Input::clean('r', 'action', TYPE_STR);
        if($this->action == 'download')
        {
            parent::checkAuth('hc_aftersale_refund_product_export');
        }else{
            parent::checkAuth('/order/refund_list');
        }
    }

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->mobile = Tool_Input::clean('r', 'mobile', TYPE_STR);
        $this->searchConf = array(
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
            'rid' => Tool_Input::clean('r', 'rid', TYPE_UINT),
            'driver_phone' => Tool_Input::clean('r', 'driver_phone', TYPE_STR),
            'from_date' => Tool_Input::clean('r', 'from_date', TYPE_STR),
            'end_date' => Tool_Input::clean('r', 'end_date', TYPE_STR),
            'sales_suid' => Tool_Input::clean('r', 'sales_suid', TYPE_UINT),
            'date' => Tool_Input::clean('r', 'date', TYPE_STR),
            'wid' => $this->getWarehouseId(),
            'reason_type' => Tool_Input::clean('r', 'reason_type', TYPE_UINT),
            'reason' => Tool_Input::clean('r', 'reason', TYPE_UINT),
            'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
            'from_in_finance_date' => Tool_Input::clean('r', 'from_in_finance_date', TYPE_STR),
            'end_in_finance_date' => Tool_Input::clean('r', 'end_in_finance_date', TYPE_STR),
        );

        $this->searchConf['wid'] = $this->searchConf['wid']>0? $this->searchConf['wid']: 0;
        
        $this->searchConf['paid'] = isset($_REQUEST['paid']) ? $_REQUEST['paid'] : Conf_Base::STATUS_ALL;
        $this->status = isset($_REQUEST['status']) ? Tool_Input::clean('r', 'status', TYPE_STR) : '';

        if (isset($_REQUEST['step']))
        {
            $this->searchConf['step'] = Tool_Input::clean('r', 'step', TYPE_UINT);
        }
    }

    protected function main()
    {
        $cityInfo = City_Api::getCity();
        //下载
        if ($this->action == 'download') {
            $searchConf = sprintf('rid>0 and status=0 and city_id=%d', $cityInfo['city_id']);
            Aftersale_Api::exportRefundProductsListByWhere($searchConf);
            exit;
        }
        if (!empty($this->mobile))
        {
            $c = Crm2_Api::getByMobile($this->mobile);
            if (!empty($c))
            {
                $this->searchConf['cid'] = $c['cid'];
            }
        }
        $this->searchConf['city_id'] = $cityInfo['city_id'];
        $res = Order_Api::getRefundList($this->searchConf, $this->start, $this->num, $this->status);

        $this->refunds = $res['list'];
        $this->total = $res['total'];
        $this->totalPrice = $res['total_price'];

        $this->addFootJs(array('js/apps/order.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $app = '/aftersale/refund_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('total_price', $this->totalPrice);
        $this->smarty->assign('mobile', $this->mobile);
        $this->smarty->assign('searchConf', $this->searchConf);
        $this->smarty->assign('refund_steps', Conf_Refund::getRefundStepNames());
        $this->smarty->assign('refunds', $this->refunds);
        $this->smarty->assign('_warehouseList', Conf_Warehouse::getWarehousesOfCity($this->searchConf['city_id']));
        unset($this->searchConf['status']);
        $page_url = '/aftersale/refund_list.php?' . http_build_query($this->searchConf);
        $this->smarty->assign('page_url',$page_url);
        $this->smarty->assign('status', $this->status);

        $this->smarty->display('order/refund_list.html');
    }
}

$app = new App('pri');
$app->run();

