<?php
/**
 * 中文字符串处理
 */
class Str_Chinese
{
	/**
	 * 中文或英文字符串转化成数组
	 */
	public static function str2arr($str)
	{
		$arr = array();
		mb_internal_encoding(SYS_CHARSET);
		$length = mb_strlen($str);
		for ($i=0; $i<$length; $i++)
		{
			$arr[] = mb_substr($str, $i, 1);
		}
		return $arr;
	}

	/**
	 * 汉字繁转简
	 */
	public static function f2j($name)
	{
		static $f2j;
		if (empty($f2j))
		{
			$filename = CORE_DATA_PATH."chinese/f2j.txt";
			$content = file_get_contents($filename);
			$fn_list = explode("\n", $content);
			mb_internal_encoding(SYS_CHARSET);
			foreach($fn_list as $fn)
			{
				$fn = trim($fn);
				if (mb_strlen($fn) != 2)
				{
					continue;
				}
				$f2j[mb_substr($fn, 0, 1)] = mb_substr($fn, 1, 1);
			}
		}


		$arr = self::str2arr($name);
		$acount = count($arr);
		for ($i=0; $i<$acount; $i++)
		{
			if (array_key_exists($arr[$i], $f2j))
			{
				$arr[$i] = $f2j[$arr[$i]];
			}
		}
		return implode("", $arr);
	}

	/**
	 * 汉字简转繁
	 */
	public static function j2f($name)
	{
		static $j2f;
		if (empty($j2f))
		{
			$filename = CORE_DATA_PATH."chinese/j2f.txt";
			$content = file_get_contents($filename);
			$fn_list = explode("\n", $content);
			mb_internal_encoding(SYS_CHARSET);
			foreach($fn_list as $fn)
			{
				$fn = trim($fn);
				if (mb_strlen($fn) != 2)
				{
					continue;
				}
				$j2f[mb_substr($fn, 0, 1)] = mb_substr($fn, 1, 1);
			}
		}


		$arr = self::str2arr($name);
		$acount = count($arr);
		for ($i=0; $i<$acount; $i++)
		{
			if (array_key_exists($arr[$i], $j2f))
			{
				$arr[$i] = $j2f[$arr[$i]];
			}
		}
		return implode("", $arr);
	}

    /**
     * 汉字转换为拼音.
     *
     * @param string $name  输入的汉字
     * @param bool $getSingle 默认取全部音节
     * @return string
     */
	public static function hz2py2($name, $getSingle = false)
    {
        static $hz2py2;
		if (empty($hz2py2))
		{
			$filename = CORE_DATA_PATH."chinese/hzpy.txt";
			$content = file_get_contents($filename);
			$fn_list = explode("\n", $content);
			mb_internal_encoding(SYS_CHARSET);
            
			foreach($fn_list as $fn)
			{
                $fns = explode("\t", trim($fn));
                if (count($fns)<2)
                {
                    continue;
                }
                
                $hz2py2[$fns[0]] = $fns[1];
			}
		}

		$arr = self::str2arr($name);
		$acount = count($arr);
        $py = '';
        $pre_ischar = false;
		for ($i=0; $i<$acount; $i++)
		{
            //是否为字符 所有简单字符 ascii 33-126
            if (ord($arr[$i])>=33 && ord($arr[$i])<=126)
            {
                if ($pre_ischar)
                {
                    $py = trim($py). $arr[$i].' ';
                }
                else
                {
                    $py .= $arr[$i]. ' ';
                }
                $pre_ischar = true;
            }
            else if (array_key_exists($arr[$i], $hz2py2))
			{
				//$arr[$i] = $hz2py2[$arr[$i]];
                $pyArr = explode(',', $hz2py2[$arr[$i]]);
                if (count($pyArr)>=2 && $getSingle)
                {
                    $py .= $pyArr[0]. ' ';
                }
                else
                {
                    $py .= $hz2py2[$arr[$i]]. ' ';
                }

                $pre_ischar = false;
			}
		}

        return trim(str_replace(',', ' ', $py));
    }

