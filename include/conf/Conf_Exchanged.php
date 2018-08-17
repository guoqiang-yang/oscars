<?php

/**
 * order 配置
 */
class Conf_Exchanged
{
	/**
	 * 订单状态:
	 */
	const
		EXCHANGED_STEP_NEW = 1,         //未审核
        EXCHANGED_STEP_SURE = 2,        //已审核
        EXCHANGED_STEP_FINISHED = 3;    //已完成

	/**
	 * @var array 付款方式
	 */
	public static $EXCHANGED_STEPS = array(
		self::EXCHANGED_STEP_NEW => '未审核',
		self::EXCHANGED_STEP_SURE => '已审核',
		self::EXCHANGED_STEP_FINISHED => '已完成',
	);

    /**
     * @var array 换货原因类别
     *
     *
     */
    public static $EXCHANGED_REASON_TYPE = array(
        1 => '库房问题',
        2 => '配送问题',
        3 => '客户问题',
        4 => '采购问题',
        5 => '客服问题',
        6 => '其他问题',
    );

    /**
     * @var array 换货原因，一级下表是换货原因类别
     *
     * @notice 和Conf_Refund::$REFUND_REASON_DETAIL id 对应
     */
    public static $EXCHANGED_REASON_DETAIL = array(
        1 => array(
            1 => array('name' => '库房错配', 'need_storage' => 0),
            3 => array('name' => '商品裁剪错误', 'need_storage' => 1),
        ),
        2 => array(
            2 => array('name' => '司机错送', 'need_storage' => 0),
            4 => array('name' => '运输损坏商品', 'need_storage' => 1),
        ),
        3 => array(
            2 => array('name' => '客户下错单', 'need_storage' => 1),
            3 => array('name' => '客户更改用料方案', 'need_storage' => 1),
            5 => array('name' => '业主嫌产品质量差', 'need_storage' => 1),
        ),
        4 => array(
            1 => array('name' => '临采商品图物不符', 'need_storage' => 1),
            3 => array('name' => '临采错配', 'need_storage' => 0),
            4 => array('name' => '产品质量问题', 'need_storage' => 1),
            5 => array('name' => '产品下差', 'need_storage' => 1),
            6 => array('name' => '产品不配套', 'need_storage' => 1),
            7 => array('name' => '产品有色差', 'need_storage' => 1),
            8 => array('name' => '未采到调换商品', 'need_storage' => 1),
        ),
        5 => array(
            1 => array('name' => '客服录错单', 'need_storage' => 1),
        ),
        6 => array(
            1 => array('name' => '无法核实', 'need_storage' => 1),
            2 => array('name' => '商品与实物不符', 'need_storage' => 1),
            3 => array('name' => '其他问题', 'need_storage' => 1),
        ),
    );



	public static function getExchangedStepName($step)
	{
		assert(isset(self::$EXCHANGED_STEPS[$step]));

		return self::$EXCHANGED_STEPS[$step];
	}

	public static function getExchangedStepNames()
	{
		return self::$EXCHANGED_STEPS;
	}

	//三种换货方式
	const EXCHANGED_TYPE_ALONE = 1,        //单独换货
          EXCHANGED_TYPE_NEXT_ORDER = 2;   //预约换货

    public static $EXCHANGED_TYPES = array(
        self::EXCHANGED_TYPE_ALONE => '单独换货',
        self::EXCHANGED_TYPE_NEXT_ORDER => '预约换货',
    );

    public static $VIRTUAL_PRODUCTS = array(
        '14436' => '客户运费',
    );

    //新建、编辑换货单，保存时，关联订单的退货时间和退货的虚拟商品及运费存放kvstore表中的键值前缀，后面拼接换货单的eid
    const REL_ORDER_INFO = 'exc_order_info';
}
