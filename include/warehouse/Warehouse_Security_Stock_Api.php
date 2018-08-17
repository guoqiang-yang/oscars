<?php

/**
 *  安全库存相关脚本.
 * 
 *  @notice
 *      - 初始化安全库存的数据（平均销售量，在途数量）脚本：
 *          app/script/stock/init_security_stock.php
 * 
 *      - 更新平均销售量【定时脚本，每天凌晨执行】
 *          app/script/stock/init_security_stock.php
 */


class Warehouse_Security_Stock_Api extends Base_Api
{
    
    /**
     * 获取安全库存值.
     * 
     * @param int $wid
     * @param array $sids
     */
    public static function getSecurityStock($wid, $sids=array())
    {
        if(empty($wid)) return array();
        
        $ws = new Warehouse_Stock();
        $field = array('sid', 'wid', 'num', 'occupied', 'ave_sale_num', 'wait_num', 'cost', 'target_num', 'recent_stat_sale');
        $where = 'wid='. $wid;
        if (!empty($sids))
        {
            $where .= ' and sid in ('. implode(',', $sids). ')';
        }
        
        $stockInfos = $ws->getByWhere($where, $field, 0, 0);
        
        foreach($stockInfos as &$one)
        {
            self::_appendSecurityDatas($one);
        }
        
        return $stockInfos;
    }
    
    /**
     * 获取多个库多个sku的安全库存相关数据.
     * 
     * @param array $sids
     * @param array $wids
     */
    public static function getSecurityStock4MoreWids($sids, $wids=array())
    {
        if (empty($sids)) return array();
        
        $ws = new Warehouse_Stock();
        $field = array('sid', 'wid', 'num', 'occupied', 'damaged_num', 'ave_sale_num', 'wait_num', 'target_num', 'recent_stat_sale');
        $where = 'sid in ('. implode(',', $sids). ')';
        
        $stockInfos = $ws->getByWhere($where, $field, 0, 0);
        
        $ret = array();
        foreach($stockInfos as &$one)
        {
            $sid = $one['sid'];
            $wid = $one['wid'];
            
            if (!empty($wids) && !in_array($wid, $wids)) continue;
            
            self::_appendSecurityDatas($one);
            $ret[$sid][$wid] = $one;
        }
        
        return $ret;
    }
    
    // 计算安全库存相关数据.
    private static function _appendSecurityDatas(&$stockinfo)
    {
        $stockinfo['deliery_day'] = self::_getDeliveryDay($stockinfo);
        $stockinfo['season_factor'] = self::_getAdjustFator($stockinfo['wid']);
        $stockinfo['delivery_day'] = self::_getDeliveryDay($stockinfo);
        $stockinfo['min_day_of_stock'] = Conf_Stock::MIN_DAY_OF_STOCK;
        $stockinfo['order_point'] = self::calOrderPoint($stockinfo);   //订货点
        $stockinfo['order_num'] = self::calOrderQuantity($stockinfo);  //订货量
        $stockinfo['turn_day'] = self::calTurnDayByCurrentStock($stockinfo); //先库存周转天数
        $stockinfo['short_quantity'] = self::calShortQuantity($stockinfo); //缺货量
    }


    /**
     * 从订单统计最近一段时间的sku的销量.
     * 
     * @param int $wid
     * @param int $days
     */
    public static function getRecentAveSalesNumFromOrder($wid, $days=56, $fields=array())
    {
        if (empty($wid)) return array();
        
        $fromDate = date('Y-m-d', time() - $days*24*3600);
        $toDate = date('Y-m-d', time() - 24*3600);
        
        $_fields = array('sid', 'sum(sales_out_num) as sales_num', 'sum(sales_out_num_tmp) as sales_num_tmp');
        $fields = !empty($fields)? $fields: $_fields;
        $group = 'group by sid';
        
        $stat4sku = Statistics_Sku_View::getBaseSkuInfoBetweenDate($wid, $fromDate, $toDate, 0, 0, $fields, $group);
        
        //sku的销售天数 近似等于 sku的库存创建的时间
        $ws = new Warehouse_Stock();
        $wsWhere = 'status=0 and wid='. $wid;
        $stockCtimes = Tool_Array::list2Map($ws->getListByWhere($wsWhere, array('sid', 'ctime'), 0, 0), 'sid', 'ctime');
        
        $aveSalesNum = array();
        foreach($stat4sku['list'] as $one)
        {
            $aveDays = $days;
            if (isset($stockCtimes[$one['sid']]))
            {
                $_day = floor((time() - strtotime($stockCtimes[$one['sid']]))/24/3600);
                $aveDays = max(min($days, $_day), 1);   //max 1 防止分母为0
            }
            
            $aveSalesNum[$one['sid']]['ave'] = ceil(($one['sales_num']+$one['sales_num_tmp'])/$aveDays);
            $aveSalesNum[$one['sid']]['total'] = $one['sales_num']+$one['sales_num_tmp'];
            //$aveSalesNum[$one['sid']] = ceil(($one['sales_num']+$one['sales_num_tmp'])/$days);
        }
        
        return $aveSalesNum;
    }


