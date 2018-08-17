<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $oid;
	private $type;
	private $order = array();
	private $oldOrder = array();

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
		$this->type = Tool_Input::clean('r', 'type', TYPE_STR);

		switch ($this->type)
		{
			case 'note':
				$this->order['note'] = Tool_Input::clean('r', 'note', TYPE_STR);
				break;
			case 'customer_note':
				$this->order['customer_note'] = Tool_Input::clean('r', 'customer_note', TYPE_STR);
				break;
			case 'driver':
				$this->order['driver_name'] = Tool_Input::clean('r', 'driver_name', TYPE_STR);
				$this->order['driver_phone'] = Tool_Input::clean('r', 'driver_phone', TYPE_STR);
				$this->order['driver_money'] = intval(100 * Tool_Input::clean('r', 'driver_money', TYPE_NUM));
				$this->order['carrier_name'] = Tool_Input::clean('r', 'carrier_name', TYPE_STR);
				$this->order['carrier_phone'] = Tool_Input::clean('r', 'carrier_phone', TYPE_STR);
				$this->order['carrier_money'] = intval(100 * Tool_Input::clean('r', 'carrier_money', TYPE_UINT));
				break;
			case 'finance':
				$this->order['real_amount'] = intval(100 * Tool_Input::clean('r', 'real_amount', TYPE_UINT));
				break;
			case 'next_step':
				$this->order['step'] = Tool_Input::clean('r', 'step', TYPE_UINT);
				break;
			case 'back':
			default:
				break;
		}

		$this->oldOrder = Order_Api::getOrderInfo($this->oid);
	}

	protected function checkPara()
    {
        if (empty($this->oid)) {
            throw new Exception('order:empty order id');
        }

        if ($this->oldOrder['status'] != Conf_Base::STATUS_NORMAL) {
            throw new Exception('订单状态异常，不能编辑！');
        }

        // 检测仓库
        if (empty($this->oldOrder['wid'])) {
            throw new Exception('order:empty wid');
        }

		switch ($this->type)
		{
			case 'note':
				break;
			case 'customer_note':
				break;
			case 'driver':
				if (Conf_Order::ORDER_STEP_BOUGHT == $this->oldOrder['step'] && empty($this->order['driver_name']) && empty($this->order['driver_phone']))
				{
					throw new Exception('order:empty driver');
				}
				break;
			case 'finance':
				break;
			case 'next_step':
				break;
            case 'back':
                if (!empty($this->oldOrder) && $this->oldOrder['step']>=Conf_Order::ORDER_STEP_PICKED)
                {
                    throw new Exception('订单已经出库，不能回滚订单状态！！');
                }
                $msg = Order_Api::canEditOrderInfo($this->oid, $this->_uid);
                if($msg['error'] > 0)
                {
                    throw new Exception($msg['errormsg']);
                }
//                if (!empty($this->oldOrder['line_id']))
//                {
//                    throw new Exception('订单已经排线，如需修改，请联系调度取消排线！');
//                }
                break;
			default:
				break;
		}
	}

	protected function checkAuth()
	{
        $type = isset($_REQUEST['type'])? $_REQUEST['type']: '';
        $nextStep = isset($_REQUEST['step'])? $_REQUEST['step']: 0;
        
        switch($type)
        {
            case 'back':
                parent::checkAuth('/order/edit_order'); break;
            
            case 'note':
            case 'customer_note':
                parent::checkAuth('hc_edit_order_note'); break;
            
            case 'next_step':
                if ($nextStep == Conf_Order::ORDER_STEP_SURE)
                {
                    parent::checkAuth(array('hc_order_cs_confirm')); break;
                }
                else if ($nextStep == Conf_Order::ORDER_STEP_PICKED)
                {
                    parent::checkAuth('hc_order_delivred'); break;
                }
                else if ($nextStep == Conf_Order::ORDER_STEP_FINISHED)
                {
                    parent::checkAuth('hc_order_bill_back'); break;
                }
                else
                {
                    throw new Exception('common:permission denied');
                }
            
            default:
                throw new Exception('common:permission denied');
        }
        
	}

	protected function main()
	{
		switch ($this->type)
		{
			case 'note':
				break;
			case 'customer_note':
				break;
			case 'driver':
				if (Conf_Order::ORDER_STEP_BOUGHT == $this->oldOrder['step'] && ($this->order['driver_name'] || $this->order['driver_phone']))
				{
					$this->order['step'] = Conf_Order::ORDER_STEP_HAS_DRIVER;
				}
				break;
			case 'finance':
				if ($this->order['real_amount'] <= 0)
					$this->order['real_amount'] = 0;
				if ($this->oldOrder['step'] < Conf_Order::ORDER_STEP_FINISHED && $this->order['real_amount'] > 0)
				{
					$this->order['step'] = Conf_Order::ORDER_STEP_FINISHED;
				}
				break;
			case 'back':
				$this->order['step'] = Conf_Order::ORDER_STEP_NEW;
				break;
			case 'next_step':
			default:
				break;
		}

		if (empty($this->order))
		{
			return;
		}
        
		// 订单出库时，判断余额是否可以支付订单，如果可以整单支付，则支付订单
		$recordFinance = FALSE;
		if (!in_array($this->oldOrder['cid'], Conf_Order::$AUTO_PAID_EXCEPT_CUSTOMERS) 
            && $this->oldOrder['source'] != Conf_Order::SOURCE_QIANG_GONG_ZHANG
            && Order_Helper::isOwnOrder($this->oldOrder['wid'], $this->oldOrder['city_id'])     //自营（非加盟）
            && $this->order['step'] == Conf_Order::ORDER_STEP_PICKED)
		{
			$customer = Crm2_Api::getCustomerInfo($this->oldOrder['cid'], FALSE, FALSE);
			if ($this->oldOrder['paid'] == Conf_Order::UN_PAID && $customer['customer']['account_amount'] - $this->oldOrder['total_order_price'] >= 0)
			{
				$this->order['payment_type'] = Conf_Base::PT_BALANCE;
				$this->order['paid'] = Conf_Order::HAD_PAID;
				$this->order['real_amount'] = $this->oldOrder['total_order_price'];
				$recordFinance = TRUE;

				//订单操作日志应收：{needToPay}，实收：{realAmount}，抹零：{change}，坏账：{$badLoans}
				$param = array(
					'needToPay' => $this->oldOrder['total_order_price'] / 100,
					'realAmount' => $this->order['real_amount'] / 100,
					'change' => 0,
					'badLoans' => 0,
					'type' => '余额自动付款',
				);
				Admin_Api::addOrderActionLog(999, $this->oid, Conf_Order_Action_Log::ACTION_RECEIPT, $param);

				//Activity_Lottery_Api::addRecord($this->oldOrder['cid'], $this->oid, $this->oldOrder['total_order_price'], $this->oldOrder['privilege']);
			}
		}
        
		// 更新订单
		if ($this->type == 'back')
		{
			Order_Api::rollbackOrder($this->oid, $this->_user);
		}
		else
		{
			Order_Api::forwardOrderStep($this->oid, $this->order['step'], $this->_user);
		}


		$orderinfoInsertRes = Order_Api::updateOrderInfo($this->oid, $this->order);

        //添加内部备注和打印备注的操作日志
        if ($orderinfoInsertRes)
        {
            if ($this->type == 'note')
            {
                $param = array(
                    '修改内部备注'=>$this->order['note']
                );
            }
            elseif ($this->type == 'customer_note')
            {
                $param = array(
                    '修改打印备注: '=>$this->order['customer_note']
                );
            }

            Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_CHANGE_INFO, $param);
        }

		// 更新财务数据
		$this->_recordFinance($recordFinance, $this->oldOrder);
        
        if (!empty($this->order['step']) && $this->oldOrder['step'] != $this->order['step']
            && $this->order['step'] != Conf_Order::ORDER_STEP_SURE ) 
        {
            //订单操作日志
            //更新为：{newStep}
            $param = array(
                'newStep' => Conf_Order::$ORDER_STEPS[$this->order['step']],
            );
            Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_CHANGE_STEP, $param);
        }



	}

	protected function outputPage()
	{
		$result = array('oid' => $this->oid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}

	private function _recordFinance($isRecord, $orderInfo)
	{
		if ($isRecord)
		{
			// 写客户应收明细
			$cid = $orderInfo['cid'];
			$uid = $orderInfo['uid'];
			$type = Conf_Money_In::FINANCE_INCOME;
			$price = $orderInfo['total_order_price'];
			$wid = $orderInfo['wid'];
			$note = '自动收款：【余额】';
			$payType = Conf_Base::PT_BALANCE;
			Finance_Api::addMoneyInHistory($cid, $type, $price, Conf_Admin::ADMINOR_AUTO, $this->oid, $wid, $note, $payType, $uid, $this->oid);

			// 写客户账户余额
			$saveData = array(
				'type' => Conf_Finance::CRM_AMOUNT_TYPE_PAID,
				'price' => 0 - abs($price),
				'objid' => $this->oid,
				'payment_type' => $payType,
				'note' => '余额支付订单：' . $this->oid,
				'suid' => Conf_Admin::ADMINOR_AUTO,
				'uid' => $orderInfo['uid'],
                'oid' => $this->oid,
			);

			$this->response = Finance_Api::addCustomerAmountHistory($cid, $saveData);
		}
	}
}

$app = new App('pri');
$app->run();

