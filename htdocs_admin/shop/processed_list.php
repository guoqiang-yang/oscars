<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $num = 20;
    private $start;
    private $search;
    
    private $list;
    private $total;
    
    protected function getPara()
    {
        $this->search = array(
            'sid' => Tool_Input::clean('r', 'sid', TYPE_UINT),
            'wid' => $this->getWarehouseId(),
            'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
        );
        
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
    }
    
    protected function main()
    {
        $spo = new Shop_Processed_Order();
        
        $this->list = $spo->getList($this->search, $this->start, $this->num);
        $this->total = $spo->getTotal($this->search);
    }
    
    protected function outputBody()
    {
        
        $app = '/shop/processed_list.php?' . http_build_query($this->search);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);
        
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('search', $this->search);
        $this->smarty->assign('all_types', Conf_Stock::getProcessedOrderTypes());
        $this->smarty->assign('all_warehouses', $this->getAllowedWarehouses());
        
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('total', $this->total);
        
        $this->smarty->display('shop/processed_list.html');
    }
    
}

$app = new App();
$app->run();