    /**
     * 更新在途数据 - 通过采购单id.
     * 
     * @param int $oid
     * @param int $wid
     */
    public static function updateWaitNumByInorderId($oid, $wid)
    {
        if (empty($oid) || empty($wid)) return;
        
        $wiop = new Warehouse_In_Order_Product();
        
        $field = array('sid');
        $where = sprintf('status=%d and source=%d and oid=%d', 
                    Conf_Base::STATUS_NORMAL, Conf_In_Order::SRC_COMMON, $oid);
        $products = $wiop->getProductsByWhere($where, $field, 0, 0);
        
        $sids = Tool_Array::getFields($products, 'sid');
        
        self::updateWaitNumByWidSid($wid, $sids);
    }
    
    /**
     * 更新在途数量 - 通过入库单id.
     * 
     * @param int $id
     * @param int $wid
     */
    public static function updateWaitNumByStockinId($id, $wid)
    {
        if (empty($id) || empty($wid)) return;
        
        $wsip = new Warehouse_Stock_In_Product();
        
        $field = array('sid');
        $products = $wsip->getProductsOfStockIn($id, 0, 0, $field);
        
        $sids = Tool_Array::getFields($products, 'sid');
        
        self::updateWaitNumByWidSid($wid, $sids);
    }
    
    /**
     * 更新在途数量 - 通过调拨单id.
     * 
     * @param int $ssid
     * @param int $descWid 调拨入库
     */
    public static function updateWaitNumByStockshiftId($ssid, $descWid)
    {
        if (empty($ssid) || empty($descWid)) return;
        
        $wssp = new Warehouse_Stock_Shift_Product();
        
        $field = array('sid');
        $subWhere = sprintf('select ssid from t_stock_shift where status=%d and ssid=%d and des_wid=%d',
                Conf_Base::STATUS_NORMAL, $ssid, $descWid);
        $where = sprintf('status=0 and ssid in (%s)', $subWhere);
        $products = $wssp->getByRawWhere($where,$field);
        
        $sids = Tool_Array::getFields($products, 'sid');
        
        self::updateWaitNumByWidSid($descWid, $sids);
    }


    /**
     * 更新在途数量.
     * 
     * 根据提供的仓库和sku列表，实时获取在途数量，并更新.
     * 
     * @param int $wid
     * @param array $sids
     * @param bool $upAllSids   是否更新全部sid的在途
     */
    public static function updateWaitNumByWidSid($wid, $sids, $upAllSids=false)
    {
        $unUp = !$upAllSids && empty($sids); //无更新
        if (empty($wid) || $unUp) return;
        
        if($upAllSids) $sids = array();
        
        $waitNum4sid = self::statWaitNumByWidSid($wid, $sids);
        
        $ws = new Warehouse_Stock();
        foreach($waitNum4sid as $sid => $num)
        {
            $upData = array('wait_num' => $num);
            $ws->save($wid, $sid, $upData, array());
        }
        
    }
    
    /**
     * 统计在途数量.
     * 
     * 根据提供的仓库和sku列表，统计在途数量.
     * 
     * @param int $wid
     * @param array $sids
     * @param int $getDays   是否更新全部sid的在途
     */
    public static function statWaitNumByWidSid($wid, $sids=array(), $getDays=Conf_Stock::DAYS_OF_STAT_WAIT_NUM)
    {
        if (empty($wid)) return  array();
        
        $waitNumFromInorder = self::_getWaitNumByWidSidFromInorder($wid, $sids, $getDays);
        
        $waitNumFromStockShift = self::_getWaitNumByWidSidFromStockShift($wid, $sids, $getDays);
        
        //更新在途
        $waitNum4sid = array();
        if (!empty($sids))
        {
            
            foreach($sids as $sid)
            {
                $_fromINorder = array_key_exists($sid, $waitNumFromInorder)? $waitNumFromInorder[$sid]: 0;
                $_fromStockShift = array_key_exists($sid, $waitNumFromStockShift)? $waitNumFromStockShift[$sid]: 0;
                $waitNum4sid[$sid] = $_fromINorder + $_fromStockShift;
            }
        }
        else
        {
            foreach($waitNumFromInorder as $sid => $_waitNum)
            {
                $waitNum = $_waitNum;

                if (array_key_exists($sid, $waitNumFromStockShift))
                {
                    $waitNum += $waitNumFromStockShift[$sid];
                }

                $waitNum4sid[$sid] = $waitNum;
            }
        }
        
        return $waitNum4sid;
    }
    
