<?php

/**
 * order 配置
 */
class Conf_Traps
{
	/**
	 * 订单状态:
	 */
	const
		TRAPS_STEP_NEW = 1,         //未审核
        TRAPS_STEP_SURE = 2,        //已审核
        TRAPS_STEP_FINISHED = 3;    //已完成

	/**
	 * @var array 付款方式
	 */
	public static $TRAPS_STEPS = array(
		self::TRAPS_STEP_NEW => '未审核',
		self::TRAPS_STEP_SURE => '已审核',
		self::TRAPS_STEP_FINISHED => '已完成',
	);

    /**
     * @var array 补货原因类别
     *
     *
     */
    public static $TRAPS_REASON_TYPE = array(
        1 => '库房问题',
        2 => '临采问题',
        3 => '配送问题',
        4 => '客服问题',
    );

    /**
     * @var array 换货原因，一级下表是换货原因类别
     *
     *
     */
    public static $TRAPS_REASON_DETAIL = array(
        1 => array(
            1 => array('name' => '库房漏装', 'need_storage' => 0)
        ),
        2 => array(
            1 => array('name' => '供应商漏装', 'need_storage' => 0)
        ),
        3 => array(
            1 => array('name' => '司机漏送', 'need_storage' => 0)
        ),
        4 => array(
            1 => array('name' => '客服漏录', 'need_storage' => 1)
        ),
    );



	public static function getTrapsStepName($step)
	{
		assert(isset(self::$TRAPS_STEPS[$step]));

		return self::$TRAPS_STEPS[$step];
	}

	public static function getTrapsStepNames()
	{
		return self::$TRAPS_STEPS;
	}

	//二种补漏方式
	const TRAPS_TYPE_ALONE = 1,        //单独补漏
          TRAPS_TYPE_NEXT_ORDER = 2;   //预约补漏

    public static $TRAPS_TYPES = array(
        self::TRAPS_TYPE_ALONE => '单独补漏',
        self::TRAPS_TYPE_NEXT_ORDER => '预约补漏',
    );

}
