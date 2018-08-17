<?php

/**
 * 城市 - 小区.
 */

class Order_Community_Api extends Base_Api
{
    
    public static function get($cmid)
    {
        $oc = new Order_Community();
        
        return $oc->get($cmid);
    }
	public static function getByIds($cmids)
	{
		$oc = new Order_Community();

		return $oc->getBulk($cmids);
	}
    public static function getByNameAlias($name)
    {
        $oc = new Order_Community();
        
        return $oc->getByNameAlias($name);
    }
    
    public static function save($cdata, $cmid=0)
    {
        $oc = new Order_Community();
        
        if (isset($cdata['name']) && !empty($cdata['name']))
        {
            $cdata['pinyin_name'] = Str_Chinese::hz2py2($cdata['name']);
        }
        
        if (empty($cmid))
        {
            $cdata['status'] = Conf_Base::STATUS_WAIT_AUDIT; //设置为未审核
            $cmid = $oc->add($cdata);
        }
        else
        {
            $oc->update($cmid, $cdata);
        }
        
        return $cmid;
    }
    
    
	public static function mergeCommunity($fromCmid, $toCmid)
	{
		$oc = new Order_Community();
		$oc->mergeCommunity($fromCmid, $toCmid);
	}
    
    public static function sphinxSearch($keyword, $cityId=0, $start=0, $num=20)
    {
        $s = new SphinxClient;
		$s->setServer("localhost", 9312);           //sphinx的主机名和端口
		$s->setArrayResult(TRUE);                   //设置返回结果集为php数组格式
        $s->SetSortMode(SPH_SORT_RELEVANCE);        //排序模式 按相关度降序排列(最好排在最前面)
		$s->setMatchMode(SPH_MATCH_ANY);            //设置扩展匹配模式 匹配查询词中的任意一个
        
        //$s->SetMatchMode ( SPH_MATCH_EXTENDED );//设置模式
        $s->SetRankingMode ( SPH_RANK_PROXIMITY );//设置评分模式
	    $s->SetFieldWeights (array('name'=>10,'pinyin_name'=>10,'alias'=>6,'address'=>1));//设置字段的权重
        $s->SetSortMode ('SPH_SORT_EXPR','@weight');//按照权重排序
		if (!empty($cityId))
		{
			$cityFilter =($cityId == Conf_City::BEIJING)?
				array(Conf_City::BEIJING, Conf_City::XIANGHE, Conf_City::OTHER) : array ( $cityId );
			$s->SetFilter ( "city_id", $cityFilter );
		}

		$s->setLimits(0, 30, 1000);                 //参数含义：起始位置，返回结果条数，最大匹配条数
		$sphinxRes = $s->query($keyword, 'community');
        
		$cmidMatchArr = array();
		if (empty($sphinxRes['matches']))
		{
			return array();
		}
		foreach ($sphinxRes['matches'] as $match)
		{
			$cmidMatchArr[$match['id']] = $match['weight'];
		}
        
        arsort($cmidMatchArr);
        
		$total = count($cmidMatchArr);
        //$cmidList = array_keys(array_slice($cmidMatchArr, $start, $num, TRUE));
        $cmidList = array_keys($cmidMatchArr);
        
        $oc = new Order_Community();
        $list = $oc->getBulk($cmidList);
        
        $finalList = array();
        $counter = 0;
        foreach($cmidList as $_cmid)
        {
            if ($list[$_cmid]['status']!=Conf_Base::STATUS_DELETED)
            {
                $finalList[$_cmid] = $list[$_cmid];
                
                if (++$counter >= $num) break;
            
            }
        }
        
        return array('total'=>$total, 'data'=>$finalList);
    }
    
    public static function search($search, $start=0, $num=20)
    {
        $oc = new Order_Community();
        
        $list = $oc->search($search, true, $start, $num);
        
         //补充市场专员信息
		$as = new Admin_Staff();
		$as->appendSuers($list['data'], 'suid', 'edit_suid', true);

	    //补充环路信息
	    $oc = new Order_Community();
	    $oc->formatRingroadInfo($list['data']);

	    //补充订单信息
	    $oc->formatOrderInfo($list['data']);
        return $list;
    }
    
    public function searchForOrder($keyword, $cityId=0, $start=0, $num=20)
    {
        $search = array('keyword' => $keyword, 'city_id' => $cityId);
        
        $oc = new Order_Community();
        
        return $oc->search($search, false, $start, $num);
    }
    
