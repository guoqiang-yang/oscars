<?php
/**
 * 在线支付相关配置
 */
class Conf_Pay
{
    private static $WEIXIN_PAY_PARAMS = array(
        /*
        Conf_City::LANGFANG => array( //廊坊 - 这个是好运的账号，用来测试
            'app' => array(
                'appid' => 'wx156a0c5984d85e40',
                'mcid' => '1494377412',
                'mkey' => '3765bc0ef43b43423ed5698e6dd27af6'
            ),
            'mp' => array(
                'appid' => 'wxc229c7682f1d2284',
                'secret' => 'ea446c0b26c0364d2fc2f48c05847850',
                'mcid' => '1284445501',
                'mkey' => '21ca9eb94d5e984bb03872b09eb441d6',
            )
        ),
        Conf_City::TIANJIN => array( //天津 - 这个是好运的账号，用来测试
            'app' => array(
                'appid' => 'wx34d1d513983b1072',
                'mcid' => '1492088722',
                'mkey' => '440cb5f7586ce6254b4cef1816054ba6'
            ),
            'mp' => array(
                'appid' => 'wxc229c7682f1d2284',
                'secret' => 'ea446c0b26c0364d2fc2f48c05847850',
                'mcid' => '1284445501',
                'mkey' => '21ca9eb94d5e984bb03872b09eb441d6',
            )
        ),
        */
        0 => array( //默认
            'app' => array(
                'appid' => 'wx00c192b586a56ead',
                'mcid' => '1336772501',
                'mkey' => '440cb5f7586ce6254b4cef1816054ba6'
            ),
            'mp' => array(
                'appid' => 'wxc229c7682f1d2284',
                'secret' => 'ea446c0b26c0364d2fc2f48c05847850',
                'mcid' => '1284445501',
                'mkey' => '21ca9eb94d5e984bb03872b09eb441d6',
            )
        )
    );

    private static $ALIPAY_PAY_PARAMS = array(
        0 => array( //默认
            //合作身份者id，以2088开头的16位纯数字
            'partner' => '2088121743537874',
            //收款支付宝账号，一般情况下收款账号就是签约账号
            'seller_id' => '2088121743537874',
            //商户的私钥（后缀是.pen）文件相对路径
            'private_key_path' => 'include/alipay/key/rsa_private_key.pem',
            //支付宝公钥（后缀是.pen）文件相对路径
            'ali_public_key_path' => 'include/alipay/key/alipay_public_key.pem',
            //签名方式 不需修改
            'sign_type' => 'rsa',
            //字符编码格式 目前支持 gbk 或 utf-8
            'input_charset' => 'utf-8',
            //ca证书路径地址，用于curl中ssl校验
            //请保证cacert.pem文件在当前文件夹目录中
            'cacert' => 'cacert.pem',
            //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
            'transport' => 'http'
        )
    );

    public static function getWeixinAppPayParams($cityId)
    {
        if (isset(self::$WEIXIN_PAY_PARAMS[$cityId]))
        {
            return self::$WEIXIN_PAY_PARAMS[$cityId]['app'];
        }
        else
        {
            return self::$WEIXIN_PAY_PARAMS[0]['app'];
        }
    }

    public static function getWeixinMpPayParams($cityId)
    {
        if (isset(self::$WEIXIN_PAY_PARAMS[$cityId]))
        {
            return self::$WEIXIN_PAY_PARAMS[$cityId]['mp'];
        }
        else
        {
            return self::$WEIXIN_PAY_PARAMS[0]['mp'];
        }
    }

    public static function getAlipayAppParams($cityId)
    {
        if (isset(self::$ALIPAY_PAY_PARAMS[$cityId]))
        {
            return self::$ALIPAY_PAY_PARAMS[$cityId];
        }
        else
        {
            return self::$ALIPAY_PAY_PARAMS[0];
        }
    }
}
