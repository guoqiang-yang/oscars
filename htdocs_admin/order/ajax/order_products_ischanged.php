<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    
    private $oid;
    
    private $priceInfo;
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->oid))
        {
            throw new Exception('参数异常：没有订单id');
        }
    }
    
    protected function main()
    {
        
        $this->priceInfo = Order_Helper::getOrderProductsPriceWithRealTime($this->oid);
        
    }
    
    protected function outputBody()
    {
		$response = new Response_Ajax();
		$response->setContent($this->priceInfo);
		$response->send();
        
		exit;
    }
    
}

$app = new App('pub');
$app->run();