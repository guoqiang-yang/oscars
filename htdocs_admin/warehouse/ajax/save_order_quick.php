<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $productStr;

	private $oid;
	private $sid;
	private $order = array();
	private $products = array();

	protected function getPara()
	{
		$this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
		$this->productStr = Tool_Input::clean('r', 'product_str', TYPE_STR);
		$this->order = array(
			'sid' => Tool_Input::clean('r', 'sid', TYPE_UINT),
			'contact_name' => Tool_Input::clean('r', 'contact_name', TYPE_STR),
			'contact_phone' => Tool_Input::clean('r', 'contact_phone', TYPE_STR),
			'delivery_date' => Tool_Input::clean('r', 'delivery_date', TYPE_STR),
			'freight' => 100*Tool_Input::clean('r', 'freight', TYPE_UINT),
			'privilege' => 100*Tool_Input::clean('r', 'privilege', TYPE_UINT),
			'privilege_note' => Tool_Input::clean('r', 'privilege_note', TYPE_STR),
			'note' => Tool_Input::clean('r', 'note', TYPE_STR),
		);
	}

	protected function checkPara()
	{
		if (empty($this->order['contact_name']))
		{
			throw new Exception('customer:contact person name empty');
		}
		if (empty($this->order['contact_phone']))
		{
			throw new Exception('order:empty phone');
		}
		if (empty($this->order['sid']))
		{
			throw new Exception('warehouse:empty supplier name');
		}
	}

	private function parseProducts($str)
	{
		// 解析字符串
		$products = $pids = array();
		$items = array_filter(explode(',', $str));
		$totalNum = 0;
		foreach ($items as $item)
		{
			list($pid, $num, $price) = explode(":", $item);

			if ($num > 0 && empty($price))
			{
				throw new Exception('warehouse:in order must has price');
			}
			if ($num > 0)
			{
				$totalNum ++;
			}

			$products[] = array('pid' => $pid, 'num' => $num, 'oid' => $this->oid, 'price' => 100*$price);
			$pids[] = $pid;
		}
		if (empty($products))
		{
			return array();
		}
		if (empty($totalNum))
		{
			throw new Exception('warehouse:empty in_order products');
		}
		return $products;
	}

	protected function main()
	{
		$this->order['step'] = Conf_In_Order::ORDER_STEP_SURE;
		$this->products = $this->parseProducts($this->productStr);
		$this->oid = Warehouse_Api::addOrder($this->sid, $this->order, $this->products);
	}

	protected function outputPage()
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

