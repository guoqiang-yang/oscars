<?php
/**
 * 地点配置
 */
class Conf_Area
{
    const Separator_Construction = '###';
    
    public static $CITY = array(
        Conf_City::BEIJING => '北京',
        Conf_City::OTHER => '北京周边',
        Conf_City::TIANJIN => '天津',
        Conf_City::LANGFANG => '廊坊',
        Conf_City::CHONGQING => '重庆',
        conf_city::CHENGDU => '成都',
        Conf_City::QINGDAO => '青岛',
        Conf_City::OTHERCITY => '其他省市',
    );

    public static $DISTRICT = array(
        Conf_City::BEIJING => array(
            1001 => '东城区',
            1002 => '西城区',
            1003 => '石景山区',
            1004 => '朝阳区',
            1005 => '丰台区',
            1006 => '海淀区',
            1007 => '门头沟区',
            1008 => '房山区',
            1009 => '通州区',
            1010 => '顺义区',
            1011 => '昌平区',
            1012 => '大兴区',
            1013 => '怀柔区',
            1014 => '平谷区',
            1015 => '密云县',
            1016 => '延庆县',
        ),
        Conf_City::OTHER => array(
            2001 => '大厂',
            2002 => '香河',
            2003 => '燕郊',
            2013 => '三河',
            2004 => '固安',
            2005 => '涿州',
            2006 => '怀来',
            2007 => '张家口',
            2008 => '北京周边其他',
        ),
        200 => array(
            20001 => '香河',
        ),
        Conf_City::TIANJIN => array(
            120101 => '和平区',
            120102 => '河东区',
            120103 => '河西区',
            120104 => '南开区',
            120105 => '河北区',
            120106 => '红桥区',
            120110 => '东丽区',
            120111 => '西青区',
            120112 => '津南区',
            120113 => '北辰区',
            120114 => '武清区',
            120115 => '宝坻区',
            120116 => '滨海新区',
            120221 => '宁河',
            120223 => '静海',
            120225 => '蓟县',
        ),
        Conf_City::WUHAN => array(
            420102 => '江岸区',
            420103 => '江汉区',
            420104 => '硚口区',
            420105 => '汉阳区',
            420106 => '武昌区',
            420107 => '青山区',
            420111 => '洪山区',
            420112 => '东西湖区',
            420113 => '汉南区',
            420114 => '蔡甸区',
            420115 => '江夏区',
            420116 => '黄陂区',
            420117 => '新洲区',
            420199 => '武汉周边区',
        ),
        Conf_City::LANGFANG => array(
            131002 => '安次区',
            131003 => '广阳区',
            131023 => '永清县',
            131025 => '大城县',
            131026 => '文安县',
            131081 => '霸州市',
        ),
        Conf_City::CHONGQING => array(
            500101  => '万州区',
            500102  => '涪陵区',
            500103  => '渝中区',
            500104  => '大渡口区',
            500105  => '江北区',
            500106  => '沙坪坝区',
            500107  => '九龙坡区',
            500108  => '南岸区',
            500109  => '北碚区',
            500110  => '綦江区',
            500111  => '大足区',
            500112  => '渝北区',
            500113  => '巴南区',
            500114  => '黔江区',
            500115  => '长寿区',
            500116  => '江津区',
            500117  => '合川区',
            500118  => '永川区',
            500119  => '南川区',
            500120  => '璧山区',
            500151  => '铜梁区',
            500152  => '潼南区',
            500153  => '荣昌区',
            500154  => '开州区',
        ),
        Conf_City::CHENGDU => array(
            510102  => '高新区',
            510104  => '锦江区',
            510105  => '青羊区',
            510106  => '金牛区',
            510107  => '武侯区',
            510108  => '成华区',
            510156  => '天府新区',
            510112  => '龙泉驿区',
            510113  => '青白江区',
            510114  => '新都区',
            510115  => '温江区',
            510116  => '双流区',
            510124  => '郫都区',
        ),
        Conf_City::QINGDAO => array(
            370202 => '市南区',
            370203 => '市北区',
            370211 => '黄岛区',
            370212 => '崂山区',
            370213 => '李沧区',
            370214 => '城阳区',
            370215 => '即墨区',
            370281 => '胶州市',
            370283 => '平度市',
            370285 => '莱西市',
        ),
        Conf_City::OTHERCITY => array(
            99999999 => '其他地区',
        ),
    );
    public static $arr = array(
        1001 => '东城区',
        1002 => '西城区',
        1003 => '石景山区',
        1004 => '朝阳区',
        1005 => '丰台区',
        1006 => '海淀区',
        1007 => '门头沟区',
        1008 => '房山区',
        1009 => '通州区',
        1010 => '顺义区',
        1011 => '昌平区',
        1012 => '大兴区',
        1013 => '怀柔区',
        1014 => '平谷区',
        1015 => '密云县',
        1016 => '延庆县');


