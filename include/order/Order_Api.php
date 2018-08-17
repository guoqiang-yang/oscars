<?php

/**
 * 订单相关接口
 */
class Order_Api extends Base_Api
{
    const MARK_FOR_FINANCE = 'mark4finance';

    /**
     * 获取微信下单时的可用送货时间和列表
     * @return array
     */
    public static function getDeliveryDateTime($cityId = 0)
    {
        $deliveryDate = array();
        $cityInfo = City_Api::getCity();
        $cityId == 0 && $cityId = $cityInfo['city_id'];

        $lastHour = 18;
        if ($cityId == Conf_City::CHONGQING)
        {
            $lastHour = 16;
        }

        //配送日期
        $today = strtotime('today');
        $i = 0;
        if (date('G') >= $lastHour)
        {
            $i = 1;
        }
        for (; $i <= 3; $i++)
        {
            $date = date('Y-m-d', $today + $i * 86400);
            $deliveryDate[] = $date;
        }
        $deliveryTime = Conf_Order::$DELIVERY_TIME_NEW;

        return array(
            'date' => $deliveryDate,
            'time' => $deliveryTime
        );
    }

    public static function getDeliveryTime4Admin()
    {
        return Conf_Order::$DELIVERY_TIME_ADMIN;
    }

    /**
     * 获取订单商品
     *
     * @param $oid
     *
     * @return array
     */
    public static function getOrderProducts($oid)
    {
        $orderProducts = array();
        $refundProducts = array();

        $oo = new Order_Order();
        $order = $oo->get($oid);
        $products = $oo->getProductsOfOrder($oid, TRUE, $order['status']);

        if (!empty($products))
        {
            $pids = Tool_Array::getFields($products, 'pid');
            $productInfos = Shop_Api::getProductInfos($pids, Conf_Activity_Flash_Sale::PALTFORM_BOTH, TRUE);

            foreach ($products as &$product)
            {
                if ($product['num'] <= 0)
                {
                    continue;
                }
                $price = false;
                if ($order['has_duty'] == Conf_Base::HAS_DUTY)
                {
//                    $duty = Conf_Base::DUTY;
//                    if (date('Y-m-d H:i:s') >= Conf_Base::NEW_DUTY_START)
//                    {
//                        $duty = Conf_Base::DUTY_20161129;
//                    }
                    $duty = Conf_Base::getDuty($order['cid']);
                    $price = $product['price'];
                    /* 商品含税价与订单商品总价计算方式保持一致 */
                    $product['price'] = self::getProductDutyPrice($product['price'], $duty);
                    $product['ori_price'] = self::getProductDutyPrice($product['ori_price'], $duty);
                }
                if($product['ori_price'] == 0)
                {
                    $product['ori_price'] = $product['price'];
                }

                $pid = $product['pid'];
                if ($product['rid'] == 0)   // 订单商品
                {
                    $product = array_merge($product, $productInfos[$pid]);
                    $orderProducts[$pid] = $product;
                }
                else    //退款单商品
                {
                    $product = array_merge($product, $productInfos[$pid]);
                    if($price)
                    {
                        $product['price'] = $price;
                    }
                    $refundProducts[$product['rid'] . '#' . $pid] = $product;
                }
            }
        }

        return array(
            'products' => $orderProducts,
            'refund_products' => $refundProducts
        );
    }

    /**
     * 获取订单异常商品列表.
     *  - 已拣货 并删除             type:del
     *  - 已拣货 但是需要重新拣货     type:re_picked
     *  - 部分拣货：                type:part_picked
     *
     */
    public static function getOrderAbnormalProducts($oid)
    {
        $oo = new Order_Order();
        $products = $oo->getProductsOfOrder($oid, FALSE, Conf_Base::STATUS_ALL);

        $abnormalProducts = array();

        if (empty($products))
            return $abnormalProducts;

        //        $pids = Tool_Array::getFields($products, 'pid');
        //        $productInfos = Shop_Api::getProductInfos($pids, Conf_Activity_Flash_Sale::PALTFORM_BOTH, true);
        $sids = Tool_Array::getFields($products, 'sid');
        $ss = new Shop_Sku();
        $skuInfos = $ss->getBulk($sids);

        Warehouse_Location_Api::parseLocationAndNum($products);

        foreach ($products as $pinfo)
        {
            $hadPicked = $pinfo['picked_time'] != '0000-00-00 00:00:00' ? TRUE : FALSE; //是否被拣过货
            $isPickedDel = $hadPicked && $pinfo['status'] != Conf_Base::STATUS_NORMAL ? TRUE : FALSE;
            $isRePicked = $hadPicked && $pinfo['picked'] == 0 && $pinfo['status'] == Conf_Base::STATUS_NORMAL ? TRUE : FALSE;
            $isPartPicked = $hadPicked && $pinfo['picked'] < ($pinfo['num'] - $pinfo['vnum']) && $pinfo['status'] == Conf_Base::STATUS_NORMAL ? TRUE : FALSE;

            $pinfo['sku_info'] = $skuInfos[$pinfo['sid']];
            if ($isPickedDel)
            {
                $abnormalProducts['del'][] = $pinfo;
            }
            else if ($isRePicked)
            {
                $abnormalProducts['re_picked'][] = $pinfo;
            }
            else if ($isPartPicked)
            {
                $abnormalProducts['part_picked'][] = $pinfo;
            }
        }

        return $abnormalProducts;
    }

    /**
     * 根据oids批量获取pids（包括已取消的订单）
     *
     * @param $oids
     *
     * @return array
     */
    public static function getPidsByOids($oids)
    {
        assert(!empty($oids));
        $oo = new Order_Order();

        $ret_normal = $oo->getPidsbyOids($oids, FALSE, Conf_Base::STATUS_NORMAL);
        $ret_cancel = $oo->getPidsbyOids($oids, FALSE, Conf_Base::STATUS_CANCEL);

        return array_merge($ret_normal, $ret_cancel);
    }

    /**
     * 向订单中添加商品
     *
     * @param       $oid
     * @param array $products
     *
     * @return bool
     */
    public static function addProducts($oid, array $products)
    {
        assert(!empty($oid));
        assert(!empty($products));

        $oo = new Order_Order();
        if (Conf_Base::switchForManagingMode())
        {
            $sp = new Shop_Product();
            $pids = Tool_Array::getFields($products, 'pid');
            $productInfo = $sp->getBulk($pids);
        }

        foreach ($products as &$product)
        {
            if (Conf_Base::switchForManagingMode())
            {
                $product['managing_mode'] = $productInfo[$product['pid']]['managing_mode'];
            }
            
            $_city = isset($product['city_id'])? $product['city_id']: 0;
            $oo->addOrderProduct($oid, $product, $_city);
        }
        $price = self::_updateOrderTotalPrice($oid);

        return $price;
    }

    /**
     * 向订单中添加商品
     *
     * @param $oid
     * @param $pid
     * @param $num
     *
     * @return bool
     */
    public static function updateOrderProductNum($oid, $pid, $num)
    {
        assert(!empty($oid));
        assert(!empty($pid));

        $oo = new Order_Order();
        if ($num > 0)
        {
            $oo->updateOrderProductNum($oid, $pid, $num);
        }
        else
        {
            $oo->deleteOrderProduct($oid, $pid);
        }

        $price = self::_updateOrderTotalPrice($oid);

        return $price;
    }

    public static function updateOrderProductPickedNum($oid, $pid, $num)
    {
        assert(!empty($oid));
        assert(!empty($pid));

        $oo = new Order_Order();
        $oo->updateOrderProductPickedNum($oid, $pid, $num);
    }

    /**
     * 从订单中删除某个商品
     *
     * @param $oid
     * @param $pid
     *
     * @return bool
     */
    public static function deleteProduct($oid, $pid)
    {
        $oo = new Order_Order();
        $oo->deleteOrderProduct($oid, $pid);
        $price = self::_updateOrderTotalPrice($oid);

        return $price;
    }

    /**
     * 获取订单基本信息，财务信息
     *
     * @param $oid
     *
     * @return array
     */
    public static function getOrderInfo($oid, $isMaster = FALSE)
    {
        //获取订单信息
        $oo = new Order_Order();
        $info = $oo->get($oid, $isMaster);
        if (!empty($info))
        {
            Order_Helper::formatOrder($info); //格式化订单信息 日期, 状态
        }

        return $info;
    }

    /**
     * 根据条件获取订单列表
     *
     * @param array $conf
     * @param array $order
     * @param int   $start
     * @param int   $num
     *
     * @return array
     */
    public static function getOrderList(array $conf, $order = array(), $start = 0, $num = 20, $suser = array(), $fields = array('*'))
    {
        $oo = new Order_Order();
        $total = 0;

        $list = $oo->getOrderListByConf($conf, $total, $order, $start, $num, $fields, $suser);
        $hasMore = $total > $start + $num;

        //客户cid转成客户信息customer
        $cc = new Crm2_Customer();
        $cc->appendInfos($list);

        //把录单人suid转换成名称信息
        $as = new Admin_Staff();
        $as->appendSuers($list);
        $as->appendSuers($list, 'sure_suid', '', TRUE);    //客服确认人
        $as->appendSuers($list, 'saler_suid', '', TRUE);    //销售

        //格式化订单信息 日期, 状态
        Order_Helper::formatOrders($list);

//        $statsticsWhere = sprintf('status=0 and step>=%d and ctime>="%s 00:00:00"', Conf_Order::ORDER_STEP_SURE, date('Y-m-d'));
//        $field = 'price-privilege-refund';
//        $sum = $oo->getSumByWhere($statsticsWhere, $field);
//
//        //查询今日订单数量
//        $orderNum = $oo->getTotalByWhere($statsticsWhere);
//        //查询今日补单数量
//        $supplementNum = $oo->getTotalByWhere('source_oid>0 and ' . $statsticsWhere);

        return array(
            'list' => $list,
            'price_total' => 0,
            'order_num' => $orderNum,
            'sum' => 0,
            'total' => $total,
            'supplement' => 0,
            'has_more' => $hasMore,
        );
    }

    /**
     * 根据主键获取订单信息.
     *
     * @array $oids
     * @return array
     */
    public static function getListByPk($ids, $field = array('*'), $orderby = array('oid', 'desc'))
    {
        $oo = new Order_Order();
        $orders = $oo->getBulk($ids, $field, $orderby);

        return $orders;
    }

    /**
     * 取订单的商品数据.
     *
     * @param $oid
     * @param $status
     *
     * @return array
     */
    public static function getOrderProduct($oid, $status = Conf_Base::STATUS_NORMAL)
    {
        $oo = new Order_Order();
        $orderProducts = $oo->getProductsOfOrder($oid, TRUE, $status);

        $orderProductList = array(
            'order' => array(),
            'refund' => array(),
        );
        foreach ($orderProducts as $product)
        {
            $rid = $product['rid'];
            $pid = $product['pid'];
            if (empty($rid))
            {
                $orderProductList['order'][$pid] = $product;
            }
            else    //退货商品
            {
                $orderProductList['refund'][$rid . '#' . $pid] = $product;
            }
        }

        return $orderProductList;
    }

    public static function getCustomerOrderByWhere($where, $start = 0, $num = 20)
    {
        $oo = new Order_Order();

        $total = 0;
        $list = $oo->getListRawWhere($where, $total, array(
            'oid',
            'desc'
        ), $start, $num);
        Order_Helper::formatOrders($list); //格式化订单信息 日期, 状态
        $hasMore = $total > $start + $num;

        return array(
            'list' => $list,
            'total' => $total,
            'has_more' => $hasMore
        );
    }

    /**
     * 获取用户的订单列表
     *
     * @param     $cid
     * @param     $uid
     * @param     $type
     * @param int $start
     * @param int $num
     *
     * @return array
     */
    public static function getCustomerOrderList($cid, $uid, $type, $start = 0, $num = 20)
    {
        $where = sprintf('cid=%d', $cid);
        if (!empty($uid))
        {
            $where .= sprintf(' and uid=%d', $uid);
        }
        $oids = Traps_Api::getAftersaleOidsNeedPay($cid);
        if (!empty($oids))
        {
            $where .= sprintf(' and oid not in(%s)', implode(',', $oids));
        }

        $needToPayNum = $needToSendNum = $needToReceiveNum = 0;

        if ($type == 1)
        {   //待支付
            $where .= sprintf(' AND paid!=%d AND status=%d AND step>0', Conf_Order::HAD_PAID, Conf_Base::STATUS_NORMAL);
        }
        else if ($type == 2)
        {    //待发货
            $where .= sprintf(' AND step>0 AND step<%d AND status=%d', Conf_Order::ORDER_STEP_PICKED, Conf_Base::STATUS_NORMAL);
        }
        else if ($type == 3)
        {    //待收货
            $where .= sprintf(' AND step>=%d AND step<%d AND status=%d', Conf_Order::ORDER_STEP_PICKED, Conf_Order::ORDER_STEP_FINISHED, Conf_Base::STATUS_NORMAL);
        }
        else if ($type == 4)
        {    //已完成
            $where .= sprintf(' AND step=%d AND status=%d', Conf_Order::ORDER_STEP_FINISHED, Conf_Base::STATUS_NORMAL);
        }
        else if ($type == 5)
        {    //已取消
            $where .= sprintf(' AND step>0 AND status=%d', Conf_Base::STATUS_CANCEL);
        }
        else if ($type == 6)
        {    //全部(包含已取消)
            $where .= sprintf(' AND step>0 AND status!=%d', Conf_Base::STATUS_DELETED);
        }
        else
        {    //全部(不包含已取消)
            $where .= sprintf(' AND step>0 AND status=%d', Conf_Base::STATUS_NORMAL);
        }

        $oo = new Order_Order();
        $total = 0;
        if ($type > 0 && $type < 6)
        {
            $list = $oo->getListRawWhere($where, $total, array(
                'oid',
                'desc'
            ), $start, $num);
        }
        else
        {
            $list = $oo->getListRawWhere($where, $total, array(
                'oid',
                'desc'
            ), 0, 0);
            $all = $unpaid = $partpaid = $paid = $canceled = array();
            foreach ($list as $item)
            {
                if ($item['status'] == Conf_Base::STATUS_NORMAL)
                {
                    if ($item['paid'] == Conf_Order::HAD_PAID)
                    {
                        $paid[] = $item;
                    }
                    else if ($item['paid'] == Conf_Order::PART_PAID)
                    {
                        $partpaid[] = $item;
                    }
                    else
                    {
                        $unpaid[] = $item;
                    }
                }
                else
                {
                    $canceled[] = $item;
                }
            }

            $all = array_merge($unpaid, $partpaid, $paid, $canceled);

            foreach ($list as $order)
            {
                if ($order['paid'] != Conf_Order::HAD_PAID && $order['status'] == Conf_Base::STATUS_NORMAL && $order['step'] > 0)
                {
                    $needToPayNum++;
                }
                if ($order['step'] > 0 && $order['step'] < Conf_Order::ORDER_STEP_PICKED && $order['status'] == Conf_Base::STATUS_NORMAL)
                {    //待发货
                    $needToSendNum++;
                }
                if ($order['step'] >= Conf_Order::ORDER_STEP_PICKED && $order['step'] < Conf_Order::ORDER_STEP_FINISHED && $order['status'] == Conf_Base::STATUS_NORMAL)
                {    //待收货
                    $needToReceiveNum++;
                }
            }

            $list = array_slice($all, $start, $num);
            $zitiAddress = Conf_Warehouse::getZitiAddress();
            foreach ($list as $k => $item)
            {
                if ($item['delivery_type'] == Conf_Order::DELIVERY_BY_YOURSELF && $item['city_id'] == Conf_City::CHONGQING)
                {
                    $list[$k]['address'] = $zitiAddress[$item['wid']]['address'];
                }
            }
        }

        if (empty($list))
        {
            return array(
                'list' => $list,
                'total' => $total,
                'has_more' => FALSE,
                'need_to_pay' => $needToPayNum,
                'need_to_send' => $needToSendNum,
                'need_to_receive' => $needToReceiveNum,
            );
        }

        $oids = Tool_Array::getFields($list, 'oid');
        $commentList = Comment_Api::getList(array('oid' => $oids), 0, 0);
        $commentListMap = array();
        if (!empty($commentList['list']))
        {
            $commentListMap = Tool_Array::list2Map($commentList['list'], 'oid');
        }
        $today = strtotime('today');

        foreach ($list as $key => $order)
        {
            //订单详情页底部的两个个按钮显示
            //按钮1：不显示（0），取消订单（1），申请退货（2）
            //按钮2：不显示（0），去支付（1），再次购买（2），订单评价（3），查看物流（4）
            $buttonData = Order_Helper::getButtonsByOrder($order, $commentListMap[$order['oid']], FALSE);
            $list[$key]['button_1'] = $buttonData['button_1'];
            $list[$key]['button_2'] = $buttonData['button_2'];

            if ($order['ship_time'] > 0)
            {
                list($day, $time) = explode(' ', $order['ship_time']);
                if ($today - strtotime($day) > 14 * 86400)
                {
                    $list[$key]['can_refund'] = FALSE;
                }
                else
                {
                    $list[$key]['can_refund'] = TRUE;
                }
            }
            else
            {
                $list[$key]['can_refund'] = TRUE;
            }
        }

        Order_Helper::formatOrders($list); //格式化订单信息 日期, 状态
        $hasMore = $total > $start + $num;

        //是否有退货
        $refundPrivileges = array();
        $refunds = Order_Api::getRefundList(array('oid' => $oids), 0, 0);
        $refundPrice = array();
        $refundsMap = array();
        if (!empty($refunds['list']))
        {
            foreach ($refunds['list'] as $item)
            {
                if ($item['paid'] != Conf_Order::HAD_PAID)
                {
                    continue;
                }
                $oid = $item['oid'];
                $refundsMap[$item['oid']][] = $item['rid'];
                $refundPrivileges[$oid] += $item['refund_privilege'] + $item['adjust'];
                $refundPrice[$oid] += $item['price'] + $item['refund_freight'] + $item['refund_carry_fee'];
            }
        }
        $refundFees = Refund_Api::getRefundFeeByOids($oids, TRUE);

        foreach ($list as &$item)
        {
            if (!empty($refundsMap[$item['oid']]))
            {
                $item['step_show'] = '有退货';
            }
            $oid = $item['oid'];
            $item['total_order_price'] = $item['price'] + $item['customer_carriage'] + $item['freight'] - $item['privilege'] - $refundPrice[$oid] + $refundPrivileges[$oid] + $refundFees[$oid]['freight'] + $refundFees[$oid]['carry_fee'];
        }

        return array(
            'list' => $list,
            'total' => $total,
            'has_more' => $hasMore,
            'need_to_pay' => $needToPayNum,
            'need_to_send' => $needToSendNum,
            'need_to_receive' => $needToReceiveNum,
        );
    }

