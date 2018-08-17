<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    
    private $planId;
    private $taskId;
    private $cate1;
    private $cate2;
    
    private $num = 40;
    private $start = 0;
    
    private $products;
    private $total;
    private $cates;
    
    protected function getPara()
    {
        $this->planId = Tool_Input::clean('r', 'plan_id', TYPE_UINT);
        $this->taskId = Tool_Input::clean('r', 'task_id', TYPE_UINT);
        $this->cate1 = Tool_Input::clean('r', 'cate1', TYPE_UINT);
        $this->cate2 = Tool_Input::clean('r', 'cate2', TYPE_UINT);
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
    }
    
    protected function main()
    {
        $ret = Warehouse_Api::getInventoryProductsByCate($this->planId, $this->taskId, $this->cate1, $this->cate2, $this->start, $this->num, true);
        
        $this->total = $ret['total'];
        $this->products = $ret['list'];
        $this->cates = $ret['cates'];
    }
    
    protected function outputBody()
    {
        $app = '/warehouse/inventory_product_list.php?plan_id='.$this->planId.'&task_id='.$this->taskId;
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);
        
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('product_list', $this->products);
        $this->smarty->assign('cates', $this->cates);
        $this->smarty->assign('show_sub_title', '计划号:'.$this->planId.(!empty($this->taskId)?' - 任务号:'.$this->taskId:''));
        $this->smarty->assign('cate1', Conf_Sku::$CATE1);
        $this->smarty->assign('cate2', Conf_Sku::$CATE2);
        $this->smarty->assign('request_uri', $app);
        
        $this->smarty->display('warehouse/inventory_product_list.html');
    }
    
}

$app = new App();
$app->run();