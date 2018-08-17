<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/3/28
 * Time: 16:54
 */
class Conf_Fee
{
    public static function getFixedInput($wid, $month)
    {
        if (empty($wid) || empty($month))
        {
            return 0;
        }

        return intval(self::$FIXED_INPUT[$wid][$month]);
    }

    public static function getStaffSalary($wid, $month)
    {
        if (empty($wid) || empty($month))
        {
            return 0;
        }

        return intval(self::$STAFF_SALARY[$wid][$month]);
    }

    public static function getOtherInput($wid, $month)
    {
        if (empty($wid) || empty($month))
        {
            return 0;
        }

        return intval(self::$OTHER_INPUT[$wid][$month]);
    }


    //库房固定费用
    private static $FIXED_INPUT = array(
        Conf_Warehouse::WID_3 => array(
            '2017-03' => 48045.23,
            '2017-04' => 48045.23,
            '2017-05' => 48172.95,
            '2017-06' => 50172.95,
        ),
        Conf_Warehouse::WID_4 => array(
            '2017-03' => 64031.9,
            '2017-04' => 64031.9,
            '2017-05' => 23053.81,
            '2017-06' => 29854.31,
        ),
        Conf_Warehouse::WID_5 => array(
            '2017-03' => 10039.73,
            '2017-04' => 10039.73,
            '2017-05' => 10083.4,
            '2017-06' => 10083.4,
        ),
        Conf_Warehouse::WID_6 => array(
            '2017-03' => 90003.87,
            '2017-04' => 90003.87,
            '2017-05' => 0,
            '2017-06' => 0,
        ),
        Conf_Warehouse::WID_TJ1 => array(
            '2017-03' => 21713.58,
            '2017-04' => 21713.58,
            '2017-05' => 28934.41,
            '2017-06' => 28974.41,
        ),
        Conf_Warehouse::WID_101 => array(
            '2017-05' => 6000,
            '2017-06' => 4629.16,
        ),
        Conf_Warehouse::WID_LF1 => array(
            '2017-05' => 1740,
            '2017-06' => 1671.51,
        ),
    );

    //人员工资
    private static $STAFF_SALARY = array(
        Conf_Warehouse::WID_3 => array(
            '2017-03' => 179418.94,
            '2017-04' => 194141.54,
            '2017-05' => 153399.65,
            '2017-06' => 153399.65,
        ),
        Conf_Warehouse::WID_4 => array(
            '2017-03' => 89361.38,
            '2017-04' => 142651.79,
            '2017-05' => 122245.83,
            '2017-06' => 122245.83,
        ),
        Conf_Warehouse::WID_5 => array(
            '2017-03' => 57371.53,
            '2017-04' => 121924.36,
            '2017-05' => 53119.52,
            '2017-06' => 53119.52,
        ),
        Conf_Warehouse::WID_6 => array(
            '2017-03' => 65788.68,
            '2017-04' => 65788.68,
            '2017-05' => 0,
            '2017-06' => 0,
        ),
        Conf_Warehouse::WID_TJ1 => array(
            '2017-03' => 77361.22,
            '2017-04' => 63030.77,
            '2017-05' => 114679.41,
            '2017-06' => 114679.41,
        ),
        Conf_Warehouse::WID_101 => array(
            '2017-05' => 23651.3,
            '2017-06' => 23651.3,
        ),
        Conf_Warehouse::WID_LF1 => array(
            '2017-05' => 34541.74,
            '2017-06' => 34541.74,
        ),
    );

    //其他变动成本
    private static $OTHER_INPUT = array(
        Conf_Warehouse::WID_3 => array(
            '2017-03' => 54877.88,
            '2017-04' => 5704.48,
            '2017-05' => 49031.17,
            '2017-06' => 45969.32,
        ),
        Conf_Warehouse::WID_4 => array(
            '2017-03' => 56684.33,
            '2017-04' => 3060.28,
            '2017-05' => 40720.17,
            '2017-06' => 88270.94,
        ),
        Conf_Warehouse::WID_5 => array(
            '2017-03' => 13965.97,
            '2017-04' => 3826,
            '2017-05' => 7939.49,
            '2017-06' => 4337.98,
        ),
        Conf_Warehouse::WID_6 => array(
            '2017-03' => 206389.72 ,
            '2017-04' => 0,
            '2017-05' => 0,
            '2017-06' => 0,
        ),
        Conf_Warehouse::WID_TJ1 => array(
            '2017-03' => 19666.16,
            '2017-04' => 9524.92,
            '2017-05' => 14867.99,
            '2017-06' => 102839.26,
        ),
        Conf_Warehouse::WID_101 => array(
            '2017-05' => 31435.06,
            '2017-06' => 11715.19,
        ),
        Conf_Warehouse::WID_LF1 => array(
            '2017-05' => 74341,
            '2017-06' => 10924.11,
        ),
    );
}