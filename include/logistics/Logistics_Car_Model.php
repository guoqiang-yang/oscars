<?php

/**
 * Class Role_Car_Model
 *
 * 司机车型
 */
class Logistics_Car_Model extends Base_Func
{

	public function get($mid)
	{
		return Conf_Driver::$CAR_MODEL[$mid];
	}

	public function getAll(&$total)
	{
		$total = count(Conf_Driver::$CAR_MODEL);

		return Conf_Driver::$CAR_MODEL;
	}
}
