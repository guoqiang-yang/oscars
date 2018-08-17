<?php
class Tool_Array
{
	/**
	 * 从对象(或数组)中提取指定字段的值
	 */
	public static function getFields($objs, $key)
	{
		$ids = array();
		if (is_array($objs))
		{
			foreach($objs as $obj)
			{
				if (is_array($obj))
				{
					$ids[] = $obj[$key];
				}
				else if (is_object($obj))
				{
					$ids[] = $obj->$key;
				}
				else
				{
					$ids[] = $obj;
				}
			}
		}
		return $ids;
	}

	public static function list2Map(array $list, $key, $value_key=null)
	{
		if(empty($list))
		{
			return array();
		}

		$map = array();
		foreach($list as $item)
		{
			if ($value_key !== null)
			{
				$map[$item[$key]] = $item[$value_key];
			}
			else
			{
				$map[$item[$key]] = $item;
			}
		}
		return $map;
	}

	public static function filterEmpty(&$list)
	{
		if (!empty($list) && is_array($list))
		{
			foreach ($list as $idx => $item)
			{
				if (empty($item))
				{
					unset($list[$idx]);
				}
			}
		}
		return $list;
	}

	public static function sortByField(&$arr, $fieldName, $flag='desc')
	{
		$indexArr = array();
		foreach ($arr as $idx=>$item)
		{
			$indexArr[$idx] = $item[$fieldName];
		}

		if ('desc' == $flag)
		{
			arsort($indexArr);
		}
		else
		{
			asort($indexArr);
		}

		$result = array();
		foreach ($indexArr as $idx=>$field)
		{
			$result[$idx] = $arr[$idx];
		}
		$arr = $result;
		return $arr;
	}

	public static function checkCopyFields($srcArr, $fields)
	{
		$arr = array();
		foreach ($fields as $field)
		{
			$field = trim($field);
			assert( ! empty($field) );

			if (isset($srcArr[$field]))
			{
				$arr[$field] = $srcArr[$field];
			}
		}

		return $arr;
	}

	public static function copyFields($srcArr, array $fields, array $toFields=array())
	{
		assert(empty($toFields) || count($fields) == count($toFields));

		$arr = array();
		foreach ($fields as $idx => $field)
		{
			$field = trim($field);
			assert( ! empty($field) );

			if (empty($toFields))
			{
				$arr[$field] = $srcArr[$field];
			}
			else
			{
				$toField = $toFields[$idx];
				assert( ! empty($toField) );
				$arr[$toField] = $srcArr[$field];
			}
		}

		return $arr;
	}

	public static function mergeFields($srcArr, &$toArr, $fields)
	{
		foreach ($fields as $field)
		{
			$field = trim($field);
			assert( ! empty($field) );

			if (isset($srcArr[$field]))
			{
				$toArr[$field] = $srcArr[$field];
			}else
			{
				$toArr[$field] = '';
			}
		}

		return $toArr;
	}

	public static function rand($source,$count = 1)
	{
		if(!is_array($source) || $count <= 0)
		{
			return array();
		}
		if($count == 1)
		{
			$keys = array(array_rand($source,$count));
		}
		else
		{
			$keys = array_rand($source,$count);
		}

		$list = array();
		foreach($keys as $key)
		{
			$list[$key] = $source[$key];
		}
		if($count == 1 && !empty($list))
		{
			return array_shift($list);
		}
		return $list;
	}

	public static function where($collection,$where)
	{
		if(!is_array($where) || empty($where))
		{
			return $collection;
		}
		$list = array();
		foreach($collection as $idx=>$item)
		{
			$hit = true;
			foreach($where as $key=>$val)
			{
				if(isset($item[$key]) && $item[$key] != $val)
				{
					$hit = false;
					break;
				}
			}
			if($hit)
			{
				$list[$idx] = $item;
			}
		}
		return $list;
	}

	//为了防止汉字被解析成unicode编码，5.4以上可以用参数实现
	public static function jsonEncode($params)
	{
		$params = self::_url_encode($params);
		$params = json_encode($params);
		$params = urldecode($params);

		return $params;
	}

	public static function sumFields($arr, $filed)
	{
		if (empty($arr))
		{
			return 0;
		}

		$sum = 0;
		foreach ($arr as $item)
		{
			$sum += $item[$filed];
		}

		return $sum;
	}

	private static function _url_encode($arr)
	{
		foreach ($arr as $k => $v)
		{
			if (is_array($v))
			{
				$arr[$k] = self::_url_encode($v);
			}
			else
			{
				$arr[$k] = urlencode($v);
			}
		}

		return $arr;
	}
}
