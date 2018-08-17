<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/3/2
 * Time: 11:31
 */
class Conf_Comment
{
    const COMMENT_LEVEL_GOOD = 1;     //好评
    const COMMENT_LEVEL_MEDIUM = 2;   //中评
    const COMMENT_LEVEL_BAD = 3;      //差评

    public static $COMMENT_DESC = array(
        self::COMMENT_LEVEL_GOOD => '好评',
        self::COMMENT_LEVEL_MEDIUM => '中评',
        self::COMMENT_LEVEL_BAD => '差评',
    );

    public static $COMMENT_TAGS = array(
        //好评标签：配送服务非常好、搬运服务非常好、材料质量非常好、配货包装非常好、商城体验非常好、客服服务非常好。
        self::COMMENT_LEVEL_GOOD => array(
            1 => '配送服务非常好',
            2 => '搬运服务非常好',
            3 => '材料质量非常好',
            4 => '配货包装非常好',
            5 => '商城体验非常好',
            6 => '客服服务非常好',
        ),
        //中评标签：配送服务有待提升、搬运服务有待提升、材料质量一般、配货包装一般、商城体验一般、客服服务一般。
        self::COMMENT_LEVEL_MEDIUM => array(
            31 => '配送服务有待提升',
            32 => '搬运服务有待提升',
            33 => '材料质量一般',
            34 => '配货包装一般',
            35 => '商城体验一般',
            36 => '客服服务一般',
        ),
        //差评标签：配送服务有延迟、搬运服务态度差、材料质量有问题、存在错配漏配问题、商城使用有问题、客服服务态度差。
        self::COMMENT_LEVEL_BAD => array(
            61 => '配送服务有延迟',
            62 => '搬运服务态度差',
            63 => '材料质量有问题',
            64 => '存在错配漏配问题',
            65 => '商城使用有问题',
            66 => '客服服务态度差',
        ),
    );


    //取消订单原因
    //订单不能按时送达、订单配送信息有误、材料买错了、重复下单/误下单、其他渠道价格更低、不想买了、其他原因
    public static $CANCEL_REASONS = array(
        1 => '订单不能按时送达',
        2 => '订单配送信息有误',
        3 => '材料买错了',
        4 => '重复下单/误下单',
        5 => '其他渠道价格更低',
        6 => '不想买了',
        7 => '其他原因',
    );

}