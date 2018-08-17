<?php

/**
 * 货位类.
 * 
 * @rule
 *      1. sku -> location(货位) 一对多的关系
 */

class Warehouse_Location extends Base_Func
{
    private $_dao = null;
    private $_historyDao = null;
    
    function __construct()
    {
        parent::__construct();
        
        $this->_dao = new Data_Dao('t_sku_2_location');
        $this->_historyDao = new Data_Dao('t_sku_location_history');
    }
    
    // 添加一个货位
    public function add($location, $wid, $sid=0, $num=0)
    {
        assert(!empty($location) && !empty($wid));
        
        if (!$this->checkLocaton($location))
        {
            return -1;
        }
        
        $info = array(
            'location' => $location,
            'wid' => $wid,
            'sid' => $sid,
            'num' => $num,
            'status' => Conf_Base::STATUS_NORMAL,
        );
        $update = array('status',);
        
        $change = array(
            'num' => $num,
        );
        
        $this->_dao->add($info, $update, $change);
        
        return 1;
    }
    
    public function updateById($id, $upData, $chgData=array())
    {
        assert(!empty($id));
        assert(!empty($upData) || !empty($chgData));
        
        $affectedRow = $this->_dao->update($id, $upData, $chgData);
        
        return $affectedRow;
    }
    
    public function update($sid, $location, $wid, $upData, $chgData=array())
    {
        assert(!empty($location) && !empty($wid));
        assert(!empty($upData) || !empty($chgData));
        
        $where = 'status=0 and sid='.$sid. ' and location="'.$location.'"  and wid='.$wid;
        
        $affectedrows = $this->_dao->updateWhere($where, $upData, $chgData);
        
        return $affectedrows;
    }
    
    /**
     * 校验location的格式；eg：A-01-10-99 or VFLoc_01.
     * 
     * 如果格式错误报错，如果格式【基本正确】补齐为正确格式
     * 基本正确的格式：
     *      A-1-2-99    -> A-01-02-99
     *      A-1         -> A-01-00-00
     *      等等
     * 
     * @param string $location  货位号
     * @param bool  $isContainVirtual 是否包含虚拟货位检查
     */
    public function checkLocaton(&$location, $isContainVirtual=true)
    {
        $newLocation = '';
        $loc = explode('-', str_replace('-', '-', $location)); // 中文横华线转换为半角
        
        // 至少标记两个位置：货区-货架
        if (count($loc)<2)
        {
            return false;
        }
        if (count($loc)>4)
        {
            return false;
        }
        
        // 检查虚拟货位，是否为虚拟货位
        if ($isContainVirtual && count($loc) == 2)
        {
            if ($loc[0] == Conf_Warehouse::VFLAG_PREFIX
                && array_key_exists($loc[1], Conf_Warehouse::$Virtual_Flags))
            {
                return true;
            }
        }
        
        // 错误格式 eg: A-01-0-10
        if (isset($loc[3]) && !empty($loc[3]) && empty($loc[2]))
        {
            return false;
        }
        
        // 货区
        $loc[0] = strtoupper($loc[0]);
        if (strlen($loc[0])!=1 || ord($loc[0])<65 || ord($loc[0])>90)
        {
            return false;
        }
        $newLocation .= $loc[0].'-';
        
        // 货架
        $shelf = intval($loc[1]);
        if ($shelf<=0 || $shelf>=100)
        {
            return false;
        }
        $newLocation .= ($shelf<10? '0'.$shelf: $shelf). '-';
        
        // 架层
        if (!isset($loc[2]) || empty($loc[2]))
        {
            $newLocation .= '00-00';
            $location = $newLocation;
            return true;
        }
        
        $layer = intval($loc[2]);
        if ($layer >= 100)
        {
            return false;
        }
        $newLocation .= ($layer<10? '0'.$layer: $layer). '-';
        
        //层位
        if (!isset($loc[3]))
        {
            $newLocation .= '00';
            $location = $newLocation;
            return true;
        }
        
        $pos = intval($loc[3]);
        if ($pos >= 100)
        {
            return false;
        }
        $newLocation .= ($pos<10? '0'.$pos: $pos);
        
        $location = $newLocation;
        
        return true;
    }
    
    public function getById($id)
    {
        return $this->_dao->get($id);
    }
    
