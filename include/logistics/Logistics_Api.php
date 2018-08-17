<?php

/**
 * 物流相关
 */
class Logistics_Api extends Base_Api
{
    public static function getDriver($did)
    {
        $rd = new Logistics_Driver();

        return $rd->get($did);
    }

    public static function getByDids($dids)
    {
        $rd = new Logistics_Driver();

        return $rd->getByDids($dids);
    }

    public static function getDriverList($searchConf, $start = 0, $num = 0, $getAll = 0)
    {
        $rd = new Logistics_Driver();

        $total = 0;
        $driverList = $rd->getList($total, $searchConf, $start, $num, $getAll);
        $t = 0;
        if (!empty($driverList))
        {
            $rcm = new Logistics_Car_Model();
            $modelList = $rcm->getAll($t);

            $rds = new Logistics_Driver_Source();
            $sourceList = $rds->getAll($t);

            foreach ($driverList as &$info)
            {
                $info['car_model'] = $modelList[$info['car_model']];
                $info['source'] = $sourceList[$info['source']];
                $info['warehouse'] = !empty($info['wid']) ? Conf_Warehouse::$WAREHOUSES[$info['wid']] : '-';
                $info['car_code_show'] = $info['car_code'] ? Conf_Driver::$CAR_CODE[$info['car_code']] : '-';
                $info['can_carry_show'] = $info['can_carry'] ? Conf_Driver::$CAN_CARRY[$info['can_carry']] : '-';
                $info['_trans_scope'] = !empty($info['trans_scope']) ? explode(',', $info['trans_scope']) : array();
            }
        }

        return array('total' => $total, 'list' => $driverList);
    }

    public static function getCoopworkerInfo($search, $userType = 0)
    {
        $ret = array();
        if ($userType == Conf_Base::COOPWORKER_DRIVER || $userType == 0)
        {
            $ld = new Logistics_Driver();
            $ret['driver'] = $ld->getList($total, $search, 0, 0, 1);
        }

        if ($userType == Conf_Base::COOPWORKER_CARRIER || $userType == 0)
        {
            $lc = new Logistics_Carrier();
            $ret['carrier'] = $lc->getList($total, $search, 0, 0, 1);
        }

        return $ret;
    }

    public static function addDriver(array $info)
    {
        //检查条件
        if (!Str_Check::checkMobile($info['mobile']))
        {
            throw new Exception('common:mobile format error');
        }

        $rd = new Logistics_Driver();
        $oldDriver = $rd->getByMobile($info['mobile']);
        if (!empty($oldDriver))
        {
            throw new Exception('driver: mobile used');
        }

        //添加
        return $rd->add($info);
    }

    public static function updateDriver($did, $info, $change = array())
    {
        if (isset($info['mobile']) && !Str_Check::checkMobile($info['mobile']))
        {
            throw new Exception('common:mobile format error');
        }

        //更新
        $rd = new Logistics_Driver();
        $updateRow = $rd->update($did, $info, $change);

        if ($updateRow == -1)
        {
            return FALSE;
        }

        return TRUE;
    }

    public static function deleteDriver($did)
    {
        $rd = new Logistics_Driver();

        return $rd->delete($did);
    }

    public static function getCarrier($cid)
    {
        $rc = new Logistics_Carrier();

        return $rc->get($cid);
    }

    public static function getCarrierList($searchConf, $start = 0, $num = 0, $getAll = 0)
    {
        $rc = new Logistics_Carrier();

        $total = 0;
        $carrierList = $rc->getList($total, $searchConf, $start, $num, $getAll);

        foreach ($carrierList as &$info)
        {
            $info['warehouse'] = !empty($info['wid']) ? Conf_Warehouse::$WAREHOUSES[$info['wid']] : '-';
        }

        return array('total' => $total, 'list' => $carrierList);
    }

    public static function addCarrier(array $info)
    {
        //检查条件
        if (!Str_Check::checkMobile($info['mobile']))
        {
            throw new Exception('common:mobile format error');
        }

        $rc = new Logistics_Carrier();
        $oldCarrier = $rc->getByMobile($info['mobile']);
        if (!empty($oldCarrier))
        {
            throw new Exception('carrier: mobile used');
        }

        //添加
        return $rc->add($info);
    }

    public static function updateCarrier($cid, $info, $change = array())
    {
        if (isset($info['mobile']) && !Str_Check::checkMobile($info['mobile']))
        {
            throw new Exception('common:mobile format error');
        }

        //更新
        $rc = new Logistics_Carrier();
        $updateRow = $rc->update($cid, $info, $change);

        if ($updateRow == -1)
        {
            return FALSE;
        }

        return TRUE;
    }

    public static function deleteCarrier($cid)
    {
        $rc = new Logistics_Carrier();

        return $rc->delete($cid);
    }

    public static function getCarModel($mid)
    {
        $rcm = new Logistics_Car_Model();

        return $rcm->get($mid);
    }

    public static function getModelList()
    {
        $rcm = new Logistics_Car_Model();

        $total = 0;
        $data = $rcm->getAll($total);

        return array('list' => $data, 'total' => $total);
    }

    public static function getSource($sid)
    {
        $rs = new Logistics_Driver_Source();

        return $rs->get($sid);
    }

    public static function getSourceList()
    {
        $rds = new Logistics_Driver_Source();

        $total = 0;
        $data = $rds->getAll($total);

        return array('list' => $data, 'total' => $total);
    }

