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
                             'js/apps/highcharts/cate.js',
                         ));
        $this->addCss(array(
                          'css/highcharts/unica_one.css'
                      ));

        $this->data = Statistics_Api::getCateInfo($this->days, $this->cityId);
    }

    protected function outputBody()
    {
        $this->smarty->assign('day', $this->days);
        $this->smarty->assign('profit', json_encode($this->data['profit']));
        $this->smarty->assign('price', json_encode($this->data['price']));
        $this->smarty->assign('city_id', $this->cityId);
        $this->smarty->assign('city_list', Conf_City::$CITY);

        $this->smarty->display('statistics/cate.html');
    }
}

$app = new App('pri');
$app->run();