    /**
     * 删除货位.
     */
    public function delById($id, $phyDel=false)
    {
        return $this->_dao->delete($id, $phyDel);
    }
    
    /**
     * 通过唯一建查询货位信息.
     * 
     * @param int $sid
     * @param string $loc
     * @param int $wid
     */
    public function get($sid, $loc, $wid)
    {
        assert(!empty($sid));
        assert(!empty($loc));
        assert(!empty($wid));
        
        $where = array(
            'status' => Conf_Base::STATUS_NORMAL,
            'sid' => $sid,
            'location' => $loc,
            'wid' => $wid,
        );
        
        return $this->_dao->getListWhere($where);
    }
    
    // 查询货位记录，是否创建改货位
    public function getByLocation($location, $wid)
    {
        assert(!empty($location));
        
        $where = array(
            'status' => Conf_Base::STATUS_NORMAL,
            'location' => $location,
            'wid' => $wid,
        );
        
        return $this->_dao->getListWhere($where);
    }
    
    public function getByLocations($locations, $wid)
    {
        assert(!empty($locations));
        
        $where = array(
            'status' => Conf_Base::STATUS_NORMAL,
            'location' => $locations,
            'wid' => $wid,
        );
        
        return $this->_dao->getListWhere($where);
    }
    
    // 查询货位，左匹配查询
    // eg: A-01-%; A-10-03-%
    public function getByLikeLocations($wid, $area, $shelf=0, $layer=0, $start=0, $num=20)
    {
        $_shelf = abs(intval($shelf));
        $_layer = abs(intval($layer));
        assert(!empty($area) && strlen($area)==1);
        assert(ord($area)>=65 && ord($area)<=90); // A~Z
        assert($_shelf<=100 && $_layer<=100);
        
        $location = $area.'-';
        if (!empty($_shelf))
        {
            $location .= $_shelf<10? ('0'.$_shelf): $_shelf;
            $location .= '-';
            
            if (!empty($_layer))
            {
                $location .= $_layer<10? ('0'.$_layer): $_layer;
                $location .= '-';
            }
        }
        $location .= '%';
        
        $where = 'status='.Conf_Base::STATUS_NORMAL. ' and wid='.$wid
                . ' and location like "'. $location. '"';
        
        return $this->_dao->limit($start, $num)->getListWhere($where);
    }
     
    /**
     * 通过sid获取货位信息.
     * 
     * @param array $sid
     * @param int $wid
     * @param string $flag {all:全部; virtual:虚拟货位; actual:实际货位}
     */
    public function getBySid($sid, $wid, $flag='all')
    {
        assert(!empty($sid)&&!empty($wid));
        
        $where = array(
            'status' => Conf_Base::STATUS_NORMAL,
            'sid' => $sid,
            'wid' => $wid,
        );
        
        $ret = $this->_dao->getListWhere($where);
        
        $virtual = $actual = array();
        if ($flag != 'all')
        {
            foreach($ret as $info)
            {
                list($prefix, $_vflag) = explode('-', $info['location'], 2);
                if ($prefix == Conf_Warehouse::VFLAG_PREFIX)
                {
                    $virtual[] = $info;
                }
                else
                {
                    $actual[] = $info;
                }
            }
            
        }
        
        return $flag=='virtual'? $virtual: ($flag=='actual'? $actual: $ret);
    }
    
    /**
     * 根据sids获取未上架的数量.
     * 
     * @param array $sids
     * @param int $wid
     * @param int $vflag 虚拟货位的货位来源类型 {0:全部; 1:入库单; 2:移库单; 3:销售退货单}
     * 
     */
    public function statVirtualNumBySids($sids, $wid, $vflag=0)
    {
        assert(!empty($sids)&&is_array($sids) && !empty($wid));
        
        $fields = array('sid', 'sum(num) as total_num');
        $groupby = ' group by sid';
        $where = sprintf('status=0 and wid=%d and sid in (%s)', $wid, implode(',', $sids));
        
        if ($vflag != 0)
        {
            $where .= ' and location='.Conf_Warehouse::VFLAG_PREFIX.'-'.$vflag;
        }
        else
        {
            $where .= ' and location like "'.Conf_Warehouse::VFLAG_PREFIX. '%"';
        }
        
        $ret = $this->_dao->setFields($fields)->getListWhere($where.$groupby, false);
        
        return Tool_Array::list2Map($ret, 'sid', 'total_num');
    }
    