    ////////////////////////////// 小区距离相关方法  ////////////////////////////////////////

    /**
     * 更新距离.
     * 
     * @param int $wid  仓库id
     * @param int $communityId  小区id
     * @param float $distance   距离，公里
     * @param int $status
     * @param string $note
     */
	public static function saveDistance($wid, $communityId, $distance, $status, $note)
	{
		$ocf = new Order_Community_Fee();

		$where = array(
			'cmid' => $communityId,
			'wid' => $wid,
			'car_model' => 0,
		);

		$info = array(
			'distance' => $distance * 1000,
			'status' => $status,
			'note' => $note,
		);

		return $ocf->updateWhere($where, $info);
	}
    
    /**
     * 添加小区距离.
     * 
     * @param int $communityId
     * @param int $wid
     * @param float $distance
     * @param string $note
     */
    public static function addDistance($communityId, $wid, $distance, $note='')
    {
        if ($distance <= 0) return false;
        
        $ocf = new Order_Community_Fee();
        $info = array(
            'cmid' => $communityId,
            'wid' => $wid,
            'car_model' => 0,
            'distance' => $distance,
            'freight' => 0,
            'note' => $note,
            'status' => Conf_Base::STATUS_WAIT_AUDIT,
        );
        
        $ocf->add($info);
        
        return true;
    }

    /**
     * 计算小区到各个仓库之间的距离.
     * 
     * @param int $communityId  小区id
     * @return array
     */
	public static function calCommunityWarehousesDis($communityId)
	{
		$data = array();

		//如果没有，调用接口获取，然后存储到数据库
		//1、读取小区数据，获取经纬度
		$community = self::get($communityId);
        $cityId = $community['city_id']==Conf_City::OTHER? Conf_City::BEIJING: $community['city_id'];
        
        // 获取城市中所有的仓库和坐标
        $wids = Appconf_Warehouse::wid4CreateOrder($cityId);
        foreach($wids as $_wid => $_widDesc)
        {
            $locationList[$_wid] = Conf_Warehouse::$LOCATION[$_wid];
        }
        
        // 判断合法性
        if (empty($community) || empty($cityId) || empty($locationList)
            || $community['lng'] == 0 || $community['lat'] == 0)
		{
			return $data;
		}
        
        //读取DB中存的距离 && 将存在的距离返回
        $ofc = new Order_Community_Fee();
        $distanceWhere = array(
            'status' => array(Conf_Base::STATUS_NORMAL, Conf_Base::STATUS_WAIT_AUDIT),
            'wid' => array_keys($locationList),
            'cmid' => $communityId,
        );
        
        $existDistances = $ofc->getListWhere($distanceWhere);
        
        foreach($existDistances as $one)
        {
            if ($one['distance'] > 0)
            {
                $data[$one['wid']] = array(
                    'name' => Conf_Warehouse::$WAREHOUSES[$one['wid']],
                    'distance' => round($one['distance'] / 1000, 2),
                );
                if (array_key_exists($one['wid'], $locationList))
                {
                    unset($locationList[$one['wid']]);
                }
            }
        }
        
        //从db中没有获取数据的，从百度获取
		foreach ($locationList as $wid => $location)
		{
			if (empty($location))
			{
				continue;
			}

            $cityName = Conf_City::$CITY[$cityId];
            $destinationPos = array('lat'=>$community['lat'], 'lng'=>$community['lng']);
            $distance = self::_getDistanceByPosFromBaidu($location, $destinationPos, $cityName, $cityName);
            
			$data[$wid] = array(
				'name' => Conf_Warehouse::$WAREHOUSES[$wid],
				'distance' => round($distance / 1000, 2),
			);
            
            // 写DB
            self::addDistance($communityId, $wid, $distance);
		}

		return $data;
	}

    /**
     * 获取仓库的所有距离，不建议使用.
     */
	public static function getDistances()
	{
		$ocf = new Order_Community_Fee();

		$where = array(
			'car_model' => 0,
		);

		return $ocf->getListWhere($where);
	}

    /**
     * 获取小区到仓库的距离.
     * 
     * @param int $cmid
     * @param int $wid
     * 
     * @return float 距离
     */
    public static function getDistanceByWid($cmid, $wid)
    {
        $ocf = new Order_Community_Fee();
        $ret = $ocf->getCommunityInfo($cmid, $wid);

        return $ret['distance'];
    }
    
