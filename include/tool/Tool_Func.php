<?php
/**
 * Func 函数
 */
class Tool_Func
{
	public static function filterPara($srcPara, $paraConf)
	{
		$dstPara = array();
		foreach ($paraConf as $var_name => $var_type)
		{
			if (!isset($srcPara[$var_name]))
			{
				continue;
			}

			$var_value = $srcPara[$var_name];
			Tool_Input::cast($var_value, $var_type);
			$dstPara[$var_name] = $var_value;
		}
		return $dstPara;
	}
    
    public static function setIntVal(&$objVal, $k, $val)
    {
        if (array_key_exists($k, $objVal))
        {
            $objVal[$k] += $val;
        }
        else
        {
            $objVal[$k] = $val;
        }
    }
    
    /**
     * 签名：有序参数签名.
     */
    public static function sign4HC($params, $key)
    {
        if (empty($params)) return '';
        
        $params['consumer_key'] = $key;
        unset($params['apiversion']);   //不是从app端来的，服务端加入的参数，去掉
        unset($params['iversion']);
        ksort($params);
        
        $signParams = array();
        foreach($params as $_key => $val)
        {
            $signParams[] = $_key. '='. $val;
        }
        //sort($signParams);
        $signStr = implode('&', $signParams);
        
        return md5($signStr);
    }
    
    public static function verifySign4HC($params, $key)
    {
        if (empty($params['access_signature'])) return false;
        
        $sign = $params['access_signature'];
        unset($params['access_signature']);
        
        return self::sign4HC($params, $key)==$sign? true: false;
    }
}
