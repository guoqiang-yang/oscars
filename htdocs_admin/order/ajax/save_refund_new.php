<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $rid;
	private $productStr;
    private $oldRefund = array();
	private $refund = array();
	private $products = array();
	private $orderProduct = array();
    private $chgedPnum4VRefund = array();   // 空退: 调的空退商品数量
	private $isRefundFreight;
	private $isRefundCarryFee;
	private $relOrderInfo;

	protected function getPara()
	{
		$this->rid = Tool_Input::clean('r', 'rid', TYPE_UINT);
		$this->productStr = Tool_Input::clean('r', 'product_str', TYPE_STR);
		$this->refund = array(
			'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
			'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
			'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
			'refund_freight' => Tool_Input::clean('r', 'refund_freight', TYPE_UINT) * 100,
			'refund_carry_fee' => Tool_Input::clean('r', 'refund_carry_fee', TYPE_UINT) * 100,
            'freight' => Tool_Input::clean('r', 'freight', TYPE_UINT) * 100,
            'carry_fee' => Tool_Input::clean('r', 'carry_fee', TYPE_UINT) * 100,
			'reason' => Tool_Input::clean('r', 'reason', TYPE_UINT),
			'reason_type' => Tool_Input::clean('r', 'reason_type', TYPE_UINT),
			'note' => Tool_Input::clean('r', 'note', TYPE_STR),
		);
		$this->isRefundFreight = Tool_Input::clean('r', 'is_refund_freight', TYPE_UINT);
		$this->isRefundCarryFee = Tool_Input::clean('r', 'is_refund_carry_fee', TYPE_UINT);
        
		if ($this->isRefundFreight == 0)
		{
			$this->refund['refund_freight'] = 0;
		}

		if ($this->isRefundCarryFee == 0)
		{
			$this->refund['refund_carry_fee'] = 0;
		}
        $isTrapsFreight = Tool_Input::clean('r', 'is_traps_freight', TYPE_UINT);
        $isTrapsCarryFee = Tool_Input::clean('r', 'is_traps_carry_fee', TYPE_UINT);
        if($isTrapsCarryFee == 0)
        {
            $this->refund['carry_fee'] = 0;
        }
        if($isTrapsFreight == 0)
        {
            $this->refund['freight'] = 0;
        }
        
		if ($this->refund['type'] == Conf_Refund::REFUND_TYPE_ALONE)
        {
            $this->relOrderInfo['delivery_date'] = Tool_Input::clean('r', 'delivery_date', TYPE_STR);
            $this->relOrderInfo['delivery_time'] = Tool_Input::clean('r', 'delivery_time', TYPE_STR);
            $this->relOrderInfo['delivery_time_end'] = Tool_Input::clean('r', 'delivery_time_end', TYPE_STR);
        }
	}

	protected function checkPara()
	{
		if (empty($this->refund['oid']))
		{
			throw new Exception('order:empty order id');
		}
	}

	private function parseProducts($str)
	{
		// 解析字符串
		$products = $pids = array();
		$items = array_filter(explode(',', $str));
		foreach ($items as $item)
		{
			list($pid, $num, $price) = explode(":", $item);
            
            // 新建退货单 退货数量为0的商品，不记录
            if (empty($this->rid) && $num==0)
            {
                continue;
            }
            
            //创建退货单时：申请数量==退货数量；防止订单重复退货；最终的退货数量在售后二次审核时，最终确定
			$products[] = array(
				'pid' => $pid,
				'apply_rnum' => $num,
                'num' => $num,
				'oid' => $this->refund['oid'],
				'rid' => $this->rid,
				'price' => 100 * $price,
                'ori_price' => $this->orderProduct['order'][$pid]['ori_price'],
				'cost' => $this->orderProduct['order'][$pid]['cost'],
				'sid' => $this->orderProduct['order'][$pid]['sid'],
                'city_id' => $this->orderProduct['order'][$pid]['city_id'],
			);
			$pids[] = $pid;
		}

		if (empty($products))
		{
			return array();
		}

		return $products;
	}

	protected function checkAuth()
	{
		parent::checkAuth('/order/edit_refund_new');
		
	}

	protected function main()
	{
        $this->oldRefund = !empty($this->rid)? Order_Api::getBaseRefundInfo($this->rid): array();
        
        // 订单商品，退单商品
		$this->orderProduct = Order_Api::getOrderProduct($this->refund['oid']);
        Refund_Api::appendRefundInfo4OrderProducts($this->orderProduct['order'], $this->orderProduct['refund']);
        
		if (!$this->rid || $this->oldRefund['step'] < Conf_Refund::REFUND_STEP_SURE)
		{
			$this->products = $this->parseProducts($this->productStr);

			if (empty($this->products)) throw new Exception('warehouse:empty products');
		}
        
        // 检测是否可以进行空退
        $this->_check4VirtualRefund();
        
        // 退货单
        if (empty($this->rid))   //新建退款单
        {
            $oid = $this->refund['oid'];
            $this->refund['step'] = Conf_Refund::REFUND_STEP_NEW;
            $this->refund['suid'] = $this->_uid;
            $this->rid = Order_Api::addRefund($oid, $this->refund, $this->products);

            // 关联订单
            if (isset($this->relOrderInfo) && !empty($this->relOrderInfo) && !empty($this->rid))
            {
                $kv = new Data_Kvdb();
                $kv->set(Conf_Refund::REL_ORDER_INFO . $this->rid, json_encode($this->relOrderInfo));
            }
            
            //订单操作日志
            $this->_writeRefundLog($oid);
        }
        else    //编辑退款单
        {
            if ($this->oldRefund['step'] == Conf_Refund::REFUND_STEP_NEW)
            {
                $kv = new Data_Kvdb();
                $kv->set(Conf_Refund::REL_ORDER_INFO . $this->rid, json_encode($this->relOrderInfo));
            }
            Order_Api::updateRefund($this->rid, $this->refund, $this->products);
        }
        
        // 空退相关 更新商品已空退数量
        $this->_updateOrderProducts2VirtualRefund();
	}

	protected function outputPage()
	{
		$result = array('rid' => $this->rid, 'oid' => $this->refund['oid']);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
    
    // 检测商品，是否可以空退
    private function _check4VirtualRefund()
    {
        if ($this->refund['type'] != Conf_Refund::REFUND_TYPE_VIRTUAL &&
            $this->oldRefund['type'] != Conf_Refund::REFUND_TYPE_VIRTUAL) return;
        
        //退单中商品的数量
        $thisRefundPnum = array();
        foreach($this->orderProduct['refund'] as $key => $pinfo)
        {
            list($_rid, $_pid) = explode('#', $key);
            if ($_rid != $this->rid) continue;
            
            $thisRefundPnum[$_pid] = $pinfo['num'];
        }
        
        //退货商品的数量变更
        if (empty($this->rid) || $this->oldRefund['type']==$this->refund['type'])
        {//新建退货单，或不变更退货类型
            foreach($this->products as $_pinfo)
            {
                $hadRPnum = intval($thisRefundPnum[$_pinfo['pid']]);
                $this->chgedPnum4VRefund[$_pinfo['pid']] = $_pinfo['num'] - $hadRPnum;
            }
        }
        else if ($this->refund['type']==Conf_Refund::REFUND_TYPE_VIRTUAL && $this->oldRefund['type']!=$this->refund['type'])
        {//退货单类型变更: 非空退 -> 空退
            foreach($this->products as $_pinfo)
            {
                $this->chgedPnum4VRefund[$_pinfo['pid']] = $_pinfo['num'];
            }
        }
        else if ($this->refund['type']!=Conf_Refund::REFUND_TYPE_VIRTUAL && $this->oldRefund['type']!=$this->refund['type'])
        {//退货单类型变更: 空退 -> 非空退
            foreach($this->products as $_pinfo)
            {
                $this->chgedPnum4VRefund[$_pinfo['pid']] = 0-$_pinfo['num'];
            }
        }
        
        // 校验
        foreach($this->chgedPnum4VRefund as $_pid => $_chgNum)
        {
            $canRefundVnum = $this->orderProduct['order'][$_pid]['can_refund_vnum'];
            if ($_chgNum > $canRefundVnum)
            {
                throw new Exception("【空退】商品数量不足：$_pid 预退数量：$_chgNum 可退数量：". $this->orderProduct['order'][$_pid]['can_refund_vnum']);
            }
        }
    }
    
    private function _updateOrderProducts2VirtualRefund()
    {
        $oo = new Order_Order();
        foreach($this->chgedPnum4VRefund as $_pid => $_chgNum)
        {
            if ($_chgNum == 0) continue;
            
            $newRefundVnum = max(0, $this->orderProduct['order'][$_pid]['refund_vnum']+$_chgNum);
            
            $oo->updateOrderProductInfo($this->refund['oid'], $_pid, 0, array('refund_vnum'=>$newRefundVnum));
        }
    }
    
    private function _writeRefundLog($oid)
    {
        $param['wid'] = $this->refund['wid'];
        $param['products'] = '';
        foreach ($this->products as $p)
        {
            $param['products'] .= $p['pid'] . '=>' . $p['num'] . ';';
        }
        Admin_Api::addOrderActionLog($this->_uid, $oid, Conf_Order_Action_Log::ACTION_CREATE_REFUND_ORDER, $param);
    }
    
}

$app = new App('pri');
$app->run();

