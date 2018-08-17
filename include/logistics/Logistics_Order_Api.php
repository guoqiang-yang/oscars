<?php

/**
 * 调度系统 - 订单相关逻辑.
 */

class Logistics_Order_Api extends Base_Api
{
    
    public static function getByLineId($id)
    {
        $lol = new Logistics_Order_Line();
        
        return $lol->getByLineId($id);
    }
    
    public static function searchOrderLine($search, $start, $num=20, $order='', $objType = Conf_Coopworker::OBJ_TYPE_ORDER)
    {
        $lol = new Logistics_Order_Line();
        $orderLineList = $lol->search($search, $start, $num, $order);
        
        if ($orderLineList['total']==0)
        {
            return $orderLineList;
        }
        
        // 线路上的所有订单
        $oids = array();
        $lineIds = array();
        foreach($orderLineList['data'] as $one)
        {
            $oids = array_merge($oids, explode(',', $one['oids']));
            $lineIds[] = $one['id'];
        }
        
        // 所有订单的详情
        $oo = new Order_Order();
        $oField = array('oid', 'address', 'delivery_date', 'step');
        $orderInfos = $oo->getBulk($oids, $oField);
        
        // 订单上的所有司机
        $_coopworkers = $_driversInQueue = $driverInfos = array();
        if (!empty($lineIds))
        {
            $lc = new Logistics_Coopworker();
            $ld = new Logistics_Driver();
            $ldq = new Logistics_Driver_Queue();
            $_coopworkers = $lc->getByOids($oids, Conf_Base::COOPWORKER_DRIVER, $objType);
            $_driversInQueue = $ldq->getByLineids($lineIds);
            
            $dids = array();
            foreach($_coopworkers as $_c)
            {
                $dids[] = $_c['cuid'];
            }
            foreach($_driversInQueue as $_d)
            {
                $dids[] = $_d['did'];
            }
            
            $driverInfos = array();
            if (!empty($dids))
            {
                $dField = array('did','name','mobile', 'car_model');
                $driverInfos = Tool_Array::list2Map($ld->getByDids($dids, $dField), 'did');
            }
        }
        
        $coopworkers = array();
        foreach($_coopworkers as $c)
        {
            $coopworkers[$c['oid']][] = array_merge($c, $driverInfos[$c['cuid']]);
        }
        $driversInQueue = array();
        foreach($_driversInQueue as $dq)
        {
            $driversInQueue[$dq['line_id']][$dq['did']] = array_merge($dq, $driverInfos[$dq['did']]);
        }
        
        $line['_car_models'] = array();    
        foreach($orderLineList['data'] as &$line)
        {
            $line['driver_step'] = 0;
            $line['_car_models'] = $lol->parseCarModelInLine($line['car_models']);
            foreach ($_driversInQueue as $queue)
            {
                if ($queue['line_id'] == $line['id'] && $queue['step'] == 4)
                {
                    $line['driver_step'] = $queue['step'];
                    break;
                }
            }
            $driversInLine = array_key_exists($line['id'], $driversInQueue)? $driversInQueue[$line['id']]: array();
            self::_setOrderLineMoreInfo($line, $orderInfos, $coopworkers, $driversInLine);
        }
        
        
        return $orderLineList;
    }
    
    /**
     * 补充排线信息.
     * 
     * mid_para: stepNum {8:已送达 4:派送中 2:已接单 1:未接单}
     * 
     * @param array $line
     * @param array $orderInfos
     * @param array $coopworkers
     * @param array $driverInLine
     */
    private static function _setOrderLineMoreInfo(&$line, $orderInfos, $coopworkers, $driversInLine)
    {   
        $oidOnLine = explode(',', $line['oids']);
        $innerStepDesc = array(8=>'已送达', 4=>'派送中', 2=>'已接单', 1=>'待接单', 0=>'未分配',);
        
        $coopworkerOnLine = array();
        $line['order_max_step'] = 0;
        foreach($oidOnLine as $_oid)
        {
            if (array_key_exists($_oid, $coopworkers))
            {
                foreach($coopworkers[$_oid] as $co)
                {
                    $coopworkerOnLine[$co['cuid']] = $co;
                }
            }
            
            $address = explode(Conf_Area::Separator_Construction, $orderInfos[$_oid]['address']);
            
            $timestamp = strtotime($orderInfos[$_oid]['delivery_date']);
            $hour = date('H', $timestamp);
            $orderInfos[$_oid]['delivery_date'] = date('m-d', $timestamp).' '.$hour.'点-'.($hour+Conf_Order::INTER_HOUR).'点';
            $orderInfos[$_oid]['address'] = $address[0];
            $orderInfos[$_oid]['step_desc'] = Conf_Order::$ORDER_STEPS[$orderInfos[$_oid]['step']];
            $line['order_info'][] = $orderInfos[$_oid];
            $line['order_max_step'] = max($line['order_max_step'], $orderInfos[$_oid]['step']);
        }
        
        //补充线路信息
        $line['step_desc'] = Conf_Coopworker::$Order_Line_Step_Descs[$line['step']];
        $line['step_num'] = 0;
        
        if ($line['step'] != Conf_Coopworker::ORDER_LINE_NO_DRIVER) //部分分配||已分配
        {
            $lineStepNum = 8;
            foreach($line['_car_models'] as &$cm)
            {
                // 未分配
                if ($cm['is_alloc'] == 0)
                {
                    $cm['step_desc'] = '未分配';
                    $cm['step_num'] = 0;
                    $lineStepNum = 0;
                    continue;
                }
                
                // 接单司机
                foreach($coopworkerOnLine as $k=>$w)
                {
                    if ($w['car_model'] == $cm['model'])
                    {
                        $cm = array_merge($cm, $w);
                        
                        if ($w['arrival_time']!='0000-00-00 00:00:00')
                        {
                            $cm['step_desc'] = '已送达';
                            $cm['step_num'] = 8;
                            $lineStepNum = min($lineStepNum, 8);
                        }
                        else if ($w['delivery_time']!='0000-00-00 00:00:00')
                        {
                            $cm['step_desc'] = '派送中';
                            $cm['step_num'] = 4;
                            $lineStepNum = min($lineStepNum, 4);
                        }
                        else if ($w['confirm_time']!='0000-00-00 00:00:00')
                        {
                            $cm['step_desc'] = '已接单';
                            $cm['step_num'] = 2;
                            $lineStepNum = min($lineStepNum, 2);
                        }
                        
                        unset($coopworkerOnLine[$k]);
                        
                        if (array_key_exists($k, $driversInLine))
                        {
                            unset($driversInLine[$k]);
                        }
                        break;
                    }
                }
                
                // 分配 但 未接单司机
                if (!isset($cm['step_desc']))
                {
                    foreach($driversInLine as $k => $d)
                    {
                        if ($d['car_model'] == $cm['model'])
                        {
                            $cm = array_merge($cm, $d);
                            unset($driversInLine[$k]);
                            break;
                        }
                    }
                    
                    $cm['step_desc'] = '待接单';
                    $lineStepNum = 1;
                    
                } 
                else
                {
                    continue;
                }
            }
            
            $line['step_desc'] = $innerStepDesc[$lineStepNum];
            $line['step_num'] = $lineStepNum;
            
        }
        
    }
    
