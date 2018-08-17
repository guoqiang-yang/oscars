<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $eid;
	private $exchanged = array();
	private $refundProducts = array();
	private $orderProducts = array();
    private $exchangedProducts = array();

	protected function getPara()
	{
		$this->eid = Tool_Input::clean('r', 'eid', TYPE_UINT);
        $this->refundProducts = json_decode(Tool_Input::clean('r', 'relproducts', TYPE_STR), true);
        $this->exchangedProducts = json_decode(Tool_Input::clean('r', 'excproducts', TYPE_STR), true);
		$this->exchanged = array(
			'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
			'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
			'm_type' => Tool_Input::clean('r', 'type', TYPE_UINT),
            'need_storage' => Tool_Input::clean('r', 'need_storage', TYPE_UINT),
			'reason_second_id' => Tool_Input::clean('r', 'reason_detail', TYPE_UINT),
			'reason_id' => Tool_Input::clean('r', 'reason_type', TYPE_UINT),
			'note' => Tool_Input::clean('r', 'note', TYPE_STR),
            'carry_fee' => Tool_Input::clean('r', 'carry_fee', TYPE_UINT),
            'freight' => Tool_Input::clean('r', 'freight', TYPE_UINT),
            'privilege' => Tool_Input::clean('r', 'privilege', TYPE_UINT)
		);
		if ($this->exchanged['m_type'] == 1)
        {
            $this->exchanged['delivery_date'] = Tool_Input::clean('r', 'delivery_date', TYPE_STR);
            $this->exchanged['delivery_time'] = Tool_Input::clean('r', 'delivery_time', TYPE_STR);
            $this->exchanged['delivery_time_end'] = Tool_Input::clean('r', 'delivery_time_end', TYPE_STR);
        }
	}

	protected function checkPara()
	{
		if (empty($this->exchanged['oid']))
		{
			throw new Exception('order:empty order id');
		}
        if (empty($this->exchanged['wid']))
        {
            throw new Exception('order:empty wid');
        }

        if ($this->exchanged['m_type'] == 1)
        {
            if (empty($this->exchanged['delivery_date'])) {
                throw new Exception('order:delivery date empty');
            }
            if (empty($this->exchanged['delivery_time'])) {
                throw new Exception('order:delivery time empty');
            }
            if (empty($this->exchanged['delivery_time_end'])) {
                throw new Exception('order:delivery time empty');
            }
            $startTime = strtotime($this->exchanged['delivery_date']) + $this->exchanged['delivery_time'] * 3600;
            $endTime = strtotime($this->exchanged['delivery_date']) + $this->exchanged['delivery_time_end'] * 3600;
            $this->exchanged['exchanged_time'] = date('Y-m-d H:00:00', $startTime);
            $this->exchanged['exchanged_time_end'] = date('Y-m-d H:00:00', $endTime);
            unset($this->exchanged['delivery_date']);
            unset($this->exchanged['delivery_time']);
            unset($this->exchanged['delivery_time_end']);
        }
        $refund_num = 0;
        $exchanged_num = 0;
        $virtual_num = 0;
        $this->orderProducts = Order_Api::getOrderProduct($this->exchanged['oid']);
        if($this->exchanged['need_storage'] == 1)
        {
            foreach ($this->refundProducts as $key=>$p)
            {
                if ($p['num'] <= 0)
                {
                    unset($this->refundProducts[$key]);
                    continue;
                }
                $refund_num += $p['num'];
                $apply_num = $this->orderProducts['order'][$p['pid']]['num']-$this->orderProducts['refund'][$p['pid']]['num'];
                if($apply_num<$p['num'])
                {
                    throw new Exception('refund:商品Pid-'.$p['pid'].' 退货数量大于可退数量');
                }
            }
        }else{
            foreach ($this->exchangedProducts as $key=>$p)
            {
                if ($p['num'] <= 0)
                {
                    unset($this->exchangedProducts[$key]);
                    continue;
                }
                $apply_num = $this->orderProducts['order'][$p['pid']]['num']-$this->orderProducts['refund'][$p['pid']]['num'];
                if($apply_num<$p['num'])
                {
                    throw new Exception('exchanged:商品Pid-'.$p['pid'].' 换货数量大于可退数量');
                }
            }
            foreach ($this->refundProducts as $p)
            {
                $refund_num += $p['num'];
            }
        }

        $orderInfo = Order_Api::getOrderInfo($this->exchanged['oid']);
        $relProducts = Shop_Api::getProductInfosBySids(array_keys(Conf_Exchanged::$VIRTUAL_PRODUCTS), $orderInfo['city_id'], Conf_Activity_Flash_Sale::PALTFORM_WECHAT, false);
        foreach ($this->exchangedProducts as $key=>$p)
        {
            if ($p['num'] <= 0)
            {
                unset($this->exchangedProducts[$key]);
                continue;
            }
            if(!in_array($p['pid'],array_keys($relProducts)))
            {
                $exchanged_num += $p['num'];
            }else{
                $virtual_num++;
            }

        }
        if(empty($this->refundProducts))
        {
            throw new Exception('refund:退货商品不能为空');
        }
        if(empty($this->exchangedProducts))
        {
            throw new Exception('order:换货商品不能为空');
        }
        if($this->exchanged['need_storage'] == 0)
        {
            if(count($this->refundProducts) != (count($this->exchangedProducts)-$virtual_num))
            {
                throw new Exception('退货商品种类不等于换货商品种类');
            }
            if($refund_num != $exchanged_num)
            {
                throw new Exception('退货商品数量不等于换货商品数量');
            }
            $this->exchanged['carry_fee'] = 0;
            $this->exchanged['freight'] = 0;
            $this->exchanged['privilege'] = 0;
        }
        $reasonList = Conf_Exchanged::$EXCHANGED_REASON_DETAIL;
        $need_storage = $reasonList[$this->exchanged['reason_id']][$this->exchanged['reason_second_id']]['need_storage'];
        if($this->exchanged['need_storage'] != $need_storage)
        {
            $reason_str = $need_storage > 0 ? '需要入库':'不需要入库';
            throw new Exception($reason_str);
        }

        $this->exchanged['content'] = json_encode(array('refund_products'=>$this->refundProducts,'exchanged_products'=>$this->exchangedProducts));
	}



	protected function checkAuth()
	{
		parent::checkAuth('/order/edit_exchanged');
	}

	protected function main()
	{
		if ($this->eid)
		{
			$oldExchanged = Exchanged_Api::getExchanged($this->eid);
			$oldStep = $oldExchanged['info']['step'];
            if($oldStep >= Conf_Exchanged::EXCHANGED_STEP_SURE)
            {
                throw new Exception('Exchanged:换货单已通过审核，不能再编辑');
            }
		}

        if (empty($this->eid))   //新建换货单
        {
            $oid = $this->exchanged['oid'];
            $tmp_exchanged = Exchanged_Api::getExchangedList('oid='.$oid.' AND step<>'.Conf_Exchanged::EXCHANGED_STEP_FINISHED.' AND exchanged_status='.Conf_Base::STATUS_NORMAL);
            if($tmp_exchanged['total']>0)
            {
                $exchanged = reset($tmp_exchanged['list']);
                throw new Exception('Exchanged:该订单还有在处理中的换货单,EID:'.$exchanged['eid']);
            }
            $this->exchanged['step'] = Conf_Exchanged::EXCHANGED_STEP_NEW;
            $this->exchanged['suid'] = $this->_uid;
            $this->eid = Exchanged_Api::addExchanged($oid, $this->exchanged);

            //订单操作日志
            $param = array(
                'wid' => $this->exchanged['wid'],
                'eid' => $this->eid
            );
            $param['rel_products'] = '';
            $param['adjust'] = 0;
            foreach ($this->refundProducts as $p)
            {
                $param['rel_products'] .= $p['pid'] . '=>' . $p['num'] . ';';
                $param['adjust'] += $p['num']*$p['price'];
            }
            $param['adjust2'] = 0;
            $param['exchanged_products'] = '';
            foreach ($this->exchangedProducts as $p)
            {
                $param['exchanged_products'] .= $p['pid'] . '=>' . $p['num'] . ';';
                $param['adjust2'] += $p['num']*$p['price'];
            }

            Admin_Api::addOrderActionLog($this->_uid, $oid, Conf_Order_Action_Log::ACTION_CREATE_EXCHANGED_ORDER, $param);
        }
        else    //编辑换货单
        {
            Exchanged_Api::updateExchanged($this->eid, $this->exchanged);
        }

	}

	protected function outputPage()
	{
		$result = array('eid' => $this->eid, 'oid' => $this->exchanged['oid'], 'st' => $this->retSt);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pri');
$app->run();

