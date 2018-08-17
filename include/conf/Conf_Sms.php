<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/11/30
 * Time: 11:02
 */
class Conf_Sms
{
    const LOGIN_REGISTER_KEY = 1;
    const COUPON_REMIND_KEY = 2;
    const CERTIFICATE_PASS_KEY = 3;
    const CERTIFICATE_DENY_KEY = 4;
    const CERTIFICATE_IDENTITY_CHANGED_KEY = 5;
    const RECHARGE_SUCC_KEY = 6;
    const CONSUME_SUCC_KEY = 7;
    const REFUND_SUCC_KEY = 8;
    const DELIVERY_SUCC_KEY = 9;
    const STAFF_USER_SMS_KEY = 10;

    //https://dysms.console.aliyun.com/dysms.htm?spm=5176.8195934.907839.sms8.5ed7ed90WX7V1d#/develop/template
    private static $_ALIYUN_TEMPLATE_ID_CONF = array(
        self::LOGIN_REGISTER_KEY => 'SMS_114385418',
        self::COUPON_REMIND_KEY => 'SMS_115390643',
        self::CERTIFICATE_PASS_KEY => 'SMS_115385612',
        self::CERTIFICATE_DENY_KEY => 'SMS_115380664',
        self::CERTIFICATE_IDENTITY_CHANGED_KEY => 'SMS_115390589',
        self::RECHARGE_SUCC_KEY => 'SMS_115380666',
        self::CONSUME_SUCC_KEY => 'SMS_115390592',
        self::REFUND_SUCC_KEY => 'SMS_115380670',
        self::DELIVERY_SUCC_KEY => 'SMS_115390593',
        self::STAFF_USER_SMS_KEY => 'SMS_133971086',
    );

    private static $MESSAGE_CONF = array(
        self::LOGIN_REGISTER_KEY => '您的验证码是：${code}，如非本人操作，请勿泄露。',
        self::COUPON_REMIND_KEY => '亲爱的老板，您有${amount}元好材优惠券即将失效，赶快使用，机不可失哦！关注官微"好材"或详询4000585788',
        self::CERTIFICATE_PASS_KEY => '您的实名认证已通过，快去买您想要的材料吧！',
        self::CERTIFICATE_DENY_KEY => '很遗憾！您的实名认证未通过！',
        self::CERTIFICATE_IDENTITY_CHANGED_KEY => '您的客户类型已修改，可以显示价格了，快去买您想要的材料吧！',
        self::RECHARGE_SUCC_KEY => '亲爱的工长，您已充值成功，请登录好材APP查询，如有问题请致电好材客服热线400-058-5788，感谢您对好材的支持，祝您生活愉快！',
        self::CONSUME_SUCC_KEY => '亲爱的工长，您已消费成功，余额${balance}元，请登录好材APP查询，如有问题请致电好材客服热线400-058-5788，感谢您对好材的支持，祝您生活愉快！',
        self::REFUND_SUCC_KEY => '亲爱的工长，您的订单已经完成退款，请您留意查收好材账户!如有问题请致电好材客服热线400-058-5788，感谢您对好材的支持，祝您生活愉快！',
        self::DELIVERY_SUCC_KEY => '亲爱的工长，您购买的辅材已出库开始派送，请您保持电话畅通，如有问题请致电好材客服热线400-058-5788，感谢您对好材的支持，祝您生活愉快！',
        self::STAFF_USER_SMS_KEY => '您申请的${status},获取凭证码${code}',
    );

    public static function isVerifyMessage($key)
    {
        $arr = array(
            self::LOGIN_REGISTER_KEY,
        );

        return in_array($key, $arr);
    }


    public static function getAliyunTemplateId($key)
    {
        return self::$_ALIYUN_TEMPLATE_ID_CONF[$key];
    }

    public static function getMessage($key, $para = array())
    {
        $msg = self::$MESSAGE_CONF[$key];
        if (!empty($para))
        {
            $keys = array();
            $values = array();
            foreach ($para as $key => $value)
            {
                $keys[] = '${' . $key . '}';
                $values[] = $value;
            }

            $msg = str_replace($keys, $values, $msg);
        }

        return $msg;
    }
}