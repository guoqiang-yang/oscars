<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/10/14
 * Time: 上午10:57
 * 用户消息的推送
 */
class Conf_User_Msg
{
    public static $FIRST_LG = 'first_lg';

    //消息类型
    public static $MSG_ORDER_SEND = 1;//订单发货
    public static $MSG_ORDER_FINISH = 2;//订单完成
    public static $MSG_COUPON = 3;//奖励到账通知
    public static $MSG_REFUND = 4;//退款到账通知
    public static $MSG_SYSTEM = 5;//系统通知（注册）
    public static $MSG_SYSTEM_ERROR = 6;//系统通知（故障）
    public static $MSG_TYPE = array(
        1 => '订单已发货',
        2 => '订单已完成',
        3 => '奖励到账通知',
        4 => '退款到账通知',
        5 => '系统通知',
        6 => '系统通知',
    );


    //消息类型（一级）
    public static $MSG_CX = 1;//促销
    public static $MSG_WL = 2;//物流
    public static $MSG_ZC = 3;//资产
    public static $MSG_XT = 4;//系统
    public static $MSG_CATE1 = array(
        1 => 'last_rq_cx',
        2 => 'last_rq_wl',
        3 => 'last_rq_zc',
        4 => 'last_rq_xt',
    );

    //具体操作类型描述
    public static $MSG_DESC = array(
        1 => '您的订单{ocode}已发货，将由配送员 {name} 为您送货，联系电话：{mobile} 感谢您对好材的支持！',
        2 => '您的订单{ocode}已完成，感谢您对好材的支持，如您对我们的产品或服务有任何问题请拨打客服热线：4000585788！',
        3 => '您获得的{amount}元现金券礼包已到账，请您到“我的”-“我的优惠”中查看，谢谢您对好材的支持！',
        4 => '您的订单{ocode}的{amount}元退款已到账，请您到“我的”中查看余额，谢谢您对好材的支持！',
        5 => '感谢您注册好材，我们努力为您提供正品低价的装修辅材服务！',
        6 => '系统发生故障，好材程序猿正在拼命恢复中，给您造成的不变请您谅解！',
    );
}