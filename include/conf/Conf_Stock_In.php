<?php

class Conf_Stock_In
{
    /**
     * 入库单状态
     */
    const
        STEP_STOCKIN       = 1,
        STEP_PART_SHELVED  = 2,
        STEP_SHELVED       = 3;
    
    
    public static $Step_Descs = array(
        self::STEP_STOCKIN =>      '已入库',
        self::STEP_PART_SHELVED => '部分上架',
        self::STEP_SHELVED =>      '已上架',
    );
    
    const UN_PAID = 0,              //未支付
          HAD_PAID = 1,             //已支付
          CHECKED_ACCOUNT = 2,      //采购兑账
          FINANCE_ACCOUNT = 3;      //财务已兑账

	public static function getPaidStatusName($paid)
	{
		$name = '未付';
		if ($paid==1)
		{
			$name = '已付';
		}
		else if ($paid==2)
		{
			$name = '采购已兑账';
		}
		else if ($paid==3)
		{
			$name = '兑账未支付';
		}
		return $name;
	}
}

