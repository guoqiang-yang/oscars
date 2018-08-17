<?php

/**
 * 订单状态流转类.
 * 
 * @author guoqiang yang
 * @date 2017-08-01
 */

class Order_Step
{   
    private $response;
    
    private $oid;
    private $orderInfo;
    
    private $hOrder;
    
    function __construct($oid)
    {
        $this->response = array(
            'errno' => 0,
            'errmsg' => 'succ',
            'data' => array()
        );
        
        assert(!empty($oid));
        
        $this->oid = $oid;
        $this->hOrder = new Order_Order();
        $this->orderInfo = $this->hOrder->get($this->oid, true);
        
    }


    /**
     * 客服确认.
     * 
     * @param array $staff
     */
    public function sure($staff)
    {
        try{
            $this->_checkOrderSure($staff);
            $orderProducts = $this->hOrder->getProductsOfOrder($this->oid);
            $isChgOrderProductsPrice = $this->_appendMoreInfo4OrderProducts($orderProducts);   //附加订单商品信息
            $this->_allocAndOccupiedStock($orderProducts);          //分配库存，并占用
            $this->_updateOrderProducts4Sure($orderProducts);       //更新订单商品信息
            $this->_setPrivilege4Sure();
            $this->_updateOrder4Sure($staff);
            $this->_updateDeliveryDate();
            
            if ($isChgOrderProductsPrice)
            {
                Order_Api::updateOrderTotalPrice($this->oid, $orderProducts);
            }
            
            Order_Api::updateOrderModify($this->oid);
            
            // 日志
            $param['newStep'] = Conf_Order::$ORDER_STEPS[Conf_Order::ORDER_STEP_SURE];
			Admin_Api::addOrderActionLog($staff['suid'], $this->oid, Conf_Order_Action_Log::ACTION_CHANGE_STEP, $param);
            
            //记录时间, 闪现提示使用
            //self::setLatestSureTime($this->orderInfo['wid']);
            
            
            //刷新订单中空采商品的占用
            //$this->orderInfo['step'] = Conf_Order::ORDER_STEP_SURE;
            //self::_refreshProductOccupied($oid, $this->orderInfo);
            
        } catch(Exception $e) {
            $this->_showErrorMsg($e->getCode(), $e->getMessage());
            return $this->response;
        }
        
        return $this->response;
    }
    
    /**
     * 客服确认: 校验数据.
     */
    private function _checkOrderSure($staff)
    {
        if (empty($this->orderInfo) || $this->orderInfo['status']!=Conf_Base::STATUS_NORMAL)
        {
            throw new Exception ('', 1001);
        }
        if (empty($this->orderInfo['wid']))
        {
            throw new Exception('', 1002);
        }
        if ($this->orderInfo['step'] >= Conf_Order::ORDER_STEP_SURE)
        {
            throw new Exception ('', 2001);
        }
        if ($this->orderInfo['delivery_type']!=Conf_Order::DELIVERY_BY_YOURSELF
            && empty($this->orderInfo['community_id']))
        {
            throw new Exception('', 2002);
        }
        $needPayOrGuarantedCityList = array(Conf_City::CHONGQING, Conf_City::CHENGDU);
        if (in_array($this->orderInfo['city_id'], $needPayOrGuarantedCityList) && $this->orderInfo['aftersale_type'] == 0 
            && $this->orderInfo['paid'] == Conf_Order::UN_PAID && $this->orderInfo['is_guaranteed'] == 2)
        {
            throw new Exception('', 2004);
        }
        
        // 普采缺货不能确认
        if ($this->orderInfo['aftersale_type'] == 0)
        {
            Warehouse_Api::checkOrderProductsStockByOid($this->oid, $this->orderInfo['wid']);
        }
        
        // 需要领导确认的订单 - 现款后确认订单
        // 黑名单销售，可以确认：1 完全付款 2 首单客户&&应收<2000元 3 无欠款客户&&应收<2000元
        $this->_canSureSalesOrder($staff);
    }
    
