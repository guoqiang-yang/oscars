<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 16/6/6
 * Time: 17:39
 */
class Conf_Qrcode
{
	const QRCODE_TYPE_CATE = 1;     //品类码
	const QRCODE_TYPE_SINGLE = 2;   //单品码
	const QRCODE_TYPE_THIRD_PARTY = 3;  //第三方码

	public static $QRCODE_TYPE = array(
		self::QRCODE_TYPE_CATE => '品类码',
		self::QRCODE_TYPE_SINGLE => '单品码',
		self::QRCODE_TYPE_THIRD_PARTY => '第三方条码',
	);

	//订单类型
	const ORDER_TYPE_NONE = 999;        //不是订单-sku品类码
	const ORDER_TYPE_IN_ORDER = 001;    //采购单


	//订单号
	const ORDER_ID_NONE = '0000';       //不是订单-sku品类码
}