    /**
     * 获取未排线的订单.
     * 
     * @param array $search
     * @param bool $getMoreInfo {商品摘要, 距离, 运费}
     */
    public static function getUnlineOrders($search, $getMoreInfo=false)
    {
        if (isset($search['oid']) && !empty($search['oid']))
        {
            $where = sprintf('status=%d and step>=%d and step<%d and line_id=0 and delivery_type!=%d',
                Conf_Base::STATUS_NORMAL, Conf_Order::ORDER_STEP_SURE, Conf_Order::ORDER_STEP_HAS_DRIVER, Conf_Order::DELIVERY_BY_YOURSELF);
            
            $where .= ' and oid='.$search['oid'];
        }
        else if (isset($search['delivery_btime']) && !empty($search['delivery_btime'])
                && isset($search['delivery_etime']) && !empty($search['delivery_etime']) )
        {
            $where = sprintf('status=%d and step>=%d and step<%d and line_id=0 and delivery_type!=%d',
                Conf_Base::STATUS_NORMAL, Conf_Order::ORDER_STEP_SURE, Conf_Order::ORDER_STEP_HAS_DRIVER, Conf_Order::DELIVERY_BY_YOURSELF);
            
            $where .= ' and delivery_date>="'. $search['delivery_btime'].
                      '" and delivery_date<="'. $search['delivery_etime'].'"';
            if (isset($search['wid']) && !empty($search['wid']))
            {
                $where .= ' and wid='. $search['wid'];
            }
            else
            {
                $myCity = City_Api::getCity();
                $widOfCity = Conf_Warehouse::$WAREHOUSE_CITY;
                $wids = $widOfCity[$myCity['city_id']];
                
                if (!empty($wids))
                {
                    $where .= ' and wid in ('. implode(',', $wids).')';
                }
            }
            
            if (isset($search['cid']) && !empty($search['cid']))
            {
                $where .= ' and cid='. $search['cid'];
            }
        }
        else
        {
            throw new Exception('请输入配送时间段 or 订单号！！');
        }
        
        $oo = new Order_Order();
        $field = array('oid', 'delivery_date', 'community_id', 'wid', 'cid', 
                        'paid', 'contact_name', 'contact_phone', 'step', 'line_id', 'delivery_type',
                        'note', 'customer_note', 'source_oid', 'service', 'floor_num', 'aftersale_type', 'aftersale_id');
        $orderList = $oo->getListRawWhereWithoutTotal($where, array('delivery_date', 'asc'), 0, 0, $field);
        
        if (!empty($orderList))
        {
            $communityIds = Tool_Array::getFields($orderList, 'community_id');
            $oc = new Order_Community();
            $communityInfos = $oc->getBulk($communityIds);
            
            foreach($orderList as &$oinfo)
            {
                $oinfo['lng'] = $communityInfos[$oinfo['community_id']]['lng'];
                $oinfo['lat'] = $communityInfos[$oinfo['community_id']]['lat'];
                $oinfo['cm_name'] = $communityInfos[$oinfo['community_id']]['name'];
                $oinfo['cm_address'] = $communityInfos[$oinfo['community_id']]['address'];
                
                $hour = date('H', strtotime($oinfo['delivery_date']));
                $oinfo['show_delivery_date'] = date('m-d', strtotime($oinfo['delivery_date']))
                                                .' '.$hour.'-'.($hour+Conf_Order::INTER_HOUR).'点';
            }
            
            self::setPriorityOfOrders($orderList);
        }
        
        self::getUnlineOrdersMoreInfo($orderList);
        
        return $orderList;
    }
    
    protected static function setPriorityOfOrders(&$orderList)
    {
        $orderOfHour = array();
        $cids = array();
        foreach($orderList as $_order)
        {
            $hour = date('H', strtotime($_order['delivery_date']));
            $orderOfHour[$hour][] = $_order['oid'];
            $cids[] = $_order['cid'];
        }
        ksort($orderOfHour);
        
        $cc = new Crm2_Customer();
        $customerInfos = $cc->getBulk(array_unique($cids));
        
        $mapImgs = Conf_Order::$Order_Priority_Mapimg;
        $beforeMapimg = array();
        
        foreach($orderOfHour as $oids)
        {
            $mapImg = array_shift($mapImgs);
            $mapImg = !empty($mapImg)? $mapImg: $beforeMapimg;
            
            foreach($oids as $oid)
            {
                $cid = $orderList[$oid]['cid'];
                $priority = self::getOrderPriority($orderList[$oid], $customerInfos[$cid]);
                
                $orderList[$oid]['priority'] = $priority;
                $orderList[$oid]['priority_desc'] = Conf_Order::$Priority_Desc[$priority];
                $orderList[$oid]['mapimg'] = $mapImg[$priority];
                $orderList[$oid]['customer_name'] = $customerInfos[$cid]['name'];
            }
            
            $beforeMapimg = $mapImg;
        }
    }
    
    /**
     * 获取订单优先级.
     * 
     * @rule
     *  0:普通 1:VIP客户订单 2:已支付订单 3:首单
     */
    protected static function getOrderPriority($orderInfo, $customerInfo)
    {
        $priority = 0;
        
        $orderNum = $customerInfo['order_num'];
        $deliveryTimes = explode(' ', $orderInfo['delivery_date']);
        
        if ($orderInfo['delivery_type'] == Conf_Order::DELIVERY_QUICKLY) // 加急订单
        {
            $priority = 5;
        }
        else if ($orderNum==0 || ($orderNum==1 && $deliveryTimes[0]==$customerInfo['last_order_date']))  // 首单 (近似判断)
        {
            $priority = 3;
        }
        else if ($orderInfo['paid'] == Conf_Order::HAD_PAID)    //已支付
        {
            $priority = 2;
        }
        else if ($customerInfo['level_for_sys'] == Conf_User::CRM_SYS_LEVEL_VIP) //VIP客户
        {
            $priority = 1;
        }
        
        return $priority;
    }

