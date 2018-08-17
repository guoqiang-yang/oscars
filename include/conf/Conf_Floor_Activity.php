<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/9/14
 * Time: 下午6:05
 */
class Conf_Floor_Activity {
    public static $PRICE = array(0,1,2,3,4,5,6,7,8,9);
    const FLOOR_WATER = 1;
    const FLOOR_ELET = 2;
    const FLOOR_SWD = 3;
    const FLOOR_TILE = 4;
    const FLOOR_OIL = 5;
    const FLOOR_TOOL = 6;
    public static $FLOOR_TYPE = array(
        self::FLOOR_WATER => '水工材料',
        self::FLOOR_ELET => '电工材料',
        self::FLOOR_SWD => '木工材料',
        self::FLOOR_TILE => '瓦工材料',
        self::FLOOR_OIL => '油工材料',
        self::FLOOR_TOOL => '工具材料',
    );
    const PIC_BIG = 1;
    const PIC_ONE = 2;
    const PIC_TWO = 3;
    const PIC_THREE = 4;
    const PIC_FOUR = 5;
    const PIC_FIVE = 6;
    const PIC_SIX = 7;
    const PIC_SEVEN = 8;
    public static $PICTURE = array(
        self::PIC_BIG => '大图',
        self::PIC_ONE => '图1',
        self::PIC_TWO => '图2',
        self::PIC_THREE => '图3',
        self::PIC_FOUR => '图4',
        self::PIC_FIVE => '图5',
        self::PIC_SIX => '图6',
        self::PIC_SEVEN => '图7',
    );
    const TYPE_TOPIC = 1;
    const TYPE_PRO = 2;
    public static $TYPE = array(
        self::TYPE_TOPIC => '外部活动专题页',
        self::TYPE_PRO => '应用内部商品详情页',
    );
    const MARK_HOT = 1;
    const MARK_SPECIAL = 2;
    const MARK_BOOM = 3;
    const MARK_NEW = 4;
    public static $MARK = array(
        self::MARK_HOT => '热卖',
        self::MARK_SPECIAL => '特价',
        self::MARK_BOOM => '爆款',
        self::MARK_NEW => '最新',
    );
}