    //0-未知;
    //北京: 1 => '二环以内', 2 => '二至三环', 3 => '三至四环', 4 => '四至五环', 5 => '五至六环', 6 => '六环以外',
    //天津: 1-外环以里; 2-外环以外
    //武汉: 1 => '二环以内', 2 => '二至三环', 3 => '三至四环', 4 => '四环外',
    public static $AREA = array(
        1001 => array(1 => '二环以内', 2 => '二至三环'), // 东城区
        1002 => array(1 => '二环以内', 2 => '二至三环'), // 西城区
        1003 => array(4 => '四至五环', 5 => '五至六环', 6 => '六环以外'), // 石景山区
        1004 => array(2 => '二至三环', 3 => '三至四环', 4 => '四至五环', 5 => '五至六环'), // 朝阳区
        1005 => array(2 => '二至三环', 3 => '三至四环', 4 => '四至五环', 5 => '五至六环', 6 => '六环以外'), // 丰台区
        1006 => array(2 => '二至三环', 3 => '三至四环', 4 => '四至五环', 5 => '五至六环', 6 => '六环以外'), // 海淀区
        1007 => array(5 => '五至六环', 6 => '六环以外'), // 门头沟区
        1008 => array(5 => '五至六环', 6 => '六环以外'), // 房山区
        1009 => array(5 => '五至六环', 6 => '六环以外'), // 通州区
        1010 => array(5 => '五至六环', 6 => '六环以外'), // 顺义区
        1011 => array(5 => '五至六环', 6 => '六环以外'), // 昌平区
        1012 => array(4 => '四至五环', 5 => '五至六环', 6 => '六环以外'), // 大兴区
        1013 => array(6 => '六环以外'), // 怀柔区
        1014 => array(6 => '六环以外'), // 平谷区
        1015 => array(6 => '六环以外'), // 密云县
        1016 => array(6 => '六环以外'), // 延庆县
        2001 => array(6 => '六环以外'), //大厂
        2002 => array(6 => '六环以外'), //香河
        2003 => array(6 => '六环以外'), //燕郊
        2013 => array(6 => '六环以外'), //三河
        2004 => array(6 => '六环以外'), //固安
        2005 => array(6 => '六环以外'), //涿州
        2006 => array(6 => '六环以外'), //怀来
        2007 => array(6 => '六环以外'), //张家口
        2008 => array(6 => '六环以外'), //其他地区
        120101 => array(1 => '外环以内'), //和平区,
        120102 => array(1 => '外环以内'), //河东区,
        120103 => array(1 => '外环以内'), //河西区,
        120104 => array(1 => '外环以内'), //南开区,
        120105 => array(1 => '外环以内'), //河北区,
        120106 => array(1 => '外环以内'), //红桥区,
        120107 => array(2 => '外环以外'), //塘沽区,
        120108 => array(2 => '外环以外'), //汉沽区,
        120109 => array(2 => '外环以外'), //大港区,
        120110 => array(1 => '外环以内', 2 => '外环以外'), //东丽区,
        120111 => array(1 => '外环以内', 2 => '外环以外'), //西青区,
        120112 => array(1 => '外环以内', 2 => '外环以外'), //津南区,
        120113 => array(1 => '外环以内', 2 => '外环以外'), //北辰区,
        120114 => array(2 => '外环以外'), //武清区,
        120115 => array(2 => '外环以外'), //宝坻区,
        120116 => array(2 => '外环以外'), //滨海新区,
        120221 => array(2 => '外环以外'), //宁河,
        120223 => array(2 => '外环以外'), //静海,
        120225 => array(2 => '外环以外'), //蓟县,
        
        420102 => array(1 => '二环以内', 2 => '二至三环', 3 => '三至四环'),  //'江岸区',
        420103 => array(1 => '二环以内', 2 => '二至三环',),  //'江汉区',
        420104 => array(1 => '二环以内', 2 => '二至三环',),  //'硚口区',
        420105 => array(1 => '二环以内', 2 => '二至三环', 3 => '三至四环'),  //'汉阳区',
        420106 => array(1 => '二环以内'),  //'武昌区',
        420107 => array(2 => '二至三环', 3 => '三至四环'),  //'青山区',
        420111 => array(1 => '二环以内', 2 => '二至三环', 3 => '三环至外环高速', 4 => '外环高速外'),  //'洪山区',
        420112 => array(3 => '金山大道内和机场高速以东', 4 => '金山大道外和机场高速以西'),  //'东西湖区',
        420113 => array(4 => '四环外'),  //'汉南区',
        420114 => array(3 => '三至四环', 4 => '四环外'),  //'蔡甸区',
        420115 => array(3 => '三至四环', 4 => '四环外'),  //'江夏区',
        420116 => array(3 => '三至四环', 4 => '四环外'),  //'黄陂区',
        420117 => array(4 => '四环外'),  //'新洲区',
        420199 => array(4 => '四环外'),  //'武汉周边区'
        
        // 廊坊：1=>'市区', 2=>'郊县'
        //131001 => array(1 => '市区'), //廊坊市区
        
        131002 => array(1 => '市区', 2 => '郊县'), //廊坊 安次区
        131003 => array(1 => '市区', 2 => '郊县'), //廊坊 广阳区
        131023 => array(2 => '郊县'), //廊坊 永清县
        131025 => array(2 => '郊县'), //廊坊 大城县
        131026 => array(2 => '郊县'), //廊坊 文安县
        131081 => array(2 => '郊县'), //廊坊 霸州市

        500101  => array(2 => '绕城高速外'),
        500102  => array(2 => '绕城高速外'),
        500103  => array(1 => '绕城高速内'),
        500104  => array(1 => '绕城高速内'),
        500105  => array(1 => '绕城高速内', 2 => '绕城高速外'),
        500106  => array(1 => '绕城高速内', 2 => '绕城高速外'),
        500107  => array(1 => '绕城高速内', 2 => '绕城高速外'),
        500108  => array(1 => '绕城高速内', 2 => '绕城高速外'),
        500109  => array(1 => '绕城高速内', 2 => '绕城高速外'),
        500110  => array(2 => '绕城高速外'),
        500111  => array(2 => '绕城高速外'),
        500112  => array(1 => '绕城高速内', 2 => '绕城高速外'),
        500113  => array(1 => '绕城高速内', 2 => '绕城高速外'),
        500114  => array(2 => '绕城高速外'),
        500115  => array(2 => '绕城高速外'),
        500116  => array(1 => '绕城高速内', 2 => '绕城高速外'),
        500117  => array(2 => '绕城高速外'),
        500118  => array(2 => '绕城高速外'),
        500119  => array(2 => '绕城高速外'),
        500120  => array(2 => '绕城高速外'),
        500151  => array(2 => '绕城高速外'),
        500152  => array(2 => '绕城高速外'),
        500153  => array(2 => '绕城高速外'),
        500154  => array(2 => '绕城高速外'),
        
        510102  => array(2=>'二至三环', 3=>'三至四环内'),                               //高新区
        510104  => array(1=>'二环以内', 2=>'二至三环', 3=>'三至四环内', 4=>'四环外'),      //锦江区
        510105  => array(1=>'二环以内', 2=>'二至三环', 3=>'三至四环内', 4=>'四环外'),      //青羊区
        510106  => array(1=>'二环以内', 2=>'二至三环', 3=>'三至四环内', 4=>'四环外'),      //金牛区
        510107  => array(1=>'二环以内', 2=>'二至三环', 3=>'三至四环内', 4=>'四环外'),      //武侯区
        510108  => array(1=>'二环以内', 2=>'二至三环', 3=>'三至四环内', 4=>'四环外'),      //成华区
        510156  => array(4=>'四环外'),                                                //天府新区
        510112  => array(3=>'三至四环内', 4=>'四环外'),                                 //龙泉驿区
        510113  => array(4=>'四环外'),                                                //青白江区
        510114  => array(3=>'三至四环内', 4=>'四环外'),                                 //新都区
        510115  => array(4=>'四环外'),                                                //温江区
        510116  => array(4=>'四环外'),                                                //双流区
        510124  => array(3=>'三至四环内', 4=>'四环外'),                                 //郫都区

        370202 => array(1 => '市区'),
        370203 => array(1 => '市区'),
        370211 => array(1 => '郊县'),
        370212 => array(1 => '市区'),
        370213 => array(1 => '市区'),
        370214 => array(1 => '市区'),
        370215 => array(1 => '市区'),
        370281 => array(1 => '郊县'),
        370283 => array(1 => '郊县'),
        370285 => array(1 => '郊县'),
        // 其他省市
        99999999 => array(1 => '其他'),
    );

