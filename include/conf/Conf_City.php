<?php
/**
 * 城市
 */
class Conf_City
{
    const BEIJING = 101;

    private static $CITY_INFOS = array(
        self::BEIJING   => array('cn'=>'北京', 'py'=>'beijing', 'code'=>'BJ', 'poi'=>array('lng'=>'116.404', 'lat'=>'39.915')),
    );
    
    
    private static $OFFLINE_CITY = array(
        //self::BEIJING
    );

    /**
     * 获取城市信息.
     */
    public static function getByCityId($cityId, $key='')
    {
        $cityInfo = array_key_exists($cityId, self::$CITY_INFOS)? self::$CITY_INFOS[$cityId]: array();
        
        if (empty($key))
        {
            return $cityInfo;
        }
        else
        {
            return array_key_exists($key, $cityInfo)? $cityInfo[$key]: '';
        }
    }
    
    /**
     * 获取Cookie中使用的city-key.
     */
    public static function getKey4Cookie($platfome)
    {
        $conf = array(
            'sa'    => '_admin_city',       //sa管理后台
        );
        
        return array_key_exists($platfome, $conf)? $conf[$platfome]: '_err_city';
    }

}
