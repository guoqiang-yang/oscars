<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    
    private $did;
    private $otype;
    
    protected function getPara()
    {
        $this->did = Tool_Input::clean('r', 'did', TYPE_UINT);
        $this->otype = Tool_Input::clean('r', 'otype', TYPE_STR);
    }
    
    protected function checkPara()
    {
        if (empty($this->did) || empty($this->otype))
        {
            throw new Exception('参数错误');
        }
    }
    
    protected function main()
	{
		switch ($this->otype)
        {
            case 'reset_df_passwd':
                Logistics_Auth_Api::resetDefaultPasswd($this->did, Conf_Base::COOPWORKER_DRIVER);
                break;
            default:
                break;
        }
	}

	protected function outputPage()
	{
		$result = array('did' => $this->did);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App();
$app->run();