    public static function calFreightByProductsConstruction($products, $consturction)
    {
        $cinfo = Crm2_Api::getConstructionSite($consturction);
        if (empty($cinfo))
        {
            throw new Exception('common:params error');
        }

        $city = $cinfo['city'];
        $district = $cinfo['district'];
        $area = $cinfo['ring_road'];

        return self::calFreightByProductsAddress($products, $city, $district, $area, 0, $cinfo['community_id'], $cinfo['cid']);
    }

    public static function calFreightByAddress($oid, $city, $district, $area, $communityId = 0, $delvieryType = Conf_Order::DELIVERY_EXPRESS)
    {
        $oo = new Order_Order();
        $orderInfo = $oo->get($oid);

        //4.1起，六环内 非砂石砖类订单金额满800元 免运费，否则收取29元。
        $orderProducts = Order_Api::getOrderProducts($oid);
        empty($communityId) && $communityId = $orderInfo['community_id'];

        $freight = self::calFreightByProductsAddress($orderProducts['products'], $city, $district, $area, $orderInfo['source_oid'], $communityId, $orderInfo['cid']);
        if ($city == Conf_City::CHONGQING && $delvieryType == Conf_Order::DELIVERY_QUICKLY)
        {
            $freight += 3000;
        }

        return $freight;
    }

    public static function calFreightByProductsAddress($products, $city, $district, $area, $sourceOid = 0, $communityId = 0, $cid = 0)
    {
        //补单按实际计算,暂时不计算优惠
        if ($sourceOid > 0)
        {
            return 0;
        }

        //处理套餐商品
        Shop_Helper::appendSinglePrice4PackageProducts($products, $city);

        switch ($city)
        {
            case Conf_City::CHONGQING:
                $feeFunc = new Logistics_Fee_Chongqing();
                $freight = $feeFunc->calFreight($products, $area);
                break;
            case Conf_City::TIANJIN:
                $feeFunc = new Logistics_Fee_Tianjin();
                $freight = $feeFunc->calFreight($products, $communityId);
                break;
            case Conf_City::LANGFANG:
                $feeFunc = new Logistics_Fee_Langfang();
                $freight = $feeFunc->calFreight($products, $cid, $communityId);
                break;
            case Conf_City::CHENGDU:
                $feeFunc = new Logistics_Fee_Chengdu();
                $freight = $feeFunc->calFreight($products, $district);
                break;
            case Conf_City::QINGDAO:
                $feeFunc = new Logistics_Fee_Qingdao();
                $freight = $feeFunc->calFreight($products, $district);
                break;
            case Conf_City::BEIJING;
            default:
                $feeFunc = new Logistics_Fee_Beijing();
                $freight = $feeFunc->calFreight($products, $city, $district, $area);
        }

        $freight < 0 && $freight = 0;

        return $freight * 100;
    }

    public static function calCarryFee4Carrier($oid)
    {
        $carryFee = 0;
        $carryFeeEle = 0;
        $workerCarryFee = 0;
        $workerCarryFeeEle = 0;

        $oo = new Order_Order();
        $orderProducts = $oo->getProductsOfOrder($oid);
        $pids = Tool_Array::getFields($orderProducts, 'pid');

        if (!empty($orderProducts))
        {
            $ss = new Shop_Product();
            $products = $ss->getBulk($pids);
            
            foreach ($orderProducts as $orderProduct)
            {
                $pid = $orderProduct['pid'];
                if (empty($products[$pid]))
                {
                    continue;
                }
                $baseProduct = $products[$pid];
                Shop_Api::setBaseCarryFee4Worker($baseProduct, $orderProduct);
                
                $carryFee += $baseProduct['carrier_fee'] * $orderProduct['num'];
                $carryFeeEle += $baseProduct['carrier_fee_ele'] * $orderProduct['num'];
                $workerCarryFee += $baseProduct['worker_ca_fee'] * $orderProduct['num'];
                $workerCarryFeeEle += $baseProduct['worker_ca_fee_ele'] * $orderProduct['num'];
            }
        }

        return array(
            'common' => $carryFee, 
            'ele' => $carryFeeEle, 
            'worker'=>array('common'=>$workerCarryFee, 'ele'=>$workerCarryFeeEle)
        );
    }
    
    /**
     * @param $oid
     * @param $service
     * @param $floorNum
     *
     * @return int
     *
     * 这个方法只用于在订单确认的时候计算搬运费
     * 因为这个时候订单的上楼方式，楼层都没有确定
     */
    public static function calCarryFee($oid, $service = -1, $floorNum = 0)
    {
        $oo = new Order_Order();
        $orderInfo = $oo->get($oid);

        $service == -1 && $service = $orderInfo['service'];
        $floorNum == 0 && $floorNum = $orderInfo['floor_num'];

        if ($service == 0)
        {
            return 0;
        }

        $client = new Yar_Client(MS . "/cmpt/order/fees");
        $result = $client->AdminCarryFee($oid);
        $fee = 0;
        if ( isset($result['user']) ) {
            $fee = $result['user'];
        }
        return $fee;
        //$products = $oo->getProductsOfOrder($oid);

        //return self::calCarryFeeByProducts($products, $service, $floorNum, $orderInfo['source_oid'], $orderInfo['city_id']);
    }

