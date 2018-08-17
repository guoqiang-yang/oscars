<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $id;
	private $productStr;

	private $products = array();

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
		$this->productStr = Tool_Input::clean('r', 'product_str', TYPE_STR);
	}

	protected function checkPara()
	{
		if (empty($this->id))
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
			$products[] = array('pid' => $pid, 'num' => $num, 'id' => $this->id, 'price' => round($price*100));
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
		$this->products = $this->parseProducts($this->productStr);
		Warehouse_Api::addStockInProducts($this->id, $this->products);
	}

	protected function outputPage()
	{
		$result = array('id' => $this->id);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App('pri');
$app->run();

