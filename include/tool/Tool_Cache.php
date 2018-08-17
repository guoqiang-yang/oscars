<?php

class Tool_Cache
{
	static $instanceCache = array();
	public static function getInstance($className)
	{
		if (!isset(self::$instanceCache[$className]))
		{
			self::$instanceCache[$className] = new $className;
		}
		return self::$instanceCache[$className];
	}

	static $phpCache = array();
	public static function set($module, $key, $value)
	{
		if (!isset(self::$phpCache[$module])) self::$phpCache[$module] = array();
		self::$phpCache[$module][$key] = $value;
	}
	public static function setBulk($module, $keyValues)
	{
		if (!isset(self::$phpCache[$module])) self::$phpCache[$module] = array();

		foreach ($keyValues as $key => $value)
		{
			self::$phpCache[$module][$key] = $value;
		}
	}
	public static function get($module, $key)
	{
		if (isset(self::$phpCache[$module][$key]))
		{
			return self::$phpCache[$module][$key];
		}
		return false;
	}
	public static function delete($module, $key)
	{
		if (isset(self::$phpCache[$module][$key]))
		{
			unset(self::$phpCache[$module][$key]);
		}
	}
	public static function setArr($module, $key, $value)
	{
		$value = json_encode($value);
		return self::set($module, $key, $value);
	}
	public static function getArr($module, $key)
	{
		$res = self::get($module, $key);
		return json_decode($res, true);
	}
}