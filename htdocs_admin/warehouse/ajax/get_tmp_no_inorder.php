<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $num = 20;
    private $deliveryDate;
    private $wid;
    
    private $leftList;
    private $html;
    
    protected function getPara()
    {
        $this->wid = $this->getWarehouseId()?: Conf_Warehouse::WID_5;
        $this->deliveryDate = Tool_Input::clean('r', 'delivery_date', TYPE_STR);
    }
    
    protected function main()
    {
        $this->leftList = Warehouse_Temp_Purchase_Api::getTmpBoughtNoInorder($this->wid, $this->deliveryDate, $this->num);
        
        $this->smarty->assign('wid', $this->wid);
        $this->smarty->assign('left_list', $this->leftList);
        $this->html = $this->smarty->fetch('warehouse/aj_tmp_bought_no_inorder.html');
    }
    
    protected function outputBody()
    {
        $res = array('html' => $this->html);
        
        $response = new Response_Ajax();
		$response->setContent($res);
		$response->send();
        
		exit;
    }
}

$app = new App();
$app->run();
