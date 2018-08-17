<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    // 订单：oid:priority,oid:priority,...
    // 车型：car_model:fee,car_model:fee,...
    private $oids;
    private $carModels;
    private $lineType;
    
    private $response;
    
    protected function getPara()
    {
        $this->oids = explode(',', Tool_Input::clean('r', 'oids', TYPE_STR));
        $this->carModels = explode(',', Tool_Input::clean('r', 'car_models', TYPE_STR));
        $this->lineType = Tool_Input::clean('r', 'line_type', TYPE_STR);
    }
    
    protected function checkPara()
    {
        if (empty($this->oids) || empty($this->carModels) || empty($this->lineType))
        {
            throw new Exception('common:params error');
        }
        
        if (count($this->oids) > 10)
        {
            throw new Exception('线路上订单太多，请重新安排！');
        }
    }
    
    protected function main()
    {
//        if (ENV == 'online')
//        {
//            throw new Exception('Could you wait for a moment？');
//        }
        
        $this->response = Logistics_Order_Api::saveOrderLine($this->oids, $this->carModels, $this->_uid, $this->lineType);
        
    }
    
    protected function outputBody()
    {
        $response = new Response_Ajax();
		$response->setContent($this->response);
		$response->send();
        
		exit;
    }
}

$app = new App();
$app->run();