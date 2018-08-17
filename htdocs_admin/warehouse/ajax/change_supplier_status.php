<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    private $sid;
    private $status;
    private $reason;

	protected function getPara()
	{
	    $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
	    $this->status = Tool_Input::clean('r', 'status', TYPE_UINT);
	    $this->reason = Tool_Input::clean('r', 'reason', TYPE_STR);
	}

	protected function checkPara()
    {
        
    }

    protected function main()
	{
	    $info = array('status' => $this->status);
        Warehouse_Api::updateSupplier($this->sid, $info);
        if ($this->status == Conf_Base::STATUS_UN_AUDIT)
        {
            $supplier = Warehouse_Api::getSupplier($this->sid);
            $messageData = array(
                'm_type' => 1,
                'typeid' => 0,
                'content' => '驳回供货商【供货商ID:'.$this->sid.'，供货商名称:'.$this->supplier['name'].'】；原因【'.$this->reason.'】；需要处理。',
                'send_suid' => $this->_uid,
                'receive_suid' => $supplier['create_suid'],
                'url' => '/warehouse/edit_supplier.php?sid='.$this->sid,
            );
            Admin_Message_Api::create($messageData);
        }
        if ($this->status == Conf_Base::STATUS_NORMAL)
        {
            $supplier = Warehouse_Api::getSupplier($this->sid);
            $messageData = array(
                'm_type' => 1,
                'typeid' => 0,
                'content' => '供货商审核通过【供货商ID:'.$this->sid.'，供货商名称:'.$this->supplier['name'].'】。',
                'send_suid' => $this->_uid,
                'receive_suid' => $supplier['create_suid'],
            );
            Admin_Message_Api::create($messageData);
        }
	}

	protected function outputPage()
	{
		$result = array('sid' => $this->sid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App('pri');
$app->run();

