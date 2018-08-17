<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $method;
	private $eid;

	protected function getPara()
	{
		$this->eid = Tool_Input::clean('r', 'eid', TYPE_UINT);
		$this->method = Tool_Input::clean('r', 'method', TYPE_STR);
	}

	protected function checkPara()
    {
        if (empty($this->eid))
        {
            throw new Exception('Exchanged:empty eid');
        }
        if (empty($this->method))
        {
            throw new Exception('Exchanged:empty method');
        }
    }

	protected function main()
	{
		switch ($this->method)
        {
            case 'cancel':
                $update = array(
                    'exchanged_status'=> Conf_Base::STATUS_CANCEL,
                    'audit_suid' => $this->_uid
                );
                $exchangedInfo = Exchanged_Api::getExchanged($this->eid);
                Exchanged_Api::updateExchanged($this->eid, $update);
                Admin_Api::addOrderActionLog($this->_uid, $exchangedInfo['info']['oid'], Conf_Order_Action_Log::ACTION_CANCEL_EXCHANGED_ORDER, array('eid'=>$this->eid));
                break;
            case 'delete':
                $update = array(
                    'exchanged_status'=> Conf_Base::STATUS_DELETED,
                    'audit_suid' => $this->_uid
                );
                $exchangedInfo = Exchanged_Api::getExchanged($this->eid);
                if($exchangedInfo['info']['step'] >= Conf_Exchanged::EXCHANGED_STEP_SURE)
                {
                    $orderInfo = Order_Api::getOrderInfo($exchangedInfo['info']['aftersale_oid']);
                    if($orderInfo['step'] >= Conf_Order::ORDER_STEP_PICKED)
                    {
                        throw new Exception('补单已经出库，不能删除！');
                    }
                    $refundInfo = Order_Api::getRefund($exchangedInfo['info']['refund_id']);
                    if($refundInfo['step'] >= Conf_Refund::REFUND_STEP_IN_STOCK)
                    {
                        throw new Exception('退货单已经入库，不能删除！');
                    }
                    if($orderInfo['paid'] == Conf_Order::UN_PAID || $orderInfo['real_amount'] == 0)
                    {
                        Order_Api::deleteOrder($exchangedInfo['info']['aftersale_oid']);
                    }else{
                        Order_Api::refundAndDelete($exchangedInfo['info']['aftersale_oid'], $this->_user);
                    }
                    Order_Api::deleteRefund($exchangedInfo['info']['refund_id']);
                }
                Exchanged_Api::updateExchanged($this->eid, $update);
                Admin_Api::addOrderActionLog($this->_uid, $exchangedInfo['info']['oid'], Conf_Order_Action_Log::ACTION_DELETE_EXCHANGED_ORDER, array('eid'=>$this->eid));

                break;
            case 'audit':
                Exchanged_Api::auditExchange($this->eid,$this->_uid);
                break;
            default:
                throw new Exception('无效参数');
                break;
        }
	}

	protected function outputPage()
	{
		$result = array('eid' => $this->eid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pri');
$app->run();