    public static function calCarryFeeByProducts($products, $service, $floorNum, $sourceOid = 0, $cityId = Conf_City::BEIJING)
    {
        Shop_Helper::appendSinglePrice4PackageProducts($products, $cityId);

        switch ($cityId)
        {
            case Conf_City::CHONGQING:
                $feeFunc = new Logistics_Fee_Chongqing();
                $carryFee = $feeFunc->calCarryFee($products, $service, $floorNum, $sourceOid);
                break;
            case Conf_City::TIANJIN:
                $feeFunc = new Logistics_Fee_Tianjin();
                $carryFee = $feeFunc->calCarryFee($products, $service, $floorNum, $sourceOid);
                break;
            case Conf_City::LANGFANG:
                $feeFunc = new Logistics_Fee_Langfang();
                $carryFee = $feeFunc->calCarryFee($products, $service, $floorNum, $sourceOid);
                break;
            case Conf_City::CHENGDU:
                $feeFunc = new Logistics_Fee_Chengdu();
                $carryFee = $feeFunc->calCarryFee($products, $service, $floorNum, $sourceOid);
                break;
            case Conf_City::QINGDAO:
                $feeFunc = new Logistics_Fee_Qingdao();
                $carryFee = $feeFunc->calCarryFee($products, $service, $floorNum, $sourceOid);
                break;
            case Conf_City::BEIJING:
            default:
                $feeFunc = new Logistics_Fee_Beijing();
                $carryFee = $feeFunc->calCarryFee($products, $service, $floorNum, $sourceOid);
        }

        return $carryFee;
    }

    public static function getFeeByDistanceAndModel($distance, $model, $wid = 0)
    {
        $fee = 0;

        $wid2city = Conf_Warehouse::$WAREHOUSE_CITY_MAPPING;
        $cityId = array_key_exists($wid, $wid2city) ? $wid2city[$wid] : Conf_City::BEIJING;

        $baseFee = 0;   //基础费用
        $increFee = 0;  //增量费用
        $km = ceil($distance / 1000);
        switch ($model)
        {
            case 4:     //金杯
                $baseFee = 6000;
                $increFee = $cityId == Conf_City::TIANJIN ? 500 : 600;
                if ($km <= 5)
                {
                    $fee = $baseFee;
                }
                else
                {
                    $fee = $baseFee + ($km - 5) * $increFee;
                }
                break;
            case 2:     //小面
                $baseFee = $cityId == Conf_City::TIANJIN ? 3500 : 4000;
                $increFee = $cityId == Conf_City::TIANJIN ? 350 : 400;

                if ($km <= 5)
                {
                    $fee = $baseFee;
                }
                else
                {
                    $fee = $baseFee + ($km - 5) * $increFee;
                }

                break;
            default:
                //nothing
        }

        return $fee;
    }

    public static function getFeeByDistanceAndModelNew($distance, $model, $wid = 0)
    {
        $fee = 0;

        $wid2city = Conf_Warehouse::$WAREHOUSE_CITY_MAPPING;
        $cityId = array_key_exists($wid, $wid2city) ? $wid2city[$wid] : Conf_City::BEIJING;

        $baseFee = 0;   //基础费用
        $increFee = 0;  //增量费用
        $km = ceil($distance / 1000);
        switch ($model)
        {
            case 4:     //金杯
                $baseFee = 6000;
                $increFee = $cityId == Conf_City::TIANJIN ? 500 : 600;
                if ($wid == Conf_Warehouse::WID_5 || $wid == Conf_Warehouse::WID_4)
                {
                    if ($km <= 5)
                    {
                        $fee = $baseFee;
                    }
                    else if ($km <= 15)
                    {
                        $fee = $baseFee + ($km - 5) * $increFee;
                    }
                    else
                    {
                        //金杯15公里内起步5公里60，超1公里费6元，16公里以上（含）超1公里递减0.3元以此类推，递减至4元止；
                        $fee = $baseFee + (15 - 5) * $increFee;
                        for ($i = 1; $i <= $km - 15; $i++)
                        {
                            $moreFee = $increFee - ($i * 30);

                            if ($moreFee < 400)
                            {
                                $moreFee = 400;
                            }

                            $fee += $moreFee;
                        }
                    }

                    $fee = floor($fee / 100) * 100;
                }
                else
                {
                    if ($km <= 5)
                    {
                        $fee = $baseFee;
                    }
                    else
                    {
                        $fee = $baseFee + ($km - 5) * $increFee;
                    }
                }
                break;
            case 2:     //小面
                $baseFee = $cityId == Conf_City::TIANJIN ? 3500 : 4000;
                $increFee = $cityId == Conf_City::TIANJIN ? 350 : 400;
                if ($wid == Conf_Warehouse::WID_5 || $wid == Conf_Warehouse::WID_4)
                {
                    if ($km <= 5)
                    {
                        $fee = $baseFee;
                    }
                    else if ($km <= 15)
                    {
                        $fee = $baseFee + ($km - 5) * $increFee;
                    }
                    else
                    {
                        //小面15公里内起步5公里40，超1公里费4元，16公里以上（含）超1公里递减0.2元以此类推，递减至3元止；
                        $fee = $baseFee + (15 - 5) * $increFee;
                        for ($i = 1; $i <= $km - 15; $i++)
                        {
                            $moreFee = $increFee - ($i * 0.2);
                            if ($moreFee < 4)
                            {
                                $moreFee = 4;
                            }

                            $fee += $moreFee;
                        }
                    }

                    $fee = floor($fee / 100) * 100;
                }
                else
                {
                    if ($km <= 5)
                    {
                        $fee = $baseFee;
                    }
                    else
                    {
                        $fee = $baseFee + ($km - 5) * $increFee;
                    }
                }

                break;
            default:
                //nothing
        }

        return $fee;
    }