    protected static function getUnlineOrdersMoreInfo(&$orderInfos)
    {
        // 取摘要
        $oo = new Order_Order();
        $oids = array_keys($orderInfos);
        $products = $oo->getProductsByOids($oids);
        
        $ss = new Shop_Sku();
        $sids = Tool_Array::getFields($products, 'sid');
        $skuInfos = $ss->getBulk($sids);
      
        $allCate2 = Conf_Sku::$CATE2;
        foreach($products as $p)
        {
            if ($p['status']!=Conf_Base::STATUS_NORMAL || $p['rid']!=0)
            {
                continue;
            }
            
            $oid = $p['oid'];
            $cate2 = array_key_exists($p['sid'], $skuInfos)? $skuInfos[$p['sid']]['cate2']: 0;
            $cate2 = in_array($cate2, Conf_Sku::$Summary_Cate2_Ids)? $cate2: 10000;
                
            if (!isset($orderInfos[$oid]['summary']) || !array_key_exists($cate2, $orderInfos[$oid]['summary']))
            {
                if ($cate2 == 10000)
                {
                    $orderInfos[$oid]['summary'][$cate2]['name'] = '其他';
                }
                else
                {
                    $cate1 = $skuInfos[$p['sid']]['cate1'];
                    $orderInfos[$oid]['summary'][$cate2]['name'] = $allCate2[$cate1][$cate2]['name'];
                }
                $orderInfos[$oid]['summary'][$cate2]['num'] = 0;
            }
            $orderInfos[$oid]['summary'][$cate2]['num'] += $p['num'];

            if (empty($orderInfos[$oid]['max_length']))
            {
                $orderInfos[$oid]['max_length'] = $skuInfos[$p['sid']]['length'];
            }
            else
            {
                $orderInfos[$oid]['max_length'] = max($orderInfos[$oid]['max_length'], $skuInfos[$p['sid']]['length']);
            }
            if (empty($orderInfos[$oid]['max_width']))
            {
                $orderInfos[$oid]['max_width'] = $skuInfos[$p['sid']]['width'];
            }
            else
            {
                $orderInfos[$oid]['max_width'] = max($orderInfos[$oid]['max_width'], $skuInfos[$p['sid']]['width']);
            }
            if (empty($orderInfos[$oid]['max_height']))
            {
                $orderInfos[$oid]['max_height'] = $skuInfos[$p['sid']]['height'];
            }
            else
            {
                $orderInfos[$oid]['max_height'] = max($orderInfos[$oid]['max_height'], $skuInfos[$p['sid']]['height']);
            }
            $orderInfos[$oid]['total_weight'] += $skuInfos[$p['sid']]['weight'] * $p['num'];
        }
        
        // 获取距离，运费
        foreach($orderInfos as &$oinfo)
        {
            $df = Order_Community_Api::getDistanceAndFeeListNew($oinfo['community_id'], $oinfo['wid']);
            $oinfo['distance'] = $df['distance'];
//            $oinfo['show_fee'] = '';
//            foreach($df['fee_list'] as $o)
//            {
//                $_model = ($o['car_model']==4||$o['car_model']==7)? '金杯': $o['$_model'];
//                $oinfo['show_fee'] .= $_model.':￥'.($o['freight']/100)." ";
//            }
//            $oinfo['show_fee'] = !empty($oinfo['show_fee'])?$oinfo['show_fee']:'暂无';
        }
    }
    
    /**
     * 取订单信息.
     * 
     * @param int $oid
     */
    public static function getOrderProduct($oid)
    {
        if (empty($oid))
        {
            throw new Exception('订单ID为空');
        }
        
        $res = array(
            'summary' => array(),
            'products' => array(),
            'refund_products' => array(),
        );
        
        $oo = new Order_Order();
        $products = $oo->getProductsOfOrder($oid, TRUE);

		if (!empty($products))
		{
            $ss = new Shop_Sku();
            $sids = Tool_Array::getFields($products, 'sid');
            $skuInfos = $ss->getBulk($sids);
            
            $allCate2 = Conf_Sku::$CATE2;
			foreach ($products as &$product)
			{
				if ($product['num'] <= 0)
				{
					continue;
				}

                $product['sku_info'] = array_key_exists($product['sid'], $skuInfos)?
                            $skuInfos[$product['sid']]: array();
                
                // 商品摘要
                $cate2 = isset($product['sku_info']['cate2'])?$product['sku_info']['cate2']:0;
                $cate2 = in_array($cate2, Conf_Sku::$Summary_Cate2_Ids)? $cate2: 10000;
                
                if (!array_key_exists($cate2, $res['summary']))
                {
                    $cate1 = $product['sku_info']['cate1'];
                    $name = $cate2!=10000? $allCate2[$cate1][$cate2]['name']: '其他';

                    $res['summary'][$cate2]['name'] = $name;
                    $res['summary'][$cate2]['num'] = 0;
                }
                $res['summary'][$cate2]['num'] += $product['num'];
                
                // 订单商品详情
                if ($product['rid'] == 0)   // 订单商品
				{
                    $res['products'][] = $product;
				}
				else    //退款单商品
				{
                    $res['refund_products'][] = $product;
				}
			}
		}

        return $res;
    }

