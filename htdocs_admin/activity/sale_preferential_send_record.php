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
            'send_suid' => Tool_Input::clean('r', 'send_suid', TYPE_UINT),
            'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
            'bdate' => Tool_Input::clean('r', 'bdate', TYPE_STR),
            'edate' => Tool_Input::clean('r', 'edate', TYPE_STR),
        );
    }

    protected function main()
    {
        $this->searchConf['status'] = Conf_Base::STATUS_NORMAL;
        if(empty($this->searchConf['send_suid']))
        {
            unset($this->searchConf['send_suid']);
        }
        if(empty($this->searchConf['oid']))
        {
            unset($this->searchConf['oid']);
        }
        $osp = new Order_Sale_Preferential();
        list($this->recordList, $this->totalPrivilege) = $osp->getListRawWhere($this->searchConf, $this->total, array(),$this->start, $this->pageNum, array('*'), false);
        $this->addFootJs(array(''));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $app = '/activity/sale_preferential_send_record.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->pageNum, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('list', $this->recordList);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('total_privilege', $this->totalPrivilege);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->display('activity/sale_preferential_send_record.html');
    }
}

$app = new App('pri');
$app->run();

