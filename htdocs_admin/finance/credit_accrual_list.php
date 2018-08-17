<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
    private $num = 20;
    private $start;
    private $search;
    private $total;
    private $list;
    
    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->search = array(
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
            'start_date' => Tool_Input::clean('r', 'start_date', TYPE_STR),
            'end_date' => Tool_Input::clean('r', 'end_date', TYPE_STR),
        );
    }
    
    protected function main()
    {
        $data = Tpfinance_Api::getAccrualList($this->search, $this->start, $this->num);
        $this->list = $data['list'];
        $this->total = $data['total'];
    }
    
    protected function outputBody()
    {
        $app = '/finance/credit_accrual_list.php?' . http_build_query($this->search);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('search', $this->search);

        $this->smarty->display('finance/credit_accrual_list.html');
    }
    
}

$app = new App();
$app->run();