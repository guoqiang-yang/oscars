<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    
    private $wid;
    
    private $html;
    
    protected function getPara()
    {
        $this->wid = $this->getWarehouseId();
    }
    
    protected function main()
    {
        $drivers = Logistics_Api::statAvailabedDrivers($this->wid);
        
        $this->smarty->assign('car_models', Conf_Driver::$CAR_MODEL);
        $this->smarty->assign('drivers', $drivers);
        
        $this->html = $this->smarty->fetch('logistics/aj_get_availabled_drivers.html');
    }
    
    protected function outputBody()
    {
        $response = new Response_Ajax();
		$response->setContent(array('html'=> $this->html));
		$response->send();
        
		exit;
    }
}

$app = new App('pub');
$app->run();
