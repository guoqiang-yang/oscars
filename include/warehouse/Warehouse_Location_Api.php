<?php

class Warehouse_Location_Api extends Base_Api
{
    /**
     * 添加货位.
     */
    public static function addLocation($location, $wid, $sid=0, $num=0)
    {
        if (empty($location) || empty($wid))
        {
            return 0;
        }
        
        $wl = new Warehouse_Location();
        $ret = $wl->add($location, $wid, $sid, $num);
        
        return $ret;
    }
    
    /**
     * 通过唯一建查询货位信息.
     */
    public static function getLocation($sid, $loc, $wid)
    {
        $wl = new Warehouse_Location();
        
        return $wl->get($sid, $loc, $wid);
    }

    /**
     * 通过wid、sids批量获取货位库存信息
     *
     * @param $sids
     * @param $wid
     * @param string $flag
     * @param int $vflag
     * @return array
     */
    public static function getSkuLocationBySids($sids, $wid, $flag='all', $vflag=0)
    {
        $wl = new Warehouse_Location();
        $ret = $wl->getLocationsBySids($sids, $wid, $flag, $vflag);

        if ($ret)
        {
            $ss = new Shop_Sku();
            $sids = array_unique(Tool_Array::getFields($ret, 'sid'));
            $skuInfos = Tool_Array::list2Map($ss->getBulk($sids), 'sid');

            foreach ($ret as &$one)
            {
                $one['_skuInfo'] = array_key_exists($one['sid'], $skuInfos)?$skuInfos[$one['sid']]: array();
            }
        }

        return $ret;
    }

    /**
     * 删除货位.
     */
    public static function delLocation($id, $skuid, $location)
    {
        $wl = new Warehouse_Location();
        
        $locInfo = $wl->getById($id);
        
        if (empty($locInfo) || $locInfo['sid']!=$skuid || $locInfo['location']!=$location)
        {
            throw new Exception('删除的货位，信息错误！');
        }
        
        if ($locInfo['num'] != 0)
        {
            throw new Exception('货位库存不为空，不能删除！');
        }
        
        $wl->delById($id, TRUE);
    }


    /**
     * 搜索货位.
     */
    public static function searchLocation($search, $start=0, $num=20, $field=array('*'), $order=array('id', 'desc'))
    {
        $wl = new Warehouse_Location();
        
        $ret = $wl->search($search, $start, $num, $field, $order);
        
        if ($ret['total'])
        {
            $ss = new Shop_Sku();
            $sids = array_unique(Tool_Array::getFields($ret['list'], 'sid'));
            $skuInfos = Tool_Array::list2Map($ss->getBulk($sids), 'sid');

            foreach($ret['list'] as &$one)
            {
                $one['_skuInfo'] = array_key_exists($one['sid'], $skuInfos)?
                                $skuInfos[$one['sid']]: array();
                
                $one['is_virtual'] = 0;
                if (strpos($one['location'], Conf_Warehouse::VFLAG_PREFIX) === 0)
                {
                    $one['is_virtual'] = 1;
                    $virtualNumPart = str_replace(Conf_Warehouse::VFLAG_PREFIX.'-', '', $one['location']);
                    $one['virtual_type'] = Conf_Warehouse::$Virtual_Flags[$virtualNumPart]['name'];
                }
            }
        }
        
        return $ret;
    }
    
    /**
     * 检查货位好是否合法.
     */
    public static function checkLocaton(&$location, $isContainVirtual=true)
    {
        $wl = new Warehouse_Location();
        
        return $wl->checkLocaton($location, $isContainVirtual);
    }
    
