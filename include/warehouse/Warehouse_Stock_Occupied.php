<?php

/**
 * 库存占用，刷新逻辑.
 * 
 * @relation
 *  1 销售订单
 *  2 调拨单
 * 
 * @rule （优先保证销售订单）
 *  1 分配占用顺序：
 *      
 *  1 分配占用：优先销售订单，其次调拨单
 *  2 抢占占用：优先调拨单，其次销售订单
 * 
 */


class Warehouse_Stock_Occupied
{
    /**
     * 单据类型.
     */
    CONST OBJTYPE_ORDER         = 1,
          OBJTYPE_STOCK_SHIFT   = 2;
    
    private $response = array();
    
    private $orderHandler = null;       //t_order, t_order_product
    private $sshiftHandler = null;      //t_stock_shift
    private $ssproductHandler = null;   //t_stock_shift_product
    private $slocationHandler = null;      //t_sku_2_location
    private $stockHandler = null;       //t_stock
    
    function __construct()
    {
        // 初始化返回结果
        $this->response = array(
            'errno' => 0,
            'errmsg' => '刷新成功',
        );
        
        $this->orderHandler = new Order_Order();
        $this->sshiftHandler = new Warehouse_Stock_Shift();
        $this->ssproductHandler = new Warehouse_Stock_Shift_Product();
        $this->stockHandler = new Warehouse_Stock();
        $this->slocationHandler = new Warehouse_Location();
    }

    /**
     * 获取sku的占用列表.
     * 
     * @param int $sid
     * @param int $wid
     */
    public function getOccupiedBySidWid($sid, $wid)
    {
        $orderProduct = $this->_getSortedOrderProducts4Redistribute($sid, $wid);
        foreach ($orderProduct as &$_op)
        {
            $_op['obj_id'] = $_op['oid'];
            $_op['type'] = '订单';
            $_op['url'] = '/order/order_detail.php?oid=' . $_op['oid'];
        }
        $sshiftProduct = $this->_getSortedSShiftProducts4Redistribute($sid, $wid);
        foreach ($sshiftProduct as &$_ssp)
        {
            $_ssp['obj_id'] = $_ssp['ssid'];
            $_ssp['type'] = '调拨单';
            $_ssp['url'] = '/warehouse/stock_shift_detail.php?ssid=' . $_ssp['ssid'];
            $_ssp['location'] = $_ssp['from_location'];
            $_ssp['delivery_date'] = $_ssp['ctime'];
        }

        $products = array_merge($sshiftProduct, $orderProduct);

        return $products;
    }
    
    /**
     * 自动刷新.
     * 
     * @relation
     *      盘盈；销售单退货入库；采购入库；调拨入库；其他入库；盘点模块-盘盈
     *      盘亏；采购入库的退货出库；其他出库；盘点模块-盘亏
     *      加工单：组合售卖，整转零售
     * @notice
     *      因为有了占用，所以不需要更新
     *          销售单的出库；调拨出库
     * 
     * @param int $wid
     * @param int/array $sids
     */
    public function autoRefreshOccupied($wid, $sids)
    {
        $this->response['errno'] = 0;
        
        if (empty($wid) || empty($sids))
        {
            $this->_showErrorMsg(1001);
            return $this->response;
        }
        //获取货位库存
        $sids = !is_array($sids)? array($sids): $sids;
        $_sLocations = $this->slocationHandler->getLocationsBySids($sids, $wid, 'actual');
        $stockLocations = array();
        foreach($_sLocations as $one)
        {
            $stockLocations[$one['sid']][] = $one;
        }
        unset($_sLocations);
    
        if (empty($stockLocations))
        {
            $this->_showErrorMsg(1005);
            return $this->response; 
        }
        
        try{
            foreach($stockLocations as $_sid =>$sLocation)
            {
                $this->redistributeOccupied($_sid, $wid, $sLocation);
            }
            
            return $this->response;
            
        } catch(Exception $e) {
            $this->_showErrorMsg($e->getCode());
            return $this->response;
        }
        
    }
    
