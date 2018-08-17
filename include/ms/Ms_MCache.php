<?php

/**
 * MCache
 * @todo : (1)zip (2)value是数组对象时的处理
 */

class Ms_MCache
{
	private static $instances = array();
	private static $servantName = 'MCache';
	private static $jproxy;

	private $_prx;	//实际中间层访问句柄

	private function __construct($prx)
	{
		$this->_prx = $prx;
	}

	public static function getInstance($group = '')
	{
		if ( empty(self::$instances[$group]) )
		{
			if (empty(self::$jproxy))
			{
				self::$jproxy = new Ms_JProxy();
			}
			if (empty($group))
			{
				$servantName = self::$servantName;
			} else {
				$servantName = self::$servantName . "#" . $group;
			}
			$prx = self::$jproxy->createProxy($servantName);

			self::$instances[$group] = new Ms_MCache($prx);
		}

		return self::$instances[$group];
	}

	function set($key, $value, $expire = 0)
	{
		$para = array(
			'key' => strval($key),
			'value' => strval($value),
			);
		if ($expire > 0)
		{
			$para['expire'] = $expire;
		}
		$ret = $this->_prx->invoke('set', $para);
		return $ret['ok'];
	}

	function add($key, $value, $expire = 0)
	{
		$para = array(
			'key' => strval($key),
			'value' => strval($value),
			);
		if ($expire > 0)
		{
			$para['expire'] = $expire;
		}
		$ret = $this->_prx->invoke('add', $para);
		return $ret['ok'];
	}

	function replace($key, $value, $expire = 0)
	{
		$para = array(
			'key' => strval($key),
			'value' => strval($value),
			);
		if ($expire > 0)
		{
			$para['expire'] = $expire;
		}
		$ret = $this->_prx->invoke('replace', $para);
		return $ret['ok'];
	}

	function get($key)
	{
		$para = array(
			'key' => strval($key),
			);
		$ret = $this->_prx->invoke('get', $para);
		$ret = strval($ret['value']);
		if (strlen($ret) == 0)
		{
			$ret = false;
		}
		return $ret;
	}

	function getMulti($keys)
	{
		if (empty($keys))
		{
			return array();
		}

		$keys = array_map("strval", array_unique($keys));
		$len = count($keys);
		$tmpRet = array();
		for ($i = 0; $i < $len; $i+=500)
		{
			$subkey = array_slice($keys, $i, 500);
			$pars = array("keys" => $subkey);
			$ret = $this->_prx->invoke("getMulti", $pars);
			$tmpRet = array_merge($tmpRet, $ret['values']);
		}

		$ret = array();
		foreach ($tmpRet as $k => $v)
		{
			$v = strval($v);
			if (strlen($v))
			{
				$ret[$k] = $v;
			}
		}
		return $ret;
	}

	function delete($key)
	{
		$pars = array(
			"key" => $key,
		);
		$ret = $this->_prx->invoke("delete", $pars);
		return $ret['ok'];
	}

	function increment($key , $value)
	{
		$para = array(
			'key' => $key,
			'value' => $value,
			);
		$ret = $this->_prx->invoke('increment', $para);
		if ($ret["ok"])
		{
			return $ret["value"];
		}
		else
		{
			return false;
		}
	}

	function decrement($key , $value)
	{
		$para = array(
			'key' => $key,
			'value' => $value,
			);
		$ret = $this->_prx->invoke('decrement', $para);
		if ($ret["ok"])
		{
			return $ret["value"];
		}
		else
		{
			return false;
		}
	}

	/**
	 * 不存在则创建
	 */
	function incrementEx($key, $value = 1, $expire = 0)
	{
		$ret = $this->increment($key, $value);
		if ($ret !== false)
		{
			return $ret;
		}
		$ret = $this->add($key, $value, $expire);
		if ($ret !== false)
		{
			return $value;
		}
		return $this->increment($key, $value);
	}

	function setObj($key, $value, $expire = 0)
	{
		return $this->set($key,Tool_Vbs::encode($value),$expire);
	}

	function getObj($key)
	{
		$value = $this->get($key);
		return Tool_Vbs::decode($value);
	}

	function getMultiObj($keys)
	{
		$values = $this->getMulti($keys);
		foreach($values as $key=>$value)
		{
			$values[$key] = Tool_Vbs::decode($value);
		}
		return $values;
	}
}
?>