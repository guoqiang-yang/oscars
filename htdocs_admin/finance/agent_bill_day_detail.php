<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $bid;
    private $billInfo;

    protected function getPara()
    {
        $this->bid = Tool_Input::clean('r', 'bid', TYPE_UINT);
    }

    protected function checkPara()
    {
        if(empty($this->bid))
        {
            throw new Exception('common: param error');
        }
    }

    protected function main()
    {
        $this->billInfo = Agent_Api::getAgentBillDayInfo($this->bid);
    }

    protected function outputBody()
    {
        $this->smarty->assign('bill_info', $this->billInfo);
        $this->smarty->display('finance/agent_bill_day_detail.html');
    }
}

$app = new App('pri');
$app->run();