    /**
     * 获取距离信息.
     * 
     * @param type $communityId
     * @param type $wid
     * @param type $fromBaidu
     * @return type
     */
    public static function getDistanceInfo($communityId, $wid, $fromBaidu=false)
    {
        $ocf = new Order_Community_Fee();
        $data = $ocf->getCommunityInfo($communityId, $wid);
        
        if (empty($data['distance']) && $fromBaidu)
        {
            $community = self::get($communityId);
            $cityId = $community['city_id']==Conf_City::OTHER? Conf_City::BEIJING: $community['city_id'];
            
            if (empty($community) || empty($cityId))
            {
                return array();
            }
            
            $cityName = Conf_City::$CITY[$cityId];
            $destinationPos = array('lat'=>$community['lat'], 'lng'=>$community['lng']);
            $originPos = Conf_Warehouse::$LOCATION[$wid];
            
            $data['distance'] = self::_getDistanceByPosFromBaidu($originPos, $destinationPos, $cityName, $cityName);
            $data['status'] = Conf_Base::STATUS_WAIT_AUDIT;
            
            
            // 写DB
            self::addDistance($communityId, $wid, $data['distance']);
        }

        return $data;
    }

    public static function getDistanceAndFeeListNew($communityId, $wid)
    {
        $result = Order_Community_Api::getDistanceInfo($communityId, $wid, true);
        $distance = $result['distance'];

        $data = array(
            'distance' => 0,
            'fee_list' => array(),
            'status' => Conf_Base::STATUS_NORMAL,
            'note' => '',
        );
        
        if ($distance > 0)
        {
            $data['distance'] = round($distance / 1000, 2);
            $carModels = Conf_Driver::$CAR_MODEL;

            $i = 0;
            $fee_list = array();
            foreach ($carModels as $model => $name)
            {
                $fee_list[$i]['cmid'] = $communityId;
                $fee_list[$i]['wid'] = $wid;
                $fee_list[$i]['car_model'] = $model;
                $fee_list[$i]['distance'] = $distance;
                $fee_list[$i]['freight'] = self::calFeesByDistanceAndWid($data['distance'], $wid, $model);
                $fee_list[$i]['_model'] = Conf_Driver::$CAR_MODEL[$model];
                $i++;
            }
            $data['fee_list'] = $fee_list;
            $data['status'] = $result['status'];
        }

        return $data;
    }

    /**
     * 通过距离和车型计算运费.
     * 
     * @param flat $distance
     * @param int $wid
     * @param itn $carModel
     * @param $cityId
     */
    public static function calFeesByDistanceAndWid($distance, $wid, $carModel)
    {
        $cityId = Conf_Warehouse::$WAREHOUSE_CITY_MAPPING[$wid];

        if ($cityId == Conf_City::LANGFANG)
        {
            $fee = 0;
            $distance = round($distance);
            if ($carModel == Conf_Driver::CAR_MODEL_DIANDONGPINGBAN)
            {
                if ($distance <= 5)
                {
                    $key = Conf_Driver::LF_DISTANCE_WITHIN_FIVE_KM;
                }
                else
                {
                    $key = Conf_Driver::LF_DISTANCE_MORE_THAN_FIVE_KM;
                }

                $fee = Conf_Driver::$LANGFANG_CAR_MODEL_FEE_RULES[$carModel][$key];
            }
            else if ($carModel == Conf_Driver::CAR_MODEL_PINGDINGJINBEI)
            {
                $fee = $distance * Conf_Driver::$LANGFANG_CAR_MODEL_FEE_RULES[$carModel];
            }
        }
        else if ($cityId == Conf_City::TIANJIN)
        {
            $distance = round($distance);
            if ($distance <= 5)
            {
                $fee = Conf_Driver::$TIANJIN_CAR_MODEL_FEE_RULES[$carModel]['5km'];
            }
            else if ($distance > 5 && $distance <=10)
            {
                $fee = Conf_Driver::$TIANJIN_CAR_MODEL_FEE_RULES[$carModel]['10km'];
            }
            else if ($distance > 10 && $distance <=15)
            {
                $fee = Conf_Driver::$TIANJIN_CAR_MODEL_FEE_RULES[$carModel]['15km'];
            }
            else
            {
                $rule = Conf_Driver::$TIANJIN_CAR_MODEL_FEE_RULES[$carModel];
                $fee = $rule['more_than_15km']['base'] + $rule['more_than_15km']['increase'] * ($distance - 15);
            }
        }
        else
        {
            $distance = floor($distance);
            $ruleNum = Conf_Driver::$WAREHOUSE_DRIVER_FEE_RULES[$wid];
            $rule = Conf_Driver::$DRIVER_FEE_RULES[$ruleNum];
            if ($distance <= 5)
            {
                $fee = $rule[$carModel]['base'];
            }
            else if ($distance <= 15)
            {
                $fee = $rule[$carModel]['base'] + $rule[$carModel]['increase'] * ($distance - 5);
            }
            else
            {
                if ($rule[$carModel]['decline'] == 0 && $rule[$carModel]['min_increase'] == 0)
                {
                    $fee = $rule[$carModel]['base'] + $rule[$carModel]['increase'] * ($distance - 5);
                }
                else
                {
                    $fee = $rule[$carModel]['base'] + $rule[$carModel]['increase'] * (15 - 5);
                    for ($i = 1; $i <= ($distance - 15); $i++)
                    {
                        $moreFee = $rule[$carModel]['increase'] - $rule[$carModel]['decline'] * $i;
                        if ($moreFee <= $rule[$carModel]['min_increase'])
                        {
                            $moreFee = $rule[$carModel]['min_increase'];
                        }
                        $fee += $moreFee;
                    }
                }
            }
        }

        $fee = floor($fee / 100) * 100;
        return $fee;
    }