    public static function isChongQingMainDistrict($district)
    {
        //渝中区、江北区、南岸区、九龙坡区、沙坪坝区、大渡口区、北碚区、渝北区、巴南区
        $mainDistrict = array(500103, 500104, 500105, 500106, 500107, 500108, 500109, 500112, 500113);
        return in_array($district, $mainDistrict);
    }

    // 1-满免运费区域; 2-按实际发生计费区域;
    public static function getFreightFeeType($city, $district, $area)
    {
        $type = 0;
        if (Conf_City::BEIJING == $city)
        {
            $pureNearDistricts = array(1001,1002,1004,1009);//东城,西城,朝阳,通州
            if ($area >= 0 && $area <= 5) //环线标记在六环里
            {
                $type = 1;
            }
            elseif (in_array($district,$pureNearDistricts)) //城区在六环里||通州
            {
                $type = 1;
            }
        }
        else if(Conf_City::TIANJIN == $city)
        {
            //中心城六区、以及北辰/西青/东丽/津南区
            $tianjinNearDistricts = array(120101,120102,120103,120104,120105,120106,120110,120111,120112,120113);
            if (in_array($district, $tianjinNearDistricts))
            {
                $type = Conf_Order::ORDER_MIN_FREIGHT_TIANJIN;
            }
        }
        else if (Conf_City::WUHAN)
        {
            $wuhanNearDistrictsJiangbei = array(420102, 420103, 420104, 420105, 420112); //中心城区江北
            $wuhanNearDistrictsJiangNan = array(420106, 420107, 420111); //中心城区江南
            if (in_array($district, $wuhanNearDistrictsJiangbei))
            {
                $type = Conf_Order::ORDER_MIN_FREIGHT_WUHAN_JIANGBEI;
            }
            else if (in_array($district, $wuhanNearDistrictsJiangNan))
            {
                $type = Conf_Order::ORDER_MIN_FREIGHT_WUHAN_JIANGNAN;
            }
            //东西湖区的金山大道外或机场高速以西、洪山区三环外按实际距离算
            if (($district == 420112 && $area == 4) || ($district == 420111 && $area >= 3))
            {
                $type = 0;
            }
        }
        return $type;
    }

