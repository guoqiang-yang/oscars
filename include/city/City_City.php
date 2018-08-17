<?php

/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 16/3/12
 * Time: 13:50
 */
class City_City
{
    private static $_CITY_ID_KEYS = 'shop_global_city_id';

    private static $_PLATFORM_KEY_MAPS = array(
        'shop' => 'shop_global_city_id',
        'toc' => 'toc_global_city_id',
    );
    private static $_COOKIE_KEY_MAPS = array(
        'shop' => 'city_id',
        'toc' => 'toc_h5_city_id',
    );

    public function setCity($cityId, $platform = 'shop')
    {
        $key = self::$_PLATFORM_KEY_MAPS[$platform];
        Data_Global::set($key, $cityId);
        $cookieKey = self::$_COOKIE_KEY_MAPS[$platform];
        setcookie($cookieKey, $cityId, time() + 8640000, '/');
    }

    public function getCity($platform = 'shop')
    {
        $key = self::$_PLATFORM_KEY_MAPS[$platform];
        $city = Data_Global::get($key);
        $cookieKey = self::$_COOKIE_KEY_MAPS[$platform];
        if (empty($city) && isset($_COOKIE[$cookieKey]) && !empty($_COOKIE[$cookieKey]))
        {
            $city = $_COOKIE[$cookieKey];
        }

        !$city && $city = 0;

        return $city;
    }

    public function setCityTimeStamp()
    {
        session_start();

        $_SESSION['choose_city_timestamp'] = time();
    }

    public function getCityTimeStamp()
    {
        session_start();

        return intval($_SESSION['choose_city_timestamp']);
    }
}