<?php

include_once ('../../../global.php');

//保存好材<==>第三方sku映射关系，没用
exit;

class App extends App_Admin_Ajax
{
	private $id;
	private $info;

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
		$this->info = array(
			'mid' => Tool_Input::clean('r', 'mid', TYPE_UINT),
			'msid' => Tool_Input::clean('r', 'msid', TYPE_UINT),
			'sid' => Tool_Input::clean('r', 'sid', TYPE_UINT),
			'mprice' => Tool_Input::clean('r', 'mprice', TYPE_NUM) * 100,
			'price' => Tool_Input::clean('r', 'price', TYPE_NUM) * 100,
		);
	}

	protected function checkPara()
	{
		if (empty($this->info['mid']))
		{
			throw new Exception('merchant: mid empty');
		}
		if (empty($this->info['msid']))
		{
			throw new Exception('merchant: msid empty');
		}
		if (empty($this->info['sid']))
		{
			throw new Exception('merchant: sid empty');
		}
		if (empty($this->info['mprice']))
		{
			throw new Exception('merchant: mprice empty');
		}
		if (empty($this->info['price']))
		{
			throw new Exception('merchant: price empty');
		}

		$conf = array(
			'mid' => $this->info['mid'],
			'msid' => $this->info['msid'],
		);
		$list = Merchant_Api::getList($conf, 0, 1);
		if (!empty($list))
		{
			throw new Exception('merchant: msid exists');
		}

		$conf = array(
			'mid' => $this->info['mid'],
			'sid' => $this->info['sid'],
		);
		$list = Merchant_Api::getList($conf, 0, 1);
		if (!empty($list))
		{
			throw new Exception('merchant: sid exists');
		}
	}

	protected function main()
	{
		if (empty($this->id))
		{
			$this->id = Merchant_Api::add($this->info);
		}
		else
		{
			Merchant_Api::update($this->id, $this->info);
		}
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