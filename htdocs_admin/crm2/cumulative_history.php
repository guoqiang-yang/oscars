<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    // cgi参数
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
            'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
        );
    }

    protected function main()
    {
        $data = Crm2_Api::getCumulativeHistory($this->searchConf, $this->start, $this->num);

        $this->total = $data['total'];
        $this->list = $data['list'];
    }

    protected function outputBody()
    {
        $app = '/crm2/cumulative_history.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('cumulative_threshold', Conf_Coupon::$CUMULATIVE_STEP);

        $this->smarty->display('crm2/cumulative_history.html');
    }
}

$app = new App('pri');
$app->run();

