<?php
/*
 * 检查是否含有敏感词
 */
class Str_Forbid
{
	private static $forbidDatas = array();

	private static function loadForbidData($filename)
	{
		if (empty(self::$forbidDatas[$filename]))
		{
			self::$forbidDatas[$filename] = array();

			$kvStore = new Tool_KVStore();
			$key = "forbid_".$filename;
			$content = $kvStore->get($key);

			if(empty($content))
			{
				$file = CORE_DATA_PATH."forbid/".$filename.".txt";
				if (!file_exists($file))
				{
					return ;
				}
				$content = file_get_contents($file);
			}
			$fn_list = explode("\n", $content);
			mb_internal_encoding(SYS_CHARSET);
			foreach($fn_list as $fn)
			{
				$fn = trim($fn);
				if (strlen($fn) == 0)
				{
					continue;
				}
				array_push(self::$forbidDatas[$filename], $fn);
			}
		}
	}
	// 匹配模式
	function checkForbid($str,$filename = "commonForbid")
	{
		self::loadForbidData($filename);
		//判断敏感词
		$str = mb_strtolower($str);
		foreach (self::$forbidDatas[$filename] as $keyword)
		{
			if (false !== mb_strpos($str, mb_strtolower($keyword)))
			{
				return false;
			}
		}
		return true;
	}
	// 等于模式
	function checkEqualForbid($str,$filename = "commonForbid")
	{
		self::loadForbidData($filename);
		$str = mb_strtolower($str);
		foreach (self::$forbidDatas[$filename] as $keyword)
		{
			if (mb_strtolower($keyword) === $str)
			{
				return false;
			}
		}
		return true;
	}
	// 替换成**
	function replaceForbid(&$str,$filename = "commonForbid")
	{
		self::loadForbidData($filename);
		$forbid = false;
		mb_internal_encoding(SYS_CHARSET);
		foreach (self::$forbidDatas[$filename] as $keyword)
		{
			if (false !== mb_strpos($str, $keyword))
			{
				$forbid = true;
				$str = mb_ereg_replace($keyword, "**", $str);
			}
		}
		return $forbid;
	}
}