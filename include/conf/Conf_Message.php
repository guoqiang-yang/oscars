<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/4/11
 * Time: 14:22
 */
class Conf_Message
{
    //1:系统消息,2:售后工单,3:退货单,4:换货单,5:生日提醒,6:日程提醒
    const MESSAGE_TYPE_SYS = 1;
    const MESSAGE_TYPE_AFTERSALE = 2;
    const MESSAGE_TYPE_REFUND = 3;
    const MESSAGE_TYPE_EXCHANGE = 4;
    const MESSAGE_TYPE_BIRTHDAY_REMIND = 5;
    const MESSAGE_TYPE_SCHEDULE_REMIND = 6;
    const MESSAGE_TYPE_APPOINTMENT = 7;
    const MESSAGE_TYPE_KJL_DESIGN = 8;

    private static $MESSAGE_TYPE = array(
        self::MESSAGE_TYPE_SYS => '系统消息',
        self::MESSAGE_TYPE_AFTERSALE => '售后工单',
        self::MESSAGE_TYPE_REFUND => '退货单',
        self::MESSAGE_TYPE_EXCHANGE => '换货单',
        self::MESSAGE_TYPE_BIRTHDAY_REMIND => '生日提醒',
        self::MESSAGE_TYPE_SCHEDULE_REMIND => '日程提醒',
        self::MESSAGE_TYPE_APPOINTMENT => '预约提醒',
        self::MESSAGE_TYPE_KJL_DESIGN => '户型装修提醒',
    );

    public static function getMessageType()
    {
        return self::$MESSAGE_TYPE;
    }
}