    /**
     * 强制刷新单据的占用.
     * 
     * @rule
     *  1 获取货位库存，占用，计算有效库存：num-occupied
     *  2 单据需要占用的数量 < 有效库存；直接占用，并更新货位库存占用，总库存占用   【return】
     *  3 库存为零；直接返回    【return】
     *  4 抢占 - 可用库存不足
     *      4.1 抢占可用库存
     *      4.1 抢占调拨单：按照添加商品的时间倒序抢占，如果还存在未占用执行【4.2】，否则【4.x】
     *      4.2 抢占订单：按照配送时间倒序，优先抢占未拣货商品，跳转【4.x】
     *      4.x 更新货位库存占用，总库存占用
     * 
     * @param int $objid   单据id
     * @param int $objtype 单据类型
     * @param int/array $sids    sku-id  空更新单据全部商品
     */
    public function forceRefreshOccupied($objid, $objtype, $sids=array())
    {
        try
        {
            // 单据信息
            $objInfo = $this->_getObjInfo($objid, $objtype);
            $products = $this->_getWaitRefreshProductBySids($objid, $objtype, $sids);
            Warehouse_Location_Api::parseLocationAndNum($products, 'loc');  
            
            //货位库存信息
            $stockLocations = array();
            $sids = Tool_Array::getFields($products, 'sid');
            $_sLocations = $this->slocationHandler->getLocationsBySids($sids, $objInfo['wid'], 'actual');
            foreach($_sLocations as $one)
            {
                $stockLocations[$one['sid']][] = $one;
            }
            unset($_sLocations);
            
            // 分配占用
            foreach($products as $_pinfo)
            {
                // 没有库存，跳过
                if (!array_key_exists($_pinfo['sid'], $stockLocations)) continue;
                
                $this->_refreshOccupiedByVnum($objid, $objtype, $objInfo['wid'], $_pinfo, $stockLocations[$_pinfo['sid']]);
            }
            
            return $this->response;
            
        } catch(Exception $e) {
            $this->_showErrorMsg($e->getCode());     
            return $this->response;
        }
    }
    
    /**
     * 通过缺货(Vnum), 刷新占用.
     * 
     * @param int $objid
     * @param int $objtype
     * @param int $wid
     * @param array $pinfo  单据商品详情
     * @param array $sLocation 货位库存
     */
    private function _refreshOccupiedByVnum($objid, $objtype, $wid, $pinfo, $sLocation)
    {
        $waitRefreshNum = $pinfo['vnum'];
        $totalStockNum = $hadOccupied = 0;
        foreach($sLocation as $_sl)
        {
            $totalStockNum += $_sl['num'];           //总库存
            $hadOccupied += $_sl['occupied'];   //总占用
        }
        
        if ($totalStockNum <= 0)    //没有库存，不再重新分配库存，返回
        {
            throw new Exception('', 1006);
        }
        else if ($totalStockNum-$hadOccupied > $waitRefreshNum)    //可用库存，大于等待刷新数量，直接占用
        {
            $this->_refresh4CompleteOccupiedUseAvailStock($objid, $objtype, $wid, $pinfo, $sLocation);
        }
        else    //重新分配库存占用，维度：wid+sid
        {
            $this->redistributeOccupied($pinfo['sid'], $wid, $sLocation, $objid, $objtype, $pinfo);
        }
        
        return;
    }
    
