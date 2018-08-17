<?php
/**
 * 支付宝支付配置信息
 * @author wangxuemin
 *
 */
class Conf_Ali
{
    /* 城市支付配置信息（key代表城市ID），各城市商户秘钥和支付宝公钥pem文件处北京外，
     * 都会在放在 （include/alipay/key/）下以城市city_id为文件名的文件夹下  
     */
    private static $cityParams = array(
        Conf_City::BEIJING => array(
            /* 合作身份者id，以2088开头的16位纯数字 */
            "partner" => "2088121743537874",
            /* 收款支付宝账号，一般情况下收款账号就是签约账号 */
            "seller_id" => "2088121743537874",
            /* 商户的私钥（后缀是.pem）文件相对路径 */
            "private_key_path" => "include/alipay/key/rsa_private_key.pem",
            /* 支付宝公钥（后缀是.pem）文件相对路径 */
            "ali_public_key_path" => "include/alipay/key/alipay_public_key.pem",
            /* 签名方式 有rsa和rsa2，rsa2需PHP5.5版本及以上。目前使用rsa */
            "sign_type" => "RSA",
            /* 字符编码格式 目前支持 gbk 或 utf-8 */
            "input_charset" => "utf-8",
            /* 请保证cacert.pem文件在当前文件夹目录中 */
            "cacert" => "cacert.pem",
            /* 访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http */
            "transport" => "http",
            /* 城市 */
            "city_name" => "北京",
            /* 城市ID */
            "city_id" => 101,
            /* app接口支付seller_id（支付宝商户号） */
            "app_seller_id" => "haocai2015@qq.com"
        )/* ,
        120 => array(
            "partner" => "",
            "seller_id" => "",
            "private_key_path" => "",
            "ali_public_key_path" => "",
            "sign_type" => "RSA",
            "input_charset" => "utf-8",
            "cacert" => "cacert.pem",
            "transport" => "http",
            "city_name" => "天津",
            "city_id" => 120,
            "app_seller_id" => ""
        )*/,
        Conf_City::CHONGQING => array(
            "partner" => "2088031672959600",
            "seller_id" => "2088031672959600",
            "private_key_path" => "include/alipay/key/500/rsa_private_key.pem",
            "ali_public_key_path" => "include/alipay/key/500/alipay_public_key.pem",
            "sign_type" => "RSA",
            "input_charset" => "utf-8",
            "cacert" => "cacert.pem",
            "transport" => "http",
            "city_name" => "重庆",
            "city_id" => Conf_City::CHONGQING,
            "app_seller_id" => "chongqinghaocai@haocaisong.cn"
        ),
        Conf_City::CHENGDU => array(
            "partner" => "2088031679825227",
            "seller_id" => "2088031679825227",
            "private_key_path" => "include/alipay/key/5101/rsa_private_key.pem",
            "ali_public_key_path" => "include/alipay/key/5101/alipay_public_key.pem",
            "sign_type" => "RSA",
            "input_charset" => "utf-8",
            "cacert" => "cacert.pem",
            "transport" => "http",
            "city_name" => "成都",
            "city_id" => Conf_City::CHENGDU,
            "app_seller_id" => "chengdouhaocai@haocaisong.cn"
        ),
        Conf_City::QINGDAO => array(
            "partner" => "2088131453039434",
            "seller_id" => "2088131453039434",
            "private_key_path" => "include/alipay/key/3702/rsa_private_key.pem",
            "ali_public_key_path" => "include/alipay/key/3702/alipay_public_key.pem",
            "sign_type" => "RSA",
            "input_charset" => "utf-8",
            "cacert" => "cacert.pem",
            "transport" => "http",
            "city_name" => "青岛",
            "city_id" => Conf_City::QINGDAO,
            "app_seller_id" => "qingdaohc@haocaisong.cn"
        )
    );
    
    /**
     * 根据城市ID获取城市信息，如果没有支付城市默认取北京
     * @author wangxuemin
     * @param int $city_id 城市ID
     * @return Array
     */
    public static function getCityParamById($city_id)
    {
        if (isset(self::$cityParams[$city_id])) {
            return self::$cityParams[$city_id];
        } else {
            return self::$cityParams[Conf_City::BEIJING];
        }
    }
}