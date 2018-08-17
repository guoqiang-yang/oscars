<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $cid;
	private $carrier;
	private $referer;

	protected function getPara()
	{
		$this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
		$this->carrier = array(
			'name' => Tool_Input::clean('r', 'name', TYPE_STR),
			'mobile' => Tool_Input::clean('r', 'mobile', TYPE_STR),
            'real_name' => Tool_Input::clean('r', 'real_name', TYPE_STR),
            'card_num' => Tool_Input::clean('r', 'card_num', TYPE_STR),
			'bank_info' => Tool_Input::clean('r', 'bank_info', TYPE_STR),
			'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
			'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
		);
		$this->referer = Tool_Input::clean('r', 'referer', TYPE_STR);
	}

	protected function checkPara()
	{
		if (empty($this->carrier['name']))
		{
			throw new Exception('carrier: name empty');
		}
		if (empty($this->carrier['mobile']))
		{
			throw new Exception('carrier: mobile empty');
		}
        if (!ctype_digit($this->carrier['card_num']))
        {
            throw new Exception('收款卡号格式不正确，请重新填写！');
        }
	}

	protected function checkAuth()
	{
		parent::checkAuth('/logistics/add_carrier');
	}

	protected function main()
	{
		$wid = $this->carrier['wid'];
		$cityId = Conf_Warehouse::getCityByWarehouse($wid);
		$this->carrier['city_id'] = $cityId;

		if ($this->cid)
		{
			Logistics_Api::updateCarrier($this->cid, $this->carrier);

		}
		else
		{
			$ret = Logistics_Api::addCarrier($this->carrier);
			$this->cid = $ret;
		}
	}

	protected function outputPage()
	{
		$result = array('cid' => $this->cid, 'referer' => $this->referer);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App('pri');
$app->run();

