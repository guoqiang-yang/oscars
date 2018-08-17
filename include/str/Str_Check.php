<?php
/**
*	检查Email地址是否合法
*/

class Str_Check
{
	/**
	 * 手机号验证
	 */
	public static function checkMobile($mobile)
	{
		$exp = "/^1[0-9]{10}$|^0[0-9]{10}/";

		if(preg_match($exp,$mobile))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function checkEmail($email)
	{
		//检查email用户和域名字符串长度，rfc规定最大不超过320,本应用限定128个字符
		if (!ereg("[^@]{1,64}@[^@]{1,255}", $email) || strlen($email) > 128)
		{
			return false;
		}

		//分割email地址，分隔符: '@'
		$email_array = explode("@", $email);
		if(count($email_array) != 2)
		{
			return false;
		}

		//检查Eamil user部分，即'@'前面部分的字符串
		$local_array = explode(".", $email_array[0]);
		for ($i = 0; $i < count($local_array); $i++)
		{
			if(!ereg("^(([a-za-z0-9!#$%&*+/=?^_`{|}~-][a-za-z0-9!#$%&*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i]))
			{
				return false;
			}
		}
		////检查Eamil 域名部分，即'@'后面部分的字符串，域名或IP地址
		if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1]))
		{
			$domain_array = explode(".", $email_array[1]);
			if (count($domain_array) < 2)
			{
				return false; //域名格式不正确
			}
			for ($i = 0; $i < count($domain_array); $i++)
			{
				if (!ereg("^(([a-za-z0-9][a-za-z0-9-]{0,61}[a-za-z0-9])|([a-za-z0-9]+))$", $domain_array[$i]))
				{
					return false;
				}
			}
		}
		return true;
	}

	// 检查密码格式是否复合要求
	public static function checkPassword($password)
	{
		$password = trim($password);
		if (strlen($password) < 6 || strlen($password) > 18)
		{
			return false;
		}
		return true;
	}
    
    // 是否为简单的密码
    public static function isSimplePasswd($passwd, $mobile='')
    {
        if (empty($passwd) || $passwd=='111111' || $passwd=='123456')
        {
            return true;
        }
        
        if (!empty($mobile) && $passwd==substr($mobile, -6))
        {
            return true;
        }
        
        return false;
    }
    
    /**
     * 检测当前的用户密码是否为简单密码 Md5密码.
     * 
     * @param string $password
     * @param string $salt  不能为空，如果为空可使用方法 self::isSimplePasswd()
     * @param string $mobile 
     */
    public static function isSimplePasswd4User($password, $salt, $mobile)
    {
        if (empty($password) || empty($salt))
        {
            return true;
        }
        
        // 密码格式 md5($salt . ':' . $password)
        $simplePasswds = array(
            md5($salt. ':111111'),
            md5($salt. ':123456'),
        );
        
        if (!empty($mobile))
        {
            $simplePasswds[] = md5($salt. ':'. substr($mobile, -6));
        }
        
        if (in_array($password, $simplePasswds))
        {
            return true;
        }
        
        return false;
    }

	// 把字符串中的非UTF-8字符转换成U+FFFD
	// php的json_encode只支持UTF-8数据 若有非UTF-8数据 则直接返回null
	// 因此需要保证入库的数据必须是合法的utf-8数据
	public static function sanitizeUTF8($string)
	{
		if (self::isUTF8($string))
		{
			return $string;
		}

		$result = array();

		$regex =
			"/([\x01-\x7F]".
			"|[\xC2-\xDF][\x80-\xBF]".
			"|[\xE0-\xEF][\x80-\xBF][\x80-\xBF]".
			"|[\xF0-\xF4][\x80-\xBF][\x80-\xBF][\x80-\xBF])".
			"|(.)/";

		$offset = 0;
		$matches = null;
		while (preg_match($regex, $string, $matches, 0, $offset))
		{
			if (!isset($matches[2])) {
				$result[] = $matches[1];
			}
			else
			{
				// U+FFFD.
				$result[] = "\xEF\xBF\xBD";
			}
			$offset += strlen($matches[0]);
		}

		return implode('', $result);
	}

	public static function isUTF8($string)
	{
		return mb_check_encoding($string, 'UTF-8');
	}
}