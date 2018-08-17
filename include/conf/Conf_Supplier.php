<?php
/**
 * 库房配置
 */
class Conf_Supplier
{
	CONST TYPE_FACTORY = 1, //厂家
		TYPE_1 = 2,  //一批
		TYPE_2 = 3,  //一批
		TYPE_OTHER = 4;  //其他

    private static $TYPES = array(
        self::TYPE_FACTORY => '厂家',
        self::TYPE_1 => '一批',
        self::TYPE_2 => '二批',
        self::TYPE_OTHER => '其他',
    );

	public static function getTypes()
	{
		return self::$TYPES;
	}

	public static function getTypeName($type)
	{
		return isset(self::$TYPES[$type]) ? self::$TYPES[$type]:'' ;
	}
}