    //todo: 改成 城市 -> 环线 配置 【函数形式】
    public static $FEE_TYPE = array(
        '101_1001' => 1,
        '101_1002' => 1,
        '101_1003' => 1,
        '101_1004' => 1,
        '101_1005_1' => 1,
        '101_1005_2' => 2,
        '101_1006_1' => 1,
        '101_1006_2' => 2,
        '101_1007_1' => 1,
        '101_1007_2' => 2,
        '101_1008_1' => 1,
        '101_1008_2' => 2,
        '101_1009_1' => 1,
        '101_1009_2' => 2,
        '101_1010_1' => 1,
        '101_1010_2' => 2,
        '101_1011_1' => 1,
        '101_1011_2' => 2,
        '101_1012_1' => 1,
        '101_1012_2' => 2,
        '101_1013' => 3,
        '101_1014' => 3,
        '101_1015' => 3,
        '101_1016' => 3,
        '102_2001' => 3,
        '102_2002' => 3,
        '102_2003' => 3,
        '102_2004' => 3,
        '102_2005' => 3,
        '102_2006' => 4,
        '102_2007' => 4,
        '102_2008' => 5,
        '200_20001' => 5,
        //天津
        '120_120101' => 1,
        '120_120102' => 1,
        '120_120103' => 1,
        '120_120104' => 1,
        '120_120105' => 1,
        '120_120106' => 1,
        '120_120110' => 1,
        '120_120111' => 1,
        '120_120112' => 1,
        '120_120113' => 1,
    );

