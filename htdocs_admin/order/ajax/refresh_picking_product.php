<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $pid;
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
    }
    
    protected function checkPara()
    {
        if (empty($this->oid) || empty($this->pid))
        {
            throw new Exception('common:params error');
        }
    }
    
    protected function main()
    {
        Order_Picking_Api::refreshPickingProduct($this->oid, $this->pid);
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