<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $nextStep;
	private $rid;
	private $oid;
	private $optype;
	private $refundFreight;
	private $refundCarryFee;
	private $isRefundFreight;
	private $isRefundCarryFee;
	private $productStr;
	private $addOrder;
	private $refundType;
    private $freight;
    private $carryFee;
    private $isTrapsFreight;
    private $isTrapsCarryFee;

	protected function getPara()
	{
		$this->rid = Tool_Input::clean('r', 'rid', TYPE_UINT);
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
		$this->nextStep = Tool_Input::clean('r', 'next_step', TYPE_UINT);
		$this->refundFreight = Tool_Input::clean('r', 'refund_freight', TYPE_UINT) * 100;
		$this->refundCarryFee = Tool_Input::clean('r', 'refund_carry_fee', TYPE_UINT) * 100;
		$this->optype = Tool_Input::clean('r', 'optype', TYPE_STR);
		$this->isRefundFreight = Tool_Input::clean('r', 'is_refund_freight', TYPE_UINT);
		$this->isRefundCarryFee = Tool_Input::clean('r', 'is_refund_carry_fee', TYPE_UINT);
        $this->freight = Tool_Input::clean('r', 'freight', TYPE_UINT) * 100;
        $this->carryFee = Tool_Input::clean('r', 'carry_fee', TYPE_UINT) * 100;
        $this->isTrapsFreight = Tool_Input::clean('r', 'is_traps_freight', TYPE_UINT);
        $this->isTrapsCarryFee = Tool_Input::clean('r', 'is_traps_carry_fee', TYPE_UINT);
		$this->productStr = Tool_Input::clean('r', 'product_str', TYPE_STR);
		if ($this->nextStep == Conf_Refund::REFUND_STEP_SURE)
        {
            $this->refundType = Tool_Input::clean('r', 'refund_type', TYPE_UINT);
            $this->addOrder = array(
                'delivery_date' => Tool_Input::clean('r', 'delivery_date', TYPE_STR),
                'delivery_date_end' => Tool_Input::clean('r', 'delivery_date_end', TYPE_STR),
            );
        }
	}
    
    protected function checkPara()
    {
		if ($this->isRefundFreight == 0)
		{
			$this->refundFreight = 0;
		}

		if ($this->isRefundCarryFee == 0)
		{
			$this->refundCarryFee = 0;
		}
        if ($this->isTrapsFreight == 0)
        {
            $this->freight = 0;
        }

        if ($this->isTrapsCarryFee == 0)
        {
            $this->carryFee = 0;
        }
        
		$data = Refund_Api::getMaxRefundInfo($this->oid, $this->rid);
		if ($this->refundFreight > $data['freight'])
		{
			$this->refundFreight = $data['freight'];
		}
		if ($this->refundCarryFee > $data['carry_fee'])
		{
			$this->refundCarryFee = $data['carry_fee'];
		}

        if ($this->nextStep == Conf_Refund::REFUND_STEP_SURE)
        {

            $refund_info = Order_Api::getRefund($this->rid);
            $refund_info['info']['freight'] = $this->freight;
            $refund_info['info']['carry_fee'] = $this->carryFee;
            $refundPrice = Refund_Api::calRefundPrice($refund_info['info']);
            $refundPrivilege = Privilege_2_Api::recalPromotionPrivilege($refund_info['info']['oid'], $this->rid, $refund_info['products']);
            $refundPrice -= $refundPrivilege;
            if($refundPrice<0)
            {
                throw new Exception('refund: refund amount cannot be negative');
            }
        }
    }

    protected function checkAuth()
	{
        $optype = isset($_REQUEST['optype'])? $_REQUEST['optype']: '';
        
        switch($optype)
        {
            case 'audit':
                parent::checkAuth('hc_refund_audit');break;
            case 'into_stock':
                parent::checkAuth('hc_refund_into_stock'); break;
            case 'final_audit':
                parent::checkAuth('hc_refund_final_audit'); break;
            case 'finance':
                parent::checkAuth('hc_refund_finance_paid'); break;
            default:
                throw new Exception('common:permission denied');
        }
	}

	protected function main()
	{
		Refund_Api::updateRefundStepNew($this->_user, $this->rid, $this->nextStep, $this->productStr, $this->optype);

		$update = array(
			'oid' => $this->oid,
			'refund_freight' => $this->refundFreight,
			'refund_carry_fee' => $this->refundCarryFee,
            'freight' => $this->freight,
            'carry_fee' => $this->carryFee,
		);

		if ($this->nextStep == Conf_Refund::REFUND_STEP_SURE && $this->refundType)
        {
            $update['type'] = $this->refundType;
        }

		if ($this->nextStep == Conf_Refund::REFUND_STEP_SURE && ($this->refundType == Conf_Refund::REFUND_TYPE_ALONE || $this->refundType == Conf_Refund::REFUND_TYPE_NEXT_ORDER))
		{
            $price = $num = 0;
            foreach ($this->addOrder['products'] as $key =>$product) {
                if($product['num'] == 0)
                {
                    unset($this->addOrder['products'][$key]);
                    continue;
                }
                $price += $product['num'] * $product['price'];
                $num += $product['num'];
            }
            $refund = Refund_Api::getRefundByRid($this->rid);
            $order = Order_Api::getOrderInfo($refund['oid']);

            $cid = $order['cid'];
            $newOrder['wid'] = $refund['wid'];
            $newOrder['cid'] = $order['cid'];
            $newOrder['uid'] = $order['uid'];
            $newOrder['bid'] = $order['bid'];
            $newOrder['source_oid'] = $refund['oid'];
            $newOrder['saler_suid'] = $order['saler_suid'];
            $newOrder['step'] = Conf_Order::ORDER_STEP_NEW;
            $newOrder['source'] = Conf_Order::SOURCE_AFTER_SALE;
            $newOrder['contact_name'] = $order['contact_name'];
            $newOrder['contact_phone'] = $order['contact_phone'];
            $newOrder['city_id'] = $order['city_id'];
            $newOrder['district'] = $order['district'];
            $newOrder['area'] = $order['area'];
            $newOrder['address'] = $order['address'];
            if (empty($order['community_id']))
            {
                $newOrder['community_id'] = Conf_Order::VIRTUAL_COMMUNITY_ID;
            }
            else
            {
                $newOrder['community_id'] = $order['community_id'];
            }
            $newOrder['construction'] = $order['construction'];
            if($this->refundType == Conf_Refund::REFUND_TYPE_ALONE)
            {
                $newOrder['delivery_date'] = $this->addOrder['delivery_date'];
                $newOrder['delivery_date_end'] = $this->addOrder['delivery_date_end'];
            }
            $newOrder['product_num'] = $num;
            $newOrder['price'] = $price;
            $newOrder['note'] = '';
            $newOrder['suid'] = $this->_uid;
            $newOrder['aftersale_id'] = $this->rid;
            $newOrder['aftersale_type'] = Conf_Order::AFTERSALE_TYPE_REFUND;

            $products = $this->addOrder['products'];
            $suid = $this->_uid;
            $source = Conf_Order::SOURCE_AFTER_SALE;

            $newOid = Order_Api::addOrder($cid, $newOrder, $products, $suid, $source);
            Order_Api::forwardOrderStep($newOid, Conf_Order::ORDER_STEP_SURE, $this->_user);
            $update['rel_oid'] = $newOid;
            $update['rel_type'] = Conf_Refund::REFUND_REL_TYPE_ORDER;
        }

        if ($this->nextStep == Conf_Refund::REFUND_STEP_SURE 
            && ($this->refundType == Conf_Refund::REFUND_TYPE_NEXT_ORDER||$this->refundType==Conf_Refund::REFUND_TYPE_IMMEDIATELY))
        {
            $update['freight'] = $this->freight;
            $update['carry_fee'] = $this->carry_fee;
        }

        Order_Api::updateRefund($this->rid, $update);
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