    /**
     * 订单排线.
     *
     * @param array $oids {oid:priority, ...., }
     * @param array $carModels {model:fee, .... }
     * @param int $staffUid
     * @param string $lineType
     * @return array
     * @throws Exception
     */
    public static function saveOrderLine($oids, $carModels, $staffUid, $lineType = 'common')
    {
        if (empty($oids)||empty($carModels)
            ||!is_array($oids) || !is_array($carModels))
        {
            throw new Exception('common:params error');
        }
        
        $oid2Priority = $carmodel2Fee = array();
        $_oids = array();
        foreach($oids as $o)
        {
            $op = explode(':', $o);
            $oid2Priority[$op[0]] = $op[1];
            $_oids[] = $op[0];
        }
        
        foreach($carModels as $c)
        {
            $carmodel2Fee[] = explode(':', $c);
        }
        
        // 检测订单的合法性
        // 1 订单属于$wid库 2 客服确认未出库 3 未排线
        $oo = new Order_Order();
        $orderInfos = array_values($oo->getBulk($_oids));
        
        if(empty($orderInfos))
        {
            throw new Exception('该线路尚未分配订单，无法提交！');
        }
        
        $wid = $orderInfos[0]['wid'];
        $deliveryTime = $orderInfos[0]['delivery_date'];
        $address = str_replace(Conf_Area::Separator_Construction, ' ', $orderInfos[0]['address']);
        $priority = array_key_exists($orderInfos[0]['oid'], $oid2Priority)? $oid2Priority[$orderInfos[0]['oid']]:0;
        
        foreach($orderInfos as $oinfo)
        {
            if ($oinfo['wid'] != $wid)
            {
                throw new Exception('订单不是来自同一个仓库！');
            }
            if ($oinfo['step']<Conf_Order::ORDER_STEP_SURE||$oinfo['step']>=Conf_Order::ORDER_STEP_PICKED)
            {
                throw new Exception('订单状态错误：客服未确认 或 已出库！');
            }
            if (!empty($oinfo['line_id']))
            {
                throw new Exception('订单已经排线：'.$oinfo['oid']);
            }
            
            // 配送时间更早 && 优先级更高
            $_priority = array_key_exists($oinfo['oid'], $oid2Priority)? $oid2Priority[$oinfo['oid']]:0;
            if ($oinfo['delivery_date'] <= $deliveryTime && $_priority>$priority)
            {
                $deliveryTime = $oinfo['delivery_date'];
                $address = str_replace(Conf_Area::Separator_Construction, ' ', $oinfo['address']);
                $priority = $_priority;
            }
        }
        
        // 记录排线数量
        $lol = new Logistics_Order_Line();
        $moreInfo = array(
            'delivery_date' => $deliveryTime,
            'address' => $address,
            'priority' => $priority, 
            'suid' => $staffUid,
            'driver_fee' => 0,
            'step' => Conf_Coopworker::ORDER_LINE_NO_DRIVER,
            'trans_scope' => self::getOrdersTransScope($orderInfos),
        );

        switch ($lineType)
        {
            case 'escort':
                $moreInfo['can_escort'] = 1;
                break;
            case 'trash':
                $moreInfo['can_trash'] = 1;
                break;
        }
        
        $models = array();
        foreach($carmodel2Fee as $modelFee)
        {
            $model = $modelFee[0];
            $fee = $modelFee[1];
            $moreInfo['driver_fee'] += $fee;
            $models[] = Conf_Coopworker::Orderline_CarModel_Flag.$model
                        .Conf_Coopworker::Orderline_CarModel_Sp1.Conf_Coopworker::ORDER_LINE_NO_DRIVER
                        .Conf_Coopworker::Orderline_CarModel_Sp1.$fee;
        }
        $moreInfo['wid'] = $wid;
        $moreInfo['car_models'] = $models;
        
        $lineId = $lol->add($_oids, $wid, $models, $moreInfo);
            
        // 写入订单
        foreach($_oids as $o)
        {
            $oo->update($o, array('line_id'=>$lineId));
        }
        
        // 分配司机
        $allocRet = self::autoAllocateDriverByLineId($lineId, $moreInfo);
        
        $ret = $allocRet;
        $ret['line_id'] = $lineId;
        
        return $ret;
    }
    
    /**
     * 获取订单的运送范围.
     *
     * @param type $orderInfos
     */
    public static function getOrdersTransScope($orderInfos)
    {
        return ''; //运力范围暂时下线：addby guoqiangyang; 20170716
        
        $oc = new Order_Community();
        $communityIds = Tool_Array::getFields($orderInfos, 'community_id');
        $communityInfos = $oc->getBulk($communityIds);
        
        $cityInfo = City_Api::getCity();
        $allCitys = Conf_City::$CITY;
        unset($allCitys[Conf_City::XIANGHE]);
        
        $cityId = array_key_exists($cityInfo['city_id'], $allCitys)?
                    $cityInfo['city_id']: Conf_City::BEIJING;
        
        if ($cityId != Conf_City::BEIJING) return '';
        
        $transScope = array();
        foreach($communityInfos as $one)
        {
            $_transScope = Conf_Area::getSpecialTransScope($cityId, $one['cmid'], $one);
            
            $transScope = array_merge($transScope, $_transScope);
        }
        
        return !empty($transScope)? implode(',', array_unique($transScope)): '';
    }
    
    /**
     * 通过线路自动分配司机.
     * 
     * @param int $lineId
     * @param array $lineInfo
     * @param $exceptDids 不在参与派单的司机ids
     */
    public static function autoAllocateDriverByLineId($lineId, $lineInfo=array(), $exceptDids=array())
    {
        assert($lineId);
        
        $lol = new Logistics_Order_Line();
            
        // 获取线路信息 && 解析车型
        if (empty($lineInfo) || empty($lineInfo['oids']))
        {
            $lineInfo = $lol->getByLineId($lineId);
        }
        
        $waitCarModelCount = 0;
        $allCarModel = array();
        if (isset($lineInfo['car_models']) && !empty($lineInfo['car_models']))
        {
            //model-demo: D4:1:10000,D2:0:6000
            if (!is_array($lineInfo['car_models']))
            {
                $models = explode(Conf_Coopworker::Orderline_CarModel_Sp2, $lineInfo['car_models']);
            }
            else 
            {
                $models = $lineInfo['car_models'];
            }

            foreach($models as $m)
            {
                $ms = explode(Conf_Coopworker::Orderline_CarModel_Sp1, $m);
                $allCarModel[] = $ms;

                if ($ms[1] != 0)
                {
                    continue;
                }
                $waitCarModelCount ++;
            }
        }
        
        if ($waitCarModelCount == 0)
        {
            throw new Exception('该线路已经分配，请重试！');
        }
        
        // 获取司机
        $hadAllocated = array();
        $drivers = Logistics_Api::getAvailableDriverQueue($lineInfo['wid']);
        
        $driverInfos = array();
        if (!empty($drivers))
        {
            $ld = new Logistics_Driver();
            $driverInfos = Tool_Array::list2Map($ld->getByDids(Tool_Array::getFields($drivers, 'did')), 'did');
        }
        
        $toUpCarModels = array();
        foreach($allCarModel as $mone)
        {
            // 该车型已经分配
            if ($mone[1] == 1)
            {
                $toUpCarModels[] = implode(Conf_Coopworker::Orderline_CarModel_Sp1, $mone);
                continue;
            }
            
            // 该车型未分配
            $_carModel = substr($mone[0], 1);
            foreach($drivers as $k=>$d)
            {
                // 排除的司机不在派单之列
                if (in_array($d['did'], $exceptDids))
                {
                    continue;
                }
                
                // 匹配上车型分配线路
                if ($d['car_model']==$_carModel
                    && self::_canAllocated($driverInfos[$d['did']], $lineInfo))
                {
                    $alloRet = Logistics_Api::allocOrder($d['did'], $lineId, $mone[2]);
                    
                    // 成功：标记线路上车型：已分配
                    if ($alloRet)
                    {
                        $mone[1] = 1;
                        $hadAllocated[] = array(
                            'did' => $d['did'],
                            'model' => $_carModel,
                            'fee' => $mone[2],
                        );

                        unset($drivers[$k]);
                        break;
                    }
                }
            }
            $toUpCarModels[] = implode(Conf_Coopworker::Orderline_CarModel_Sp1, $mone);
        }
        
        // 检测是否分配了车型，并标记
        $hadAllocatedCount = count($hadAllocated);
        $ret = ($waitCarModelCount==$hadAllocatedCount)? 2 :
                ($hadAllocatedCount==0? 0: 1);
        
        if (!empty($hadAllocated))
        {
            $toUpCarModel = implode(Conf_Coopworker::Orderline_CarModel_Sp2, $toUpCarModels);
            $lol->update($lineId, array('car_models'=>$toUpCarModel, 'step'=>$ret));
        }
     
        // retno: {0-未分配 1-部分分配 2-全部分配}
        return array('retno'=>$ret, 'had_alloc'=>$hadAllocated);
    }
    