    public static function getOrdersOfCustomer($cid, $order = array(), $start = 0, $num = 20, $extConf = array())
    {
        $oo = new Order_Order();
        $list = $oo->getCustomerOrderList($cid, $total, $order, $start, $num, array('*'), $extConf);

        //补充了订单的退款信息等
        $or = new Order_Refund();
        $data = $or->getListOfCustomer($cid, $rtotal, 0, 0);
        $orlist = Tool_Array::list2Map($data, 'oid');
        foreach ($list as $k => $info)
        {
            $list[$k]['refund_price'] = $orlist[$info['oid']]['price'];
            $list[$k]['user_need_to_pay'] = Order_Helper::calOrderNeedToPay($info);
        }

        $hasMore = $total > $start + $num;

        Order_Helper::formatOrders($list); //格式化订单信息 日期, 状态

        return array(
            'list' => $list,
            'total' => $total,
            'has_more' => $hasMore
        );
    }

    public static function addOrder($cid, $info, $products = array(), $suid = 0, $source = 0)
    {
        $cid = intval($cid);
        $suid = intval($suid);
        assert($cid > 0);

        //是否含税
        $customer = Crm2_Api::getCustomerInfo($cid, FALSE, FALSE);
        $info['has_duty'] = $customer['customer']['has_duty'];
        $info['cid'] = $cid;
        $info['suid'] = $suid;
        $info['source'] = $source;

        $oo = new Order_Order();
        $oid = $oo->add($info);

        if (!empty($products))
        {
            $oo = new Order_Order();
            foreach ($products as $product)
            {
                $oo->addOrderProduct($oid, $product, $info['city_id']);
            }

            self::_updateOrderTotalPrice($oid, $products);
        }

        return $oid;
    }

    /**
     * 从新版（2016-10）客户端下单；h5下单
     *
     * @param $cid
     * @param $uid
     * @param $infos
     * $addressId, $service, $floorNum, $deliveryDate, $deliveryTime, $note, $products,
     * $cuts = array(), $paymentType = Conf_Base::CUSTOMER_PT_ONLINE_PAY
     *
     * @return mixed
     */
    public static function addClientOrder($cid, $uid, $infos)
    {
        $cid = intval($cid);
        assert($cid > 0);

        //商品
        $type = Conf_Activity_Flash_Sale::PALTFORM_WECHAT;
        if ($infos['source'] == Conf_Order::SOURCE_APP_ANDROID || $infos['source'] == Conf_Order::SOURCE_APP_IOS)
        {
            $type = Conf_Activity_Flash_Sale::PALTFORM_APP;
        }
        $productsNew = self::formatProudcts4Order($infos['products'], $type, $infos['city_id'],$cid, 'real');

        $total = 0;
        foreach ($productsNew as $item)
        {
            $total += $item['num'] * $item['price'];
        }

        $cc = new Crm2_Customer();
        $customer = $cc->get($cid);

        // 小区地址
        $addressId = $infos['address_id'];
        $construction = Crm2_Api::getConstructionSite($addressId);
        $addressDetail = $construction['address'];
        if (!empty($construction['community_id']))
        {
            if (!empty($construction['community_name']))
            {
                $addressDetail = $construction['community_name'] . Conf_Area::Separator_Construction . $addressDetail;
            }
            else
            {
                $cc = new Order_Community();
                $communityInfo = $cc->get($construction['community_id']);
                
                if (!empty($communityInfo))
                {
                    $addressDetail = $communityInfo['name'] . Conf_Area::Separator_Construction . $addressDetail;
                }
            }
        }else{
            $addressDetail = $construction['_community_name'] . Conf_Area::Separator_Construction . $addressDetail;
        }
        
        //信息
        $info = array();
        $info['cid'] = $cid;
        $info['uid'] = $uid;
        $info['bid'] = intval($customer['bid']);
        $info['saler_suid'] = intval($customer['sales_suid']);
        $info['step'] = Conf_Order::ORDER_STEP_NEW;
        $info['city'] = $infos['delivery_type'] == Conf_Order::DELIVERY_BY_YOURSELF ? $infos['city_id'] : $construction['city'];
        $info['district'] = $construction['district'];
        $info['area'] = $construction['area'];
        $info['address'] = $addressDetail;
        $info['construction'] = $addressId;
        $info['community_id'] = $construction['community_id'];
        $info['has_duty'] = $customer['has_duty'];
        $info['source'] = Conf_Order::SOURCE_WEIXIN;
        $info['price'] = $total;
        $info['service'] = $infos['service'];
        $info['floor_num'] = $infos['floor_num'];
        $info['customer_note'] = $infos['note'];
        $info['customer_payment_type'] = $infos['payment_type'];
        $info['source'] = $infos['source'];
        $info['city_id'] = $infos['city_id'];
        $info['delivery_date'] = $infos['delivery_date'];
        $info['delivery_type'] = $infos['delivery_type'];
        $info['delivery_date_end'] = $infos['delivery_date_end'];
        $info['wid'] = $infos['wid'];
        if ($infos['delivery_type'] == Conf_Order::DELIVERY_BY_YOURSELF)
        {
            $info['contact_name'] = $infos['contact_name'];
            $info['contact_phone'] = $infos['contact_phone'];
        }
        else
        {
            $info['contact_name'] = $construction['contact_name'];
            $info['contact_phone'] = $construction['contact_phone'];
        }

        //获取库房id（社区店）
        if (empty($infos['city_id']))
        {
            $cityInfo = City_Api::getCity();
            $cityId = $cityInfo['city_id'];
            $info['wid'] = Order_Helper::getWidByCityAndAddress($cityId, $info['address']);
            $info['city_id'] = $cityId;
        }

        // 增加cuts逻辑
        $cuts = $infos['cuts'];
        if ( empty($cuts) ) {
            $cuts = array();
        }
        //费用
        $products1 = array();
        foreach ($productsNew as $product)
        {
            $note = null;
            if ( isset($cuts[$pid]) ) {
                $note = '裁断数量:' . intval($cuts[$pid]);
            }
            $products1[] = array(
                'pid' => $product['pid'],
                'num' => $product['num'],
                'price' => $product['price'],
                'note' => $note,
            );
        }

        if ($infos['delivery_type'] == Conf_Order::DELIVERY_QUICKLY)
        {
            //$info['customer_carriage'] = Logistics_Api::calCarryFeeByProducts($products1, $infos['service'], $infos['floor_num'], 0, $infos['city_id']);

            $client = new Yar_Client(MS . "/cmpt/order/fees");
            $result = $client->AppCarryFee($products1, $infos['city_id'], $infos['floor_num'], $infos['service']);
            $info['customer_carriage'] = 0;
            if ( isset($result['user']) ) {
                $info['customer_carriage'] = $result['user'];
            }

            $info['freight'] = Logistics_Api::calFreightByProductsAddress($productsNew, $construction['city'], $construction['district'], $construction['ring_road'], 0, $construction['community_id'], $cid);
            $info['freight'] += 30 * 100;
        }
        else if ($infos['delivery_type'] == Conf_Order::DELIVERY_BY_YOURSELF)
        {
            $info['customer_carriage'] = 0;
            $info['freight'] = 0;
        }
        else
        {
            //$info['customer_carriage'] = Logistics_Api::calCarryFeeByProducts($products1, $infos['service'], $infos['floor_num'], 0, $infos['city_id']);
            $client = new Yar_Client(MS . "/cmpt/order/fees");
            $result = $client->AppCarryFee($products1, $infos['city_id'], $infos['floor_num'], $infos['service']);
            $info['customer_carriage'] = 0;
            if ( isset($result['user']) ) {
                $info['customer_carriage'] = $result['user'];
            }

            $info['freight'] = Logistics_Api::calFreightByProductsAddress($productsNew, $construction['city'], $construction['district'], $construction['ring_road'], 0, $construction['community_id'], $cid);
        }

        $oo = new Order_Order();
        $oid = $oo->add($info);

        if (Conf_Base::switchForManagingMode())
        {
            $sp = new Shop_Product();
            $pids = Tool_Array::getFields($productsNew, 'pid');
            $productInfo = $sp->getBulk($pids);
        }

        foreach ($productsNew as $product)
        {
            if (Conf_Base::switchForManagingMode())
            {
                $product['managing_mode'] = $productInfo[$product['pid']]['managing_mode'];
            }

            $oo->addClicentOrderProduct($oid, $product, $infos['cuts']);
        }

        $info['oid'] = $oid;
        $orderInfo = Order_Api::getOrderInfo($oid, TRUE);
        $orderProducts = Order_Api::getOrderProducts($oid);
        $orderProducts = Privilege_Api::getRealBuyProducts($orderProducts['products'], $oid);
        $privilegeInfo = Privilege_2_Api::savePromotionPrivilege($orderInfo['cid'], $orderProducts, $orderInfo, false, $infos['gift_discount_pids']);
        Order_Api::updateOrderInfo($oid, array('privilege' => $privilegeInfo['total_privilege']));
        $info['privilege'] = $privilegeInfo['total_privilege'];

        return $info;
    }

    /**
     * 计算商品总价
     * @param     $products
     * @param int $platform
     * @param int $cityId
     *
     * @return array
     */
    public static function formatProudcts4Order($products, $platform = Conf_Activity_Flash_Sale::PALTFORM_WECHAT, $cityId = Conf_City::BEIJING, $cid=0, $reqFor= 'show')
    {
        $list = array();

        //获取pid
        $pids = array_keys($products);

        //格式化商品信息
        $productInfos = Shop_Api::getProductInfos($pids, $platform, false, $cityId, $cid, $reqFor);
        foreach ($products as $pid => $num)
        {
            if (intval($pid) <= 0)
            {
                continue;
            }
            $productInfo = $productInfos[$pid];
            if (empty($productInfo))
            {
                continue;
            }
            $item = array(
                'pid' => $pid,
                'sid' => $productInfo['sku']['sid'],
                'num' => $num,
                'cost' => $productInfo['product']['cost'],
                'price' => $productInfo['product']['sale_price'],
                'ori_price' => $productInfo['product']['ori_price'],
                'sku' => $productInfo['sku'],
                'product' => $productInfo['product'],
            );

            $list[$pid] = $item;
        }

        return $list;
    }

    /**
     * 只改基本信息，如收货人，地址，送货时间等
     *
     * @param       $oid
     * @param       $info
     * @param array $change
     *
     * @return boolean
     */
    public static function updateOrderInfo($oid, $info, $change = array())
    {
        //这个方法只更改信息，不更新订单进度等
        unset($info['step']);
        if (empty($info) && empty($change))
        {
            return TRUE;
        }

        $oo = new Order_Order();
        $oldInfo = Order_Api::getOrderInfo($oid);
        
        if ($oldInfo['paid'] != Conf_Order::HAD_PAID && $info['paid'] == Conf_Order::HAD_PAID)
        {
            //出库更新，addby-guoqiang/20170413
            //self::updateCustomerOrder($oldInfo['cid'], $oid);
            
            // 更新客户最新下单时间
            $info['pay_time'] = date('Y-m-d H:i:s');

            if ($oldInfo['step'] >= Conf_Order::ORDER_STEP_FINISHED)
            {
                // 发放优惠券逻辑
                Coupon_Api::sendPromotionCoupon($oldInfo['cid'], $oid);

                WeiXin_Message_Api::sendOrderFinishMessage($oldInfo['uid'], $oldInfo['oid']);

                $userInfo = Crm2_Api::getUserInfo($oldInfo['uid'], false, false);
                $coeff = Conf_User::getCoeff4MemberGrade($userInfo['user']['grade']);
                $oldInfo['pay_time'] = date('Y-m-d H:i:s');
                Cpoint_Api::getCustomerOrderPoint($oldInfo, $coeff);
                if ($oldInfo['will_get_point'] > 0)
                {
//                    $totalPoint = $userInfo['user']['total_point'] + $oldInfo['will_get_point'];
//                    $word = sprintf('亲爱的工长，您的订单已完成，本次获得%d积分，总积分%d，如有问题请致电好材客服热线400-058-5788，感谢您对好材的支持，祝您生活愉快！', $oldInfo['will_get_point'], $totalPoint);
//                    Data_Sms::send($userInfo['user']['mobile'], $word);
                }
            }

            if ($oldInfo['step'] == Conf_Order::ORDER_STEP_NEW && $oldInfo['delivery_type'] == Conf_Order::DELIVERY_QUICKLY)
            {
                $hour = date('G');
                $oldDeliveryDate = strtotime($oldInfo['delviery_date']);
                $now = time();
                if ($oldDeliveryDate - $now < 7200)
                {
                    if ($hour >= 0 && $hour <= 7)
                    {
                        $info['delivery_date'] = date('Y-m-d 9:00:00', time());
                        $info['delivery_date_end'] = date('Y-m-d 11:00:00', time());
                    }
                    else if ($hour >= 8 && $hour < 18)
                    {
                        $startTime = time() + 2 * 3600;
                        $endTime = time() + 4 * 3600;
                        $info['delivery_date'] = date('Y-m-d H:00:00', $startTime);
                        $info['delivery_date_end'] = date('Y-m-d H:00:00', $endTime);
                    }
                    else
                    {
                        $tomorrow = strtotime('tomorrow');
                        $info['delivery_date'] = date('Y-m-d 9:00:00', $tomorrow);
                        $info['delivery_date_end'] = date('Y-m-d 11:00:00', $tomorrow);
                    }
                }
            }

            //自动分单
            if($oldInfo['city_id'] == Conf_City::CHONGQING && $oldInfo['step'] < Conf_Order::ORDER_STEP_SURE
                && in_array($oldInfo['source'],array(Conf_Order::SOURCE_APP_IOS, Conf_Order::SOURCE_APP_ANDROID, Conf_Order::SOURCE_WEIXIN)) && $oldInfo['paid'] == Conf_Order::UN_PAID)
            {
                $queue = new Data_Queue();
                $queue->enqueue(Queue_Base::Queue_Type_Auto_Order, array('oid' => $oid));
            }
        }

        $affectRow = $oo->update($oid, $info, $change);

        if ($affectRow && $oldInfo['paid'] != Conf_Order::HAD_PAID && $info['paid'] == Conf_Order::HAD_PAID
            && !Order_Helper::isFranchiseeOrder($oldInfo['wid']))
        {
            Cpoint_Api::updatePointWhenCustomerOrder($oid);
        }

        return $affectRow;
    }