    /**
     * 通过距离和车型计算运费,按仓库id选择规则.
     *
     * @param float $distance
     * @param int $wid
     * @param int $carModel
     *
     * @return int
     */
    public static function calFeesByDistanceAndWidNew($distance, $wid, $carModel)
    {
        $flag = Conf_Coopworker::getEditCoopworkerFlagForWid($wid, Conf_Base::COOPWORKER_DRIVER);
        if ($flag == 'driver_2')
        {
            $fee = 0;
            $distance = round($distance);
            if ($carModel == Conf_Driver::CAR_MODEL_DIANDONGPINGBAN)
            {
                if ($distance <= 5)
                {
                    $key = Conf_Driver::LF_DISTANCE_WITHIN_FIVE_KM;
                }
                else
                {
                    $key = Conf_Driver::LF_DISTANCE_MORE_THAN_FIVE_KM;
                }

                $fee = Conf_Driver::$LANGFANG_CAR_MODEL_FEE_RULES[$carModel][$key];
            }
            else if ($carModel == Conf_Driver::CAR_MODEL_PINGDINGJINBEI)
            {
                $fee = $distance * Conf_Driver::$LANGFANG_CAR_MODEL_FEE_RULES[$carModel];
            }
        }
        else if ($flag == 'driver_3')
        {
            $distance = round($distance);
            if ($distance <= 5)
            {
                $fee = Conf_Driver::$TIANJIN_CAR_MODEL_FEE_RULES[$carModel]['5km'];
            }
            else if ($distance > 5 && $distance <=10)
            {
                $fee = Conf_Driver::$TIANJIN_CAR_MODEL_FEE_RULES[$carModel]['10km'];
            }
            else if ($distance > 10 && $distance <=15)
            {
                $fee = Conf_Driver::$TIANJIN_CAR_MODEL_FEE_RULES[$carModel]['15km'];
            }
            else
            {
                $rule = Conf_Driver::$TIANJIN_CAR_MODEL_FEE_RULES[$carModel];
                $fee = $rule['more_than_15km']['base'] + $rule['more_than_15km']['increase'] * ($distance - 15);
            }
        }
        else if ($flag == 'driver_5')
        {
            $distance = floor($distance);
            $rule = Conf_Driver::$CHONGQING_CAR_MODEL_FEE_RULES[$carModel];
            if ($distance <= 10)
            {
                $fee = $rule['10km'];
            }
            else if ($distance > 10 && $distance <= 20)
            {
                $fee = $rule['10km'] + ($distance - 10) * $rule['more_than_10km_per_km'];
            }
            else
            {
                $fee = $rule['10km'] + 10 * $rule['more_than_10km_per_km'] + ($distance - 20) * $rule['more_than_20km_per_km'];
            }
        }
        else if ($flag == 'driver_1')
        {
            $distance = floor($distance);
            $ruleNum = Conf_Driver::$WAREHOUSE_DRIVER_FEE_RULES[$wid];
            $rule = Conf_Driver::$DRIVER_FEE_RULES[$ruleNum];
            if ($distance <= 5)
            {
                $fee = $rule[$carModel]['base'];
            }
            else if ($distance <= 15)
            {
                $fee = $rule[$carModel]['base'] + $rule[$carModel]['increase'] * ($distance - 5);
            }
            else
            {
                if ($rule[$carModel]['decline'] == 0 && $rule[$carModel]['min_increase'] == 0)
                {
                    $fee = $rule[$carModel]['base'] + $rule[$carModel]['increase'] * ($distance - 5);
                }
                else
                {
                    $fee = $rule[$carModel]['base'] + $rule[$carModel]['increase'] * (15 - 5);
                    for ($i = 1; $i <= ($distance - 15); $i++)
                    {
                        $moreFee = $rule[$carModel]['increase'] - $rule[$carModel]['decline'] * $i;
                        if ($moreFee <= $rule[$carModel]['min_increase'])
                        {
                            $moreFee = $rule[$carModel]['min_increase'];
                        }
                        $fee += $moreFee;
                    }
                }
            }
        }
        else if ($flag == 'driver_4')
        {
            $distance = floor($distance);
            $rule = Conf_Driver::$QINGDAO_CAR_MODEL_FEE_RULES[$carModel];
            if ($distance <= 10)
            {
                $fee = $rule['10km'];
            }
            else if ($distance > 10 && $distance <= 20)
            {
                $fee = $rule['10km'] + ($distance - 10) * $rule['more_than_10km_per_km'];
            }
            else
            {
                $fee = $rule['10km'] + 10 * $rule['more_than_10km_per_km'] + ($distance - 20) * $rule['more_than_20km_per_km'];
            }
        }
        else
        {
            $fee = 0;
        }

        $fee = floor($fee / 100) * 100;
        return $fee;
    }

