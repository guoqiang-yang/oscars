<?php

/**
 * Class Logistics_Driver_Source
 *
 * 司机来源
 */
class Logistics_Driver_Source extends Base_Func
{
	public function get($sid)
	{
		return Conf_Driver::$DRIVER_SOURCE[$sid];
	}

	public function getAll(&$total)
	{
		// 查询数量
		$total = count(Conf_Driver::$DRIVER_SOURCE);

		return Conf_Driver::$DRIVER_SOURCE;
	}
}