    private static function _canAllocated($driverInfo, $lineInfo)
    {
        if (! Conf_Driver::isLimitForOrderLine($driverInfo, $lineInfo))
        {
            return false;
        }

        $driverTransScope = empty($driverInfo['trans_scope'])? array():
                            explode(',', $driverInfo['trans_scope']);

        $isOk = false;
        if (empty($lineInfo['trans_scope']))
        {
            $isOk = true;
        }
        else
        {
            $lineTransScope = explode(',', $lineInfo['trans_scope']);
            $_transScope = array_intersect($lineTransScope, $driverTransScope);

            if (!empty($_transScope))
            {
                $isOk = true;
            }
        }

        if (isset($lineInfo['can_trash']) && !empty($lineInfo['can_trash']))
        {
            $trashOk = false;
            if (!empty($driverInfo['can_trash']))
            {
                $trashOk = true;
            }
        }
        else
        {
            $trashOk = true;
        }

        if (isset($lineInfo['can_escort']) && !empty($lineInfo['can_escort']))
        {
            $escortOk = false;
            if (!empty($driverInfo['can_escort']))
            {
                $escortOk = true;
            }
        }
        else
        {
            $escortOk = true;
        }

        if ($isOk && $trashOk && $escortOk)
        {
            $canAllocated = true;
        }
        else
        {
            $canAllocated = false;
        }

        return $canAllocated;
    }
    
    /**
     * 通过司机自动分配线路.
     * 
     * @param int $driverId
     * @param int $carModel
     * @param int $wid
     * @param int $exceptLineId 不能分配的Line_id
     * 
     * @return {0-未分配 1-分配成功}
     */
    public static function autoAllocateLineByDriver($driverId, $carModel, $wid, $exceptLineId=0)
    {
        if (empty($driverId))
        {
            throw new Exception('orderline: line2driver-no driverId');
        }
        if(empty($carModel))
        {
            throw new Exception('orderline: line2driver-no carmodel');
        }
        if(empty($wid))
        {
            throw new Exception('orderline: line2driver-no wid');
        }
        
        // 获取未分配的线路
        $cc = new Logistics_Order_Line();
        $lineInfos = $cc->getUnAllocLine($wid, $carModel, true, $exceptLineId);
        
        // 根据配送范围，找一条适合该司机的的线路
        $ld = new Logistics_Driver();
        $driverInfo = $ld->get($driverId);
        
        $lineInfo = array();
        foreach($lineInfos as $line)
        {
            if (self::_canAllocated($driverInfo, $line))
            {
                $lineInfo = $line;
                break;
            }
        }
        
        // 为司机分配线路
        if (empty($lineInfo))
        {
            return 0;
        }
        
        // 给司机分配线路
        $models = explode(Conf_Coopworker::Orderline_CarModel_Sp2, $lineInfo['car_models']);
            
        $allCarModel = array();
        foreach($models as $m)
        {
            $ms = explode(Conf_Coopworker::Orderline_CarModel_Sp1, $m);
            $allCarModel[] = $ms;
        }
        
        foreach($allCarModel as &$mone)
        {
            $_carModel = substr($mone[0], 1);
            if ($mone[1]==0 && $_carModel==$carModel)
            {
                Logistics_Api::allocOrder($driverId, $lineInfo['id'], $mone[2]);
                $mone[1] = 1;
                break;
            }
        }
        
        // 标记已分配的车型
        $toUpCarModels = array();
        $lineStepNum = 0;
        foreach($allCarModel as $m)
        {
            if ($m[1] == 1)
            {
                $lineStepNum++;
            }
            $toUpCarModels[] = implode(Conf_Coopworker::Orderline_CarModel_Sp1, $m);
        }
        
        $lol = new Logistics_Order_Line();
        $lineStep = empty($lineStepNum)?Conf_Coopworker::ORDER_LINE_NO_DRIVER:
                    ($lineStepNum==count($toUpCarModels))? Conf_Coopworker::ORDER_LINE_HAD_DRIVER: Conf_Coopworker::ORDER_LINE_PART_DRIVER;
        $toUpCarModel = implode(Conf_Coopworker::Orderline_CarModel_Sp2, $toUpCarModels);
        $lol->update($lineInfo['id'], array('car_models'=>$toUpCarModel, 'step'=>$lineStep));
        
        return 1;
    }
    