    ///////////////////////////////////////////////////
    ///////////////司机派单/////////////////////////////
    ///////////////////////////////////////////////////

    public static function getQueueList($seacrhConf, $start = 0, $num = 20, $order = '')
    {
        $ldq = new Logistics_Driver_Queue();

        $data = $ldq->getList($seacrhConf, $start, $num, $order);
        if (!empty($data['list']))
        {
            $dids = Tool_Array::getFields($data['list'], 'did');
            $ld = new Logistics_Driver();
            $ret = $ld->getByDids($dids);
            $drivers = Tool_Array::list2Map($ret, 'did');

            foreach ($data['list'] as &$item)
            {
                $item['_driver'] = $drivers[$item['did']];
                $item['_wid'] = Conf_Warehouse::$WAREHOUSES[$item['wid']];
                $item['_car_model'] = Conf_Driver::$CAR_MODEL[$item['car_model']];
                $item['_step'] = Conf_Driver::$STEP_DESC[$item['step']];
                $item['_driver']['isLimited'] = Conf_Driver::isLimitCar($item['_driver']['car_code']);
            }
        }

        return $data;
    }

    public static function updateDriverInQueue($did, $info)
    {
        $ldq = new Logistics_Driver_Queue();

        return $ldq->updateByDid($did, $info);
    }

    /**
     * 统计仓库可用车辆，已经车辆的总数.
     *
     * @param int $wid
     *
     * @return array
     */
    public static function statAvailabedDrivers($wid)
    {
        $queueWhere = sprintf('status=0 and step=%d', Conf_Driver::STEP_CHECK_IN);
        $driverWhere = 'status=0';

        if (!empty($wid))
        {
            $queueWhere .= ' and wid=' . $wid;
            $driverWhere .= ' and wid=' . $wid;
        }
        $queueWhere .= ' group by car_model';
        $driverWhere .= ' group by car_model';

        $ldq = new Logistics_Driver_Queue();
        $ld = new Logistics_Driver();

        $ldqField = array('car_model', 'count(1) as av');
        $ldqRet = Tool_Array::list2Map($ldq->getListByWhere($queueWhere, array('check_time', 'asc'), $ldqField, FALSE), 'car_model');
        $ldRet = $ld->getByRawWhere($driverWhere, 0, 0, array('car_model', 'count(1) as total'));

        foreach ($ldRet as &$one)
        {
            $one['av_num'] = 0;
            if (array_key_exists($one['car_model'], $ldqRet))
            {
                $one['av_num'] = $ldqRet[$one['car_model']]['av'];
            }
        }

        return $ldRet;
    }

    /**
     * 获取可分配订单的司机队列
     *
     * @param $wid
     *
     * @return array
     */
    public static function getAvailableDriverQueue($wid)
    {
        $ldq = new Logistics_Driver_Queue();

        $where = sprintf('wid=%d AND line_id=0 AND step=%d AND status=%d', $wid, Conf_Driver::STEP_CHECK_IN, Conf_Base::STATUS_NORMAL);
        $orderBy = 'order by check_time asc';
        $list = $ldq->getListByWhere($where, $orderBy);
        if (empty($list))
        {
            return array();
        }

        $today = date('Y-m-d H:i:s', strtotime('today'));
        $tomorrow = date('Y-m-d H:i:s', strtotime('tomorrow'));
        foreach ($list as $k => $v)
        {
            if ($v['refuse_time'] >= $today && $v['refuse_time'] < $tomorrow && $v['refuse_num'] >= Conf_Driver::MAX_REFUSE_NUM)
            {
                unset($list[$k]);
            }
        }

        return $list;
    }

    /**
     * 获取司机队列信息
     *
     * @param $did
     *
     * @return array|mixed
     */
    public static function getDriverQueue($did)
    {
        if (empty($did))
        {
            return array();
        }

        $ldq = new Logistics_Driver_Queue();
        $queue = $ldq->getByDid($did);

        return $queue;
    }

    /**
     * 获取多个司机队列信息
     *
     * @param $dids
     *
     * @return array
     */
    public static function getDriverQueueByIds($dids)
    {
        $dids = array_unique(array_filter($dids));
        if (empty($dids))
        {
            return array();
        }

        $ldq = new Logistics_Driver_Queue();
        $list = $ldq->geBulk($dids);
        if (!empty($list))
        {
            foreach ($list as &$item)
            {
                $item['_step'] = Conf_Driver::$STEP_DESC[$item['step']];
            }
        }

        return $list;
    }

    public static function getLineDetail($lineId)
    {
        $func = new Logistics_Order_Line();

        $info = $func->getByLineId($lineId);
        $oids = explode(',', $info['oids']);
        $info['_oids'] = $oids;

        if (empty($info) || empty($oids))
        {
            return array();
        }

        return $info;
    }