	public static function hz2py($str , &$firststr_arr , &$pystr_arr , &$filterstr="")
	{
		$firststr_arr = array("");
		$pystr_arr = array("");
		$filterstr = "";
		$singleflag = 0;

		$id = dba_open(CORE_DATA_PATH."chinese/hz2py","r","gdbm");
		if(!$id)
		{
			return;
		}
		mb_internal_encoding(SYS_CHARSET);
		$len = mb_strlen($str);
		for($i=0; $i<$len; $i++)
		{
			$char = mb_substr($str, $i, 1);
			if(mb_strwidth($char) > 1)
			{
				$pystrtemp = dba_fetch($char, $id);
				$arr = split("," , $pystrtemp);

				//拼音
				$pystr_arr2 = array();
				foreach($pystr_arr as $py)
				{
					foreach($arr as $value)
					{
						$pystr_arr2[] = $py.strtolower($value);
					}
				}
				$pystr_arr = $pystr_arr2;

				//拼音头字母
				$firststr_arr2 = array();
				foreach($firststr_arr as $firstpy)
				{
					foreach($arr as $value)
					{
						$firststr_arr2[] = $firstpy.substr(strtolower($value) , 0 , 1);
					}
				}
				$firststr_arr = $firststr_arr2;

				//过滤字符串
				if(0 != strlen($pystrtemp))
				{
					$filterstr .= $char;
				}
			}
			else
			{
				$char = strtolower($char);
				$ord = ord($char);
				if ($ord >= 97 && $ord <= 122)
				{
					//拼音
					foreach($pystr_arr as $k=>$py)
					{
						$pystr_arr[$k] .= $char;
					}
					//拼音头字母
					foreach($firststr_arr as $k=>$firstpy)
					{
						$firststr_arr[$k] .= $char;
					}
					//过滤字符串
					$filterstr .= $char;
				}
		   }
		   }
		dba_close($id);
		return true;
	}

	// 是否匹配拼音或者英文
	public static function isMatchPy($keyword,$str)
	{
		$keyword = strtolower($keyword);
		self::hz2py($str,$first,$all);
		foreach($first as $v)
		{
			$ret1 = strstr($v,$keyword);
		}
		foreach($all as $v)
		{
			$ret2 = strstr($v,$keyword);
		}
		return ( $ret1 || $ret2);
	}

	public static function matchPinyin($query, $data, $dataKey)
	{
		$matched_list = array();
		foreach ($data as $item)
		{
			$name = strlen($dataKey) ? $item[$dataKey] : $item;
			$textmatch = $pymatch = $pyfirstmatch = $mixengmatch = false;

			self::hz2py($name , $name_pyfirst_arr , $name_py_arr , $filter_name);
			$ismixeng = self::filterEngStr($name , $engstr);

			$textmatch = strtolower(substr($filter_name , 0 , strlen($query))) == strtolower($query);
			$pymatch = self::checkMatch($name_py_arr, $query);
			$pyfirstmatch = self::checkMatch($name_pyfirst_arr, $query);
			if ($ismixeng)
			{
				$mixengmatch = strtolower(substr($engstr , 0 , strlen($query))) == strtolower($query);
			}

			if($textmatch == false && $pymatch == false
				&& $pyfirstmatch == false && $mixengmatch == false)
			{
				continue;
			}

			$matched_list[] = $item;
		}
		return $matched_list;
	}

	private static function checkMatch($arr, $text)
	{
		mb_internal_encoding(SYS_CHARSET);
		foreach ($arr as $value)
		{
			$value = str_replace("|", "", $value);
			if (mb_substr($value, 0, mb_strlen($text)) == strtolower($text))
			{
				return true;
			}
		}
		return false;
	}

