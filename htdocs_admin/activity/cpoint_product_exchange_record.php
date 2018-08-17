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
            'btime' => Tool_Input::clean('r', 'btime', TYPE_STR),
            'etime' => Tool_Input::clean('r', 'etime', TYPE_STR),
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'uid' => Tool_Input::clean('r', 'uid', TYPE_UINT),
            'pid' => Tool_Input::clean('r', 'pid', TYPE_UINT),
            'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
        );
        if(isset($_REQUEST['isexpress']))
        {
            $this->searchConf['isexpress'] = Tool_Input::clean('r', 'isexpress', TYPE_UINT);
        }
    }

    protected function main()
    {
        $res = Cpoint_Api::getExchangeProductList($this->searchConf, $this->start, $this->pageNum);
        $this->productList = $res['list'];
        $this->total = $res['total'];
        $this->addFootJs(array('js/apps/cpoint_express.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $app = '/activity/cpoint_product_exchange_record.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->pageNum, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('list', $this->productList);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('search_conf', $this->searchConf);
        unset($this->searchConf['isexpress']);
        $stepUrl = '/activity/cpoint_product_exchange_record.php?' . http_build_query($this->searchConf);
        $this->smarty->assign('step_url', $stepUrl);
        $this->smarty->assign('express_list', Conf_Cpoint::getExpress());
        $this->smarty->display('activity/cpoint_product_exchange_record.html');
    }
}

$app = new App('pri');
$app->run();