    /**
     * 是否可以确认某销售的订单.
     * 
     *  需要领导确认的订单 - 现款后确认订单
     *  黑名单销售，可以确认：1 完全付款 2 首单客户&&应收<2000元 3 无欠款客户&&应收<2000元
     */
    private function _canSureSalesOrder($staff)
    {
        $_superSureSuid = array(1454, 1423, 1181, 1004, 1029, 1426);
        $_auditSureSuid = array(
            1426 => array(
                'name' => '奉铁钢',
                'sales' => array(),
            ),
            1118 => array(
                'name' => '黄行',
                'sales' => array(1261, 1349),
            ),
        );
        
        $cc = new Crm2_Customer();
        $customerInfo = $cc->get($this->orderInfo['cid']);
        if($customerInfo['level_for_sys'] == Conf_User::CRM_SYS_LEVEL_BAD && $this->orderInfo['paid'] != Conf_Order::HAD_PAID)
        {
            throw new Exception('黑名单客户，请先付款再确认！');
        }
     
        $orderTotalPay = Order_Helper::calOrderTotalPrice($this->orderInfo);
        $orderNeedPay = Order_Helper::calOrderNeedToPay($this->orderInfo);
        
        $isHadPaid = $this->orderInfo['paid']==Conf_Order::HAD_PAID&&$orderNeedPay<=0? true: false; //是否安全支付
        $firstOrder = $customerInfo['order_num']==0 && $orderTotalPay<=200000? true: false;         //是否首单&&小于2000
        $noArrear = $customerInfo['account_balance']<100 && $orderTotalPay<=200000? true: false;    //是否无欠款&&小于2000
        $canAmountPay = $customerInfo['account_amount']>$orderNeedPay? true: false;                 //是否余额可以支付
        $isAftersaleOrder = $this->orderInfo['aftersale_type']!=0? true: false;                     //是否售后单关联订单
        
        $isLimitedSales = false;
        $canAuditSuid = 0;
        $canAuditName = '';
        foreach($_auditSureSuid as $_auditSuid => $conf)
        {
            if (in_array($this->orderInfo['saler_suid'], $conf['sales']))
            {
                $isLimitedSales = true;
                $canAuditSuid = $_auditSuid;
                $canAuditName = $conf['name'];
                break;
            }
        }
        
        $isSuperAuditor = in_array($staff['suid'], $_superSureSuid)? true: false;
        $isDirectAuditor = $canAuditSuid==$staff['suid']? true: false;

        $canSure = $isHadPaid || $firstOrder || $noArrear || $canAmountPay? true: false;
        if (in_array($this->orderInfo['saler_suid'], array(1041, 1089, 1320)) ) //特殊处理 from：肖亮东
        {
            $canSure = $isHadPaid || $canAmountPay ? true: false;
        }
        
        if ($isLimitedSales && !$isSuperAuditor && !$isDirectAuditor && !$canSure && !$isAftersaleOrder)
        {
            throw new Exception('确认失败：订单未支付，请先支付订单或找【'.$canAuditName.'】审核确认');
        }
    }
    
