<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    
    private $wid;
    private $sid;
    
    private $response;
    
    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/location_list');
    }

    protected function getPara()
    {
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_INT);
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_INT);
        
    }
    
    protected function main()
    {
        $wso = new Warehouse_Stock_Occupied();
        
        $this->response = $wso->autoRefreshOccupied($this->wid, array($this->sid));
        
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