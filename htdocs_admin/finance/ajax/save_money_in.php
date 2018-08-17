<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $cid;
    private $uid;
    private $wid;
    private $type;
    private $price;
    private $note;
    private $objid;
    private $payment_type;
    private $adtype;    //财务调账使用 应收增加:1 应收减少:2
    private $moling;    //抹零金额
    private $discount;  //返点金额
    private $payUseBalance; //是否使用余额支付
    private $badDebt;   //坏账金额
    private $serviceFee;    //服务费

    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->uid = Tool_Input::clean('r', 'uid', TYPE_UINT);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->type = Tool_Input::clean('r', 'type', TYPE_UINT);
        $this->price = Tool_Input::clean('r', 'price', TYPE_NUM) * 100;
        $this->note = Tool_Input::clean('r', 'note', TYPE_STR);
        $this->objid = Tool_Input::clean('r', 'objid', TYPE_UINT);
        $this->payment_type = Tool_Input::clean('r', 'payment_type', TYPE_UINT);
        $this->adtype = Tool_Input::clean('r', 'adtype', TYPE_UINT);
        $this->moling = Tool_Input::clean('r', 'moling', TYPE_NUM) * 100;
        $this->discount = Tool_Input::clean('r', 'discount', TYPE_NUM) * 100;
        $this->payUseBalance = Tool_Input::clean('r', 'use_balance', TYPE_UINT);
        $this->badDebt = Tool_Input::clean('r', 'bad_debt', TYPE_NUM) * 100;
        //$this->serviceFee = Tool_Input::clean('r', 'service_fee', TYPE_NUM) * 100;
        $this->serviceFee = 0;
	}

	protected function checkPara()
	{
		if (!array_key_exists($this->type, Conf_Money_In::$STATUS_DESC))
		{
			throw new Exception('type: type undefined');
		}
		/*
		if (empty($this->price))
		{
			throw new Exception('finance:empty money');
		}
		*/
		if ($this->type == Conf_Money_In::FINANCE_ADJUST && empty($this->adtype))
		{
			throw new Exception('adtype: empty adjust type');
		}
        
        if (($this->price-$this->moling)>0 && $this->payment_type==Conf_Base::PT_BALANCE)
        {
            throw new Exception('请重新选择支付方式：余额支付方式无需选择，或核对实际支付金额');
        }
	}

