<?php

/**
 * 城市 - 小区.
 */

class Order_Community extends Base_Func
{
    private $dao;
    
    function __construct()
    {
        $this->dao = new Data_Dao('t_community');
        
        parent::__construct();
    }
    
    public function get($cmid)
    {
        assert($cmid);
        
        return $this->dao->get($cmid);
    }   
    
    public function getBulk($cmids)
    {
        assert($cmids);
        
        $list = $this->dao->getList($cmids);
        
        return $list;
    }


    public function getByNameAlias($name)
    {
        if (empty($name))
        {
            return array();
        }
        
        $where = 'status!='.Conf_Base::STATUS_DELETED. ' and (name="'.$name.'" or alias="'.$name.'")';
        
        return $this->dao->getListWhere($where);
    }
    
    public function add($cdata)
    {
        assert(!empty($cdata));
        assert(!empty($cdata['city_id']) && !empty($cdata['district_id']) 
                && !empty($cdata['ring_road']));
        assert(!empty($cdata['lng']) && !empty($cdata['lat']));
        
        $cdata['pics'] = $this->is($cdata['pics'])?: '';
        $cdata['ctime'] = $this->is($cdata['ctime'])?: date('Y-m-d H:i:s');
        $cmid = $this->dao->add($cdata);
        
        return $cmid;
    }
    
    public function update($cmid, $cdata)
    {
        assert(!empty($cmid));
        assert(!empty($cdata));
        $affectRow = $this->dao->update($cmid, $cdata);
        
        return $affectRow;
    }
    
    public function search($search, $withTotal=true, $start=0, $num=20)
    {
        $where = '1=1';
        
        if ($this->is($search['cmid']))
        {
            $where .= sprintf(' and cmid=%d', $search['cmid']);
        }
        if ($this->is($search['keyword'])) // 从name，pinyin_name，alias中筛选
        {
            $where .= sprintf(' and (name like "%%%s%%" or pinyin_name like "%%%s%%" or alias like "%%%s%%")',
                    $search['keyword'], $search['keyword'], $search['keyword']);
        }
        else if ($this->is($search['full_keyword'])) // 从name, pinyin_name, alias, address, area中筛选
        {
            $where .= sprintf(' and (name like "%%%s%%" or pinyin_name like "%%%s%%" or alias like "%%%s%%" or address like "%%%s%%" or area like "%%%s%%")',
                    $search['full_keyword'], $search['full_keyword'], $search['full_keyword'], $search['full_keyword'], $search['full_keyword']);
        }
        
        if ($this->is($search['city_id']))
        {
            $where .= ' and city_id='. $search['city_id'];
        }
        
        if ($this->is($search['district_id']))
        {
            $where .= ' and district_id='. $search['district_id'];
        }
        
	    if ($this->is($search['is_inside']))
	    {
		    $where .= ' and is_inside='. $search['is_inside'];
	    }

	    if ($this->is($search['ring_road']))
	    {
		    $where .= ' and ring_road='. $search['ring_road'];
	    }

	    if (-1 != $search['status'])
	    {
		    $where .= ' and status='. intval($search['status']);
	    }

	    if ($this->is($search['ring_road_status']))
	    {
		    if ('yes' == $search['ring_road_status'])
		    {
			    $where .= ' and ring_road>0';
		    }
		    elseif ('no' == $search['ring_road_status'])
		    {
			    $where .= ' and ring_road=0';
		    }
	    }

	    if ($this->is($search['source']))
	    {
		    if ('import' == $search['source'])
		    {
			    $where .= ' and suid=0';
		    }
		    elseif ('cs' == $search['source'])
		    {
			    $where .= ' and suid>0';
		    }
	    }

	    if ($this->is($search['has_order']))
	    {
		    $where .= ' and cmid in (select community_id from t_order where status=0 and step>=2)';
	    }

        $data = $this->dao->limit($start, $num)->order('cmid','desc')->getListWhere($where);
        
        $total = 0;
        if ($withTotal && !empty($data));
        {
            $total = $this->dao->getTotal($where);
        }
        
        return $withTotal? array('total'=>$total, 'data'=>$data): $data;
    }

	public function mergeCommunity($fromCmid, $toCmid)
	{
		//查询两个小区的信息
		$fromCommunity = $this->dao->get($fromCmid);
		$toCommunity = $this->dao->get($toCmid);
		if (Conf_Base::STATUS_DELETED == $toCommunity['status'])
		{
			throw new Exception('community: cannot merge to deleted');
		}

		//删除被合并小区,记住是合并到哪个小区了
		$update = array('status' => Conf_Base::STATUS_DELETED, 'merge_to_cmid'=> $toCmid);
		$this->dao->update($fromCmid, $update);

		//被删的作为目标小区的别名
		$alias = $toCommunity['alias'];
		if (false === strpos($alias, $fromCommunity['name']))
		{
			$alias .= ',';
			$alias .= $fromCommunity['name'];
			$update = array('alias' => $alias);
			$this->dao->update($toCmid, $update);
		}

		//更新关联的送货地址,订单;
		//todo: 更新前, 记录被改的订单记录
		$oo = new Order_Order();
		$cc = new Crm2_Construction();
		$where = array('community_id' => $fromCmid);
		$info = array('community_id' => $toCmid);
		$oo->updateByWhere($info, array(), $where);
		$cc->updateBulk($info, array(), $where);
	}

