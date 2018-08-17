<?php
/**
 *   文本格式化
 * 
 * @category	kxm
 * @package	 moment
 * @subpackage  text
 * @author	  huqiu
 */
class Str_Format
{ 
	/**
	 * 显示模板
	 */
	public static function parsePtn($ptn,$data)
	{
		if(!$data)
		{
			return $ptn;
		}
		
		$trans = array();
		foreach($data as $key=>$value)
		{
			if($key == "ta")
			{
				$value = $value  ? "她" : "他";
			}
			$key = "{".$key."}";
			$trans[$key] = $value;
		}
		$word = strtr($ptn,$trans);
		return $word;
	}
}