<?php

/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/6/1
 * Time: 18:21
 */
class Conf_Fit
{
    public static function getHouseArea()
    {
        return self::$_HOUSE_AREA;
    }

    private static $_HOUSE_AREA = array(
        1 => '50㎡以下', 2 => '50-80㎡', 3 => '80-100㎡', 4 => '100-130㎡', 5 => '130㎡以上',
    );

    public static function getHouseStyle()
    {
        return self::$_HOUSE_STYLE;
    }

    private static $_HOUSE_STYLE = array(
        1 => '田园风格', 2 => '地中海', 3 => '美式风格', 4 => '欧式古典', 5 => '古典中式', 6 => '中西混搭', 7 => '现代简约',
    );

    public static function getHouseType()
    {
        return self::$_HOUSE_TYPE;
    }

    private static $_HOUSE_TYPE = array(
        1 => '一居', 2 => '二居', 3 => '三居', 4 => '大户型', 5 => '复式', 6 => '小户型', 7 => '别墅', 8 => '跃层', 9 => '公寓',
    );

    public static function getHouseSpace()
    {
        return self::$_HOUSE_SPACE;
    }

    private static $_HOUSE_SPACE = array(
        1 => '整套', 2 => '客厅', 3 => '餐厅', 4 => '卧室', 5 => '儿童房', 6 => '书房', 7 => '卫浴', 8 => '厨房', 9 => '阳台', 10 => '花园',
    );

    public static function getDesign()
    {
        return self::$_DESIGNS;
    }

    private static $_DESIGNS = array(
        1 => '中式风格', 2 => '现代风格', 3 => '欧式风格', 4 => '田园风格', 5 => '美式风格', 6 => '东南亚风格', 7 => '地中海风格',
    );

    public static function getFitStep()
    {
        return self::$_FIT_STEP;
    }

    private static $_FIT_STEP = array(
        1 => '收房验房', 2 => '装修预算', 3 => '装修合同', 4 => '选工长', 5 => '选设计师', 6 => '选装修公司', 7 => '选材料', 8 => '拆改', 9 => '水电', 10 => '防水', 11 => '泥瓦工', 12 => '水工', 13 => '油漆', 14 => '装修知识', 15 => '工程验收', 16 => '装修环保', 17 => '验收事项', 18 => '保洁', 19 => '成品安装', 20 => '搬家搬场', 21 => '去除甲醛',
    );

    public static function getMainMaterial()
    {
        return self::$_MAIN_MATERIAL;
    }

    private static $_MAIN_MATERIAL = array(
        1 => '地板砖', 2 => '木板砖', 3 => '水龙头', 4 => '浴霸', 5 => '开关电工', 6 => '玻璃', 7 => '门窗',
    );

    public static function getOtherMaterial()
    {
        return self::$_OTHER_MATERIAL;
    }

    private static $_OTHER_MATERIAL = array(
        1 => '水', 2 => '电', 3 => '木', 4 => '瓦', 5 => '油', 6 => '工具',
    );

    public static function getFitBudget()
    {
        return self::$_BUDGET;
    }

    private static $_BUDGET = array(
        1 => '2-3万', 2 => '3-5万', 3 => '5-8万', 4 => '8-10万', 5 => '10-15万', 6 => '15-20万', 7 => '20万以上',
    );

    public static function getAppointmentStep()
    {
        return self::$_APPOINTMENT_STEP;
    }
    private static $_APPOINTMENT_STEP = array(
        1 => '待审核',
        33 => '待处理',
        99 => '处理完成',
    );
}