    /**
     * 更新在途数量 - 通过采购单.
     * 
     * 更新仓库id，sku id列表查询【近30天】未收货的采购单
     *      - 先款后货，赠品单：创建采购单，增加在途；入库商品，减少对应商品的在途
     *      - 其他：创建采购单，增加在途；入库，减少采购单全部商品的在途
     * 
     * sid的在途数量 = sid的总采购数量-sid的总入库数量  (只计算普采商品）
     * 
     * @param int $wid
     * @param array $sids
     * @param date $getDays 统计天数
     */
    private static function _getWaitNumByWidSidFromInorder($wid, $sids=array(), $getDays=30)
    {
        $wiop = new Warehouse_In_Order_Product();
        $wsip = new Warehouse_Stock_In_Product();
        $beginTime = date('Y-m-d 00:00:00', time()-$getDays*24*3600);
        
        // 采购单的普采商品统计 (待收货，部分收货的普采单和综合采购单中的普采商品）
        $inorderFields = array('sid', 'sum(num)');
        $inorderGroup = ' group by sid';
//        $inorderSubWhere = sprintf('select oid from t_in_order where status=0 and wid=%d and ctime>="%s" and step in (%d,%d) and source in (%d, %d)',
//                        $wid, $beginTime, Conf_In_Order::ORDER_STEP_SURE, Conf_In_Order::ORDER_STEP_PART_RECEIVED, 
//                        Conf_In_Order::SRC_COMMON, Conf_In_Order::SRC_COMPOSITIVE);
        
        $inorderSubWhere = sprintf('select oid from t_in_order where status=0 and wid=%d and ctime>="%s" and source in (%d, %d)'.
                                    ' and (((payment_type=%d or in_order_type=%d) and step in (%d, %d))'.
                                        'or (payment_type!=%d and in_order_type!=%d and step=%d) )',
                                    $wid, $beginTime, Conf_In_Order::SRC_COMMON, Conf_In_Order::SRC_COMPOSITIVE,
                                    Conf_Stock::PAYMENT_MONEY_FIRST, Conf_In_Order::IN_ORDER_TYPE_GIFT, Conf_In_Order::ORDER_STEP_SURE, Conf_In_Order::ORDER_STEP_PART_RECEIVED,
                                    Conf_Stock::PAYMENT_MONEY_FIRST, Conf_In_Order::IN_ORDER_TYPE_GIFT, Conf_In_Order::ORDER_STEP_SURE);
        
        $inorderWhere = sprintf('status=0 and source=%d and oid in (%s)', Conf_In_Order::SRC_COMMON, $inorderSubWhere);
        if (!empty($sids))
        {
            $inorderWhere .= ' and sid in ('. implode(',', $sids). ')';
        }
        
        $inorderRet = Tool_Array::list2Map($wiop->getProductsByWhere($inorderWhere.$inorderGroup, $inorderFields, 0, 0), 'sid', 'primary');
        
        // 入库单的普采入库商品统计
        $stockinFields = array('sid', 'sum(num)');
        $stockinGroup = ' group by sid';
        $stockinOrder = ' order by sid';
        $stockinSubWhere = sprintf('select id from t_stock_in where status=0 and wid=%d and source=%d and oid in (%s)',
                            $wid, Conf_In_Order::SRC_COMMON, $inorderSubWhere);
        $stockinWhere = sprintf('status=0 and srid=0 and id in (%s)', $stockinSubWhere);
        if (!empty($sids))
        {
            $stockinWhere .= ' and sid in ('. implode(',', $sids). ')';
        }
        
        $stockinRet = Tool_Array::list2Map($wsip->getByRawWhere($stockinWhere.$stockinGroup, 't_stock_in_product', $stockinFields, $stockinOrder, 0, 0), 'sid');
        
        //统计在途数量
        $waitNum = array();
        if (!empty($sids))
        {
            foreach($sids as $_sid)
            {
                $_fromInorderNum = array_key_exists($_sid, $inorderRet)? $inorderRet[$_sid]['sum(num)']: 0;
                $_fromStockInNum = array_key_exists($_sid, $stockinRet)? $stockinRet[$_sid]['sum(num)']: 0;
                $waitNum[$_sid] = $_fromInorderNum - $_fromStockInNum;
            }
        }
        else
        {
            foreach($inorderRet as $sid => $one)
            {
                $stockInNum = array_key_exists($sid, $stockinRet)? $stockinRet[$sid]['sum(num)']: 0;
                $num= $one['sum(num)'] - $stockInNum;

                $waitNum[$sid] = $num>0? $num: 0;   //安全检测，理论不会出现负数情况
            }
        }
        
        return $waitNum;
    }
    
