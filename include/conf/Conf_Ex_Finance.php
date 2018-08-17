<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/7/4
 * Time: 15:37
 */
class Conf_Ex_Finance
{
    const TYPE_PERSONAL = 1;
    const TYPE_COMPANY = 2;

    const APPLY_STEP_NEW = 1;
    const APPLY_STEP_THIRD_PARTY_PASS = 11;
    const APPLY_STEP_HAOCAI_PASS = 22;
    const APPLY_STEP_CREDIT_GRANTING = 33;
    const APPLY_STEP_THIRD_PARTY_REJECTED = -1;
    const APPLY_STEP_HAOCAI_REJECTED = -11;

    private static $STEP_SHOW = array(
        self::APPLY_STEP_NEW => '待第三方授信',
        self::APPLY_STEP_THIRD_PARTY_PASS => '第三方授信通过，待好材审核',
        self::APPLY_STEP_HAOCAI_PASS => '好材审核通过，待授信',
        self::APPLY_STEP_CREDIT_GRANTING => '已授信',
        self::APPLY_STEP_THIRD_PARTY_REJECTED => '第三方授信未通过',
        self::APPLY_STEP_HAOCAI_REJECTED => '好材审核未通过',
    );

    public static function getApplyStep($step = 0)
    {
        if ($step != 0)
        {
            return self::$STEP_SHOW[$step];
        }

        return self::$STEP_SHOW;
    }


    const HISTORY_OBJTYPE_ORDER         = 1;    //订单
    const HISTORY_OBJTYPE_REFUND        = 2;    //退款
    const HISTORY_OBJTYPE_REPAYMENT     = 3;    //还款
    const HISTORY_OBJTYOE_TRANS_AMOUNT  = 4;   //转余额
    const HISTORY_OBJTYPE_CANCEL_ORDER  = 5;    //取消订单

    private static $_HISTORY_OBJTYPE = array(
        self::HISTORY_OBJTYPE_ORDER         => '订单付款',
        self::HISTORY_OBJTYPE_REFUND        => '订单退款',
        self::HISTORY_OBJTYPE_REPAYMENT     => '用户还款',
        self::HISTORY_OBJTYOE_TRANS_AMOUNT  => '转余额',
        self::HISTORY_OBJTYPE_CANCEL_ORDER  => '订单取消',
    );
    
    public static function getObjType()
    {
        return self::$_HISTORY_OBJTYPE;
    }

    const MAX_OVERFLOW_AMOUNT = 100000;         //最大超出金额：1000

    const RATE_PER_DAY = 0.00035;               //日息0.035%

    public static function calAccrualByPrice($price)
    {
        //$price是分，转换成厘，计算之后四舍五入
        return round($price * 10 * self::RATE_PER_DAY);
    }
}