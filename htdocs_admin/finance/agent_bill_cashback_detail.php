<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $id;
    private $billInfo;

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
    }

    protected function checkPara()
    {
        if(empty($this->id))
        {
            throw new Exception('common: param error');
        }
    }

    protected function main()
    {
        $this->billInfo = Agent_Api::getAgentBillCashbackInfo($this->id);
        $this->addFootJs('js/apps/agent_bill_cashback.js');
    }

    protected function outputBody()
    {
        $this->smarty->assign('bill_info', $this->billInfo);
        $this->smarty->assign('type_list', Conf_Agent::getAgentCashbackTypes());
        $this->smarty->display('finance/agent_bill_cashback_detail.html');
    }
}

$app = new App('pri');
$app->run();
