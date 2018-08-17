<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    private $fromCmid;
    private $toCmid;
    
    protected function getPara()
    {
        $this->fromCmid = Tool_Input::clean('r', 'from_cmid', TYPE_UINT);
	    $this->toCmid = Tool_Input::clean('r', 'to_cmid', TYPE_UINT);
    }
    
    protected function checkPara()
    {
        if (empty($this->fromCmid))
        {
            throw new Exception('common:params error');
        }
	    if (empty($this->toCmid))
	    {
		    throw new Exception('没有选择合并到哪个小区');
	    }
    }
    
    protected function main()
    {
        Order_Community_Api::mergeCommunity($this->fromCmid, $this->toCmid);
    }
    
    protected function outputBody()
    {
	    $result = array('from_cmid' => $this->fromCmid, 'to_cmid' => $this->toCmid);
	    $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App();
$app->run();