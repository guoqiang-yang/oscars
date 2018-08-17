<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $deliveryType;
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->deliveryType = Tool_Input::clean('r', 'delivery_type', TYPE_UINT);
        if (empty($this->deliveryType))
        {
            $this->deliveryType = Conf_Order::DELIVERY_BY_YOURSELF;
        }
    }
    
    protected function checkPara()
    {
        if (empty($this->oid) || !array_key_exists($this->deliveryType, Conf_Order::$DELIVERY_TYPES))
        {
            throw new Exception('order change delivery: param error');
        }
    }
    
    protected function main()
    {
        Order_Api::changeDeliveryType($this->oid, $this->deliveryType, $this->_user);
    }
    
    protected function outputPage()
	{
		$result = array('oid' => $this->oid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App();
$app->run();