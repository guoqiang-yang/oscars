<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    const DATE_MODE = 'date';
    const MONTH_MODE = 'month';
    private $start;
    private $viewMode;
    private $fromDate;
    private $endDate;
    private $list;
    private $num = 20;
    private $total;
    private $action;
    private $city;
    private $warehouseList;
    private $wid;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->viewMode = Tool_Input::clean('r', 'view_mode', TYPE_STR);
        $this->fromDate = Tool_Input::clean('r', 'from_date', TYPE_STR);
        $this->endDate = Tool_Input::clean('r', 'end_date', TYPE_STR);
        $this->action = Tool_Input::clean('r', 'action', TYPE_STR);
        $this->city = Tool_Input::clean('r', 'city', TYPE_UINT);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->viewMode))
        {
            $this->viewMode = self::MONTH_MODE;
        }
        if (empty($this->fromDate))
        {
            if (date('d') == '01')
            {
                $this->fromDate = date("Y-m-01", strtotime('-1 day'));
                $this->endDate = date("Y-m-t", strtotime('-1 day'));
            }
            else
            {
                $this->fromDate = date("Y-m-01");
                $this->endDate = date("Y-m-d", strtotime('-1 day'));
            }
        }
        if(empty($this->city) && empty($this->wid))
        {
            $this->wid = $this->getAllAllowedWids4User(0, true);
        }elseif(empty($this->wid))
        {
            $this->wid = $this->getAllAllowedWids4User($this->city, true);
        }
    }

    protected function main()
    {
        if ($this->action == 'download')
        {
            if (self::DATE_MODE == $this->viewMode)
            {
                Statistics_Api::exportForWeb(Conf_Statics::TYPE_ORDER_FEE_DAY, $this->fromDate, $this->endDate, array('city' => $this->city, 'wid' => $this->wid));
            }
            else
            {
                Statistics_Api::exportForWeb(Conf_Statics::TYPE_ORDER_FEE_MONTH, $this->fromDate, $this->endDate, array('city' => $this->city, 'wid' => $this->wid));
            }
            exit;
        }
        if (self::DATE_MODE == $this->viewMode)
        {
            $res = Statistics_Api::getBaseInfoBetweenDate($this->city, $this->wid, $this->fromDate, $this->endDate, $this->start, $this->num);
            $this->list = $res['list'];
            $this->total = $res['total'];
        }
        else if (self::MONTH_MODE == $this->viewMode)
        {
            $this->list = Statistics_Api::getBaseInfoOfAllMonth($this->city, $this->wid);
        }

        foreach ($this->list as &$item)
        {
            $item['price'] = $item['amount'] - $item['customer_freight'] - $item['customer_carriage'] + $item['privilege'];
        }
        $wids = $this->getAllAllowedWids4User(0, true);
        $warehouses = Conf_Warehouse::$WAREHOUSES;
        foreach ($warehouses as $wid => $warehouse)
        {
            if(in_array($wid, $wids))
            {
                $this->warehouseList[$wid] = array(
                    'city' => Conf_Warehouse::getCityByWarehouse($wid),
                    'wname' => $warehouse,
                );
            }
        }
    }

    protected function outputBody()
    {
        if(is_array($this->wid))
        {
            $this->wid = 0;
        }
        $app = '/statistics/order_fee_list.php?view_mode=' . $this->viewMode . '&from_date=' . $this->fromDate . '&end_date=' . $this->endDate. '&city=' . $this->city . '&wid=' . $this->wid;
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('data_list', $this->list);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('view_mode', $this->viewMode);
        $this->smarty->assign('from_date', $this->fromDate);
        $this->smarty->assign('end_date', $this->endDate);
        $this->smarty->assign('city_list', $this->getAllowedCities4User());
        $this->smarty->assign('city_id', $this->city);
        $this->smarty->assign('yesterday', date('Y-m-d', strtotime('yesterday')));
        $this->smarty->assign('warehouse_list', $this->warehouseList);
        $this->smarty->assign('cur_wid', $this->wid);

        $this->smarty->display('statistics/order_fee_list.html');
    }
}

$app = new App('pri');
$app->run();