    /**
     * 是否已签到
     *
     * @param $did
     *
     * @return bool
     */
    public static function isCheckIn($did)
    {
        if (empty($did))
        {
            return FALSE;
        }

        $ldq = new Logistics_Driver_Queue();
        $queue = $ldq->isCheckIn($did);
        if (!empty($queue))
        {
            return TRUE;
        }

        return FALSE;
    }

    public static function isArrive($did)
    {
        if (empty($did))
        {
            return FALSE;
        }

        $ldq = new Logistics_Driver_Queue();
        $queue = $ldq->getByDid($did);
        if (!empty($queue) && $queue['step'] != Conf_Driver::STEP_ARRIVE && $queue['step'] != Conf_Driver::STEP_EMPTY)
        {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * 签到
     *
     * @param $did
     * @param $wid
     *
     * @return bool|int|mixed
     */
    public static function checkIn($did, $wid)
    {
        $ldq = new Logistics_Driver_Queue();

        //判断司机是否存在
        $driver = self::getDriver($did);
        if (empty($driver))
        {
            return FALSE;
        }

        $queue = self::getDriverQueue($did);
        if (!empty($queue) && $queue['step'] != Conf_Driver::STEP_EMPTY && $queue['step'] != Conf_Driver::STEP_ARRIVE)
        {
            return FALSE;
        }

        //签到
        $info = array(
            'line_id' => 0, 'did' => $did, 'name' => $driver['name'], 'wid' => $wid, 'fee' => 0, 'car_model' => $driver['car_model'], 'step' => Conf_Driver::STEP_CHECK_IN, 'check_time' => date('Y-m-d H:i:s'), 'alloc_time' => '0000-00-00 00:00:00', 'status' => Conf_Base::STATUS_NORMAL,
        );
        $res = $ldq->checkIn($info);

        //分配订单
        Logistics_Order_Api::autoAllocateLineByDriver($did, $info['car_model'], $wid);

        return $res;
    }

    /**
     * @param $did
     * @param $lineId
     * @param $fee
     *
     * @return bool|int
     */
    public static function allocOrder($did, $lineId, $fee)
    {
        $ldq = new Logistics_Driver_Queue();
        $queue = $ldq->getByDid($did);

        if ($queue['step'] != Conf_Driver::STEP_CHECK_IN)
        {
            return FALSE;
        }

        $today = date('Y-m-d H:i:s', strtotime('today'));
        $tomorrow = date('Y-m-d H:i:s', strtotime('tomorrow'));
        if ($queue['refuse_time'] >= $today && $queue['refuse_time'] < $tomorrow && $queue['refuse_num'] >= Conf_Driver::MAX_REFUSE_NUM)
        {
            return FALSE;
        }

        $lol = new Logistics_Order_Line();
        $lineInfo = $lol->getByLineId($lineId);
        $oids = explode(',', $lineInfo['oids']);

        $openid = WeiXin_Coopworker_Api::getCoopworkerOpenid($did, 1);
        if (!empty($openid))
        {
            $oid = $oids[0];
            $order = Order_Api::getOrderInfo($oid);
            $carry = '不上楼';
            if ($order['service'] == 1)
            {
                $carry = '电梯上楼';
            }
            else if ($order['service'] == 2)
            {
                $carry = '楼梯上楼';
            }
            $wxRet = WeiXin_Coopworker_Api::sendAllocNoticeMessage($openid, $oid, $order['_delivery_date'], $order['address'], $carry, $fee, $lineId);

            $info = "【派单】司机ID: $did\t";
            $info .= "WX_SendMsgRet = " . var_export($wxRet, TRUE) . "\n";
            Tool_Log::addFileLog('co/order_line_' . date('Ymd'), $info);
        }

        $carModel = Conf_Driver::$CAR_MODEL[$queue['car_model']];
        $params = array('wid' => $queue['wid'], 'carModel' => $carModel, 'lineId' => $lineId, 'oid' => implode(',', $oids));
        Logistics_Api::addActionLog(999, $did, Conf_Base::COOPWORKER_DRIVER, Conf_Logistics_Action_Log::ACTION_ALLOC_ORDER, 0, $params, $lineId);
        //通过did获得司机的regid，推送消息
        $driver = Logistics_Api::getDriver($did);
        if (!empty($driver['regid']))
        {
            Push_Xiaomi_Api::pushToUserMessage($driver['regid'], Conf_Driver::$MSG_PUSH[Conf_Driver::MSG_NEW_ORDER]['title'], Conf_Driver::$MSG_PUSH[Conf_Driver::MSG_NEW_ORDER]['desc'], Push_Xiaomi_Api::HAOCAI_DRIVER, 1);
        }
        $res = self::_changeStep($did, Conf_Driver::STEP_ALLOC, $lineId, $fee);

        return $res;
    }

    public static function acceptOrder($did)
    {
        $ldq = new Logistics_Driver_Queue();
        $queue = $ldq->getByDid($did);
        if (empty($queue) || $queue['wid'] <= 0 || $queue['line_id'] <= 0)
        {
            return FALSE;
        }

        if ($queue['step'] != Conf_Driver::STEP_ALLOC)
        {
            return FALSE;
        }

        $today = date('Y-m-d H:i:s', strtotime('today'));
        $tomorrow = date('Y-m-d H:i:s', strtotime('tomorrow'));
        if ($queue['refuse_time'] >= $today && $queue['refuse_time'] < $tomorrow && $queue['refuse_num'] >= Conf_Driver::MAX_REFUSE_NUM)
        {
            return FALSE;
        }

        $lol = new Logistics_Order_Line();
        $lineInfo = $lol->getByLineId($queue['line_id']);
        if (empty($lineInfo) || empty($lineInfo['oids']))
        {
            return FALSE;
        }

        $oids = explode(',', $lineInfo['oids']);
        $oo = new Order_Order();
        $orders = $oo->getList($oids);
        foreach ($orders as $order)
        {
            if ($order['step'] < Conf_Order::ORDER_STEP_SURE)
            {
                return FALSE;
            }
        }

        foreach ($oids as $oid)
        {
            $hasSelected = Logistics_Coopworker_Api::getOrderOfWorkers($oid, 1);

            $cuids = Tool_Array::getFields($hasSelected, 'cuid');
            if (in_array($did, $cuids))
            {
                continue;
            }

            $data = array(
                'cuid' => $did, 'car_model' => $queue['car_model'], 'oid' => $oid, 'wid' => $queue['wid'], 'price' => $queue['fee'], 'type' => 1, 'suid' => 999, 'user_type' => 1, 'alloc_time' => $queue['alloc_time'], 'confirm_time' => date('Y-m-d H:i:s'),
            );

            $oc = new Logistics_Coopworker();
            $oc->saveWorkerForOrder($data);

            // 调度权限：更新订单状态为 '已安排司机'
            $step = Conf_Order::ORDER_STEP_HAS_DRIVER;
            $staff = array('suid' => 999);
            Order_Api::forwardOrderStep($oid, $step, $staff);
        }

        $res = self::_changeStep($did, Conf_Driver::STEP_ACCEPT);
        if ($res)
        {
            Logistics_Api::addActionLog(999, $did, Conf_Base::COOPWORKER_DRIVER, Conf_Logistics_Action_Log::ACTION_ACCEPT_ORDER, 0, array(), $queue['line_id']);
        }

        return $res;
    }

    public static function sendOrder($did)
    {
        $ldq = new Logistics_Driver_Queue();
        $queue = $ldq->getByDid($did);

        if ($queue['step'] != Conf_Driver::STEP_ACCEPT)
        {
            return FALSE;
        }

        $res = self::_changeStep($did, Conf_Driver::STEP_LEAVE);
        if ($res)
        {
            Logistics_Api::addActionLog(999, $did, Conf_Base::COOPWORKER_DRIVER, Conf_Logistics_Action_Log::ACTION_SEND_ORDER, 0, array(), $queue['line_id']);
        }

        return $res;
    }

    public static function finishOrder($did)
    {
        $ldq = new Logistics_Driver_Queue();
        $queue = $ldq->getByDid($did);

        if ($queue['step'] != Conf_Driver::STEP_LEAVE)
        {
            return FALSE;
        }

        $res = self::_changeStep($did, Conf_Driver::STEP_ARRIVE);
        if ($res)
        {
            Logistics_Api::addActionLog(999, $did, Conf_Base::COOPWORKER_DRIVER, Conf_Logistics_Action_Log::ACTION_ARRIVE_ORDER, 0, array(), $queue['line_id']);
        }
    }

    public static function clearDriverQueue($did)
    {
        $ldq = new Logistics_Driver_Queue();
        $where = array('did' => $did);

        return $ldq->deleteByWhere($where);
    }

    private static function _changeStep($did, $newStep, $lineId = 0, $fee = 0)
    {
        $ldq = new Logistics_Driver_Queue();

        $queue = $ldq->getByDid($did);
        if (empty($queue) || $queue['step'] >= $newStep)
        {
            return FALSE;
        }

        $update = array(
            'step' => $newStep,
        );

        if ($newStep == Conf_Driver::STEP_ALLOC)
        {
            $update['line_id'] = $lineId;
            $update['alloc_time'] = date('Y-m-d H:i:s');
            $update['fee'] = $fee;
        }
        else if ($newStep == Conf_Driver::STEP_ACCEPT)
        {
            $update['refuse_num'] = 0;
        }
        else if ($newStep == Conf_Driver::STEP_LEAVE)
        {
            $driverQueue = Logistics_Api::getDriverQueue($did);
            $lineInfo = Logistics_Api::getLineDetail($driverQueue['line_id']);
            $oids = explode(',', $lineInfo['oids']);
            $upaDate['delivery_time'] = date('Y-m-d H:i:s');
            foreach ($oids as $oid)
            {
                Logistics_Coopworker_Api::updateOrderOfWorker($did, $oid, Conf_Base::COOPWORKER_DRIVER, $upaDate);
            }
        }

        return $ldq->update($queue['id'], $update);
    }

    /**
     * 调度释放分配订单的司机.
     *
     * @param int $lineid  排线ID
     * @param int $did     司机ID，{!0-释放线上的一个司机， 0-释放线上的所有司机}
     * @param int $oid     订单id
     * @param int $reason  原因
     * @param int $adminId 操作员ID
     */
    public static function unsetLineId($lineid, $did = 0, $oid = 0, $reason = '', $adminId = Conf_Admin::ADMINOR_AUTO)
    {
        $ldq = new Logistics_Driver_Queue();

        $list = $ldq->getByLineid($lineid);

        if (!empty($list))
        {
            foreach ($list as $queue)
            {
                if (empty($queue))
                {
                    continue;
                }

                $update = array(
                    'step' => Conf_Driver::STEP_CHECK_IN, 'line_id' => 0,
                );

                $affectRaw = 0;
                if (empty($did)) //$did为空，释放所有线路司机
                {
                    $affectRaw = $ldq->update($queue['id'], $update);
                }
                else if ($did == $queue['did']) //$did不空，只释放指定司机
                {
                    $affectRaw = $ldq->update($queue['id'], $update);
                }

                // 为该司机自动分配订单
                if ($affectRaw)
                {
                    Logistics_Order_Api::autoAllocateLineByDriver($queue['did'], $queue['car_model'], $queue['wid'], $lineid);

                    // add log
                    $params = array('reason' => $reason);
                    Logistics_Api::addActionLog($adminId, $queue['did'], Conf_Base::COOPWORKER_DRIVER, Conf_Logistics_Action_Log::ACTION_RELEASE_ORDER_LINE, $oid, $params, $lineid);
                }
            }
        }
    }

    public static function refuseOrder($did, $oid = 0, $reason = '', $adminId = Conf_Admin::ADMINOR_AUTO)
    {
        $ldq = new Logistics_Driver_Queue();

        $queue = $ldq->getByDid($did);
        if (empty($queue) || $queue['step'] != Conf_Driver::STEP_ALLOC)
        {
            return FALSE;
        }

        $today = date('Y-m-d H:i:s', strtotime('today'));
        $tomorrow = date('Y-m-d H:i:s', strtotime('tomorrow'));

        $update = array(
            'line_id' => 0, 'fee' => 0, 'alloc_time' => '0000-00-00 00:00:00', 'check_time' => date('Y-m-d H:i:s'), 'refuse_time' => date('Y-m-d H:i:s'), 'step' => Conf_Driver::STEP_CHECK_IN,
        );

        if ($queue['refuse_time'] >= $today && $queue['refuse_time'] < $tomorrow)
        {
            $change = array('refuse_num' => 1);
        }
        else
        {
            $change = array();
            $update['refuse_num'] = 1;
        }

        $res = $ldq->update($queue['id'], $update, $change);
        if ($res)
        {
            $params = array('reason' => $reason);
            Logistics_Api::addActionLog($adminId, $did, Conf_Base::COOPWORKER_DRIVER, Conf_Logistics_Action_Log::ACTION_REFUSE_ORDER, $oid, $params, $queue['line_id']);
        }

        return $res;
    }

    /**
     * 根据订单计算云鸟的2017新运费
     * 1、订单配送距离15KM内（含），每单按货值的8%收费，每个订单单值按1050元计算，补单不收费。
     * 2、订单配送距离大于15KM，从16KM起，金杯补助4元/KM、小面补助3元/KM（以好材系统调用百度地图线路距离为准）；
     * 3、小面核载1.2吨，金杯核载2.5吨：超载按80元/吨收取运费。
     * 示例：订单配送距离15KM内、货重5.5吨，使用金杯及其它车型配送运费为324元（84+3*80）。
     * 超载部分运费， 按（超载重量x / 1000） * 80 计算；最终获得的金额，向下取整，比如5.6算5块；
     * 订单配送距离，不足一公里的部分舍去；
     *
     * @param $orders
     *
     * @return array
     */
    public static function calYunniaoFreight($orders, $drivers = array())
    {
        if (empty($drivers))
        {
            $dd = new Data_Dao('t_driver');
            $drivers = $dd->setSlave()->getAll();
        }

        //距离
        $cmids = Tool_Array::getFields($orders, 'community_id');
        $distances = Order_Community_Api::getDistanceByCmids($cmids);
        $orderDistances = array();
        foreach ($orders as $order)
        {
            $oid = $order['oid'];
            $wid = $order['wid'];
            $cmid = $order['community_id'];
            $orderDistances[$oid] = $distances[$cmid][$wid];
        }

        //重量
        $orderWeight = array();
        $oids = Tool_Array::getFields($orders, 'oid');
        $orderProducts = Order_Api::getProductByOids($oids, array('rid', 'sid', 'oid', 'num'));
        $skuAll = Shop_Api::getAllSku();
        foreach ($orderProducts as $product)
        {
            if ($product['rid'] > 0)
            {
                continue;
            }

            $sid = $product['sid'];
            $oid = $product['oid'];
            $weight = $skuAll[$sid]['weight'];
            $orderWeight[$oid] += $weight * $product['num'] / 1000;
        }

        $baseFee = 1050 * 0.08;
        $orderFreight = array();
        $orderDrivers = Logistics_Coopworker_Api::getByOids($oids);
        foreach ($orderDrivers as $worker)
        {
            //只看运费
            if ($worker['type'] != 1)
            {
                continue;
            }

            //只看云鸟
            $cuid = $worker['cuid'];
            if ($drivers[$cuid]['source'] != 3)
            {
                continue;
            }

            $oid = $worker['oid'];
            //* 1、订单配送距离15KM内（含），每单按货值的8%收费，每个订单单值按1050元计算，补单不收费。
            if ($orderDistances[$oid] <= 15 * 1000)
            {
                $orderFreight[$oid] += $baseFee;
            }
            //* 2、订单配送距离大于15KM，从16KM起，金杯补助4元/KM、小面补助3元/KM（以好材系统调用百度地图线路距离为准）；
            else
            {
                $moreDistance = floor(($orderDistances[$oid] - 15 * 1000) / 1000);
                $moreFee = 0;
                if ($drivers[$cuid]['car_model'] == Conf_Driver::CAR_MODEL_XIAOXINGMIANBAO || $drivers[$cuid]['car_model'] == Conf_Driver::CAR_MODEL_ZHONGXINGMIANBAO)
                {
                    $moreFee = floor($moreDistance * 3) * 100;
                }
                else
                {
                    $moreFee = floor($moreDistance * 4) * 100;
                }

                $orderFreight[$oid] += $baseFee + $moreFee;
            }
            //* 3、小面核载1.2吨，金杯核载2.5吨：超载按80元/吨收取运费。
            if ($drivers[$cuid]['car_model'] == Conf_Driver::CAR_MODEL_XIAOXINGMIANBAO || $drivers[$cuid]['car_model'] == Conf_Driver::CAR_MODEL_ZHONGXINGMIANBAO)
            {
                if ($orderWeight[$oid] > 1.2 * 1000)
                {
                    $moreWeight = floor((($orderWeight[$oid] - 1.2 * 1000) / 1000) * 80) * 100;
                    $orderFreight[$oid] += $moreWeight + $baseFee;
                }
            }
            else
            {
                if ($orderWeight[$oid] > 2.5 * 1000)
                {
                    $moreWeight = floor((($orderWeight[$oid] - 2.5 * 1000) / 1000) * 80) * 100;
                    $orderFreight[$oid] += $moreWeight + $baseFee;
                }
            }
        }

        return $orderFreight;
    }

    //////////////////////日志//////////////////////
    public static function addActionLog($adminId, $cuid, $type, $actionType, $oid = 0, $params = array(), $lineId = 0)
    {
        $aol = new Logistics_Action_Log();

        $info = array(
            'admin_id' => $adminId, 'cuid' => $cuid, 'type' => $type, 'action_type' => $actionType, 'params' => json_encode($params), 'oid' => $oid, 'line_id' => $lineId,
        );

        return $aol->add($info);
    }

    public static function getActionLogList($searchConf, $start = 0, $num = 0)
    {
        $aol = new Logistics_Action_Log();
        $data = $aol->getList($searchConf, $start, $num);
        $total = $data['total'];
        $list = $data['list'];

        $dids = array();
        $cids = array();
        foreach ($list as $item)
        {
            if ($item['type'] == Conf_Base::COOPWORKER_DRIVER)
            {
                $dids[] = $item['cuid'];
            }
            if ($item['type'] == Conf_Base::COOPWORKER_CARRIER)
            {
                $cids[] = $item['cuid'];
            }
        }

        $drivers = array();
        if (!empty($dids))
        {
            $ld = new Logistics_Driver();
            $data = $ld->getByDids($dids);
            $drivers = Tool_Array::list2Map($data, 'did');
        }

        $carriers = array();
        if (!empty($cids))
        {
            $lc = new Logistics_Carrier();
            $data = $lc->getByCids($cids);
            $carriers = Tool_Array::list2Map($data, 'cid');
        }

        $as = new Admin_Staff();
        $adminList = $as->getList($t, 0, 0);
        $adminListMap = Tool_Array::list2Map($adminList, 'suid');
        foreach ($list as &$info)
        {

            $desc = Conf_Logistics_Action_Log::$ACTION_DESC[$info['action_type']];

            if (!empty($info['params']))
            {
                $params = json_decode($info['params']);
                if (!empty($params))
                {
                    foreach ($params as $k => $v)
                    {
                        $desc = str_replace('{' . $k . '}', $v, $desc);
                    }
                }
            }

            if ($info['type'] == Conf_Base::COOPWORKER_DRIVER)
            {
                $info['_worker'] = $drivers[$info['cuid']];
            }
            if ($info['type'] == Conf_Base::COOPWORKER_CARRIER)
            {
                $info['_worker'] = $carriers[$info['cuid']];
            }

            $info['desc'] = $desc;
            $info['admin_name'] = $adminListMap[$info['admin_id']]['name'];
            $info['action'] = Conf_Logistics_Action_Log::$ACTION_TYPE[$info['action_type']];
        }

        return array('list' => $list, 'total' => $total);
    }

    public static function getDriverByDids($dids)
    {
        $ld = new Logistics_Driver();
        $data = $ld->getByDids($dids);

        return $data;
    }

    /**
     *
     * 生成司机附加费用
     *
     * @param array $data , 数组，键值为费用类型，值为金额，单位分
     *
     * @return string 返回字符串，格式为 类型：金额  多个用逗号分隔
     */

    public static function generateDriverFee($data)
    {
        assert(!empty($data) && is_array($data));

        $arr = array();
        foreach ($data as $type => $price)
        {
            if (!empty($price))
            {
                $arr[] = $type . ':' . $price * 100;
            }
        }

        return implode(',', $arr);
    }

    /**
     *
     * 解析司机附加费用
     *
     * @param $otherPrice , 从数据库取出来的附加费用
     *
     * @return array $data 返回数组
     */

    public static function parseDriverFee($otherPrice)
    {
        assert(!empty($otherPrice));

        $arr = explode(',', $otherPrice);
        $data = array();
        foreach ($arr as $value)
        {
            if (!empty($value))
            {
                $fee = explode(',', $value);
                foreach ($fee as $price)
                {
                    $one = explode(':', $price);
                    $data[$one[0]] = $one[1];
                }
            }
        }

        return $data;
    }
}
