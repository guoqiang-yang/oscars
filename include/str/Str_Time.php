<?php

class Str_Time
{
	/*
		0-60秒内：   刚刚
		1-59分钟：   N分钟前
		今天 HH:MM
		昨天 HH:MM
		前天 HH:MM
		当年：	   M月D日 HH:MM
		跨年：	   YYYY年M月D日 HH:MM
	*/
	function t2s($t, $showYear=true)
	{
		$now = time();
		$tt = strtotime($t);
		$interval = $now - $tt;

		if ($interval <= 60)
		{
			return "刚刚";
		}

		if ($interval < 3600)
		{
			return round($interval/60)."分钟前";
		}

		list($year, $month, $day, $h, $m, $s) = sscanf($t, "%d-%d-%d %d:%d:%d");
		if ($month < 10) $month = "0".$month;
		if ($day < 10) $day = "0".$day;
		if ($h < 10) $h = "0".$h;
		if ($m < 10) $m = "0".$m;

		$t_today = strtotime(date("Y-m-d 23:59:59"));
		$interval2 = $t_today - $tt;
		if ($interval2 >= 0)
		{
			if ($interval2 < 86400)
			{
				return "今天 ".$h.":".$m;
			}
			else if ($interval2 < 86400*2)
			{
				return "昨天 ".$h.":".$m;
			}
			else if ($interval2 < 86400*3)
			{
				return "前天 ".$h.":".$m;
			}
		}

		if ($year == date("Y", $now) || !$showYear)
		{
			return $month."月".$day."日 ".$h.":".$m;
		}
		else
		{
			return $year."年".$month."月".$day."日 ".$h.":".$m;
		}
	}

	/*
	当年：	  M月D日 HH:MM
	跨年：	  YYYY年M月D日 HH:MM
	*/
	function t2s2($t)
	{
		list($year, $month, $day, $h, $m, $s) = sscanf($t, "%d-%d-%d %d:%d:%d");
		if ($month < 10) $month = "0".$month;
		if ($day < 10) $day = "0".$day;
		if ($h < 10) $h = "0".$h;
		if ($m < 10) $m = "0".$m;

		if ($year == date("Y"))
		{  
			return $month."月".$day."日 ".$h.":".$m;
		}
		else
		{
			return $year."年".$month."月".$day."日 ".$h.":".$m;
		}
	}

	/*
	当年：	  MM-DD HH:MM
	跨年：	  YYYY-MM-DD HH:MM
	*/
	function t2s3($t)
	{
		list($year, $month, $day, $h, $m, $s) = sscanf($t, "%d-%d-%d %d:%d:%d");
		if ($month < 10) $month = "0".$month;
		if ($day < 10) $day = "0".$day;
		if ($h < 10) $h = "0".$h;
		if ($m < 10) $m = "0".$m;

		if ($year == date("Y"))
		{
			return $month."-".$day." ".$h.":".$m;
		}
		else
		{
			return $year."-".$month."-".$day." ".$h.":".$m;
		}
	}

	function s2hm($interval)
	{
		$hour = floor($interval / 3600);
		$minute = floor(($interval - $hour * 3600) / 60);
		if ($minute)
		{
			return $hour."小时".$minute."分";
		}
		else if ($hour)
		{
			return $hour."小时整";
		}
		else
		{
			return $interval."秒";
		}
	}
	
	/**
	 * 获取一周第几天对应的中文
	 */
	public static function getWeekHZ($dayNo)
	{
		$arr = array("一", "二", "三", "四", "五", "六", "日");
		return "星期".$arr[$dayNo-1];
	}
	
	public static function getMicro()
	{
		list($msec, $sec) = explode(" ", microtime());
		return ((float)$msec + (float)$sec);
	}
	// 每个月的天数
	public static function getMonthDays($month, $year)
	{
		switch($month)
		{
			case 4:
			case 6:
			case 9:
			case 11:
				$days = 30;
				break;

			case 2:
				if ($year % 4 == 0)
				{
					if ($year % 100 == 0)
					{
						$days = $year % 400 == 0 ? 29 : 28;
					}
					else
					{
						$days =29;
					}
				}
				else
				{
					$days = 28;
				}
				break;

			default:
				$days = 31;
				break;
		}

		return $days;
	}
}