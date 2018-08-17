<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $data;
    private $startTime;
    private $endTime;

    protected function getPara()
    {
        $this->startTime = Tool_Input::clean('r', 'start_time', TYPE_STR);
        $this->endTime = Tool_Input::clean('r', 'end_time', TYPE_STR);
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
    }

    protected function main()
    {
        $this->data = Statistics_Api::getStockInTop20($this->startTime, $this->endTime);
    }

    protected function outputBody()
    {
        $this->smarty->assign('start_time', $this->startTime);
        $this->smarty->assign('end_time', $this->endTime);
        $this->smarty->assign('data', $this->data);

        $this->smarty->display('statistics/stock_in.html');
    }
}

$app = new App('pri');
$app->run();