    /**
     * 通过sid获取未上架的单据.
     * 
     * @param int $sid
     * @param int $wid
     * @param string $vLoc
     */
    public static function getUnshelvedBills($sid, $wid, $vLoc)
    {
        $response = array();
        list($preVLoc, $flagVLoc) = explode('-', $vLoc);
        
        if ($preVLoc != Conf_Warehouse::VFLAG_PREFIX)
        {
            return $response;
        }
        
        //获取近15天未上架的单据
        $data = date('Y-m-d 00:00:00', strtotime("-30 day"));
        
        $products = array();
        switch ($flagVLoc)
        {
            case Conf_Warehouse::VFLAG_STOCK_IN:
                $wsip = new Warehouse_Stock_In_Product();
                $field = array('id', 'sid', 'num', 'location', 'ctime');
                $where = sprintf('status=0 and sid=%d and id in '.
                                 ' (select id from t_stock_in where status=0 and step<%d and wid=%d and ctime>="%s" and source!=%d)',
                                $sid, Conf_Stock_In::STEP_SHELVED, $wid, $data, Conf_In_Order::SRC_TEMPORARY);
                $stockInProducts = $wsip->getByRawWhere($where, 't_stock_in_product', $field);
                
                foreach($stockInProducts as $one)
                {
                    $products[] = array(
                        'id' => $one['id'],
                        'num' => $one['num'],
                        'ctime' => $one['ctime'],
                        'desc' => '入库单',
                        'link' => '/warehouse/edit_stock_in.php?id='. $one['id'],
                    );
                }
                break;
            case Conf_Warehouse::VFLAG_SHIFT:
                $wssp = new Warehouse_Stock_Shift_Product();
                $field = array('ssid', 'sid', 'num', 'ctime');
                $where = sprintf('status=0 and sid=%d and ssid in '.
                            '(select ssid from t_stock_shift where des_wid=%d and step>=%d and step<%d and ctime>="%s")',
                            $sid, $wid, Conf_Stock_Shift::STEP_STOCK_IN, Conf_Stock_Shift::STEP_SHELVED, $data);
                $shiftProducts = $wssp->getByRawWhere($where, $field);
                
                foreach($shiftProducts as $one)
                {
                    $products[] = array(
                        'id' => $one['ssid'],
                        'num' => $one['num'], 
                        'ctime' => $one['ctime'],
                        'desc' => '调拨单',
                        'link' => '/warehouse/stock_shift_detail.php?ssid='. $one['ssid'],
                    );
                }
                
                break;
            case Conf_Warehouse::VFLAG_ORDER_REFUND:
                $oo = new Order_Order();
                $field = array('oid', 'rid', 'num', 'ctime');
                $where = sprintf('status=0 and sid=%d and rid in '.
                                 '(select rid from t_refund where status=0 and wid=%d and step>=%d and step<%d and ctime>="%s")',
                                $sid, $wid, Conf_Refund::REFUND_STEP_IN_STOCK, Conf_Refund::REFUND_STEP_SHELVED, $data);
                $refundProducts = $oo->getOrderProductsByRawWhere($where, 0, 0, $field);
                
                foreach($refundProducts['data'] as $one)
                {
                    $products[] = array(
                        'id' => $one['rid'],
                        'num' => $one['num'],
                        'ctime' => $one['ctime'],
                        'desc' => '退货单',
                        'link' => '/order/edit_refund_new.php?rid='. $one['rid'],
                    );
                }
                
                break;
            default:
                break;
        }
        
        return $products;
    }
    
    /**
     * 获取将要上架的单据的详情，和商品列表，和sid对应虚拟货位的数据.
     * 
     * @param int $billId   单据id 
     * @param int $type     单据类型    Conf_Warehouse::$Virtual_Flags
     */
    public static function getBillDetailAndProducts($billId, $type)
    {
        $res = array(
            'info' => array(), 
            'products' => array(), 
            'vlocation' => array(),
            'alocation' => array(),     //可使用货架
            'shelved_res' => array(),   //已上架信息
            'chk_val' => 1,  //检测是否可以上架：0-ok; !0-not ok
            'chk_msg' => '非法操作，不能上架', //检测结果
        );
        
        if (empty($billId) || empty($type))
        {
            return $res;
        }
        
        switch($type)
        {
            case Conf_Warehouse::VFLAG_STOCK_IN:
                $wsi = new Warehouse_Stock_In();
                $wsip = new Warehouse_Stock_In_Product();
                
                $res['info'] = $wsi->get($billId);
                $res['products'] = Tool_Array::list2Map($wsip->getProductsOfStockIn($billId),'sid');
                
                // 临采采购，不上架
                if ($res['info']['source'] == Conf_In_Order::SRC_TEMPORARY)
                {
                    $res['chk_val'] = 20;
                    $res['chk_msg'] = '临采入库单，不需要上架操作！';
                }
                
                break;
            case Conf_Warehouse::VFLAG_SHIFT:
                $wss = new Warehouse_Stock_Shift();
                $wssp = new Warehouse_Stock_Shift_Product();
                
                $res['info'] = $wss->getById($billId);
                $res['info']['wid'] = $res['info']['des_wid'];
                $res['products'] = Tool_Array::list2Map($wssp->get($billId), 'sid');
                
                //处理products 下标
                foreach($res['products'] as &$p)
                {
                    $p['location'] = $p['to_location'];
                    
                    // 已上架信息
                    $res['shelved_res'][$p['sid']]['shelved'] = array('loc'=>'', 'num'=>0); //已上架
                    $res['shelved_res'][$p['sid']]['damaged'] = array('loc'=>'', 'num'=>0); //损坏
                    $res['shelved_res'][$p['sid']]['loss'] = array('loc'=>'', 'num'=>0);    //预盘亏/盘亏
                    if (!empty($p['to_location']))
                    {
                        $res['shelved_res'][$p['sid']]['shelved'] = array('loc'=>$p['to_location'], 'num'=>$p['num']-$p['abnormal_num']);
                    }
                    // 异常处理信息
                    if (!empty($p['abnormal_num']) && !empty($p['abnormal_location']))
                    {
                        $_tmpPinfo = array($p['sid']=>array('num'=>$p['abnormal_num'], 'abnormal_location'=>$p['abnormal_location']));
                        self::parseLocationAndNum($_tmpPinfo, 'abnormal_location');
                        
                        foreach($_tmpPinfo[$p['sid']]['_abnormal_location'] as $_one)
                        {
                            if ($_one['loc'] == Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_DAMAGED]['flag'])
                            {
                                $res['shelved_res'][$p['sid']]['damaged'] = array('loc'=>$_one['loc'], 'num'=>$_one['num']);
                            }
                            else if ($_one['loc'] == Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_LOSS]['flag'])
                            {
                                $res['shelved_res'][$p['sid']]['loss'] = array('loc'=>$_one['loc'], 'num'=>$_one['num']);
                            }
                        }
                    }
                }
                
