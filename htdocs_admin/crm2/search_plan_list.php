<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $cid;
    private $num = 20;
    private $start;
    private $list;
    private $total;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
    }

    protected function main()
    {
        $data = Saas_Api::getCustomerSearchList($this->_uid, $this->cid, $this->start, $this->num);
        $this->total = $data['total'];
        $this->list = $data['list'];
    }

    protected function outputBody()
    {
        $app = '/crm2/search_plan_list.php?cid=' . $this->cid;
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('cid', $this->cid);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('list', $this->list);

        $this->smarty->display('crm2/search_plan_list.html');
    }
}

$app = new App('pri');
$app->run();