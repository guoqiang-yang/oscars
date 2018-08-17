<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
    private $pageNum = 20;
    private $sid;
    private $wid;
    
    private $skuInfo;
    private $fifoCostList;
    private $historyList;
    
    protected function getPara()
    {
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
    }
    
    protected function main()
    {
        $this->fifoCostList = Shop_Cost_Api::getCostInFifoQueue($this->sid, $this->wid);
        
        $this->historyList = Shop_Cost_Api::getFifoCostHistory($this->sid, $this->wid, 0, $this->pageNum);
     
        $this->skuInfo = !empty($this->sid)? Shop_Api::getSkuInfo($this->sid): array('title'=>'请输入SKU ID查询！');
    }
    
    protected function outputBody()
    {
        $pageHtml = Str_Html::getJsPagehtml2(0, $this->pageNum, $this->historyList['total'], 'fifo_history_list');
        
        $this->smarty->assign('sid', $this->sid);
        $this->smarty->assign('wid', $this->wid);
        $this->smarty->assign('skuinfo', $this->skuInfo);
        $this->smarty->assign('fifo_cost_list', $this->fifoCostList);
        $this->smarty->assign('fifo_history', $this->historyList['data']);
        $this->smarty->assign('history_total', $this->historyList['total']);
        $this->smarty->assign('allow_worehouses', $this->getAllowedWids4User(true));
        
        $this->smarty->assign('page_html', $pageHtml);
        $this->smarty->display('shop/cost_queue.html');
    }
    
}

$app = new App();
$app->run();