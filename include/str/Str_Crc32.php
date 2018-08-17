<?php
/*
 * str 转化成 int 用户生成分表ID
 */
class Str_Crc32
{
	public static function str2int($str)
	{
		return crc32(strtolower($str));
	}
}