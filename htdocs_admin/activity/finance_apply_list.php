<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $start;
    private $searchConf;
    private $num = 20;
    private $total;
    private $list;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->searchConf = array(
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'mobile' => Tool_Input::clean('r', 'mobile', TYPE_STR),
            'company' => Tool_Input::clean('r', 'company', TYPE_STR),
            'legal_person_name' => Tool_Input::clean('r', 'legal_person_name', TYPE_STR),
            'step' => Tool_Input::clean('r', 'step', TYPE_INT),
        );
    }

    protected function main()
    {
        $data = Tpfinance_Api::getList($this->searchConf, $this->start, $this->num);

        $this->list = $data['list'];
        $this->total = $data['total'];
    }

    protected function outputBody()
    {
        $app = '/activity/finance_apply_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('step_show', Conf_Ex_Finance::getApplyStep());

        $this->smarty->display('activity/finance_apply_list.html');
    }
}

$app = new App('pri');
$app->run();

