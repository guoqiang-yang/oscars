<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $pageNum = 20;
    private $start;
    private $sid;
    private $wid;
    
    private $historyList;
    
    protected function getPara()
    {
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        
    }
    
    protected function main()
    {
        $this->historyList = Shop_Cost_Api::getFifoCostHistory($this->sid, $this->wid, $this->start, $this->pageNum);
    }
    
    protected function outputBody()
    {
        $pageHtml = Str_Html::getJsPagehtml2($this->start, $this->pageNum, $this->historyList['total'], 'fifo_history_list');
        
        $this->smarty->assign('page_html', $pageHtml);
        $this->smarty->assign('fifo_history', $this->historyList['data']);
        $this->smarty->assign('history_total', $this->historyList['total']);
        $html = $this->smarty->fetch('shop/cost_history_queue.html');
        
        $response = new Response_Ajax();
		$response->setContent(array('html'=>$html));
		$response->send();

		exit;
    }
}

$app = new App();
$app->run();