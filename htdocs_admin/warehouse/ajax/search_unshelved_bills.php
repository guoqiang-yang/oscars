<?php

include_once('../../../global.php');

class app extends App_Admin_Ajax
{
    private $sid;
    private $title;
    private $wid;
    private $vLoc;
    
    private $html;
    
    protected function getPara()
    {
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->title = Tool_Input::clean('r', 'title', TYPE_STR);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->vLoc = Tool_Input::clean('r', 'vloc', TYPE_STR);
    }
    
    protected function main()
    {
        $products = Warehouse_Location_Api::getUnshelvedBills($this->sid, $this->wid, $this->vLoc);
        
        $this->smarty->assign('products', $products);
        $this->smarty->assign('sid', $this->sid);
        $this->smarty->assign('title', $this->title);
        $this->smarty->assign('wid', $this->wid);
        $this->smarty->assign('warehouses', Conf_Warehouse::$WAREHOUSES);
        $this->html = $this->smarty->fetch('warehouse/aj_show_unselved_bills.html');
    }
    
    protected function outputBody()
    {
        $response = new Response_Ajax();
		$response->setContent(array('html'=>$this->html));
		$response->send();
        
        exit;
    }
    
}

$app = new App();
$app->run();