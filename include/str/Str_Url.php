<?php
/*
 * url 相关的字符串操作
 */
class Str_Url
{
	/**
	 * 用于url的base64编码
	 */
	function base64url_encode($data)
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	/**
	 * 用于url的base64解码
	 */
	function base64url_decode($data)
	{
		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
	}

	function isLink($url)
	{
		$scheme = parse_url($url, PHP_URL_SCHEME);
		if (! $scheme || ($scheme != 'http' && $scheme != 'https'))
		{
			return false;
		}

		if (filter_var($url, FILTER_VALIDATE_URL))
		{
			if (strpos(parse_url($url, PHP_URL_HOST), ".") > 0)
			{
				return true;
			}
		}

		return false;
	}

	function hasLink($str)
	{
		if (ereg("(mms://|http://|ftp://|https://|www\.)[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)+[^\s.]*", $str))
		{
			return true;
		}
		return false;
	}

	function extractLinks($src, &$urls)
	{
		if (0 == preg_match("#(mms://|http://|ftp://|https://|www\.)[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)+[^\s.]*#i", $src, $res, PREG_OFFSET_CAPTURE))
		{
			return str_replace("\n ", "\n&nbsp;", str_replace("  ", "&nbsp; ", $src));
		}
		$len = strlen($src);
		$start = $res[0][1];
		for ($end=$start; $end<$len; $end++)
		{
			if (ord($src[$end]) <= 0x20
				|| $src[$end] == "'"
				|| $src[$end] == '"'
				|| $src[$end] == '<'
				|| $src[$end] == '>'
				|| ord($src[$end]) >= 0x80)
			{
				break;
			}
		}
		$urls[] = substr($src, $start, $end - $start);
		self::extractLinks(substr($src, $end), $urls);
	}

	/**
	 * 实现和js中的encodeURI一样的功能
	 *
	 * https://developer.mozilla.org/en/JavaScript/Reference/Global_Objects/encodeURI
	 *
	 */
	function encodeURI($url)
	{
		$reserved = array(
			'%2D'=>'-',
			'%5F'=>'_',
			'%2E'=>'.',
			'%21'=>'!',
			'%2A'=>'*',
			'%27'=>"'",
			'%28'=>'(',
			'%29'=>')',
		);
		$unescaped = array(
			'%3B'=>';',
			'%2C'=>',',
			'%2F'=>'/',
			'%3F'=>'?',
			'%3A'=>':',
			'%40'=>'@',
			'%26'=>'&',
			'%3D'=>'=',
			'%2B'=>'+',
			'%24'=>'$',
		);
		$score = array(
			'%23'=>'#'
		);
		// 防止 % 被转两次
		$special = array(
			'%25' => '%',
		);
		return strtr(rawurlencode($url), array_merge($reserved, $unescaped, $score, $special));
	}

	/**
	 * 过滤 URL
	 *
	 * 成功返回url 失败返回false
	 */
	public static function sanitizeURL($url, $allowedDomains = array())
	{
		$urlInfo = parse_url($url);
		if (isset($urlInfo["host"]) && !empty($allowedDomains))
		{
			if (!in_array($urlInfo["host"], $allowedDomains))
			{
				return false;
			}
		}
		return $url;
	}
}