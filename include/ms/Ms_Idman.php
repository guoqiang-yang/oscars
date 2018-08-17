<?php

class Ms_Idman
{
	private static $instances = array();
	private static $servantName = 'IdMan';
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

			self::$instances[$group] = new Ms_Idman($prx);
		}

		return self::$instances[$group];
	}

	private function _doit($op, $kind)
	{
		$r = $this->_prx->invoke($op, array("kind"=>$kind));
		return $r['id'];
	}

	public function newId($kind)
	{
		return $this->_doit("newId", $kind);
	}

	public function newTimeId($kind)
	{
		return $this->_doit("newTimeId", $kind);
	}

	public function newStringLongId($kind)
	{
		return strval($this->_doit("newId", $kind));
	}

	public function lastId($kind)
	{
		return $this->_doit("lastId", $kind);
	}
}
?>