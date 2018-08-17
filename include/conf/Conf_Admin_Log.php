<?php
/**
 * 管理员操作日志log
 */
class Conf_Admin_Log
{
    
    ////////////////////////////// 后台操作日志 - 新 //////////////////////////////
    
    // 日志对象类型
    const OBJTYPE_CUSTOMER              = 1;    //客户
    const OBJTYPE_BUSINESS              = 2;    //企业用户
    const OBJTYPE_COUPON                = 3;    //优惠券
    const OBJTYPE_PRODUCT               = 4;    //商品
    const OBJTYPE_SKU                   = 5;    //sku
    const OBJTYPE_COMMUNITY             = 6;    //小区
    const OBJTYPE_SUPPLIER              = 7;    //供应商
    const OBJTYPE_IN_ORDER              = 8;    //采购单
    const OBJTYPE_STOCK_IN              = 9;    //入库单
    const OBJTYPE_STOCK_SHIFT           = 10;   //调拨单
    const OBJTYPE_CWORKER_STATEMENT     = 11;   //第三方工人结算单
    const OBJTYPE_STOCK_IN_REFUND       = 12;   //入库退货单
    const OBJTYPE_PROMOTION_ACTIVITY    = 13;   //促销活动
    const OBJTYPE_SATOCK_SHIFT          = 14;   //调拔单
    const OBJTYPE_SALER_PRIVILEGE       = 15;   //销售优惠

    //日志的操作类型及显示格式
    private static $Action_Formats = array(
        self::OBJTYPE_CUSTOMER => array(
            1 => array('name'=>'添加', 'format'=>'信息：“{name}(id：{id})”'),
        ),
        
        self::OBJTYPE_BUSINESS => array(
            1 => array('name' => '添加企业客户', 'format' => '添加企业客户：(bid:{bid}),详情：json-{json}'),
            2 => array('name' => '编辑企业客户', 'format' => '编辑企业客户：(bid:{bid}),详情：json-{json}'),
            3 => array('name' => '绑定企业客户', 'format' => '绑定企业客户：(bid:{bid}),详情：json-{json}'),
            4 => array('name' => '解绑企业客户', 'format' => '解绑企业客户：(bid:{bid}),详情：json-{json}'),
        ),
        
        self::OBJTYPE_COUPON => array(
            1 => array('name'=>'生成优惠券', 'format'=>'生成优惠券：(id:{id}),修改内容：json-{json}'),
            2 => array('name'=>'修改优惠券', 'format'=>'修改优惠券：(id:{id}),修改内容：json-{json}'),
        ),
        
        self::OBJTYPE_PRODUCT => array(
            1 => array('name'=>'添加商品', 'format'=>"添加商品：(pid:{pid})"),
            2 => array('name'=>'修改商品信息', 'format'=>"修改商品：(pid:{pid}）, 修改内容：{changed}"),
            3 => array('name'=>'组合售卖', 'format'=>"组合售卖：(pid:{pid})"),
            4 => array('name'=>'整转零售', 'format'=>"整转零售：(pid:{pid})"),
        ),
        
        self::OBJTYPE_SKU => array(
            1 => array('name'=>'改采购价', 'format'=>"修改了商品：(sid:{sid})，修改内容：价格由{from_price}元修改为{to_price}元。"),
            2 => array('name'=>'添加sku', 'format'=>"添加sku(sid:{sid})"),
            3 => array('name'=>'关联sku', 'format'=>"关联sku(sid:{sid})"),
            4 => array('name'=>'删除关联sku', 'format'=>"删除关联sku(sid:{sid})"),
            5 => array('name'=>'添加sku并关联', 'format'=>"关联sku：{rel_sid}"),
            6 => array('name'=>'修改sku', 'format'=>"修改sku：(sid:{sid}）, 修改内容：{changed}"),
        ),
        
        self::OBJTYPE_COMMUNITY => array(
            1 => array('name'=>'改小区距离', 'format'=>"修改了小区距离：(cmid:{cmid}),修改内容由{from_distance}公里修改为{to_distance}公里"),
        ),
        
        self::OBJTYPE_SUPPLIER => array(
            1 => array('name' => '供应商修改成本', 'format' => "修改成本：(sid:{sid})，修改价格：价格由{from_price}元修改为{to_price}元"),
        ),
        
        self::OBJTYPE_IN_ORDER => array(
            1 => array('name'=>'生成采购单', 'format'=>"生成采购单：(oid:{oid})"),
            2 => array('name'=>'删除采购单', 'format'=>"删除采购单：(oid:{oid})"),
            3 => array('name'=>'入库', 'format'=>"入库：入库单(id:{id})"),
            4 => array('name'=>'删除入库单', 'format'=>"删除入库单：(id:{id})"),
            5 => array('name'=>'上架', 'format'=>"上架：入库单(id:{id})"),
            6 => array('name'=>'生成退货单', 'format'=>"生成退货单：(srid:{srid})，入库单：(id:{id})"),
            7 => array('name'=>'确认退货单', 'format'=>"确认退货单：(srid:{srid})，入库单：(id:{id})"),
            8 => array('name'=>'修改采购商品', 'format'=>"修改采购单商品(sid:{sid})，修改内容：价格由{from_price}元修改为{to_price}元，修改内容：采购数量由{from_num}修改为{to_num}。"),
            9 => array('name'=>'删除采购商品', 'format'=>"删除采购商品：(sid:{sid})"),
            10 => array('name'=>'删除入库商品', 'format'=>"删除入库商品：(sid:{sid})，入库单(id:{id})"),
            11 => array('name'=>'审核', 'format'=>"{desc}"),
        ),

        self::OBJTYPE_STOCK_IN => array(),
        
        self::OBJTYPE_STOCK_SHIFT => array(),
        
        self::OBJTYPE_CWORKER_STATEMENT => array(),

        self::OBJTYPE_STOCK_IN_REFUND => array(
            1 => array('name'=>'确认退货单', 'format'=>"确认退货单：(srid:{srid})，入库单：(id:{id})"),
        ),
        self::OBJTYPE_PROMOTION_ACTIVITY => array(
            1 => array('name' => '生成活动', 'format' => '生成活动：(id:{id}),修改内容：json-{json}'),
            2 => array('name' => '修改活动', 'format' => '修改活动：(id:{id}),修改内容：json-{json}'),
            3 => array('name' => '上线活动', 'format' => '上线活动：(id:{id}),上线时间：{ctime}'),
            4 => array('name' => '下线活动', 'format' => '下线活动：(id:{id}),下线时间：{ctime}'),
            5 => array('name' => '删除活动', 'format' => '删除活动：(id:{id}),删除时间：{ctime}'),
        ),
        self::OBJTYPE_SATOCK_SHIFT => array(
            1 => array('name' => '创建调拔单', 'format' => '生成调拔单：(id:{id})，修改内容：json-{json}'),
            2 => array('name' => '修改调拔单', 'format' => '修改调拔单：(id:{id})，修改内容：json-{json}'),
            3 => array('name' => '发起申请', 'format' => '发起申请：(id:{id})'),
            4 => array('name' => '驳回', 'format' => '驳回调拔单：(id:{id})，原因：{reason}'),
            5 => array('name' => '出库', 'format' => '调拔单出库：(id:{id})'),
            6 => array('name' => '入库', 'format' => '调拔单入库：(id:{id})'),
            7 => array('name' => '处理差异', 'format' => '调拔单处理差异：类型：{type}，数量：{num}，原因：{note}'),
            8 => array('name' => '上架', 'format' => '上架'),
        ),
        self::OBJTYPE_SALER_PRIVILEGE => array(
            1 => array('name' => '修改销售优惠', 'format' => '将 {name} 的优惠金额由 {old_amount} 元调整至 {amount} 元，调整月份：{month}'),
        )
    );