	public function formatRingroadInfo(&$list, $resultField='_ring_road')
	{
		foreach ($list as &$item)
		{
			$district = $item['district_id'];
			$ringroad = $item['ring_road'];
			if (isset(Conf_Area::$AREA[$district][$ringroad]))
			{
				$item[$resultField] = Conf_Area::$AREA[$district][$ringroad];
			}
		}
	}

	public function formatOrderInfo(&$list)
	{
		if (empty($list)) return;

		// 获取订单数量信息
		$dao = new Data_Dao('t_order');
		$oids = Tool_Array::getFields($list, 'cmid');
		$where = sprintf('status=0 and step>=2 and community_id in (%s) group by community_id',
			implode(",", $oids));
		$fields = array('community_id', 'count(1)');
		$orderInfos = $dao->setFields($fields)->getListWhere($where,false);

		$orderInfos = Tool_Array::list2Map($orderInfos, 'community_id');
		//补充信息
		foreach ($list as &$item)
		{
			$communityId = $item['cmid'];
			$item['_order_num'] = isset($orderInfos[$communityId]) ? intval($orderInfos[$communityId]['count(1)']):0;
		}
	}

    
    /**
     * 获取坐标附近的小区.
     * 
     * @param string $lat
     * @param string $lng
     * @param int $limit
     * @param bool $includeSelf 是否包含自己这个坐标
     */
    public function getNearCommunitysByPoint($lat, $lng, $limit=10, $includeSelf=false)
    {
        $where = sprintf('status<>1 and lat>%s-0.1 and lat<%s+0.1 and lng>%s-0.1 and lng<%s+0.1', $lat, $lat, $lng, $lng);
        if (!$includeSelf)
        {
            $where .= sprintf('  and lat<>%s and lng<>%s', $lat, $lng);
        }
        
        $distance = "ACOS(SIN(('$lat' * 3.1415) / 180 ) * SIN((LAT * 3.1415) / 180 ) "
                    . "+ COS(('$lat' * 3.1415) / 180 ) * COS((LAT * 3.1415) / 180 ) * COS(('$lng'* 3.1415) / 180 - (LNG * 3.1415) / 180 ) ) * 6380 as distance";
        
        $fields = array('cmid', 'name', 'city', 'district', 'address', 'lat', 'lng', 'city_id', 'district_id', $distance);
        
        $orderby = 'order by distance';
        
        return $this->dao->setFields($fields)->order($orderby)->limit(0, $limit)->getListWhere($where);
    }
    
    /**
     * 匹配小区.
     * 
     * @rule 
     *      1 距离为0
     *      2 小区的名字完全匹配，并且同一城市，同一城区
     * 
     * @param array $rawCommunityInfo
     */
    public function matchCommunity($rawCommunityInfo)
    {
        $matchCommunityId = 0;
        
        $chkFields = array('lat', 'lng', 'community_name', 'city', 'district');
        foreach($chkFields as $_f)
        {
            if (!isset($rawCommunityInfo)||empty($rawCommunityInfo[$_f])) return $matchCommunityId;
        }
        
//        //通过名称精确匹配
//        $exactCommunitys = $this->getByNameAlias($rawCommunityInfo['community_name']);

        //未匹配成功，通过坐标匹配
        // 获取最近的系统存储的小区
        $nearCommunitys = $this->getNearCommunitysByPoint($rawCommunityInfo['lat'], $rawCommunityInfo['lng'], 30, true);
        
        foreach($nearCommunitys as $_cinfo)
        {
            if ($_cinfo['distance'] == '0') 
            {
                $matchCommunityId = $_cinfo['cmid']; break;
            }
            
            $matchRet = strpos($rawCommunityInfo['community_name'], $_cinfo['name']);
    
            if ($matchRet!==false && $rawCommunityInfo['city']==$_cinfo['city_id']) //$rawCommunityInfo['district']==$_cinfo['district_id']
            {
                $matchCommunityId = $_cinfo['cmid']; break;
            }
        }
        
        return $matchCommunityId;
    }
    
    /**
     * 获取一定范围内的小区.
     * 
     * @param strint $lat
     * @param strint $lng
     * @param int $distance 单位：米
     * @param int $start
     * @param int $num
     * @param bool $includeSelf
     */
    public function getAroundCommunitysByDistance($lat, $lng, $distance=1000, $start=0, $num=20, $includeSelf=true)
    {
        $_distance = sprintf('st_distance (point (lng, lat),point(%s, %s)) *111195 AS distance', $lng, $lat);
        $field = array('cmid', 'name', 'city', 'district', 'address', 'lat', 'lng', $_distance);
        
        $where = 'status<>1';
        if (!$includeSelf)
        {
            $where .= sprintf('  and lat<>%s and lng<>%s', $lat, $lng);
        }
        $where .= ' having distance<'. $distance;
        
        $data = $this->dao->setFields($field)
                          ->limit($start, $num)
                          ->order('distance','asc')
                          ->getListWhere($where);
        
        return $data;
    }
    
    
	public function getTenAroundCommunitys($lat,$lng){
        $data = $this->dao->setFields(array('cmid', 'name', 'city', 'district', 'address', 'lat', 'lng','ROUND(6378.138*2*ASIN(SQRT(POW(SIN(('.$lat.'*PI()/180-lat*PI()/180)/2),2)+COS('.$lat.'*PI()/180)*COS(lat*PI()/180)*POW(SIN(('.$lng.'*PI()/180-lng*PI()/180)/2),2)))*1000) AS distance'))->limit(0,10)->order('distance','asc')->getListWhere('status<>1 and lat<>'.$lat.' AND lng<>'.$lng);
        return $data;
	}

}