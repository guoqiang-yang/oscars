<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $nextStep;
	private $rid;
	private $refundToBalance;
	private $adjust;
    private $optype;
    
    private $unstockinPids; //不入库不上架的商品id列表

	protected function getPara()
	{
		$this->rid = Tool_Input::clean('r', 'rid', TYPE_UINT);
		$this->nextStep = Tool_Input::clean('r', 'next_step', TYPE_UINT);
		$this->refundToBalance = Tool_Input::clean('r', 'refund_to_balance', TYPE_UINT);
		$this->adjust = Tool_Input::clean('r', 'adjust', TYPE_NUM);
        $this->optype = Tool_Input::clean('r', 'optype', TYPE_STR);
        
        $this->unstockinPids = json_decode(Tool_Input::clean('r', 'unstockin_pids', TYPE_STR), true);
	}

	protected function main()
	{
		Order_Api::updateRefundStep($this->_user, $this->rid, $this->nextStep, 
                $this->refundToBalance, $this->adjust, $this->optype, $this->unstockinPids);
	}

	protected function outputPage()
	{
		$result = array('rid' => $this->rid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pri');
$app->run();