    /**
     * 通过sid获取货位信息.
     * 
     * @param array $sids
     * @param int $wid
     * @param string $flag {all:全部; virtual:虚拟货位; actual:实际货位}
     * @param int $vflag 虚拟货位的货位来源类型 {0:全部; 1:入库单; 2:移库单; 3:销售退货单}
     */
    public function getLocationsBySids($sids, $wid, $flag='all', $vflag=0)
    {
        assert(!empty($sids)&&is_array($sids) && !empty($wid));
        
        $where = 'status='.Conf_Base::STATUS_NORMAL. ' and wid='.$wid
                . ' and sid in ('. implode(',', $sids). ')';
        
        $ret = $this->_dao->getListWhere($where);
    
        $virtual = $actual = array();
        foreach($ret as &$one)
        {
            $one['free_num'] = $one['num']-$one['occupied'];
            
            list($prefix, $_vflag) = explode('-', $one['location'], 2);
            if ($prefix == Conf_Warehouse::VFLAG_PREFIX)
            {
                if($vflag!=0)
                {
                    if ($_vflag==$vflag)
                    {
                        $virtual[] = $one;
                    }
                }
                else
                {
                    $virtual[] = $one;
                }
            }
            else
            {
                $actual[] = $one;
            }
        }
        
        if ($flag == 'virtual')
        {
            return $virtual;
        }
        else if ($flag == 'actual')
        {
            return $actual;
        }
        
        return array('actual'=>$actual, 'virtual'=>$virtual,);
    }
    
    public function search($searchConf, $start=0, $num=20, $field=array('*'), $order=array('sid', 'desc'))
    {
        //仓库wid是必须的，否则搜索结果没有意义
        assert(!empty($searchConf['wid']));
        
        $where = 'status='. Conf_Base::STATUS_NORMAL;
        $where .= ' and wid='. $searchConf['wid'];
        
        if ($this->is($searchConf['sid']))
        {
            $where .= ' and sid='. $searchConf['sid'];
        }
        else if ($this->is($searchConf['sids']))
        {
            $where .= ' and sid in ('. implode(',', $searchConf['sids']).')';
        }
        else if ($this->is($searchConf['is_used']))
        {
            if ($searchConf['is_used'] == 1)
            {
                $where .= ' and sid != 0';
            }
            else
            {
                $where .= ' and sid = 0';
            }
        }
        else if ($this->is($searchConf['title']))
        {
            $where .= sprintf(' and sid in (select sid from t_sku where title like "%%%s%%" or alias like "%%%s%%")',
                        $searchConf['title'], $searchConf['title']);
        }
        
        if ($this->is($searchConf['un_shelved']))
        {
            $where .= ' and num>0 and location like "VFLoc%"';
        }
        else
        {
            $locationWhere = $this->_genLocationForSearchWhere($searchConf);

            if (!empty($locationWhere))
            {
                $where .= $locationWhere;
            }
            else
            {
                $where .= ' and (location not like "VFLoc%" or (location like "VFLoc%" and num>0))';
            }
        }
        
        $total = $this->_dao->getTotal($where);
        $list = $this->_dao->setFields($field)
                           ->order($order[0], $order[1])
                           ->limit($start, $num)
                           ->getListWhere($where);
        
        return array('list'=>$list, 'total'=>$total);
    }

    public function getRawWhere($where, $start = 0, $num = 20, $field = array('*'), $order = array('sid', 'desc'))
    {
        assert(!empty($where));

        $list = $this->_dao->setFields($field)
            ->order($order[0], $order[1])
            ->limit($start, $num)
            ->getListWhere($where);

        return $list;
    }

	public function exportSearch($searchConf, $start = 0, $num = 0)
	{
		//仓库wid是必须的，否则搜索结果没有意义
		assert(!empty($searchConf['wid']));

		$where = sprintf('status=%d AND wid=%d AND location NOT LIKE "VFLoc%%"', Conf_Base::STATUS_NORMAL, $searchConf['wid']);

		if (!empty($searchConf['lstart']))
		{
			$where .= sprintf(' AND location>="%s"', $searchConf['lstart']);
		}

		if (!empty($searchConf['lend']))
		{
			$where .= sprintf(' AND location <="%s"', $searchConf['lend']);
		}

		$total = $this->_dao->getTotal($where);
		$list = $this->_dao->order('order by location asc, sid asc')->limit($start, $num)->getListWhere($where);

		return array('list'=>$list, 'total'=>$total);
	}
    
