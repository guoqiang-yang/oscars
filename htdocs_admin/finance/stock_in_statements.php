<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $statementInfo;
    private $searchConf;
    private $start;
    private $total;
    private $num = 20;
    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->searchConf = array(
            'id' => Tool_Input::clean('r', 'id', TYPE_UINT),
            'supplier_id' => Tool_Input::clean('r', 'supplier_id', TYPE_UINT),
            'suid' => Tool_Input::clean('r', 'suid', TYPE_UINT),
            'start_ctime' => Tool_Input::clean('r', 'start_ctime', TYPE_STR),
            'end_ctime' => Tool_Input::clean('r', 'end_ctime', TYPE_STR),
            'payment_type' => Tool_Input::clean('r', 'payment_type', TYPE_UINT),
            'is_invoice' => Tool_Input::clean('r', 'is_invoice', TYPE_UINT)
        );
        $this->searchConf['paid'] = isset($_REQUEST['paid'])? $_REQUEST['paid']: Conf_Base::STATUS_ALL;
    }

    protected function main()
    {
        $this->statementInfo = Finance_StockIn_Statements_Api::getStatementList($this->searchConf, $this->total, $this->start, $this->num);
        $this->addFootJs(array('js/apps/stock.js'));
    }

    protected function outputBody()
    {
        $app = '/finance/stock_in_statements.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('statement_list', $this->statementInfo);
        $this->smarty->assign('search_conf', $this->searchConf);
        unset($this->searchConf['paid']);
        $page_url = '/finance/stock_in_statements.php?' . http_build_query($this->searchConf);
        $this->smarty->assign('page_url',$page_url);
        $this->smarty->assign('paid_sources', Conf_Finance::$MONEY_OUT_PAID_TYPES);
        $this->smarty->display('finance/stock_in_statements.html');
    }
}

$app = new App('pri');
$app->run();