    /**
     * 根据小区id和仓库id，获取各种车型的基础运费.
     * 
     * @param $data     $data = array(array('cmid'=>$community_id, 'wid' => $wid));
     * @param $isGetFee
     * @return array
     *
     */
    public static function getBlukCommunityFees($data, $isGetFee=true)
    {
        $where = sprintf('status in(%d,%d) and distance > 0 and (1 = 0', Conf_Base::STATUS_NORMAL, Conf_Base::STATUS_WAIT_AUDIT);
        foreach ($data as $value)
        {
            $where .= sprintf(' OR (cmid = %d AND wid = %d)', $value['cmid'], $value['wid']);
        }

        $where .= ')';
        $ocf = new Order_Community_Fee();
        $communityFeeInfos = $ocf->getListWhere($where, 0);
        $carModels = Conf_Driver::$CAR_MODEL;

        if (empty($communityFeeInfos))
        {
            return array();
        }

        $info = array();

        foreach ($communityFeeInfos as $value)
        {
            $cmid = $value['cmid'];
            $wid = $value['wid'];

            foreach ($carModels as $model => $name)
            {
                $info[$cmid . '#' . $wid]['distance'] = $value['distance'];
                if ($isGetFee)
                {
                    $info[$cmid . '#' . $wid]['fee'][$model] = Order_Community_Api::calFeesByDistanceAndWidNew(round($value['distance']/1000,2), $wid, $model);
                }
            }
        }

        return $info;
    }

    /**
     * 通过两个坐标从百度地图获取距离.
     * 
     * @param array $originPos {lat:xxx, lng:xxx}
     * @param array $destinationPos {lat:xxx, lng:xxx}
     * @param string $originRegion  城市中文名称
     * @param string $destinationRegion 城市中文名称
     */
    private static function _getDistanceByPosFromBaidu($originPos, $destinationPos, $originRegion='北京', $destinationRegion='北京')
    {
        if (empty($destinationPos) || $destinationPos['lng'] == 0 || $destinationPos['lat'] == 0
            || empty($originPos) || $originPos['lng'] == 0 || $originPos['lat'] == 0)
        {
            return 0;
        }
        
        //调用接口
        $baiduUrlForDistance = 'http://api.map.baidu.com/direction/v1';
        $baiduParamForDistance = array(
            'mode' => 'driving',
            'tactics' => 11,
            'origin' => $originPos['lat'].','.$originPos['lng'],
            'destination' => $destinationPos['lat'].','.$destinationPos['lng'],
            'origin_region' => $originRegion,
            'destination_region' => $destinationRegion,
            'output' => 'json',
            'ak' => Conf_Base::BAIDU_KEY,
        );
        $distanceInfo = json_decode(Tool_Http::get($baiduUrlForDistance, $baiduParamForDistance), true);
        
        return $distanceInfo['status'] == 0? $distanceInfo['result']['routes'][0]['distance']: 0;
        
    }
    