    private function _genLocationForSearchWhere($searchConf)
    {
        $location = '';
        if($this->is($searchConf['area']))
        {
            $location = $searchConf['area'];
        }
        
        if ($this->is($searchConf['shelf']))
        {
            $intShelf = intval($searchConf['shelf']);
            $strShelf = $intShelf>=10? $intShelf: ('0'.$intShelf);
            
            $location .= !empty($location)? ('-'.$strShelf): ('%-'.$strShelf);
        }
        else if (!empty($location))
        {
            $location .= '-%';
        }
        
        if ($this->is($searchConf['layer']))
        {
            $intLayer = intval($searchConf['layer']);
            $strShelf = $intLayer>=10? $intLayer: ('0'.$intLayer);
            
            $location .= !empty($location)? ('-'.$strShelf.'%'): ('%-%-'.$strShelf.'%');
        }
        
        if (!empty($location) && substr($location, -1, 1)!='%')
        {
            $location .= '%';
        }
        
        return !empty($location)? ' and location like "'.$location.'"': '';
    }
    
    //////////////////////// t_sku_location_history   ///////////////////////////////
    
    public function addHistory($sid, $wid, $srcLoc, $info)
    {
        assert( !empty($wid) );
		assert( !empty($sid) );
        assert( !empty($srcLoc) );
        assert( !empty($info) );

		$info['wid'] = $wid;
		$info['sid'] = $sid;
        $info['src_loc'] = $srcLoc;
		$info['ctime'] = $info['mtime'] = date('Y-m-d H:i:s');
        
        $res = $this->_historyDao->add($info);
		$id = $res['insertid'];

		return $id;
    }

	public function oneSkuManyLocations($wid, $start = 0, $num = 20, $field = array('*'), $order = array('sid', 'desc'))
	{
		//仓库wid是必须的，否则搜索结果没有意义
		assert($wid);

		$where = sprintf('status=%d AND wid=%d', Conf_Base::STATUS_NORMAL, $wid);
		$where .= ' AND (location NOT LIKE "VFLoc%%") GROUP BY sid HAVING count(1) > 1';

		$sidList = $this->_dao->setFields(array('id', 'sid'))->limit($start, $num)->getListWhere($where);
		$sids = Tool_Array::getFields($sidList, 'sid');
        
        $list = array();
        $total = 0;
        if (!empty($sids))
        {
            $where2 = sprintf('status=%d AND wid=%d AND (location NOT LIKE "VFLoc%%") AND sid IN (%s)', Conf_Base::STATUS_NORMAL, $wid, implode(',', $sids));
            $list = $this->_dao->setFields($field)->order($order[0], $order[1])->getListWhere($where2);
            $total = $this->_dao->getTotal($where, 'distinct(sid)');
        }
        
		return array('list' => $list, 'total' => $total);
	}

	public function oneLocationManySkus($wid, $start = 0, $num = 20, $field = array('*'), $order = array('sid', 'desc'))
	{
		//仓库wid是必须的，否则搜索结果没有意义
		assert($wid);

		$where = sprintf('status=%d AND wid=%d', Conf_Base::STATUS_NORMAL, $wid);
		$where .= ' AND (location NOT LIKE "VFLoc%%")';

		$list1 = $this->_dao->setFields(array('id', 'sid', 'location'))->getListWhere($where);
		$location1 = Tool_Array::getFields($list1, 'location');
		$location2 = array_unique($location1);
		$location3 = array_diff_key($location1, $location2);
		$location4 = array_unique($location3);
		$location5 = array_slice($location4, $start, $num);
		$where2 = sprintf('status=%d AND wid=%d AND (location NOT LIKE "VFLoc%%") AND location IN ("%s")', Conf_Base::STATUS_NORMAL, $wid, implode('","', $location5));
		$list = $this->_dao->setFields($field)->order($order[0], $order[1])->getListWhere($where2);
		$total = count($location4);

		return array('list' => $list, 'total' => $total);
	}
}