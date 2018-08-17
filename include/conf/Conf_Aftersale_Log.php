<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/8/31
 * Time: 下午5:33
 */
class Conf_Aftersale_Log
{

    const STATUS_CREATE = 1;
    const STATUS_AFTER_CREATE = 2;
    const STATUS_NEW = 3;
    const STATUS_UNDEAL = 4;
    const STATUS_DEAL = 5;
    const STATUS_FINISH = 6;
    public static $STATUS = array(
        self::STATUS_CREATE=> '已创建',
        self::STATUS_AFTER_CREATE=> '(new)待处理',
        self::STATUS_NEW => '待处理',
        self::STATUS_UNDEAL => '处理中',
        self::STATUS_DEAL => '已处理',
        self::STATUS_FINISH => '已关闭',
    );

    const METHOD_NO = 1;
    const METHOD_YES = 2;
    const METHOD_BACK = 3;
    const METHOD_CLOSE = 4;
    public static $DEAL_METHOD = array(
        self::METHOD_NO => '未处理完成',
        self::METHOD_YES => '处理完成',
    );
    public static $DEAL_METHOD_DONE = array(
        self::METHOD_BACK => '尚未完成',
        self::METHOD_CLOSE => '关闭工单',
    );

    const ACTION_NEW = 1;
    const ACTION_UNDEAL = 2;
    const ACTION_UNASSIGN = 3;
    const ACTION_DEAL = 4;
    const ACTION_FINISH = 5;
    public static $ACTION = array(
        self::ACTION_NEW => '创建工单',
        self::ACTION_UNDEAL => '指派',
        self::ACTION_UNASSIGN => '不指派',
        self::ACTION_DEAL => '处理',
        self::ACTION_FINISH => '关闭',
    );
}