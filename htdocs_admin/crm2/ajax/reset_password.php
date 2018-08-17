<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $bid;
    private $response;


    protected function getPara()
    {
        $this->bid = Tool_Input::clean('r', 'bid', TYPE_UINT);
        $this->response = array('errno'=>0, 'data'=>array());
    }
    
    protected function checkPara()
    {
        if (empty($this->bid))
        {
            throw new Exception('common:params error');
        }
    }
    
    protected function main()
    {
        $this->_resetDfBusinessPasswd();
    }
    
    protected function outputBody()
    {
        $response = new Response_Ajax();
        $response->setContent($this->response);
        $response->send();
        
        exit;
    }
    
    private function _resetDfBusinessPasswd()
    {
        Business_Auth_Api::resetDefaultPasswd($this->bid);
        
        $this->response['data']['ret'] = 1;
    }
}

$app = new App();
$app->run();