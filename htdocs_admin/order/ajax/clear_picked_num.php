<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    
    private $oid;
    private $pid;
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_INT);
        $this->pid = Tool_Input::clean('r', 'pid', TYPE_INT);
    }
    
    protected function checkAuth()
    {
        parent::checkAuth('/order/ajax/refresh_force');
    }
    
    protected function checkPara()
    {
        if (empty($this->oid) || empty($this->pid))
        {
            throw new Exception('数据异常');
        }
    }


    protected function main()
    {
        $oo = new Order_Order();
        
        $oo->updateOrderProductInfo($this->oid, $this->pid, 0, array('picked'=>0));
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