<?php

/**
 * 订单操作日志log
 */
class Conf_Order_Action_Log
{
	//管理员操作类型
	const ACTION_NEW_ORDER = 1;
	const ACTION_DELETE_ORDER = 2;
	const ACTION_CANCEL_ORDER = 3;
	const ACTION_CHANGE_STEP = 4;
	const ACTION_CHANGE_FEE = 5;
	const ACTION_RECEIPT = 6;
	//const ACTION_ADD_REFUND = 7;
	const ACTION_CHG_SALER = 8;
	const ACTION_SET_PART_PAID = 9;
	const ACTION_PRE_ONLINEPAY_PRIVILEGE_MODIFY = 10;
	const ACTION_RESET_ORDER = 11;
	const ACTION_CHANGE_PRODUCTS = 12;
	const ACTION_CHANGE_INFO = 13;
	const ACTION_SET_PICKING_GROUP = 14;
	const ACTION_REFUND_AND_DELETE = 15;
	const ACTION_PRINT = 16;
	const ACTION_CREATE_REFUND_ORDER = 17;
	const ACTION_AGREE_REFUND_ORDER = 18;
	const ACTION_PUT_IN_REFUND_ORDER = 19;
	const ACTION_PAY_REFUND_ORDER = 20;
	const ACTION_ADMIN_CANCEL_ORDER = 21;
    const ACTION_GUARANTEED_ORDER = 22;
    const ACTION_DRIVER_ORDER = 23;
    const ACTION_AUDIT_REFUND_ORDER = 24;
	const ACTION_EDIT_COOPWORKER = 25;
	const ACTION_ORDER_ARRIVE = 26;
    const ACTION_CREATE_EXCHANGED_ORDER = 27;
    const ACTION_AUDIT_EXCHANGED_ORDER = 28;
    const ACTION_FINISHED_EXCHANGED_ORDER = 29;
    const ACTION_DELETE_EXCHANGED_ORDER = 30;
    const ACTION_CANCEL_EXCHANGED_ORDER = 31;
    const ACTION_CHANGE_CITY_ORDER = 32;
    const ACTION_CREATE_TRAPS_ORDER = 33;
    const ACTION_AUDIT_TRAPS_ORDER = 34;
    const ACTION_FINISHED_TRAPS_ORDER =35;
    const ACTION_DELETE_TRAPS_ORDER = 36;
    const ACTION_REBUT_REFUND_ORDER = 37;
    const ACTION_DELETE_REFUND_ORDER = 38;
    const ACTION_UPDATE_SHORTAGE = 39;
    const ACTION_REFRESH_FORCE = 40;
    const ACTION_SALE_PREFERENTIAL = 41;

	public static $ACTION_TYPE = array(
		self::ACTION_NEW_ORDER => '提交订单',
		self::ACTION_DELETE_ORDER => '删除订单',
		self::ACTION_CANCEL_ORDER => '取消订单',
		self::ACTION_CHANGE_STEP => '更新状态',
		self::ACTION_CHANGE_FEE => '更改费用',
		self::ACTION_RECEIPT => '收款',
		//self::ACTION_ADD_REFUND => '退货',
		self::ACTION_CHG_SALER => '修改销售',
		self::ACTION_SET_PART_PAID => '设置部分收款',
		self::ACTION_PRE_ONLINEPAY_PRIVILEGE_MODIFY => '提前支付优惠变动',
		self::ACTION_RESET_ORDER => '恢复订单',
		self::ACTION_CHANGE_PRODUCTS => '修改商品',
		self::ACTION_CHANGE_INFO => '修改信息',
		self::ACTION_SET_PICKING_GROUP => '设置拣货分组',
		self::ACTION_REFUND_AND_DELETE => '退款并删除订单',
		self::ACTION_PRINT => '打印订单',
		self::ACTION_CREATE_REFUND_ORDER => '生成退货单',
		self::ACTION_AGREE_REFUND_ORDER => '同意退货单',
		self::ACTION_PUT_IN_REFUND_ORDER => '退货单入库',
		self::ACTION_PAY_REFUND_ORDER => '退货单退款',
		self::ACTION_ADMIN_CANCEL_ORDER => '管理员取消订单',
        self::ACTION_GUARANTEED_ORDER => '订单担保',
        self::ACTION_DRIVER_ORDER => '司机订单',
        self::ACTION_AUDIT_REFUND_ORDER => '退货单终审&提交财务',
		self::ACTION_EDIT_COOPWORKER => '编辑司机/搬运工',
		self::ACTION_ORDER_ARRIVE => '订单送达',
        self::ACTION_CREATE_EXCHANGED_ORDER => '生成换货单',
        self::ACTION_AUDIT_EXCHANGED_ORDER => '审核换货单',
        self::ACTION_FINISHED_EXCHANGED_ORDER => '完成换货单',
        self::ACTION_DELETE_EXCHANGED_ORDER => '删除换货单',
        self::ACTION_CANCEL_EXCHANGED_ORDER => '取消换货单',
        self::ACTION_CHANGE_CITY_ORDER => '切换城市',
        self::ACTION_CREATE_TRAPS_ORDER => '生成补漏单',
        self::ACTION_AUDIT_TRAPS_ORDER => '审核补漏单',
        self::ACTION_FINISHED_TRAPS_ORDER => '完成补漏单',
        self::ACTION_DELETE_TRAPS_ORDER => '删除补漏单',
        self::ACTION_REBUT_REFUND_ORDER => '驳回退货单',
        self::ACTION_DELETE_TRAPS_ORDER => '删除退货单',
        self::ACTION_UPDATE_SHORTAGE => '更新缺货',
        self::ACTION_REFRESH_FORCE => '强制刷新',
        self::ACTION_SALE_PREFERENTIAL => '销售优惠调整',
	);

