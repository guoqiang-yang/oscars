<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $did;
	private $driver;
	private $referer;

	protected function getPara()
	{
	    $carProvince = Tool_Input::clean('r', 'car_province', TYPE_STR);
		$this->driver = array(
			'name' => Tool_Input::clean('r', 'name', TYPE_STR),
			'mobile' => Tool_Input::clean('r', 'mobile', TYPE_STR),
            'real_name' => Tool_Input::clean('r', 'real_name', TYPE_STR),
            'card_num' => Tool_Input::clean('r', 'card_num', TYPE_STR),
			'bank_info' => Tool_Input::clean('r', 'bank_info', TYPE_STR),
			'car_model' => Tool_Input::clean('r', 'car_model', TYPE_UINT),
			'source' => Tool_Input::clean('r', 'source', TYPE_UINT),
			'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
			'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
			'car_code' => Tool_Input::clean('r', 'car_code', TYPE_INT),
			'can_carry' => Tool_Input::clean('r', 'can_carry', TYPE_UINT),
			'can_trash' => Tool_Input::clean('r', 'can_trash', TYPE_UINT),
			'can_escort' => Tool_Input::clean('r', 'can_escort', TYPE_UINT),
			'score' => Tool_Input::clean('r', 'score', TYPE_UINT),
			'note' => Tool_Input::clean('r', 'note', TYPE_STR),
            'trans_scope' => Tool_Input::clean('r', 'trans_scope', TYPE_STR),
            'car_province' => $carProvince,
            'car_number' => str_replace($carProvince, '', Tool_Input::clean('r', 'car_number', TYPE_STR)),
		);
		$this->referer = Tool_Input::clean('r', 'referer', TYPE_STR);

        // 编辑司机
        $this->did = Tool_Input::clean('r', 'did', TYPE_UINT);
	}

	protected function checkPara()
	{
		if (empty($this->driver['name']))
		{
			throw new Exception('driver: name empty');
		}
		if (empty($this->driver['mobile']))
		{
			throw new Exception('driver: mobile empty');
		}
		if (empty($this->driver['car_model']))
		{
			throw new Exception('driver: car model empty');
		}
		if (empty($this->driver['source']))
		{
			throw new Exception('driver: source empty');
		}
        if (!ctype_digit($this->driver['card_num']))
        {
            throw new Exception('收款卡号格式不正确，请重新填写！');
        }
        if (empty($this->driver['car_province']) || empty($this->driver['car_number']))
        {
            throw new Exception('车牌号不能为空');
        }
	}

	protected function main()
	{
		$cityId = Conf_Warehouse::getCityByWarehouse($this->driver['wid']);
		$this->driver['city_id'] = $cityId;

		if ($this->did)
		{
            $driverQueue = Logistics_Api::getDriverQueue($this->did);

            if ($this->driver['status'] == Conf_Base::STATUS_DELETED)
            {
                if (!empty($driverQueue))
                {
                    if ($driverQueue['step'] <= Conf_Driver::STEP_CHECK_IN || $driverQueue['step'] >= Conf_Driver::STEP_ARRIVE )
                    {
                        Logistics_Api::clearDriverQueue($this->did);
                    }
                    else
                    {
                        throw new Exception('driver:driver has been allocated');
                    }
                }
            }

            if ($driverQueue['car_model'] != $this->driver['car_model'] && ($driverQueue['step'] == Conf_Driver::STEP_EMPTY
                || $driverQueue['step'] == Conf_Driver::STEP_CHECK_IN || $driverQueue['step'] == Conf_Driver::STEP_ARRIVE))
            {
                Logistics_Api::updateDriverInQueue($this->did, array('car_model' => $this->driver['car_model']));
            }

			Logistics_Api::updateDriver($this->did, $this->driver);
		}
		else
		{
			$ret = Logistics_Api::addDriver($this->driver);
			$this->did = $ret;
		}
	}

	protected function outputPage()
	{
		$result = array('did' => $this->did, 'referer' => $this->referer);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App('pri');
$app->run();

