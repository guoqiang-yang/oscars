<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $list;
    private $startTime;
    private $endTime;
    private $start = 0;
    private $num = 200;
    private $type;
    private $cityId;

    protected function getPara()
    {
        $this->startTime = Tool_Input::clean('r', 'start_time', TYPE_STR);
        $this->endTime = Tool_Input::clean('r', 'end_time', TYPE_STR);
        $this->type = Tool_Input::clean('r', 'type', TYPE_STR);
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
            $this->startTime = date('Y-m-d', strtotime('-7 days'));
        }

        if (empty($this->type))
        {
            $this->type = 'num';
        }
        if (empty($this->cityId))
        {
            $this->cityId = array_keys($this->getAllowedCities4User());
        }
    }

    protected function main()
    {
        $this->addHeadJs(array());
        $this->addFootJs(array());
        $this->addCss(array());

        $this->list = Statistics_Api::getHotSale($this->type, $this->startTime, $this->endTime, $this->start, $this->num, $this->cityId);
    }

    protected function outputBody()
    {
        $this->smarty->assign('start_time', $this->startTime);
        $this->smarty->assign('end_time', $this->endTime);
        $this->smarty->assign('list', $this->list['data']);
        $this->smarty->assign('total', count($this->list['data']));
        $this->smarty->assign('type', $this->type);
        $this->smarty->assign('city_id', $this->cityId);
        $this->smarty->assign('city_list', $this->getAllowedCities4User());

        $this->smarty->display('statistics/hot.html');
    }
}

$app = new App('pri');
$app->run();
