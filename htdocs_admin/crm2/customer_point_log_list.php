<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $pageNum = 20;
    // 中间结果
    private $productList;
    private $staffList;
    private $total;
    private $searchConf;
    private $start;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->searchConf = array(
            'uid' => Tool_Input::clean('r', 'uid', TYPE_UINT),
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'mobile' => Tool_Input::clean('r', 'mobile', TYPE_STR),
        );
    }

    protected function main()
    {
        $res = Cpoint_Api::getConsumePointList($this->searchConf, $this->start, $this->pageNum);
        $this->productList = $res['list'];
        $this->total = $res['total'];
        $this->addFootJs(array('js/apps/point_log.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $app = '/crm2/customer_point_log_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->pageNum, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('list', $this->productList);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->display('crm2/customer_point_log_list.html');
    }
}

$app = new App('pri');
$app->run();

