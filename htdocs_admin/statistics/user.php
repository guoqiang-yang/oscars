<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $data;
    private $days;
    private $cityId;

    protected function getPara()
    {
        $this->days = Tool_Input::clean('r', 'days', TYPE_INT);
        $this->cityId = Tool_Input::clean('r', 'city_id', TYPE_STR);
    }

    protected function checkPara()
    {
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
                             'js/apps/highcharts/user.js',
                         ));
        $this->addCss(array(
                          'css/highcharts/unica_one.css'
                      ));

        $this->data = Statistics_Api::getUserInfo($this->days, $this->cityId);
    }

    protected function outputBody()
    {
        $this->smarty->assign('day', $this->days);
        $this->smarty->assign('days', json_encode($this->data['days']));
        $this->smarty->assign('total', json_encode($this->data['total']));
        $this->smarty->assign('new', json_encode($this->data['new']));
        $this->smarty->assign('city_id', $this->cityId);
        $this->smarty->assign('city_list', Conf_City::$CITY);

        $this->smarty->display('statistics/user.html');
    }
}

$app = new App('pri');
$app->run();