    /**
     * 客服确认：为订单商品补充信息.
     * 
     * 信息：成本，仓库，sid(如果为空)
     */
    private function _appendMoreInfo4OrderProducts(&$orderProducts)
    {
        if (empty($orderProducts) && $this->orderInfo['aftersale_type'] != Conf_Order::AFTERSALE_TYPE_REFUND)
        {
            throw new Exception(2003);
        }
        
        // 检测订单商品的skuid是否存在，没有存在，补充（防止t_order_product sid 为0）
        $sp = new Shop_Product();
        $allPids = array();
        $abnormalPid = array();
        foreach ($orderProducts as $p)
        {
            if (empty($p['sid'])) //防止t_order_product sid 为0
            {
                $abnormalPid[] = $p['pid'];
            }
            $allPids[] = $p['pid'];
        }
        
        // 补充没有skuid的订单商品信息；实时更新商品的售价
        $productInfos = $sp->getBulk($allPids);
        
        Shop_Api::decoratorProducts($productInfos, $this->orderInfo['cid'],$this->orderInfo['city_id'], Conf_Activity_Flash_Sale::PALTFORM_BOTH,'real');
        
        $isChgOrderProductsPrice = false;
        foreach($orderProducts as &$_p)
        {
            $newPrice = $productInfos[$_p['pid']]['sale_price'];
            
            if ($_p['price'] != $newPrice)
            {
                $isChgOrderProductsPrice = true;
            }
            
            $_p['price'] = $newPrice;
            $_p['ori_price'] = $productInfos[$_p['pid']]['ori_price'];
            
            if (empty($_p['sid']))
            {
                $_p['sid'] = $productInfos[$_p['pid']]['sid'];
            }
        }
        
//        if (!empty($abnormalPid))
//        {
//            $abnormalPinfos = $sp->getBulk($abnormalPid);
//            foreach($orderProducts as &$_p)
//            {
//                if (!empty($_p['sid'])) continue;
//                
//                if (isset($abnormalPinfos[$_p['pid']]))
//                {
//                    $_p['sid'] = $abnormalPinfos[$_p['pid']]['sid'];
//                }
//                else
//                {
//                    throw new Exception('订单商品基本信息异常：pid: '. $_p['pid']);
//                }
//            }
//        }
        unset($_p);
        
        // 补充商品的成本，仓库等属性
        $sids = Tool_Array::getFields($orderProducts, 'sid');
        $skuCosts = Shop_Cost_Api::getSimpleCost($this->orderInfo['wid'], $sids);
        foreach ($orderProducts as &$_p)
        {
            $_p['cost'] = $skuCosts[$_p['sid']];
            $_p['wid'] = $this->orderInfo['wid'];
        }
        
        return $isChgOrderProductsPrice;
    }
    