    /**
     * 刷新占用：使用可用库存更新完全分配占用.
     */
    private function _refresh4CompleteOccupiedUseAvailStock($objid, $objtype, $wid, $pinfo, $sLocation)
    {
        //清除原占用
        $chgOccupied = array();
        foreach($pinfo['_loc'] as $item)
        {
            foreach($sLocation as &$sloc)
            {
                if ($sloc['location'] == $item['loc'])
                {
                    $chgOccupied[$sloc['id']] = 0-$item['num']; 
                    $sloc['free_num'] += $item['num'];  //释放占用
                    continue;
                }
            }
        }
        //优先分配多的库存
        Tool_Array::sortByField($sLocation, 'free_num', 'desc');
        
        //重新分配
        $num = $pinfo['num'];
        $newLoc = array();
        foreach($sLocation as $_sloc)
        {
            if ($_sloc['free_num'] >= $num)
            {
                $newLoc[] = array(
                    'loc' => $_sloc['location'],
                    'num' => $num,
                );
                $chgOccupied[$_sloc['id']] = intval($chgOccupied[$_sloc['id']])+$num;
                break;
            }
            else
            {
                $newLoc[] = array(
                    'loc' => $_sloc['location'],
                    'num' => $_sloc['free_num'],
                );
                $chgOccupied[$_sloc['id']] = intval($chgOccupied[$_sloc['id']])+$_sloc['free_num'];
                $num -= $_sloc['free_num'];
            }
        }
        
        $newLocFormated = Warehouse_Location_Api::genLocationAndNum(array($pinfo['sid']=>$newLoc));
        
        //订单重新分配占用
        $update = array('vnum'=>0, 'loc'=>$newLocFormated[$pinfo['sid']]);
        $this->_updateProductInfo($objid, $objtype, $pinfo['sid'], $update);
        
        //货位库存，库存更新占用
        $tChgNum = 0;
        foreach($chgOccupied as $skuLocid => $chgNum)
        {
            $this->slocationHandler->updateById($skuLocid, array(), array('occupied'=>$chgNum));
            $tChgNum += $chgNum;
        }
        $this->stockHandler->update($wid, $pinfo['sid'], array(), array('occupied'=>$tChgNum));
        
    }
    
    /**
     * 重新分配库存占用.
     * 
     * @rule
     *  1 优先给指定的单分配占用：objid+objtype（不为0时）
     *  2 再者：给订单分配
     *  3 最后：调拨单
     * 
     * @notice
     *  1 获取近一个月的符合条件的订单，调拨单
     *  2 订单：客服确认并未出库；排序：已拣货，配送的end_time
     *  3 调拨单：创建未出库；排序：创建时间
     * 
     * @param int $sid
     * @param int $wid
     * @param array $sLocation  货位库存
     * @param int $objid    不为0，优先给该单分配
     * @param int $objtype
     * @param array $objProduct 该单的商品详情
     */
    protected function redistributeOccupied($sid, $wid, $sLocation, $objid=0, $objtype=0, $objProduct=array())
    {
        //获取包含该sku_id的订单
        $productFromOrder = $this->_getSortedOrderProducts4Redistribute($sid, $wid, $objid, $objtype);
        
        //获取包含该sku_id的调拨单
        $productFromSShift = $this->_getSortedSShiftProducts4Redistribute($sid, $wid, $objid, $objtype);
        
        //重新占用：完全分配的：vnum=0；不能完全分配的：vnum between(0, num]
        //货位库存的占用清为0
        foreach($sLocation as &$slitem)
        {
            $slitem['occupied'] = 0;
        }
        
        //优先级1：指定的单据
        if ($objid!=0 && $objtype!=0)
        {
            $_objidFlag = $objtype==self::OBJTYPE_ORDER? 'oid': 'ssid';
            $objProduct[$_objidFlag] = $objid;
            $this->_redistributeOccupied($objProduct, $objtype, $sLocation);
        }
        //优先级2：订单
        foreach($productFromOrder as $_orderProduct)
        {
            $this->_redistributeOccupied($_orderProduct, self::OBJTYPE_ORDER, $sLocation);
        }
        //优先级3：调拨单
        foreach($productFromSShift as $_sshiftProduct)
        {
            $this->_redistributeOccupied($_sshiftProduct, self::OBJTYPE_STOCK_SHIFT, $sLocation);
        }
        
        //同步库存占用
        $tOccupied = 0;
        foreach($sLocation as $item)
        {
            $tOccupied += $item['occupied'];
            $ret = $this->slocationHandler->updateById($item['id'], array('occupied'=>$item['occupied']));
        }
        $ret  = $this->stockHandler->update($wid, $sid, array('occupied'=>$tOccupied), array());
    }
    
