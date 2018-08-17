<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $keyword;
    private $start;
    private $wid;
    private $num = 20;
    private $total;
    private $products;

    protected function getPara()
    {
        $this->keyword = Tool_Input::clean('r', 'keyword', TYPE_STR);
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->wid = $this->getWarehouseId();
    }

    protected function main()
    {
        if ($this->keyword)
        {
            $res = Shop_Api::searchSku($this->keyword, $this->start, $this->num);
            $this->products = $res['list'];
            $this->total = $res['total'];
        }

        Warehouse_Api::appendStock(0, $this->products);
        $sids = Tool_Array::getFields($this->products, 'sid');
        $wids = array_keys($this->getAllowedWids4User(false));
        $stocks = Warehouse_Api::getStockBySidsWids($sids, $wids);

        foreach($this->products as &$item)
        {
            $item['_stock'] = array_key_exists($item['sid'], $stocks)? $stocks[$item['sid']]: array();
        }

        $this->addFootJs(array('js/apps/stock.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $app = sprintf('/warehouse/stock_search.php?keyword=%s', $this->keyword);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('wid', $this->wid);
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('keyword', $this->keyword);
        $this->smarty->assign('products', $this->products);
        $this->smarty->display('warehouse/stock_search.html');
    }
}

$app = new App('pri');
$app->run();

