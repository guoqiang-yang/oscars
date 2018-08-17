<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/4/10
 * Time: 16:34
 */
class Conf_Crm
{
    const VISIT_TYPE_MOBILE = 1;
    const VISIT_TYPE_WEIXIN = 2;
    const VISIT_TYPE_QQ = 3;
    const VISIT_TYPE_SMS = 4;
    const VISIT_TYPE_SCENE = 5;
    const VISIT_TYPE_OTHER = 99;
    //其他拜访分类
    private static $TYPE_LIST = array(
        self::VISIT_TYPE_SCENE => '现场拜访',
        self::VISIT_TYPE_MOBILE => '电话拜访',
        self::VISIT_TYPE_WEIXIN => '微信拜访',
        self::VISIT_TYPE_QQ => 'QQ拜访',
        self::VISIT_TYPE_SMS => '短信拜访',
        self::VISIT_TYPE_OTHER => '其他',
    );

    public static function getTypeList()
    {
        return self::$TYPE_LIST;
    }

    //7天之内的拜访可以编辑
    const EDIT_VISIT_INTERVAL = 604800;

    //提醒间隔
    private static $REMIND_LIST = array(
        1 => array('name' => '不提醒', 'interval' => 0),
        2 => array('name' => '提前30分钟', 'interval' => 1800),
        3 => array('name' => '提前1小时', 'interval' => 3600),
        4 => array('name' => '提前2小时', 'interval' => 7200),
        5 => array('name' => '提前6小时', 'interval' => 21600),
        6 => array('name' => '提前1天', 'interval' => 86400),
    );

    public static function getRemindList()
    {
        return self::$REMIND_LIST;
    }

    private static $RELATION_LIST = array(
        1 => '父母',
        2 => '配偶',
        3 => '子女',
        4 => '兄弟姐妹',
        5 => '同事',
        6 => '朋友',
        7 => '同学',
        8 => '其他',
    );

    public static function getRelationList()
    {
        return self::$RELATION_LIST;
    }

    private static $NEED_CERT_CITY = array(
        Conf_City::BEIJING => true,
        Conf_City::TIANJIN => true,
        Conf_City::LANGFANG => true,
        Conf_City::CHONGQING => false,
        Conf_City::CHENGDU => false
    );

    public static function getNeedCertCity()
    {
        return self::$NEED_CERT_CITY;
    }
}