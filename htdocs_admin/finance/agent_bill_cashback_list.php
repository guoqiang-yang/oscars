<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $start;
    private $searchConf;
    private $num = 20;
    private $total;
    private $cashbackList;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->searchConf = array(
            'id' => Tool_Input::clean('r', 'id', TYPE_UINT),
            'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
            'day' => Tool_Input::clean('r', 'day', TYPE_STR),
            'aid' => Tool_Input::clean('r', 'aid', TYPE_UINT),
            'title' => Tool_Input::clean('r', 'title', TYPE_STR),
        );
    }

    protected function checkPara()
    {

    }

    protected function main()
    {
        $this->cashbackList = Agent_Api::getAgentBillCashbackList($this->searchConf, $this->total ,$this->start, $this->num);
    }

    protected function outputBody()
    {
        $app = '/finance/agent_bill_cashback_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('cashback_list', $this->cashbackList);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('type_list', Conf_Agent::getAgentCashbackTypes());
        $this->smarty->display('finance/agent_bill_cashback_list.html');
    }
}

$app = new App('pri');
$app->run();