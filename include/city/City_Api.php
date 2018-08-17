<?php

/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 16/3/12
 * Time: 13:49
 */
class City_Api extends Base_Api
{
    private static $adminUser = array(9196, 65648, 40456);

    public static function getCityList($showAll = FALSE, $uid = 0)
    {
        $list = Conf_City::$CITY;
        if (!$showAll)
        {
            //unset($list[Conf_City::WUHAN]);
            foreach (Conf_City::$OFFLINE_CITY as $_cityId)
            {
                unset ($list[$_cityId]);
            }
        }

        return $list;
    }

    public static function setCity($cityId, $platform = 'shop')
    {
        if (!array_key_exists($cityId, Conf_City::$CITY))
        {
            $cityId = Conf_City::BEIJING;
        }
        $cc = new City_City();
        $cc->setCity($cityId, $platform);

        return $cityId;
    }

    public static function getCity($platform = 'shop')
    {
        $cc = new City_City();
        $cityId = $cc->getCity($platform);

        $isDefault = FALSE;
        !$cityId && $isDefault = TRUE;
        !$cityId && $cityId = Conf_City::BEIJING;
        $cityName = Conf_City::$CITY[$cityId];

        return array('is_default' => $isDefault, 'city_id' => $cityId, 'city_name' => $cityName);
    }

    public static function getDefaultWarehouse()
    {
        $cc = new City_City();
        $cityId = $cc->getCity();
        !$cityId && $cityId = Conf_City::BEIJING;

        return Conf_Warehouse::$WAREHOUSE_CITY[$cityId][0];
    }

    public static function setCityTimeStamp()
    {
        $cc = new City_City();

        $cc->setCityTimeStamp();
    }

    public static function getCityTimeStamp()
    {
        $cc = new City_City();

        return $cc->getCityTimeStamp();
    }

    public static function bigBeijing($cityId)
    {
        if (in_array($cityId, array(Conf_City::BEIJING, Conf_City::OTHER)))
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }
}