<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $start;
    private $searchConf;
    private $num = 20;
    private $total;
    private $historyList;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->searchConf = array(
            'aid' => Tool_Input::clean('r', 'aid', TYPE_UINT),
            'title' => Tool_Input::clean('r', 'title', TYPE_STR),
            'objtype' => Tool_Input::clean('r', 'objtype', TYPE_UINT),
            'from_date' => Tool_Input::clean('r', 'from_date', TYPE_STR),
            'end_date' => Tool_Input::clean('r', 'end_date', TYPE_STR),
        );
    }

    protected function checkPara()
    {

    }

    protected function main()
    {
        $this->historyList = Agent_Api::getAgentAmountHistoryList($this->searchConf, $this->total ,$this->start, $this->num);

        $this->addFootJs(array('js/apps/agent_flow.js'));
    }

    protected function outputBody()
    {
        $app = '/finance/agent_flow_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('history_list', $this->historyList);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('pay_types', Conf_Finance::$MONEY_OUT_PAID_TYPES);
        $this->smarty->assign('all_types', Conf_Agent::getAgentAmountHistoryTypes());
        
        $this->smarty->display('finance/agent_flow_list.html');
    }
}

$app = new App('pri');
$app->run();