    public static function getBaseDriverFeesByOids($oids)
    {
        assert(is_array($oids));
        $orderInfos = Order_Api::getListByPk($oids);
        $orderInfos = Tool_Array::list2Map($orderInfos, 'oid');
        ksort($orderInfos);

        if (empty($orderInfos))
        {
            return false;
        }

        foreach ($orderInfos as $key => $order)
        {
            $data[$key]['cmid'] = $order['community_id'];
            $data[$key]['wid'] = $order['wid'];
            $data[$key]['city_id'] = $order['city_id'];
        }

        if (empty($data))
        {
            return false;
        }

        $orderDriverFees = Order_Community_Api::getBlukCommunityFees($data, true);
        $maxDistance = max(array_unique(Tool_Array::getFields($orderDriverFees, 'distance')));

        $values = array();
        foreach ($orderDriverFees as $key => $fee)
        {
            if (in_array($maxDistance, $fee))
            {
                $values = explode('#', $key);
            }
        }

        $result = array();
        foreach ($orderInfos as $order)
        {
            if ($order['community_id'] == $values[0] && $order['wid'] == $values[1])
            {
                $result['oid'] = $order['oid'];
                $result['fee'] = $orderDriverFees[$values[0] . '#' . $values[1]];
                break;
            }
        }

        return $result;
    }

    public static function getDistanceByCmids($communityIds)
    {
        $result = array();
        $ocf = new Order_Community_Fee();
        $where = array('cmid' => $communityIds);
        $data = $ocf->getListWhere($where);

        if (!empty($data))
        {
            foreach ($data as $item)
            {
                if ($item['car_model'] > 0)
                {
                    continue;
                }
                $cmid = $item['cmid'];
                $wid = $item['wid'];
                $distance = $item['distance'];
                $result[$cmid][$wid] = $distance;
            }
        }

        return $result;
    }
    
    /**
     * 计算两个坐标点距离.
     * 
     * @param array $p1 {lat:xx, lng:xx}
     * @param array $p2 {lat:xx, lng:xx}
     */
    public static function calDistanceBetweenPoints($p1, $p2)
    {
        $R = 6378137; //地球半径
        
        $lat1 = $p1['lat'] * pi() /180.0;
        $lat2 = $p2['lat'] * pi() /180.0;
        
        $dLat = $lat1 - $lat2;
        $dLng = ($p1['lng']-$p2['lng']) * pi() /180.0;
        
        $sa2 = sin($dLat / 2.0);
        $sb2 = sin($dLng / 2.0);
        
        $distance = 2* $R* asin(sqrt($sa2* $sa2 + cos($lat1)* cos($lat2)*  $sb2* $sb2));
        
        return $distance;
    }

    /**
     * 根据小区ID和仓库ID获取小区到仓库的距离
     * @author libaolong
     * @param $cmidWidInfo
     * @return array
     */
    public static function getDistancesBetweenCommunityAndWid($cmidWidInfo)
    {
        $cdf = new Data_Dao('t_community_distance_fee');

        $distences = array();
        $cmids = array();
        foreach($cmidWidInfo as $item)
        {
            $cmids[] = $item['cmid'];
        }

        if (empty($cmids)) return array();

        $where = sprintf(' cmid in (%s) and distance>0 ', implode(',', $cmids));
        $list = $cdf->setFields(array('cmid', 'distance', 'wid'))->getListWhere($where, false);

        foreach($list as $lItem)
        {
            $_distances[$lItem['cmid'].'.'.$lItem['wid']] = $lItem['distance'];
        }

        foreach($cmidWidInfo as $item)
        {
            $distences[$item['cmid'].'.'.$item['wid']] = $_distances[$item['cmid'].'.'.$item['wid']];
        }

        return $distences;
    }
}