    /**
     * 客服确认：占用库存.
     */
    private function _allocAndOccupiedStock(&$orderProducts)
    {
        if(empty($orderProducts)) return;
        $needOccupied = true;
        
        //判断是否换货单生成的补单,需不需要占用库存
        if ($this->orderInfo['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_EXCHANGED)
        {
            $exchangedInfo = Exchanged_Api::getExchanged($this->orderInfo['aftersale_id']);
            if ($exchangedInfo['info']['need_storage'] == 0)
            {
                $needOccupied = false;
            }
        }
        //判断是否补漏单生成的补单，需不需要占用库存
        if ($this->orderInfo['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_TRAPS)
        {

            $trapsInfo = Traps_Api::getTraps($this->orderInfo['aftersale_id']);
            if ($trapsInfo['info']['need_storage'] == 0)
            {
                $needOccupied = FALSE;
            }
        }
        
        if (!$needOccupied) return;
        
        // 占用库存
        $wl = new Warehouse_Location();
        $ws = new Warehouse_Stock();
        $productWithLocAndVnum = Warehouse_Location_Api::distributeNumFromLocation($orderProducts, $this->orderInfo['wid'], 1, TRUE);
        
        $sids = Tool_Array::getFields($orderProducts, 'sid');
        $stockInfos = Tool_Array::list2Map($ws->getBulk($this->orderInfo['wid'], $sids), 'sid');
        
        foreach($orderProducts as &$_p)
        {
            $_p['outsourcer_id'] = 0;
            if($stockInfos[$_p['sid']]['outsourcer_id'] > 0)
            {
                $_p['vnum'] = $_p['num'];
                $_p['outsourcer_id'] = $stockInfos[$_p['sid']]['outsourcer_id'];    //将外包商的信息记录到订单商品/快照
                
                continue;
            }
            $locInfo4Sku = $productWithLocAndVnum[$_p['sid']];
            
            //订单商品分配占用
            $_p['location'] = $locInfo4Sku['loc'];
            $_p['vnum'] = $locInfo4Sku['vnum'];
            
            //拣货
            $allocNum4Sid = 0; //sid总占用
            foreach($locInfo4Sku['raw_loc'] as $locInfo)
            {
                $allocNum4Sid += $locInfo['num'];
            }
            if ($_p['picked'] > 0 && $allocNum4Sid<$_p['picked'])  //重新拣货：已拣货，占用库存小于捡货数量时picked置成0
            {
                $_p['picked'] = 0;
            }
            else if (Order_Picking_Api::isAutoPicked($this->orderInfo['wid'], $_p['pid']))   //自动拣货
            {
                $_p['picked'] = $_p['num'] - $_p['vnum'];
                $_p['picked_time'] = date('Y-m-d H:i:s');
            }
            
            // 更新占用
            // t_sku_2_location 更新占用
            if (!empty($locInfo4Sku['raw_loc']))
            {
                foreach ($locInfo4Sku['raw_loc'] as $rawLoc)
                {
                    $wlChgData = array('occupied' => $rawLoc['num']);
                    $wl->update($_p['sid'], $rawLoc['loc'], $this->orderInfo['wid'], array(), $wlChgData);
                }
            }

            // t_stock 更新占用
            $occupiedNum4Sid = $_p['num'] - $_p['vnum'];
            if ($occupiedNum4Sid > 0)
            {
                $ws->save($this->orderInfo['wid'], $_p['sid'], array(), array('occupied' => $occupiedNum4Sid));
            }
        }
    }
    
    private function _updateOrderProducts4Sure($orderProducts)
    {
        foreach ($orderProducts as $_p)
        {
            $opUpdata = array(
                'sid' => $_p['sid'],
                'wid' => $_p['wid'],
                'cost' => $_p['cost'],
                'location' => $_p['location'],
                'vnum' => $_p['vnum'],
                'picked' => $_p['picked'],
                'picked_time' => $_p['picked_time'],
                'outsourcer_id' => $_p['outsourcer_id'],
            );
            if (isset($_p['price']))
            {
                $opUpdata['price'] = $_p['price'];
            }
            if (isset($_p['ori_price']))
            {
                $opUpdata['ori_price'] = $_p['ori_price'];
            }
            
            $this->hOrder->updateOrderProductInfo($this->oid, $_p['pid'], 0, $opUpdata);
        }
    }
    
    private function _updateOrder4Sure($staff)
    {   
        // 如果是客服确认, 直接变为已采购, 默认跳过采购确认这一步
        $upData['step'] = Conf_Order::ORDER_STEP_BOUGHT;

        // 客服自提，订单状态直接到 已安排司机
        if ($this->orderInfo['delivery_type'] == Conf_Order::DELIVERY_BY_YOURSELF)
        {
            $upData['step'] = Conf_Order::ORDER_STEP_HAS_DRIVER;
        }
        //如果已经有司机了，客服确认之后直接跳转到已安排司机的状态
        $oc = new Logistics_Coopworker();
        $ret = $oc->getByOid($this->oid, 0, 1);
        if (!empty($ret))
        {
            $upData['step'] = Conf_Order::ORDER_STEP_HAS_DRIVER;
        }
        
        // 订单收款状态：已收款，并且实收和应收不等，按照实际更新收款状态
        $_userNeedToPay = Order_Helper::calOrderNeedToPay($this->orderInfo, false);
        if ($_userNeedToPay > 0 && $this->orderInfo['paid'] == Conf_Order::HAD_PAID)
        {
            $upData['paid'] = Conf_Order::PART_PAID;
            Order_Api::updateOrderModify($this->oid);
        }
        else if ($_userNeedToPay < 0 && $this->orderInfo['paid'] == Conf_Order::PART_PAID)
        {
            $upData['paid'] = Conf_Order::HAD_PAID;
            Order_Api::updateOrderModify($this->oid);
        }
        
        //确认时间&确认人
        $upData['sure_suid'] = $staff['suid'];
        $upData['sure_time'] = date('Y-m-d H:i:s');
        
        $this->hOrder->update($this->oid, $upData);
    }
           
    private function _setPrivilege4Sure()
    {
        $preferentialDao = new Order_Sale_Preferential();
        $preferentialInfo = $preferentialDao->getItem($this->oid);
        if(!empty($preferentialInfo))
        {
            $order_amount = $this->orderInfo['price'] - $this->orderInfo['privilege'] - $this->orderInfo['refund'] + $this->orderInfo['sale_privilege'];
            $orderMaxAmount = floor($order_amount * 10/100);
            if($orderMaxAmount < $this->orderInfo['sale_privilege'])
            {
                throw new Exception('优惠金额超过该发放人限额，限额为'.($orderMaxAmount/100).'元,请重新调整销售优惠后再确认订单!');
            }
        }

        $cc = new Coupon_Coupon();
        $cc->useCoupon($this->oid);
        $orderProducts = Order_Api::getOrderProducts($this->oid);
        $realOrderPorducts = Privilege_Api::getRealBuyProducts($orderProducts['products'], $this->oid);
        $activityProducts = Privilege_Api::getActivityProducts($this->oid);
        $promotionPrivlege = Privilege_2_Api::savePromotionPrivilege($this->orderInfo['cid'], $realOrderPorducts, $this->orderInfo, false, $activityProducts);
        //更新优惠
        $upData['privilege'] = $promotionPrivlege['total_privilege'];
        $upData['privilege_note'] = $promotionPrivlege['privilege_note'];
        $this->hOrder->update($this->oid, $upData);
    }
    
    /**
     * 订单收款状态：检测应收和实收，如果应收大于实收，设置成部分支付
     */
    private function _checkOrderPaidStatus()
    {
        $_userNeedToPay = Order_Helper::calOrderNeedToPay($this->orderInfo, false);
        if ($_userNeedToPay > 0 && $this->orderInfo['paid'] != Conf_Order::UN_PAID)
        {
            if ($this->orderInfo['paid'] == Conf_Order::HAD_PAID)
            {
                Order_Api::updateOrderInfo($this->oid, array('paid' => Conf_Order::PART_PAID));
            }
            Order_Api::updateOrderModify($this->oid, $_userNeedToPay);
        }
        else if ($_userNeedToPay < 0)
        {
            if ($this->orderInfo['paid'] == Conf_Order::PART_PAID)
            {
                Order_Api::updateOrderInfo($this->oid, array('paid' => Conf_Order::HAD_PAID));
            }

            Order_Api::updateOrderModify($this->oid, $_userNeedToPay);
        }
    }

    
    private function _showErrorMsg($errno, $errmsg='')
    {
        $errMsgs = array(
            1001 => '订单数据异常',
            1002 => '订单确认仓库id',
            
            2001 => '订单已经确认',
            2002 => '配送订单，需要填写小区信息，再确认',
            2003 => '订单商品为空，不能确认',
            2004 => '订单不能确认，如需确认，请先通知用户付款或销售组长担保',
            2005 => '确认失败：订单未支付，请先支付订单或找肖总/秦总审核确认',
            
            9999 => '对不起，操作失败！',
        );
        
        if (array_key_exists($errno, $errMsgs))
        {
            $this->response['errno'] = $errno;
            $this->response['errmsg'] = $errMsgs[$errno];
        }
        else
        {
            $this->response['errno'] = 9999;
            $this->response['errmsg'] = !empty($errmsg)? $errmsg: $errMsgs[9999];
        }
    }

    private function _updateDeliveryDate()
    {
        if ($this->orderInfo['delivery_type'] == Conf_Order::DELIVERY_QUICKLY && $this->orderInfo['paid'] == Conf_Order::UN_PAID)
        {
            $hour = date('G');
            $oldDeliveryDate = strtotime($this->orderInfo['delviery_date']);
            $now = time();
            if ($oldDeliveryDate - $now < 7200)
            {
                if ($hour >= 0 && $hour <= 7)
                {
                    $update['delivery_date'] = date('Y-m-d 9:00:00', time());
                    $update['delivery_date_end'] = date('Y-m-d 11:00:00', time());
                }
                else if ($hour >= 8 && $hour < 18)
                {
                    $startTime = time() + 2 * 3600;
                    $endTime = time() + 4 * 3600;
                    $update['delivery_date'] = date('Y-m-d H:00:00', $startTime);
                    $update['delivery_date_end'] = date('Y-m-d H:00:00', $endTime);
                }
                else
                {
                    $tomorrow = strtotime('tomorrow');
                    $update['delivery_date'] = date('Y-m-d 9:00:00', $tomorrow);
                    $update['delivery_date_end'] = date('Y-m-d 11:00:00', $tomorrow);
                }

                $this->hOrder->update($this->oid, $update);
            }
        }
    }
}