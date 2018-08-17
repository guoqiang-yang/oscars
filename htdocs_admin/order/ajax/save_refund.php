<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $rid;
	private $productStr;
	private $refund = array();
	private $products = array();
	private $orderProduct = array();
	private $retSt = 0;

	protected function getPara()
	{
		$this->rid = Tool_Input::clean('r', 'rid', TYPE_UINT);
		$this->productStr = Tool_Input::clean('r', 'product_str', TYPE_STR);
		$this->refund = array(
			'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
			'adjust' => intval(Tool_Input::clean('r', 'adjust', TYPE_NUM) * 100),
			'note' => Tool_Input::clean('r', 'note', TYPE_STR),
			'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
		);
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
			$products[] = array(
				'pid' => $pid,
				'num' => $num,
				'oid' => $this->refund['oid'],
				'rid' => $this->rid,
				'price' => 100 * $price,
				'cost' => $this->orderProduct['order'][$pid]['cost'],
                'sid' => $this->orderProduct['order'][$pid]['sid'],
			);
			$pids[] = $pid;
		}
		if (empty($products))
		{
			return array();
		}

		return $products;
	}

	protected function main()
	{
		if ($this->rid)
		{
			$oldRefund = Order_Api::getRefund($this->rid);
			$oldStep = $oldRefund['info']['step'];
		}

		$this->orderProduct = Order_Api::getOrderProduct($this->refund['oid']);

		if (!$this->rid || $oldStep < Conf_Refund::REFUND_STEP_SURE)
		{
			$this->products = $this->parseProducts($this->productStr);
			if (empty($this->products))
			{
				throw new Exception('warehouse:empty products');
			}
		}

		// 检测退货商品的数量
		$this->_checkRefundNum();

		if ($this->retSt == 0)
		{
			if (empty($this->rid))   //新建退款单
			{
				$order = Order_Api::getOrderInfo($this->refund['oid']);

				$this->refund['city_id'] = $order['city_id'];
				$oid = $this->refund['oid'];
				$this->refund['step'] = Conf_Refund::REFUND_STEP_NEW;
				$this->refund['suid'] = $this->_uid;
				$this->rid = Order_Api::addRefund($oid, $this->refund, $this->products);

				//订单操作日志
				$param = array(
					'wid' => $this->refund['wid'],
					'adjust' => $this->refund['adjust'] / 100,
				);
				$param['products'] = '';
				foreach ($this->products as $p)
				{
					if ($p['num'] <= 0)
					{
						continue;
					}
					$param['products'] .= $p['pid'] . '=>' . $p['num'] . ';';
				}

				Admin_Api::addOrderActionLog($this->_uid, $oid, Conf_Order_Action_Log::ACTION_CREATE_REFUND_ORDER, $param);
			}
			else    //编辑退款单
			{
				Order_Api::updateRefund($this->rid, $this->refund, $this->products);
			}
		}
	}

	protected function outputPage()
	{
		$result = array('rid' => $this->rid, 'oid' => $this->refund['oid'], 'st' => $this->retSt);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}

	private function _checkRefundNum()
	{
		$canRefundProductNums = array();
		foreach ($this->orderProduct['order'] as $sid => $product)
		{
			$num = $product['num'];
			$excludeKey = $this->rid . '#' . $sid;
			foreach ($this->orderProduct['refund'] as $rkey => $rproduct)
			{
				if ($rkey != $excludeKey && $product['pid'] == $rproduct['pid'])
				{
					$num -= $rproduct['num'];
				}
			}

			$canRefundProductNums[$sid] = $num;
		}

		$refundProduct = array();
		foreach ($this->products as $rproduct)
		{
			if ($rproduct['num'] > $canRefundProductNums[$rproduct['pid']])
			{
				$this->retSt = -1;
			}

			if ($rproduct['num'] > 0)
			{
				$refundProduct[] = $rproduct['pid'];
			}
		}

		if (empty($refundProduct))  //退货单为空
		{
			//$this->retSt = -2;
		}

	}

}

$app = new App('pri');
$app->run();

