<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $wid;
    private $buyDate;
    private $skuid;
    
    private $retSt;
    
    protected function getPara()
    {
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->skuid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->buyDate = Tool_Input::clean('r', 'buy_date', TYPE_STR);
    }
    
    protected function main()
    {
        $wthp = new Warehouse_Temporary_Had_Purchase();
        
        $upData = array('status'=>Conf_Base::STATUS_DELETED);
        $this->retSt = $wthp->update($this->buyDate, $this->wid, $this->skuid, $upData);
    }
    
    protected function outputBody()
    {
        $result = array('st' => $this->retSt);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
    }
}

$app = New App();
$app->run();