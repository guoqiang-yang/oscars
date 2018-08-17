<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $data;
    private $cate1;
    private $wid;
    private $start;
    private $num = 20;
    private $total;
    private $download = 0;

    protected function getPara()
    {
        $this->cate1 = Tool_Input::clean('r', 'cate1', TYPE_UINT);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->download = Tool_Input::clean('r', 'download', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->wid))
        {
            $this->wid = $this->getAllAllowedWids4User(0, true);
        }
    }

    protected function main()
    {
        if (!empty($this->download))
        {
            Statistics_Api::exportForWeb(Conf_Statics::TYPE_SKU_STOCK_INFO, $this->cate1, $this->wid);
            exit;
        }
        $result = Statistics_Api::getStockSkuList($this->cate1, $this->wid, $this->start, $this->num);
        $this->data = $result['list'];
        $this->total = $result['total'];
    }

    protected function outputBody()
    {
        if(is_array($this->wid))
        {
            $this->wid = 0;
        }
        $app = '/statistics/stock_sku.php?cate1=' . $this->cate1 . '&wid=' . $this->wid;
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('data', $this->data);
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('warehouse_list', Conf_Warehouse::$WAREHOUSES);
        $this->smarty->assign('cate1', $this->cate1);
        $this->smarty->assign('wid', $this->wid);
        $this->smarty->assign('wids', $this->getAllAllowedWids4User(0, true));

        $this->smarty->display('statistics/stock_sku.html');
    }
}

$app = new App('pri');
$app->run();
