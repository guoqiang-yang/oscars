<?php

/**
 * 订单状态流转类.
 * 
 * @author guoqiang yang
 * @date 2017-08-01
 */

class Order_Step_Flow
{   
    private $oid;
    private $orderInfo;
    private $orderProduct;
    private $customerInfo;
    private $operator;
    private $nextStep;
    
    private $hOrder;
    private $hCustomer;
    
    function __construct($oid, $nextStep)
    {
        assert(!empty($oid) || !empty($nextStep));
        
        $this->oid = $oid;
        $this->nextStep = $nextStep;
        
        $this->hOrder = new Order_Order();
        $this->hCustomer = new Crm2_Customer();
        
        $this->orderInfo = $this->hOrder->get($this->oid, true);
        $this->orderProduct = $this->hOrder->getProductsOfOrder($this->oid);
        $this->customerInfo = $this->hCustomer->get($this->orderInfo['cid']);
    }
    
    /**
     * 设置操作员信息.
     */
    public function setOperator($staff)
    {
        $this->operator = $staff;
        
        $this->operator['suid'] = empty($this->operator['suid'])? $staff['fid']: $this->operator['suid'];
        $this->operator['_my_wids'] = !empty($this->operator['wids'])?
                            explode(',', $this->operator['wids']): array($this->operator['wid']);
    }
    
    /**
     * 客服确认.
     */
    public function sure()
    {
        $this->_commonCheckOrder();
        $this->_checkOrder4Sure();
        $isChgOrderProductsPrice = $this->_appendMoreProductInfo4Sure();    //附加订单商品信息
        $this->_occupiedStock4Sure();                                       //分配库存，并占用
        $this->_updateOrderProducts4Sure();                                 //更新订单商品信息

        $this->_setPrivilege4Sure();                                        //更新优惠
        $this->_updateOrder4Sure();                                         //更新订单信息

        if ($isChgOrderProductsPrice)                                       //更新订单金额
        {
            Order_Api::updateOrderTotalPrice($this->oid, $this->orderProduct);
        }

        Order_Api::updateOrderModify($this->oid);                           //更新财务流水

        $param['newStep'] = Conf_Order::$ORDER_STEPS[Conf_Order::ORDER_STEP_SURE];      //订单日志
        Admin_Api::addOrderActionLog($this->operator['suid'], $this->oid, Conf_Order_Action_Log::ACTION_CHANGE_STEP, $param);
    }
    
    public function hadDriver()
    {
        $param['newStep'] = Conf_Order::$ORDER_STEPS[Conf_Order::ORDER_STEP_HAS_DRIVER];
        
        Admin_Api::addOrderActionLog($this->operator['suid'], $this->oid, Conf_Order_Action_Log::ACTION_CHANGE_STEP, $param);
    }
    
    /**
     * 检测订单拣货.
     */
    public function checkPicked()
    {
        $this->_commonCheckOrder();
        $this->_isComplatePicked4CheckPicked();
        $this->_chkMarkVnumFlag4CheckPicked();
    }


    /**
     * 订单出库.
     */
    public function dispatch()
    {
        //Update Info
        $upOrderInfo['ship_time'] = date('Y-m-d H:i:s');
        $upOrderInfo['step'] = Conf_Order::ORDER_STEP_PICKED;
        $update['op_note'] = sprintf("ffee:%d,cfee:%d,prfee:%d", $this->orderInfo['freight'], $this->orderInfo['customer_carriage'], $this->orderInfo['privilege']);
        $this->_commonCheckOrder();
        $this->_checkOrder4Dispatch();
        $this->_updateStock4Dispatch();                      //更新库存
        $this->_orderFinance4Dispatch($upOrderInfo);
        $this->_orderMsg4Dispatch();
        
        $this->hOrder->update($this->oid, $upOrderInfo);

        //更新客户数据
        Order_Api::updateCustomerOrder($this->orderInfo['cid']);
        
        if (!Order_Helper::isFranchiseeOrder($this->orderInfo['wid']))
        {
            Cpoint_Api::updatePointWhenCustomerOrder($this->oid);
        }
        
        Admin_Api::addOrderActionLog($this->operator['suid'], $this->oid, Conf_Order_Action_Log::ACTION_CHANGE_STEP, 
                                        array('newStep' => Conf_Order::$ORDER_STEPS[Conf_Order::ORDER_STEP_PICKED]));
    }
    
