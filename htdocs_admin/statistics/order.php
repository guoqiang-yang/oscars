<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $data;
    private $mode;
    private $startDate;
    private $endDate;
    private $startTime;
    private $endTime;
    private $cityId;

    protected function getPara()
    {
        $this->startDate = Tool_Input::clean('r', 'start_date', TYPE_STR);
        $this->endDate = Tool_Input::clean('r', 'end_date', TYPE_STR);
        $this->startTime = Tool_Input::clean('r', 'start_time', TYPE_STR);
        $this->endTime = Tool_Input::clean('r', 'end_time', TYPE_STR);
        $this->mode = Tool_Input::clean('r', 'sel_mode', TYPE_STR);
        $this->cityId = Tool_Input::clean('r', 'city_id', TYPE_STR);
    }

    protected function checkPara()
    {
        if (empty($this->mode))
        {
            $this->mode = 'ctime';
        }
        if (empty($this->startDate))
        {
            $this->startDate = date('Y-m-d', strtotime('-30 days'));
        }
        if (empty($this->endDate))
        {
            $this->endDate = date('Y-m-d');
        }
        if (empty($this->startTime))
        {
            $this->startTime = '00:00:00';
        }
        if (empty($this->endTime))
        {
            $this->endTime = '23:59:59';
        }
        if (empty($this->cityId))
        {
            $this->cityId = 'all';
        }
    }

    protected function main()
    {
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
        $this->data = Statistics_Api::getOrderInfo($this->startDate, $this->endDate, $this->startTime, $this->endTime, $this->mode, $this->cityId);
    }

    protected function outputBody()
    {
        $this->smarty->assign('days', json_encode($this->data['days']));
        $this->smarty->assign('total', json_encode($this->data['total']));
        $this->smarty->assign('price', json_encode($this->data['price']));

        $this->smarty->assign('mode', $this->mode);
        $this->smarty->assign('start_date', $this->startDate);
        $this->smarty->assign('end_date', $this->endDate);
        $this->smarty->assign('start_time', $this->startTime);
        $this->smarty->assign('end_time', $this->endTime);
        $this->smarty->assign('city_id', $this->cityId);
        $this->smarty->assign('city_list', Conf_City::$CITY);

        $this->smarty->display('statistics/order.html');
    }
}

$app = new App('pri');
$app->run();
