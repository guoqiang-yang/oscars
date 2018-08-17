<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $tid;
	private $traps = array();
    private $trapsProducts = array();

	protected function getPara()
	{
		$this->tid = Tool_Input::clean('r', 'tid', TYPE_UINT);
        $this->trapsProducts = json_decode(Tool_Input::clean('r', 'products', TYPE_STR), true);
		$this->traps = array(
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
		if ($this->traps['m_type'] == 1)
        {
            $this->traps['delivery_date'] = Tool_Input::clean('r', 'delivery_date', TYPE_STR);
            $this->traps['delivery_time'] = Tool_Input::clean('r', 'delivery_time', TYPE_STR);
            $this->traps['delivery_time_end'] = Tool_Input::clean('r', 'delivery_time_end', TYPE_STR);
        }
	}

	protected function checkPara()
	{
		if (empty($this->traps['oid']))
		{
			throw new Exception('order:empty order id');
		}
        if (empty($this->traps['wid']))
        {
            throw new Exception('order:empty wid');
        }

        if ($this->traps['m_type'] == 1)
        {
            if (empty($this->traps['delivery_date'])) {
                throw new Exception('order:delivery date empty');
            }
            if (empty($this->traps['delivery_time'])) {
                throw new Exception('order:delivery time empty');
            }
            if (empty($this->traps['delivery_time_end'])) {
                throw new Exception('order:delivery time empty');
            }
            $startTime = strtotime($this->traps['delivery_date']) + $this->traps['delivery_time'] * 3600;
            $endTime = strtotime($this->traps['delivery_date']) + $this->traps['delivery_time_end'] * 3600;
            $this->traps['traps_time'] = date('Y-m-d H:00:00', $startTime);
            $this->traps['traps_time_end'] = date('Y-m-d H:00:00', $endTime);
            unset($this->traps['delivery_date']);
            unset($this->traps['delivery_time']);
            unset($this->traps['delivery_time_end']);
        }
        if(!empty($this->trapsProducts))
        {
            foreach ($this->trapsProducts as $key => &$product)
            {
                if($product['num'] == 0)
                {
                    unset($this->trapsProducts[$key]);
                }
                $product['price'] = floor($product['price']*100);
            }
        }
        if(empty($this->trapsProducts))
        {
            throw new Exception('order:补漏商品不能为空');
        }
        if($this->traps['need_storage'] == 0)
        {
            $this->traps['carry_fee'] = 0;
            $this->traps['freight'] = 0;
            $this->traps['privilege'] = 0;
            $pids = Tool_Array::getFields($this->trapsProducts, 'pid');
            $orderProducts = Order_Api::getOrderProducts($this->traps['oid']);
            $orderPids = Tool_Array::getFields($orderProducts['products'], 'pid');
            if($pids != array_intersect($pids, $orderPids))
            {
                throw new Exception('不需要出库不能添加新商品');
            }
        }
        $need_storage = Conf_Traps::$TRAPS_REASON_DETAIL[$this->traps['reason_id']][$this->traps['reason_second_id']]['need_storage'];
        if($this->traps['need_storage'] != $need_storage)
        {
            $reason_str = $need_storage > 0 ? '需要出库':'不需要出库';
            throw new Exception($reason_str);
        }

        $this->traps['content'] = json_encode($this->trapsProducts);
	}



	protected function checkAuth()
	{
		parent::checkAuth('/order/edit_traps');
	}

	protected function main()
	{
		if ($this->tid)
		{
			$oldTraps = Traps_Api::getTraps($this->tid);
			$oldStep = $oldTraps['info']['step'];
            if($oldStep >= Conf_Traps::TRAPS_STEP_SURE)
            {
                throw new Exception('补漏单已通过审核，不能再编辑');
            }
		}

        if (empty($this->tid))   //新建换货单
        {
            $oid = $this->traps['oid'];
            $tmp_traps = Traps_Api::getTrapsList('oid='.$oid.' AND step<>'.Conf_Traps::TRAPS_STEP_FINISHED.' AND traps_status='.Conf_Base::STATUS_NORMAL);
            if($tmp_traps['total']>0)
            {
                $traps = reset($tmp_traps['list']);
                throw new Exception('Traps:该订单还有在处理中的补漏单,TID:'.$traps['tid']);
            }
            $this->traps['step'] = Conf_Traps::TRAPS_STEP_NEW;
            $this->traps['suid'] = $this->_uid;
            $this->tid = Traps_Api::addTraps($oid, $this->traps);

            //订单操作日志
            $param = array(
                'wid' => $this->traps['wid'],
                'tid' => $this->tid
            );
            $param['adjust'] = 0;
            $param['traps_products'] = '';
            foreach ($this->trapsProducts as $p)
            {
                $param['traps_products'] .= $p['pid'] . '=>' . $p['num'] . ';';
                $param['adjust'] += $p['num']*$p['price'];
            }

            Admin_Api::addOrderActionLog($this->_uid, $oid, Conf_Order_Action_Log::ACTION_CREATE_TRAPS_ORDER, $param);
        }
        else    //编辑换货单
        {
            Traps_Api::updateTraps($this->tid, $this->traps);
        }

	}

	protected function outputPage()
	{
		$result = array('tid' => $this->tid, 'oid' => $this->traps['oid']);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pri');
$app->run();

