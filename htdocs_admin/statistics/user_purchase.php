<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    const DATE_MODE = 'date', MONTH_MODE = 'month';
    private $start;
    private $viewMode;
    private $fromDate;
    private $endDate;
    private $lineType;
    private $list;
    private $num = 20;
    private $total;
    private $days;
    private $lines;
    private $lineTitle;
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
        $this->lineType = Tool_Input::clean('r', 'line_type', TYPE_STR);
        $this->action = Tool_Input::clean('r', 'action', TYPE_STR);
        $this->city = Tool_Input::clean('r', 'city', TYPE_UINT);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->viewMode))
        {
            $this->viewMode = self::DATE_MODE;
        }
        if (empty($this->fromDate))
        {
            $this->fromDate = date("Y-m-01");
            $this->endDate = date("Y-m-d");
            if (strtotime($this->endDate) - strtotime($this->fromDate) < 7*86400)
            {
                $this->fromDate = date("Y-m-d", strtotime('-8 day'));
            }
        }
        if (empty($this->lineType))
        {
            $this->lineType = 'order';
        }
        if(empty($this->city) && empty($this->wid))
        {
            $this->wid = $this->getAllAllowedWids4User(0, true);
        }elseif (empty($this->wid))
        {
            $this->wid = $this->getAllAllowedWids4User($this->city, true);
        }
    }

    protected function main()
    {
        //下载
        if ($this->action == 'download')
        {
            if (self::DATE_MODE == $this->viewMode)
            {
                Statistics_Api::exportForWeb(Conf_Statics::TYPE_USER_PURCHASE_DAY, $this->fromDate, $this->endDate, array('city' => $this->city, 'wid' => $this->wid));
            }
            else
            {
                Statistics_Api::exportForWeb(Conf_Statics::TYPE_USER_PURCHASE_MONTH, $this->fromDate, $this->endDate, array('city' => $this->city, 'wid' => $this->wid));
            }
            exit;
        }

        if (self::DATE_MODE == $this->viewMode)
        {
            $getToday = false;
            $today = date('Y-m-d');
            if ($today >= $this->fromDate && $today <= $this->endDate)
            {
                $getToday = true;
            }
            $list = Statistics_Api::getBaseInfoBetweenDate($this->city, $this->wid, $this->fromDate, $this->endDate, 0, 0, $getToday);
            $this->list = array_slice($list['list'], $this->start, $this->num);
            $this->total = $list['total'];

            switch ($this->lineType)
            {
                case 'customer':
                    $this->lineTitle = '用户数';
                    $total = array();
                    $new = array();
                    $old = array();
                    $list = array_reverse($list['list']);
                    if (!empty($list))
                    {
                        foreach ($list as $item)
                        {
                            $this->days[] = $item['day'];
                            $total[] = intval($item['buyer_num']);
                            $new[] = intval($item['new_buyer_num']);
                            $old[] = intval($item['buyer_num']) - intval($item['new_buyer_num']);
                        }
                    }
                    $this->lines = array(
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '人'
                            ),
                            'name' => '购买用户',
                            'data' => $total,
                        ),
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '人'
                            ),
                            'name' => '新客数',
                            'data' => $new,
                        ),
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '人'
                            ),
                            'name' => '老客数',
                            'data' => $old,
                        ),
                    );
                    break;
                case 'order':
                    $this->lineTitle = '订单量';
                    $total = array();
                    $new = array();
                    $old = array();
                    $list = array_reverse($list['list']);
                    if (!empty($list))
                    {
                        foreach ($list as $item)
                        {
                            $this->days[] = $item['day'];
                            $total[] = intval($item['order_num']);
                            $new[] = intval($item['new_order_num']);
                            $old[] = intval($item['order_num']) - intval($item['new_order_num']);
                        }
                    }
                    $this->lines = array(
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '单'
                            ),
                            'name' => '订单量',
                            'data' => $total,
                        ),
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '单'
                            ),
                            'name' => '新客订单量',
                            'data' => $new,
                        ),
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '单'
                            ),
                            'name' => '老客订单量',
                            'data' => $old,
                        ),
                    );
                    break;
                case 'amount':
                    $this->lineTitle = '订单金额';
                    $total = array();
                    $new = array();
                    $old = array();
                    $list = array_reverse($list['list']);
                    if (!empty($list))
                    {
                        foreach ($list as $item)
                        {
                            $this->days[] = $item['day'];
                            $total[] = intval($item['amount'] / 100);
                            $new[] = intval($item['new_amount'] / 100);
                            $old[] = round((intval($item['amount']) - intval($item['new_amount'])) / 100);
                        }
                    }
                    $this->lines = array(
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '元'
                            ),
                            'name' => '订单金额',
                            'data' => $total,
                        ),
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '元'
                            ),
                            'name' => '新客订单金额',
                            'data' => $new,
                        ),
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '元'
                            ),
                            'name' => '老客订单金额',
                            'data' => $old,
                        ),
                    );
                    break;
                case 'price':
                    $this->lineTitle = '客单价';
                    $total = array();
                    $new = array();
                    $old = array();
                    $list = array_reverse($list['list']);
                    if (!empty($list))
                    {
                        foreach ($list as $item)
                        {
                            $this->days[] = $item['day'];
                            $total[] = intval($item['order_num']) > 0 ? round($item['amount'] / ($item['order_num'] * 100), 1) : 0;
                            $new[] = intval($item['new_order_num']) > 0 ? round($item['new_amount'] / ($item['new_order_num'] * 100), 1) : 0;
                            $old[] = intval($item['order_num']) - intval($item['new_order_num']) > 0 ? round((intval($item['amount']) - intval($item['new_amount'])) / ((intval($item['order_num']) - intval($item['new_order_num'])) * 100), 1) : 0;
                        }
                    }
                    $this->lines = array(
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '元'
                            ),
                            'name' => '客单价',
                            'data' => $total,
                        ),
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '元'
                            ),
                            'name' => '新客客单价',
                            'data' => $new,
                        ),
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '元'
                            ),
                            'name' => '老客客单价',
                            'data' => $old,
                        ),
                    );
                    break;
                default:
                    //nothing
            }
        }
        else if (self::MONTH_MODE == $this->viewMode)
        {
            $this->list = Statistics_Api::getBaseInfoOfAllMonth($this->city, $this->wid);

            switch ($this->lineType)
            {
                case 'customer':
                    $this->lineTitle = '用户数';
                    $total = array();
                    $new = array();
                    $old = array();
                    $list = array_reverse($this->list);
                    if (!empty($list))
                    {
                        foreach ($list as $item)
                        {
                            $this->days[] = $item['month'];
                            $total[] = intval($item['buyer_num']);
                            $new[] = intval($item['new_buyer_num']);
                            $old[] = intval($item['buyer_num']) - intval($item['new_buyer_num']);
                        }
                    }
                    $this->lines = array(
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '人'
                            ),
                            'name' => '购买用户',
                            'data' => $total,
                        ),
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '人'
                            ),
                            'name' => '新客数',
                            'data' => $new,
                        ),
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '人'
                            ),
                            'name' => '老客数',
                            'data' => $old,
                        ),
                    );
                    break;
                case 'order':
                    $this->lineTitle = '订单量';
                    $total = array();
                    $new = array();
                    $old = array();
                    $list = array_reverse($this->list);
                    if (!empty($list))
                    {
                        foreach ($list as $item)
                        {
                            $this->days[] = $item['month'];
                            $total[] = intval($item['order_num']);
                            $new[] = intval($item['new_order_num']);
                            $old[] = intval($item['order_num']) - intval($item['new_order_num']);
                        }
                    }
                    $this->lines = array(
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '单'
                            ),
                            'name' => '订单量',
                            'data' => $total,
                        ),
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '单'
                            ),
                            'name' => '新客订单量',
                            'data' => $new,
                        ),
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '单'
                            ),
                            'name' => '老客订单量',
                            'data' => $old,
                        ),
                    );
                    break;
                case 'amount':
                    $this->lineTitle = '订单金额';
                    $total = array();
                    $new = array();
                    $old = array();
                    $list = array_reverse($this->list);
                    if (!empty($list))
                    {
                        foreach ($list as $item)
                        {
                            $this->days[] = $item['month'];
                            $total[] = intval($item['amount'] / 100);
                            $new[] = intval($item['new_amount'] / 100);
                            $old[] = round((intval($item['amount']) - intval($item['new_amount'])) / 100);
                        }
                    }
                    $this->lines = array(
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '元'
                            ),
                            'name' => '订单金额',
                            'data' => $total,
                        ),
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '元'
                            ),
                            'name' => '新客订单金额',
                            'data' => $new,
                        ),
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '元'
                            ),
                            'name' => '老客订单金额',
                            'data' => $old,
                        ),
                    );
                    break;
                case 'price':
                    $this->lineTitle = '客单价';
                    $total = array();
                    $new = array();
                    $old = array();
                    $list = array_reverse($this->list);
                    if (!empty($list))
                    {
                        foreach ($list as $item)
                        {
                            $this->days[] = $item['month'];
                            $total[] = intval($item['order_num']) > 0 ? round($item['amount'] / ($item['order_num'] * 100), 1) : 0;
                            $new[] = intval($item['new_order_num']) > 0 ? round($item['new_amount'] / ($item['new_order_num'] * 100), 1) : 0;
                            $old[] = intval($item['order_num']) - intval($item['new_order_num']) > 0 ? round((intval($item['amount']) - intval($item['new_amount'])) / ((intval($item['order_num']) - intval($item['new_order_num'])) * 100), 1) : 0;
                        }
                    }
                    $this->lines = array(
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '元'
                            ),
                            'name' => '客单价',
                            'data' => $total,
                        ),
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '元'
                            ),
                            'name' => '新客客单价',
                            'data' => $new,
                        ),
                        array(
                            'tooltip' => array(
                                'valueSuffix',
                                '元'
                            ),
                            'name' => '老客客单价',
                            'data' => $old,
                        ),
                    );
                    break;
                default:
                    //nothing
            }
        }
        $wids = $this->getAllAllowedWids4User(0, true);
        $warehouses = Conf_Warehouse::$WAREHOUSES;
        foreach ($warehouses as $wid => $warehouse)
        {
            if(in_array($wid, $wids)){
                $this->warehouseList[$wid] = array(
                    'city' => Conf_Warehouse::getCityByWarehouse($wid),
                    'wname' => $warehouse,
                );
            }
        }

        $this->addHeadJs(array(
                             'js/jquery.min.js',
                             'js/highcharts/highcharts.js',
                             'js/highcharts/modules/exporting.js',
                             'js/highcharts/themes/dark_unica.js',
                         ));

        $this->addFootJs(array(
                             'js/apps/highcharts/order.js',
                         ));
        $this->addCss(array(
                          'css/highcharts/unica_one.css'
                      ));
    }

    protected function outputBody()
    {
        if(is_array($this->wid))
        {
            $this->wid = 0;
        }
        $app = '/statistics/user_purchase.php?view_mode=' . $this->viewMode . '&from_date=' . $this->fromDate . '&end_date=' . $this->endDate . '&city=' . $this->city . '&wid=' . $this->wid;
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('days', json_encode($this->days));
        $this->smarty->assign('lines', json_encode($this->lines));
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('data_list', $this->list);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('view_mode', $this->viewMode);
        $this->smarty->assign('from_date', $this->fromDate);
        $this->smarty->assign('end_date', $this->endDate);
        $this->smarty->assign('line_type', $this->lineType);
        $this->smarty->assign('title', $this->lineTitle);
        $this->smarty->assign('city_list', $this->getAllowedCities4User());
        $this->smarty->assign('city_id', $this->city);
        $this->smarty->assign('yesterday', date('Y-m-d', strtotime('yesterday')));
        $this->smarty->assign('today', date('Y-m-d'));
        $this->smarty->assign('warehouse_list', $this->warehouseList);
        $this->smarty->assign('cur_wid', $this->wid);

        $this->smarty->display('statistics/user_purchase.html');
    }
}

$app = new App('pri');
$app->run();
