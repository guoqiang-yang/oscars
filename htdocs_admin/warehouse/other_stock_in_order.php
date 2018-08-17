<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
    private $searchConf;
    private $num = 20;
    private $start = 0;
    private $total;
    private $orderList;
    private $staffList;

    protected function getPara()
    {
        $this->searchConf = array(
            'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
            'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
            'order_type' => Conf_Stock::OTHER_STOCK_ORDER_TYPE_IN,
            'step' => Tool_Input::clean('r', 'step', TYPE_UINT),
        );
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
    }

    protected function checkPara()
    {
        $curCity = City_Api::getCity();
        if (empty($this->searchConf['wid']))
        {
            if (empty($this->_user['city_wid_map'][$curCity['city_id']]))
            {
                $this->searchConf['wid'] = -1;
            }
            else
            {
                $this->searchConf['wid'] = $this->_user['city_wid_map'][$curCity['city_id']];
            }
        }
    }

    protected function main()
    {
        $orderList = Warehouse_Api::getOtherStockOutOrderList($this->searchConf, array(), $this->start, $this->num);
        $suids = Tool_Array::getFields($orderList['list'], 'suid');
        $this->total = $orderList['total'];
        $this->orderList = $orderList['list'];
        if (!empty($suids))
        {
            $this->staffList = Tool_Array::list2Map(Admin_Api::getStaffs($suids), 'suid', 'name');
        }
        else
        {
            $this->staffList = array();
        }
        $this->addFootJs(array('js/apps/stock.js'));
    }

    protected function outputBody()
    {
        if (is_array($this->searchConf['wid']))
        {
            unset($this->searchConf['wid']);
        }
        $app = '/warehouse/other_stock_in_order.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('order_list', $this->orderList);
        $this->smarty->assign('staff_list', $this->staffList);
        $this->smarty->assign('type_list', Conf_Stock::getOtherStockTypes($this->searchConf['order_type']));
        $this->smarty->assign('step_list', Conf_Stock::$OTHER_STOCK_OUT_ORDER_STEPS);
        $this->smarty->assign('search_conf', $this->searchConf);

        $this->smarty->display('warehouse/other_stock_in_order.html');
    }

}

$app = new App('pri');
$app->run();