    /**
     * 通过对象类型获取日志格式.
     */
    public static function getFormatByObjType($objType)
    {
        return array_key_exists($objType, self::$Action_Formats)? self::$Action_Formats[$objType]: array();
    }
    
    /**
     * 是否定义了对象类型.
     */
    public static function hasObjType($objType)
    {
        return array_key_exists($objType, self::$Action_Formats);
    }

    /**
     * 是否定义的操作类型.
     */
    public static function hasActionType($objTye, $actionType)
    {
        return array_key_exists($objTye, self::$Action_Formats)
                && array_key_exists($actionType, self::$Action_Formats[$objTye]);
    }
    
    ///////////////////////////// 后台操作日志 - 旧 ///////////////////////////////
    
	//管理员操作类型
	public static $ACTION_ADD_CUSTOMER = 1;
	public static $ACTION_CHANGE_SUID = 2;
	public static $ACTION_ADD_PRODUCT = 3;
	public static $ACTION_UPDATE_PRODUCT = 4;
    public static $ACTION_RESET_PASS = 5;
    public static $ACTION_ADD_SKU = 6;
    public static $ACTION_UPDATE_SKU = 7;
    public static $ACTION_CHG_CUSTOMER_SALER = 8;
    public static $ACTION_CHG_CUSTOMER_STATUS = 9;
    public static $ACTION_SEND_VIP_COUPON = 10;

	public static $ACTION_TYPE = array(
		1 => '添加客户',
		2 => '更改客户所属销售',
		3 => '添加商品',
		4 => '修改商品',
        5 => '重置密码',
        6 => '添加sku',
        7 => '修改sku',
        8 => '更改客户销售人员',
        9 => '修改客户状态',
		10 => '发放vip现金券',
	);

	//具体操作类型描述
	public static $ACTION_DESC = array(
		1 => '添加了用户“{name}(id：{id})”',
		2 => '更改了手机号为{mobiles}的客户所属销售{num}为{sname}(id:{suid})',
		3 => '添加了商品：{name}(id:{id})',
		4 => '修改了商品：{name}(id:{id})，修改内容：{changed}',
        5 => '重置了用户的密码(Uid:{uid})',
        6 => '添加了sku：{name}(id:{id})',
        7 => '修改了sku：{name}(id:{id})，修改内容：{changed}',
        8 => '更改了客户：{name}(id:{cid})，[{desc}]专员，From suid1:{suid1} To suid2:{suid2}',
        9 => '客户状态变更：{customer_info}，状态：【{fr_status}】 => 【{to_status}】',
		10 => '给用户（{cid}）发放{num}张(ID:{coupon_id})VIP现金券',
	);
}
