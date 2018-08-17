<?php

/**
 * app版本管理
 */
class Conf_App_Version
{
	const APP_SHOP = 1;     //商城
	const APP_DRIVER = 2;   //司机端
	const APP_WMS = 3;      //wms
    const APP_CRM = 4;      //crm

	const DEV_ENV = 1;      //正式环境
	const DEV_TEST = 2;     //测试环境


	public static $CATE_LIST = array(
		self::APP_SHOP => '商城',
		self::APP_DRIVER => '司机端',
		self::APP_WMS => 'wms',
        self::APP_CRM => 'crm',
	);

	public static $DEV_LIST = array(
		self::DEV_ENV => '正式环境',
		self::DEV_TEST => '测试环境',
	);
}
