<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $dueDate;
	private $cid;
	private $note;

	protected function getPara()
	{
		$this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
		$this->dueDate = Tool_Input::clean('r', 'due_date', TYPE_STR);
		$this->note = Tool_Input::clean('r', 'note', TYPE_STR);
	}
	
	protected function main()
	{
        $upData = array(
            'payment_due_date' => $this->dueDate, 
            'last_remind_suid' => $this->_uid, 
            'last_remind_date' => date('Y-m-d H:i:s')
        );
        
		Crm2_Api::updateCustomerInfo($this->cid, $upData, array('remind_count' => 1) );

        $upTData = array(
            'cid' => $this->cid, 
            'type' => 2, 
            'edit_suid' => $this->_uid, 
            'content' => $this->note,
        );
		Crm2_Api::saveCustomerTracking(0, $upTData);
	}
	
	protected function outputPage()
	{
		$result = array('res'=> 'succ');

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App('pri');
$app->run();