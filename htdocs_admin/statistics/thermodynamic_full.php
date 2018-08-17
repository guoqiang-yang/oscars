<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $data;
    private $month;
    private $cityId;
    private $year;
    private $yearList;
    private $monthList;
    protected $headTmpl = 'head/head_none.html';
    protected $tailTmpl = 'tail/tail_none.html';

    protected function getPara()
    {
        $this->year = Tool_Input::clean('r', 'year', TYPE_STR);
        $this->month = Tool_Input::clean('r', 'month', TYPE_STR);
        $this->cityId = Tool_Input::clean('r', 'city_id', TYPE_STR);
    }

    protected function checkPara()
    {
        if (empty($this->month))
        {
            if (date('d') == '01')
            {
                $this->month = date('m', strtotime('-1 month'));
            }
            else
            {
                $this->month = date('m');
            }
        }

        if (empty($this->year))
        {
            $this->year = date('Y');
        }
    }

    protected function main()
    {
        $this->monthList = array(1,2,3,4,5,6,7,8,9,10,11,12);
        $this->yearList = array();
        for ($y=2015; $y<=date('Y'); $y++)
        {
            $this->yearList[] = $y;
        }
        
        $month = $this->year . '-' . $this->month;
        $startDate = date('Y-m-01 00:00:00', strtotime($month));
        $endDate = date('Y-m-t 23:59:59', strtotime($month));
        $communityOrders = array();
        $start = 0;
        $step = 5000;
        do
        {
            $where = sprintf('status=%d AND delivery_date>="%s" AND delivery_date<="%s" AND step>=%d',
                             Conf_Base::STATUS_NORMAL, $startDate, $endDate, Conf_Order::ORDER_STEP_PICKED, $this->cityId);
            if (!empty($this->cityId))
            {
                $where .= sprintf(' AND city_id=%d', $this->cityId);
            }
            $orders = Order_Api::getOrderListByWhere($where, array(), $start, $step);
            if (count($orders) <= 0)
            {
                break;
            }

            foreach ($orders as $order)
            {
                $communityId = $order['community_id'];
                if(!empty($this->cityId) && Conf_City::BEIJING != $this->cityId)
                {
                    $communityOrders[$communityId]+=10;
                }
                else
                {
                    $communityOrders[$communityId]++;
                }
            }

            $start += $step;
        }
        while (count($orders) > 0);

        if (!empty($communityOrders))
        {
            $cmids = array_keys($communityOrders);
            $communitys = Order_Community_Api::getByIds($cmids);

            foreach ($communitys as $c)
            {
                $cid = $c['cmid'];
                $this->data[] = array(
                    'lng' => $c['lng'],
                    'lat' => $c['lat'],
                    'count' => $communityOrders[$cid] * 7,
                );
            }
        }

        $this->addFootJs(array(
                             'http://api.map.baidu.com/api?v=2.0&ak=YnOoqPMg9gnlLYqO2ew3LwQI',
                             'http://api.map.baidu.com/library/Heatmap/2.0/src/Heatmap_min.js',
                             'js/apps/thermodynamic.js',
                         ));
    }

    protected function outputBody()
    {

        $this->smarty->assign('data', json_encode($this->data));
        $this->smarty->assign('city_id', $this->cityId);
        $this->smarty->assign('city_list', Conf_City::$CITY);
        $this->smarty->assign('month', $this->month);
        $this->smarty->assign('key', Conf_Base::BAIDU_KEY);
        $this->smarty->assign('year_list', $this->yearList);
        $this->smarty->assign('month_list', $this->monthList);
        $this->smarty->assign('year', $this->year);

        $this->smarty->display('statistics/thermodynamic_full.html');
    }
}

$app = new App('pri');
$app->run();
