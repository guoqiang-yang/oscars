<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $startTime;
    private $endTime;
    private $cancelNum;
    private $orderNum;
    private $lines;
    private $cancelResons;
    private $days;
    private $start;
    private $num = 30;
    private $list;
    private $pageData;
    private $searchConf;
    private $total;

    protected function getPara()
    {
        $this->startTime = Tool_Input::clean('r', 'start_date', TYPE_STR);
        $this->endTime = Tool_Input::clean('r', 'end_date', TYPE_STR);
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->endTime))
        {
            $this->endTime = date('Y-m-d', strtotime('yesterday'));
        }
        if (empty($this->startTime))
        {
            $this->startTime = date('Y-m-d', strtotime('-30 days'));
        }
    }

    protected function main()
    {
        $this->searchConf = array();
        $this->searchConf['start_date'] = $this->startTime;
        $this->searchConf['end_date'] = $this->endTime;

        $where = sprintf('status!=%d AND step>=%d AND ctime>="%s 00:00:00" AND ctime<="%s 23:59:59" AND source_oid=0 AND aftersale_type=0',
                         Conf_Base::STATUS_DELETED, Conf_Order::ORDER_STEP_NEW, $this->startTime, $this->endTime);
        $oo = new Order_Order();
        $this->orderNum = $oo->getTotal($where);

        $list = Order_Api::getCancelList($this->searchConf, 0, 0);
        if (!empty($list['list']))
        {
            $orderCancel = Tool_Array::list2Map($list['list'], 'oid');
            $oids = Tool_Array::getFields($list['list'], 'oid');
            $orderList = Order_Api::getBulk($oids, array('oid', 'cid', 'ctime'));
            $cids = Tool_Array::getFields($orderList, 'cid');
            $customerInfos = Crm2_Api::getCustomers($cids);
            //列表数据
            foreach ($orderList as $order)
            {
                $this->list[] = array(
                    'oid' => $order['oid'],
                    'cid' => $order['cid'],
                    'cname' => $customerInfos[$order['cid']]['name'],
                    'order_time' => $order['ctime'],
                    'cancel_time' => $orderCancel[$order['oid']]['ctime'],
                    'reason' => $orderCancel[$order['oid']]['reason'] ? Conf_Comment::$CANCEL_REASONS[$orderCancel[$order['oid']]['reason']] : '无',
                );
            }
            $this->pageData = array_slice($this->list, $this->start, $this->num);

            $this->cancelNum = count($this->list);

            //线状图
            $day = $this->startTime;
            $daysInfos = array();
            while ($day <= $this->endTime)
            {
                $this->days[] = $day;
                $daysInfos[$day] = 0;
                $day = date('Y-m-d', strtotime($day) + 86400);
            }
            $cancelReasons = array();
            foreach ($list['list'] as $item)
            {
                $day = date('Y-m-d', strtotime($item['ctime']));
                $daysInfos[$day]++;
                $reason = $item['reason'];
                $cancelReasons[$reason]++;
            }
            $daysInfos = array_values($daysInfos);
            $this->lines = array(
                array(
                    'tooltip' => array(
                        'valueSuffix',
                        '元'
                    ),
                    'name' => '取消订单数量',
                    'data' => $daysInfos,
                )
            );

            //饼状图
            $this->total = count($this->list);
            foreach ($cancelReasons as $reason => $num)
            {
                $this->cancelResons[] = array(
                    'name' => $reason ? Conf_Comment::$CANCEL_REASONS[$reason] : '无',
                    'y' => round($num * 100 / $this->total, 2),
                    'percentage' => round($num * 100 / $this->total, 2),
                    'num' => $num,
                );
            }
        }
        else
        {
            //没有退款单，那么数量和金额都是0
            //没有退款单，百分比也就是0，没必要去统计订单数量和订单金额，赋一个假值1
            $this->cancelNum = 0;
            $this->lines = array();
        }

        $this->addHeadJs(array(
                             'js/jquery.min.js',
                             'js/highcharts/highcharts.js',
                             'js/highcharts/modules/exporting.js',
                             'js/highcharts/modules/data.js',
                             'js/highcharts/modules/drilldown.js',
                             'js/highcharts/themes/dark_unica.js',
                         ));

        $this->addFootJs(array(
                             'js/apps/highcharts/cancel.js',
                         ));
        $this->addCss(array(
                          'css/highcharts/unica_one.css'
                      ));
    }

    protected function outputBody()
    {
        $this->smarty->assign('start_time', $this->startTime);
        $this->smarty->assign('end_time', $this->endTime);
        $this->smarty->assign('cancel_total', $this->cancelNum);
        $this->smarty->assign('order_total', $this->orderNum);
        $this->smarty->assign('days', json_encode($this->days));
        $this->smarty->assign('lines', json_encode($this->lines));
        $this->smarty->assign('cancel_reason_data', json_encode($this->cancelResons));
        $this->smarty->assign('page_data', $this->pageData);
        $this->smarty->assign('total', $this->total);
        $app = '/statistics/cancel_statistics.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);
        $this->smarty->assign('pageHtml', $pageHtml);

        $this->smarty->display('statistics/cancel_statistics.html');
    }
}

$app = new App('pri');
$app->run();