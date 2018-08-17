<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $start;
    private $searchConf;
    private $num = 20;
    private $total;
    private $dayList;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->searchConf = array(
            'bid' => Tool_Input::clean('r', 'bid', TYPE_UINT),
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
        $this->dayList = Agent_Api::getAgentBillDayList($this->searchConf, $this->total ,$this->start, $this->num);

        $this->addFootJs(array('js/apps/agent_flow.js'));
    }

    protected function outputBody()
    {
        $app = '/finance/agent_bill_day_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('day_list', $this->dayList);
        $this->smarty->assign('total', $this->total);
        $this->smarty->display('finance/agent_bill_day_list.html');
    }
}

$app = new App('pri');
$app->run();