    /**
     * 北京三环坐标.
     */
    protected static $Beijing_3Huan = array(
        'ws' => array(116.323206, 39.858353),    //西南角
        'en' => array(116.453712, 39.974345),
    );

    /**
     * 获取小区所在的特殊运输条件.
     * 
     * @param int $cityId   司机所属的城市ID
     * @param int $communityId  小区ID
     * @param array $communityInfo 小区信息
     * @return array
     */
    public static function getSpecialTransScope($cityId, $communityId, $communityInfo=array())
    {
        $sPosition = array();
        if (empty($communityInfo))
        {
            $communityInfo = Order_Community_Api::get($communityId);
        }
        $transScope = array_key_exists($cityId, Conf_Driver::$TRANS_SCOPES)?
                            Conf_Driver::$TRANS_SCOPES[$cityId]: array();
        
        if (empty($communityInfo) || empty($transScope))
        {
            return $sPosition;
        }
        
        if ($cityId == Conf_City::BEIJING)
        {
            if ($communityInfo['ring_road']==1 && $communityInfo['city_id']==Conf_City::BEIJING) // 二环内
            {
                $sPosition = array('110#2');
            }
            else if ($communityInfo['ring_road']==2 && $communityInfo['city_id']==Conf_City::BEIJING) // 三环
            {
                $sPosition = array('110#2', '110#3');
            }
            else if (self::_isLangFang($communityInfo['district_id'])) // 廊坊
            {
                $sPosition = array('13109');
            }
            else if (self::_isTianJin($communityInfo['city_id']))  // 天津
            {
                $sPosition = array('120');
            }
        }
        
        return $sPosition;
    }
    
    // 是否为三环
//    private static function _isBeijing3Huan($lng, $lat)
//    {
//        if (self::$Beijing_3Huan['ws'][0]<=$lng && $lng<=self::$Beijing_3Huan['en'][0]
//            && self::$Beijing_3Huan['ws'][1]<=$lat && $lat<=self::$Beijing_3Huan['en'][1])
//        {
//            return true;
//        }
//        
//        return false;
//    }
    
    
    // 是否为廊坊
    private static function _isLangFang($districtID)
    {
        $districtIDs = array(2001, 2002, 2003, 2004, 2005, 20001,2013);
        
        return in_array($districtID, $districtIDs)? true: false;
    }
    // 是否为天津 北京仓发天津区域的订单，有些司机可能会不去
    private static function _isTianJin($cityId)
    {
//        $districtIDs = array(2009, 2010, 2011, 2012);
//        
//        return in_array($districtId, $districtIDs)? true: false;
        
        return ($cityId == Conf_City::TIANJIN)? true: false;
        
    }
    