//	protected function checkAuth()
//	{
//		parent::checkAuth();
//
//		// 财务权限
//		if (($this->type == Conf_Money_In::FINANCE_INCOME || $this->type == Conf_Money_In::FINANCE_ADJUST)
//                && !Admin_Role_Api::hasAuthority('w_edit_finance', $this->_uid, $this->_user))
//		{
//			throw new Exception('common:permission denied');
//		}
//	}

	protected function main()
	{
		// 财务收款
		if ($this->type == Conf_Money_In::FINANCE_INCOME)
		{
			if (empty($this->objid))
			{
				throw new Exception('common: order id is empty!');
			}

			$orderInfo = Order_Api::getOrderInfo($this->objid);
            
            //是否能做收款操作
            Order_Helper::canDealOrder($orderInfo, $this->_user);
            
			$customer = Crm2_Api::getCustomerInfo($orderInfo['cid'], FALSE, FALSE);

			$usedBalance = 0;
			$willPaid = $orderInfo['user_need_to_pay'];
            
            //提前支付（未出库前支付，需要插入一条 客服应付记录）
//            Finance_Api::addMoneyInHistory($orderInfo['cid'], Conf_Money_In::ORDER_PAIED, $orderInfo['user_need_to_pay'], Conf_Admin::ADMINOR_AUTO, $this->objid, $orderInfo['wid'], '', 0, $orderInfo['uid'], $this->objid);

            //余额支付
            if ($this->payUseBalance && $customer['customer']['account_amount'] > 0)
            {
                $usedBalance = min($willPaid, $customer['customer']['account_amount']);

                // 完全使用余额支付，支付金额要减去返点，服务费等
                if ($this->price == 0)
                {
                    $usedBalance -= ($this->discount + $this->serviceFee);
                }

                //插入客户应收明细
                Finance_Api::addMoneyInHistory($this->cid, $this->type, $usedBalance, $this->_uid, $this->objid, $this->wid, '余额收款', Conf_Base::PT_BALANCE, $this->uid, $this->objid);

                //插入客户账务余额流水
                $saveData = array(
                    'type' => Conf_Finance::CRM_AMOUNT_TYPE_PAID,
                    'price' => 0 - abs($usedBalance),
                    'payment_type' => Conf_Base::PT_BALANCE,
                    'note' => '余额支付销售单：' . $this->objid,
                    'objid' => $this->objid,
                    'suid' => $this->_uid,
                    'uid' => $this->uid,
                    'oid' => $this->objid,
                );

                $this->response = Finance_Api::addCustomerAmountHistory($this->cid, $saveData);
            }

            $_remainPay = $this->price - $this->moling - $this->discount - $this->badDebt - $this->serviceFee;
            $_remainPay = $_remainPay > 0 ? $_remainPay : 0;

            //插入财务模块数据
            if ($_remainPay > 0 && $this->type != Conf_Base::PT_BALANCE)
            {
                Finance_Api::addMoneyInHistory($this->cid, $this->type, $_remainPay, $this->_uid, $this->objid, $this->wid, $this->note, $this->payment_type, $this->uid, $this->objid);

                // 多支付的钱进入余额
                //				$_morePaid = $_remainPay - ($willPaid - $usedBalance - $this->discount);
                //				if ($_morePaid > 0)
                //				{
                //					Finance_Api::addMoneyInHistory(
                //                      $this->cid,
                //                      Conf_Money_In::CUSTOMER_AMOUNT_TRANSFER,
                //                      $_morePaid,
                //                      $this->_uid,
                //                      $this->objid,
                //                      $this->wid,
                //                      '多支付金额进入余额',
                //                      Conf_Base::PT_BALANCE,
                //                      $this->uid,
                //                      $this->objid
                //                  );
                //
                //					//插入客户账务余额流水
                //					$saveData = array(
                //						'type' => Conf_Finance::CRM_AMOUNT_TRANSFER,
                //						'price' => $_morePaid,
                //						'payment_type' => Conf_Base::PT_BALANCE,
                //						'note' => '余额转入：' . $this->objid,
                //						'objid' => $this->objid,
                //						'suid' => $this->_uid,
                //						'uid' => $this->uid,
                //						'oid' => $this->objid,
                //					);
                //
                //					$this->response = Finance_Api::addCustomerAmountHistory($this->cid, $saveData);
                //				}
            }

            // 抹零记录财务流水
            if ($this->moling > 0)
            {
                Finance_Api::addMoneyInHistory($this->cid, Conf_Money_In::FINANCE_ADJUST, 0 - abs($this->moling), $this->_uid, $this->objid, $this->wid, '抹零（单号：' . $this->objid . '）', 0, $this->uid, $this->objid);
            }

            // 客户返点金额进入财务流水
            if ($this->discount > 0)
            {
                Finance_Api::addMoneyInHistory($this->cid, Conf_Money_In::CUSTOMER_DISCOUNT, 0 - abs($this->discount), $this->_uid, $this->objid, $this->wid, '客户返点', 0, $this->uid, $this->objid);
            }

            // 客户坏账
            if ($this->badDebt > 0)
            {
                Finance_Api::addMoneyInHistory($this->cid, Conf_Money_In::CUSTOMER_BAD_DEBT, 0 - abs($this->badDebt), $this->_uid, $this->objid, $this->wid, '客户坏账', 0, $this->uid, $this->objid);
            }

            // 平台/平台的 服务费
            if ($this->serviceFee > 0)
            {
                Finance_Api::addMoneyInHistory($this->cid, Conf_Money_In::PLATFORM_SERVICE_FEE, 0 - abs($this->serviceFee), $this->_uid, $this->objid, $this->wid, '平台服务费', 0, $this->uid, $this->objid);
            }

            // 标记订单付款状态
            $realAmount = $_remainPay + $orderInfo['real_amount'] + $usedBalance + $this->moling + $this->discount + $this->badDebt + $this->serviceFee;
            $paid = (intval(strval($realAmount)) >= intval(strval($orderInfo['total_order_price']))) ? Conf_Order::HAD_PAID : Conf_Order::PART_PAID;

            //订单操作日志应收：{needToPay}，实收：{realAmount}，抹零：{change}，坏账：{$badLoans}
            $param = array(
                'needToPay' => $orderInfo['user_need_to_pay'] / 100,
                'realAmount' => ($this->price+$usedBalance) / 100,
                'change' => $this->moling / 100,
                'badLoans' => $this->badDebt / 100,
                'serviceFee' => $this->serviceFee / 100,
                'type' => '财务收款',
            );
            Admin_Api::addOrderActionLog($this->_uid, $this->objid, Conf_Order_Action_Log::ACTION_RECEIPT, $param);

            // 部分支付记录log，排查问题
            if (0 && $paid == Conf_Order::PART_PAID)
            {
                $desc = array(
                    'oid' => $this->objid,
                    'cid' => $this->cid,
                    'price' => $this->price,
                    'moling' => $this->moling,
                    'discount' => $this->discount,
                    'badDebt' => $this->badDebt,
                    'serviceFee' => $this->serviceFee,
                    'usedBalance' => $usedBalance,
                    '_remainPay' => $_remainPay,
                    'real_amount' => $orderInfo['real_amount'],
                    'total_price' => $orderInfo['total_order_price'],
                );
                Tool_Log::addFileLog('check_part_paid.' . date('Ymd'), json_encode($desc) . "\n", TRUE);
            }

            Activity_Lottery_Api::addRecord($this->cid, $this->objid, $_remainPay + $usedBalance, $orderInfo['privilege']);

            $order = array(
                'paid' => $paid,
                'real_amount' => $realAmount,
                'payment_type' => $this->payment_type,
                'pay_time' => date('Y-m-d H:i:s', time())
            );
            Order_Api::updateOrderInfo($this->objid, $order);
            if($paid == Conf_Order::HAD_PAID && $orderInfo['step'] >= Conf_Order::ORDER_STEP_FINISHED)
            {
                Coupon_Api::sendPromotionCoupon($this->cid, $this->objid);
            }
        }
        // 其他情况
        else
        {
            // #### 财务调账的页面操作已经下线！！！

            if ($this->type == Conf_Money_In::FINANCE_ADJUST)
            {
                $this->price = $this->adtype == 2 ? 0 - $this->price : $this->price;
            }

            // $this->adtype==10 财务退款
            if ($this->type == Conf_Money_In::FINANCE_ADJUST && $this->adtype == 10)
            {
                $this->type = Conf_Money_In::FINANCE_REFUND;
            } //$this->adtype==11 客户预付
            else if ($this->type == Conf_Money_In::FINANCE_ADJUST && $this->adtype == 11)
            {
                $this->type = Conf_Money_In::CUSTOMER_PRE_PAY;
            }

            //更新应收明细
            Finance_Api::addMoneyInHistory($this->cid, $this->type, $this->price, $this->_uid, $this->objid, $this->wid, $this->note, $this->payment_type, $this->uid);

            //财务退款，款额退到 客户账户余额
            if ($this->type == Conf_Money_In::FINANCE_REFUND)
            {
                $saveData = array(
                    'type' => Conf_Finance::CRM_AMOUNT_TYPE_FINANCE_REFUND,
                    'price' => abs($this->price),
                    'payment_type' => Conf_Base::PT_BALANCE,
                    'note' => '财务退款',
                    'suid' => $this->_uid,
                    'uid' => $this->uid,
                );

                Finance_Api::addCustomerAmountHistory($this->cid, $saveData);
            }
        }
    }

    protected function outputPage()
    {
        $result = array('objid' => $this->objid);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();