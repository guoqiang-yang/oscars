<?php

/**
 * 账期客户的支付 - [财务管理-账期客户结账].
 *
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $cid;
    private $oids;
    private $etime;
    private $paymentType;
    private $note;
    private $orderList;
    private $remainPrice = 0;

    protected function checkAuth()
    {
        parent::checkAuth('/finance/customer_account_pay');
    }

    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->oids = Tool_Input::clean('r', 'oids', TYPE_STR);
        $this->etime = Tool_Input::clean('r', 'etime', TYPE_STR);
        $this->paymentType = Tool_Input::clean('r', 'payment_type', TYPE_UINT);
        $this->note = Tool_Input::clean('r', 'note', TYPE_STR);
    }

    protected function checkPara()
    {
        $this->oids = json_decode($this->oids, true);
        if (empty($this->oids) || empty($this->cid) || empty($this->note) || !array_key_exists($this->paymentType, conf_Base::$PAYMENT_TYPES))
        {
            throw new Exception('参数错误，请联系管理员！');
        }
    }

    protected function main()
    {
        $this->oids = Tool_Array::list2Map($this->oids, 'oid');
        $_oids = array_keys($this->oids);
        // 获取到截止时间的订单信息，以及支付金额等数据
        $orderMoreInfo = Finance_Api::getCustomerUnpaidOrderList($this->cid, $this->etime);
        $this->orderList = $orderMoreInfo['order'];

        $oo = new Order_Order();

        foreach ($this->orderList as $_order)
        {
            if(!in_array($_order['oid'], $_oids))
            {
                continue;
            }
            $upData = array('paid' => 0,'payment_type' => $this->paymentType, 'pay_time' => date('Y-m-d H:i:s', time()));
            $change = array('real_amount' => 0);
            if (floor($_order['bills']['will_real_pay']) <= $this->oids[$_order['oid']]['realpay'] + $this->oids[$_order['oid']]['moling'])
            {
                $upData['paid'] = Conf_Order::HAD_PAID;
                $change['real_amount'] = $this->oids[$_order['oid']]['realpay']+$this->oids[$_order['oid']]['moling'];
            }
            else if($this->oids[$_order['oid']]['realpay'] > 0)
            {
                $upData['paid'] = Conf_Order::PART_PAID;
                $change['real_amount'] = $this->oids[$_order['oid']]['realpay']+$this->oids[$_order['oid']]['moling'];
            }

            // 更新订单
            $oo->update($_order['oid'], $upData, $change);
            // 更新财务流水
            Finance_Api::addMoneyInHistory(
                    $this->cid, Conf_Money_In::FINANCE_INCOME, $this->oids[$_order['oid']]['realpay'], $this->_uid,
                    $_order['oid'], $_order['wid'], $this->note, $this->paymentType, $_order['uid'], $_order['oid']);
            if($this->oids[$_order['oid']]['moling'] > 0)
            {
                Finance_Api::addMoneyInHistory($this->cid, Conf_Money_In::FINANCE_ADJUST, 0-abs($this->oids[$_order['oid']]['moling']), $this->_uid, 
                        $_order['oid'], $_order['wid'], '抹零', 0, $_order['uid'], $_order['oid']);
            }
            //订单操作日志应收：{needToPay}，实收：{realAmount}，抹零：{change}，坏账：{$badLoans}
            $param = array(
                'needToPay' => $_order['bills']['will_real_pay'] / 100,
                'realAmount' => ($this->oids[$_order['oid']]['realpay']+$this->oids['oid']['moling']) / 100,
                'change' => $this->oids[$_order['oid']]['moling'] / 100,
                'badLoans' => 0,
                'serviceFee' => 0,
                'type' => '财务收款',
            );
            Admin_Api::addOrderActionLog($this->_uid, $_order['oid'], Conf_Order_Action_Log::ACTION_RECEIPT, $param);
            if($upData['paid']== Conf_Order::HAD_PAID && $_order['step'] >= Conf_Order::ORDER_STEP_FINISHED)
            {
                Coupon_Api::sendPromotionCoupon($this->cid, $_order['oid']);
            }
        }

    }

    protected function outputBody()
    {
        $result = array('st' => '1');

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App();
$app->run();