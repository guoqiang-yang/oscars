<?php

class Conf_Finance
{

	/**
	 * 默认返现比例.
	 */
	const CUSTOMER_CASHBACK_RATE = 0;

	/**
	 * 返现基数 - 达到该基数可以返现.
	 */
	const CUSTOMER_CASHBACK_BASE = 1000000; // 10000元


	/**============================================
	 *          客户余额
	 **============================================*/

	/**
	 * 客户余额类型
	 */
	const
		CRM_AMOUNT_TYPE_PREPAY          = 1,
		CRM_AMOUNT_TYPE_CASHBACK        = 2,
		CRM_AMOUNT_TYPE_PAID            = 3,
		CRM_AMOUNT_TYPE_REFUND          = 4,
		CRM_AMOUNT_TYPE_CASH            = 5,
		CRM_AMOUNT_TYPE_FINANCE_REFUND  = 6,
		CRM_AMOUNT_TYPE_PREPAY_ORDER    = 7,
		CRM_AMOUNT_TYPE_PAYBACK         = 8,
        CRM_AMOUNT_TYPE_DEL_REFUND      = 9,
        CRM_AMOUNT_TYPE_CONTACT_REBATE  = 10,
        CRM_AMOUNT_TYPE_PAY_TAXES = 11,
        CRM_AMOUNT_CUSTOMER_TRANSFER    = 99,
		CRM_AMOUNT_TRANSFER             = 100;


	public static $Crm_AMOUNT_TYPE_DESCS = array(
		self::CRM_AMOUNT_TYPE_PREPAY        => '预付',
		self::CRM_AMOUNT_TYPE_CASHBACK      => '预存返现',          //下线
		self::CRM_AMOUNT_TYPE_PAID          => '订单支付',      
		self::CRM_AMOUNT_TYPE_REFUND        => '订单退款',          //退款单退回
		self::CRM_AMOUNT_TYPE_CASH          => '提现',
		self::CRM_AMOUNT_TYPE_FINANCE_REFUND    => '财务退款',      //从客户财务流水退回, 下线
		self::CRM_AMOUNT_TYPE_PREPAY_ORDER      => '订单预存',
		self::CRM_AMOUNT_TYPE_PAYBACK           => '客户补偿',
        self::CRM_AMOUNT_TYPE_DEL_REFUND        => '删单退款',
        self::CRM_AMOUNT_TYPE_CONTACT_REBATE    => '合同返点',
        self::CRM_AMOUNT_TYPE_PAY_TAXES         => '支付其他服务费',
        self::CRM_AMOUNT_CUSTOMER_TRANSFER      => '客户间余额转移',
		self::CRM_AMOUNT_TRANSFER               => '财务转余额',
	);

	/**
	 * 对应供货商的财务支出类型 money_out
	 */
	const MO_PRIVATE_CMBC_3829 = 1,
		MO_PRIVATE_CMBC_9940 = 2,
		MO_PRIVATE_SPDB_8336 = 3,
        MO_PRIVATE_CCB_5306 = 4,
		MO_PUBLIC_CMBC = 10,
        MO_GENERAL_BRCB = 20,
        MO_GENERAL_CCB = 21,
        MO_GENERAL_CMB = 22,
        MO_TJ_LZ_0701   = 23,
        MO_CHEQUE = 30,
        MO_RESERVE_FUND = 31,
        MO_BASE_CMB_0501_CQ = 40,
        MO_PUBLIC_0201_CD = 41,
        MO_CMB_0628_QD = 42,
        MO_CCB_0698_BJHZ = 43,
		MO_CASH = 99,
		MO_BALANCE = 100;

	public static $MONEY_OUT_PAID_TYPES = array(
		//self::MO_PRIVATE_CMBC_3829  => '民生私户-3829',
		//self::MO_PRIVATE_CMBC_9940  => '民生私户-9940',
		//self::MO_PRIVATE_SPDB_8336  => '浦发私户-8336',     //2017年不在使用
        self::MO_PRIVATE_CCB_5306   => '中信私户-5306',
		self::MO_PUBLIC_CMBC        => '民生公户-8567',
        //self::MO_GENERAL_BRCB       => '农商一般户-8850',
        self::MO_GENERAL_CCB        => '交通一般户1678',
        self::MO_GENERAL_CMB        => '招商银行一般户0907',
        self::MO_TJ_LZ_0701         => '天津乐赞-0701',
        self::MO_BASE_CMB_0501_CQ   => '招商基本户0501_渝',
        self::MO_PUBLIC_0201_CD     => '成都公户-0201',
        self::MO_CMB_0628_QD        => '青岛招商-0628',
        self::MO_CCB_0698_BJHZ      => '北京好住_建行_0698',
        
        //self::MO_CHEQUE             => '支票',
        self::MO_RESERVE_FUND       => '备用金',
		self::MO_CASH => '现金',                            //2017年不在使用
        self::MO_BALANCE => '余额',
	);

	const REMIND_MIN_AMOUNT = 200000;       //余额短信提醒值

    const SELLER_RATIO = 4;   //商家结算扣点%
    
    /**
     * 平台账号结账.
     * 
     * @var rebate 返点比率
     * @var order_scode 订单来源source-code
     * @var pay_coop_fee 是否支付第三方工人费用（运费，搬运费）
     */
    public static $Platform_Debits = array(
        '1001' => array('name'=>'抢工长', 'rebate'=>0.0505, 'pay_coop_fee'=>0, 'order_scode'=>Conf_Order::SOURCE_QIANG_GONG_ZHANG),
    );

    /**
     * 供应商余额体系
     */
    const AMOUNT_TYPE_SETTLEMENT = 1;
    const AMOUNT_TYPE_PREPAY = 2;

    private static $_AMOUNT_TYPE = array(
        self::AMOUNT_TYPE_SETTLEMENT => '结算单支付',
        self::AMOUNT_TYPE_PREPAY => '预付',
    );

    public static function getAmountType()
    {
        return self::$_AMOUNT_TYPE;
    }
}