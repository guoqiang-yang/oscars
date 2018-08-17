<?php
/**
 * 全局变量
 */
class Data_Global
{
    private static $_DATA;

    public static function set($key, $val)
    {
        self::$_DATA[$key] = $val;
    }

    public static function get($key)
    {
        return self::$_DATA[$key];
    }
}