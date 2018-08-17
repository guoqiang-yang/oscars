<?php

/**
 * order 配置
 */
class Conf_Refund
{
	/**
	 * 订单状态:
	 */
	const
		REFUND_STEP_NEW = 1,         //未审核
		REFUND_STEP_SURE = 2,        //未入库
		REFUND_STEP_IN_STOCK = 3,    //已入库
		REFUND_STEP_PAID = 4,        //已付款
		REFUND_STEP_PART_SHELVED = 5,//部分上架
		REFUND_STEP_SHELVED = 6;     //已上架

    const REFUND_REJECTED = -1;

	// 付款状态
	const UN_PAID = 0,          //未退款
		HAD_PAID = 1,           //已退款
        HAD_AUDIT = 2;          //已审核，审核才能退款

	/**
	 * @var array 付款方式
	 */
	public static $REFUND_STEPS = array(
		self::REFUND_STEP_NEW => '未审批',
		self::REFUND_STEP_SURE => '未入库',
		self::REFUND_STEP_IN_STOCK => '已入库',
		self::REFUND_STEP_PAID => '已退款',
		self::REFUND_STEP_PART_SHELVED => '部分上架',
		self::REFUND_STEP_SHELVED => '已上架',
	);

    public static $REFUND_STEP_TITLE = array(
        self::REFUND_REJECTED => '审核未通过',
        self::REFUND_STEP_NEW => '审核中',
        self::REFUND_STEP_SURE => '正在取货',
        self::REFUND_STEP_IN_STOCK => '正在退款',
        self::REFUND_STEP_PAID => '已退款',
    );

    public static $REFUND_STEP_DESC = array(
        self::REFUND_REJECTED => '您的退货申请审核未通过，感谢您的支持与理解！',
        self::REFUND_STEP_NEW => '您的申请已提交成功，服务人员正在审核，请耐心等待。',
        self::REFUND_STEP_SURE => '您的申请已审核通过，我们已安排取货人员进行取货，请注意接听电话。',
        self::REFUND_STEP_IN_STOCK => '您的退货商品已通过验收，财务人员正在为您办理退款。',
        self::REFUND_STEP_PAID => '您的退款已退至您的好材账户中，请注意查收。',
    );

	public static $REFUND_REASON = array(
		1 => '客户买多了',
		2 => '客户下错单',
		3 => '客服下错单',
		4 => '库房发错货',
		5 => '商品有质量问题',
		6 => '商品与用户所需商品不符',
		99 => '其他',
	);


    /**
     * @var array 退货原因类别
     *
     *
     */
    public static $REFUND_REASON_TYPE = array(
        1 => '库房问题',
        2 => '配送问题',
        3 => '客户问题',
        4 => '采购问题',
        5 => '客服问题',
        6 => '其他问题',
    );

    /**
     * @var array 退货原因，一级下表是退货原因类别
     *
     *
     */
    public static $REFUND_REASON_DETAIL = array(
        1 => array(
            1 => '库房错配',
            2 => '库房漏配',
            3 => '商品裁剪错误',
        ),
        2 => array(
            1 => '司机漏送',
            2 => '司机错送',
            3 => '延时配送',
            4 => '运输损坏商品',
            5 => '运输商品丢失',
        ),
        3 => array(
            1 => '客户买多了',
            2 => '客户下错单',
            3 => '客户更改用料方案',
            4 => '客户现场没人收货',
            5 => '业主嫌产品质量差',
            6 => '客户觉得价格贵',
            7 => '客户临时更改配送时间',
            8 => '小区不让进',
            9 => '客户下重单',
            10 => '客户临时修改商品',
        ),
        4 => array(
            1 => '临采商品图物不符',
            2 => '临采漏配',
            3 => '临采错配',
            4 => '产品质量问题',
            5 => '产品下差',
            6 => '产品不配套',
            7 => '产品有色差',
            8 => '临采未采到',
            9 => '库房断货',
        ),
        5 => array(
            1 => '客服录错单',
            2 => '客服核对地址错误',
            3 => '客服下重单',
        ),
        6 => array(
            1 => '无法核实',
            2 => '商品与实物不符',
            3 => '其他问题',
        ),
    );



	public static function getRefundStepName($step)
	{
		assert(isset(self::$REFUND_STEPS[$step]));

		return self::$REFUND_STEPS[$step];
	}

	public static function getRefundStepNames()
	{
		return self::$REFUND_STEPS;
	}

	//三种退货方式
	const REFUND_TYPE_IMMEDIATELY = 1,      //现场退货
          REFUND_TYPE_ALONE = 2,            //单独退货
          REFUND_TYPE_NEXT_ORDER = 3,       //预约退货
          REFUND_TYPE_VIRTUAL = 4;          //空退

    public static $REFUND_TYPES = array(
        self::REFUND_TYPE_IMMEDIATELY   => '现场退货',
        self::REFUND_TYPE_ALONE         => '单独退货',
        self::REFUND_TYPE_NEXT_ORDER    => '预约退货',
        self::REFUND_TYPE_VIRTUAL       => '空退',
    );

    const REFUND_VIRTUAL_FREIGHT = 14436,
          REFUND_VIRTUAL_CARRY_FEE = 14552;
    public static $VIRTUAL_PRODUCTS = array(
        self::REFUND_VIRTUAL_FREIGHT => '客户运费',
        self::REFUND_VIRTUAL_CARRY_FEE => '搬运费'
    );

    //新建、编辑退货单，保存时，关联订单的退货时间和退货的虚拟商品及运费存放kvstore表中的键值前缀，后面拼接退货单的rid
    const REL_ORDER_INFO = 'rel_order_info';

    const REFUND_REL_TYPE_ORDER = 1, //订单
        REFUND_REL_TYPE_EXCHANGED = 2; //换货单
    public static $REFUND_REL_TYPE_DESC = array(
        self::REFUND_REL_TYPE_ORDER => '订单',
        self::REFUND_REL_TYPE_EXCHANGED => '换货单'
    );
}
