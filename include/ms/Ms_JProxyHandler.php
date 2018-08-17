<?php

/**
 * JProxyHandler 对Proxy对象的包装，以便统一处理
 */

class Ms_JProxyHandler
{
	private $proxy;

	public function __construct($prx)
	{
		assert(!empty($prx));
		$this->proxy = $prx;
	}

	public function invoke($api, $param)
	{
		try
		{
			return $this->proxy->invoke($api, $param);
		}
		catch(Exception $ex)
		{
			Tool_Log::debug("JProxy_exception", $_SERVER["PHP_SELF"]." ".$ex->getCode()." ".$ex->getMessage());
			throw new Ms_JProxyException($ex->getMessage(), $ex->getCode());
		}
	}

}
?>