	private static function filterEngStr($str, &$engstr)
	{
		mb_internal_encoding(SYS_CHARSET);

		$engstr = "";
		$len = mb_strlen($str);
		for ($i=0; $i<$len; $i++)
		{
			$char = mb_substr($str, $i, 1);
			$ord = ord($char);
			if ($ord >= 97 && $ord <= 122)
			{
				$engstr .= $char;
			}
		}
		if (0 != strlen($engstr) && $str != $engstr)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param $arr array( 146=>'张三', 161=>'李四', ..)
	 * @return id列表
	 */
	public static function suggest($arr, $key)
	{
		$res1 = array();
		$res2 = array();
		$res3 = array();
		$res4 = array();

		if(empty($key))
		{
			return array();
		}

		foreach($arr as $id=>$name)
		{
			//1. 完全匹配
			if (0 == strncasecmp($name,$key,strlen($key)))
			{
				$res1[] = $id;
				continue;
			}

			//2. 汉字第一个字母匹配
			$key = strtolower($key);
			Str_Chinese::hz2py($name,$first,$all);
			$found = false;
			foreach($first as $v)
			{
				if ( 0 == strncasecmp($v, $key, strlen($key)) )
				{
					$res2[] = $id;
					$found = true;
					break;
				}
			}
			if ($found) continue;

			//3. 全名搜索
			if (strstr($name, $key))
			{
				$res3[] = $id;
				continue;
			}

			//4. 全拼搜索
			foreach($first as $v)
			{
				$ret = strstr($v,$key);
				if ($ret) break;
			}
			if($ret)
			{
				$res4[] = $id;
				continue;
			}
			foreach($all as $v)
			{
				$ret = strstr($v,$key);
				if ($ret) break;
			}
			if($ret)
			{
				$res4[] = $id;
				continue;
			}
		}

		return array_merge($res1, $res2, $res3, $res4);
	}

	public static function subString($str, $length, $ext = '')
	{
		mb_internal_encoding(SYS_CHARSET);
		return mb_strimwidth($str, 0, $length, $ext);
	}

	function lenString($srcstr)
	{
		mb_internal_encoding(SYS_CHARSET);
		return mb_strwidth($srcstr);
	}

	/**
	 * 60前、60后、70后、80后、90后、00后、10后
	 */
	public static function getAgeStep($year)
	{
		if($year < 1960)
		{
			return '60前';
		}
		else if($year >= 1960 && $year < 1970)
		{
			return '60后';
		}
		else if($year >= 1970 && $year < 1980)
		{
			return '70后';
		}
		else if($year >= 1980 && $year < 1990)
		{
			return '80后';
		}
		else if($year >= 1990 && $year < 2000)
		{
			return '90后';
		}
		else if($year >= 2000 && $year < 2010)
		{
			return '00后';
		}
		else if($year >= 2010)
		{
			return '10后';
		}
	}

	public static function getAstro($month, $day)
	{
		if (($month == 12 && $day >= 22) || ($month == 1 && $day <= 20))
		{
			return "摩羯座";
		}
		else if (($month == 1 && $day >= 21) || ($month == 2 && $day <= 19))
		{
			return "水瓶座";
		}
		else if (($month == 2 && $day >= 20) || ($month == 3 && $day <= 20))
		{
			return "双鱼座";
		}
		else if (($month == 3 && $day >= 21) || ($month == 4 && $day <= 20))
		{
			return "白羊座";
		}
		else if (($month == 4 && $day >= 21) || ($month == 5 && $day <= 21))
		{
			return "金牛座";
		}
		else if (($month == 5 && $day >= 22) || ($month == 6 && $day <= 21))
		{
			return "双子座";
		}
		else if (($month == 6 && $day >= 22) || ($month == 7 && $day <= 22))
		{
			return "巨蟹座";
		}
		else if (($month == 7 && $day >= 23) || ($month == 8 && $day <= 23))
		{
			return "狮子座";
		}
		else if (($month == 8 && $day >= 24) || ($month == 9 && $day <= 23))
		{
			return "处女座";
		}
		else if (($month == 9 && $day >= 24) || ($month == 10 && $day <= 23))
		{
			return "天秤座";
		}
		else if (($month == 10 && $day >= 24) || ($month == 11 && $day <= 22))
		{
			return "天蝎座";
		}
		else if (($month == 11 && $day >= 23) || ($month == 12 && $day <= 21))
		{
			return "射手座";
		}
		return "";
	}

	/**
	 * 检查字符串中是否含有标点
	 */
	public static function symbol($str)
	{
		return preg_match('/[^\x{4e00}-\x{9fa5}0-9A-Za-z]/u', $str);
	}

	/**
	 * 过滤空格与换行符转换为一个半角空格
	 * 连续多个空格与换行符仅转换为一个空格
	 * 去除头尾空格
	 */
	public static function filterSpaceLine($content, $length = 0, $ext = '')
	{
		$length = intval($length);
		if ($length)
		{
			$content = Str_Chinese::subString($content,$length,$ext);
		}
		$content = trim($content);
		$content = trim($content, "\n");
		$content = str_replace(array("\r\n", "\r", "\n"), "\n", $content);
		$content = preg_replace("/\n/", " ", $content);
		$content = htmlspecialchars($content);
		$content = preg_replace("/\s+/", "&nbsp;", $content);
		return $content;
	}

	/**
	 * 计算UTF-8编码的字节长度
	 */
	public static function getUTF8Length($str){
		return (strlen($str) + mb_strlen($str,'UTF8'))/2;
	}

	public static function getChineseNum($num)
	{
		$c1 = "零壹贰叁肆伍陆柒捌玖";
		$c2 = "分角元拾佰仟万拾佰仟亿";
		$num = round($num, 2);
		$num = $num * 100;
		if (strlen($num) > 10) {
			return "数据太长，没有这么大的钱吧，检查下";
		}
		$i = 0;
		$c = "";
		while (1) {
			if ($i == 0) {
				$n = substr($num, strlen($num)-1, 1);
			} else {
				$n = $num % 10;
			}
			$p1 = substr($c1, 3 * $n, 3);
			$p2 = substr($c2, 3 * $i, 3);
			if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
				$c = $p1 . $p2 . $c;
			} else {
				$c = $p1 . $c;
			}
			$i = $i + 1;
			$num = $num / 10;
			$num = (int)$num;
			if ($num == 0) {
				break;
			}
		}
		$j = 0;
		$slen = strlen($c);
		while ($j < $slen) {
			$m = substr($c, $j, 6);
			if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
				$left = substr($c, 0, $j);
				$right = substr($c, $j + 3);
				$c = $left . $right;
				$j = $j-3;
				$slen = $slen-3;
			}
			$j = $j + 3;
		}