    public static function getArea2()
    {
        $area = array();
        foreach (self::$CITY as $k => $city)
        {
            $area[$k]['name'] = $city;
            $area[$k]['dist'] = isset(self::$DISTRICT[$k]) ? self::$DISTRICT[$k] : array();
            if (!empty($area[$k]['dist']))
            {
                foreach ($area[$k]['dist'] as $j => $dist)
                {
                    $area[$k]['dist'][$j] = array();
                    $area[$k]['dist'][$j]['name'] = $dist;
                    $area[$k]['dist'][$j]['area'] = isset(self::$AREA[$j]) ? self::$AREA[$j] : array();
                    if (!empty($area[$k]['dist'][$j]['area']))
                    {
                        foreach ($area[$k]['dist'][$j]['area'] as $i => $area)
                        {
                            $area[$k]['dist'][$j]['area'][$i] = $area;
                        }
                    }
                }
            }
        }

        return $area;
    }

    public static function getArea()
    {
        $cities = array();
        foreach (self::$CITY as $cityId => $cityName)
        {
            $city = array('id' => $cityId, 'name' => $cityName, 'dist'=> array() );
            $distInfo = isset(self::$DISTRICT[$cityId]) ? self::$DISTRICT[$cityId] : array();
            if (!empty($distInfo))
            {
                foreach ($distInfo as $distId => $distName)
                {
                    $dist = array('id'=> $distId, 'name'=> $distName, 'area'=> array());
                    $areaInfo = isset(self::$AREA[$distId]) ? self::$AREA[$distId] : array();
                    if (!empty($areaInfo))
                    {
                        foreach ($areaInfo as $areaId => $areaName)
                        {
                            $area = array('id'=>$areaId, 'name'=>$areaName);
                            $dist['area'][] = $area;
                        }
                    }
                    $city['dist'][] = $dist;
                }
            }
            $cities[] = $city;
        }

        return $cities;
    }


    /*********老的，没删*************/

    /*
    public static $AREA = array(
        1005 => array(1 => '六环内', 2 => '六环外'),
        1006 => array(1 => '六环内', 2 => '六环外'),
        1007 => array(1 => '六环内', 2 => '六环外'),
        1008 => array(1 => '六环内', 2 => '六环外'),
        1009 => array(1 => '六环内', 2 => '六环外'),
        1010 => array(1 => '六环内', 2 => '六环外'),
        1011 => array(1 => '六环内', 2 => '六环外'),
        1012 => array(1 => '六环内', 2 => '六环外'),
    );
    */

    private static $distConf = array(
        1=>'朝阳',
        2=> '海淀',
        3=> '东城',
        4=> '西城',
        5=> '丰台',
        6=> '通州',
        7=> '昌平',
        8=> '石景山',
        9=> '房山',
        10=> '大兴',
        11=> '顺义');

    private static $areaConf = array(
        1=> array('双井/国贸', '三里屯/团结湖', '四惠/十里堡', '望京', '三元桥/太阳宫', '朝阳公园', '潘家园', '欢乐谷', '常营', '管庄/定福庄', '亚运村', '安贞', '青年路', '北苑', '朝阳门', '十八里店', '奥林匹克公园'),
        2=> array('公主坟/万寿路', '五棵松', '北下关', '中关村', '五道口', '学院路/学清路', '北太平庄', '长春桥', '航天桥', '魏公村', '紫竹桥', '清河', '上地', '北部新区'),
        3=> array('东直门', '安定门', '天坛', '崇文门'),
        4=> array('复兴门', '西直门', '菜市口', '广安门'),
        5=> array('六里桥', '刘家窑', '木樨园', '宋家庄', '马家堡', '玉泉营', '右安门', '方庄', '长辛店', '花乡', '北大地', '卢沟桥', '青塔'),
        6=> array('八里桥', '新华大街', '通胡大街', '梨园', '宋庄', '次渠', '潞苑', '通州北苑', '玉桥', '果园'),
        7=> array('县城内', '沙河', '回龙观', '天通苑', '北七家'),
        8=> array('鲁谷', '八角', '古城', '杨庄'),
        9=> array('燕山', '良乡', '窦店', '阎村', '长阳', '房山城关'),
        10=> array('黄村', '西红门', '旧宫', '亦庄', '采育', '榆垡'),
        11=> array('主城区内', '天竺', '后沙峪', '李桥', '赵全营')
    );
    
