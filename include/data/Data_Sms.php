<?php

/**
 * 短信
 */

class Data_Sms
{
    private static $EX_MOBILES = array(
        18910053781
    );

    public static function sendNew($mobile, $key, $para)
    {
        if (!Str_Check::checkMobile($mobile))
        {
            return FALSE;
        }

        if (!in_array($mobile, self::$EX_MOBILES) && BASE_HOST != '.haocaisong.cn')
        {
            return FALSE;
        }

        //只是针对验证码
        if (Conf_Sms::isVerifyMessage($key))
        {
            $res = Safe_Api::checkVerifyCodeLimit($mobile);
            if (!$res)
            {
                return FALSE;
            }
        }
        else
        {
            $res = Safe_Api::checkNoticeLimit($mobile);
            if (!$res)
            {
                return FALSE;
            }
        }

        //阿里云邮件引擎
        $engine = Sms_Factory::getSmsEngine('aliyun', $mobile, $key, $para);
        //第一信息邮件引擎
        //$engine = Sms_Factory::getSmsEngine('yixinxi', $mobile, $key, $para);
        return $engine->send();
    }

    //目前是阿里云发验证码，第一信息发通知，所以单独分开了；
    //如果通知也用阿里云的话，可以直接调用sendNew，这个方法就可以删除了
    public static function sendVerifyCode($mobile, $code)
    {
        if (!Str_Check::checkMobile($mobile))
        {
            return FALSE;
        }

        //不要删！不要删！不要删！
        //测试环境发短信，请添加例外手机号！！！！！
        if (!in_array($mobile, self::$EX_MOBILES) && BASE_HOST != '.haocaisong.cn')
        {
            return FALSE;
        }

        $res = Safe_Api::checkVerifyCodeLimit($mobile);
        if (!$res)
        {
            return FALSE;
        }

        $accessKeyId = "LTAI8xLoqhLezMge";//参考本文档步骤2
        $accessKeySecret = "8vtppMJTdCChDwsZPiTD5fTaYnLFPm";//参考本文档步骤2
        $signName = '好材';
        $templateCode = 'SMS_114385418';
        $templateParam = "{\"code\":\"$code\"}";

        date_default_timezone_set('GMT');
        $para = array(
            'SignatureMethod' => 'HMAC-SHA1',
            'SignatureNonce' => time(),
            'AccessKeyId' => $accessKeyId,
            'SignatureVersion' => '1.0',
            'Timestamp' => date('Y-m-d') . "T" . date('H:i:s') . "Z",
            'Format' => 'JSON',
            'Action' => 'SendSms',
            'Version' => '2017-05-25',
            'RegionId' => 'cn-hangzhou',
            'PhoneNumbers' => $mobile,
            'SignName' => $signName,
            'TemplateParam' => $templateParam,
            'TemplateCode' => $templateCode,
        );
        unset($para['Signature']);

        ksort($para);

        $query = '';
        foreach ($para as $k => $v)
        {
            $k = self::specialUrlEncode($k);
            $v = self::specialUrlEncode($v);

            $query .= $k . '=' . $v . '&';
        }
        $query = substr($query, 0, strlen($query) - 1);
        $queryStr = 'GET&' . self::specialUrlEncode("/") . '&' . self::specialUrlEncode($query);
        $signStr = self::getSignature($accessKeySecret . "&", $queryStr);
        $queryPara = 'Signature=' . self::specialUrlEncode($signStr) . '&' . $query;

        date_default_timezone_set("Asia/Shanghai");
        $ret = Tool_Http::get('http://dysmsapi.aliyuncs.com/', $queryPara);

        Tool_Log::addFileLog('sms/verify_log_'.date('Ym'), $mobile.'-'.$code.'-'.$ret);

        return true;
    }

    private static function specialUrlEncode($str)
    {
        $str = urlencode($str);
        $str = str_replace("+", "%20", $str);
        $str = str_replace("*", "%2A", $str);
        $str = str_replace("%7E", "~", $str);

        return $str;
    }

    private static function getSignature($key, $str)
    {
        $signature = "";
        if (function_exists('hash_hmac'))
        {
            $signature = base64_encode(hash_hmac("sha1", $str, $key, true));
        }
        else
        {
            $blocksize = 64;
            $hashfunc = 'sha1';
            if (strlen($key) > $blocksize)
            {
                $key = pack('H*', $hashfunc($key));
            }
            $key = str_pad($key, $blocksize, chr(0x00));
            $ipad = str_repeat(chr(0x36), $blocksize);
            $opad = str_repeat(chr(0x5c), $blocksize);
            $hmac = pack('H*', $hashfunc(($key ^ $opad) . pack('H*', $hashfunc(($key ^ $ipad) . $str))));
            $signature = base64_encode($hmac);
        }

        return $signature;
    }


    ////////////////可以删了///////////////
    public static function send($mobile, $words, $type = 'notice')
    {
        //不要删！不要删！不要删！
        //测试环境发短信，请添加例外手机号！！！！！
        if (!in_array($mobile, self::$EX_MOBILES) && BASE_HOST != '.haocaisong.cn')
        {
            return FALSE;
        }

        //只是针对验证码 频率控制提前到：Crm2_Auth_Api::sendVerifyCode()
        if ($type == 'verifycode')
        {
//            $res = Safe_Api::checkVerifyCodeLimit($mobile);
//            if (!$res)
//            {
//                return FALSE;
//            }
        }
        else
        {
            $res = Safe_Api::checkNoticeLimit($mobile);
            if (!$res)
            {
                return FALSE;
            }
        }

        //http://web.1xinxi.cn/asmx/smsservice.aspx?name=test&pwd=112345&content=testmsg&mobile=13566677777,18655555555&stime=&sign=签名 &type=pt&extno
        if ($type == 'verifycode')
        {
            $params = array(
                'name' => '18811412450',
                'pwd' => 'AA5E71A41D48A5121254FC30DC07',
                'mobile' => $mobile,
                'content' => $words,
                'sign' => '好材',
                'type' => 'pt',
            );
        }
        else
        {
            $params = array(
                'name' => '好材短信子账号',
                'pwd' => 'AA5E71A41D48A5121254FC30DC07',
                'mobile' => $mobile,
                'content' => $words,
                'sign' => '好材',
                'type' => 'pt',
            );
        }

        $ret = Tool_Http::post('http://web.1xinxi.cn/asmx/smsservice.aspx', $params);
        Tool_Log::addFileLog('sms/verify_log_'.date('Ym'), $mobile.'-'.$words.'-'.$ret);
        return $ret;
    }
}