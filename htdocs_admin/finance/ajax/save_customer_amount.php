<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $cid;
    private $type;
    private $price;
    private $tax;
    private $cityId;
    private $note;
    private $paymentType;
    private $cashRate;
    private $response = 0;

    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->type = Tool_Input::clean('r', 'type', TYPE_UINT);
        $this->price = Tool_Input::clean('r', 'price', TYPE_NUM) * 100;
        $this->tax = Tool_Input::clean('r', 'tax', TYPE_NUM) * 100;
        $this->cityId = Tool_Input::clean('r', 'city_id', TYPE_UINT);
        $this->note = Tool_Input::clean('r', 'note', TYPE_STR);
        $this->paymentType = Tool_Input::clean('r', 'payment_type', TYPE_UINT);
        $this->cashRate = Tool_Input::clean('r', 'cash_rate', TYPE_UINT);
        
        // 不返点 2017-02-11 by guoqiangyang
        $this->cashRate = 0;
    }

    protected function checkPara()
    {
        if (empty($this->cid) || !array_key_exists($this->type, Conf_Finance::$Crm_AMOUNT_TYPE_DESCS) || empty($this->price) || empty($this->paymentType))
        {
            throw new Exception('参数错误，请查询！');
        }

        $cinfo = Crm2_Api::getCustomerInfo($this->cid);

        if (empty($cinfo))
        {
            $this->response = -10;
        }
    }

    protected function main()
    {
        if ($this->response != 0)
        {
            return;
        }

        // price 需要写入负数
        $sType = array(
            Conf_Finance::CRM_AMOUNT_TYPE_PAID,
            Conf_Finance::CRM_AMOUNT_TYPE_CASH,
        );

        if (in_array($this->type, $sType))
        {
            $this->price = 0 - abs($this->price);
        }

        //支付税金
        if ($this->type == Conf_Finance::CRM_AMOUNT_TYPE_PAY_TAXES)
        {
            $this->price = 0 - $this->price;
        }

        $saveData = array(
            'type' => $this->type,
            'price' => $this->price,
            'payment_type' => $this->paymentType,
            'note' => $this->note,
            'suid' => $this->_uid,
            'city_id' => $this->cityId,
        );

        // 预存款，记录起客户所属的销售
        if ($this->type == Conf_Finance::CRM_AMOUNT_TYPE_PREPAY)
        {
            $customerInfo = Crm2_Api::getCustomerInfo($this->cid, FALSE, FALSE);
            $saveData['saler_suid'] = $customerInfo['customer']['sales_suid'];
        }

        $this->response = Finance_Api::addCustomerAmountHistory($this->cid, $saveData);
        if (!empty($this->tax) && $this->type == Conf_Finance::CRM_AMOUNT_TYPE_PREPAY)
        {
            $tax = abs($this->tax);
            $saveData['price'] = -$tax;
            $saveData['note'] = '支付其他服务费';
            $saveData['type'] = Conf_Finance::CRM_AMOUNT_TYPE_PAY_TAXES;
            $this->response = Finance_Api::addCustomerAmountHistory($this->cid, $saveData);
        }

        // 预付 - 返现
//        if ($this->type == Conf_Finance::CRM_AMOUNT_TYPE_PREPAY && $this->price >= Conf_Finance::CUSTOMER_CASHBACK_BASE && $this->cashRate > 0)
//        {
//            $amount = $this->price / 100 * $this->cashRate / 100;
//            $vipCouponNum = floor($amount / 50);
//
//            //Coupon_Api::addVipCoupon($this->cid, array(50 => $vipCouponNum));
//            //发送vip新券
//            for($i=0;$i<$vipCouponNum;$i++)
//            {
//                Coupon_Api::addPromotionCoupon($this->cid, 1, 0, 0, Conf_Coupon::TYPE_PRE_PAY);
//            }
//        }

        if ($this->type == Conf_Finance::CRM_AMOUNT_TYPE_PREPAY)
        {
            Crm2_Api::delLimit($this->cid, Crm2_Limit::$KEY_SEND_AMOUNT_MSG);
        }
    }

    protected function outputBody()
    {
        $result = array('st' => is_array($this->response) ? 1 : $this->response);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App();
$app->run();