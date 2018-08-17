<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $data;
    private $startTime;
    private $endTime;
    private $cityId;

    protected function getPara()
    {
        $this->startTime = Tool_Input::clean('r', 'start_time', TYPE_STR);
        $this->endTime = Tool_Input::clean('r', 'end_time', TYPE_STR);
        $this->cityId = Tool_Input::clean('r', 'city_id', TYPE_STR);
    }

    protected function checkPara()
    {
        if (empty($this->endTime))
        {
            $this->endTime = date('Y-m-d');
        }

        if (empty($this->startTime))
        {
            $this->startTime = date('Y-m-01');
        }

        if (empty($this->cityId))
        {
            $this->cityId = 'all';
        }
    }

    protected function main()
    {
        $this->data = Statistics_Api::getPriceRegion($this->startTime, $this->endTime, $this->cityId);
    }

    protected function outputBody()
    {
        $this->smarty->assign('start_time', $this->startTime);
        $this->smarty->assign('end_time', $this->endTime);
        $this->smarty->assign('data', $this->data);
        $this->smarty->assign('city_id', $this->cityId);
        $this->smarty->assign('city_list', Conf_City::$CITY);

        $this->smarty->display('statistics/price.html');
    }
}

$app = new App('pri');
$app->run();
