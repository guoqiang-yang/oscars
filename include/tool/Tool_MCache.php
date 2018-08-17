<?php

class Tool_MCache
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
	public static function set($module, $key, $value, $expire=0)
	{
		if (!isset(self::$phpCache[$module])) self::$phpCache[$module] = array();
		self::$phpCache[$module][$key] = $value;

		$mc = Data_Memcache::getInstance();
		$mc->set($module . '_' . $key, $value, $expire);
	}
	public static function setBulk($module, $keyValues, $expire=0)
	{
		if (!isset(self::$phpCache[$module])) self::$phpCache[$module] = array();

		foreach ($keyValues as $key => $value)
		{
			self::$phpCache[$module][$key] = $value;

			$mc = Data_Memcache::getInstance();
			$mc->set($module . '_' . $key, $value, $expire);
		}
	}
	public static function get($module, $key)
	{
		if (isset(self::$phpCache[$module][$key]))
		{
			return self::$phpCache[$module][$key];
		}

		$mc = Data_Memcache::getInstance();
		$val = $mc->get($module . '_' . $key);
		self::$phpCache[$module][$key] = $val;
		return $val;
	}
	public static function delete($module, $key)
	{
		if (isset(self::$phpCache[$module][$key]))
		{
			unset(self::$phpCache[$module][$key]);
		}

		$mc = Data_Memcache::getInstance();
		$mc->delete($module . '_' . $key);
	}
	public static function setArr($module, $key, $value, $expire=0)
	{
		$value = json_encode($value);
		return self::set($module, $key, $value, $expire);
	}
	public static function getArr($module, $key)
	{
		$res = self::get($module, $key);
		return json_decode($res, true);
	}
}