    /**
     * 修改排线和司机关系.
     * 
     * @param int $lineId
     * @param array $dids
     * @param int $oid (add/del)
     * @param array $carModelInfo {car_model, price, did, step}
     * @param string $opType {reset:重排  reject:拒单}
     * @param string $reason 拒单原因
     * @param int $adminId 操作员ID
     */
    public static function chgOrderLineAndDriver($lineId, $dids, $oid, $carModelInfo, $opType, $reason, $adminId)
    {
        $lol = new Logistics_Order_Line();
        $lineInfo = $lol->getByLineId($lineId);
        
        if (empty($lineInfo))
        {
            throw new Exception('所排线路不存在！');
        }
        
        $oids = explode(',', $lineInfo['oids']);
        
        // 订单
        $oo = new Order_Order();
        $orderInfos = $oo->getBulk($oids);
        
        // 接单司机
        $lc = new Logistics_Coopworker();
        $coopDrivers = Tool_Array::list2Map($lc->getByOids($oids, Conf_Base::COOPWORKER_DRIVER), 'cuid');
        
        
        // common check
        $orderstep = 0;
        foreach($orderInfos as $oinfo)
        {
            $orderstep = max($orderstep, $oinfo['step']);
        }
        
        if (($opType!='add_modify_order'&&$opType!='del_modify_order'&&$orderstep>=Conf_Order::ORDER_STEP_PICKED) 
            ||(($opType=='add_modify_order'||$opType=='del_modify_order')&& $orderstep>=Conf_Order::ORDER_STEP_FINISHED) )
        {
            throw new Exception('订单已出库，不能操作！');
        }
        
        if ($opType == 'add_modify_order') //修改排线-加单
        {
            self::_addOrder4Line($lineInfo, $oid, $coopDrivers, $adminId);
        }
        else if ($opType == 'del_modify_order') //修改排线-减单
        {
            self::_delOrder4Line($lineInfo, $oid, $coopDrivers, $adminId);
        }
        else if ($opType == 'reject') // 司机拒单
        {
            self::rejectOrderLineAndDriver($lineId, $lineInfo, $dids, $coopDrivers, $reason, $adminId);
        }
        else if ($opType == 'cancel') // 取消排线
        {
            self::_cancelOrderLineAndDriver($lineInfo, $reason, $adminId);
        }
        else if ($opType == 'add_chg_carmodel') //加车型
        {
            self::_addCarModel4Line($lineInfo, $carModelInfo, $adminId);
        }
        else if ($opType == 'del_chg_carmodel') //减车型
        {
            self::_delCarModel4Line($lineInfo, $carModelInfo, $adminId);
        }
    }
    
    /**
     * 改车型 - 添加车型.
     * 
     *  - 加车型
     *  - 触发为该线路自动匹配司机
     */
    private static function _addCarModel4Line($lineInfo, $carModelInfo, $adminId)
    {
        if (!isset($carModelInfo['car_model']) || empty($carModelInfo['car_model']) )
        {
            throw new Exception('添加的车型不存在');
        }
        $_carmodel = Conf_Coopworker::Orderline_CarModel_Flag.$carModelInfo['car_model']
                      .Conf_Coopworker::Orderline_CarModel_Sp1.'0'
                      .Conf_Coopworker::Orderline_CarModel_Sp1.$carModelInfo['price'];
        $upCarModel = empty($lineInfo['car_models'])? $_carmodel: 
                        $lineInfo['car_models'].Conf_Coopworker::Orderline_CarModel_Sp2.$_carmodel;
        
        // 更新车型
        $lol = new Logistics_Order_Line();
        $lol->update($lineInfo['id'], array('car_models'=>$upCarModel));
        
        // 自动匹配车型
        $lineInfo['car_models'] = $upCarModel;
        $result = self::autoAllocateDriverByLineId($lineInfo['id'], $lineInfo);
        // 写日志
        $allCarModels = Conf_Driver::$CAR_MODEL;
        if (empty($result['had_alloc'][0]['did']))
        {
            $cuid = 0;
        }
        else
        {
            $cuid = $result['had_alloc'][0]['did'];
        }
        $reason = array('reason' => '添加车型：'. $allCarModels[$carModelInfo['car_model']]);
        Logistics_Api::addActionLog($adminId, $cuid, Conf_Base::COOPWORKER_DRIVER,
                    Conf_Logistics_Action_Log::ACTION_CHG_CAR_MODEL, 0, $reason, $lineInfo['id']);
    }
    
    /**
     * 改车型 - 删除车型.
     * 
     * — 如果未派单，直接取消
     * — 如果已派单，但是未接单，【释放分配司机，并为该司机自动匹配线路】
     * — 如果已派单，并司机接单，【释放司机和订单关系，释放分配司机，并为该司机自动匹配线路】
     * — 如果线上的车型都删除了，则将线删除
     */
    private static function _delCarModel4Line($lineInfo, $carModel, $adminId)
    {
        if (($carModel['step']==0&&$carModel['did']!=0) || ($carModel['step']!=0&&$carModel['did']==0))
        {
            throw new Exception('车型 司机状态错误！联系管理员处理！');
        }
        
        $lol = new Logistics_Order_Line();
        $parseCarModels = $lol->parseCarModelInLine($lineInfo['car_models']);
        $isLastCarModels = count($parseCarModels)==1? 1: 0;
        
        // 更新线路
        if ($isLastCarModels)
        {
            $upLindData = array('status'=>Conf_Base::STATUS_DELETED);
            
        }
        else
        {
            $canUp = false;
            foreach($parseCarModels as $k=>$oneModel)
            {
                if ($oneModel['model']==$carModel['car_model'])
                {
                    if (($carModel['did']!=0&&$oneModel['is_alloc']==1)
                        || ($carModel['did']==0&&$oneModel['is_alloc']==0))
                    {
                        $canUp = true;
                        unset($parseCarModels[$k]);
                        break;
                    }
                }
            }
            if (!$canUp)
            {
                throw new Exception('删除车型异常，请联系管理员！');
            }
            
            $upLindData = array('car_models'=>$lol->genCarModelForLine($parseCarModels));
        }
        
        $affectRow = $lol->update($lineInfo['id'], $upLindData);
        
        if (!$affectRow)
        {
            return;
        }
        
        // 最后一个车，释放订单
        if ($isLastCarModels)
        {
            self::_clearLineInfoForOrder($lineInfo['oids']);
        }
        
        // 更新司机和订单关系
        $allCarModels = Conf_Driver::$CAR_MODEL;
        if ($carModel['step']==Conf_Driver::STEP_ALLOC
            || $carModel['step']==Conf_Driver::STEP_ACCEPT
            || $carModel['step']==Conf_Driver::STEP_LEAVE) //派送 or 接单 or 出库
        {
            Logistics_Api::unsetLineId($lineInfo['id'], $carModel['did'], 0, '删除车型：'.$allCarModels[$carModel['car_model']], $adminId);
        }
        
        // 释放 接单的司机（解除订单和司机的关系）
        if ($carModel['step'] == Conf_Driver::STEP_ACCEPT)
        {
            $lc = new Logistics_Coopworker();
            $oids = explode(',', $lineInfo['oids']);
            foreach($oids as $oid)
            {
                $upData = array('status'=>Conf_Base::STATUS_DELETED);
                $lc->update($oid, $carModel['did'], Conf_Base::COOPWORKER_DRIVER, $upData);
            }
        }
    }
    