    /**
     * 只改step字段，以及一些需要的处理，而且step向前
     *
     * @param $oid
     * @param $newStep
     * @param $staff
     *
     * @return boolean
     * @throws Exception
     */
    public static function forwardOrderStep($oid, $newStep, $staff)
    {
        $oo = new Order_Order();
        $oldOrder = $oo->get($oid);
        $cid = $oldOrder['cid'];
        $wid = $oldOrder['wid'];
        if ($wid == 0)
        {
            $wid = City_Api::getDefaultWarehouse();
            //throw new Exception('order:empty wid');
        }
        
        $update = array();
        $now = date('Y-m-d H:i:s');

        //如果状态有变化，且新状态大于老状态，则进行逻辑
        //新状态小于老状态，是rollbackOrder
        if ($newStep != $oldOrder['step'] && $newStep > $oldOrder['step'])
        {
            $products = $oo->getProductsOfOrder($oid);
            if (empty($products) && $oldOrder['aftersale_type'] != Conf_Order::AFTERSALE_TYPE_REFUND)
            {
                throw new Exception('Order:empty products');
            }

            if (Conf_Order::ORDER_STEP_NEW != $newStep)
            {
                //是否可以处理该订单
                Order_Helper::canDealOrder($oldOrder, $staff);
        
                //检查用户是否能操作这个修改
                $stepRes = Order_Helper::getOrderNextStep($staff, $oldOrder);
                if ($staff['suid'] != 999 && $stepRes != $newStep)
                {
                    throw new Exception('common:permission denied');
                }

                //客服确认
                if ($newStep == Conf_Order::ORDER_STEP_SURE && $oldOrder['step'] < Conf_Order::ORDER_STEP_SURE)
                {
                    $os = new Order_Step($oid);
                    $sureRet = $os->sure($staff);
                    if ($sureRet['errno'] != 0)
                    {
                        throw new Exception($sureRet['errmsg']);
                    }

                    return true;
                }
                //出库
                else if ($newStep == Conf_Order::ORDER_STEP_PICKED && $oldOrder['step'] < Conf_Order::ORDER_STEP_PICKED)
                {
                    if ($wid != $staff['wid'] && $staff['wid'] != 0)
                    {
                        throw new Exception('仓库id和操作人id不一致，请核对！！');
                    }

                    $lockedRet = Conf_Warehouse::isLockedWarehouse($wid);
                    if ($lockedRet['st'])
                    {
                        throw new Exception($lockedRet['msg']);
                    }

                    $suid = $staff['suid'];
                    //判断是否换货单生成的补单,需不需要出库
                    $need_storage = TRUE;
                    if ($oldOrder['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_EXCHANGED)
                    {
                        $exchangedInfo = Exchanged_Api::getExchanged($oldOrder['aftersale_id']);
                        if ($exchangedInfo['info']['need_storage'] == 0)
                        {
                            $need_storage = FALSE;
                        }
                        //$need_storage && self::_checkUpdateStockNum($suid, $wid, $oid, $products);
                    }
                    //判断是否补漏单生成的补单，需不需要出库
                    if ($oldOrder['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_TRAPS)
                    {
                        //$need_storage = true;
                        $trapsInfo = Traps_Api::getTraps($oldOrder['aftersale_id']);
                        if ($trapsInfo['info']['need_storage'] == 0)
                        {
                            $need_storage = FALSE;
                        }
                    }
                    $need_storage && self::_checkUpdateStockNum($suid, $wid, $oid, $products);

                    $price = Order_Helper::calOrderPayableTotalPrice($oldOrder);
                    $type = Conf_Money_In::ORDER_PAIED;
                    $suid = $staff['suid'];

                    Finance_Api::addMoneyInHistory($cid, $type, $price, $suid, $oid, $wid, '', 0, $oldOrder['uid'], $oid);

                    //出库时间
                    $update['ship_time'] = $now;
                    $update['op_note'] = sprintf("ffee:%d,cfee:%d,prfee:%d", $oldOrder['freight'], $oldOrder['customer_carriage'], $oldOrder['privilege']);

                    $coopworker = Logistics_Coopworker_Api::getOrderOfWorkers($oldOrder['oid'], Conf_Base::COOPWORKER_DRIVER);
                    if (!empty($coopworker))
                    {
                        $orderDriver = array_shift($coopworker);
                        $driver = Logistics_Api::getDriver($orderDriver['cuid']);
                        //消息
                        $msgInfo = array(
                            'ocode' => date('Ymd', strtotime($oldOrder['ctime'])) . '-' . $oldOrder['oid'],
                            'name' => $driver['name'],
                            'mobile' => $driver['mobile'],
                        );
                        Crm2_User_Msg_Api::addMsg($oldOrder['uid'], $oldOrder['cid'], Conf_User_Msg::$MSG_ORDER_SEND, $msgInfo);
                    }
                    
                    if (empty($oldOrder['aftersale_type']))
                    {
                        WeiXin_Message_Api::sendOrderSetoutMessage($oldOrder['uid'], $oldOrder['oid']);
                    }
                    
                    $userInfo = Crm2_Api::getUserInfo($oldOrder['uid'], false, false);
//                    $word = sprintf('亲爱的工长，您购买的辅材已出库开始派送，请您保持电话畅通，如有问题请致电好材客服热线400-058-5788，感谢您对好材的支持，祝您生活愉快！');
//                    Data_Sms::send($userInfo['user']['mobile'], $word);
                    //Data_Sms::sendNew($userInfo['user']['mobile'], Conf_Sms::DELIVERY_SUCC_KEY, array());
                }
                else if ($newStep == Conf_Order::ORDER_STEP_FINISHED)
                {
                    //订单多付自动转余额
                    $_totalAmount = Order_Helper::calOrderPayableTotalPrice($oldOrder);
                    if($oldOrder['real_amount'] > $_totalAmount && !Order_Helper::isFranchiseeOrder($oldOrder['wid'], $oldOrder['city_id']))
                    {
                        $_userNeedToPay = $_totalAmount - $oldOrder['real_amount'];
                        //余额从客户账务流水 转移到 客户余额流水中
                        Finance_Api::addMoneyInHistory($cid, Conf_Money_In::CUSTOMER_AMOUNT_TRANSFER, abs($_userNeedToPay), 
                                            Conf_Admin::ADMINOR_AUTO, $oid, $wid, '订单' . $oid . '回单，余额自动转移', Conf_Base::PT_BALANCE, $oldOrder['uid'], $oid);

                        //插入客户账务余额流水
                        $saveData = array(
                            'type' => Conf_Finance::CRM_AMOUNT_TRANSFER,
                            'price' => abs($_userNeedToPay),
                            'payment_type' => Conf_Base::PT_BALANCE,
                            'note' => '订单' . $oid . '回单，余额自动转移',
                            'objid' => $oid,
                            'oid' => $oid,
                            'suid' => Conf_Admin::ADMINOR_AUTO,
                        );
                        $update['real_amount'] = $oldOrder['real_amount'] - abs($_userNeedToPay);

                        Finance_Api::addCustomerAmountHistory($cid, $saveData);
                    }
                    //更新订单付款状态
                    $_totalAmount2 = Order_Helper::calOrderTotalPrice($oldOrder);
                    if ($oldOrder['paid'] == Conf_Order::PART_PAID && $oldOrder['real_amount'] >= $_totalAmount2)
                    {
                        Order_Api::updateOrderInfo($oid, array('paid' => Conf_Order::HAD_PAID));
                        $oldOrder['paid'] = Conf_Order::HAD_PAID;
                    }
                    $update['back_time'] = $now;

                    $date = date('Y-m-d H:00:00', strtotime($oldOrder['delivery_date']));
                    if ($oldOrder['source_oid'] == 0 && $date >= Conf_Activity::$AFTERNOON_CONF['start_time'] && $date < Conf_Activity::$AFTERNOON_CONF['end_time'] && $oldOrder['paid'] == Conf_Order::HAD_PAID)
                    {
                        $startHour = date('G', strtotime($oldOrder['delivery_date']));
                        $endHour = date('G', strtotime($oldOrder['delivery_date_end']));
                        if ($oldOrder['source'] == Conf_Order::SOURCE_WEIXIN && $startHour >= 12 && $endHour <= 15)
                        {
                            Coupon_Api::sendAfternoonCoupon($cid);
                        }
                        if ($oldOrder['suid'] > 0 && $startHour >= 13 && $endHour <= 15)
                        {
                            Coupon_Api::sendAfternoonCoupon($cid);
                        }
                    }
                    if ($oldOrder['paid'] == Conf_Order::HAD_PAID)
                    {
                        Coupon_Api::sendPromotionCoupon($cid, $oid);

                        WeiXin_Message_Api::sendOrderFinishMessage($oldOrder['uid'], $oldOrder['oid']);

//                        $userInfo = Crm2_Api::getUserInfo($oldOrder['uid'], false, false);
//                        $ch = new Cpoint_History();
//                        $pointHistory = $ch->getPointHistoryByOid($oldOrder['uid'], $oid);
//                        $totalPoint = intval($pointHistory['total_point']);
//                        $willGetPoint = intval($pointHistory['chg_point']);
//                        $word = sprintf('亲爱的工长，您的订单已完成，本次获得%d积分，总积分%d，如有问题请致电好材客服热线400-058-5788，感谢您对好材的支持，祝您生活愉快！', $willGetPoint, $totalPoint);
//                        Data_Sms::send($userInfo['user']['mobile'], $word);
                    }

                    $res = Logistics_Coopworker_Api::getOrderOfWorkers($oid, Conf_Base::COOPWORKER_DRIVER);
                    if (!empty($res))
                    {
                        foreach ($res as $driver)
                        {
                            $openid = WeiXin_Coopworker_Api::getCoopworkerOpenid($driver['cuid'], 1);
                            if (!empty($openid))
                            {
                                WeiXin_Coopworker_Api::sendBackNoticeMessage($openid, $oid, $driver['price']);
                            }
                        }
                    }

                    //消息
                    $msgInfo = array(
                        'ocode' => date('Ymd', strtotime($oldOrder['ctime'])) . '-' . $oldOrder['oid'],
                    );
                    Crm2_User_Msg_Api::addMsg($oldOrder['uid'], $oldOrder['cid'], Conf_User_Msg::$MSG_ORDER_FINISH, $msgInfo);

                    Logistics_Coopworker_Api::finishOrderCoopworker($oid);

                    //判断是否换货单生成的补单
                    if ($oldOrder['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_EXCHANGED)
                    {
                        $exchangedInfo = Exchanged_Api::getExchanged($oldOrder['aftersale_id']);
                        //判断对应的退贷单是否已完成，如果完成换货单状态自动变为已完成
                        $refundInfo = Order_Api::getRefund($exchangedInfo['info']['refund_id']);
                        if (intval($refundInfo['info']['step']) >= Conf_Refund::REFUND_STEP_IN_STOCK)
                        {
                            Exchanged_Api::updateExchanged($exchangedInfo['info']['eid'], array('step' => Conf_Exchanged::EXCHANGED_STEP_FINISHED));
                            Admin_Api::addOrderActionLog($staff['suid'], $exchangedInfo['info']['oid'], Conf_Order_Action_Log::ACTION_FINISHED_EXCHANGED_ORDER, array('eid' => $exchangedInfo['info']['eid']));
                        }
                    }
                    //判断是否补漏单生成的补单
                    if ($oldOrder['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_TRAPS)
                    {
                        $trapsInfo = Traps_Api::getTraps($oldOrder['aftersale_id']);
                        Traps_Api::updateTraps($oldOrder['aftersale_id'], array('step' => Conf_Traps::TRAPS_STEP_FINISHED));
                        Admin_Api::addOrderActionLog($staff['suid'], $trapsInfo['info']['oid'], Conf_Order_Action_Log::ACTION_FINISHED_TRAPS_ORDER, array('tid' => $trapsInfo['info']['tid']));
                    }
                }
                else if ($newStep == Conf_Order::ORDER_STEP_HAS_DRIVER) //这样加很蛋疼，后面处理
                {
                    // 安排司机的日志
                    $param = array('newStep' => Conf_Order::$ORDER_STEPS[Conf_Order::ORDER_STEP_HAS_DRIVER]);
                    Admin_Api::addOrderActionLog($staff['suid'], $oid, Conf_Order_Action_Log::ACTION_CHANGE_STEP, $param);
                }
            }

            $update['step'] = $newStep;
            $oo->update($oid, $update);

            
            //出库更新客户消费数据
            if ($newStep == Conf_Order::ORDER_STEP_PICKED)
            {
                self::updateCustomerOrder($cid);
                Cpoint_Api::updatePointWhenCustomerOrder($oid);
            }
            
            //更新用户订单数等 【出库更新，addby-guoqiang/20170413】
            //self::updateCustomerOrder($cid);

            // 第三方合作订单，订单更新时通知第三方，具体逻辑在调用函数中实现 （暂时下线：addby guoqiang-20170413)
            //Open_Order_Api::notifyPartnerOrderStatus($oid, $newStep, $oldOrder['source']);

            //出库给司机app发push消息
            if ($oldOrder['delivery_type'] != Conf_Order::DELIVERY_BY_YOURSELF && $newStep == Conf_Order::ORDER_STEP_PICKED && $oldOrder['step'] < Conf_Order::ORDER_STEP_PICKED)
            {
                $driver = Logistics_Auth_Api::checkMobile($driver['mobile'], Conf_Base::COOPWORKER_DRIVER);

                //判断该排线所有的订单是否都已经出库
                $can_set_out = 1;
                $ret = Logistics_Api::getDriverQueue($driver['uid']);
                $step = $ret['step'];
                if (!empty($ret['line_id']))
                {
                    $lineInfo = Logistics_Api::getLineDetail($ret['line_id']);
                }

                if (!empty($lineInfo['oids']) && $step >= Conf_Driver::STEP_ACCEPT)
                {
                    $oids = explode(',', $lineInfo['oids']);
                    $orders = Order_Api::getListByPk($oids);
                    foreach ($oids as $oid)
                    {
                        if ($orders[$oid]['step'] < Conf_Order::ORDER_STEP_PICKED)
                        {
                            $can_set_out = 2;
                            break;
                        }
                    }
                }
                //发推送消息
                if (!empty($driver['user']['regid']) && $can_set_out === 1)
                {
                    Push_Xiaomi_Api::pushToUserMessage($driver['user']['regid'], Conf_Driver::$MSG_PUSH[Conf_Driver::MSG_SEND]['title'], Conf_Driver::$MSG_PUSH[Conf_Driver::MSG_SEND]['desc'], Push_Xiaomi_Api::HAOCAI_DRIVER, 2);
                }
            }
        }

        return TRUE;
    }

    /**
     * 自动分单（重庆订单自动分配给最近有货的仓库，并自动确认订单）
     * @param $oid
     */
    public static function autoSeparateOrder($oid)
    {
        $result = array(
            'errno' => 0,
            'errmsg' => '',
        );
        $oo = new Order_Order();
        $oldOrder = $oo->get($oid);
        if($oldOrder['city_id'] != Conf_City::CHONGQING)
        {
            $result['errno'] = 1;
            $result['errmsg'] = '该订单不是重庆城市的';
            return $result;
        }
        if($oldOrder['paid'] == Conf_Order::UN_PAID)
        {
            $result['errno'] = 2;
            $result['errmsg'] = '该订单没有支付';
            return $result;
        }
        if($oldOrder['step'] >= Conf_Order::ORDER_STEP_SURE)
        {
            $result['errno'] = 3;
            $result['errmsg'] = '该订单已确认过';
            return $result;
        }

        if($oldOrder['delivery_type'] != Conf_Order::DELIVERY_BY_YOURSELF)
        {
            $wid = self::autoAllocateWid4Order($oid);
            if($wid == 0)
            {
                $result['errno'] = 4;
                $result['errmsg'] = '该订单没有小区信息';
                return $result;
            }
            $oo->update($oid, array('wid' =>$wid));
        }

        $staff = Admin_Api::getStaff(Conf_Admin::ADMINOR_AUTO);
        $os = new Order_Step($oid);
        $result = $os->sure($staff);

        return $result;
    }

    /**
     * 回退订单，从客服确认/已安排司机回到未确认
     *
     * @param $oid
     */
    public static function rollbackOrder($oid, $staff)
    {
        $oo = new Order_Order();
        $oldOrder = $oo->get($oid);
        $wid = $oldOrder['wid'];

        //是否可以处理该订单
        Order_Helper::canDealOrder($oldOrder, $staff);
        
        if (empty($wid))
        {
            throw new Exception('order:empty wid');
        }

        if ($oldOrder['step'] < Conf_Order::ORDER_STEP_SURE || $oldOrder['step'] >= Conf_Order::ORDER_STEP_PICKED)
        {
            throw new Exception('order:status error');
        }

        //判断订单是否换货补单
        if ($oldOrder['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_EXCHANGED)
        {
            throw new Exception('该订单是换货单(id:' . $oldOrder['aftersale_id'] . ')的补单，不能回滚！');
        }

        $oo->update($oid, array('step' => Conf_Order::ORDER_STEP_NEW));

        self::_releaseOccupied($oid, $wid);

        //释放优惠券
        $cc = new Coupon_Coupon();
        $cc->releaseCoupon($oid);
    }

    /**
     * 回退订单， 从自提到配送，从已安排司机到未安排司机
     *
     * @param $oid
     */
    public static function pickupToExpress($oid)
    {
        $oo = new Order_Order();

        $oo->update($oid, array('step' => Conf_Order::ORDER_STEP_BOUGHT));
    }

    /**
     * 取消，h5端客户触发
     *
     * @param $cid
     * @param $oid
     * @param $reason
     *
     * @return bool
     * @throws Exception
     */
    public static function cancelOrder($cid, $oid, $reason)
    {
        $order = Order_Api::getOrderInfo($oid);
        if ($order['status'] != Conf_Base::STATUS_NORMAL)
        {
            throw new Exception('order:status error');
        }
        if ($order['step'] >= Conf_Order::ORDER_STEP_SURE)
        {
            throw new Exception('order:status error');
        }
        if ($order['cid'] != $cid)
        {
            throw new Exception('order:status error');
        }
        if ($order['paid'] != Conf_Order::UN_PAID)
        {
            throw new Exception('order:had paid');
        }

        $oo = new Order_Order();
        $oo->cancel($oid);

        //取消原因
        $ocr = new Order_Cancel_Reason();
        $order = $oo->get($oid);
        $info = array(
            'oid' => $oid,
            'cid' => $cid,
            'uid' => $order['uid'],
            'reason' => $reason,
        );
        $ocr->add($info);

        //释放优惠券
        $cc = new Coupon_Coupon();
        $cc->releaseCoupon($oid);

        //出库更新，addby-guoqiang/20170413
//        $order = $oo->get($oid);
//        self::updateCustomerOrder($order['cid']);

//        Activity_Lottery_Api::cancelMarkLottery($order['cid'], $oid);

        return TRUE;
    }

    /**
     * 后台管理删除订单
     *
     * @param $oid
     *
     * @return boolean
     */
    public static function deleteOrder($oid)
    {
        $oo = new Order_Order();
        $oldOrder = $oo->get($oid);

        if ($oldOrder['paid'] != Conf_Order::UN_PAID && $oldOrder['real_amount'] > 0)
        {
            throw new Exception('order:had paid');
        }
        
        $wid = !empty($oldOrder['wid']) ? $oldOrder['wid'] : Conf_Warehouse::WID_3;

        if ($oldOrder['step'] >= Conf_Order::ORDER_STEP_SURE)
        {
            $do_status = true;
            if($oldOrder['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_EXCHANGED)
            {
                $exchangedInfo = Exchanged_Api::getExchanged($oldOrder['aftersale_id']);
                if($exchangedInfo['info']['need_storage'] == 0)
                {
                    $do_status = false;
                }
            }elseif($oldOrder['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_TRAPS){
                $trapsInfo = Traps_Api::getTraps($oldOrder['aftersale_id']);
                if($trapsInfo['info']['need_storage'] == 0)
                {
                    $do_status = false;
                }
            }
            // 释放一个订单占用的库存
            $do_status && self::_releaseOccupied($oid, $wid);
        }

        // 删除
        $oo->delete($oid);

        // 释放一个订单的优惠券
        Coupon_Api::releaseCoupon($oid);

//        Activity_Lottery_Api::cancelMarkLottery($oldOrder['cid'], $oid);

        //出库更新，addby-guoqiang/20170413
//        self::updateCustomerOrder($oldOrder['cid']);

        return TRUE;
    }

    /**
     * 后台管理恢复订单
     *
     * @param $oid
     *
     * @return boolean
     */
    public static function resetOrder($oid)
    {
        $oo = new Order_Order();
        $orderInfo = $oo->get($oid);

        $fca = new Finance_Customer_Amount();
        $where = array(
            'objid'=>$oid, 
            'status'=>Conf_Base::STATUS_NORMAL,
            'type'=>array(Conf_Finance::CRM_AMOUNT_TYPE_PREPAY, Conf_Finance::CRM_AMOUNT_TYPE_DEL_REFUND)
        );
        
        $customerAmountNum = $fca->getTotalByWhere($where);
        
        //恢复订单
        //1 已付款 && 未退余额（删除后，客户支付订单；几乎同时）
        if ($orderInfo['real_amount']>0 && $customerAmountNum==0)
        {
            $orderUpData = array(
                'status' => Conf_Base::STATUS_NORMAL,
                'step' => Conf_Order::ORDER_STEP_NEW,
            );
        }
        else    //2 未付款 || 已付款&&退余额
        {
            $orderUpData = array(
                'status' => Conf_Base::STATUS_NORMAL,
                'step' => Conf_Order::ORDER_STEP_NEW,
                'paid' => Conf_Order::UN_PAID,
                'real_amount' => 0,
                'payment_type' => 0,
            );
        }
        
        return $oo->recoveryOrder($oid, $orderUpData);
        
    }

    public static function isFristOrder($oid, array $orderInfo = array())
    {
        if (empty($orderInfo))
        {
            $oo = new Order_Order();
            $orderInfo = $oo->get($oid);
        }

        $oo = new Order_Order();
        $firstOrder = $oo->isFristOrder($orderInfo);

        return $firstOrder;
    }

    /**
     * 选择司机-搬运工
     *
     * @param $objId
     * @param $cuid
     * @param $price
     * @param $role
     * @param $userType
     * @param $wid
     * @param $_user
     * @param $objType
     *
     * @throws Exception
     */
    public static function  selectDriverCarrier($objId, $cuid, $price, $role, $userType, $wid, $_user, $objType = Conf_Coopworker::OBJ_TYPE_ORDER)
    {
        $hasSelected = Logistics_Coopworker_Api::getOrderOfWorkers($objId, $role, FALSE, $objType);
        
        foreach ($hasSelected as $oner)
        {
            if ($cuid == $oner['cuid'] && $role == $oner['type'])
            {
                throw new Exception('工人已经选择，请重新选择！');
            }
        }
        
        $oo = new Order_Order();
        $orderInfo = $oo->get($objId);
        if ($orderInfo['step'] < Conf_Order::ORDER_STEP_SURE)
        {
            throw new Exception('订单未确认，不能安排司机/搬运工！');
        }

        $data = array(
            'cuid' => $cuid,
            'oid' => $objId,
            'wid' => $wid,
            'price' => $price,
            'type' => $role,
            'suid' => $_user['suid'],
            'user_type' => $userType,
            'obj_id' => $objId,
            'obj_type' => $objType,
        );

        if ($role == Conf_Base::COOPWORKER_DRIVER)
        {
            $ld = new Logistics_Driver();
            $driver = $ld->get($cuid);
            $data['car_model'] = $driver['car_model'];
        }

        $oc = new Logistics_Coopworker();
        $oc->saveWorkerForOrder($data);

        //更新每个coopworker_order的订单的搬运工总人数
        if ($role == Conf_Base::COOPWORKER_CARRIER)
        {
            self::updateCoopworkerOrderCarrierNum($objId);
        }

        // 调度权限：更新订单状态为 '已安排司机'
//        $oo = new Order_Order();
//        $orderInfo = $oo->get($objId);
        if ($orderInfo['step'] >= Conf_Order::ORDER_STEP_SURE && $orderInfo['step'] < Conf_Order::ORDER_STEP_HAS_DRIVER )
        {
            $oo->update($objId, array('step'=>Conf_Order::ORDER_STEP_HAS_DRIVER));
            
            // 安排司机的日志
            $param = array('newStep' => Conf_Order::$ORDER_STEPS[Conf_Order::ORDER_STEP_HAS_DRIVER]);
			Admin_Api::addOrderActionLog($_user['suid'], $objId, Conf_Order_Action_Log::ACTION_CHANGE_STEP, $param);
        }
    }

    /**
     *
     * 更新每个coopworker_order的订单的搬运工总人数
     * @param int $oid
     */
    public static function updateCoopworkerOrderCarrierNum($oid)
    {
        $where = array(
            'oid' => $oid,
            'type' => Conf_Base::COOPWORKER_CARRIER,
            'status' => Conf_Base::STATUS_NORMAL
        );

        $lc = new Logistics_Coopworker();
        $list = $lc->getByOid($oid, 0, Conf_Base::COOPWORKER_CARRIER);
        $data = array('times' => count($list));
        $lc->updateByWhere($where, $data);
    }


    /**
     * 财务修改订单金额相关选项
     *
     * @param $oid
     * @param $freight
     * @param $privilege
     * @param $customerCarriage
     */
    public static function updateOrderByFinanceModify($oid, $freight, $privilege, $customerCarriage)
    {
        // 更新订单表 t_order
        $orderUpdata = array(
            'freight' => $freight,
            'privilege' => $privilege,
            'customer_carriage' => $customerCarriage,
        );

        $oo = new Order_Order();
        $oldOrder = $oo->get($oid);
        $oo->update($oid, $orderUpdata);

        // 更新财务历史表
//        $fmi = new Finance_Money_In();
//        $accountBalanceDiff = 0;
        $orderInfo = $oo->get($oid);

        $userNeedToPay = Order_Helper::calOrderNeedToPay($orderInfo);
        if ($userNeedToPay > 0)
        {
            if ($orderInfo['paid'] == Conf_Order::HAD_PAID)
            {
                $oo->update($oid, array('paid' => Conf_Order::PART_PAID));
            }
        }
        else
        {
            if ($orderInfo['paid'] != Conf_Order::HAD_PAID)
            {
                $oo->update($oid, array('paid' => Conf_Order::HAD_PAID));
            }
        }

//        // 订单支付
//        $history = $fmi->getByObjid($oid, Conf_Money_In::ORDER_PAIED);
//        if (!empty($history))
//        {
//            $newPrice = $orderInfo['price'] + $freight + $customerCarriage - $privilege;
//            $amountDiff = $newPrice - abs($history['price']);
//
//            if ($amountDiff <> 0)
//            {
////                $upPriceWhere = 'id=' . $history['id'];
////                $fmi->update($upPriceWhere, array('price' => $newPrice), array());
////
////                $upAmountWhere = 'cid=' . $history['cid'] . ' and id>=' . $history['id'];
////                $fmi->update($upAmountWhere, array(), array('amount' => $amountDiff));
//
//                $accountBalanceDiff += $amountDiff;
//            }
//        }
        if($orderInfo['step'] >= Conf_Order::ORDER_STEP_PICKED)
        {
            if($oldOrder['freight'] != $freight)
            {
//                $accountBalanceDiff += $freight-$oldOrder['freight'];
                Finance_Api::addMoneyInHistory($orderInfo['cid'], Conf_Money_In::SUPPLEMENT_FREIGHT, $freight-$oldOrder['freight'], Conf_Admin::ADMINOR_AUTO, $oid, $orderInfo['wid'], '', 0, $orderInfo['uid'], $oid);
            }
            if($oldOrder['privilege'] != $privilege)
            {
//                $accountBalanceDiff += $oldOrder['privilege']-$privilege;
                Finance_Api::addMoneyInHistory($orderInfo['cid'], Conf_Money_In::SUPPLEMENT_PRIVILEGE, $oldOrder['privilege']-$privilege, Conf_Admin::ADMINOR_AUTO, $oid, $orderInfo['wid'], '', 0, $orderInfo['uid'], $oid);
            }
            if($oldOrder['customer_carriage'] != $customerCarriage)
            {
//                $accountBalanceDiff += $customerCarriage-$oldOrder['customer_carriage'];
                Finance_Api::addMoneyInHistory($orderInfo['cid'], Conf_Money_In::SUPPLEMENT_CARRIAGE, $customerCarriage-$oldOrder['customer_carriage'], Conf_Admin::ADMINOR_AUTO, $oid, $orderInfo['wid'], '', 0, $orderInfo['uid'], $oid);
            }
        }

//        // 更新客户表的账单余额
//        if ($accountBalanceDiff != 0)
//        {
//            $cc = new Crm2_Customer();
//            $cid = $orderInfo['cid'];
//            $change = array('account_balance' => $accountBalanceDiff);
//            $cc->update($cid, array(), $change);
//        }
    }

    /**
     * 财务修改订单金额相关选项
     *
     * @param $oid
     * @param $modify
     */
    public static function updateOrderModify($oid, $modify=0)
    {
        $oo = new Order_Order();

        // 更新财务历史表
        $fmi = new Finance_Money_In();
        $accountBalanceDiff = 0;
        $orderInfo = $oo->get($oid);

        // 订单支付
        $history = $fmi->getByObjid($oid, Conf_Money_In::ORDER_PAIED);
        if (!empty($history))
        {
            $newPrice = $orderInfo['price'] + $orderInfo['freight'] + $orderInfo['customer_carriage'] - $orderInfo['privilege'];
            $amountDiff = $newPrice - abs($history['price']);

            if ($amountDiff <> 0)
            {
//                $upPriceWhere = 'id=' . $history['id'];
//                $fmi->update($upPriceWhere, array('price' => $newPrice), array());
//
//                $upAmountWhere = 'cid=' . $history['cid'] . ' and id>=' . $history['id'];
//                $fmi->update($upAmountWhere, array(), array('amount' => $amountDiff));

                $accountBalanceDiff += $amountDiff;
            }
        }

        // 更新客户表的账单余额
        if ($accountBalanceDiff != 0)
        {
            $cc = new Crm2_Customer();
            $cid = $orderInfo['cid'];
            $change = array('account_balance' => $accountBalanceDiff);
            $cc->update($cid, array(), $change);
        }
    }

    /**
     * 修改订单配送方式（自提/送货上门）
     *
     * @param $oid
     * @param $deliveryType
     * @param $staff
     */
    public static function changeDeliveryType($oid, $deliveryType, $staff)
    {
        $oo = new Order_Order();
        $orderInfo = $oo->get($oid);

        // 自提 && 未出库，直接将步骤更新为已经安排司机，到‘未出库’状态
        if ($deliveryType == Conf_Order::DELIVERY_BY_YOURSELF && $orderInfo['step'] < Conf_Order::ORDER_STEP_PICKED)
        {
            $step = Conf_Order::ORDER_STEP_HAS_DRIVER;
            self::forwardOrderStep($oid, $step, $staff);
        }
        // 重新安排司机
        else if ($deliveryType != Conf_Order::DELIVERY_BY_YOURSELF && $orderInfo['step'] < Conf_Order::ORDER_STEP_PICKED && empty($orderInfo['driver_phone']))
        {
            $step = Conf_Order::ORDER_STEP_BOUGHT;
            self::forwardOrderStep($oid, $step, $staff);
        }

        $upData['delivery_type'] = $deliveryType;
        self::updateOrderInfo($oid, $upData);
    }

    public static function getLatestSureTime($wid = 0)
    {
        if ($_SERVER['SERVER_ADDR'] == '127.0.0.1')
        {
            return 0;
        }

        $key = self::_getKey($wid);
        $time = Data_Memcache::getInstance()->get($key);

        return $time;
    }

    public static function setLatestSureTime($wid, $time = 0)
    {
        if ($_SERVER['SERVER_ADDR'] == '127.0.0.1')
        {
            return 0;
        }

        if (0 == $time)
        {
            $time = time();
        }

        //总体的
        $key = self::_getKey(0);
        Data_Memcache::getInstance()->set($key, $time, 86400 * 7);

        //该库的
        if ($wid > 0)
        {
            $key = self::_getKey($wid);
            Data_Memcache::getInstance()->set($key, $time, 86400 * 7);
        }
    }

    private static function _getKey($wid = 0)
    {
        $key = 'hc-max-oid';
        if ($wid > 0)
        {
            $key .= '-' . $wid;
        }

        return $key;
    }

    /**
     * 更新用户相关信息（支付完成时更新）
     *
     * @param $cid
     *
     * @throws Exception
     */
    public static function updateCustomerOrder($cid, $oid = 0)
    {
        // 新的累计核算逻辑 && 更新信息
//        $info = self::statisticsCustomerOrder($cid, $oid);
//        Crm2_Api::updateCustomerInfo($cid, $info);
        
        $cc = new Crm2_Customer();
        $consumeFromOrder = Crm2_Stat_Api::statOrderDatas4Customer($cid);
        $cc->update($cid, $consumeFromOrder);
    }

    /**
     * 计算用户订单信息
     * 包括订单总额，除砂石类总额，首单日期，第二单日期，订单数，最后下单日期
     *
     * @param $cid
     * @param $oid
     *
     * @return array
     * 
     * @notice
     *      请使用方法：Crm2_Stat_Api::statAllConsume4Customer
     *                Crm2_Stat_Api::statOrderDatas4Customer ...
     */
    public static function statisticsCustomerOrder($cid, $oid = 0)
    {
        $oo = new Order_Order();

        $orderList = $oo->getOrderListForStatistic($cid);

        $lastOrderDate = '0000-00-00';
        $orderNum = 0;
        $totalAmount = 0;
        $orderAmount = 0;
        $refundAmount = 0;
        $firstOrderDate = '0000-00-00';
        $sendOrderDate = '0000-00-00';

        $total = count($orderList);
        foreach ($orderList as $order)
        {
            $orderNum++;

            if ($order['paid'] != Conf_Order::UN_PAID || $order['oid'] == $oid)
            {
                $totalAmount += $order['price'] + $order['freight'] + $order['customer_carriage'] - $order['refund'] - $order['privilege'];
            }

            $refundAmount += $order['refund'];

            if ($orderNum == 1)
            {
                $firstOrderDate = date('Y-m-d', strtotime($order['ctime']));
            }
            if ($orderNum == 2)
            {
                $sendOrderDate = date('Y-m-d', strtotime($order['ctime']));
            }
            if ($orderNum == $total)
            {
                $lastOrderDate = date('Y-m-d', strtotime($order['ctime']));
            }
        }

        return array(
            'first_order_date' => $firstOrderDate,
            'second_order_date' => $sendOrderDate,
            'last_order_date' => $lastOrderDate,
            'order_num' => $orderNum,
            'order_amount' => $orderAmount,
            'total_amount' => $totalAmount,
            'refund_amount' => $refundAmount,
        );
    }

    /**
     * 退款单列表
     *
     * @param array $conf
     * @param int   $start
     * @param int   $num
     *
     * @return array
     */
    public static function getRefundList($conf, $start = 0, $num = 20, $status = '')
    {
        $or = new Order_Refund();
        $list = $or->getList($conf, $total, $start, $num, $status);
        $hasMore = $total > $start + $num;

        //客户cid转成客户信息customer
        $cc = new Crm2_Customer();
        $cc->appendInfos($list);

        //操作人信息
        $fieldNames = array(
            'suid',
            'received_suid',
            'shelved_suid'
        );
        Admin_Api::appendStaffSimpleInfo($list, $fieldNames);

        //补充订单信息
        $oo = new Order_Order();
        $oo->appendInfos($list);
        Order_Helper::formatRefunds($list);

        //总退款额
        $totalPrice = 0;
        if (!empty($conf['from_date']))
        {
            $totalPrice = $or->getTotalPrice($conf);
        }

        return array(
            'list' => $list,
            'total' => $total,
            'has_more' => $hasMore,
            'total_price' => $totalPrice
        );
    }

    /**
     * 退单的基础信息
     */
    public static function getBaseRefundInfo($rid)
    {
        $wio = new Order_Refund();
        
        return $wio->get($rid);
    }
    
    /**
     * 退款单详情
     *
     * @param $rid
     *
     * @return array
     */
    public static function getRefund($rid)
    {
        //获取退款单信息
        $wio = new Order_Refund();
        $info = $wio->get($rid);
        $info['_warehouse_name'] = isset(Conf_Warehouse::$WAREHOUSES[$info['wid']]) ? Conf_Warehouse::$WAREHOUSES[$info['wid']] : '';

        //补充订单信息
        $oid = $info['oid'];
        $oo = new Order_Order();
        $order = $oo->get($oid);

        //补充客户信息
        $cc = new Crm2_Customer();
        $info['_customer'] = $cc->get($order['cid']);

        //补充商品信息
        $or = new Order_Refund();
        $products = $or->getProductsOfRefund($rid);
        $pids = Tool_Array::getFields($products, 'pid');
        $productInfos = Shop_Api::getProductInfos($pids, Conf_Activity_Flash_Sale::PALTFORM_BOTH, TRUE);
        foreach ($products as &$product)
        {
            $pid = $product['pid'];
            $product = array_merge($product, $productInfos[$pid]);
        }

        $fieldNames = array(
            'suid',
            'received_suid',
            'shelved_suid',
            'verify_suid'
        );
        $moreInfo = array($info);
        Admin_Api::appendStaffSimpleInfo($moreInfo, $fieldNames);

        $refund = array(
            'info' => $moreInfo[0],
            'products' => $products,
            'order' => $order
        );

        return $refund;
    }

    /**
     * 添加退款单
     *
     * @param       $oid
     * @param array $info
     * @param array $products
     *
     * @return mixed
     * @throws Exception
     */
    public static function addRefund($oid, $info, $products = array())
    {
        assert($oid > 0);
        $info['oid'] = $oid;

        // 客户id
        $oo = new Order_Order();
        $order = $oo->get($oid);
        $info['cid'] = $order['cid'];
        $info['uid'] = $order['uid'];
        $info['city_id'] = $order['city_id'];

        //判断异库退货
        if($order['city_id'] == Conf_City::CHONGQING && $info['wid'] != $order['wid'])
        {
            throw new Exception('不能异库退货，有疑问找申雪婷！');
        }
        //检查条件
        self::_ifCanRefund($oid, 0, $products);

        //添加基本信息
        $or = new Order_Refund();
        $rid = $or->add($info);

        //添加商品列表
        assert(!empty($products));
        $or->updateRefundProducts($rid, $products, $info['wid']);

        self::_updateRefundTotalPrice($rid);

        return $rid;
    }

    public static function addRefund4Exchanged($oid, $info, $products = array())
    {
        assert($oid > 0);
        $info['oid'] = $oid;

        // 客户id
        $oo = new Order_Order();
        $order = $oo->get($oid);
        $info['cid'] = $order['cid'];
        $info['uid'] = $order['uid'];
        $info['city_id'] = $order['city_id'];

        //判断异库退货
        if($order['city_id'] == Conf_City::CHONGQING && $info['wid'] != $order['wid'])
        {
            throw new Exception('不能异库退货，有疑问找申雪婷！');
        }
        if($info['rel_type'] == Conf_Refund::REFUND_REL_TYPE_EXCHANGED){
            $exchangedInfo = Exchanged_Api::getExchanged($info['rel_oid']);
            if($exchangedInfo['info']['need_storage'] == 1)
            {
                //检查条件
                self::_ifCanRefund($oid, 0, $products);
            }
        }

        //添加基本信息
        $or = new Order_Refund();
        $rid = $or->add($info);

        //添加商品列表
        assert(!empty($products));
        $or->updateRefundProducts($rid, $products, $info['wid']);

        self::_updateRefundTotalPrice($rid);

        return $rid;
    }

    /**
     * 更新退款单
     *
     * @param       $rid
     * @param array $info
     * @param array $products
     *
     * @return bool
     * @throws Exception
     */
    public static function updateRefund($rid, $info, $products = array())
    {
        $oid = $info['oid'];
        assert($oid > 0);
        $oo = new Order_Order();
        $order = $oo->get($oid);
        //判断异库退货
        if($order['city_id'] == Conf_City::CHONGQING && isset($info['wid']) && $info['wid'] != $order['wid'])
        {
            throw new Exception('不能异库退货，有疑问找申雪婷！');
        }

        if (!empty($products))
        {
            self::_ifCanRefund($oid, $rid, $products);
        }

        $or = new Order_Refund();
        if (!empty($info))
        {
            $or->update($rid, $info);
        }

        if (!empty($products))
        {
            $or->updateRefundProducts($rid, $products, $info['wid']);
            self::_updateRefundTotalPrice($rid);
        }

        return TRUE;
    }

    /**
     * 更新退款单进度
     *
     * @param     $staff
     * @param     $rid
     * @param     $nextStep
     * @param int $refund2Balance
     * @param     $adjust
     * @param     $optype        操作类型，eg：finance-财务退款
     * @param     $unStockinPids 不入库-不上架的商品id列表
     *
     * @throws Exception
     */
    public static function updateRefundStep($staff, $rid, $nextStep, $refund2Balance = 0, $adjust = 0, $optype = '', $unStockinPids = array())
    {
        $or = new Order_Refund();
        $refund = $or->get($rid);
        $step = $refund['step'];
        $suid = $staff['suid'];

        $wid = $refund['wid'];

        //检查用户是否能操作这个修改
        $stepRes = Order_Helper::getRefundNextStep($staff, $step);
        if ($stepRes['next_step'] != $nextStep)
        {
            throw new Exception('common:permission denied');
        }

        if ($optype == 'finance')
        {
            if ($step < Conf_Refund::REFUND_STEP_IN_STOCK)
            {
                throw new Exception('退货单未入库，不能退款！');
            }
            if ($refund['paid'] != Conf_Refund::UN_PAID)
            {
                throw new Exception('订单已退款，请不用重复操作！');
            }
        }

        // 未升级的库，不走该逻辑
        if (!Conf_Warehouse::isUpgradeWarehouse($wid))
        {
            $unStockinPids = array();
        }

        // 如果入库, 则直接加库存 - 退货入库
        if ($nextStep == Conf_Refund::REFUND_STEP_IN_STOCK && $step < Conf_Refund::REFUND_STEP_IN_STOCK)
        {
            $ws = new Warehouse_Stock();
            $wsh = new Warehouse_Stock_History();
            $sp = new Shop_Product();
            $wl = new Warehouse_Location();

            $or = new Order_Refund();
            $products = $or->getProductsOfRefund($rid);

            if (empty($refund['wid']))
            {
                throw new Exception('order:empty wid');
            }

            //获取原库存
            $sids = array();
            foreach ($products as &$p)
            {
                if (empty($p['sid'])) //防止t_order_product sid 为0
                {
                    $pinfo = $sp->get($p['pid']);
                    $sids[] = $pinfo['sid'];
                    $p['sid'] = $pinfo['sid'];
                }
                else
                {
                    $sids[] = $p['sid'];
                }
            }
            $oldStocks = Tool_Array::list2Map($ws->getBulk($wid, $sids), 'sid');

            $location = Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_ORDER_REFUND]['flag'];

            $unStockinNum = 0;
            foreach ($products as $product)
            {
                $sid = $product['sid'];
                $pid = $product['pid'];
                // 砂石砖类不减库存, 也不留库存记录 [老版库逻辑]
                if ($product['num'] <= 0 || (in_array($pid, Conf_Order::$SAND_CEMENT_BRICK_PIDS) && $wid == Conf_Warehouse::WID_3))
                {
                    continue;
                }

                // 不入库不上架的商品处理
                if (in_array($product['pid'], $unStockinPids))
                {
                    //模拟上架
                    $rpwhere = 'status=0 and oid=' . $product['oid'] . ' and pid=' . $product['pid'] . ' and rid=' . $product['rid'];
                    $or->updateRefundProductByWhere($rpwhere, array('location' => '不入库-不上架'));

                    //模拟已做采购单
                    $pwhere = 'status=0 and rid=0 and oid=' . $product['oid'] . ' and pid=' . $product['pid'];
                    $pChgData = array(
                        'tmp_bought_num' => $product['num'],
                        'tmp_inorder_num' => $product['num']
                    );
                    $or->updateRefundProductByWhere($pwhere, array(), $pChgData);

                    $unStockinNum++;
                    continue;
                }

                //入库
                $change = array('num' => $product['num']);
                $ws->save($wid, $sid, array(), $change);

                // 保存历史记录
                $history = array(
                    'old_num' => isset($oldStocks[$sid]) ? $oldStocks[$sid]['num'] : 0,
                    'old_occupied' => isset($oldStocks[$sid]) ? $oldStocks[$sid]['occupied'] : 0,
                    'num' => $product['num'],
                    'occupied' => 0,
                    'iid' => $rid,
                    'suid' => $suid,
                    'type' => Conf_Warehouse::STOCK_HISTORY_REFUND_IN,
                );
                $wsh->add($wid, $sid, $history);

                // 新库逻辑：入库商品放到虚拟货位，等待上架
                if (Conf_Warehouse::isUpgradeWarehouse($wid))
                {
                    if ($product['num'] > 0)
                    {
                        $wl->add($location, $wid, $product['sid'], $product['num']);
                    }
                }
            }

            //更改订单refund字段
            self::updateOrderInfo($refund['oid'], array(), array('refund' => $refund['price']));
        }

        //退款
        if ($refund2Balance && $optype == 'finance')
        {
            self::_checkRefund($refund, $adjust);
        }

        //执行更新
        $info = array(
            'step' => $nextStep,
            'oid' => $refund['oid']
        );
        if ($nextStep == Conf_Refund::REFUND_STEP_IN_STOCK && $step < Conf_Refund::REFUND_STEP_IN_STOCK)
        {
            // 不入库的数量等于退单中商品的数量，修改订单状态为【已上架】
            if ($unStockinNum == count($products))
            {
                $info['step'] = Conf_Refund::REFUND_STEP_SHELVED;
            }
            $info['received_suid'] = $suid;
        }

        if ($optype == 'finance')
        { //退款，只更新paid字段
            $info = array(
                'paid' => Conf_Refund::HAD_PAID,
                'oid' => $refund['oid']
            );
        }
        self::updateRefund($rid, $info);

        //退款
        $refundPrice = 0;
        if ($optype == 'finance')
        {
            $refundPrice = self::_paidRefund($refund, $staff, $refund2Balance, $adjust);
        }

        if ($nextStep == Conf_Refund::REFUND_STEP_SURE)
        {
            //订单操作日志
            Admin_Api::addOrderActionLog($staff['suid'], $refund['oid'], Conf_Order_Action_Log::ACTION_AGREE_REFUND_ORDER);
        }
        else if ($nextStep == Conf_Refund::REFUND_STEP_IN_STOCK)
        {
            //订单操作日志
            Admin_Api::addOrderActionLog($staff['suid'], $refund['oid'], Conf_Order_Action_Log::ACTION_PUT_IN_REFUND_ORDER);
        }
        else if ($optype == 'finance')
        {
            //订单操作日志
            $param = array(
                'balance' => $refund2Balance ? 'Y' : 'N',
                'adjust' => $refundPrice / 100,
            );
            Admin_Api::addOrderActionLog($staff['suid'], $refund['oid'], Conf_Order_Action_Log::ACTION_PAY_REFUND_ORDER, $param);
        }
    }

    /**
     * 删除退款单
     *
     * @notice: 【不推荐使用】请使用self::deleteRefundNew
     * 
     * @param $rid
     *
     * @return bool
     */
    public static function deleteRefund($rid)
    {
        // 删除退款单表
        $or = new Order_Refund();

        return $or->delete($rid);
    }
    
    /**
     * 删除退单 - 含处理空退商品的数量变更.
     * 
     * @param int $rid
     * @param int $oid
     */
    public static function deleteRefundNew($rid, $oid)
    {
        $or = new Order_Refund();
        $refundInfo = $or->get($rid);
        
        if (empty($refundInfo) || $refundInfo['oid']!=$oid || $refundInfo['status']!=Conf_Base::STATUS_NORMAL)
        {
            throw new Exception('delete refund: refund order Detail Error!');
        }
        if ($refundInfo['step'] > Conf_Refund::REFUND_STEP_SURE)
        {
            throw new Exception('delete refund: step Error!');
        }

        $orderInfo = self::getAfterSaleOrder($rid, Conf_Order::AFTERSALE_TYPE_REFUND);
        if(!empty($orderInfo))
        {
            $orderInfo = current($orderInfo);
            if($orderInfo['step'] >= Conf_Order::ORDER_STEP_PICKED)
            {
                throw new Exception('补单：'.$orderInfo['oid'].' 已经出库，不能删除');
            }
            self::deleteOrder($orderInfo['oid']);
        }
        
        // 空退：释放占用的空退数量
        $thisRefundProducts = array();
        if ($refundInfo['type'] == Conf_Refund::REFUND_TYPE_VIRTUAL)
        {
            $oo =  new Order_Order();
            $allOrderProducts = $oo->getProductsOfOrder($oid, true);
            
            $orderProducts = $thisRefundProducts = array();
            
            foreach($allOrderProducts as $item)
            {
                if ($item['rid'] == 0)
                {
                    $orderProducts[$item['pid']] = $item['refund_vnum'];
                }
                else if ($item['rid'] == $rid)
                {
                    $thisRefundProducts[$item['pid']] = $item['num'];
                }
            }
            
            foreach($thisRefundProducts as $pid => $rNum)
            {
                if (!array_key_exists($pid, $orderProducts) || $rNum>$orderProducts[$pid])
                {
                    throw new Exception('空退数量异常！请联系技术人员查询！');
                }
            }
        }
        // 删除退单
        $or->delete($rid);
        //删除补单
        
        // 释放空退占用的商品
        foreach($thisRefundProducts as $pid => $rNum)
        {
            $oo->updateOrderProductInfo($oid, $pid, 0, array(), array('refund_vnum'=>0-$rNum));
        }
    }

    /**
     * 驳回退款单
     *
     * @param $rid
     *
     * @return bool
     */
    public static function rebutRefund($rid)
    {
        // 驳回退款单表
        $or = new Order_Refund();

        return $or->rebut($rid);
    }

    /**
     * 取销售单商品详情
     *
     * @param date $bdate       日期 'YYYY-MM-DD'
     * @param date $edate       日期 'YYYY-MM-DD'
     * @param int  $btype       采购类型 {0: 全部; 1: 实采; 2: 空采}
     * @param int  $pSearchCate 商品检索分类 {0: 全部; 1: 沙子水泥砖; 3: 其他}
     * @param int  $start       开始位置
     * @param int  $num         每页数量
     * @param int  $wid         仓库
     *
     * @return array()
     */
    public static function getSaleProductDetails($bdate, $edate, $btype, $pSearchCate, $wid = 0, $start = 0, $num = 20)
    {
        $oo = new Order_Order();
        $ss = new Shop_Sku();

        $specialPids = implode(',', Conf_Order::$SAND_CEMENT_BRICK_PIDS);
        $_bdate = $bdate . ' 00:00:00';
        $_edate = $edate . ' 23:59:59';
        $where = "oid in (select oid from t_order where step>=" . Conf_Order::ORDER_STEP_PICKED . " and delivery_date>='$_bdate' and delivery_date<='$_edate')";
        $where .= $pSearchCate == 1 ? ' and pid in (' . $specialPids . ') ' : ($pSearchCate == 2 ? ' and pid not in (' . $specialPids . ') ' : '');
        $where .= $btype == 1 ? ' and vnum=0' : ($btype == 2 ? ' and vnum!=0' : '');
        if (!empty($wid))
        {
            if (is_array($wid))
            {
                $where .= sprintf(' and wid in (%s)', implode(',', $wid));
            }
            else
            {
                $where .= ' and wid=' . $wid;
            }
        }
        $where .= ' and rid=0 and status=0';
        $opDatas = $oo->getOrderProductsByWhere($where, $start, $num);

        if ($opDatas['total'] != 0)
        {
            $skuids = Tool_Array::getFields($opDatas['data'], 'sid');
            $sDatas = Tool_Array::list2Map($ss->getBulk($skuids), 'sid');

            foreach ($opDatas['data'] as &$one)
            {
                $_sid = $one['sid'];
                $one['title'] = $sDatas[$_sid]['title'];
                $one['pic'] = Data_Pic::getPicUrl($sDatas[$_sid]['pic_ids'], 'middle');
                $one['unit'] = !empty($sDatas[$_sid]['unit'])? $sDatas[$_sid]['unit']: '个';    //单位
            }
        }

        return $opDatas;
    }

    /**
     * 销售汇总
     *
     * @param     $bdate
     * @param     $edate
     * @param     $btype
     * @param     $pSearchCate
     * @param     $wid
     * @param int $start
     * @param int $num
     *
     * @return array
     */
    public static function getSaleProductsSummary($bdate, $edate, $btype, $pSearchCate, $wid, $start = 0, $num = 20)
    {
        $oo = new Order_Order();
        $sp = new Shop_Product();
        $ss = new Shop_Sku();

        $specialPids = implode(',', Conf_Order::$SAND_CEMENT_BRICK_PIDS);
        $_bdate = $bdate . ' 00:00:00';
        $_edate = $edate . ' 23:59:59';
        $where = "oid in (select oid from t_order where step>=" . Conf_Order::ORDER_STEP_SURE . " and delivery_date>='$_bdate' and delivery_date<='$_edate')";
        $where .= $pSearchCate == 1 ? ' and pid in (' . $specialPids . ') ' : ($pSearchCate == 2 ? ' and pid not in (' . $specialPids . ') ' : '');
        $where .= $btype == 1 ? ' and vnum=0' : ($btype == 2 ? ' and vnum!=0' : '');
        if (!empty($wid))
        {
            if (is_array($wid))
            {
                $where .= sprintf(' and wid in (%s)', implode(',', $wid));
            }
            else
            {
                $where .= ' and wid=' . $wid;
            }
        }
        $where .= ' and rid=0 and status=0';
        $field = array(
            '*',
            'sum(num) as total_num',
            'sum(vnum) as total_vnum',
            'count(1) as total_order'
        );
        $opDatas = $oo->getOrderProductsByWhere($where, $start, $num, 'pid', array(
            'pid',
            'desc'
        ), $field);

        if ($opDatas['total'] != 0)
        {
            $pids = Tool_Array::getFields($opDatas['data'], 'pid');
            $pDatas = Tool_Array::list2Map($sp->getBulk($pids), 'pid');
            $skuids = Tool_Array::getFields($pDatas, 'sid');
            $sDatas = Tool_Array::list2Map($ss->getBulk($skuids), 'sid');

            foreach ($opDatas['data'] as &$one)
            {
                $_sid = $one['sid'];
                $one['title'] = $sDatas[$_sid]['title'];
                $one['pic'] = Data_Pic::getPicUrl($sDatas[$_sid]['pic_ids'], 'middle');
                $one['unit'] = $sDatas[$_sid]['unit'];    //单位
            }
        }

        return $opDatas;
    }

    public static function balancePay($oid, $amount)
    {
        $oo = new Order_Order();
        $order = $oo->get($oid);

        // 写客户应收明细
        $cid = $order['cid'];
        $uid = $order['uid'];
        $type = Conf_Money_In::FINANCE_INCOME;
        $price = $amount;
        $wid = $order['wid'];
        $note = '余额支付';
        $payType = Conf_Base::PT_BALANCE;
        Finance_Api::addMoneyInHistory($cid, $type, $price, Conf_Admin::ADMINOR_AUTO, $oid, $wid, $note, $payType, $uid, $oid);

        // 写客户账户余额
        $saveData = array(
            'type' => Conf_Finance::CRM_AMOUNT_TYPE_PAID,
            'price' => 0 - abs($price),
            'objid' => $oid,
            'payment_type' => $payType,
            'note' => '余额支付订单：' . $oid,
            'suid' => Conf_Admin::ADMINOR_AUTO,
            'uid' => $order['uid'],
            'oid' => $oid,
        );

        Finance_Api::addCustomerAmountHistory($cid, $saveData);
    }

    public static function getOrderPayInfo($oid, $uid = 0)
    {
        $order = Order_Api::getOrderInfo($oid);
        $userNeedToPay = $order['user_need_to_pay'];
        $orderProducts = Order_Api::getOrderProducts($oid);
        $orderProducts = Privilege_Api::getRealBuyProducts($orderProducts['products'], $oid, $activity_products_amount);
        if ($order['paid'] == Conf_Order::UN_PAID)
        {
            $activityProducts = Privilege_Api::getActivityProducts($oid);
            $promotionPrivilege = Privilege_2_Api::computePromotionPrivilege($order['cid'], $orderProducts, $order, TRUE, $activityProducts);
            if ($promotionPrivilege['total_privilege'] > 0)
            {
                $userNeedToPay += $order['privilege'];
                $userNeedToPay -= $promotionPrivilege['total_privilege'];
            }
        }

        if ($uid <= 0)
        {
            return array(
                'account_amount' => 0,
                'user_need_to_pay' => $userNeedToPay,
                'is_franchiess' => $order['is_franchiess'],
            );
        }

        $uid2 = $uid ? $uid : $order['uid'];
        $userInfo = Crm2_Api::getUserInfo($uid2);
        $accountAmount = $userInfo['user']['is_admin'] ? $userInfo['customer']['account_amount'] : 0;

        return array(
            'account_amount' => $accountAmount,
            'user_need_to_pay' => $userNeedToPay,
            'is_franchiess' => $order['is_franchiess'],
        );
    }

    public static function refundAndDelete($oid, $staff)
    {
        $oo = new Order_Order();
        $orderInfo = $oo->get($oid);
        
        Order_Helper::canDealOrder($orderInfo, $staff);
        
        if ($orderInfo['status'] != Conf_Base::STATUS_NORMAL || $orderInfo['paid'] == Conf_Order::UN_PAID 
            || $orderInfo['step'] >= Conf_Order::ORDER_STEP_PICKED || $orderInfo['real_amount'] <= 0)
        {
            throw new Exception('order:invalid status');
        }

        if ($orderInfo['step'] >= Conf_Order::ORDER_STEP_SURE && empty($orderInfo['aftersale_type']))
        {
            throw new Exception('请回滚订单，到客服未确认状态在操作');
        }
        
        //检测客户应收明细
        $fmi = new Finance_Money_In();
        $moneyInDatas = $fmi->getByObjidAllData($oid, Conf_Money_In::FINANCE_INCOME);
        if (empty($moneyInDatas))
        {
            throw new Exception('该订单财务流水异常，删除失败！');
        }
        
        //客户支付金额校验（流水累计==订单实付金额）
        $hadPaidAmount = 0;
        foreach($moneyInDatas as $item)
        {
            $hadPaidAmount += abs($item['price']);
        }
        if ($hadPaidAmount != $orderInfo['real_amount'])
        {
            throw new Exception('订单实收和流水实收不一致，请查询');
        }
        
        // 检查是否做删除退预付
        $mi = new Finance_Customer_Amount();
        $where = sprintf('status=0 and objid=%d AND type=%d', $oid, Conf_Finance::CRM_AMOUNT_TYPE_PREPAY);
        $customerAmountNum = $mi->getTotalByWhere($where);
        if ($customerAmountNum > 0)
        {
            throw new Exception('已存在退入余额的记录，请查证');
        }
        
        // 删除订单，订单商品
        $oo->delete($oid);

        // 释放一个订单的优惠券
        Coupon_Api::releaseCoupon($oid);

        //删除销售优惠
        if ($orderInfo['sale_privilege'] > 0)
        {
            $preferentialDao = new Order_Sale_Preferential();
            $preferentialInfo = $preferentialDao->getItem($oid);
            if(!empty($preferentialInfo))
            {
                $preferentialDao->delete($oid);
            }
        }
        
        //冲抵客户流水
        $note = '删除订单并退款：oid: '. $oid;
        Finance_Api::addMoneyInHistory($orderInfo['cid'], Conf_Money_In::ORDER_DEL_REFUND, 
                    $hadPaidAmount, Conf_Admin::ADMINOR_AUTO, $oid, $orderInfo['wid'], $note, 
                    Conf_Base::PT_BALANCE, $orderInfo['uid'], $oid);
        
        //转客户余额
        // 加盟商订单，不写余额
        if (Order_Helper::isFranchiseeOrder($orderInfo['wid'], $orderInfo['city_id'])) return;

        $miData = array(
            'cid' => $orderInfo['cid'],
            'objid' => $oid,
            'oid' => $oid,
            'uid' => $orderInfo['uid'],
            'type' => Conf_Finance::CRM_AMOUNT_TYPE_DEL_REFUND,
            'price' => $hadPaidAmount,
            'payment_type' => Conf_Base::PT_BALANCE,
            'note' => $note,
            'suid' => Conf_Admin::ADMINOR_AUTO,
            'ctime' => date('Y-m-d H:i:s'),
        );
        $customerAmountRet = $mi->save($miData);
        if (!empty($customerAmountRet['data']['amount']))
        {
            $cc = new Crm2_Customer();
            $cc->update($orderInfo['cid'], array('account_amount'=>$customerAmountRet['data']['amount']));
        }
        
        //售后订单，释放占用
        if ($orderInfo['step'] >= Conf_Order::ORDER_STEP_SURE && !empty($orderInfo['aftersale_type']))
        {
            $do_status = true;
            if($orderInfo['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_EXCHANGED)
            {
                $exchangedInfo = Exchanged_Api::getExchanged($orderInfo['aftersale_id']);
                if($exchangedInfo['info']['need_storage'] == 0)
                {
                    $do_status = false;
                }
            }elseif($orderInfo['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_TRAPS){
                $trapsInfo = Traps_Api::getTraps($orderInfo['aftersale_id']);
                if($trapsInfo['info']['need_storage'] == 0)
                {
                    $do_status = false;
                }
            }
            // 释放一个订单占用的库存
            $do_status && self::_releaseOccupied($oid, $orderInfo['wid']);
        }
    }

    /**
     * 在线支付
     *
     * @param     $oid
     * @param     $amount
     * @param     $paymentType
     * @param int $useBalance
     *
     * @return array
     */
    public static function onlinePayOrder($oid, $amount, $paymentType, $useBalance = 0)
    {
        $orderInfo = Order_Api::getOrderInfo($oid);

        if ($orderInfo['paid'] != Conf_Order::HAD_PAID)
        {
            //计算优惠-如果发现订单的实收大于0，则认为订单之前被支付过一次
            //已经享受过优惠，就没有优惠了
            $privilege = 0;
            $orderProducts = Order_Api::getOrderProducts($oid);
            $activityProducts = Privilege_Api::getActivityProducts($oid);
            $privilege3 = Privilege_2_Api::savePromotionPrivilege($orderInfo['cid'], $orderProducts['products'], $orderInfo, TRUE, $activityProducts);

            if ($privilege3['manjian_privilege'] > $privilege)
            {
                $privilege += $privilege3['total_privilege'];
            }
            else
            {
                $privilege += ($privilege3['total_privilege'] - $privilege3['manjian_privilege']);
            }
            if ($privilege3['total_privilege'] > 0)
            {
                $privilege -= $orderInfo['privilege'];
            }

            //如果使用余额
            $balanceAmount = 0;
            if ($useBalance)
            {
                $customer = Crm2_Api::getCustomerInfo($orderInfo['cid'], FALSE, FALSE);
                $balance = $customer['customer']['account_amount'];
                //应该使用的余额 = 还需支付金额 - 在线付金额 - 优惠
                $balanceAmount = $orderInfo['user_need_to_pay'] - $amount - $privilege;
                //最多可用用户现有余额
                if ($balanceAmount > $balance)
                {
                    $balanceAmount = $balance;
                }
                if ($balanceAmount < 0)
                {
                    $balanceAmount = 0;
                }
            }

            $info = array(
                'payment_type' => $paymentType,
                'real_amount' => $orderInfo['real_amount'] + $amount + $balanceAmount,
                'privilege' => $orderInfo['privilege'] + $privilege,
                'customer_payment_type' => Conf_Base::CUSTOMER_PT_ONLINE_PAY,
            );

            //订单操作日志应收：{needToPay}，实收：{realAmount}，抹零：{change}，坏账：{$badLoans}
            $param = array(
                'needToPay' => $orderInfo['user_need_to_pay'] / 100,
                'realAmount' => ($amount + $balanceAmount) / 100,
                'change' => 0,
                'badLoans' => 0,
                'type' => Conf_Base::$PAYMENT_TYPES[$info['payment_type']],
            );
            Admin_Api::addOrderActionLog(999, $oid, Conf_Order_Action_Log::ACTION_RECEIPT, $param);

            //是否已完成付款
            if ($amount + $privilege + $balanceAmount >= $orderInfo['user_need_to_pay'])
            {
                $info['paid'] = Conf_Order::HAD_PAID;
            }
            else
            {
                $info['paid'] = Conf_Order::PART_PAID;
            }
            if ($privilege != 0)
            {
                Order_Api::updateOrderByFinanceModify($oid, $orderInfo['freight'], $orderInfo['privilege'] + $privilege, $orderInfo['customer_carriage']);
            }

            Order_Api::updateOrderInfo($oid, $info);
//            // 写财务流水 - 销售单
//            Finance_Api::addMoneyInHistory($orderInfo['cid'], Conf_Money_In::ORDER_PAIED, $orderInfo['user_need_to_pay'], Conf_Admin::ADMINOR_AUTO, $oid, $orderInfo['wid'], '', 0, $orderInfo['uid'], $oid);
            //使用余额，扣除余额，写流水
            if ($useBalance && $balanceAmount > 0)
            {
                Order_Api::balancePay($oid, $balanceAmount);
            }
            // 写财务流水 - 财务收款
            if ($paymentType == Conf_Base::PT_ALIPAY)
            {
                Finance_Api::addMoneyInHistory($orderInfo['cid'], Conf_Money_In::FINANCE_INCOME, $amount, Conf_Admin::ADMINOR_AUTO, $oid, $orderInfo['wid'], '客户支付宝在线支付', Conf_Base::PT_ALIPAY, $orderInfo['uid'], $oid);
            }
            else if ($paymentType == Conf_Base::PT_WEIXIN_ONLINE)
            {
                Finance_Api::addMoneyInHistory($orderInfo['cid'], Conf_Money_In::FINANCE_INCOME, $amount, Conf_Admin::ADMINOR_AUTO, $oid, $orderInfo['wid'], '客户微信在线支付', Conf_Base::PT_WEIXIN_ONLINE, $orderInfo['uid'], $oid);
            }
            else if ($paymentType == Conf_Base::PT_CREDIT_PAY)
            {
                Finance_Api::addMoneyInHistory($orderInfo['cid'], Conf_Money_In::FINANCE_INCOME, $amount, Conf_Admin::ADMINOR_AUTO, $oid, $orderInfo['wid'], '客户金融账户支付', Conf_Base::PT_CREDIT_PAY, $orderInfo['uid'], $oid);
            }
            else
            {
                Finance_Api::addMoneyInHistory($orderInfo['cid'], Conf_Money_In::FINANCE_INCOME, $amount, Conf_Admin::ADMINOR_AUTO, $oid, $orderInfo['wid'], '客户微信App支付', Conf_Base::PT_WEIXIN_APP, $orderInfo['uid'], $oid);
            }

            //微信通知
            WeiXin_Message_Api::sendOrderPaySuccMessage($orderInfo['uid'], $orderInfo['oid']);
        }

        return $info;
    }

    public static function getProductByOids($oids, $fields = array('*'))
    {
        $oo = new Order_Order();

        return $oo->getProductsByOids($oids, array(), $fields);
    }

    /**
     * 检测重复订单
     * 规则：取前一天0点到现在，最多3个单子，然后对比单子商品；
     * 如果所有商品都一样（暂时忽略数量）则认为单子有可能重复；
     *
     * @param $oid
     */
    public static function checkDuplicate($oid)
    {
        //订单信息
        $orderInfo = self::getOrderInfo($oid);
        if (empty($orderInfo))
        {
            return -1;
        }

        $oo = new Order_Order();

        //订单商品
        $orderProducts = $oo->getProductsOfOrder($oid);
        $curPids = Tool_Array::getFields($orderProducts, 'pid');
        if (empty($orderProducts))
        {
            return -2;
        }

        //昨天0点到现在，最多三个订单
        $cid = $orderInfo['cid'];
        $where = sprintf('cid=%d AND oid !=%d AND status=%d AND step>=%d AND date(ctime)>="%s"', $cid, $oid, Conf_Base::STATUS_NORMAL, Conf_Order::ORDER_STEP_NEW, date('Y-m-d', strtotime('yesterday')));
        $orderList = $oo->getListRawWhere($where, $total, array(
            'oid',
            'desc'
        ), 0, 3, array('oid'));
        if (empty($orderList))
        {
            return -3;
        }

        //待对比订单的商品
        $oids = Tool_Array::getFields($orderList, 'oid');
        $products = $oo->getProductsByOids($oids, array(
            'oid',
            'desc'
        ), array(
                                               'oid',
                                               'pid'
                                           ));
        if (empty($products))
        {
            return -4;
        }

        //格式化
        $orderProductsCheck = array();
        foreach ($products as $product)
        {
            $oid = $product['oid'];
            $orderProductsCheck[$oid][] = $product['pid'];
        }

        foreach ($orderProductsCheck as $oid => $pids)
        {
            $diff1 = array_diff($curPids, $pids);
            $diff2 = array_diff($pids, $curPids);

            if (empty($diff1) && empty($diff2))
            {
                return $oid;
            }
        }

        return -5;
    }

    public static function hasWeixingProduct($productList)
    {
        foreach ($productList as $p)
        {
            if (empty($p['sku']['bid']))
            {
                continue;
            }

            if ($p['sku']['bid'] == 132)
            {
                return TRUE;
            }
        }

        return FALSE;
    }

    public static function checkSupplementList($oid)
    {
        //		$oo = new Order_Order();
        //		$order = $oo->get($oid);
        //		if (!Conf_Warehouse::isUpgradeWarehouse($order['wid']))
        //		{
        //			return 0;
        //		}

        $oo = new Order_Order();
        $where = sprintf('source_oid=%d AND status=%d AND step>=%d', $oid, Conf_Base::STATUS_NORMAL, Conf_Order::ORDER_STEP_NEW);
        $list = $oo->getListRawWhereWithoutTotal($where, '');

        $subOid = 0;

        // 判断补单是不是全部出库
        $isCommplatePicked = TRUE;
        foreach ($list as $_oinfo)
        {
            if ($_oinfo['step'] < Conf_Order::ORDER_STEP_PICKED)
            {
                $isCommplatePicked = FALSE;
                break;
            }
        }
        if ($isCommplatePicked)
        {
            return $subOid;
        }

        $oids = Tool_Array::getFields($list, 'oid');
        $products = $oo->getProductsByOids($oids);
        if (!empty($products))
        {
            foreach ($products as $p)
            {
                if ($p['picked'] != $p['num'] - $p['vnum'])
                {
                    $subOid = $p['oid'];
                    break;
                }
            }
        }

        return $subOid;
    }

    public static function appendProductsImages(&$list)
    {
        if (!empty($list))
        {
            $oids = Tool_Array::getFields($list, 'oid');
            $pro = Order_Api::getPidsbyOids($oids);
            $spids = array_unique(Tool_Array::getFields($pro, 'pid'));
            $proInfo = Shop_Api::getProductInfos($spids, Conf_Activity_Flash_Sale::PALTFORM_WECHAT, true);
            $products = array();
            foreach ($pro as $v)
            {
                $products[$v['oid']][] = array(
                    'oid' => $v['oid'],
                    'pid' => $v['pid'],
                    'imgurl' => $proInfo[$v['pid']]['sku']['_pic']['middle'],
                );
            }

            foreach ($list as &$info)
            {
                $info['product_list'] = array_slice($products[$info['oid']], 0, 4);
            }
        }
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////私有方法///////////////////////////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * 客服确认时的库存变化
     *
     * @param       $wid
     * @param       $oid
     * @param array $products
     */
    private static function _checkUpdateStockOccupied($wid, $oid, &$products, $oldOrder)
    {
        if (empty($products))
        {
            return;
        }

        $ws = new Warehouse_Stock();
        $oo = new Order_Order();
        $sp = new Shop_Product();

        //获取原库存
        $sids = array();
        $productsIdxBySid = array();
        foreach ($products as &$p)
        {
            if (empty($p['sid'])) //防止t_order_product sid 为0
            {
                $pinfo = $sp->get($p['pid']);
                $sids[] = $pinfo['sid'];
                $p['sid'] = $pinfo['sid'];
            }
            else
            {
                $sids[] = $p['sid'];
            }
            $productsIdxBySid[$p['sid']] = $p;
        }

        // 新库逻辑
        if (Conf_Warehouse::isUpgradeWarehouse($wid))
        {
            $wl = new Warehouse_Location();
            $productWithLocAndVnum = Warehouse_Location_Api::distributeNumFromLocation($products, $wid, 1, TRUE);

            foreach ($productWithLocAndVnum as $sid => $lvInfo)
            {
                $pid = $productsIdxBySid[$sid]['pid'];

                // t_order_prodouct 标记货位，空采数量
                $opUpdata = array();
                $opUpdata['location'] = !empty($lvInfo['loc']) ? $lvInfo['loc'] : '';
                $opUpdata['vnum'] = !empty($lvInfo['vnum']) ? $lvInfo['vnum'] : 0;

                //自动拣货
                if (Order_Picking_Api::isAutoPicked($wid, $pid))
                {
                    $opUpdata['picked'] = $productsIdxBySid[$sid]['num'] - $opUpdata['vnum'];
                    if ($opUpdata['picked'] > 0)
                    {
                        $opUpdata['picked_time'] = date('Y-m-d H:i:s');
                    }
                }
                //占用库存小于捡货数量时picked置成0
                else if ($productsIdxBySid[$sid]['picked'] > 0)
                {
                    $occupied_num = 0;
                    foreach ($lvInfo['raw_loc'] as $locInfo)
                    {
                        $occupied_num += $locInfo['num'];
                    }
                    if ($occupied_num < $productsIdxBySid[$sid]['picked'])
                    {
                        $opUpdata['picked'] = 0;
                    }
                }

                $oo->updateOrderProductInfo($oid, $productsIdxBySid[$sid]['pid'], 0, $opUpdata);

                // t_sku_2_location 更新占用
                if (!empty($lvInfo['raw_loc']))
                {
                    foreach ($lvInfo['raw_loc'] as $rawLoc)
                    {
                        $wlChgData = array('occupied' => $rawLoc['num']);
                        $wl->update($sid, $rawLoc['loc'], $wid, array(), $wlChgData);
                    }
                }

                // t_stock 更新占用
                $occupied = $productsIdxBySid[$sid]['num'] - $opUpdata['vnum'];
                if ($occupied > 0)
                {
                    $ws->save($wid, $sid, array(), array('occupied' => $occupied));
                }
            }

            return;
        }

        // 老库逻辑

        //更新 库存占用
        $oldStocks = Tool_Array::list2Map($ws->getBulk($wid, $sids), 'sid');

        foreach ($products as &$product)
        {
            $sid = $product['sid'];
            $oldStockNum = isset($oldStocks[$sid]) ? $oldStocks[$sid]['num'] : 0;

            // 占用不会小于0
            $oldOccupied = (isset($oldStocks[$sid]) && $oldStocks[$sid]['occupied'] > 0) ? $oldStocks[$sid]['occupied'] : 0;

            if ($oldStockNum - $oldOccupied > 0)    //有库存
            {
                if ($oldStockNum - $oldOccupied < $product['num'])
                {    //库存不够
                    $product['vnum'] = $product['num'] - ($oldStockNum - $oldOccupied);
                }
                else
                {  //库存够
                    $product['vnum'] = 0;
                }
            }
            else
            {  //没库存
                $product['vnum'] = $product['num'];
            }

            // 修改空采
            if ($product['vnum'] > 0)
            {
                $oo->updateOrderProductVnum($oid, $sid, $product['vnum']);
            }

            // 占用库存
            $change = array('occupied' => $product['num']);
            $ws->save($wid, $sid, array(), $change);
        }
    }

    /**
     * 出库时的库存变化
     *
     * @param $suid
     * @param $wid
     * @param $oid
     * @param $products
     */
    private static function _checkUpdateStockNum($suid, $wid, $oid, $products)
    {
        if (empty($products))
        {
            return;
        }
        
        $ws = new Warehouse_Stock();
        $wsh = new Warehouse_Stock_History();
        $sp = new Shop_Product();

        //获取原库存
        $sids = array();
        foreach ($products as &$p)
        {
            if (empty($p['sid'])) //防止t_order_product sid 为0
            {
                $pinfo = $sp->get($p['pid']);
                $sids[] = $pinfo['sid'];
                $p['sid'] = $pinfo['sid'];
            }
            else
            {
                $sids[] = $p['sid'];
            }
        }
        $oldStocks = Tool_Array::list2Map($ws->getBulk($wid, $sids), 'sid');

        // 新库逻辑
        if (ENV == 'online')
        {
            foreach ($products as $_p)
            {
                if (!self::_isScanCode4Picked($wid, $_p['location'])) continue;

                if ($_p['num']-$_p['vnum'] != $_p['picked'])
                {
                    $desc = '应出库数量：' . ($_p['num'] - $_p['vnum']) . '; 实际拣货数量：' . $_p['picked'];
                    $errmsg = sprintf('货位:%s Skuid:%d 【%s】', $_p['location'], $_p['sid'], $desc);
                    throw new Exception($errmsg);
                }
            }
        }

        // 检测：标记外采逻辑
        if (!Order_Helper::isFranchiseeOrder($wid))
        {
            $_sids = Tool_Array::getFields($products, 'sid');
            $_cityId = Conf_Warehouse::$WAREHOUSE_CITY_MAPPING[$wid];
            $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE | Conf_Product::PRODUCT_STATUS_OFFLINE;
            $productInfos = Tool_Array::list2Map($sp->getBySku($_sids, $_cityId, $statusTag), 'pid');

            foreach ($products as $_p)
            {
                // 普采商品，缺货，必须标记才能出库！
                if ($oldStocks[$_p['sid']]['outsourcer_id']==0 
                    && $_p['vnum'] > 0 && $productInfos[$_p['pid']]['buy_type']==Conf_Product::BUY_TYPE_COMMON 
                    && $_p['vnum_deal_type']==Conf_Base::STATUS_NORMAL)
                {
                    throw new Exception('普采商品，缺货，需要标记【外采】，在出库');
                }

                if ($oldStocks[$_p['sid']]['outsourcer_id']==0
                    && $_p['vnum'] > 0 && $productInfos[$_p['pid']]['buy_type']==Conf_Product::BUY_TYPE_COMMON
                    && $_p['vnum_deal_type']==Conf_Warehouse::ORDER_VNUM_FLAG_LATER)
                {
                    throw new Exception('普采商品，已标记【晚到】暂时不能出库');
                }
            }
        }

        // 出库操作
        $wl = new Warehouse_Location();
        Warehouse_Location_Api::parseLocationAndNum($products);
        $fifoCostProducts = array();

        foreach ($products as $pinfo)
        {
            $sid = $pinfo['sid'];
            if($oldStocks[$sid]['outsourcer_id'] > 0)
            {
                continue;
            }

            // 更新货位库存，占用
            if (isset($pinfo['_location']) && !empty($pinfo['_location']))
            {
                foreach ($pinfo['_location'] as $loc)
                {
                    $wlChgdata = array(
                        'num' => (0 - $loc['num']),
                        'occupied' => (0 - $loc['num'])
                    );
                    $wl->update($sid, $loc['loc'], $wid, array(), $wlChgdata);
                }
            }

            // 更新总库存，占用，并写库存历史
            $numFromStock = $pinfo['num'] - $pinfo['vnum'];

            //需要从仓库出货，减库存，记录出入库历史
            if ($numFromStock > 0)
            {
                $wsChgdata = array(
                    'num' => (0 - $numFromStock),
                    'occupied' => (0 - $numFromStock),
                );
                $ws->save($wid, $sid, array(), $wsChgdata);

                // 保存历史记录
                $history = array(
                    'old_num' => isset($oldStocks[$sid]) ? $oldStocks[$sid]['num'] : 0,
                    //'old_occupied' => isset($oldStocks[$sid]) ? $oldStocks[$sid]['occupied'] : 0,
                    'num' => 0 - abs($numFromStock),
                    //'occupied' => $pinfo['num'],
                    'iid' => $oid,
                    'suid' => $suid,
                    'type' => Conf_Warehouse::STOCK_HISTORY_OUT,
                );
                $wsh->add($wid, $sid, $history);

                $fifoCostProducts[] = array('sid'=>$sid, 'num'=>$numFromStock);
            }
        }

        // 使用COST-FIFO队列刷新订单成本
        $oo = new Order_Order();
        $refreshCostDatas = Shop_Cost_Api::getCostsWithSkuAndNums($wid, $fifoCostProducts);

        $billInfo = array('out_id'=>$oid, 'out_type'=>Conf_Warehouse::STOCK_HISTORY_OUT);
        foreach($refreshCostDatas as $_sid => $fifoCosts)
        {
            if (empty($fifoCosts['_cost_fifo'])) continue;

            $oo->updateOrderProductBySid($oid, 0, $_sid, array('cost'=>$fifoCosts['cost']));
            Shop_Cost_Api::dequeue4FifoCost($_sid, $wid, $billInfo, $fifoCosts['_cost_fifo']);
        }

        return;
    }

    /**
     * 是否扫码.
     * 
     * @param type $wid
     * @param type $loc
     */
    private static function _isScanCode4Picked($wid, $loc)
    {
        if (Order_Helper::isFranchiseeOrder($wid)) return false;
        
        $widNoScan = array(
            Conf_Warehouse::WID_101, Conf_Warehouse::WID_LF1,
            Conf_Warehouse::WID_TJ2, Conf_Warehouse::WID_BJ_WJ1,
            Conf_Warehouse::WID_LF_COOP1, Conf_Warehouse::WID_QD1,
        );
        $areaNoScan = array(
            Conf_Warehouse::WID_4 => array('A', 'B'),
            Conf_Warehouse::WID_TJ1 => array('A', 'B'),
            Conf_Warehouse::WID_8 => array('E'),
            Conf_Warehouse::WID_CHD1 => array('A', 'B', 'C'),
            Conf_Warehouse::WID_CQ1 => array('A', 'B', 'D'),
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
    
    /**
     * 删除订单/回滚订单的时候释放库存
     *
     * @param $oid
     * @param $wid
     */
    private static function _releaseOccupied($oid, $wid)
    {
        $ws = new Warehouse_Stock();
        $oo = new Order_Order();
        $products = $oo->getProductsOfOrder($oid);

        // 新库逻辑
        if (Conf_Warehouse::isUpgradeWarehouse(($wid)))
        {
            Warehouse_Location_Api::parseLocationAndNum($products);

            // 释放t_order_product 上的location标记，空采数量
            //$opUpdata = array('location' => '', 'vnum' => 0);
            $opUpdata = array('vnum' => 0,'outsourcer_id' => 0);
            $oo->updateOrderProductInfo($oid, 0, 0, $opUpdata);

            $wl = new Warehouse_Location();
            $ws = new Warehouse_Stock();
            $sids = Tool_Array::getFields($products, 'sid');
            $stockInfos = Tool_Array::list2Map($ws->getBulk($wid, $sids), 'sid');
            
            foreach ($products as $p)
            {
                if ($stockInfos[$p['sid']]['outsourcer_id']>0 || $p['num'] == $p['vnum'])
                {
                    continue;
                }

                // 释放货架的占用
                if (isset($p['_location']) && !empty($p['_location']))
                {
                    foreach ($p['_location'] as $loc)
                    {
                        $wlChgdata = array('occupied' => (0 - $loc['num']));
                        $wl->update($p['sid'], $loc['loc'], $wid, array(), $wlChgdata);
                    }
                }

                // 释放库存的占用
                $wsChgdata = array('occupied' => (0 - ($p['num'] - $p['vnum'])));
                $ws->save($wid, $p['sid'], array(), $wsChgdata);
            }

            return;
        }

        // 老库逻辑
        foreach ($products as $product)
        {
            // 释放占用数量
            $pid = $product['pid'];
            $change = array('occupied' => 0 - $product['num']);
            $ws->save($wid, $product['sid'], array(), $change);
            $oo->updateOrderProductVnum($oid, $pid, 0);

            // 释放空采数量
            if ($product['vnum'] <> 0)
            {
                $oo->updateOrderProductVnum($oid, $pid, 0);
            }
        }
    }

    /**
     * 刷新订单商品的占用.
     *
     * @reason  解决空采占用问题
     *      订单的空采商品，按照配送时间优先分配占用
     *
     * @rule
     *  - 订单待刷商品的条件
     *      1 该订单的临采商品
     *      2 临采商品没有下临采采购单（对销售回退商品情况判定）
     *      3 临采商品对应对应的库存不为0
     *  - 被刷商品所在订单条件
     *      1 未打印 or 非今天的订单
     *      2 客服确认 and 未出库
     *      3 已临采（做了临采单据）
     *
     * @do
     *      重新库存在订单上的占用
     *      修改字段：vnum，location，picked（存在不扫码区的情况）
     *
     * @param type $oid
     * @param type $sid
     * 
     * @notice 普采商品缺货不能客服确认，故下线改部分逻辑 addby guoqiang yang; 2017-06-07
     */
    private static function _refreshProductOccupied($oid, $orderinfo = array(), $orderProducts = array())
    {
        $oo = new Order_Order;
        if (empty($orderinfo))
        {
            $orderinfo = $oo->get($oid);
        }
        if (empty($orderProducts))
        {
            $orderProducts = $oo->getProductsOfOrder($oid);
        }

        $wid = isset($orderinfo['wid']) ? $orderinfo['wid'] : 0;
        $pids2sids = array();

        // 取订单的临采商品
        foreach ($orderProducts as $product)
        {
            if ($product['vnum'] > 0 && $product['tmp_inorder_num'] == 0)
            {
                $pids2sids[$product['pid']] = $product['sid'];
            }
        }
        if (!isset($orderinfo['step']) || $orderinfo['step'] <= Conf_Order::ORDER_STEP_SURE || $orderinfo['step'] > Conf_Order::ORDER_STEP_PICKED || empty($wid) || empty($pids2sids))
        {
            return FALSE;
        }

        // 获取空采商品的库存（总库存）
        $ws = new Warehouse_Stock();
        $stockOfProduct = Tool_Array::list2Map($ws->getBulk($wid, array_values($pids2sids)), 'sid');
        foreach ($pids2sids as $pid => $sid)
        {
            if (!array_key_exists($sid, $stockOfProduct) || $stockOfProduct[$sid]['num'] <= 0)
            {
                unset($pids2sids[$pid]);
            }
        }
        if (empty($pids2sids))
        {
            return FALSE;
        }

        $today = date('Y-m-d 23:59:59');
        $kind = sprintf('t_order_product op inner join t_order o' . ' on o.status=0 and o.wid=%d and (o.has_print=0 or o.delivery_date>="%s") and o.step>=%d and o.step<%d', $wid, $today, Conf_Order::ORDER_STEP_SURE, Conf_Order::ORDER_STEP_PICKED);
        $where = sprintf('op.status=0 and op.rid=0 and op.pid in (%s) and op.tmp_inorder_num=0 and op.oid=o.oid', implode(',', array_keys($pids2sids)));
        $order = 'order by o.delivery_date asc';
        $field = array(
            'o.oid',
            'o.delivery_date',
            'op.pid',
            'op.sid',
            'op.num',
            'op.vnum',
            'op.location'
        );

        $waitDealProducts = $oo->getByRawWhere($kind, $where, $field, $order);
        Warehouse_Location_Api::parseLocationAndNum($waitDealProducts);

        // 统计可被订单占用的，可分配的货位库存
        $locationNums = array();
        foreach ($waitDealProducts as $wpone)
        {
            if (!isset($wpone['_location']) || empty($wpone['_location']))
            {
                continue;
            }
            $pid = $wpone['pid'];
            foreach ($wpone['_location'] as $l)
            {
                $loc = $l['loc'];
                if (!isset($locationNums[$pid][$loc]))
                {
                    $locationNums[$pid][$loc] = $l['num'];
                }
                else
                {
                    $locationNums[$pid][$loc] += $l['num'];
                }
            }
        }
        if (empty($locationNums))
        {
            return FALSE;
        }

        // 重新分配库存
        foreach ($waitDealProducts as $waitOne)
        {
            $upLocations = array();
            $pid = $waitOne['pid'];
            $saleNum = $waitOne['num'];
            if (array_key_exists($pid, $locationNums) && !empty($locationNums[$pid]))
            {
                foreach ($locationNums[$pid] as $_loc => &$_num)
                {
                    $diffNum = $_num - $saleNum;
                    if ($diffNum > 0)
                    {
                        $upLocations[$pid][] = array(
                            'loc' => $_loc,
                            'num' => $saleNum
                        );
                        $_num = $diffNum;
                        $saleNum = 0;
                    }
                    else
                    {
                        $upLocations[$pid][] = array(
                            'loc' => $_loc,
                            'num' => $_num
                        );
                        $saleNum -= $_num;
                        unset($locationNums[$pid][$_loc]);
                    }
                }
            }

            $upLoc = '';
            if (!empty($upLocations))
            {
                $upLoc = Warehouse_Location_Api::genLocationAndNum($upLocations);
            }
            //echo "{$waitOne['oid']}\t$pid\t{$waitOne['num']}\t{$waitOne['vnum']}\t$saleNum\t{$upLoc[$pid]}\n";

            $upData = array(
                'location' => $upLoc[$pid],
                'vnum' => $saleNum,
            );
            $oo->updateOrderProductInfo($waitOne['oid'], $pid, 0, $upData);
        }
    }

    /**
     * 更新订单总金额
     *
     * @param $oid
     *
     * @return int
     */
    public static function updateOrderTotalPrice($oid, $products)
    {
        self::_updateOrderTotalPrice($oid, $products);
    }

    private static function _updateOrderTotalPrice($oid, $products = array())
    {
        $price = 0;
        $num = 0;
        $oo = new Order_Order();

        $order = $oo->get($oid);
        $hasDuty = $order['has_duty'];

        if (empty($products))
        {
            $products = $oo->getProductsOfOrder($oid, FALSE, Conf_Base::STATUS_NORMAL, FALSE);
        }

        foreach ($products as $product)
        {
            if ($hasDuty == Conf_Base::HAS_DUTY)
            {
//                $duty = Conf_Base::DUTY;
//                if (date('Y-m-d H:i:s') >= Conf_Base::NEW_DUTY_START)
//                {
//                    $duty = Conf_Base::DUTY_20161129;
//                }
                $duty = Conf_Base::getDuty($order['cid']);
                /* 含税价根据商品单价计算后再乘商品数量与商品列表显示保持一致 */
                $price += self::getProductDutyPrice($product['price'], $duty) * $product['num'];
            }
            else
            {
                $price += $product['price'] * $product['num'];
            }

            $num++;
        }

        $paidStatus = Conf_Order::UN_PAID;
        if ($order['real_amount'] > 0)
        {
            $order['price'] = $price;
            $needPaid = Order_Helper::calOrderNeedToPay($order);
            $paidStatus = $needPaid==0? Conf_Order::HAD_PAID: Conf_Order::PART_PAID;
        }
        
        $info = array(
            'paid' => $paidStatus,
            'price' => $price,
            'product_num' => $num
        );
        self::updateOrderInfo($oid, $info);

        return $price;
    }

    /**
     * 检查是否能添加退货单. 条件：退货数量不能超过销售单数量.
     *
     * @param $oid
     * @param $rid
     * @param $products
     *
     * @throws Exception
     * @return bool
     */
    public static function _ifCanRefund($oid, $rid, $products)
    {
        // 1.获取订单商品
        $oo = new Order_Order();
        $orderProducts = $oo->getProductsOfOrder($oid, TRUE);

        // 2.获取除rid之外的退款单；订单可能分多次退货
        $canRefundNum = array();
        foreach ($orderProducts as $_product)
        {
            if ($rid != 0 && $_product['rid'] == $rid || $_product['status'] != Conf_Base::STATUS_NORMAL)
            {
                continue;
            }

            if (!array_key_exists($_product['pid'], $canRefundNum))
            {
                $canRefundNum[$_product['pid']] = 0;
            }

            if ($_product['rid'] == 0)
            {
                $canRefundNum[$_product['pid']] += $_product['num'];
            }
            else if ($_product['rid'] > 0)
            {
                if($_product['num'] == 0)
                {
                    continue;
                }
                $has_refund_num = $_product['picked'] + $_product['damaged_num'];
                if($has_refund_num > 0)
                {
                    $canRefundNum[$_product['pid']] -= $has_refund_num;
                }else{
                    $canRefundNum[$_product['pid']] -= $_product['apply_rnum'];
                }
            }
        }

        // 3.看是否超过订单商品
        foreach ($products as $product)
        {
            $pid = $product['pid'];

            if ($product['apply_rnum'] > $canRefundNum[$pid])
            {
                throw new Exception(sprintf('商品ID:[%d]   申请退货数量大于订单商品的可退数量', $pid));
            }

            if ($product['num'] > $canRefundNum[$pid])
            {
                throw new Exception(sprintf('商品ID:[%d]    退货数量大于订单商品的可退数量', $pid));
            }
        }

        return TRUE;
    }

    /**
     * 更新退款单总价
     *
     * @param $rid
     */
    private static function _updateRefundTotalPrice($rid)
    {
        $price = 0;
        $or = new Order_Refund();
        $products = $or->getProductsOfRefund($rid);
        foreach ($products as $product)
        {
            $price += $product['price'] * $product['num'];
        }

        $wio = new Order_Refund();
        $info = array('price' => $price);
        $wio->update($rid, $info);
    }

    /**
     * 退货单需要退款，并退款到客户账户余额
     *
     * @param $refundInfo
     * @param $staff
     * @param $refund2Balance 退款是否进入余额
     * @param $adjust
     */
    protected static function _paidRefund($refundInfo, $staff, $refund2Balance, $adjust)
    {
        // 财务: 应收减少
        self::_addMoneyInRefundHistory($refundInfo, $staff);

        $price = 0;
        // 退款金额写入余额
        if ($refund2Balance)
        {
            // 财务退款，写入 应收明细
            $cid = $refundInfo['cid'];
            $price = $refundInfo['price'] - $adjust * 100;
            $price < 0 && $price = 0;
            $objid = $refundInfo['rid'];
            $note = '退货退款（oid:' . $refundInfo['oid'] . ', rid:' . $objid . ')';
            Finance_Api::addMoneyInHistory($cid, Conf_Money_In::ORDER_REFUND, $price, $staff['suid'], $objid, $refundInfo['wid'], $note, Conf_Base::PT_BALANCE, $refundInfo['uid'], $refundInfo['oid']);

            $saveData = array(
                'uid' => $refundInfo['uid'],
                'type' => Conf_Finance::CRM_AMOUNT_TYPE_REFUND,
                'price' => abs($price),
                'payment_type' => Conf_Base::PT_BALANCE,
                'note' => $note,
                'objid' => $objid,
                'suid' => $staff['suid'],
                'oid' => $refundInfo['oid'],
            );

            Finance_Api::addCustomerAmountHistory($cid, $saveData);

            $oldOrder = Order_Api::getOrderInfo($refundInfo['oid']);
            $msgInfo = array(
                'ocode' => $oldOrder['_oid'],
                'amount' => $price / 100,
            );
            Crm2_User_Msg_Api::addMsg($oldOrder['uid'], $oldOrder['cid'], Conf_User_Msg::$MSG_REFUND, $msgInfo);
        }

        return $price;
    }

    /**
     * 退款流水日志
     *
     * @param $refund
     * @param $staff
     */
    private static function _addMoneyInRefundHistory($refund, $staff)
    {
        $price = 0 - $refund['price'];
        $wid = $refund['wid'] ? $refund['wid'] : Conf_Warehouse::WID_3;
        $type = Conf_Money_In::REFUND_PAIED;
        $cid = $refund['cid'];
        $suid = $staff['suid'];
        $rid = $refund['rid'];

        Finance_Api::addMoneyInHistory($cid, $type, $price, $suid, $rid, $wid, '', 0, $refund['uid'], $refund['oid']);
    }

    private static function _checkRefund($refundInfo, $adjust)
    {
        $price = $refundInfo['price'] - $adjust * 100;
        $price < 0 && $price = 0;
        $fca = new Finance_Customer_Amount();
        $where = sprintf(' cid=%d AND type=%d AND objid=%d AND payment_type=%d AND price=%d', $refundInfo['cid'], Conf_Money_In::CUSTOMER_AMOUNT_TRANSFER, $refundInfo['oid'], Conf_Base::PT_BALANCE, $price);
        $total = $fca->getTotalByWhere($where);
        if ($total > 0)
        {
            throw new Exception('order:has refund');
        }
    }

    public static function getSonOrder($oid)
    {
        $where = array('source_oid' => $oid);
        $oo = new Order_Order();
        $data = $oo->getListRawWhere($where, $total, '', 0, 0);

        return $data;
    }

    public static function getAfterSaleOrder($afterSaleId, $afterSaleType)
    {
        $oo = new Order_Order();
        $data = $oo->getByAfterSaleId($afterSaleId, $afterSaleType);

        return $data;
    }

    public static function updateOrderProduct($oid, $sid, $update, $change = array())
    {
        $oo = new Order_Order();

        return $oo->updateOrderProduct($oid, $sid, $update, $change);
    }

    public static function forceRefresh($sid, $oid)
    {
        $oo = new Order_Occupied();

        $oo->forceRefreshSkuOccupiedOfOrder($sid, $oid);
    }

    public static function getWeightByOids($oids)
    {
        $result = array();

        if (!empty($oids))
        {
            $products = self::getProductByOids($oids);
            if (!empty($products))
            {
                $sids = Tool_Array::getFields($products, 'sid');
                $ss = new Shop_Sku();
                $skus = $ss->getBulk($sids);

                foreach ($products as $product)
                {
                    $sid = $product['sid'];
                    $oid = $product['oid'];
                    $weight = $skus[$sid]['weight'];
                    $num = $product['num'];

                    $result[$oid] += $weight * $num;
                }
            }
        }

        return $result;
    }

    public static function getOrderListByWhere($where, $order = array(), $start = 0, $num = 20, $fields = array('*'))
    {
        $oo = new Order_Order();
        $list = $oo->getListRawWhereWithoutTotal($where, $order, $start, $num, $fields);

        return $list;
    }

    public static function getCancelList($searchConf, $start = 0, $num = 20)
    {
        $ocr = new Order_Cancel_Reason();

        return $ocr->getList($searchConf, $start, $num);
    }

    public static function getBulk($oids, $fields = array('*'))
    {
        $oo = new Order_Order();

        return $oo->getBulk($oids, $fields);
    }

    // 合成订单操作：生成写入模式: 【只保存非0操作】
    public static function generateOpNote($opNote)
    {
        $ret = array();
        $allOpNotes = Conf_Order::getOrderOperateNote();
        
        foreach ($opNote as $op => $value)
        {
            if (in_array($op, $allOpNotes) && $value>0)
            {
                $ret [] = $op . ':' . $value;
            }
        }
        
        return !empty($ret)? implode(',', $ret): '';
    }

    // 解析订单操作：对于没有保存的选项，设置为0
    public static function parseOpNote($opNote)
    {
        $ret = array();
        $opNotes = explode(',', $opNote);
        $allOpNotes = Conf_Order::getOrderOperateNote();
        
        foreach ($opNotes as $item)
        {
            list($op, $value) = explode(':', $item);
            
            if (in_array($op, $allOpNotes))
            {
                $ret[$op] = $value;
                
                $k = array_search($op, $allOpNotes);
                unset($allOpNotes[$k]);
            }
        }
        
        if (!empty($allOpNotes))
        {
            foreach($allOpNotes as $_op)
            {
                $ret[$_op] = 0;
            }
        }
        
        return $ret;
    }
    
    /**
     * 获取某个城市下，订单商品有库存的仓库id，如果为空，返回默认仓库id.
     * 
     * @param array $orderProducts  [[pid:xx,sid:xx,num:xx], ..., ]
     * @param int $cityId 
     */
    public static function getWidsByProductsStock($orderProducts, $cityId)
    {
        $ws = new Warehouse_Stock();
        $widsInCity = Appconf_Warehouse::wid4City($cityId, 'online');
        $sids = Tool_Array::getFields($orderProducts, 'sid');
        
        if (empty($sids)) return array(Appconf_Warehouse::defaultWid4AutoAllocOrder ($cityId));
        
        $sp = new Shop_Product();
        $pids = Tool_Array::getFields($orderProducts, 'pid');
        $productInfos = $sp->getBulk($pids);
        
        foreach ($widsInCity as $wid => $wname)
        {
            $stockInfo = Tool_Array::list2Map($ws->getBulk($wid, $sids), 'sid');
            
            $canSale = true;
            foreach($orderProducts as $p)
            {
                $_sid = $p['sid'];
                $_pid = $p['pid'];

                if($productInfos[$_pid]['buy_type'] == Conf_Product::BUY_TYPE_TEMP)
                {
                    continue;
                }
                
                if (!array_key_exists($_sid, $stockInfo))
                {
                    $canSale = false; break;
                }
                
                $availStock = $stockInfo[$_sid]['num'] - $stockInfo[$_sid]['occupied'] - $stockInfo[$_sid]['damaged_num'];
              
                if ($p['num'] > $availStock)
                {
                    $canSale = false; break;
                }
            }
            
            if (!$canSale)
            {
                unset($widsInCity[$wid]);
            }
        }
        
        return !empty($widsInCity)? array_keys($widsInCity): array(Appconf_Warehouse::defaultWid4AutoAllocOrder ($cityId));
    }

    /**
     * 根据订单id自动选择仓库
     * @rule
     *      1 筛选出有库存的仓库
     *      2 从筛选出的仓库中，优先选择最近的仓库
     *      3 如果没有满足条件的仓库，则制定默认仓库（写配置文件，每个城市一个默认仓库）
     * @param $oid
     * @return int $wid
     */
    public static function autoAllocateWid4Order($oid)
    {
        assert($oid);
        
        $oo = new Order_Order();
        $order = $oo->get($oid);
        if (empty($order['community_id']) || empty($order['city_id']))
        {
            return 0;
        }
        
        //可配货的仓库
        $orderProducts = $oo->getProductsOfOrder($oid);
        $allowWids = self::getWidsByProductsStock($orderProducts, $order['city_id']);
        
        $selectedWid = 0;
        $minDistance = 0;
        if (count($allowWids) > 1)
        {
            foreach ($allowWids as $_wid)
            {
                $distance = Order_Community_Api::getDistanceByWid($order['community_id'], $_wid);
                
                $minDistance = $minDistance==0? $distance: min($minDistance, $distance);
                $selectedWid = $minDistance=$distance? $_wid: $selectedWid;
                
            }
        }
        else
        {
            $selectedWid = $allowWids[0];
        }
        
        return $selectedWid;
    }

    /**
     * 判断是否可以修改订单
     */
    public static function canEditOrderInfo($oid, $suid=0)
    {
        $data = array(
            'error' => 0,
            'errormsg' => '',
        );
        $orderInfo = self::getOrderInfo($oid);
        if(empty($orderInfo))
        {
            $data = array(
                'error' => 1,
                'errormsg' => '订单不存在',
            );
        }else{
            if($orderInfo['city_id'] == Conf_City::CHONGQING)
            {
                $canEditList = array(1581, 1029, 1525, 1580, 1668, 1670, 1671, 1672, 1673, 1674, 1648,1630,1676,1254,1710);
                if($orderInfo['step'] >= Conf_Order::ORDER_STEP_SURE && !in_array($suid, $canEditList))
                {
                    $data = array(
                        'error' => 2,
                        'errormsg' => '订单已确认，不能修改！',
                    );
                }
                if($orderInfo['paid'] != Conf_Order::UN_PAID && !in_array($suid, $canEditList))
                {
                    $data = array(
                        'error' => 3,
                        'errormsg' => '订单已付款，不能修改',
                    );
                }
            }else{
                if($orderInfo['step'] >= Conf_Order::ORDER_STEP_PICKED)
                {
                    $data = array(
                        'error' => 4,
                        'errormsg' => '订单已出库，不能修改',
                    );
                }
            }
        }
        return $data;
    }

    /**
     * 更新订单活动商品到订单商品表中
     * @param $oid
     * @param $realOrderProducts
     * @param $activityProducts
     * @param $gift_products
     * @param $discount_products
     */
    public static function updateActivityProducts2Order($oid, $realOrderProducts, $orderActivityProducts, $gift_products, $discount_products)
    {
        assert($oid>0);
        $changeProducts = array();//需要更新的商品
        $addProducts = array();//需要新增的商品
        $gift_pids = array();
        $discount_pids = array();
        $has_gift_pids = array();
        $has_discount_pids = array();
        $new_gift_products = array();
        $new_discount_products = array();
        $oo = new Order_Order();
        if(!empty($gift_products))
        {
            foreach ($gift_products as $tmp_product)
            {
                foreach ($tmp_product as $pid => $item)
                {
                    $gift_pids[] = $pid;
                    $new_gift_products[$pid] = $item;
                }
            }
        }
        if(!empty($discount_products))
        {
            foreach ($discount_products as $tmp_product)
            {
                foreach ($tmp_product as $pid => $item)
                {
                    $discount_pids[] = $pid;
                    $new_discount_products[$pid] = $item;
                }
            }
        }
        $orderInfo = self::getOrderInfo($oid);
        foreach ($orderActivityProducts as $item)
        {
            $pid = $item['pid'];
            switch ($item['type'])
            {
                case Conf_Privilege::$TYPE_GIFT:
                    $has_gift_pids[] = $pid;
                    if(!in_array($pid, $gift_pids))
                    {
                        //如果赠品更换，原赠品数量减去
                        $tmp_item = $item;
                        $tmp_item['num'] = -$item['num'];
                        $changeProducts[$pid] = $tmp_item;
                    }elseif($new_gift_products[$pid]['num'] != $item['num']){
                        //如果赠品数量不一致，当前数量减原数量
                        $tmp_item = $item;
                        $tmp_item['num'] = $new_gift_products[$pid]['num'] - $item['num'];
                        $changeProducts[$pid] = $tmp_item;
                    }
                    break;
                case Conf_Privilege::$TYPE_SPECIAL_PRICE:
                    $has_discount_pids[] = $pid;
                    if(!in_array($pid, $discount_pids))
                    {
                        //如果特价商品更换，原特价商品数量减去
                        $tmp_item = $item;
                        $tmp_item['num'] = -$item['num'];
                        $changeProducts[] = $tmp_item;
                    }elseif($new_discount_products[$pid]['num'] != $item['num']){
                        //如果特价商品数量不一致，当前数量减原数量
                        $tmp_item = $item;
                        $tmp_item['num'] = $new_discount_products[$pid]['num'] - $item['num'];
                        $changeProducts[] = $tmp_item;
                    }
                    break;
                default:
            }
        }
        foreach ($gift_products as $aid => $tmp_products) {
            foreach ($tmp_products as $pid => $item){
                if (!in_array($pid, $has_gift_pids)) {
                    if (!in_array($pid, array_keys($realOrderProducts)) && !in_array($pid, array_keys($orderActivityProducts))) {
                        if(empty($addProducts[$pid]))
                        {
                            $addProducts[$pid] = $item;
                        }else{
                            $addProducts[$pid]['num'] += $item['num'];
                        }
                    } else {
                        $changeProducts[] = $item;
                    }
                }
                if(!in_array($pid, array_keys($realOrderProducts)))
                {
                    $oo->updateorderDeleteProductStatus($oid,$item['sid'],array('status' => Conf_Base::STATUS_NORMAL,'num' => $item['num']));
                }
            }
        }
        foreach ($discount_products as $aid => $tmp_products)
        {
            foreach ($tmp_products as $pid => $item)
            {
                if(!in_array($pid, $has_discount_pids))
                {
                    if(!in_array($pid, array_keys($realOrderProducts)) && !in_array($pid, array_keys($orderActivityProducts)))
                    {
                        if(empty($addProducts[$pid]))
                        {
                            $addProducts[$pid] = $item;
                        }else{
                            $addProducts[$pid]['num'] += $item['num'];
                        }
                    }else{
                        $changeProducts[] = $item;
                    }
                }
                if(!in_array($pid, array_keys($realOrderProducts)))
                {
                    $oo->updateorderDeleteProductStatus($oid,$item['sid'],array('status' => Conf_Base::STATUS_NORMAL,'num' => $item['num']));
                }
            }
        }
        $addProducts = array_filter($addProducts);
        $changeProducts = array_filter($changeProducts);

        if(!empty($changeProducts))
        {
            foreach ($changeProducts as $item)
            {
                $oo->updateOrderProduct($oid, $item['sid'], array('status' => Conf_Base::STATUS_NORMAL), array('num' => $item['num']));
            }
        }
        if(!empty($addProducts))
        {
            $pids = Tool_Array::getFields($addProducts, 'pid');
            $productInfos = Shop_Api::getProductInfos($pids, Conf_Activity_Flash_Sale::PALTFORM_BOTH, true);
            foreach ($addProducts as $item)
            {
                $info = array(
                    'pid' => $item['pid'],
                    'sid' => $item['sid'],
                    'num' => $item['num'],
                    'oid' => $oid,
                    'price' => $productInfos[$item['pid']]['product']['sale_price'] > 0 ? $productInfos[$item['pid']]['product']['sale_price'] : $productInfos[$item['pid']]['product']['price'],
                    'cost' => $productInfos[$item['pid']]['product']['cost'],
                    'status' => Conf_Base::STATUS_NORMAL
                );
                $oo->addOrderProduct($oid, $info, $orderInfo['city_id']);
            }
        }
        self::_updateOrderTotalPrice($oid);

    }

    /**
     * 更新订单优惠商品金额到订单商品表中
     * @param $oid
     * @param $privilegeProducts
     */
    public static function updatePrivilegeProducts2Order($oid, $privilegeProducts)
    {
        assert($oid>0);
        $oo = new Order_Order();
        $orderProducts = self::getOrderProducts($oid);
        foreach ($orderProducts['products'] as $pid => $product)
        {
            $oo->updateOrderProductInfo($oid, $pid, 0, array('privilege' => 0));
        }
        if(!empty($privilegeProducts))
        {
            foreach ($privilegeProducts as $pid => $product)
            {
                if($pid >0)
                {
                    $oo->updateOrderProductInfo($oid, $pid, 0, array('privilege' => $product['privilege']));
                }
            }
        }
    }

    /**
     * 获取商家结算单本期未结算订单列表
     *
     */
    public static function getOrderListWithNoSellerBill($bid, $conf, &$total, $start = 0, $num = 20)
    {
        $oo = new Order_Order();
        $where = sprintf('status=%d AND step>=%d AND oid not in(select objid from t_seller_bill_receipt where bid=%d and objtype=1)', Conf_Base::STATUS_NORMAL, Conf_Order::ORDER_STEP_PICKED, $bid);
        if(!empty($conf['wid']))
        {
            $where .= ' AND wid='.$conf['wid'];
        }
        if(!empty($conf['start_time']))
        {
            $where .= ' AND ship_time>="'.$conf['start_time'].'"';
        }
        if(!empty($conf['end_time']))
        {
            $where .= ' AND ship_time<"'.$conf['end_time'].'"';
        }
        $data = $oo->getListRawWhere($where, $total, false, $start, $num);
        if(!empty($data))
        {
            $oids = Tool_Array::getFields($data, 'oid');
            $sellerBillDao = new Finance_Seller_Bill();
            $where = sprintf('objtype=%d and objid in(%s)', 1, implode(',', $oids));
            $orderList = $sellerBillDao->getSellerBillReceiptList($where, $tmp_total, 0, 0);
            $orderList = Tool_Array::list2Map($orderList, 'objid');
            foreach ($data as &$item)
            {
                $oid = $item['oid'];
                $item['bill_amount'] = Order_Helper::calOrderTotalPrice($item);
                if(isset($orderList[$oid]))
                {
                    $item['bid'] = $orderList[$oid]['bid'];
                }else{
                    $item['bid'] = 0;
                }
            }
        }
        unset($item);

        return $data;
    }
    
    /**
     * 获取商品含税价
     * @author wangxuemin
     * @param int $price
     * @param int $duty
     * @return int
     */
    public static function getProductDutyPrice($price, $duty = 0) {
        return round($price / (1 - $duty));
    }
}
