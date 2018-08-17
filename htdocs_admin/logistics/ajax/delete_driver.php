<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $did;
    private $step;

	protected function getPara()
	{
		$this->did = Tool_Input::clean('r', 'did', TYPE_UINT);
	}

	protected function checkPara()
	{
	}

	protected function main()
	{
        $driverQueue = Logistics_Api::getDriverQueue($this->did);
        if (!empty($driverQueue))
        {
            //已接单、已出库的司机不能删除
            if ($driverQueue['step'] >= Conf_Driver::STEP_ALLOC && $driverQueue['step'] <= Conf_Driver::STEP_LEAVE)
            {
                $this->step = Conf_Driver::$STEP_DESC[$driverQueue['step']];
            }

            //签到的司机先撤销签到状态
            if ($driverQueue['step'] == Conf_Driver:: STEP_CHECK_IN || $driverQueue['step'] == Conf_Driver:: STEP_ARRIVE)
            {
                Logistics_Api::clearDriverQueue($this->did);
            }
        }
        if (empty($this->step))
        {
            Logistics_Api::deleteDriver($this->did);
        }
	}

	protected function outputPage()
	{
		$result = array('did' => $this->did);

        if (isset($this->step) && !empty($this->step))
        {
            $result['status'] = $this->step;
        }
		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App('pri');
$app->run();