    /**
     * 修改排线 - 添加订单.
     * 
     *  — 添加线路订单如果已经安排司机并接单，需要建立司机和添加订单关系
     *  - 添加订单，未排线，未出库
     */
    private static function _addOrder4Line($lineInfo, $oid, $coopDrivers, $adminId)
    {
        $lol = new Logistics_Order_Line();
        $oo = new Order_Order();
        $orderInfo = $oo->get($oid);
        if (empty($orderInfo) || $orderInfo['status']!=Conf_Base::STATUS_NORMAL)
        {
            throw new Exception('订单不存在！');
        }
        if ($orderInfo['step'] >= Conf_Order::ORDER_STEP_PICKED)
        {
            throw new Exception('添加订单已出库!');
        }
        if ($orderInfo['step'] < Conf_Order::ORDER_STEP_SURE)
        {
            throw new Exception('添加订单客服为确认！');
        }
        if (!empty($orderInfo['line_id']))
        {
            throw new Exception('添加订单已完成排线！');
        }
        
        $transScope = $lineInfo['trans_scope'];
        $newTransScope = self::getOrdersTransScope(array($orderInfo));
        if (!empty($lineInfo['trans_scope']) && !empty($newTransScope))
        {
            $_transScope = explode(',', $lineInfo['trans_scope']);
            $_newTransScope = explode(',', $newTransScope);
            
            $transScope = implode(',', array_unique(array_merge($_transScope, $_newTransScope)));
        }
        else if (!empty($newTransScope))
        {
            $transScope = $newTransScope;
        }
        
        // 订单加入到线上
        $upDate = array(
            'oids' => $lineInfo['oids'].','.$oid,
            'trans_scope' => $transScope,
        );
        $lol->update($lineInfo['id'], $upDate);
        
        // 线标记到订单上
        if (empty($coopDrivers))
        {
            $orderUpData = array('line_id'=>$lineInfo['id']);
        }
        else 
        {
            $orderUpData = array('line_id'=>$lineInfo['id'], 'step'=>Conf_Order::ORDER_STEP_HAS_DRIVER);
        }
        $oo->update($oid, $orderUpData);
        
        // 如果已安排司机（司机接单）；关联司机和添加的订单
        $lc = new Logistics_Coopworker();
        foreach($coopDrivers as $driver)
        {
            $data = array(
                'cuid' => $driver['cuid'],
                'oid' => $oid,
                'wid' => $orderInfo['wid'],
                'type' => Conf_Base::COOPWORKER_DRIVER,
                'user_type' => Conf_Base::COOPWORKER_DRIVER,
                'confirm_time' => $driver['confirm_time']!='0000-00-00 00:00:00'? $driver['confirm_time']: date('Y-m-d H:i:s'),
                'alloc_time' => $driver['alloc_time']!='0000-00-00 00:00:00'? $driver['alloc_time']: date('Y-m-d H:i:s'),
                'delivery_time' => $driver['delivery_time'],
                'suid' => $adminId,
                'note' => '线路添加订单',
                'car_model' => $driver['car_model'],
            );
            $lc->saveWorkerForOrder($data);
        }
        $reason = array('reason' => '添加订单：' . $oid);
        Logistics_Api::addActionLog($adminId, 0, Conf_Base::COOPWORKER_DRIVER,
            Conf_Logistics_Action_Log::ACTION_CHG_ORDER, $oid, $reason, $lineInfo['id']);
    }
    
    /**
     * 修改排线 - 删除订单.
     * 
     *  — 删除的订单是线路的最后一单，则删除该线路，否则保留
     *  — 删除的订单是线路的最后一单，司机已经接单，释放接触司机和订单关系
     *  - 删除的订单是线路的最后一单，已分配司机，释放线路与司机的关系
     *  — 如果存在释放的司机（未接单or接单），为司机自动分配订单
     */
    private static function _delOrder4Line($lineInfo, $oid, $coopDrivers, $adminId)
    {
        $lineOids = explode(',', $lineInfo['oids']);
        if (!in_array($oid, $lineOids))
        {
            throw new Exception('移出订单不在线路上，请核实！');
        }
        
        $oo = new Order_Order();
        
        $lol = new Logistics_Order_Line();
        if (count($lineOids) > 1) // 不是线路的最后一单
        {
            //解除 线在订单上的标记
            self::_clearLineInfoForOrder($oid);
            
            $newLineOids = array_diff($lineOids, array($oid));
            $orderInfos = $oo->getBulk($newLineOids);
            $transScope = self::getOrdersTransScope($orderInfos);
            
            $upData = array(
                'oids' => implode(',', $newLineOids),
                'trans_scope' => $transScope,
            );
            
            $lol->update($lineInfo['id'], $upData);
            
            //订单分配的司机删除
            $lc = new Logistics_Coopworker();
            foreach($coopDrivers as $driverInfo)
            {
                $upData = array('status'=>Conf_Base::STATUS_DELETED);
                $lc->update($oid, $driverInfo['cuid'], Conf_Base::COOPWORKER_DRIVER, $upData);
            }
        }
        else // 线路的最后一单
        {
            //解除 线在订单上的标记
            self::_clearLineInfoForOrder($oid);
            
            // 释放线路
            $lol->update($lineInfo['id'], array('status'=>Conf_Base::STATUS_DELETED));
            
            // 释放 接单的司机（解除订单和司机的关系）
            $lc = new Logistics_Coopworker();
            foreach($coopDrivers as $driver)
            {
                $upData = array('status'=>Conf_Base::STATUS_DELETED);
                $lc->update($driver['oid'], $driver['cuid'], Conf_Base::COOPWORKER_DRIVER, $upData);
            }
            
            // 释放 线路上分配的司机 && 重新分配订单
            Logistics_Api::unsetLineId($lineInfo['id'], 0, $oid, '删除排线订单，释放整条线路', $adminId);
        }
        $reason = array('reason' => '删除订单：' . $oid);
        Logistics_Api::addActionLog($adminId, 0, Conf_Base::COOPWORKER_DRIVER,
            Conf_Logistics_Action_Log::ACTION_CHG_ORDER, $oid, $reason, $lineInfo['id']);
    }
    
    /**
     * 取消排线.
     * 
     *  - 订单未出库
     */
    private static function _cancelOrderLineAndDriver($lineInfo, $reason, $adminId)
    {
        $lc = new Logistics_Coopworker();
        $oids = explode(',', $lineInfo['oids']);
        
        //释放司机和订单的关系
        $lcUpData = array('status' => Conf_Base::STATUS_DELETED);
        $lcWhere = sprintf('oid in (%s) and type=%d', $lineInfo['oids'], Conf_Base::COOPWORKER_DRIVER);
        $lc->updateByWhere($lcWhere, $lcUpData);
        
//        $coopDrivers = $lc->getByOids($oids);
//        
//        if (!empty($coopDrivers))
//        {
//            throw new Exception('司机已接单，不能取消！');
//        }
        
        // 释放司机和线路的关系
        Logistics_Api::unsetLineId($lineInfo['id'], 0, $oids[0], $reason, $adminId);
        
        // 删除线路
        $lol = new Logistics_Order_Line();
        $lol->update($lineInfo['id'], array('status'=>Conf_Base::STATUS_DELETED));
        
        // 更新订单中的线路
        self::_clearLineInfoForOrder($oids);
    }
    
