<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $method;
	private $tid;

	protected function getPara()
	{
		$this->tid = Tool_Input::clean('r', 'tid', TYPE_UINT);
		$this->method = Tool_Input::clean('r', 'method', TYPE_STR);
	}

	protected function checkPara()
    {
        if (empty($this->tid))
        {
            throw new Exception('Traps:empty tid');
        }
        if (empty($this->method))
        {
            throw new Exception('Traps:empty method');
        }
    }

    protected function checkAuth()
    {
        parent::checkAuth('/order/ajax/change_traps');
    }

	protected function main()
	{
		switch ($this->method)
        {
            case 'delete':
                $update = array(
                    'traps_status'=> Conf_Base::STATUS_DELETED,
                    'audit_suid' => $this->_uid
                );
                $trapsInfo = Traps_Api::getTraps($this->tid);
                if($trapsInfo['info']['step'] >= Conf_Traps::TRAPS_STEP_SURE)
                {
                    $orderInfo = Order_Api::getOrderInfo($trapsInfo['info']['aftersale_oid']);
                    if($orderInfo['step'] >= Conf_Order::ORDER_STEP_PICKED)
                    {
                        throw new Exception('补单已经出库，不能删除！');
                    }
                    if($orderInfo['paid'] == Conf_Order::UN_PAID || $orderInfo['real_amount'] == 0)
                    {
                        Order_Api::deleteOrder($trapsInfo['info']['aftersale_oid']);
                    }else{
                        Order_Api::refundAndDelete($trapsInfo['info']['aftersale_oid'], $this->_user);
                    }
                }
                Traps_Api::updateTraps($this->tid, $update);
                Admin_Api::addOrderActionLog($this->_uid, $trapsInfo['info']['oid'], Conf_Order_Action_Log::ACTION_DELETE_TRAPS_ORDER, array('tid'=>$this->tid));

                break;
            case 'audit':
                Traps_Api::auditTraps($this->tid,$this->_uid);
                break;
            default:
                throw new Exception('无效参数');
                break;
        }
	}

	protected function outputPage()
	{
		$result = array('tid' => $this->tid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pri');
$app->run();