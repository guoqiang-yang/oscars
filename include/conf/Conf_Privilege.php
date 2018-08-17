<?php

/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 16/2/18
 * Time: 14:35
 */
class Conf_Privilege
{
	//优惠券优惠
	public static $TYPE_COUPON = 1;
	//微信商城下单
	public static $TYPE_PLACE_ORDER = 2;
	//在线支付 (微信商城在线支付：微信支付,支付宝支付)
	public static $TYPE_ONLINE_PAY = 3;
	//提前下单
	public static $TYPE_PRE_ORDER = 4;
	//错峰送货 (如果收货时间在上午11点之后)
	public static $TYPE_DELIVERY = 5;
	//提前支付 (货到前支付，不包括预付款)
	public static $TYPE_PRE_PAY = 6;
	//特殊优惠
	public static $TYPE_SPECIAL = 7;
	//vip现金券
	public static $TYPE_COUPON_VIP = 8;
	//首单优惠
	public static $TYPE_FIRST_ORDER = 9;
	//首次在线支付
	public static $TYPE_FIRST_ONLINE_PAY = 10;
	//2016-04-13,1毛钱买商品活动
	public static $TYPE_ACTIVITY_160413 = 11;
	//2016-05-12，满减
	public static $TYPE_MANJIAN_160512 = 12;
	//2016-09-16唤醒
	public static $TYPE_WAKEUP_160512 = 13;
	//2016国庆
	public static $TYPE_GUOQING = 14;
    //2016双11
    public static $TYPE_DOUBLE11 = 15;
    //用户折扣
    public static $TYPE_DISCOUNT = 16;
    //特殊商品优惠
    public static $TYPE_SPECIAL_GOODS = 17;
    //满赠
    public static $TYPE_GIFT = 18;
    //满特价
    public static $TYPE_SPECIAL_PRICE = 19;
    //运费券
    public static $TYPE_FREIGHT = 20;

	public static $TYPE_DESC = array(
		1 => '现金券',
//		2 => '微信商城下单',
//		3 => '在线支付',
//		4 => '提前下单',
//		5 => '错峰送货',
            6 => '提前支付',
		7 => '特殊优惠',
		8 => 'vip现金券',
            9 => '首单优惠',
            10 => '首次在线支付',
            11 => '促销活动',
		12 => '满减活动',
            13 => '唤醒活动',
            14 => '国庆活动',
            15 => '双11活动',
        16 => '折扣活动',
        17 => '特殊商品',
        18 => '满赠活动',
        19 => '满特价活动',
        20 => '运费券',
	);

    //优惠类型
    const PROMOTION_TYPE_MANJIAN = 1,
          PROMOTION_TYPE_COUPON = 2,
          PROMOTION_TYPE_VIP = 3,
          PROMOTION_TYPE_SPECIAL = 4,
          PROMOTION_TYPE_DISCOUNT = 5,
          PROMOTION_TYPE_GIFT = 6,
          PROMOTION_TYPE_PRICE = 7;

    //优惠类型描述

    public static $PROMOTION_TYPE_DESC = array(
        self::PROMOTION_TYPE_MANJIAN => '满减活动',
        self::PROMOTION_TYPE_COUPON => '优惠券',
        self::PROMOTION_TYPE_VIP => '现金券',
        self::PROMOTION_TYPE_SPECIAL => '特殊优惠',
        self::PROMOTION_TYPE_DISCOUNT => '折扣活动',
        self::PROMOTION_TYPE_GIFT => '满赠活动',
        self::PROMOTION_TYPE_PRICE => '满特价活动',
    );

    //每月订单销售额：单位分，运用时注意
    public static $MONTHLY_SALE_AMOUNT = array(
        '201706' => 313736292,
        '201707' => 1388903127,
        '201708' => 1468195911,
    );

    public static function getActivity4Type()
    {
        return array(
            Conf_Privilege::PROMOTION_TYPE_MANJIAN => Conf_Privilege::$TYPE_MANJIAN_160512,
            Conf_Privilege::PROMOTION_TYPE_COUPON => Conf_Privilege::$TYPE_COUPON,
            Conf_Privilege::PROMOTION_TYPE_VIP => Conf_Privilege::$TYPE_COUPON_VIP,
            Conf_Privilege::PROMOTION_TYPE_DISCOUNT => Conf_Privilege::$TYPE_DISCOUNT,
            Conf_Privilege::PROMOTION_TYPE_GIFT => Conf_Privilege::$TYPE_GIFT,
            Conf_Privilege::PROMOTION_TYPE_PRICE => Conf_Privilege::$TYPE_SPECIAL_PRICE,
        );
    }

    //销售优惠额度,订单优惠上限:单位%，运用时注意要除以100, 额度优惠上限：单位%%，运用时注意要除以10000
    public static $SALES_PRIVILEGE_RATIO = array(
        1118 => array('name' => '黄行', 'order' => 3, 'quota' => 3),
        1073 => array('name' => '王建伟(副总监)', 'order' => 3 , 'quota' => 18),
        1426 => array('name' => '奉铁刚(总监)', 'order' => 10, 'quota' => 29),
    );

    public static $SALES_PRIVILEGE = array(
        1073 => array('name' => '王建伟', 'order' => 20, 'amount' => 12500000),
        1068 => array('name' => '郭亚翔', 'order' => 20, 'amount' => 12500000),
        1118 => array('name' => '黄行',   'order' => 20, 'amount' => 4550000),
    );

    //新优惠开关
    const PROMOTION_NEW_STATUS = true;
    const PROMOTION_NEW_UPDATE_TIME = '2017-12-28 10:00:00';
}