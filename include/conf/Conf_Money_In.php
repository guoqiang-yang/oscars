<?php
/**
 * 销售收入/支出 配置
 */

class Conf_Money_In
{
    /**
     * 默认返现比例.
     */
    const CUSTOMER_CASHBACK_RATE = 6;
    
    
	/**
	 * 单据状态.
	 */
	const 
		ORDER_PAIED         = 0,		//订单需支付
		REFUND_PAIED        = 1,		//退款单需支出
		FINANCE_INCOME      = 2,		//财务收款
		FINANCE_ADJUST      = 3,		//财务调账
        FINANCE_REFUND      = 4,        //财务退款
        CUSTOMER_PRE_PAY    = 5,        //客户预付
        CUSTOMER_CASHBACK   = 6,        //客户返现
        ORDER_REFUND        = 7,        //订单退款
        CUSTOMER_DISCOUNT   = 8,        //客户返点
        AMOUNT_CUSTOMER_PAID    = 9,    //账期客户支付
        SUPPLEMENT_FREIGHT      = 10,   //调整运费
        SUPPLEMENT_CARRIAGE     = 11,   //调整搬运费
        SUPPLEMENT_PRIVILEGE    = 12,   //调整优惠
        ORDER_DEL_REFUND        = 13,   //删除订单并退款
    
        PLATFORM_SERVICE_FEE     = 98,  //平台服务费
        CUSTOMER_BAD_DEBT        = 99,  //客户坏账
        CUSTOMER_AMOUNT_TRANSFER = 100; //客户余额转移
	
	/**
	 * 单据状态描述.
	 */
	public static $STATUS_DESC = array(
		self::ORDER_PAIED       => '销售单',
		self::REFUND_PAIED      => '退款单',
		self::FINANCE_INCOME    => '财务收款',
		self::FINANCE_ADJUST    => '财务调账',  //抹零
        self::FINANCE_REFUND    => '财务退款',  //下线
        self::CUSTOMER_PRE_PAY  => '客户预付',  //下线
        self::CUSTOMER_CASHBACK => '客户返现',  //下线
        self::ORDER_REFUND      => '订单退款',
        self::CUSTOMER_DISCOUNT => '客户返点',  //下线
        self::AMOUNT_CUSTOMER_PAID  => '账期客户支付',  //下线
        self::SUPPLEMENT_FREIGHT    => '调整运费',
        self::SUPPLEMENT_CARRIAGE   => '调整搬运费',
        self::SUPPLEMENT_PRIVILEGE  => '调整优惠',
        self::ORDER_DEL_REFUND      => '删单退款',
        
        self::PLATFORM_SERVICE_FEE          => '平台服务费',
        self::CUSTOMER_BAD_DEBT             => '客户坏账',
        self::CUSTOMER_AMOUNT_TRANSFER      => '客户余额转移',
	);
}
