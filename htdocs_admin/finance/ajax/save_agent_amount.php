<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $id;
	private $aid;
    private $price;
    private $type;
    private $info;
    private $payment;

	protected function getPara()
	{
		$this->aid = Tool_Input::clean('r', 'aid', TYPE_UINT);
        $this->price = Tool_Input::clean('r', 'price', TYPE_UINT);
        $this->type = Tool_Input::clean('r', 'type', TYPE_STR);
        $this->payment = Tool_Input::clean('r', 'payment', TYPE_UINT);
	}

	protected function checkPara()
	{
        if (empty($this->aid))
        {
            throw new Exception('经销商ID不能为空');
        }
        $op = new Agent_Agent();
        $this->info = $op->getAgentInfo($this->aid);
        if(empty($this->info))
        {
            throw new Exception('经销商不存在');
        }
        if(empty($this->price))
        {
            throw new Exception('金额不能为空或者为0');
        }
        if(!in_array($this->payment, array_keys(Conf_Finance::$MONEY_OUT_PAID_TYPES)))
        {
            throw new Exception('支付方式错误');
        }
        if(empty($this->type))
        {
            throw new Exception('非法操作');
        }
	}

	protected function main()
	{
        switch ($this->type)
        {
            case 'add':
                $this->id = Agent_Api::addAgentAmountHistoryByAid($this->aid, Conf_Agent::Agent_Type_Stored, $this->price, $this->_uid, $this->payment);
                break;
            case 'withdraw':
                if(($this->info['account_balance']-$this->price)<0)
                {
                    throw new Exception('余额不足，不能提现');
                }
                $this->id = Agent_Api::addAgentAmountHistoryByAid($this->aid, Conf_Agent::Agent_Type_Withdrawals, -$this->price, $this->_uid, $this->payment);
                break;
            default:
                break;
        }
	}

	protected function outputPage()
	{
		$result = array('id' => $this->id);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App('pri');
$app->run();

