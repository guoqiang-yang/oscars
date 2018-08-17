<?php

/**
 * 采购收入/支出 配置
 */

class Conf_Money_Out
{
	/**
	 * 单据状态.
	 */
	const 
		PURCHASE_IN_STORE           = 0,	//入库单
		FINANCE_PAID                = 1,	//财务付款
		FINANCE_ADJUST              = 2,	//财务调账
		FINANCE_PRE_PAY             = 3,	//财务预付 - 预付到采购单
		SUPPLIER_PRIVILEGE          = 4,	//商家返现
        STOCKIN_REFUND              = 5;    //入库单退货出库
        
	
	/**
	 * 单据状态描述.
	 */
	public static $STATUS_DESC = array(
		self::PURCHASE_IN_STORE         => '入库单',
		self::FINANCE_PAID              => '财务付款',
		self::FINANCE_ADJUST            => '财务调账',
		self::FINANCE_PRE_PAY           => '财务预付',
		self::SUPPLIER_PRIVILEGE        => '商家返现',
        self::STOCKIN_REFUND            => '入库单退货',
	);
}
