<?php
/**
 * 检查真实姓名
 */

class Str_Name
{
	/**
	* 判断是否是汉字真实姓名
	 * @return bool
	 */

	public function checkRealName($name)
	{
		if (false == self::checkChinese($name))
		{
			return false;
		}
			
		if (false == self::checkLength($name))
		{
			return false;
		}
				
		if (false == self::checkSame($name))
		{
			return false;
		}
			
		if (false == self::checkNameValid($name))
		{
			return false;
		}
				
		if (false == self::checkFirstName($name))
		{
			return false;
		}
		
		return true;
	}

	/**
	* 字符串是否全部为汉字
	*/
	protected function checkChinese($str) 
	{
		$len = strlen($str);
		for ($i = 0; $i < $len; $i++)
		{
			if (ord($str[$i]) > 0x80) 
			{
				$i++;
			} 
			else 
			{
				return false;
			}
		}
		return true;
	}
	
	/**
	* 检查汉字字符串是否在2--8个字的范围之内
	*/
	protected function checkLength($str) 
	{
		$len = strlen($str);
		if($len < 4 || $len > 16)
		{
			return false;
		}
		return true;
	}
	
	/**
	*  检查汉字字符串是否由完全相同字符组成，完全相同时返回false
	*/
	protected function checkSame($str)
	{
		$arr = Str_Chinese::str2arr($str);
		$arr = array_unique($arr);
		if(1 == count($arr)) 
		{
			return false;
		}
		return true;			
	}
	
	/**
	* 检查姓名是否是有效名字
	*/
	protected function checkNameValid($name) 
	{
		//无效名字列表
		static $invalidName;
		if (empty($invalidName))
		{
			$invalidName = array();
			$filename = DATA_PATH."/name/invalidname.txt";
			
			$content = file_get_contents($filename);
			$fn_list = explode("\n", $content);
			mb_internal_encoding(SYS_CHARSET);
			foreach($fn_list as $fn)
			{
				$fn = trim($fn);
				if (strlen($fn) == 0)
				{
					continue;
				}
				array_push($invalidName, $fn);
			}
		}
	
		//检查名字是否有效
		$name = mb_strtolower($name);
		foreach ($invalidName as $keyword)
		{
			if (false !== mb_strpos($name, mb_strtolower($keyword)))
			{
				return false;
			}
		}
		return true;
	}
	
	/**
	* 检查姓氏是否在姓氏库中
	*/
	protected function checkFirstName($name)
	{
		//姓列表
		static $firstName;
		if (empty($firstName))
		{
			  $firstName = array();
			  $filename = DATA_PATH."/name/firstname.txt";
	
			$content = file_get_contents($filename);
			$fn_list = explode("\n", $content);
			mb_internal_encoding(SYS_CHARSET);
			foreach($fn_list as $fn)
			{
				$fn = trim($fn);
				if (strlen($fn) == 0)
				{
					continue;
				}
				array_push($firstName, $fn);
			}
		} 
		//检查名字前一个字
		$char1 = mb_substr($name, 0, 1);
	
		if(in_array($char1 , $firstName))
		{
			return true;
		}
			
		//检查名字前两个字
		$char2 = mb_substr($name, 0, 2);
		if(in_array($char2 , $firstName))
		{
			return true;
		}
	
		//检查名字前三个字
		$char3 = mb_substr($name, 0, 3);
		if(in_array($char3 , $firstName))
		{
			return true;
		}
	
		//检查名字前四个字
		$char4 = mb_substr($name, 0, 4);
		if(in_array($char4 , $firstName))
		{
			return true;
		}
		return false;
	
	}
}