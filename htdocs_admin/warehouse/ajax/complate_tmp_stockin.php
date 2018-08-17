<?php

/**
 * 临采入库单.
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $inorderId;
    private $products;
    
    protected function getPara()
    {
        $this->inorderId = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->products = json_decode(Tool_Input::clean('r', 'products', TYPE_STR), true);
    }
    
    protected function main()
    {   
        echo "Sorry!!";exit;
        
        Warehouse_Temp_Purchase_Api::complateTmpStockin($this->inorderId, $this->products, $this->_uid);
    }
    
    protected function outputBody()
    {
        $result = array('st' => 1);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
    }

}

$app = new App();
$app->run();