    /**
     * 重新分配占用.
     */
    private function _redistributeOccupied($objProduct, $objType, &$sLocation)
    {
        // 对更新可用库存，并排序
        foreach ($sLocation as &$_sl)
        {
            $_sl['free_num'] = $_sl['num']-$_sl['occupied'];
            $_sl['_sort_val'] = $_sl['free_num'];
        }
        usort($sLocation,   array('self', '_sortItemDown'));
        
        $newVnum = $objProduct['num'];
        $newLoc = array();
        foreach($sLocation as &$_sloc)
        {
            if ($_sloc['free_num'] <= 0) 
            {
                continue;
            }
            else if ($_sloc['free_num'] >= $newVnum)
            {
                $newLoc[] = array('loc'=>$_sloc['location'], 'num'=>$newVnum);
                $_sloc['occupied'] += $newVnum;
                $newVnum = 0;
                break;
            }
            else 
            {
                $newLoc[] = array('loc'=>$_sloc['location'], 'num'=>$_sloc['free_num']);
                $_sloc['occupied'] = $_sloc['num'];
                $newVnum -= $_sloc['free_num'];
            }
        }
        
        $sid = $objProduct['sid'];
        $locFormated = Warehouse_Location_Api::genLocationAndNum(array($sid=>$newLoc));
        $newLocation = isset($locFormated[$sid])&&!empty($locFormated[$sid])? $locFormated[$sid]: '';
        if ($objType == self::OBJTYPE_ORDER)    //订单
        {
            $upData = array(
                'vnum' => $newVnum,
                //'picked' => min($objProduct['picked'], $objProduct['num']-$newVnum) 如果有拣货数量有变化，应该重新拣货
                'location' => $newLocation,
            );
            
            $this->orderHandler->updateOrderProduct($objProduct['oid'], $sid, $upData);
        }
        else if ($objType == self::OBJTYPE_STOCK_SHIFT) //调拨单
        {
            $upData = array('vnum'=>$newVnum, 'from_location'=>$newLocation);
            
            $this->ssproductHandler->update($objProduct['ssid'], $sid, $upData);
        }
    }

    /**
     * 获取包含该sku_id的订单.
     * 
     * $objid,$objtype不为0，不包含该订单
     */
    private function _getSortedOrderProducts4Redistribute($sid, $wid, $objid=0, $objtype=0)
    {
        $chkDate = date('Y-m-d', time()-10*24*3600);
        $oField = array('oid', 'delivery_date', 'delivery_date_end', 'aftersale_type', 'aftersale_id');
        $oWhere = sprintf('status=0 and wid=%d and step>=%d and step<%d and delivery_date>="%s 00:00:00"',
                        $wid, Conf_Order::ORDER_STEP_SURE, Conf_Order::ORDER_STEP_PICKED, $chkDate);
        $orderInfos = $this->orderHandler->getOrderByWhere($oWhere, 0, 0, $oField);
        
        $this->_dealAfterSaleAssocOrder($orderInfos);
        
        $oids = Tool_Array::getFields($orderInfos, 'oid');
        
        if (empty($oids)) return array();
        
        $opField = array('oid', 'sid', 'num', 'vnum', 'location', 'picked');
        
        $opWhere = sprintf('status=0 and rid=0 and tmp_inorder_num=0 and wid=%d and sid=%d and oid in (%s) and oid <> %d',
                            $wid, $sid, implode(',', $oids), ($objtype==self::OBJTYPE_ORDER?$objid:0) );
        
        //$products4Order = $this->orderHandler->getOrderProductsByRawWhere($opWhere, 0, 0, $opField);
        $_products4Order = $this->orderHandler->getByRawWhere('t_order_product FORCE INDEX (PRIMARY)', $opWhere, $opField, 'order by oid desc');
        $products4Order['data'] = $_products4Order;
        
        if (empty($products4Order['data'])) return array();
        
        $pickedProducts = $unPickedProducts = array();
        foreach($products4Order['data'] as $item)
        {
            $item['delivery_date_end'] = $orderInfos[$item['oid']]['delivery_date_end'];
            $item['delivery_date'] = $orderInfos[$item['oid']]['delivery_date'];
            if ($item['picked'] > 0)
            {
                $item['_sort_val'] = $item['picked'];
                $pickedProducts[] = $item;
            }
            else
            {
                $item['_sort_val'] = $item['delivery_date_end'];
                $unPickedProducts[] = $item;
            }
        }
        
        //排序合并
		usort($pickedProducts,   array('self', '_sortItemUp'));    //按拣货多少排序，升序，尽量分配给更多已拣货订单
		usort($unPickedProducts, array('self', '_sortItemUp'));    //按配送时间排序，升序，配送时间在前的优先分配
        
        return array_merge($pickedProducts, $unPickedProducts);
    }
    
