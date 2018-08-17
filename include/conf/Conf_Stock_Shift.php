<?php

class Conf_Stock_Shift
{
	const
        STEP_PENDING = -5,      //待处理
        STEP_REBUT = -6,        //已驳回
		STEP_CREATE = 1,		//创建
		STEP_STOCK_OUT = 2,		//已出库
		STEP_STOCK_IN = 3,		//已入库
        STEP_PART_SHELVED = 4,  //部分上架
        STEP_SHELVED = 5;       //已上架
	
	public static $Step_Descs = array(
        self::STEP_PENDING      => '待处理',
		self::STEP_CREATE		=> '未出库',
        self::STEP_REBUT        => '已驳回',
		self::STEP_STOCK_OUT	=> '已出库',
		self::STEP_STOCK_IN		=> '已入库',
        self::STEP_PART_SHELVED => '部分上架',
        self::STEP_SHELVED      => '已上架',
	);

	public static function getStepName($step)
    {
        return isset(self::$Step_Descs[$step]) ? self::$Step_Descs[$step] : '-';
    }
}