    /**
     * 更新在途数量 - 通过调拨单.
     * 
     * 更加仓库id，sku id列表查询【近30天】未收货的调拨单
     * 条件：调拨出库已经出库 && 调入库还未入库
     * 
     * @param int $wid
     * @param array $sids
     * @param date $getDays 统计天数
     */
    private static function _getWaitNumByWidSidFromStockShift($wid, $sids, $getDays='')
    {
        $wssp = new Warehouse_Stock_Shift_Product;
        $field = array('sid', 'sum(num)');
        $group = ' group by sid';
        $order = 'order by sid';
        $beginTime = date('Y-m-d 00:00:00', time()-$getDays*24*3600);
        
        $subWhere = sprintf('select ssid from t_stock_shift where status=0 and ctime>="%s" and step=%d and des_wid=%d',
                    $beginTime, Conf_Stock_Shift::STEP_STOCK_OUT, $wid);
        $where = sprintf('status=0 and  ssid in (%s)', $subWhere);
        if (!empty($sids))
        {
            $where .= ' and sid in ('. implode(',', $sids). ')';
        }
        
        $stockShiftRet = $wssp->getByRawWhere($where.$group, $field, 0, 0, $order);
        
        return Tool_Array::list2Map($stockShiftRet, 'sid', 'sum(num)');
    }
    
    
    /**
     * 计算订货点.
     * 
     * 订货点 = 平均销售量*调整系数*（货期+最小库存天数）
     */
    public static function calOrderPoint($stockInfo)
    {
        $deliveryDays = self::_getDeliveryDay($stockInfo);
        $num = $stockInfo['ave_sale_num']
                *self::_getAdjustFator($stockInfo['wid'])
                *(Conf_Stock::MIN_DAY_OF_STOCK+$deliveryDays);
        
        return ceil($num);
    }

    /**
     * 计算订货量
     * 
     * 订货量 = 平均销售量*季节系数*（货期*2+最小库存天数)-现在库存-在途库存 【old】
     * 订货量 = 平均销售量*季节系数*（货期*2+最小库存天数)  【old】
     * 特殊商品订货量计算：（考虑仓库库容问题） 【old】
     *      南库=订货点-(库存+在途)+平均销售*2天
     *      北库=订货点-(库存+在途)+平均销售*4天
     * 
     * ### 坑货们，一个破公式，一个星期改了4次
     * 订货量 = 目标存量 - 库存 + min(一个货期销量，库存) 【new】
     *      目标存量 = 平均销量*调整系数*目标存量(天) //存在db中
     *      库存 = 现存量+在途
     *      一个货期销量 = 平均销量*调整系数*货期
     * 
     * @param array $stockInfo
     */
    public static function calOrderQuantity($stockInfo)
    {
        $sid = $stockInfo['sid'];
        $wid = $stockInfo['wid'];
        
        $totalStockNum = $stockInfo['num'] + $stockInfo['wait_num'];
        $oneDeliveryClcye = $stockInfo['ave_sale_num']
                           * self::_getAdjustFator($wid)
                           * self::_getDeliveryDay($stockInfo);
        
        $num = $stockInfo['target_num'] - $totalStockNum + min($oneDeliveryClcye, $totalStockNum);
        
        return $num<0? 0: ceil($num);
        
//        $speicalSkuids = Conf_Stock::$SPECIAL_SKUID_4_QUANTITY;
//        if (array_key_exists($sid, $speicalSkuids) && array_key_exists($wid, $speicalSkuids[$sid]))
//        {
//            $specialDays = $speicalSkuids[$sid][$wid];
//            $num = self::calOrderPoint($stockInfo)-$stockInfo['num']-$stockInfo['wait_num']
//                    + $stockInfo['ave_sale_num']*$specialDays;
//        }
//        else
//        {
//            
//            $deliveryDays = self::_getDeliveryDay($stockInfo);
//            $num = $stockInfo['ave_sale_num']
//                *self::_getAdjustFator($wid)
//                *(Conf_Stock::MIN_DAY_OF_STOCK+$deliveryDays*2);
//        }
//        
//        return $num<0? 0: ceil($num);
    }
    