    /**
     * 省份
     */
    public static $Province = array(
        '410000' => '河南省',
        '340000' => '安徽省',
        '320000' => '江苏省',
        '510000' => '四川省',
        '420000' => '湖北省',
        '370000' => '山东省',
        '330000' => '浙江省',

        '110000' => '北京市',
        '120000' => '天津市',
        '130000' => '河北省',
        '140000' => '山西省',
        '150000' => '内蒙古',
        '210000' => '辽宁省',
        '220000' => '吉林省',
        '230000' => '黑龙江省',
        '310000' => '上海市',
        '350000' => '福建省',
        '360000' => '江西省',
        '430000' => '湖南省',
        '440000' => '广东省',
        '450000' => '广西省',
        '460000' => '海南省',
        '500000' => '重庆市',
        '520000' => '贵州省',
        '530000' => '云南省',
        '540000' => '西藏省',
        '610000' => '陕西省',
        '620000' => '甘肃省',
        '630000' => '青海省',
        '640000' => '宁夏省',
        '650000' => '新疆省',
        '710000' => '台湾',
        '720000' => '香港',
        '730000' => '澳门',
        
    );

    public static function getDistList()
    {
        return self::$distConf;
    }

    public static function getAreaList()
    {
        $areaList = array();
        foreach (self::$areaConf as $distId => $areas)
        {
            $areaList[$distId] = array();
            foreach ($areas as $idx => $area)
            {
                $areaId = $distId*100+$idx+1;
                $areaList[$distId][$areaId] = $area;
            }
        }
        return $areaList;
    }

    public static function getLimitArea($cityId)
    {
        $area = array(
            Conf_City::BEIJING => array(1,2,3,4),
            Conf_City::TIANJIN => array(1),
        );

        return $area[$cityId];
    }
    
    /**
     * 获取城市相关的信息，供客户端客户选地址使用.
     */
    public static function getCityMoreInfo4Customer($city=array(), $curDistrict = 0, $curRingRoad = 0)
    {
        $allCities = self::$CITY;
        $cities = array();
        foreach($city as $_cityid)
        {
            if (array_key_exists($_cityid, $allCities))
            {
                $cities[$_cityid] = $allCities[$_cityid];
            }
        }
        
        !$cities && $cities = $allCities;
        
        $cityMoreInfo = array();
        foreach($cities as $cityId => $cityName)
        {
            $c = new stdClass();
            $c->code = $cityId;
            $c->address= $cityName;
            $cityMoreInfo['86'][] = $c;
            
            $_district = self::$DISTRICT[$cityId];
            foreach($_district as $_districtId => $_districtName)
            {
                if ($curDistrict != 0 && $_districtId != 99999999 && $_districtId != $curDistrict)
                {
                    continue;
                }
                $cityMoreInfo[$cityId][$_districtId] = $_districtName;
            }

            foreach($_district as $_districtId => $_districtName)
            {
                if ($curDistrict != 0 && $_districtId != 99999999 && $_districtId != $curDistrict)
                {
                    continue;
                }

                foreach (self::$AREA[$_districtId] as $aid => $aname)
                {
                    if ($curRingRoad != 0 && $aid != 99999999 && $aid != $curRingRoad)
                    {
                        continue;
                    }
                    $cityMoreInfo[$_districtId][$aid] = $aname;
                }
            }
        }
        
        return $cityMoreInfo;
    }
}
