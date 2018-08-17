<?php
/**
 * 检测和规范输入数据接口
 *
 * 类型参数说明：
 *	'noclean'  - 不做处理
 *	'int'	  - 转换成integer
 *	'unit'	 - 转换成无符号integer
 *  'num'	  - 转换成number
 *	'str'	  - 转换成string，并去除两边的空格
 *	'notrim'   - 转换成string，保留空格
 *	'arr'	  - 转换成array
 *	'file'	 - 转换成file，不支持数组提交
 *	'html'	 - HTML提交，不支持数组提交
 *	'richhtml' - HTML提交，支持更多标签，不支持数组提交
 */
class Tool_Input
{
	private static $globalSources = array (
		'g' => '_GET',
		'p' => '_POST',
		'c' => '_COOKIE',
		'r' => '_REQUEST',
		'f' => '_FILES'
	);

	/**
	 * 获取输入
	 */
	public static function clean($source, $varname, $type = 'noclean')
	{
		assert(!empty(self::$globalSources[$source]));

		self::processMagicQuotes();

		$container = $GLOBALS[self::$globalSources[$source]];
		$var = isset($container[$varname]) ? $container[$varname] : '';

		return self::cast($var, $type);
	}

	private static function processMagicQuotes()
	{
		static $hasProcessed = false;

		if (!$hasProcessed && get_magic_quotes_gpc())
		{
			self::stripslashesDeep($_GET);
			self::stripslashesDeep($_POST);
			self::stripslashesDeep($_COOKIE);
			self::stripslashesDeep($_REQUEST);

			$hasProcessed = true;
		}
	}

	public static function &cast($data, $type)
	{
		switch ($type)
		{
			case 'noclean':
				break;
			case 'int':
				$data = intval($data);
				break;
			case 'uint':
				$data = max(0, intval($data));
				break;
			case 'num':
				$data = $data + 0;
				break;
			case 'str':
				$data = trim(Str_Check::sanitizeUTF8(strval($data)));
				break;
			case 'notrim':
				$data = Str_Check::sanitizeUTF8(strval($data));
				break;
			case 'file':
				if (!is_array($data))
				{
					 $data = array(
						'name'	 => '',
						'type'	 => '',
						'size'	 => 0,
						'tmp_name' => '',
						'error'	=> UPLOAD_ERR_NO_FILE,
					 );
				}
				break;
			case 'arr':
				break;
			default:
				assert(false);
		}
		return $data;
	}

	/**
	 * 递归 stripslashes
	 */
	private static function stripslashesDeep(&$value)
	{
		if (is_array($value))
		{
			foreach ($value as $sKey => $vVal)
			{
				self::stripslashesDeep($vVal);
			}
		}
		else if (is_string($value))
		{
			$value = stripslashes($value);
		}
	}
}