    /**
     * 拒单.
     * 
     * - 已分配，未接单/已接单
     */
    public static function rejectOrderLineAndDriver($lineId, $lineInfo=array(), $dids=array(), $coopDrivers=array(), $reason='', $adminId=0)
    {
        if (empty($lineInfo))
        {
            $lol = new Logistics_Order_Line();
            $lineInfo = $lol->getByLineId($lineId);
        }

        // 获取分配的线路的司机
        $ldq = new Logistics_Driver_Queue();
        $allocDrivers = Tool_Array::list2Map($ldq->getByLineid($lineInfo['id']), 'did');

        $chgedCarModel = $lineInfo['car_models'];

        foreach($dids as $_d)
        {
//            if (array_key_exists($_d, $coopDrivers)
//                && $coopDrivers[$_d]['confirm_time']!='0000-00-00 00:00:00')
//            {
//                throw new Exception('司机已接单，拒单操作失败！');
//            }
            
            if (!array_key_exists($_d, $allocDrivers))
            {
                throw new Exception('该司机未分配到此线路上，请确认！');
            }
            
            
            $currCarModel = self::_chgCarModelStatus4OrderLine($chgedCarModel, $allocDrivers[$_d]['car_model']);
            if ($currCarModel == $chgedCarModel)
            {
                throw new Exception('排线车型状态异常，请联系管理员！');
            }
            $chgedCarModel = $currCarModel;
        }

        // 司机拒单
        $oids = explode(',', $lineInfo['oids']);
        $refuseRes = 1;
        foreach($dids as $did)
        {
            $res = Logistics_Api::refuseOrder($did, $oids[0], $reason, $adminId);
            $refuseRes = $refuseRes && $res;
        }

        if (!$refuseRes)
        {
            throw new Exception('拒单失败, 请反馈给技术人员!!');
        }
        
        // 更新排线安排的车型及状态
        $lol = new Logistics_Order_Line();
        $lol->update($lineInfo['id'], array('car_models'=>$chgedCarModel));
        $lineInfo['car_models'] = $chgedCarModel;

        foreach ($dids as $did)
        {
            //通过did获得司机的regid，推送消息
            $driver = Logistics_Api::getDriver($did);
            if (!empty($driver['regid']))
            {
                Push_Xiaomi_Api::pushToUserMessage($driver['regid'], Conf_Driver::$MSG_PUSH[Conf_Driver::MSG_REFUSE]['title'],
                    Conf_Driver::$MSG_PUSH[Conf_Driver::MSG_REFUSE]['desc'], Push_Xiaomi_Api::HAOCAI_DRIVER);
            }
        }

        // 自动分单
        self::autoAllocateDriverByLineId($lineInfo['id'], $lineInfo, $dids);
    }
    
    private static function _chgCarModelStatus4OrderLine($carModelInLine, $delCarModel)
    {
        $modelInLines = explode(Conf_Coopworker::Orderline_CarModel_Sp2, $carModelInLine);

        $isFind = false;
        foreach($modelInLines as &$model)
        {
            $_models = explode(Conf_Coopworker::Orderline_CarModel_Sp1, $model);
            $_carModel = substr($_models[0], 1);
            if ($_carModel==$delCarModel && $_models[1]==1 && !$isFind)
            {
                $_models[1] = 0;
                $isFind = true;
            }
            $model = implode(Conf_Coopworker::Orderline_CarModel_Sp1, $_models);

        }

        return implode(Conf_Coopworker::Orderline_CarModel_Sp2, $modelInLines);
    }

    public static function getUnlineOrderNum($search){
        $where = sprintf('status=%d and step>=%d and step<%d and line_id=0 and delivery_type<>%d',
            Conf_Base::STATUS_NORMAL, Conf_Order::ORDER_STEP_SURE, Conf_Order::ORDER_STEP_HAS_DRIVER, Conf_Order::DELIVERY_BY_YOURSELF);

        if (isset($search['max_oid']) && !empty($search['max_oid']))
        {
            $where .= ' and oid >'.$search['max_oid'];
        }
        if (isset($search['delivery_btime']) && !empty($search['delivery_btime'])
            && isset($search['delivery_etime']) && !empty($search['delivery_etime']) )
        {

            $where .= ' and delivery_date>="'. $search['delivery_btime'].
                '" and delivery_date<="'. $search['delivery_etime'].'"';
        }
        if (isset($search['wid']) && !empty($search['wid']))
        {
            $where .= ' and wid='. $search['wid'];
        }
        
        $oo = new Order_Order();
        $field = array('oid');
        $orderList = $oo->getListRawWhereWithoutTotal($where, '', 0, 0, $field);

        return count($orderList);
    }
    
    /**
     * 清除订单上的排线信息，使订单重新排线.
     * 
     * @params array $oids
     */
    private static function _clearLineInfoForOrder($oids)
    {
        if(!is_array($oids))
        {
            $oids = explode(',', $oids);
        }
        
        if (!is_array($oids) || empty($oids)) return;
        
        $oo = new Order_Order();
        $orderInfos = $oo->getBulk($oids, array('oid', 'step'));
        
        $hadSureOids = $unSureOids = array();
        foreach($orderInfos as $oinfo)
        {
            if ($oinfo['step'] >= Conf_Order::ORDER_STEP_SURE)
            {
                $hadSureOids[] = $oinfo['oid'];
            }
            else
            {
                $unSureOids[] = $oinfo['oid'];
            }
        }
        
        if (!empty($hadSureOids))
        {
            $upData = array('line_id'=>0, 'step'=>Conf_Order::ORDER_STEP_SURE);
            $owhere = 'oid in (' .implode(',', $hadSureOids).')';
            $oo->updateByWhere($upData, array(), $owhere);
        }
        if (!empty($unSureOids))
        {
            $upData = array('line_id'=>0, 'step'=>Conf_Order::ORDER_STEP_NEW);
            $owhere = 'oid in (' .implode(',', $unSureOids).')';
            $oo->updateByWhere($upData, array(), $owhere);
        }
    }
}