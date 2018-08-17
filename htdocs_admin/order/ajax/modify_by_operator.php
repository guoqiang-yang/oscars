<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $oid;
	private $freight;
	private $customerCarriage;
	private $note;

	//姚国全，刘兰兰,胡结满
	private static $TASK_FINANCES = array(1022, 1126, 1037, 1153);

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
		$this->freight = Tool_Input::clean('r', 'freight', TYPE_NUM) * 100;
		$this->customerCarriage = Tool_Input::clean('r', 'customer_carriage', TYPE_NUM) * 100;
		$this->note = Tool_Input::clean('r', 'note', TYPE_STR);
	}

	protected function checkPara()
	{
		// 订单有效性的验证
		if (empty($this->oid))
		{
			throw new Exception('order is Invalid!');
		}

		if (empty($this->note))
		{
			throw new Exception('请填写调整原因');
		}
	}

//	protected function checkAuth()
//	{
//		parent::checkAuth();
//		if (!Admin_Role_Api::hasAuthority('w_sale_service', $this->_uid, $this->_user))
//		{
//			throw new Exception('common:permission denied');
//		}
//	}

	protected function main()
	{
		$adjustAmount = 0;
		$adjustInfo = '';
		//订单操作日志
		$oldOrder = Order_Api::getOrderInfo($this->oid);
        
        Order_Helper::canDealOrder($oldOrder, $this->_uid);
        
        if ($oldOrder['step'] >= Conf_Order::ORDER_STEP_FINISHED && (time()-strtotime($oldOrder['back_time']))>86400*3 && $this->_uid!=1029 && $this->_uid!=1254 && $this->_uid!=1183)
        {
            throw new Exception('订单已回单超过3天，不能再修改');
        }

        if($oldOrder['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_EXCHANGED && $oldOrder['aftersale_id'] > 0)
        {
            $exchangedInfo = Exchanged_Api::getExchanged($oldOrder['aftersale_id']);
            if($exchangedInfo['info']['need_storage'] == 0)
            {
                throw new Exception(('换货单不出库不能修改'));
            }
        }elseif($oldOrder['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_TRAPS && $oldOrder['aftersale_id'] > 0){
            $trapsInfo = Traps_Api::getTraps($oldOrder['aftersale_id']);
            if($trapsInfo['info']['need_storage'] == 0)
            {
                throw new Exception(('补漏单不出库不能修改'));
            }
        }

		$amountChange = array();
		if (isset($this->freight) && $this->freight != $oldOrder['freight'])
		{
			$amountChange['运费'] = $this->freight / 100;
			$adjustInfo .= '运费从' . ($oldOrder['freight'] / 100) . '元到' . ($this->freight / 100) . '元；';
			$adjustAmount += $this->freight - $oldOrder['freight'];
            $oldOrder['freight'] = $this->freight;
            $orderProducts = Order_Api::getOrderProducts($this->oid);
            $realBuyProducts = Privilege_Api::getRealBuyProducts($orderProducts['products'],$this->oid, $activity_amount);
            $activityProducts = Privilege_Api::getActivityProducts($this->oid);
            $privilege = Privilege_2_Api::savePromotionPrivilege($oldOrder['cid'], $realBuyProducts, $oldOrder, false, $activityProducts);
            if($privilege['total_privilege'] != $oldOrder['privilege'])
            {
                $oldOrder['privilege'] = $privilege['total_privilege'];
            }
		}
		if (isset($this->customerCarriage) && $this->customerCarriage != $oldOrder['customer_carriage'])
		{
			$amountChange['搬运费'] = $this->customerCarriage / 100;
			$adjustInfo .= '搬运费从' . ($oldOrder['customer_carriage'] / 100) . '元到' . ($this->customerCarriage / 100) . '元；';
			$adjustAmount += $this->customerCarriage - $oldOrder['customer_carriage'];
		}
		if (!empty($amountChange))
		{
			$amountChange['原因'] = $this->note;
			Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_CHANGE_FEE, $amountChange);
			$adjustInfo .= '原因：' . $this->note . '；';
		}


		Order_Api::updateOrderByFinanceModify($this->oid, $this->freight, $oldOrder['privilege'], $this->customerCarriage);
        if($oldOrder['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_EXCHANGED && $oldOrder['aftersale_id'] > 0)
        {
            Exchanged_Api::updateExchanged($oldOrder['aftersale_id'], array('carry_fee' => $this->customerCarriage, 'freight' => $this->freight));
        }elseif($oldOrder['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_TRAPS && $oldOrder['aftersale_id'] > 0){
            Traps_Api::updateTraps($oldOrder['aftersale_id'], array('carry_fee' => $this->customerCarriage, 'freight' => $this->freight));
        }
	}

	protected function outputBody()
	{
		$result = array('oid' => $this->oid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pri');
$app->run();