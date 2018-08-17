<?php

/**
 * 任务队列：下载/数据导出类.
 * 
 * 
 */

class Queue_Task_Download
{
   
    private static $staffList = array();        //成员列表
    private static $productInfos = array();     //商品信息表
    private static $skuInfos = array();         //sku信息表
    
    /**
     * sku销售明细.
     */
    public static function skuSalesDetail($taskInfo, $start, $num)
    {
        $head = array(
            '订单id', '客户id', '配送时间', '出库时间', '订单总货款', '阶段', '仓库', '城市', '销售员',
            'pid', 'skuid', 'sku名称', '售价', '成本', '销量', '临采销量', '单位', '货位', '商品毛利率',
            '上楼模式', '上楼费用', '分类1', '分类2', '是否上线', '品牌', '采购模式', '经营模式', '外包商ID',
        );
        
        $orderDao = new Data_Dao('t_order');
        $oproductDao = new Data_Dao('t_order_product');
        
        $orderField = array('oid', 'cid', 'delivery_date', 'ship_time', 'price', 'step', 'wid', 'city_id', 'saler_suid', 'service');
        $orderWhere = sprintf('status=0 and step>=5 and ship_time>="%s 00:00:00" and ship_time<="%s 23:59:59"', 
                            $taskInfo['start_time'], $taskInfo['end_time']);
        if (!empty($taskInfo['wid']))
        {
            $orderWhere .= ' and wid in ('. $taskInfo['wid']. ')';
        }
        
        $orderList = $orderDao->setFields($orderField)->order('oid', 'asc')->limit($start, $num)->getListWhere($orderWhere);
    
        if (empty($orderList)) return array('data'=>array(), 'head'=>array());
        
        $opFields = array('oid', 'pid', 'sid', 'price', 'cost', 'num', 'vnum', 'location', 'managing_mode', 'outsourcer_id');
        $opWhere = sprintf('status=0 and rid=0 and oid in (%s)', implode(',', array_keys($orderList)));
        $orderProductList = $oproductDao->setFields($opFields)->getListWhere($opWhere);
        
        $res = array();
        $skuInfos4OP = self::_getSkuInfos(Tool_Array::getFields($orderProductList, 'sid'));
        $productInfos4OP = self::_getProductInfos(Tool_Array::getFields($orderProductList, 'pid'));
        foreach($orderProductList as $pItem)
        {
            $oinfo = $orderList[$pItem['oid']];
            $step = Conf_Order::$ORDER_STEPS[$oinfo['step']];
            $wname = Conf_Warehouse::$WAREHOUSES[$oinfo['wid']];
            $cityName = Conf_City::getCityName($oinfo['city_id'], true);
            $staff = self::_getStaffInfo($oinfo['saler_suid']);
            
            $skuInfo = $skuInfos4OP[$pItem['sid']];
            $productInfo = $productInfos4OP[$pItem['pid']];
            
            $price = $pItem['price'];
            if ($oinfo['has_duty'] == Conf_Base::HAS_DUTY)
            {
                $duty = Conf_Base::getDuty($oinfo['cid']);
                $price = round($price / (1 - $duty));
            }
            $grossRate = (($price - $pItem['cost']) * 100) / ($price);
            
            $serviceMode = '不上楼';
            $serviceFee = '0';
            if ($oinfo['service'] == 1)
            {
                $serviceMode = '电梯';
                $serviceFee = $productInfo[$pItem['pid']]['carrier_fee_ele']*$pItem['num']/100;
            }
            else if ($oinfo['service'] == 2)
            {
                $serviceMode = '楼梯 - '. $oinfo['floor_num']. '层';
                $serviceFee = $productInfo[$pItem['pid']]['carrier_fee']*$oinfo['floor_num']*$pItem['num']/100;
            }
            
            $res[] = array(
                $pItem['oid'], $oinfo['cid'], $oinfo['delivery_date'], $oinfo['ship_time'], 
                $oinfo['price']/100, $step, $wname, $cityName, !empty($staff['name'])? $staff['name']:'无',
                $pItem['pid'], $pItem['sid'], $skuInfo['title'], $price/100, $pItem['cost']/100,
                $pItem['num'], $pItem['vnum'], $skuInfo['unit'], !empty($pItem['location'])?$pItem['location']:'-', $grossRate.'%',
                $serviceMode, $serviceFee, $skuInfo['cate1_desc'], $skuInfo['cate2_desc'], $productInfo['st_desc'], 
                $skuInfo['brand_desc'], $productInfo['bt_desc'], $productInfo['mm_desc'], $pItem['outsourcer_id']
            );
        }
        
        return array('data'=>$res, 'head'=>$head);
    }
    
    
    private static function _getProductInfos($pids)
    {
        $productsList = array();
        $leftPids = array();
        
        foreach($pids as $_pid)
        {
            if (!empty(self::$productInfos[$_pid]))
            {
                $productsList[$_pid] = self::$productInfos[$_pid];
            }
            else
            {
                $leftPids[] = $_pid;
            }
        }
        
        if (!empty($leftPids))
        {
            $_dao = new Data_Dao('t_product');
            $_fields = array('pid', 'buy_type', 'status', 'managing_mode', 'carrier_fee', 'carrier_fee_ele');
            $_products = $_dao->setFields($_fields)->getList($leftPids);
            
            $mmDesc = Conf_Base::getManagingModes();
            $buyTypes = Conf_Product::getBuyTypeDesc();
            foreach($_products as $pid => $_pinfo)
            {
                $_pinfo['mm_desc'] = $mmDesc[$_pinfo['managing_mode']];
                $_pinfo['bt_desc'] = $buyTypes[$_pinfo['buy_type']];
                $_pinfo['st_desc'] = $_pinfo['status'] == Conf_Base::STATUS_NORMAL ? '是' : '否';
                
                self::$productInfos[$pid] = $_pinfo;
                $productsList[$pid] = $_pinfo;
            }
        }
        
        return $productsList;
    }
    
    private static function _getSkuInfos($sids)
    {
        $skuList = array();
        $leftSids = array();
        
        foreach($sids as $_sid)
        {
            if (!empty(self::$skuInfos[$_sid]))
            {
                $skuList[$_sid] = self::$skuInfos[$_sid];
            }
            else
            {
                $leftSids[] = $_sid;
            }
        }
        
        if (!empty($leftSids))
        {
            $_skuDao =  new Data_Dao('t_sku');
            $_brandDao = new Data_Dao('t_brand');
            
            $brandList = $_brandDao->getAll();
            $_skuField = array('sid', 'title', 'cate1', 'cate2', 'bid', 'unit', 'status');
            $_skuInfos = $_skuDao->setFields($_skuField)->getList($leftSids);
            
            foreach($_skuInfos as $_sku)
            {
                $_sku['cate1_desc'] = Conf_Sku::$CATE1[$_sku['cate1']]['name'];
                $_sku['cate2_desc'] = Conf_Sku::$CATE2[$_sku['cate1']][$_sku['cate2']]['name'];
                $_sku['brand_desc'] = $brandList[$_sku['bid']]['name'];
                $_sku['unit'] = !empty($_sku['unit'])? $_sku['unit']: '个';
                
                self::$skuInfos[$_sku['sid']] = $_sku;
                $skuList[$_sku['sid']] = $_sku;
            }    
        }
        
        return $skuList;
    }
    
    private static function _getStaffInfo($suid)
    {
        if (empty(self::$staffList))
        {
            $_dao = new Data_Dao('t_staff_user');
            
            self::$staffList = $_dao->setFields(array('suid', 'name'))->getAll();
        }
        
        return self::$staffList[$suid];
    }
}