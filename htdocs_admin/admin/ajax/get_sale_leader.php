<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $suid;
	private $cityId;
    
    private $result = array('errno'=>0);

	protected function getPara()
	{
        $this->suid = Tool_Input::clean('r', 'suid', TYPE_UINT);
        
		$this->cityId = Tool_Input::clean('r', 'city_id', TYPE_UINT);
	}

    
    protected function main()
    {
        $this->result['data'] = Admin_Api::getSalesLeaders($this->suid, $this->cityId);
    }
    
    protected function outputPage()
	{
        echo json_encode($this->result);
        exit;
	}
	
}

$app = new App('pub');
$app->run();

