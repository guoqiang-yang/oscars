<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $id;
    private $num;
    private $loc;
    private $sid;
    
    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->num = Tool_Input::clean('r', 'num', TYPE_UINT);
        $this->loc = Tool_Input::clean('r', 'loc', TYPE_STR);
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
    }
    
    protected function checkPara()
    {
        if (!empty($this->num))
        {
            throw new Exception('删除的货位有库存，请核对！！');
        }
    }
    
    protected function main()
    {
        Warehouse_Location_Api::delLocation($this->id, $this->sid, $this->loc);
    }
    
    protected function outputBody()
    {
        $result = array('id' => $this->id);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
    }
}

$app = new App();
$app->run();