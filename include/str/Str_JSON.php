<?php

class Str_JSON
{
	/**
	 * 安全编码Json数据
	 *
	 */
	public static function stringify($data)
	{
		$json = str_replace(array('<', '>', '&'), array('\u003c', '\u003e', '\u0026'), json_encode($data));
		$json = str_replace('\u2028', '\\\u2028', $json);
		$json = str_replace('\u2029', '\\\u2029', $json);

		return $json;
	}
}
