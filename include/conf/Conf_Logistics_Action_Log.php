<?php

/**
 * 订单操作日志log
 */
class Conf_Logistics_Action_Log
{
	//管理员操作类型
	const ACTION_CHECK_IN = 1;
	const ACTION_ALLOC_ORDER = 2;
	const ACTION_ACCEPT_ORDER = 3;
	const ACTION_SEND_ORDER = 4;
	const ACTION_ARRIVE_ORDER = 5;
	const ACTION_BACK_ORDER = 6;
	const ACTION_REFUSE_ORDER = 7;
    const ACTION_RELEASE_ORDER_LINE = 8;
    const ACTION_CHG_CAR_MODEL = 9;
    const ACTION_CLEAR_QUEUE_STATUS = 10;
    const ACTION_CHG_ORDER = 11;
    const ACTION_DRIVER_ARRIVE_ORDER = 12;

	public static $ACTION_TYPE = array(
		self::ACTION_CHECK_IN => '签到',
		self::ACTION_ALLOC_ORDER => '分配线路',
		self::ACTION_ACCEPT_ORDER => '接受订单',
		self::ACTION_SEND_ORDER => '发车出库',
		self::ACTION_ARRIVE_ORDER => '订单送达',
		self::ACTION_BACK_ORDER => '回单',
		self::ACTION_REFUSE_ORDER => '拒单',
        self::ACTION_RELEASE_ORDER_LINE => '释放订单',
        self::ACTION_CHG_CAR_MODEL => '换车型',
        self::ACTION_CLEAR_QUEUE_STATUS => '清除队列状态',
        self::ACTION_CHG_ORDER => '改订单',
        self::ACTION_DRIVER_ARRIVE_ORDER => '司机订单送达',
	);

	//具体操作类型描述
	public static $ACTION_DESC = array(
		self::ACTION_CHECK_IN => '库房{wid}，车型{carModel}',
		self::ACTION_ALLOC_ORDER => '库房{wid}，车型{carModel}，线路id{lineId}，订单号{oid}',
		self::ACTION_ACCEPT_ORDER => '',
		self::ACTION_SEND_ORDER => '',
		self::ACTION_ARRIVE_ORDER => '',
		self::ACTION_BACK_ORDER => '',
		self::ACTION_BACK_ORDER => '接单超时，拒绝接单',
        self::ACTION_REFUSE_ORDER => '',
        self::ACTION_RELEASE_ORDER_LINE => '{reason}',
        self::ACTION_CHG_CAR_MODEL => '{reason}',
        self::ACTION_CLEAR_QUEUE_STATUS => '{reason}',
        self::ACTION_CHG_ORDER => '{reason}',
        self::ACTION_DRIVER_ARRIVE_ORDER => '{remark}',
	);
}
