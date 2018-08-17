<?php
/**
 * 单例模拟。
 * 只是为了避免生成很多重复对象，并不能正的阻止用户new的方式创建多个实例。
 */
class Tool_Singleton
{
	private static $instanceList = array();

	/**
	 * 获取类实例
	 */
	public static function getInstance($className)
	{
		if(isset(self::$instanceList[$className]))
		{
			return self::$instanceList[$className];
		}
		else
		{
			$instance = new $className;
			self::$instanceList[$className] = $instance;
			return $instance;
		}
	}

}