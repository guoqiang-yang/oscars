<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $pageNum = 20;
    // 中间结果
    private $recordList;
    private $total;
    private $searchConf;
    private $start;
    private $totalPrivilege;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->searchConf = array(
            'send_suid' => Tool_Input::clean('r', 'suid', TYPE_UINT),
            'month' => Tool_Input::clean('r', 'month', TYPE_STR),
            'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
            'bdate' => Tool_Input::clean('r', 'bdate', TYPE_STR),
            'edate' => Tool_Input::clean('r', 'edate', TYPE_STR),
        );
    }

    protected function checkPara()
    {
        if(empty($this->searchConf['bdate']))
        {
            unset($this->searchConf['bdate']);
        }
        if(empty($this->searchConf['edate']))
        {
            unset($this->searchConf['edate']);
        }
        if(empty($this->searchConf['oid']))
        {
            unset($this->searchConf['oid']);
        }
    }

    protected function main()
    {
        $osp = new Order_Sale_Preferential();
        list($this->recordList, $this->totalPrivilege) = $osp->getListRawWhere($this->searchConf, $this->total, array(),$this->start, $this->pageNum);
        $this->addFootJs(array(''));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $app = '/order/city_privilege_show.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->pageNum, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('list', $this->recordList);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('total_privilege', $this->totalPrivilege);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->display('order/city_privilege_show.html');
    }
}

$app = new App('pri');
$app->run();

