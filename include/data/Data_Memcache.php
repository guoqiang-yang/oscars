<?php

/**
 * Memcache CURD
 */
class Data_Memcache
{
	private static $_prx = NULL;
	private static $_mc = NULL;

	private function __construct()
	{
	}

	public static function getInstance()
	{
		if (empty(self::$_prx))
		{
			self::$_prx = new Data_Memcache();
		}
		return self::$_prx;
	}

	private function _checkConnection()
	{
		if (defined('NO_MEMCACHE') && NO_MEMCACHE)
		{
			return;
		}
		if (empty(self::$_mc))
		{
			self::$_mc = new Memcache();
			self::$_mc->connect(MEMCACHE_HOST, MEMCACHE_PORT);
		}
	}

	/**
	 * set
	 * @return true/false
	 * @note $expire = 0, never expire.
	 * $expire should not exceed 2592000 (30 days).
	 */
	public function set($key, $value, $expire = 0, $compress = 0)
	{
		if (defined('NO_MEMCACHE') && NO_MEMCACHE)
		{
			return true;
		}
		$this->_checkConnection();
		$ret = self::$_mc->set($key, $value, $compress == 0 ? 0 : MEMCACHE_COMPRESSED, $expire);
		return $ret;
	}

	/**
	 * add
	 * @note 如果$key已存在返回false，其它与set相同
	 */
	public function add($key, $value, $expire = 0, $compress = 0)
	{
		if (defined('NO_MEMCACHE') && NO_MEMCACHE)
		{
			return true;
		}
		$this->_checkConnection();

		return self::$_mc->add($key, $value, $compress == 0 ? 0 : MEMCACHE_COMPRESSED, $expire);
	}

	/**
	 * increment
	 * @param $value 应是整数
	 * @return 新值/false
	 */
	public function increment($key, $value)
	{
		if (defined('NO_MEMCACHE') && NO_MEMCACHE)
		{
			return true;
		}
		$this->_checkConnection();

		return self::$_mc->increment($key, intval($value));
	}

	/**
	 * decrement
	 * @param $value 应是整数
	 * @return 新值/false
	 */
	public function decrement($key, $value)
	{
		if (defined('NO_MEMCACHE') && NO_MEMCACHE)
		{
			return true;
		}
		$this->_checkConnection();

		return self::$_mc->decrement($key, intval($value));
	}

	/**
	 * get
	 * @return value/false
	 */
	public function get($key)
	{
		if (defined('NO_MEMCACHE') && NO_MEMCACHE)
		{
			return true;
		}
		$this->_checkConnection();

		return self::$_mc->get($key);
	}

	/**
	 * gets
	 * @return array
	 */
	public function gets($keys)
	{
		if (empty($keys))
		{
			return array();
		}
		if (defined('NO_MEMCACHE') && NO_MEMCACHE)
		{
			return true;
		}
		$this->_checkConnection();

		$keys = array_map("strval", array_unique($keys));
		$len = count($keys);
		$ret = array();
		for ($i = 0; $i < $len; $i += 500)
		{
			$subkey = array_slice($keys, $i, 500);
			$subret = self::$_mc->get($subkey);
			foreach ($subret as $key => $value)
			{
				$ret[$key] = $value;
			}
		}
		return $ret;
	}

	/**
	 * replace
	 */
	public function replace($key, $value, $expire = 0)
	{
		if (defined('NO_MEMCACHE') && NO_MEMCACHE)
		{
			return true;
		}
		$this->_checkConnection();

		if ($expire > 0)
		{
			return self::$_mc->replace($key, $value, $compress == 0 ? 0 : MEMCACHE_COMPRESSED, $expire);
		} else
		{
			return self::$_mc->replace($key, $value, $compress == 0 ? 0 : MEMCACHE_COMPRESSED);
		}
	}

	/**
	 * delete
	 * @return true/false
	 */
	public function delete($key, $timeout = 0)
	{
		if (defined('NO_MEMCACHE') && NO_MEMCACHE)
		{
			return true;
		}
		$this->_checkConnection();

		return self::$_mc->delete($key, $timeout);
	}
}