    /**
     * 订单回单.
     */
    public function finished()
    {
        $upData['step'] = Conf_Order::ORDER_STEP_FINISHED;
        $upData['back_time'] = date('Y-m-d H:i:s');
        
        $this->_commonCheckOrder();
        $this->_autoTransAmount4Finished();
        $this->_chgOrderPayStatus($upData);
        
        $isComplatePaid = (isset($upData['paid'])&&$upData['paid']==Conf_Order::HAD_PAID)
                        || (!isset($upData['paid'])&&$this->orderInfo['paid']==Conf_Order::HAD_PAID)? true: false;
        if ($isComplatePaid)
        {
            Coupon_Api::sendPromotionCoupon($this->orderInfo['cid'], $this->oid);
            WeiXin_Message_Api::sendOrderFinishMessage($this->orderInfo['uid'], $this->orderInfo['oid']);
        }
        
        $this->_complateAftersaleOrder();
        $this->_orderMsg4Finished();
        
        $this->hOrder->update($this->oid, $upData);
        
        Admin_Api::addOrderActionLog($this->operator['suid'], $this->oid, Conf_Order_Action_Log::ACTION_CHANGE_STEP, 
                                        array('newStep' => Conf_Order::$ORDER_STEPS[Conf_Order::ORDER_STEP_FINISHED]));
    }

    
    ////////////////////////// Order Sure [step=2]///////////////////////////
    
    //订单确认：补充订单商品信息
    private function _appendMoreProductInfo4Sure()
    {
        // 补充没有skuid的订单商品信息；实时更新商品的售价
        $sp = new Shop_Product();
        $allPids = Tool_Array::getFields($this->orderProduct, 'pid');
        $productInfos = $sp->getBulk($allPids);
        
        // 商品活动价格
        $activityProductPrices = array();
//        $platform = $this->orderInfo['source']==Conf_Order::SOURCE_WEIXIN? Conf_Activity_Flash_Sale::PALTFORM_WECHAT:
//                    ($this->orderInfo['source']==Conf_Order::SOURCE_APP_IOS||$this->orderInfo['source']==Conf_Order::SOURCE_APP_ANDROID? 
//                        Conf_Activity_Flash_Sale::PALTFORM_APP: 0);
        $platform = Conf_Activity_Flash_Sale::PALTFORM_BOTH;
        if (!empty($this->orderInfo['city_id']) && !empty($platform))
        {
            $activityProductPrices = Shop_Api::getLowestPrice($this->orderInfo['city_id'], $platform);
        }
        
        $isChgOrderProductsPrice = false;
        foreach($this->orderProduct as &$_p)
        {
            $newPrice = $productInfos[$_p['pid']]['price'];
            if (array_key_exists($_p['pid'], $activityProductPrices))
            {
                $newPrice = intval($activityProductPrices[$_p['pid']]['sale_price']);
            }
            
            if ($_p['price'] != $newPrice)
            {
                $isChgOrderProductsPrice = true;
            }
            
            $_p['price'] = $newPrice;
            
            if (empty($_p['sid']))
            {
                $_p['sid'] = $productInfos[$_p['pid']]['sid'];
            }
        }
        unset($_p);
        
        // 补充商品的成本，仓库等属性
        $sids = Tool_Array::getFields($this->orderProduct, 'sid');
        $skuCosts = Shop_Cost_Api::getSimpleCost($this->orderInfo['wid'], $sids);
        foreach ($this->orderProduct as &$_p)
        {
            $_p['cost'] = $skuCosts[$_p['sid']];
            $_p['wid'] = $this->orderInfo['wid'];
        }
        
        return $isChgOrderProductsPrice;
    }
    