                break;
            case Conf_Warehouse::VFLAG_ORDER_REFUND:
                $or = new Order_Refund();
                
                $res['info'] = $or->get($billId);
                $res['products'] = Tool_Array::list2Map($or->getProductsOfRefund($billId), 'sid');
                
                foreach($res['products'] as $sid => &$pinfo)
                {
                    if ($pinfo['picked'] == 0 || $pinfo['outsourcer_id'] > 0)
                    {
                        unset($res['products'][$sid]);
                    }
                    else
                    {
                        $pinfo['num'] = $pinfo['picked']; //退货单的picked存储的是上架数量
                    }
                }
                
                break;
            
            default;
                break;
        }
        
        $res['info']['bill_name'] = Conf_Warehouse::$Virtual_Flags[$type]['name'];
        $wid = $res['info']['wid'];
        
        $sids = array_keys($res['products']);
        if (empty($sids))
        {
            return $res;
        }
        
        if (!Conf_Warehouse::isUpgradeWarehouse($wid))
        {
            $res['chk_val'] = 10;
            $res['chk_msg'] = $res['info']['wid'].'号仓库不支持上架操作';
            
            return $res;
        }
        
        $wl = new Warehouse_Location();
        $locInfos = $wl->getLocationsBySids($sids, $wid, 'all', $type); 
        $res['vlocation'] = Tool_Array::list2Map($locInfos['virtual'], 'sid');
        
        foreach($locInfos['actual'] as $aloc)
        {
            $res['alocation'][$aloc['sid']][] = $aloc['location'];
        }
        
        $res['chk_val'] = 0;
        $res['chk_msg'] = '';
        $needShelved = false;   //是否需要上架
        
        foreach($res['products'] as $sid => $info)
        {
            if(!$needShelved && empty($info['location']) )
            {
                $needShelved = true;
            }
            
            // 未上架商品检测
            $abnormalNum = isset($info['abnormal_num'])? $info['abnormal_num']: 0;
            if (empty($info['location']) && $info['num']-$abnormalNum >$res['vlocation'][$sid]['num'])
            {
                $res['chk_val'] = 11;
                $res['chk_msg'] = '虚拟货物数量不足，不能上架，请核查！';
                break;
            }
        }
        
        if ($res['chk_val']==0 && !$needShelved)
        {
            $res['chk_val'] = 12;
            $res['chk_msg'] = '已经全部上架';
        }
        
        return $res;
    }
    
    /**
     * 单据的商品上架.
     * 
     * @param int $objid
     * @param int $type
     * @param array $products
     */
    public static function billShelved($objid, $type, $products, $_uid)
    {
        //1 检测是否可以上架，以及是否为完全上架
        $isComplateShelved = true;
        $wid = 0;
        self::_checkWaitShelved($objid, $type, $products, $isComplateShelved, $wid);
        
        //锁库判断
        $lockedRet = Conf_Warehouse::isLockedWarehouse($wid);
        if ($lockedRet['st'])
        {
            throw new Exception($lockedRet['msg']);
        }
            
        //2 商品上架 && 更新相关数据
        $wl = new Warehouse_Location();
        //$ooc = new Order_Occupied();
        $vLocation = Conf_Warehouse::$Virtual_Flags[$type]['flag'];
        $sids = array();
        foreach($products as $product)
        {
            //虚拟货位减库存
            $vChgData = array('num' => (0-abs($product['num'])) );
            $wl->update($product['sid'], $vLocation, $wid, array(), $vChgData);
            
            //实际货位加库存
            
            //多对多的更新逻辑
            $wl->add($product['loc'], $wid, $product['sid'], $product['num']);
            
            $sids[] = $product['sid'];
            
            //一对多更新逻辑
//            $aUpData = array('sid'=>$product['sid']);
//            $aChgData = array('num' => abs($product['num']));
//            $wl->update($product['up_sid'], $product['loc'], $wid, $aUpData, $aChgData);
            
            //刷订单商品的占用
            //$ooc->refreshOccupiedOfSkuWhen($product['sid'], $wid);
        }
        
        //新刷新占用逻辑：addby:guoqiang/2017-06-12
        $wso = new Warehouse_Stock_Occupied();
        $wso->autoRefreshOccupied($wid, $sids);

        // 单据更新上架状态，及其单据上的商品标记货位
        switch($type)
        {
            case Conf_Warehouse::VFLAG_STOCK_IN:
                $wsi = new Warehouse_Stock_In();
                $wsip = new Warehouse_Stock_In_Product();
                
                foreach($products as $one)
                {
                    $info = array('location'=>$one['loc']);
                    $wsip->updateProduct($objid, $one['sid'], $info);

                    //更新成本
                    self::_updateCost($wid, $one['sid'], $one['num'], $one['price']);
                }
                
                $step = $isComplateShelved? Conf_Stock_In::STEP_SHELVED: Conf_Stock_In::STEP_PART_SHELVED;
                $wsi->update($objid, array('step'=>$step, 'shelved_suid'=>$_uid));
                break;
            case Conf_Warehouse::VFLAG_SHIFT:
                $wss = new Warehouse_Stock_Shift();
                $wspi = new Warehouse_Stock_Shift_Product();
                
                foreach($products as $one)
                {
                    $info = array('to_location'=>$one['loc']);
                    $wspi->update($objid, $one['sid'], $info);

                    //更新成本
                    self::_updateCost($wid, $one['sid'], $one['num'], $one['cost']);
                }
                
                $step = $isComplateShelved? Conf_Stock_Shift::STEP_SHELVED: Conf_Stock_Shift::STEP_PART_SHELVED;
                $wss->update($objid, array('step'=>$step, 'shelved_suid'=>$_uid));
                break;
            
            case Conf_Warehouse::VFLAG_ORDER_REFUND:
                $or = new Order_Refund();
                
                foreach($products as $one)
                {
                    $upWhere = array(
                        'status' => Conf_Base::STATUS_NORMAL,
                        'rid' => $objid,
                        'wid' => $wid,
                        'sid' => $one['sid'],
                    );
                    $info = array('location'=>$one['loc']);
                    $or->updateRefundProductByWhere($upWhere, $info);
                }
                
                $step = $isComplateShelved? Conf_Refund::REFUND_STEP_SHELVED: Conf_Refund::REFUND_STEP_PART_SHELVED;
                $or->update($objid, array('step'=>$step, 'shelved_suid'=>$_uid));
                break;
            default:
                break;
        }
        
        
    }
    
    /**
     * 检测是否可以上架.
     */
    private static function _checkWaitShelved($objid, $type, &$products, &$isComplateShelved=true, &$wid=0)
    {
        $objAllInfos = self::getBillDetailAndProducts($objid, $type);
        $wid = $objAllInfos['info']['wid'];
        
        if ($objAllInfos['chk_val'] != 0)
        {
            throw new Exception($objAllInfos['chk_msg']);
        }
        
        // 检查上架商品是否有效
        $locations = array();
        foreach($products as $one)
        {   
            if (!array_key_exists($one['sid'], $objAllInfos['products'])
                || !empty($objAllInfos['products'][$one['sid']]['location']) )
            {
                throw new Exception('SKU:'.$one['sid'].'已经上架完成！！');
            }
            
            $locations[] = $one['loc'];
        }
        
        // 检查是否为完全上架
        foreach($objAllInfos['products'] as $one)
        {
            if (empty($one['location']) && 
                !array_key_exists($one['sid'], $products))
            {
                $isComplateShelved = false;
                break;
            }
        }
        
        // 检查上线商品的库位是否合法; 一对多的检查逻辑
//        $wl = new Warehouse_Location();
//        $locationInfos = Tool_Array::list2Map($wl->getByLocations($locations, $wid), 'location');
//        
//        foreach($products as &$one)
//        {
//            if (!array_key_exists($one['loc'], $locationInfos))
//            {
//                throw new Exception('货位：'. $one['loc'].'在该库不存在！请先创建库位，再上架！');
//            }
//            
//            
//            $_locInfo = $locationInfos[$one['loc']];
//            if(!empty($_locInfo['sid']) && $_locInfo['sid']!=$one['sid'])
//            {
//                throw new Exception('货位：'. $_locInfo['location'].'已经被SKUID='.$_locInfo['sid'].'占用！请重新填写！');
//            }          
//            $one['up_sid'] = $_locInfo['sid'];
//        }
        
        // 补齐数据：补齐上架商品的数量
        foreach($products as &$one)
        {
            $abnormalNum = isset($objAllInfos['products'][$one['sid']]['abnormal_num'])?
                            $objAllInfos['products'][$one['sid']]['abnormal_num']: 0;
            $one['num'] = $objAllInfos['products'][$one['sid']]['num']-$abnormalNum;
            $one['price'] = $objAllInfos['products'][$one['sid']]['price'];
            $one['cost'] = $objAllInfos['products'][$one['sid']]['cost'];
        }
    }
    
    /**
     * 为sid分配货位库存.
     * 
     * @param array $products   array[0]=array('sid'=>xxx, 'num'=>10)
     * @param int $wid
     * @param int $sort {1:从多到少分配 2:从少到多分配}
     * @param bool $statVnum 是否统计虚采数量
     */
    public static function distributeNumFromLocation($products, $wid, $sort=1, $statVnum=false)
    {
        $resLocs = array();
        $vnum = array();
        
        $wl = new Warehouse_Location();
        
        $sids = Tool_Array::getFields($products, 'sid');
        $locations = $wl->getLocationsBySids($sids, $wid, 'actual');
        
        Tool_Array::sortByField($locations, 'free_num', $sort==1?'desc':'asc');
        
        $groupLocBySid = array();
        foreach($locations as $loc)
        {
            $groupLocBySid[$loc['sid']][] = $loc;
        }
        
        foreach($products as $p)
        {
            $sid = $p['sid'];
            
            // 库里没有该商品
            if (!array_key_exists($sid, $groupLocBySid))
            {
                if (!$statVnum)
                {
                    return -2;
                }
                else
                {
                    $vnum[$sid] = $p['num'];
                    continue;
                }
            }
            
            $num = $p['num'];
            foreach($groupLocBySid[$sid] as $loc)
            {
                $freeNum = $loc['free_num'];
                if ($freeNum <= 0)
                {
                    continue;
                }
                if($freeNum - $num >= 0)
                {
                    $resLocs[$sid][] = array('loc'=>$loc['location'], 'num'=>$num);
                    $num = 0;
                    break;
                }
                else
                {
                    $resLocs[$sid][] = array('loc'=>$loc['location'], 'num'=>$freeNum);
                    $num -= $freeNum;
                }
                
            }
            
            // 完全空采商品，记录货位.
            if ($statVnum && (!isset($resLocs[$sid]) || empty($resLocs[$sid])) )
            {
                $resLocs[$sid][] = array('loc'=>$loc['location'], 'num'=>0);
            }
            
            // 库存不足
            if ($num > 0)
            {
                if (!$statVnum)
                {
                    return -3;
                }
                else
                {
                    $vnum[$sid] = $num;
                    continue;
                }
            }
        }
        
        if (!$statVnum)
        {
            return self::genLocationAndNum($resLocs);
        }
        else
        {
            $ret = array();
            $formatLocs = self::genLocationAndNum($resLocs);
            
            foreach($products as $p)
            {
                $ret[$p['sid']]['loc'] = array_key_exists($p['sid'], $formatLocs)? $formatLocs[$p['sid']]:'';
                $ret[$p['sid']]['vnum'] = array_key_exists($p['sid'], $vnum)? $vnum[$p['sid']]:0;
                $ret[$p['sid']]['raw_loc'] = array_key_exists($p['sid'], $formatLocs)? $resLocs[$p['sid']]:array();
            }
            
            return $ret;
        }
    }
    
    /**
     * 生成标准格式的Location和Num的关系.
     * 
     * @param array $locations
     *      $locations = array(
     *          'sid1' => array( array('loc'=>c-01-01-01, 'num'=>10)),
     *          'sid2' => array( array('loc'=>c-01-01-02, 'num'=>10),
     *                           array('loc'=>c-01-01-03, 'num'=>10), ),
     *      );
     * 
     *  @return array $return = array(
     *          'sid1' => c-01-01-01
     *          'sid2' => c-01-01-02:10,c-01-01-03:10
     *      )
     */
    public static function genLocationAndNum($locations)
    {
        $res = array();
        
        foreach($locations as $sid => $locs)
        {
            if (count($locs) == 1)
            {
                $res[$sid] = $locs[0]['loc'];
            }
            else
            {
                $_locs = array();
                foreach ($locs as $loc)
                {
                    $_locs[] = $loc['loc'].':'.$loc['num'];
                }
                $res[$sid] = implode(',', $_locs);
            }
        }
        
        return $res;
    }
    
    /**
     * 解析商品sku所占货位的位置和数量关系.
     * 
     * @param array $productList
     *      $productList[n] = array(
     *          'num' => 10,
     *          'locName' => 'loc1:2,loc1:3' or 'loc1'
     *          ......
     *      );
     * @param string $locName
     */
    public static function parseLocationAndNum(&$productList, $locName='location')
    {
        foreach($productList as &$one)
        {
            if (!isset($one[$locName]) || empty($one[$locName]))
            {
                continue;
            }
            
            $locs = explode(',', $one[$locName]);
            
            if (count($locs) == 1)
            {
                $vnum = isset($one['vnum']) ? $one['vnum'] : 0;
                $one['_'.$locName][] = array('loc'=>$locs[0], 'num'=>($one['num'] - $vnum), 'vnum' => $vnum);
            }
            else
            {
                foreach($locs as $locInfo)
                {
                    list($loc, $num) = explode(':', $locInfo);
                    $one['_'.$locName][] = array('loc'=>$loc, 'num'=>$num, 'vnum' => $vnum);
                }
            }
        }
    }
    
    /**
     * 更新货位库存，总库存，并添加出入库历史.
     * 
     *  货位存在，更新数量；货位不存在，添加货位，更新数量
     *  $num: 变更数量
     * 
     * @param int $sid
     * @param string $loc
     * @param int $wid
     * @param int $num
     * @param int $type
     * @param int $suid
     * @param int $objid 单据id
     * @param string $note
     * @param int $reason
     */
    public static function updateLocationStockWithHistory($sid, $loc, $wid, $num, $type, $suid, $objid=0, $note='', $reason=0)
    {
        //锁库判断
        $lockedRet = Conf_Warehouse::isLockedWarehouse($wid);
        if ($lockedRet['st'])
        {
            throw new Exception($lockedRet['msg']);
        }
        
        $ws = new Warehouse_Stock();
        $wsh = new Warehouse_Stock_History();
        $wl = new Warehouse_Location();
        
        
        //更新货位数量
        $wl->add($loc, $wid, $sid, $num);
        
        //更新库存数量
        $oldStocks = $ws->get($wid, $sid);
        
        $change = array('num' => $num);
		$ws->save($wid, $sid, array(), $change);
        
        //出入库存历史
        $stockHistory = array(
            'old_num' => !empty($oldStocks)? $oldStocks['num']: 0,
            'num' => $num,
            'iid' => $objid,
            'suid' => $suid,
            'type' => $type,
            'reason' => $reason,
            'note' => $note,
        );
        $wsh->add($wid, $sid, $stockHistory);
        
        //更新订单对商品的占用
        //$ooc = new Order_Occupied();
        //$ooc->refreshOccupiedOfSkuWhen($sid, $wid);
        
        //新刷新占用逻辑：addby:guoqiang/2017-06-12
        $wso = new Warehouse_Stock_Occupied();
        $wso->autoRefreshOccupied($wid, $sid);
    }
    
    /**
     * 更新盘库(货位库存) - 盘库使用.
     * 
     *  $num: 当前的库存量
     * 
     * @param int $sid
     * @param string $loc
     * @param int $wid
     * @param string $type
     * @param int $num
     * @param string $note
     * @param int $suid
     * @param int $reason 盘亏/盘盈原因
     * @param int $type 出入库历史的类型 如果未填写跟进num默认为盘亏/盘盈   【请必填】
     * @param bool $isInventory 是否为盘点操作（只有【盘点】是设置为true）
     */
    public static function saveCheckLocation($sid, $loc, $wid, $num, $note, $suid, $reason = 0, $type=-1, $isInventory=false)
    {   
        //锁库判断
        $lockedRet = Conf_Warehouse::isLockedWarehouse($wid);
        if ($lockedRet['st'] && !$isInventory)
        {
            throw new Exception($lockedRet['msg']);
        }
        
        $ws = new Warehouse_Stock();
        $wsh = new Warehouse_Stock_History();
        $wl = new Warehouse_Location();
        
        $_locationInfo = $wl->get($sid, $loc, $wid);
        if (empty($_locationInfo))
        {
            throw new Exception('货位不存在，和重新核查！');
        }
        
        $locationInfo = current($_locationInfo);
        $realNum = $num - $locationInfo['num'];
        
        if ($realNum == 0)
        {
            throw new Exception('盘库数量的差量为0，不能更新系统！');
        }
        
        if ($type == -1)
        {
            $type = $realNum>0? Conf_Warehouse::STOCK_HISTORY_CHK_GAIN: Conf_Warehouse::STOCK_HISTORY_CHK_LOSS;
        }

        if ($type == Conf_Warehouse::STOCK_HISTORY_CHK_GAIN && Conf_Warehouse::isAgentWid($wid))
        {
            throw new Exception('无法盘盈，请联系运营处理！');
        }
        
        //更新前库存
        $oldStocks = $ws->get($wid, $sid);
        
        $change = array('num' => $realNum);
        
        //更新货位数量
        $wl->update($sid, $loc, $wid, array(), $change);
        
        //更新库存数量
		$ws->save($wid, $sid, array(), $change);
        
        //记录盘库历史
        $locHistory = array(
            'old_num' => $locationInfo['num'],
            'chg_num' => $realNum,
            'suid' => $suid,
            'type' => $type,
            'note' => $note,
        );
        $wl->addHistory($sid, $wid, $loc, $locHistory);
        
        $stockHistory = array(
            'old_num' => !empty($oldStocks)? $oldStocks['num']: 0,
            'num' => $realNum,
            'suid' => $suid,
            'type' => $type,
            'reason' => $reason,
            'note' => $note,
        );
        $wsh->add($wid, $sid, $stockHistory);
        
        //更新订单对商品的占用
        //$ooc = new Order_Occupied();
        //$ooc->refreshOccupiedOfSkuWhen($sid, $wid);
        
        //新刷新占用逻辑：addby:guoqiang/2017-06-12
        $wso = new Warehouse_Stock_Occupied();
        $wso->autoRefreshOccupied($wid, $sid);
        
        //Cost-FIFO队列
        self::_recordFifoCost4Inventory($sid, $wid, $realNum);
    }
    
    // 盘亏/盘盈写Cost-FIFO队列
    private static function _recordFifoCost4Inventory($sid, $wid, $chgNum)
    {
        if ($chgNum == 0) return;
        
        if ($chgNum >0) //盘盈 - 已库均成本记录到Cost-FIFO
        {
            $aveCost = Shop_Cost_Api::getAveCost($wid, array($sid));
            $billInfo = array('in_id'=>0, 'in_type'=>Conf_Warehouse::STOCK_HISTORY_CHK_GAIN);
            $fifoQueueDatas[] = array(
                'sid' => $sid,
                'num' => $chgNum,
                'cost' =>$aveCost[$sid],
            );
            Shop_Cost_Api::enqueueSids4FifoCost($wid, $billInfo, $fifoQueueDatas);
        }
        else //盘亏 - 变更Cost-FIFO
        {
            $product[] = array('sid'=>$sid, 'num'=>abs($chgNum));
            $refreshCostDatas = Shop_Cost_Api::getCostsWithSkuAndNums($wid, $product);
            $billInfo = array('out_id'=>0, 'out_type'=>Conf_Warehouse::STOCK_HISTORY_CHK_LOSS);
            foreach($refreshCostDatas as $_sid => $fifoCosts)
            {
                if (empty($fifoCosts['_cost_fifo'])) continue;
                Shop_Cost_Api::dequeue4FifoCost($_sid, $wid, $billInfo, $fifoCosts['_cost_fifo']);
            }
        }
    }
    
    /**
     * 货位间商品的转移.
     * 
     * @param int $sid
     * @param int $wid
     * @param string $srcLoc
     * @param string $desLoc
     * @param int $num
     * @param int $suid
     * @param string $note
     */
    public static function saveShiftLocation($sid, $wid, $srcLoc, $desLoc, $num, $suid, $note='')
    {
        $wl = new Warehouse_Location();
        $_locInfo = $wl->get($sid, $srcLoc, $wid);
        $locInfo = current($_locInfo);
        
        if (empty($locInfo))
        {
            throw new Exception('货位商品不存在！');
        }
        if ($locInfo['location']!=$srcLoc || $srcLoc==$desLoc)
        {
            throw new Exception('货位信息有误，请检查！');
        }
        if ($num > ($locInfo['num']-$locInfo['occupied']) )
        {
            throw new Exception('数量不足：货位库存:'.$locInfo['num'].' 占用:'.$locInfo['occupied']);
        }
        
        // 检查目标货位是否合格
        $canShiftVFLoc = array(Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_DAMAGED]['flag'],
            Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_LOSS]['flag']);

        if (!$wl->checkLocaton($desLoc, false) && !in_array($desLoc, $canShiftVFLoc)) {
            throw new Exception('目标货位格式不对：正确格式：A-01-02-16');
        }
        
        // 原货架上货品移出
        $outChgData = array('num' => 0-abs($num));
        // 货架商品全部移出，删除货位
        if ($num == $locInfo['num']) 
        {
            $outChgData['status'] = 1;
        }
        $wl->update($sid, $srcLoc, $wid, array(), $outChgData);
        
        // 目标货架上货品移入
        $wl->add($desLoc, $wid, $sid, abs($num));

        if (!in_array($srcLoc, $canShiftVFLoc) && in_array($desLoc, $canShiftVFLoc)) {
            $ws = new Warehouse_Stock();
            $ws->update($wid, $sid, array(), array('damaged_num' => abs($num)));
        } else if (in_array($srcLoc, $canShiftVFLoc) && !in_array($desLoc, $canShiftVFLoc))
        {
            $ws = new Warehouse_Stock();
            $ws->update($wid, $sid, array(), array('damaged_num' => 0 - abs($num)));
        }
        
        // 记录操作历史
        $locHistory = array(
            'old_num' => $locInfo['num'],
            'chg_num' => $num,
            'des_loc' => $desLoc,
            'suid' => $suid,
            'type' => Conf_Warehouse::STOCK_HISTORY_LOC_SHIFT,
            'note' => $note,
        );
        $wl->addHistory($sid, $wid, $srcLoc, $locHistory);
    }

	/**
	 * 单sku多个货架.
	 */
	public static function oneSkuManyLocations($wid, $start = 0, $num = 20, $field = array('*'), $order = array('id', 'desc'))
	{
		$data = array();
		$wl = new Warehouse_Location();

		$ret = $wl->oneSkuManyLocations($wid, $start, $num, $field, $order);

		$data['total'] = $ret['total'];
		if ($ret['total'])
		{
			$ss = new Shop_Sku();
			$sids = array_unique(Tool_Array::getFields($ret['list'], 'sid'));
			$skuInfos = Tool_Array::list2Map($ss->getBulk($sids), 'sid');

			foreach($ret['list'] as $one)
			{
				$skuInfo = array_key_exists($one['sid'], $skuInfos)? $skuInfos[$one['sid']]: array();
				if (!isset($data['list'][$one['sid']]))
				{
					$data['list'][$one['sid']] = array(
						'sid' => $one['sid'],
						'_skuInfo' => $skuInfo,
						'locations' => array(
							array(
								'location' => $one['location'],
								'num' => $one['num'],
								'occupied' => $one['occupied'],
							),
						),
					);
				}
				else
				{
					$data['list'][$one['sid']]['locations'][] = array(
						'location' => $one['location'],
						'num' => $one['num'],
						'occupied' => $one['occupied'],
					);
				}
			}
		}

		return $data;
	}

	/**
	 * 单货架多个sku.
	 */
	public static function oneLocationManySkus($wid, $start = 0, $num = 20, $field = array('*'), $order = array('id', 'desc'))
	{
		$data = array();
		$wl = new Warehouse_Location();

		$ret = $wl->oneLocationManySkus($wid, $start, $num, $field, $order);

		$data['total'] = $ret['total'];
		if ($ret['total'])
		{
			$ss = new Shop_Sku();
			$sids = array_unique(Tool_Array::getFields($ret['list'], 'sid'));
			$skuInfos = Tool_Array::list2Map($ss->getBulk($sids), 'sid');

			foreach($ret['list'] as $one)
			{
				$skuInfo = array_key_exists($one['sid'], $skuInfos)? $skuInfos[$one['sid']]: array();
				if (!isset($data['list'][$one['location']]))
				{
					$data['list'][$one['location']] = array(
						'location' => $one['location'],
						'locations' => array(
							array(
								'_skuInfo' => $skuInfo,
								'num' => $one['num'],
								'occupied' => $one['occupied'],
							),
						),
					);
				}
				else
				{
					$data['list'][$one['location']]['locations'][] = array(
						'_skuInfo' => $skuInfo,
						'num' => $one['num'],
						'occupied' => $one['occupied'],
					);
				}
			}
		}

		return $data;
	}

	public static function exportLocation($search, $start = 0, $num = 0)
	{
		$wl = new Warehouse_Location();

		$ret = $wl->exportSearch($search, $start, $num);

		if ($ret['total'])
		{
			$ss = new Shop_Sku();
			$sids = array_unique(Tool_Array::getFields($ret['list'], 'sid'));
			$skuInfos = Tool_Array::list2Map($ss->getBulk($sids), 'sid');

			foreach($ret['list'] as &$one)
			{
				$one['_skuInfo'] = array_key_exists($one['sid'], $skuInfos)?
					$skuInfos[$one['sid']]: array();

				$one['is_virtual'] = 0;
				if (strpos($one['location'], Conf_Warehouse::VFLAG_PREFIX) === 0)
				{
					$one['is_virtual'] = 1;
				}
			}
		}

		return $ret;
	}

	private static function _updateCost($wid, $sid, $num, $price)
    {
//        $ws = new Warehouse_Stock();
//        $stock = $ws->get($wid, $sid);
//        $oldNum = $stock['num'];
//        $oldNum < 0 && $oldNum = 0;
//        $oldCost = $stock['cost'];
//        $newCost = round(($price * $num + $oldCost * $oldNum) / ($num + $oldNum));
//        $ws->update($wid, $sid, array('cost' => $newCost));
    }
}