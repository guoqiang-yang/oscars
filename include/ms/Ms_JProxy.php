<?php

/**
 * JProxy
 */

class Ms_JProxy
{
	private static $ctx = array();				//环境参数

	/**
	 * 通过JProxy获取中间层句柄
	 */
	public function createProxy($proxystr)
	{
		if (!isset(self::$ctx['CALLER']))
		{
			self::$ctx['CALLER'] = $_SERVER['PHP_SELF'].'+'.dechex(mt_rand());
		}
		if (strpos($proxystr, '@') === FALSE)
		{
			$proxystr .= '@tcp::9999 timeout=70000';
		}
		$prx = kxi_engine()->stringToProxy($proxystr);
		return new Ms_JProxyHandler($prx);
   }

	/**
	 * 设置环境参数(调用者)
	 */
	public function ctxCaller($caller="")
	{
		if ($caller == "")
		{
			$myip = $this->getCallerIp();
			$caller = sprintf("%s:%s:%08x", $_SERVER['PHP_SELF'], $myip, mt_rand());
		}
		self::$ctx["CALLER"] = $caller;
	}

	/**
	 * 获取环境参数(缓存)
	 */
	public function ctxCache($second)
	{
		if (!isset(self::$ctx["CALLER"]))
		{
			$this->ctxCaller();
		}
		$ctx = self::$ctx;
		$ctx['CACHE'] = strval($second);
		return $ctx;
	}

	/**
	 * 获取调用者ip
	 */
	function getCallerIp()
	{
		$myip = $_SERVER['SERVER_ADDR'];
		if ($myip == '' || $myip == "127.0.0.1")
		{
			$filename = DATA_PATH."/localip";
			if (is_file($filename))
			{
				$myip = trim(file_get_contents($filename));
			}
			else if($myip == '')
			{
				$myip = "unknown";
			}
		}
		return $myip;
	}

	function getCaller()
	{
		return self::$ctx['CALLER'];
	}
}
?>