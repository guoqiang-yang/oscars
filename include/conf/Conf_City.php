<?php
/**
 * 城市
 */
class Conf_City
{
    const BEIJING = 101;
	const OTHER = 102;
    const TIANJIN = 120;
    const XIANGHE = 200;
    const WUHAN = 4201;
    const LANGFANG = 1310;
    const CHONGQING = 500;
    const CHENGDU = 5101;
    const QINGDAO = 3702;
    const OTHERCITY = 9999;


    public static $CITY = array(
		self::BEIJING => '北京',
        //self::XIANGHE => '香河',
        self::TIANJIN => '天津',
        //self::WUHAN => '武汉',
        self::LANGFANG => '廊坊',
        self::CHONGQING => '重庆',
        self::CHENGDU => '成都',
        self::QINGDAO => '青岛',
	);

    public static $OFFLINE_CITY = array(
        self::XIANGHE, self::WUHAN, self::LANGFANG,
    );
    
    public static $SELF_CITIES = array(
        self::BEIJING,
        self::OTHER,
        self::TIANJIN,
        self::CHONGQING,
        self::WUHAN,
        self::CHENGDU,
        self::QINGDAO,
    );
    
    private static $CITY_PINYIN_CODES = array(
        self::BEIJING => 'BJ',
        self::XIANGHE => 'XH',
        self::TIANJIN => 'TJ',
        self::WUHAN => 'WH',
        self::LANGFANG => 'LF',
        self::CHONGQING => 'CQ',
        self::CHENGDU => 'CD',
        self::QINGDAO => 'QD',
    );
    
    public static $CITY_PINYIN_FULL = array(
        self::BEIJING => 'beijing',
        self::TIANJIN => 'tianjin',
        self::WUHAN => 'wuhan',
        self::LANGFANG => 'langfang',
        self::CHONGQING => 'chongqing',
        self::CHENGDU => 'chengdu',
        self::QINGDAO => 'qingdao',
    );

    private static $CITY_PINYIN_CODES_2_ID = array(
        'BJ' => self::BEIJING,
        'XH' => self::XIANGHE,
        'TJ' => self::TIANJIN,
        'WH' => self::WUHAN,
        'LF' => self::LANGFANG,
        'CQ' => self::CHONGQING,
        'CD' => self::CHENGDU,
        'QD' => self::QINGDAO,
    );

    public static $CITY_POLICY = array(
        self::BEIJING => '满599免运费',
        self::TIANJIN => '满799免运费',
        self::WUHAN => '满799免运费',
        self::LANGFANG => '满999免运费',
        self::CHONGQING => '满799免运费',
        self::CHENGDU => '满1999免运费',
        self::QINGDAO => '满999免运费',
    );
    
    /**
     * 城市默认的中心坐标点.
     */
    private static $CITY_POI = array(
        self::BEIJING => array('lng' => '116.404', 'lat' => '39.915'),
        self::TIANJIN => array('lng' => '117.217487', 'lat' => '39.14482'),
        self::LANGFANG => array('lng' => '116.709261', 'lat' => '39.537075'),
        self::CHONGQING => array('lng' => '106.553091', 'lat' => '29.565'),
        self::CHENGDU => array('lng' => '104.074344', 'lat' => '30.666758'),
        self::QINGDAO => array('lng'=>'120.384284', 'lat'=>'36.101161'),
    );
    
    public static function cityPOI($cityId)
    {
        if (empty($cityId)) return self::$CITY_POI;
        
        $cityPois = self::$CITY_POI;
        
        return array_key_exists($cityId, self::$CITY_POI)? $cityPois[$cityId]: $cityPois[self::BEIJING];
    }
    
    public static function getCityName($cityId, $isShowID=false)
    {
        return isset(self::$CITY[$cityId]) ? self::$CITY[$cityId]: 
                    ($isShowID? $cityId: '');
    }

    public static function getCityPinyinCode($cityId)
    {
        return isset(self::$CITY_PINYIN_CODES[$cityId]) ? self::$CITY_PINYIN_CODES[$cityId]:'NONE';
    }

    
    public static function getAllCities()
    {
        return array(
            self::BEIJING => '北京',
            self::OTHER => '北京周边',
            self::XIANGHE => '香河',
            self::TIANJIN => '天津',
            self::WUHAN => '武汉',
            self::LANGFANG => '廊坊',
            self::CHONGQING => '重庆',
            self::CHENGDU => '成都',
            self::QINGDAO => '青岛',
        );
    }
    
    /**
     * 获取C端使用的城市列表
     */
    public static function getCityList4Customer()
    {
        $customerCities = array();
        
        // 默认城市列表
        $cityList = array();
        foreach(self::$CITY as $cityCode => $cityDesc)
        {
            if (in_array($cityCode, self::$OFFLINE_CITY)) continue;
            
            $cityUnit = new stdClass();
            $cityUnit->id = $cityCode;
            $cityUnit->name = $cityDesc;
            $cityUnit->pinyin = self::$CITY_PINYIN_FULL[$cityCode];
            $cityUnit->policy = self::$CITY_POLICY[$cityCode];
            
            $cityList[] = $cityUnit;
        }
        $_dfCustomerCitites = new stdClass();
        $_dfCustomerCitites->title = '城市';
        $_dfCustomerCitites->index = '★';
        $_dfCustomerCitites->list = $cityList;
        
        $customerCities['list'][] = $_dfCustomerCitites;
        
        return $customerCities;
    }
    
    public static function getCityIdByPinyinCode($cityCode)
    {
        return isset(self::$CITY_PINYIN_CODES_2_ID[$cityCode]) ? self::$CITY_PINYIN_CODES_2_ID[$cityCode]:0;
    }
    
    /**
     * 获取自营的城市.
     */
    public static function getSelfCities($getOffline=false)
    {
        $selfCityIds = $getOffline? self::$SELF_CITIES: array_diff(self::$SELF_CITIES, self::$OFFLINE_CITY);
        
        $selfCities = array();
        foreach($selfCityIds as $_cityId)
        {
            if (empty(self::$CITY[$_cityId])) continue;
            
            $selfCities[$_cityId] = self::$CITY[$_cityId];
        }
        
        return $selfCities;
    }

    /**
     *获取城市名称
     */
    public static function getCityNameByIds($cityIds)
    {
        if (!is_array($cityIds))
        {
            $cityIds = explode(',', $cityIds);
        }
        
        $cityNames = array();
        foreach($cityIds as $cityId)
        {
            $cityNames[$cityId] = self::getCityName($cityId, true);
        }
        
        return $cityNames;
    }

}