    /**
     * 初始化目标销量.
     * 
     *  目标存量 = 平均销量*调整系数*目标存量(天)   //存在db中
     *  目标存量(天) = 货期*2 + 1
     */
    public static function calTargetNum($stockInfo, $targetDays=0)
    {
        $targetDays = $targetDays==0? (self::_getDeliveryDay($stockInfo)*2 + Conf_Stock::MIN_DAY_OF_STOCK) : $targetDays;
        
        return $stockInfo['ave_sale_num']*self::_getAdjustFator($stockInfo['wid'])*$targetDays;
    }
    
    /**
     * 现库存的周转天数.
     * 
     * 周转天数 = 库存/(平均销售*调整系数)
     */
    public static function calTurnDayByCurrentStock($stockInfo)
    {
        return  empty($stockInfo['ave_sale_num'])? '--':
                round(($stockInfo['num'] - $stockInfo['occupied'] + $stockInfo['wait_num'])/($stockInfo['ave_sale_num']), 1);
    }

    /**
     * 计算缺货量.
     * 
     * 缺货量=订货点-库存
     * 
     * @param array $stockInfo
     */
    public static function calShortQuantity($stockInfo)
    {
        $orderPoint = self::calOrderPoint($stockInfo);
        
        $shortNum = $orderPoint-$stockInfo['num'];
        
        return $shortNum<0? 0: ceil($shortNum);
    }
    
    // 获取调整系数
    private static function _getAdjustFator($wid)
    {
        $factorByWid = Conf_Stock::$ADJUST_FACTOR_By_WID;
        
        return array_key_exists($wid, $factorByWid)? $factorByWid[$wid]: Conf_Stock::ADJUST_FACTOR;
    }
    
    // 获取货期.
    private static function _getDeliveryDay($stockInfo)
    {
        if ($stockInfo['deliery_cycle'] > 0)
        {
            return round($stockInfo['deliery_cycle']/24, 2);
        }
        
        $sid = $stockInfo['sid'];
        $wid = $stockInfo['wid'];
        $day = Conf_Stock::DELIVERY_DAY;
        
        if (array_key_exists($sid, Conf_Stock::$SPECIAL_DELIVERY_DAY_4_SKUID))
        {
            $dayInfo = Conf_Stock::$SPECIAL_DELIVERY_DAY_4_SKUID[$sid];
            
            if (in_array($wid, $dayInfo['wids']))
            {
                $day = $dayInfo['days'];
            }
        }
        
        return $day;
    }
    
    
    
    
    public static function getStockList($wid, $fields)
    {
        if (empty($wid)) {
            return array();
        }
        $where = sprintf('wid=%d', $wid);
        $ws = new Warehouse_Stock();
        $data = $ws->getList($where, $fields, '', 0, 0);

        return $data;
    }

    public static function getOrderProductList($where)
    {
        $where .= ' group by sid';
        $wsip= new Warehouse_In_Order_Product();
        $data = $wsip->getListRawWhere($where, $total, array('sid', 'asc'), 0, 0, array('sid', 'SUM(num) as num'));

        return $data;
    }

    public static function getStockInList($oid)
    {
        $wsi = new Warehouse_Stock_In();
        $data = $wsi->getListOfOrder($oid, '');

        return $data;
    }

    public static function getStockInProductlist($where)
    {
        $where .= ' group by sid';
        $wsip = new Warehouse_Stock_In_Product();
        $data = $wsip->getByRawWhere($where, '', array('sid', 'SUM(num) as num'));

        return $data;
    }

    public static function getStockShiftProductList($where)
    {
        $where .= ' group by sid';
        $wssp = new Warehouse_Stock_Shift_Product();
        $data = $wssp->getByRawWhere($where, array('sid', 'SUM(num) as num'));

        return $data;
    }

}