	//具体操作类型描述
	public static $ACTION_DESC = array(
		self::ACTION_NEW_ORDER => '金额{price}，搬运费{carryFee}，运费{freight}，优惠{privilege}，配送日期：{delivery_date}',
		self::ACTION_DELETE_ORDER => '',
		self::ACTION_CANCEL_ORDER => '',
		self::ACTION_CHANGE_STEP => '更新为：{newStep}',
		self::ACTION_CHANGE_FEE => '',
		self::ACTION_RECEIPT => '【{type}】应收：{needToPay}，实收：{realAmount}，抹零：{change}，坏账：{badLoans}，服务费：{serviceFee}',
		//self::ACTION_ADD_REFUND => '退款单{rid}，退款金额{amount}',
		self::ACTION_CHG_SALER => '[From]: {fromSalerId} [To]: {toSalerId}',
		self::ACTION_SET_PART_PAID => '',
		self::ACTION_PRE_ONLINEPAY_PRIVILEGE_MODIFY => '由{old}变为{new}',
		self::ACTION_RESET_ORDER => '恢复订单',
		self::ACTION_CHANGE_PRODUCTS => '',
		self::ACTION_CHANGE_INFO => '',
		self::ACTION_SET_PICKING_GROUP => '修改拣货组：仓库#{wid} 由 {from} 修改为 {to} ',
		self::ACTION_SET_PICKING_GROUP => '',
		self::ACTION_CREATE_REFUND_ORDER => '仓库#{wid}，少退金额{adjust}元，商品{products}',
		self::ACTION_PAY_REFUND_ORDER => '是否退入余额{balance}，金额{adjust}元',
        self::ACTION_GUARANTEED_ORDER => '{guaranteed_type}，备注：{note}',
        self::ACTION_DRIVER_ORDER => '司机ID：{id}，姓名：{name}，操作类型：{type_desc}',
        self::ACTION_AUDIT_REFUND_ORDER => '退货单ID{rid}，报损金额{damaged_price}',
		self::ACTION_EDIT_COOPWORKER => '{action}，角色{role}，姓名{name}，价格{price}，原因：{reason}',
		self::ACTION_ORDER_ARRIVE => '订单:{oid}，由{name}变更为{action}',
        self::ACTION_CREATE_EXCHANGED_ORDER => '换货单ID{eid}，仓库#{wid}，退货金额{adjust}元，退货商品{rel_products}，换货金额{adjust2}元，换货商品{exchanged_products}',
        self::ACTION_AUDIT_EXCHANGED_ORDER => '换货单ID{eid}，',
        self::ACTION_FINISHED_EXCHANGED_ORDER => '换货单ID{eid}，',
        self::ACTION_DELETE_EXCHANGED_ORDER => '换货单ID{eid}，',
        self::ACTION_CANCEL_EXCHANGED_ORDER => '换货单ID{eid}，',
        self::ACTION_CHANGE_CITY_ORDER => '订单从城市id:{old_city} 切换到城市id:{new_city}',
        self::ACTION_CREATE_TRAPS_ORDER => '补漏单ID{tid}，仓库#{wid}，补漏金额{adjust}元，补漏商品{traps_products}',
        self::ACTION_AUDIT_TRAPS_ORDER => '补漏单ID{tid}',
        self::ACTION_FINISHED_TRAPS_ORDER => '补漏单ID{tid}',
        self::ACTION_DELETE_TRAPS_ORDER => '补漏单ID{tid}',
        self::ACTION_REBUT_REFUND_ORDER => '退货单ID{rid}',
        self::ACTION_DELETE_REFUND_ORDER => '退货单ID{rid}',
        self::ACTION_UPDATE_SHORTAGE => '商品 {sid: {sid}}， 缺货数由{from_num}更新成{to_num}',
        self::ACTION_REFRESH_FORCE => '商品 {sid: {sid}}}',
        self::ACTION_SALE_PREFERENTIAL => '销售优惠金额 {price}元，发放人 {name}({suid})',
    );
}