		if (substr($c, strlen($c)-3, 3) == '零') {
			$c = substr($c, 0, strlen($c)-3);
		}
		if (empty($c)) {
			return "零元整";
		}else{
			return $c . "整";
		}
	}

	/**
	 * 中文或英文字符串转化成数组-但是英文单词会被作为一个字放入数组
	 */
	public static function str2WordArr($str)
	{
		$arr = array();

		mb_internal_encoding(SYS_CHARSET);
		$length = mb_strlen($str);
		$s = '';
		$n = '';
		for ($i = 0; $i < $length; $i++)
		{
			$t = mb_substr($str, $i, 1);
			$t = trim($t);

			if ($t === '')
			{
				continue;
			}

			if ((ord($t) >= 65 && ord($t) <= 90) || (ord($t) >= 97 && ord($t) <= 122))
			{
				$s .= $t;
				continue;
			}
			else
			{
				if ($s != '')
				{
					$arr[] = $s;
					$s = '';
				}
			}

			if ($t == '.' || is_numeric($t))
			{
				$n .= $t;
				continue;
			}
			else
			{
				if ($n != '')
				{
					$arr[] = $n;
					$n = '';
				}

				$arr[] = $t;
			}
		}

		if ($s != '')
		{
			$arr[] = $s;
		}

		if ($n != '')
		{
			$arr[] = $n;
		}

		return $arr;
	}
}