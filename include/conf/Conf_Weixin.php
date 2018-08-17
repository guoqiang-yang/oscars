<?php
/**
 * 微信支付配置信息
 * @author wangxuemin
 */
class Conf_Weixin {
    /* 微信统一下单URL */
    const UNIFIED_ORDER_URL = "https://api.mch.weixin.qq.com/pay/unifiedorder";
    /* 微信订单查询URL */
    const ORDER_QUERY_URL = "https://api.mch.weixin.qq.com/pay/orderquery";
    /* 微信关闭订单URL */
    const CLOSE_ORDER_URL = "https://api.mch.weixin.qq.com/pay/closeorder";
    /* 微信app支付通知Url */
    const APP_NOTIFY_URL = "http://app.api.v2.haocaisong.cn/v2.0/order/pay_callback_weixin";
    /* 微信公众账号支付通知Url */
    const MP_NOTIFY_URL = "http://shop.haocaisong.cn/paycallback/weixin.php";
    /* 服务主体信息（微信支付服务商） */
    private static $serviceParam = array(
        "app" => array(
            /* APPID */
            'appid' => 'wx00c192b586a56ead',
            /* 商户号 */
            'mcid' => '',
            /* 商户支付密钥 */
            'mkey' => ''
        ),
        "mp" => array(
            /* 公众账号ID */
            'appid' => 'wxc229c7682f1d2284',
            /* 开发者密码 */
            'secret' => 'ea446c0b26c0364d2fc2f48c05847850',
            /* 商户号 */
            'mcid' => '',
            /* 商户支付密钥 */
            'mkey' => ''
        )
    );
    /* 北京 */
    private static $beijingParam = array(
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
    );
    /* 各城市，添加城市根据下面格式追加即可（key代表城市ID） */
    private static $cityParams = array(
        /* 北京 */
        Conf_City::BEIJING => array(
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
        ),
        /* 重庆 */
        Conf_City::CHONGQING => array(
            "app" => array(
                'appid' => 'wx5c512a5c6be52bba',
                'mcid' => '1501702341',
                'mkey' => '45a838097d9ed179186e0947ad186137'
            ),
            "mp" => array(
                'appid' => 'wxc229c7682f1d2284',
                'secret' => 'ea446c0b26c0364d2fc2f48c05847850',
                'mcid' => '1500938841',
                'mkey' => '1idHleiuo98ns8s0leYqm5Und9ea0Pew'
            )
        ),
        /* 成都 */
        Conf_City::CHENGDU => array(
            "app" => array(
                'appid' => 'wx7f0b90f56b9a6716',
                'mcid' => '1501703441',
                'mkey' => 'e43ffb901e3d13e732452e60f23c9ed8'
            ),
            "mp" => array(
                'appid' => 'wxc229c7682f1d2284',
                'secret' => 'ea446c0b26c0364d2fc2f48c05847850',
                'mcid' => '1500966131',
                'mkey' => '2rdHleiuo98ns8s0leYqm5Und9ea0keu'
            )
        ),
        /* 青岛 */
        Conf_City::QINGDAO => array(
            'app' => array(
                'appid' => 'wx7e0c4fd46b370317',
                'mcid' => '1507967221',
                'mkey' => '68a833097d9yd199186e0947ad156139'
            ),
            'mp' => array(
                'appid' => 'wxc229c7682f1d2284',
                'secret' => 'ea446c0b26c0364d2fc2f48c05847850',
                'mcid' => '1284445501',
                'mkey' => '21ca9eb94d5e984bb03872b09eb441d6',
            )
        ),
    );
    
    /**
     * 获取服务主体信息
     * @author wangxuemin
     * @param string $type （app or mp）app或公众号配置，默认app
     * @return Array
     */
    public static function getServiceParam($type = 'app')
    {
        return self::$serviceParam[$type];
    }
    
    /**
     * 获取北京支付信息
     * @author wangxuemin
     * @param string $type （app or mp）app或公众号配置，默认app
     * @return Arrray
     */
    public static function getBeijingParam($type = 'app')
    {
        return self::$beijingParam[$type];
    }
    
    /**
     * 根据城市ID获取城市支付信息，如果没有支付城市默认取北京
     * @author wangxuemin
     * @param int $city_id 城市ID
     * @param string $type （app or mp）app或公众号配置，默认app
     * @return Array
     */
    public static function getCityParamById($city_id, $type = 'app')
    {
        if (isset(self::$cityParams[$city_id])) {
            return self::$cityParams[$city_id][$type];
        } else {
            return self::$cityParams[Conf_City::BEIJING][$type];
        }
    }
    
}