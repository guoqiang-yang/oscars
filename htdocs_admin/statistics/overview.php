<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $result;
    private $lackList;
    private $today;
    private $orderStepInfo;

    protected function main()
    {
        $today = date('Y-m-d');
        $this->today = $today;
        $yesterday = date('Y-m-d', strtotime('yesterday'));
        $monthStart = date('Y-m-01');
        $this->result = Statistics_Api::getData4Overview($monthStart, $yesterday, $today);

        //今日数据
        $sc = new Statistics_Common_Calculate();
        $todayData = $sc->calcBaseInfoOfDay4Overview($today);

        $oo = new Order_Order();
        $where = sprintf('status=%d AND step>=%d AND ship_time>="%s 00:00:00" AND ship_time<="%s 23:59:59" AND source_oid=0 AND aftersale_type=0 GROUP BY wid',
            Conf_Base::STATUS_NORMAL, Conf_Order::ORDER_STEP_PICKED, $today, $today);
        $_data = $oo->getByRawWhere('t_order',$where,array('wid,count(1) as order_num'));
        if(!empty($_data))
        {
            foreach ($_data as $item)
            {
                $this->result['wid_arr'][$item['wid']]['today'] = $item['order_num'];
            }
        }

        $where = sprintf('status=%d AND step>=%d AND ship_time>="%s 00:00:00" AND ship_time<="%s 23:59:59" AND source_oid=0 AND aftersale_type=0 GROUP BY source',
            Conf_Base::STATUS_NORMAL, Conf_Order::ORDER_STEP_PICKED, $today, $today);
        $_data = $oo->getByRawWhere('t_order',$where,array('source,count(1) as order_num'));
        if(!empty($_data))
        {
            foreach ($_data as $item)
            {
                switch ($item['source'])
                {
                    case Conf_Order::SOURCE_WEIXIN:
                        $this->result['source_arr']['weixin']['today'] = $item['order_num'];
                        break;
                    case Conf_Order::SOURCE_APP_ANDROID:
                    case Conf_Order::SOURCE_APP_IOS:
                        $this->result['source_arr']['app']['today'] = $item['order_num'];
                        break;
                    case Conf_Order::SOURCE_KEFU:
                    case Conf_Order::SOURCE_AFTER_SALE:
                        $this->result['source_arr']['kefu']['today'] = $item['order_num'];
                        break;
                }
            }
        }
        $this->result['today_amount'] = $todayData['amount'];
        $this->result['today_num'] = intval($todayData['order_num']);
        $this->result['today_buyer_num'] = intval($todayData['buyer_num']);
        $this->result['today_price'] = $todayData['amount'] - $todayData['customer_freight'] - $todayData['customer_carriage'] + $todayData['privilege'];
        $this->result['today_cost'] = $todayData['cost'];
        $this->result['today_refund_num'] = $todayData['refund_num'];
        $this->result['total_amount'] += $this->result['today_amount'];
        $this->result['total_price'] += $this->result['today_price'];
        $this->result['total_num'] += $this->result['today_num'];
        $this->result['total_refund_num'] += $this->result['today_refund_num'];
        $this->result['total_cost'] += $this->result['today_cost'];
        $day = date('j');
        foreach ($this->result['wid_arr'] as $wid => $item)
        {
            $this->result['wid_arr'][$wid]['total'] = ceil(($this->result['wid_arr'][$wid]['total'] + $this->result['wid_arr'][$wid]['today']) / $day);
        }
        foreach ($this->result['source_arr'] as $key => $item)
        {
            $this->result['source_arr'][$key]['total'] += $this->result['source_arr'][$key]['today'];
        }

        $todayTotal = $this->result['source_arr']['weixin']['today'] + $this->result['source_arr']['app']['today'] + $this->result['source_arr']['kefu']['today'];
        $this->result['source_arr']['weixin']['today'] = round($this->result['source_arr']['weixin']['today'] / $todayTotal, 2) * 100;
        $this->result['source_arr']['app']['today'] = round($this->result['source_arr']['app']['today'] / $todayTotal, 2) * 100;
        $this->result['source_arr']['kefu']['today'] = round($this->result['source_arr']['kefu']['today'] / $todayTotal, 2) * 100;

        $yesterdayTotal = $this->result['source_arr']['weixin']['yesterday'] + $this->result['source_arr']['app']['yesterday'] + $this->result['source_arr']['kefu']['yesterday'];
        $this->result['source_arr']['weixin']['yesterday'] = round($this->result['source_arr']['weixin']['yesterday'] / $yesterdayTotal, 2) * 100;
        $this->result['source_arr']['app']['yesterday'] = round($this->result['source_arr']['app']['yesterday'] / $yesterdayTotal, 2) * 100;
        $this->result['source_arr']['kefu']['yesterday'] = round($this->result['source_arr']['kefu']['yesterday'] / $yesterdayTotal, 2) * 100;

        $totalTotal = $this->result['source_arr']['weixin']['total'] + $this->result['source_arr']['app']['total'] + $this->result['source_arr']['kefu']['total'];
        $this->result['source_arr']['weixin']['total'] = round($this->result['source_arr']['weixin']['total'] / $totalTotal, 2) * 100;
        $this->result['source_arr']['app']['total'] = round($this->result['source_arr']['app']['total'] / $totalTotal, 2) * 100;
        $this->result['source_arr']['kefu']['total'] = round($this->result['source_arr']['kefu']['total'] / $totalTotal, 2) * 100;
        //格式化返回数据
        $this->result['total_amount'] = round($this->result['total_amount'] / 100, 2);
        $this->result['yesterday_amount'] = round($this->result['yesterday_amount'] / 100, 2);
        $this->result['today_amount'] = round($this->result['today_amount'] / 100, 2);
        $this->result['total_num'] = intval($this->result['total_num']);
        $this->result['yesterday_num'] = intval($this->result['yesterday_num']);
        $this->result['total_buyer_num'] = intval($this->result['total_buyer_num']);
        $this->result['yesterday_buyer_num'] = intval($this->result['yesterday_buyer_num']);
        $this->result['total_refund_num'] = intval($this->result['total_refund_num']);
        $this->result['yesterday_refund_num'] = intval($this->result['yesterday_refund_num']);
        $this->result['today_refund_num'] = intval($this->result['today_refund_num']);
        $this->result['total_gross_rate'] = round(($this->result['total_price'] - $this->result['total_cost']) * 100 / $this->result['total_price'], 2);
        $this->result['yesterday_gross_rate'] = round(($this->result['yesterday_price'] - $this->result['yesterday_cost']) * 100 / $this->result['yesterday_price'], 2);
        $this->result['today_gross_rate'] = round(($this->result['today_price'] - $this->result['today_cost']) * 100 / $this->result['today_price'], 2);
//        //今日订单挂单数据
//        $this->orderStepInfo = array(
//            'total' => 0,
//            'add_on' => 0,
//            'un_confirm' => 0,
//            'un_set_driver' => 0,
//            'un_delivery' => 0,
//            'un_back' => 0,
//            'back' => 0,
//        );
//        $where = sprintf('status=%d AND step>=%d AND ship_time>="%s 00:00:00" AND ship_time<="%s 23:59:59"',
//                         Conf_Base::STATUS_NORMAL, Conf_Order::ORDER_STEP_NEW, $today, $today);
//        $orderList = Order_Api::getOrderListByWhere($where, array(), 0, 0, array('oid', 'source_oid', 'step'));
//        foreach ($orderList as $order)
//        {
//            $this->orderStepInfo['total']++;
//            if ($order['source_oid'] > 0)
//            {
//                $this->orderStepInfo['add_on']++;
//            }
//            switch ($order['step'])
//            {
//                case Conf_Order::ORDER_STEP_NEW:
//                    $this->orderStepInfo['un_confirm']++;
//                    break;
//                case Conf_Order::ORDER_STEP_SURE:
//                case Conf_Order::ORDER_STEP_BOUGHT:
//                    $this->orderStepInfo['un_set_driver']++;
//                    break;
//                case Conf_Order::ORDER_STEP_HAS_DRIVER:
//                    $this->orderStepInfo['un_delivery']++;
//                    break;
//                case Conf_Order::ORDER_STEP_PICKED:
//                case Conf_Order::ORDER_STEP_DELIVERED:
//                    $this->orderStepInfo['un_back']++;
//                    break;
//                case Conf_Order::ORDER_STEP_FINISHED:
//                    $this->orderStepInfo['back']++;
//                    break;
//                default:
//                    //nothing
//            }
//        }
        $this->addHeadJs(array(
                             'js/jquery.min.js',
                             'js/highcharts/highcharts.js',
                             'js/highcharts/modules/exporting.js',
                             'js/highcharts/modules/data.js',
                             'js/highcharts/themes/dark_unica.js',
                         ));

        $this->addFootJs(array(
                             'js/apps/highcharts/overview.js',
                         ));
        $this->addCss(array(
                          'css/highcharts/unica_one.css'
                      ));
    }

    protected function outputBody()
    {
        $this->smarty->assign('result', $this->result);
        $this->smarty->assign('warehouse_list', Conf_Warehouse::$WAREHOUSES);
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('lack_list', $this->lackList);
        $this->smarty->assign('order_step_info', $this->orderStepInfo);
        $this->smarty->assign('today', $this->today);

        $this->smarty->display('statistics/overview_new.html');
    }
}

$app = new App('pri');
$app->run();

