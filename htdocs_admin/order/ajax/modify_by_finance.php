<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $oid;
	private $privilege;
	private $note;

	//姚国全，刘兰兰,胡结满
	private static $TASK_FINANCES = array(1022, 1126, 1037, 1153);

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
		$this->privilege = Tool_Input::clean('r', 'privilege', TYPE_NUM) * 100;
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
        if($this->privilege != $oldOrder['privilege'])
        {
            $or = new Order_Refund();
            $refund_list = $or->getListOfOrder($this->oid);
            if(!empty($refund_list))
            {
                foreach ($refund_list as $item)
                {
                    if($item['paid'] > Conf_Refund::UN_PAID)
                    {
                        throw new Exception('退货单（id:'.$item['rid'].'）已提交财务或已退款，不能修改');
                    }
                }
            }
        }

		$amountChange = array();
		if (isset($this->privilege) && $this->privilege != $oldOrder['privilege'])
		{
			$amountChange['优惠'] = $this->privilege / 100;
			$adjustInfo .= '优惠从' . ($oldOrder['privilege'] / 100) . '元到' . ($this->privilege / 100) . '元；';
			$adjustAmount -= $this->privilege - $oldOrder['privilege'];
            if(Conf_Privilege::PROMOTION_NEW_STATUS && $oldOrder['ctime'] >= Conf_Privilege::PROMOTION_NEW_UPDATE_TIME)
            {
                Privilege_2_Api::computeAndUpdateOrderProductsPrivilege($this->oid, $this->privilege, 2);
//                Refund_Api::updateOrderRefundPrivilege($this->oid);
            }
		}
		if (!empty($amountChange))
		{
			$amountChange['原因'] = $this->note;
			Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_CHANGE_FEE, $amountChange);
			$adjustInfo .= '原因：' . $this->note . '；';
		}

		//做了财务调整，需要加一个任务给财务相关人员
//		$suid = 0;
//		if ($adjustAmount > 0)
//		{
//			if ($oldOrder['real_amount'] > 0)
//			{
//				$customer = Crm2_Api::getCustomerInfo($oldOrder['cid'], false, false);
//				if ($customer['customer']['account_amount'] >= $adjustAmount)
//				{
//					$toSuids = self::$TASK_FINANCES;
//					$num = Data_Memcache::getInstance()->get('task_to_finance');
//					$index = $num / count($toSuids);
//					$suid = $toSuids[$index];
//				}
//				else
//				{
//					$suid = $oldOrder['saler_suid'];
//				}
//			}
//			else
//			{
//				$suid = $oldOrder['saler_suid'];
//			}
//
//			if ($suid > 0)
//			{
//				$taskParams = array(
//					'objid' => $this->oid,
//					'objtype' => Conf_Admin_Task::OBJTYPE_ORDER,
//					'short_desc' => 99,
//					'exec_suid' => $suid,
//					'title' => '订单财务调整',
//					'content' => $adjustInfo,
//					'create_suid' => $this->_uid,
//					'exec_status' => Conf_Admin_Task::ST_WAIT_DEAL,
//					'level' => Conf_Admin_Task::TASK_LEVEL_NORMAL,
//				);
//
//				//没人看
////				Admin_Task_Api::create($taskParams, $this->_uid);
//
//				if (in_array($suid, self::$TASK_FINANCES))
//				{
//					Data_Memcache::getInstance()->increment('task_to_finance', 1);
//				}
//			}
//		}

		Order_Api::updateOrderByFinanceModify($this->oid, $oldOrder['freight'], $this->privilege, $oldOrder['customer_carriage']);
        if($oldOrder['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_EXCHANGED && $oldOrder['aftersale_id'] > 0)
        {
            Exchanged_Api::updateExchanged($oldOrder['aftersale_id'], array('privilege' => $this->privilege));
        }elseif($oldOrder['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_TRAPS && $oldOrder['aftersale_id'] > 0){
            Traps_Api::updateTraps($oldOrder['aftersale_id'], array('privilege' => $this->privilege));
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