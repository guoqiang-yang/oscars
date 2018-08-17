<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
    }
    
    protected function checkPara()
    {
        if (empty($this->oid))
        {
            throw new Exception('common:params error');
        }
    }


    protected function main()
    {   
        Warehouse_Temp_Purchase_Api::complateTmpInorder($this->oid, $this->_user);
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