    //订单确认：商品占用
    private function _occupiedStock4Sure()
    {
        // 是否需要占用/变更库存
        if (!$this->_needChangeStock()) return;
        
        // 占用库存
        $wl = new Warehouse_Location();
        $ws = new Warehouse_Stock();
        $productWithLocAndVnum = Warehouse_Location_Api::distributeNumFromLocation($this->orderProduct, $this->orderInfo['wid'], 1, TRUE);
        
        foreach($this->orderProduct as &$_p)
        {
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
    
    //订单确认：更新订单商品信息
    private function _updateOrderProducts4Sure()
    {
        foreach ($this->orderProduct as $_p)
        {
            $opUpdata = array(
                'sid' => $_p['sid'],
                'wid' => $_p['wid'],
                'cost' => $_p['cost'],
                'location' => $_p['location'],
                'vnum' => $_p['vnum'],
                'picked' => $_p['picked'],
                'picked_time' => $_p['picked_time'],
            );
            if (isset($_p['price']))
            {
                $opUpdata['price'] = $_p['price'];
            }
            
            $this->hOrder->updateOrderProductInfo($this->oid, $_p['pid'], 0, $opUpdata);
        }
    }
    
    private function _updateOrder4Sure()
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
        
        if (Order_Helper::isFranchiseeOrder($this->orderInfo['wid'], $this->orderInfo['city_id']))
        {
            $upData['step'] = Conf_Order::ORDER_STEP_HAS_DRIVER;
        }
        
        // 订单收款状态：已收款，并且实收和应收不等，按照实际更新收款状态
        $this->_chgOrderPayStatus($upData);
        
        //确认时间&确认人
        $upData['sure_suid'] = $this->operator['suid'];
        $upData['sure_time'] = date('Y-m-d H:i:s');
        
        //尽快送达，更新配送时间
        $this->_updateDeliveryDate4Sure($upData);
        
        $this->hOrder->update($this->oid, $upData);
    }
    
    private function _updateDeliveryDate4Sure(&$update)
    {
        if ($this->orderInfo['delivery_type']!=Conf_Order::DELIVERY_QUICKLY || $this->orderInfo['paid']!=Conf_Order::UN_PAID) 
        {
            return;
        }
        
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
        }
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
    
    // 客服确认: 校验数据
    private function _checkOrder4Sure()
    {
        if ($this->orderInfo['step'] >= Conf_Order::ORDER_STEP_SURE)
        {
            throw new Exception ('订单已经确认');
        }
        if ($this->nextStep >= Conf_Order::ORDER_STEP_PICKED)
        {
            throw new Exception('GUN!!!');
        }
        if ($this->orderInfo['delivery_type']!=Conf_Order::DELIVERY_BY_YOURSELF && empty($this->orderInfo['community_id']))
        {
            throw new Exception('配送订单，需要填写小区信息，再确认');
        }
        if ($this->orderInfo['city_id'] == Conf_City::CHONGQING && $this->orderInfo['aftersale_type'] == 0 && $this->orderInfo['paid'] == Conf_Order::UN_PAID
            && $this->orderInfo['is_guaranteed'] == 2)
        {
            throw new Exception('订单不能确认，如需确认，请先通知用户付款或销售组长担保');
        }
        
        // 普采缺货不能确认
        if ($this->orderInfo['aftersale_type'] == 0)
        {
            Warehouse_Api::checkOrderProductsStockByOid($this->oid, $this->orderInfo['wid']);
        }
        
        // 需要领导确认的订单 - 现款后确认订单
        // 黑名单销售，可以确认：1 完全付款 2 首单客户&&应收<2000元 3 无欠款客户&&应收<2000元
        $this->_canSureSalesOrder();
    }
    
    /**
     * 是否可以确认某销售的订单.
     * 
     *  需要领导确认的订单 - 现款后确认订单
     *  黑名单销售，可以确认：1 完全付款 2 首单客户&&应收<2000元 3 无欠款客户&&应收<2000元
     */
    private function _canSureSalesOrder()
    {
        $_superSureSuid = array(1454, 1423, 1181, 1004, 1029);
        $_auditSureSuid = array(
            1073 => array(
                'name' => '王建伟、肖总',
                'sales' => array(1197, 1240),
            ),
            1426 => array(
                'name' => '奉铁钢、肖总',
                'sales' => array(1511),
            ),
            1118 => array(
                'name' => '黄行、肖总',
                'sales' => array(1261, 1378, 1107, 1434),
            ),
            1600 => array(
                'name' => '吕圆梦、肖总',
                'sales' => array(1163),
            ),
            1454 => array(
                'name' => '肖总',
                'sales' => array(
                    1039, 1041, 1089, 1320,
                ),
            ),
        );
        
        $cc = new Crm2_Customer();
        $customerInfo = $cc->get($this->orderInfo['cid']);
     
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
        
        $isSuperAuditor = in_array($this->operator['suid'], $_superSureSuid)? true: false;
        $isDirectAuditor = $canAuditSuid==$this->operator['suid']? true: false;

        $canSure = $isHadPaid || $firstOrder || $noArrear || $canAmountPay? true: false;
        if (in_array($this->orderInfo['saler_suid'], array(1039, 1041, 1089, 1320)) ) //特殊处理 from：肖亮东
        {
            $canSure = $isHadPaid || $canAmountPay ? true: false;
        }
        
        if ($isLimitedSales && !$isSuperAuditor && !$isDirectAuditor && !$canSure && !$isAftersaleOrder)
        {
            throw new Exception('确认失败：订单未支付，请先支付订单或找【'.$canAuditName.'】审核确认');
        }
    }
    
    ////////////////////// Check Order Picked [Step=5]///////////////////////////
    
    
    //是否完成拣货
    private function _isComplatePicked4CheckPicked()
    {
        //检测：是否拣货
        if (ENV != 'online') return;
        
        foreach ($this->orderProduct as $_p)
        {
            if (!self::_isScanCode4Picked($this->orderInfo['wid'], $_p['location'])
                || ($_p['num']-$_p['vnum']==$_p['picked'])) 
            {
                continue;
            }        

            $desc = '应出库数量：' . ($_p['num'] - $_p['vnum']) . '; 实际拣货数量：' . $_p['picked'];
            $errmsg = sprintf('货位:%s Skuid:%d 【%s】', $_p['location'], $_p['sid'], $desc);
            throw new Exception($errmsg);
        }
    }
    
    //普采缺货是否标记外采
    private function _chkMarkVnumFlag4CheckPicked()
    {
        // 检测：标记外采逻辑
        $sp = new Shop_Product;
        $sids = Tool_Array::getFields($this->orderProduct, 'sid');
        $cityId = $this->orderInfo['city_id'];
        $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE | Conf_Product::PRODUCT_STATUS_OFFLINE;
        $productInfos = Tool_Array::list2Map($sp->getBySku($sids, $cityId, $statusTag), 'pid');

        foreach ($this->orderProduct as $_p)
        {
            // 普采商品，缺货，必须标记才能出库！
            if ( $_p['vnum'] > 0 
                && $productInfos[$_p['pid']]['buy_type']==Conf_Product::BUY_TYPE_COMMON 
                && $_p['vnum_deal_type']!=Conf_Warehouse::ORDER_VNUM_FLAG_LACK )
            {
                throw new Exception('普采商品，缺货，需要标记【外采】，在出库');
            }
        }
    }
    
    //是否需要扫码拣货
    private static function _isScanCode4Picked($wid, $loc)
    {
        if (!Order_Helper::isFranchiseeOrder($wid)) return false;
        
        $widNoScan = array(
            Conf_Warehouse::WID_101, Conf_Warehouse::WID_LF1,
            Conf_Warehouse::WID_TJ2, Conf_Warehouse::WID_BJ_WJ1,
            Conf_Warehouse::WID_LF_COOP1,
        );
        $areaNoScan = array(
            Conf_Warehouse::WID_4 => array('A', 'B'),
            Conf_Warehouse::WID_TJ1 => array('A', 'B'),
            Conf_Warehouse::WID_8 => array('W'),
        );
        $specNoScan4Wid = array(
            Conf_Warehouse::WID_8 => array('A-55-55-55', 'A-99-99-99'),
        );
        
        $area = strtoupper(substr($loc, 0, 1));
        
        $isWidNoScan = in_array($wid, $widNoScan)? true: false;
        $isAreaNoScan = array_key_exists($wid, $areaNoScan) && in_array($area, $areaNoScan[$wid])? true: false;
        $isSpecNoScan = array_key_exists($wid, $specNoScan4Wid) && in_array($loc, $specNoScan4Wid[$wid])? true: false;
        
        return $isWidNoScan || $isAreaNoScan || $isSpecNoScan? false: true;
    }
    
    ////////////////////// Order Dispatch [Step=5]///////////////////////////
    
    //订单出库：检测
    private function _checkOrder4Dispatch()
    {
        if (!in_array($this->orderInfo['wid'], $this->operator['_my_wids']))
        {
            throw new Exception('仓库ID 和 操作人ID 不一致，请核对！');
        }
        
        //锁库判断
        $lockedRet = Conf_Warehouse::isLockedWarehouse($this->orderInfo['wid']);
        if ($lockedRet['st'])
        {
            throw new Exception($lockedRet['msg']);
        }
        
    }
    
    //订单出库：更新库存
    private function _updateStock4Dispatch()
    {
        if (! $this->_needChangeStock()) return;
        
        $ws = new Warehouse_Stock();
        $wsh = new Warehouse_Stock_History();
        $wl = new Warehouse_Location();

        //获取原库存
        $sids = Tool_Array::getFields($this->orderProduct, 'sid');
        $oldStocks = Tool_Array::list2Map($ws->getBulk($this->orderInfo['wid'], $sids), 'sid');
        
        // 出库操作
        Warehouse_Location_Api::parseLocationAndNum($this->orderProduct);
        $fifoCostProducts = array();

        foreach ($this->orderProduct as $pinfo)
        {
            $sid = $pinfo['sid'];
            $num4UpStock = $pinfo['num'] - $pinfo['vnum'];
            
            if ($num4UpStock <= 0) continue;

            //[货位库存] 消减货位库存/总库存，释放货位占用/总占用
            if (isset($pinfo['_location']) && !empty($pinfo['_location']))
            {
                foreach ($pinfo['_location'] as $loc)
                {
                    $wlChgdata = array(
                        'num' => (0 - $loc['num']),
                        'occupied' => (0 - $loc['num'])
                    );
                    $wl->update($sid, $loc['loc'], $this->orderInfo['wid'], array(), $wlChgdata);
                }
            }

            //[总库存] 消减总库存，释放货总占用，记录出入库历史
            $wsChgdata = array(
                'num' => (0 - $num4UpStock),
                'occupied' => (0 - $num4UpStock),
            );
            $ws->save($this->orderInfo['wid'], $sid, array(), $wsChgdata);

            // 保存历史记录
            $history = array(
                'old_num' => isset($oldStocks[$sid]) ? $oldStocks[$sid]['num'] : 0,
                'num' => 0 - abs($num4UpStock),
                'iid' => $this->oid,
                'suid' => $this->operator['suid'],
                'type' => Conf_Warehouse::STOCK_HISTORY_OUT,
            );
            $wsh->add($this->orderInfo['wid'], $sid, $history);

            $fifoCostProducts[] = array('sid'=>$sid, 'num'=>$num4UpStock);
        }

        // 使用COST-FIFO队列刷新订单成本
        $oo = new Order_Order();
        $refreshCostDatas = Shop_Cost_Api::getCostsWithSkuAndNums($this->orderInfo['wid'], $fifoCostProducts);

        $billInfo = array('out_id'=>$this->oid, 'out_type'=>Conf_Warehouse::STOCK_HISTORY_OUT);
        foreach($refreshCostDatas as $_sid => $fifoCosts)
        {
            if (empty($fifoCosts['_cost_fifo'])) continue;

            $oo->updateOrderProductBySid($this->oid, 0, $_sid, array('cost'=>$fifoCosts['cost']));
            Shop_Cost_Api::dequeue4FifoCost($_sid, $this->orderInfo['wid'], $billInfo, $fifoCosts['_cost_fifo']);
        }

        return;
    }
    
    private function _orderFinance4Dispatch(&$upOrderInfo)
    {
        $cid = $this->orderInfo['cid'];
        $uid = $this->orderInfo['uid'];
        $wid = $this->orderInfo['wid'];
        $need2Pay = Order_Helper::calOrderPayableTotalPrice($this->orderInfo);
        $type = Conf_Money_In::ORDER_PAIED;
        $suid = $this->operator['suid'];
        
        //应付明细
        Finance_Api::addMoneyInHistory($cid, $type, $need2Pay, $suid, $this->oid, $wid, '', 0, $uid, $this->oid);
        
        // 自动余额收款
        $isExceptCid = in_array($cid, Conf_Order::$AUTO_PAID_EXCEPT_CUSTOMERS);
        $isFranchiseeOrder =  Order_Helper::isFranchiseeOrder($wid, $this->orderInfo['city_id']);
        $isUnPaid = $this->orderInfo['paid']==Conf_Order::UN_PAID? true: false;
        $canComplatePaid = $need2Pay>0&&$this->customerInfo['account_amount']>=$need2Pay? true: false;
        
        if ($isExceptCid || $isFranchiseeOrder || !$isUnPaid || !$canComplatePaid) return;
        
        $upOrderInfo['payment_type'] = Conf_Base::PT_BALANCE;
        $upOrderInfo['paid'] = Conf_Order::HAD_PAID;
        $upOrderInfo['real_amount'] = $need2Pay;
        
        // 写客户应收明细
        $miType = Conf_Money_In::FINANCE_INCOME;
        $note = '自动收款：【余额】';
        $payType = Conf_Base::PT_BALANCE;
        Finance_Api::addMoneyInHistory($cid, $miType, $need2Pay, Conf_Admin::ADMINOR_AUTO, $this->oid, $wid, $note, $payType, $uid, $this->oid);

        // 写客户账户余额
        $saveData = array(
            'type' => Conf_Finance::CRM_AMOUNT_TYPE_PAID,
            'price' => 0 - abs($need2Pay),
            'objid' => $this->oid,
            'payment_type' => $payType,
            'note' => '余额支付单：' . $this->oid,
            'suid' => Conf_Admin::ADMINOR_AUTO,
            'uid' => $uid,
            'oid' => $this->oid,
        );
        Finance_Api::addCustomerAmountHistory($cid, $saveData);
        
        //订单操作日志 {needToPay}，实收：{realAmount}，抹零：{change}，坏账：{$badLoans}
        $param = array(
            'needToPay' => $need2Pay / 100,
            'realAmount' => $need2Pay / 100,
            'change' => 0,
            'badLoans' => 0,
            'type' => '余额自动付款',
        );
        Admin_Api::addOrderActionLog(Conf_Admin::ADMINOR_AUTO, $this->oid, Conf_Order_Action_Log::ACTION_RECEIPT, $param);

    }
    
    private function _orderMsg4Dispatch()
    {
        $coopworker = Logistics_Coopworker_Api::getOrderOfWorkers($this->orderInfo['oid'], Conf_Base::COOPWORKER_DRIVER);
        
        if (!empty($coopworker))
        {
            $orderDriver = array_shift($coopworker);
            $driver = Logistics_Api::getDriver($orderDriver['cuid']);
            
            //消息
            $msgInfo = array(
                'ocode' => date('Ymd', strtotime($this->orderInfo['ctime'])) . '-' . $this->orderInfo['oid'],
                'name' => $driver['name'],
                'mobile' => $driver['mobile'],
            );
            Crm2_User_Msg_Api::addMsg($this->orderInfo['uid'], $this->orderInfo['cid'], Conf_User_Msg::$MSG_ORDER_SEND, $msgInfo);
        }

        WeiXin_Message_Api::sendOrderSetoutMessage($this->orderInfo['uid'], $this->orderInfo['oid']);

        $userInfo = Crm2_Api::getUserInfo($this->orderInfo['uid'], false, false);
        
        Data_Sms::sendNew($userInfo['user']['mobile'], Conf_Sms::DELIVERY_SUCC_KEY, array());
        
        
//        $driver = Logistics_Auth_Api::checkMobile($driver['mobile'], Conf_Base::COOPWORKER_DRIVER);
//
//        //判断该排线所有的订单是否都已经出库
//        $can_set_out = 1;
//        $ret = Logistics_Api::getDriverQueue($driver['uid']);
//        $step = $ret['step'];
//        if (!empty($ret['line_id']))
//        {
//            $lineInfo = Logistics_Api::getLineDetail($ret['line_id']);
//        }
//
//        if (!empty($lineInfo['oids']) && $step >= Conf_Driver::STEP_ACCEPT)
//        {
//            $oids = explode(',', $lineInfo['oids']);
//            $orders = Order_Api::getListByPk($oids);
//            foreach ($oids as $oid)
//            {
//                if ($orders[$oid]['step'] < Conf_Order::ORDER_STEP_PICKED)
//                {
//                    $can_set_out = 2;
//                    break;
//                }
//            }
//        }
//        //发推送消息
//        if (!empty($driver['user']['regid']) && $can_set_out === 1)
//        {
//            Push_Xiaomi_Api::pushToUserMessage($driver['user']['regid'], Conf_Driver::$MSG_PUSH[Conf_Driver::MSG_SEND]['title'], Conf_Driver::$MSG_PUSH[Conf_Driver::MSG_SEND]['desc'], Push_Xiaomi_Api::HAOCAI_DRIVER, 2);
//        }
        
    }
    
    /////////////////////////// Order Finished ///////////////////////////////
    
    private function _autoTransAmount4Finished()
    {
        $accountPayable = Order_Helper::calOrderPayableTotalPrice($this->orderInfo);
        
        if($this->orderInfo['real_amount'] > $accountPayable && !Order_Helper::isFranchiseeOrder($this->orderInfo['wid'], $this->orderInfo['city_id']))
        {
            $transAmount = $accountPayable - $this->orderInfo['real_amount'];
            
            //余额从客户账务流水 转移到 客户余额流水中
            $note = '订单' . $this->oid . '回单，余额自动转移';
            Finance_Api::addMoneyInHistory($this->orderInfo['cid'], Conf_Money_In::CUSTOMER_AMOUNT_TRANSFER, 
                            $transAmount, Conf_Admin::ADMINOR_AUTO, $this->oid, $this->orderInfo['wid'], 
                            $note, Conf_Base::PT_BALANCE, $this->orderInfo['uid'], $this->oid);

            //插入客户账务余额流水
            $saveData = array(
                'type' => Conf_Finance::CRM_AMOUNT_TRANSFER,
                'price' => $transAmount,
                'payment_type' => Conf_Base::PT_BALANCE,
                'note' => $note,
                'objid' => $this->oid,
                'oid' => $this->oid,
                'suid' => Conf_Admin::ADMINOR_AUTO,
            );

            Finance_Api::addCustomerAmountHistory($this->orderInfo['cid'], $saveData);
        }
    }
    
    // 判断售后单完成情况
    private function _complateAftersaleOrder()
    {
        //换货单
        if ($this->orderInfo['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_EXCHANGED)
        {
            $exchangedInfo = Exchanged_Api::getExchanged($this->orderInfo['aftersale_id']);
            
            //判断对应的退贷单是否已完成，如果完成换货单状态自动变为已完成
            $refundInfo = Order_Api::getRefund($exchangedInfo['info']['refund_id']);
            if (intval($refundInfo['info']['step']) >= Conf_Refund::REFUND_STEP_IN_STOCK)
            {
                Exchanged_Api::updateExchanged($exchangedInfo['info']['eid'], array('step' => Conf_Exchanged::EXCHANGED_STEP_FINISHED));
                Admin_Api::addOrderActionLog($this->operator['suid'], $exchangedInfo['info']['oid'], 
                        Conf_Order_Action_Log::ACTION_FINISHED_EXCHANGED_ORDER, array('eid' => $exchangedInfo['info']['eid']));
            }
        }
        //补漏单
        else if ($this->orderInfo['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_TRAPS)
        {
            $trapsInfo = Traps_Api::getTraps($this->orderInfo['aftersale_id']);
            
            Traps_Api::updateTraps($this->orderInfo['aftersale_id'], array('step' => Conf_Traps::TRAPS_STEP_FINISHED));
            
            Admin_Api::addOrderActionLog($this->operator['suid'], $trapsInfo['info']['oid'], 
                    Conf_Order_Action_Log::ACTION_FINISHED_TRAPS_ORDER, array('tid' => $trapsInfo['info']['tid']));
        }
    }
    
    private function _orderMsg4Finished()
    {
        $res = Logistics_Coopworker_Api::getOrderOfWorkers($this->oid, Conf_Base::COOPWORKER_DRIVER);
        if (!empty($res))
        {
            foreach ($res as $driver)
            {
                $openid = WeiXin_Coopworker_Api::getCoopworkerOpenid($driver['cuid'], 1);
                if (!empty($openid))
                {
                    WeiXin_Coopworker_Api::sendBackNoticeMessage($openid, $this->oid, $driver['price']);
                }
            }
        }

        //消息
        $msgInfo = array(
            'ocode' => date('Ymd', strtotime($this->orderInfo['ctime'])) . '-' . $this->orderInfo['oid'],
        );
        Crm2_User_Msg_Api::addMsg($this->orderInfo['uid'], $this->orderInfo['cid'], Conf_User_Msg::$MSG_ORDER_FINISH, $msgInfo);

        Logistics_Coopworker_Api::finishOrderCoopworker($this->oid);
    }


    ///////////////////////////////// Common ///////////////////////////////
    
    // 通用检测
    private function _commonCheckOrder()
    {
        if (empty($this->orderInfo) || empty($this->orderProduct)
            || $this->orderInfo['status']!=Conf_Base::STATUS_NORMAL )
        {
            throw new Exception('订单数据异常, 请刷新重试');
        }
        
        if (empty($this->orderInfo['wid']))
        {
            throw new Exception('请编辑订单的仓库ID');
        }
        
        if ($this->nextStep <= $this->orderInfo['step'] || $this->nextStep <= Conf_Order::ORDER_STEP_NEW)
        {
            throw new Exception('订单状态异常， 请联系管理员');
        }
        
        //检查用户是否能操作这个修改
        if (Order_Helper::isFranchiseeOrder($this->orderInfo['wid'], $this->orderInfo['city_id']))
        {
            $calNextStep = Order_Helper::calOrderNextStep4Franchisee($this->orderInfo);
        }
        else
        {
            $calNextStep = Order_Helper::calOrderNextStep($this->orderInfo);
        }
        
        if ($calNextStep != $this->nextStep)
        {
            throw new Exception('订单流转状态异常，请联系技术人员');
        }
        
        //是否可以处理该订单：加盟商只能处理自己的订单
        Order_Helper::canDealOrder($this->orderInfo, $this->operator);
    }
    
    //是否需要变更/更新库存
    private function _needChangeStock()
    {
        $isChgStock = true;
        
        //判断是否换货单生成的补单,需不需要占用库存
        if ($this->orderInfo['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_EXCHANGED)
        {
            $exchangedInfo = Exchanged_Api::getExchanged($this->orderInfo['aftersale_id']);
            
            if ($exchangedInfo['info']['need_storage'] == 0)
            {
                $isChgStock = false;
            }
        }
        //判断是否补漏单生成的补单，需不需要占用库存
        else if ($this->orderInfo['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_TRAPS)
        {
            $trapsInfo = Traps_Api::getTraps($this->orderInfo['aftersale_id']);
            
            if ($trapsInfo['info']['need_storage'] == 0)
            {
                $isChgStock = FALSE;
            }
        }
        
        return $isChgStock;
    }
    
    private function _chgOrderPayStatus(&$upData)
    {
        $_userNeedToPay = Order_Helper::calOrderNeedToPay($this->orderInfo, false);
        
        if ($_userNeedToPay > 0 && $this->orderInfo['paid'] == Conf_Order::HAD_PAID)
        {
            $upData['paid'] = Conf_Order::PART_PAID;
        }
        else if ($_userNeedToPay <= 0 && $this->orderInfo['paid'] == Conf_Order::PART_PAID)
        {
            $upData['paid'] = Conf_Order::HAD_PAID;
        }
    }
    
}