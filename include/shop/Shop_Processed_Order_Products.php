<?php

class Shop_Processed_Order_Products  extends Base_Func
{
    const TYPE_COMBIN = 1;      //组装商品
    const TYPE_PART = 2;        //部件商品
    
    private $_dao = null;
    
    function __construct()
    {
        $this->_dao = new Data_Dao('t_processed_order_products');
    }
    
    public function getProducts($id)
    {
        assert(!empty($id));
        
        return $this->_dao->getListWhere('id='.$id);
    }

    /**
     * sku是否被加工过.
     * 
     * @param int $sid
     */
    public function skuHadProcessd($sid)
    {
        $where = 'sid='. $sid;
        
        $ret = $this->_dao->getTotal($where);
        
        return $ret>0? true: false;
    }

    /**
     * 为加工单添加商品.
     * 
     * @param int $id
     * @param array $products
     */
    public function addProducts($id, $products)
    {
        assert(!empty($id));
        assert(!empty($products));
        
        //check products
        $chkSt = true;
        foreach($products as $pinfo)
        {
            if (!$this->_checkBaseProduct($pinfo))
            {
                $chkSt = false;
                break;
            }
        }
        
        if (!$chkSt) return false;
        
        foreach($products as $pinfo_)
        {
            $pinfo_['id'] = $id;
            $pinfo_['num'] = abs($pinfo_['num']);
            $this->_dao->add($pinfo_);
        }
        
        return true;
    }
    
    /**
     * 检测库存，并分配货位 - 组合售卖使用.
     * 
     * @param int $combinSid 
     * @param int $wid
     * @param int $num          组合数量
     * @param array $partSkuList    {array('sid'=>xx, 'location'=>'xx),...}
     */
    public function checkAndDistributeLocation4Combin($combinSid, $wid, $num, &$partSkuList)
    {
        // 获取组合商品的组合比例
        $ss = new Shop_Sku();
        $combinSkuInfo = $ss->get($combinSid);
        $combinInfos = Tool_Array::list2Map(Shop_Helper::parseRelationSkus($combinSkuInfo['rel_sku']), 'sid');
        
        foreach($combinInfos as &$c)
        {
            $c['need_num'] = $c['num']*$num;
        }
        
        // 获取库存
        $wl = new Warehouse_Location();
        $sids = Tool_Array::getFields($partSkuList, 'sid');
        $_locStocks = $wl->getLocationsBySids($sids, $wid, 'actual');
        foreach($_locStocks as $lsone)
        {
            $locStocks[$lsone['sid'].':'.$lsone['location']] = $lsone['num'];
        }
        
        // 分配货位库存
        foreach ($partSkuList as $k => &$linfo)
        {
            $linfo['type'] = self::TYPE_PART;   //部件商品
            
            $sid = $linfo['sid'];
            $loc = $linfo['location'];
            
            $needNum = $combinInfos[$sid]['need_num'];
            $lstock = $locStocks[$sid.':'.$loc];
            //货位库存为0 unset
            if ($lstock == 0)
            {
                unset($partSkuList[$k]); continue;
            }
            
            if (!isset($linfo['num']))
            {
                $linfo['num'] = 0;
            }
            
            if ($needNum == 0)
            {
                unset($partSkuList[$k]);
            }
            else if ($lstock >= $needNum)
            {
                $linfo['num'] += 0-$needNum;    // 组合时，需要减库存
                unset($combinInfos[$sid]);
            }
            else 
            {
                $linfo['num'] += 0-$lstock;     // 组合时，需要减库存
                $combinInfos[$sid]['need_num'] -= $lstock;
            }
            
        }
        // 重组部件数据结构
        $_partSkuList = array();
        $_newPartLocs = array();
        foreach($partSkuList as $pone)
        {
            if (!isset($_partSkuList[$pone['sid']]))
            {
                $_partSkuList[$pone['sid']] = array(
                    'sid' => $pone['sid'],
                    'type' => self::TYPE_PART,
                    'num' => 0-abs($pone['num']),
                );
            }
            else
            {
                $_partSkuList[$pone['sid']]['num'] += 0-abs($pone['num']);
            }
            $_newPartLocs[$pone['sid']][] = array('loc'=>$pone['location'], 'num'=>abs($pone['num']));
        }
        
        $partSkuList = $_partSkuList;
        $newPartLocs = Warehouse_Location_Api::genLocationAndNum($_newPartLocs);
        foreach($partSkuList as &$pone)
        {
            $pone['location'] = $newPartLocs[$pone['sid']];
        }
        
        return !empty($combinInfos)? false: true;
    }
    
    /**
     * 检测库存，并分配货位 - 整转零售使用.
     * 
     * @param int $wid
     * @param array $combinSkus
     * @param array $partSkus
     */
    public function checkAndDistributeLocation4Split($wid, &$combinSkus, &$partSkus)
    {
        // 获取组合商品的组合比例
        $ss = new Shop_Sku();
        $combinSid = Tool_Array::getFields($combinSkus, 'sid');
        
        $combinSkuInfo = $ss->get($combinSid[0]);
        $combinInfos = Tool_Array::list2Map(Shop_Helper::parseRelationSkus($combinSkuInfo['rel_sku']), 'sid');
        
        $wl = new Warehouse_Location();
        $_combinLstock =$wl->getBySid($combinSid[0], $wid, 'actual');
        foreach($_combinLstock as $_cone)
        {
            $combinLstock[$_cone['sid'].':'.$_cone['location']] = $_cone['num'];
        }
        
        $totalNum = 0;
        $comNewLocs = array();
        foreach($combinSkus as &$cone)
        {
            $combinSid = $cone['sid'];
            $k = $combinSid.':'.$cone['location'];
            $_num = isset($combinLstock[$k])? $combinLstock[$k]: 0;
            if ($cone['num'] > $_num)
            {
                throw new Exception('货位库存不足：'.$cone['location']);
            }
            
            $totalNum += $cone['num'];
            $comNewLocs[$cone['sid']][] = array('loc'=>$cone['location'], 'num'=>$cone['num']);
        }
        if ($totalNum == 0)
        {
            throw new Exception('转换总数为0， 请修改！');
        }
        
        $genLoc = Warehouse_Location_Api::genLocationAndNum($comNewLocs);
        $combinSkus = array(
            'sid' => $combinSid,
            'num' => 0-$totalNum,
            'location' => $genLoc[$combinSid],
            'type' => self::TYPE_COMBIN,
        );
        
        
        foreach($partSkus as &$pone)
        {
            $pone['type'] = self::TYPE_PART;
            $rate = $combinInfos[$pone['sid']]['num'];
            $pone['num'] = $totalNum*$rate;
        }
        
        return true;
    }
    
    
    private function _checkBaseProduct($pinfo)
    {
        $chkSkuId = isset($pinfo['sid'])&&!empty($pinfo['sid'])? true: false;
        $chkType = isset($pinfo['type'])&&!empty($pinfo['type'])? true: false;
        $chkNum = isset($pinfo['num'])&&!empty($pinfo['num'])? true: false;
        $chkLocation = isset($pinfo['location'])&&!empty($pinfo['location'])? true: false;
        
        $chkSt = false;
        if ($chkSkuId && $chkType && $chkNum && $chkLocation)
        {
            $chkSt = true;
        }
        
        return $chkSt;
    }
}