    /**
     * 处理售后单关联订单 - 不需要入库的售后单关联订单，不处理.
     * 
     * @param array $orderInfo
     */
    private function _dealAfterSaleAssocOrder(&$orderInfo)
    {
        $exchangedOrder = $trapsOrder = array();
        
        foreach($orderInfo as $item)
        {
            if ($item['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_EXCHANGED)
            {
                $exchangedOrder[$item['aftersale_id']] = $item['oid'];
            }
            else if ($item['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_TRAPS)
            {
                $trapsOrder[$item['aftersale_id']] = $item['oid'];
            }
        }
        
        if (!empty($exchangedOrder))
        {
            $eids = array_keys($exchangedOrder);
            $oe = new Order_Exchanged();
            $eInfos = $oe->getBulk($eids);
            
            foreach ($eInfos as $eItem)
            {
                if ($eItem['exchanged_status']!=0 || 
                    $eItem['step']==Conf_Exchanged::EXCHANGED_STEP_NEW || 
                    $eItem['need_storage']==0)
                {
                    unset($orderInfo[$exchangedOrder[$eItem['eid']]]);
                }
            }
        }
        
        if (!empty($trapsOrder))
        {
            $tids = array_keys($trapsOrder);
            $ot = new Order_Traps();
            $tInfos = $ot->getBulk($tids);
            
            foreach($tInfos as $tItem)
            {
                if ($tItem['traps_status']!=0 ||
                    $tItem['step']==Conf_Traps::TRAPS_STEP_NEW ||
                    $tItem['need_storage']==0)
                {
                    unset($orderInfo[$trapsOrder[$tItem['tid']]]);
                }
            }
        }
    }
    
     /**
     * 获取包含该sku_id的调拨单.
     * 
     * $objid,$objtype不为0，不包含该调拨单
     */
    private function _getSortedSShiftProducts4Redistribute($sid, $wid, $objid=0, $objtype=0)
    {
        $chkDate = date('Y-m-d', time()-10*24*3600);
        $ssField = array('ssid', 'ctime');
        $ssWhere = sprintf('status=0 and step=%d and src_wid=%d and ctime>="%s 00:00:00"',
                        Conf_Stock_Shift::STEP_CREATE, $wid, $chkDate);
        $sshiftInfos = Tool_Array::list2Map($this->sshiftHandler->getByWhere($ssWhere, 0, 0, $ssField), 'ssid');
        
        $ssids = array_keys($sshiftInfos);
        
        if (empty($ssids)) return array();
        
        $sspField = array('ssid', 'sid', 'num', 'vnum', 'from_location');
        $sspWhere = sprintf('status=0 and sid=%d and ssid in (%s) and ssid<>%d', 
                    $sid, implode(',', $ssids), ($objtype==self::OBJTYPE_STOCK_SHIFT?$objid:0));
        $product4SShift = $this->ssproductHandler->getByRawWhere($sspWhere, $sspField);
        
        if (empty($product4SShift)) return array();
        
        foreach($product4SShift as &$item)
        {
            $item['ctime'] = $sshiftInfos[$item['ssid']]['ctime'];
            $item['_sort_val'] = $sshiftInfos[$item['ssid']]['ctime'];
        }

        usort($product4SShift, array('self', '_sortItemUp'));
        
        return $product4SShift;
    }
    
    /**
     * 更新单据的商品数据.
     * 
     *  vnum, loc 等
     */
    private function _updateProductInfo($objid, $objtype, $skuid, $update)
    {
        switch ($objtype)
        {
            case self::OBJTYPE_ORDER: //订单
                $_update = array(
                    'vnum' => $update['vnum'],
                    'location' => $update['loc'],
                );
                $this->orderHandler->updateOrderProduct($objid, $skuid, $_update);
                break;
            case self::OBJTYPE_STOCK_SHIFT: //调拨单
                $_update =  array(
                    'vnum' => $update['vnum'],
                    'from_location' => $update['loc'],
                );
                $this->ssproductHandler->update($objid, $skuid, $_update);
                break;
            default: 
                break;
        }
    }
    
    // 获取单据id
    private function _getObjInfo($objid, $objtype)
    {
        if (empty($objid) || empty($objtype)) throw new Exception('', 1001);
        
        $objInfo = array();
        switch($objtype)
        {
            case self::OBJTYPE_ORDER: //订单
                $orderInfo = $this->orderHandler->get($objid);
                
                if (empty($orderInfo) || $orderInfo['status']!=Conf_Base::STATUS_NORMAL || empty($orderInfo['wid']))
                    throw new Exception('', 1003);
                if ($orderInfo['step']<Conf_Order::ORDER_STEP_SURE || $orderInfo['step']>=Conf_Order::ORDER_STEP_PICKED)
                    throw new Exception('', 1010);
                
                $objInfo['wid'] = $orderInfo['wid'];
                unset($orderInfo);
                
                break;
            case self::OBJTYPE_STOCK_SHIFT: //调拨单
                $ssInfo = $this->sshiftHandler->getById($objid);
                
                if (empty($ssInfo) || $ssInfo['status']!=Conf_Base::STATUS_NORMAL || empty($ssInfo['src_wid'])||empty($ssInfo['des_wid']))
                    throw new Exception('', 1003);
                if ($ssInfo['step'] >= Conf_Stock_Shift::STEP_STOCK_OUT)
                    throw new Exception('', 1020);
                
                $objInfo['wid'] = $ssInfo['src_wid'];
                unset($ssInfo);
                
                break;
            default:
                throw new Exception('', 1002);
        }
        
        return $objInfo;
    }
    
    // 通过sku获取单分配占用的商品列表
    private function _getWaitRefreshProductBySids($objid, $objtype, $sids=array())
    {
        $products = array();
        switch ($objtype)
        {
            case self::OBJTYPE_ORDER:
                $oproducts = $this->orderHandler->getProductsOfOrder($objid);
                foreach ($oproducts as $one)
                {
                    if ($one['vnum']==0) continue;
                    $products[] = array('sid'=>$one['sid'], 'num'=>$one['num'], 'vnum'=>$one['vnum'], 'loc'=>$one['location'], 'picked'=>$one['picked']);
                }
                
                unset($oproducts);
                break;
            case self::OBJTYPE_STOCK_SHIFT:
                $ssproducts = $this->ssproductHandler->get($objid);
                foreach($ssproducts as $one)
                {
                    if ($one['vnum']==0) continue;
                    $products[] = array('sid'=>$one['sid'], 'num'=>$one['num'], 'vnum'=>$one['vnum'], 'loc'=>$one['from_location']);
                }
                unset($ssproducts);
                break;
            default:
                throw new Exception('', 1002);
        }
        
        if (!empty($sids))
        {
            if (!is_array($sids)) $sids = array($sids);
             
            foreach($products as $k=>$item)
            {
                if ($item['vnum']==0 || !in_array($item['sid'], $sids))
                {
                    unset($products[$k]);
                }
            }
        }
        
        if (empty($products))
            throw new Exception('', 1004);
        
        return $products;
    }


    private function _showErrorMsg($errno)
    {
        $errMsgs = array(
            0       => '对不起，更新占用失败！',
            1001    => '请求参数有误！',
            1002    => '该类型单据不支持刷新操作',
            1003    => '单据数据异常',
            1004    => '无商品需要做刷新操作',
            1005    => 'sku对应的库存为空，无需刷新',
            1006    => '该商品没有库存',
            1010    => '订单状态异常：（仅处理客服已确认并未出库的订单）',
            1020    => '调拨单状态异常：（仅处理未出库的调拨单）',
        );
        
        if (array_key_exists($errno, $errMsgs))
        {
            $this->response['errno'] = $errno;
            $this->response['errmsg'] = $errMsgs[$errno];
        }
        else
        {
            $this->response['errno'] = 0;
            $this->response['errmsg'] = $errMsgs[0];
        }
    }
    
    // 降序
    private function _sortItemDown($a, $b)
    {
        if ($a['_sort_val'] == $b['_sort_val'])
        {
            return 0;
        }
        return ($a['_sort_val'] > $b['_sort_val']) ? -1 : 1;
    }
    
    // 升序
    private function _sortItemUp($a, $b)
    {
        if ($a['_sort_val'] == $b['_sort_val'])
        {
            return 0;
        }
        return ($a['_sort_val'] > $b['_sort_val']) ? 1 : -1;
    }
}