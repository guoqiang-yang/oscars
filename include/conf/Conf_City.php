<?php
/**
 * 城市
 */
class Conf_City
{
    const BEIJING = 110;

    private static $CITY_INFOS = array(
        self::BEIJING   => array('cn'=>'北京', 'py'=>'beijing', 'code'=>'BJ', 'poi'=>array('lng'=>'116.404', 'lat'=>'39.915')),
    );
    
    
    private static $OFFLINE_CITY = array(
        //self::BEIJING
    );

    public static function isCityExist($cityId)
    {
        return array_key_exists($cityId, self::$CITY_INFOS)? true: false;
    }
    
    public static function getAllCities($key='')
    {
        $allCities = array();
        
        if (!empty($key))
        {
            foreach(self::$CITY_INFOS as $cityId => $cityInfo)
            {
                $allCities[$cityId] = $cityInfo[$key];
            }
        }
        else
        {
            $allCities = self::$CITY_INFOS;
        }
        
        return $allCities;
    }
    
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
     * 获取多个城市的名称.
     */
    public static function getCityCnamesByCityIds($cityIds)
    {
        $_cityIds = $cityIds;
        
        if (is_string($cityIds))
        {
            $_cityIds = explode(',', $cityIds);
        }
        
        $cnames = array();
        foreach($_cityIds as $cityId)
        {
            $cnames[] = self::getByCityId($cityId, 'cn');
        }
        
        return $cnames;
    }
}
