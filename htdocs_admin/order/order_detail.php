<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $oid;
    private $cid;
    private $order;
    private $carrierFee;
    private $customer;
    private $originOrderProducts;
    private $orderProducts;
    private $refundProducts;
    private $privilege;
    private $hasRole = FALSE;
    private $sandPrice;
    private $otherPrice;
    private $lotteryRes;
    private $today;
    private $deliveryDate;
    private $carryFee;
    private $freight;
    private $floor;
    private $isFirstOrder;
    private $has96Privilege = FALSE;
    private $driverInfos;
    private $canJoinAfternoonActivity = FALSE;
    private $lineInfo = array();
    private $originPriceTotal;
    private $orderDriverList;
    private $orderCarrierList;
    private $basePrices;
    private $maxCarrierFee;
    private $stocks;
    private $totalWeight;
    private static $EX_SUIDS = array(
        1126, 1038, 1153, 1294, 1343
    );
    private $reason;
    private $comment;
    private $abnormalProducts;
    private $colorOrder;
    private $LangFangCarrierFeeRules;
    private $profit;
    private $user;
    private $giftProducts = array();
    private $discountProducts = array();
    private $activity_products_amount = 0;
    private $isCertificate;

    private $timeArray = array();
    private $uinfo = array();

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
    }
    
    protected function _checkPageAuth()
    {
        $roleLevels = Admin_Role_Api::getRoleLevels($this->_uid, $this->_user);
        
        if ($roleLevels[Conf_Admin::ROLE_SALES_MANAGER] && in_array($this->order['city_id'], explode(',', $this->_user['cities'])) )
        {
            return;
        }
        
        if ($roleLevels[Conf_Admin::ROLE_SALES_NEW])
        {
            $_referSales = array($this->order['saler_suid'], $this->customer['customer']['sales_suid']);
            
            $isMyOrder = array_intersect($this->_user['team_member'], $_referSales);
            if (empty($isMyOrder))
            {
                throw new Exception('没有权限访问-1');
            }
            
            return;
        }
        
        if (!empty($this->_user['cities']) && !in_array($this->order['city_id'], explode(',', $this->_user['cities'])) && $this->order['city_id']!=Conf_City::OTHER)
        {
            throw new Exception('没有权限访问-3');
        }
    }
    
    protected function main()
    {
        $this->order = Order_Api::getOrderInfo($this->oid);
        $this->customer = Crm2_Api::getCustomerInfo($this->order['cid'], FALSE, FALSE);
        $this->user = Crm2_Api::getUserInfo($this->order['uid'], FALSE, FALSE);
        $orderProducts = Order_Api::getOrderProducts($this->oid);
        $activity_products_amount = 0;
        $this->orderProducts = Privilege_Api::getRealBuyProducts($orderProducts['products'], $this->oid, $this->activity_products_amount);
        $this->order['price'] -= $activity_products_amount;
        $this->originOrderProducts = $this->orderProducts;

        $this->order['_op_note'] = Order_Api::parseOpNote($this->order['op_note']);

        $this->_checkPageAuth();

        $arrSort = array();
        foreach ($orderProducts['products'] as $_key => &$_product)
        {
            $cate1List[] = $_product['sku']['cate1'];
            $_product['cate1'] = $_product['sku']['cate1'];
            $arrSort[$_key] = $_product['cate1'];
        }

        $cate1List = array_unique($cate1List);
        sort($cate1List);
        $this->colorOrder = array_flip($cate1List);
        array_multisort($arrSort, SORT_ASC, $orderProducts['products']);

        $orderActivityProductDao = new Data_Dao('t_order_activity_product');
        $activityProducts = $orderActivityProductDao->getListWhere(array('oid' => $this->oid));
        if (!empty($activityProducts))
        {
            $pids = Tool_Array::getFields($activityProducts, 'pid');
            $productInfos = Shop_Api::getProductInfos($pids, Conf_Activity_Flash_Sale::PALTFORM_WECHAT, true);
            foreach ($activityProducts as $item)
            {
                if ($item['type'] == Conf_Privilege::$TYPE_GIFT)
                {
                    $item['title'] = $productInfos[$item['pid']]['sku']['title'];
                    $this->giftProducts[] = $item;
                }
                elseif ($item['type'] == Conf_Privilege::$TYPE_SPECIAL_PRICE)
                {
                    $item['title'] = $productInfos[$item['pid']]['sku']['title'];
                    $this->discountProducts[] = $item;
                }
            }
        }
        $this->refundProducts = $orderProducts['refund_products'];

        $this->isFirstOrder = Order_Api::isFristOrder($this->oid, $this->order);

        // 获取订单的第三方工人（司机，搬运工）
        $coopworders = Logistics_Coopworker_Api::getOrderOfWorkers($this->oid, 0, TRUE, Conf_Coopworker::OBJ_TYPE_ORDER);

        $this->order['driver_list'] = array();
        $this->order['carrier_list'] = array();
        foreach ($coopworders as $oner)
        {
            if ($oner['type'] == Conf_Base::COOPWORKER_DRIVER)
            {
                $this->order['driver_list'][] = array(
                    'id' => $oner['id'], 'cuid' => $oner['cuid'], 'name' => $oner['info']['name'], 'phone' => $oner['info']['mobile'], 'price' => $oner['price'] / 100, 'paid' => $oner['paid'], 'user_type' => $oner['user_type'], 'times' => $oner['times'], 'car_model' => empty($oner['car_model']) ? $oner['info']['car_model'] : $oner['car_model'], 'statement_id' => $oner['statement_id']
                );
                $this->driverInfos[] = $oner;
            }
            else if ($oner['type'] == Conf_Base::COOPWORKER_CARRIER)
            {
                if ($oner['user_type'] == Conf_Base::COOPWORKER_DRIVER)
                {
                    $carModel = $oner['info']['car_model'];
                }
                $this->order['carrier_list'][] = array(
                    'id' => $oner['id'], 'cuid' => $oner['cuid'], 'name' => $oner['info']['name'], 'phone' => $oner['info']['mobile'], 'price' => $oner['price'] / 100, 'paid' => $oner['paid'], 'user_type' => $oner['user_type'], 'statement_id' => $oner['statement_id'], 'car_model' => $carModel,
                );
            }
        }

        //添加司机订单信息
        $orderDriverList = Logistics_Coopworker_Api::getOrderOfWorkers($this->order['oid'], Conf_Base::COOPWORKER_DRIVER, FALSE, Conf_Coopworker::OBJ_TYPE_ORDER);
        $this->orderDriverList = Tool_Array::list2Map($orderDriverList, 'cuid');

        //添加搬运工订单信息
        $orderCarrierList = Logistics_Coopworker_Api::getOrderOfWorkers($this->order['oid'], Conf_Base::COOPWORKER_CARRIER, FALSE, Conf_Coopworker::OBJ_TYPE_ORDER);
        $this->orderCarrierList = Tool_Array::list2Map($orderCarrierList, 'cuid');

        foreach ($this->orderDriverList as &$_order)
        {
            if (!empty($_order['other_price']))
            {
                $_order['other_price'] = Logistics_Api::parseDriverFee($_order['other_price']);
                $_order['other_price_json'] = json_encode($_order['other_price']);
            }
        }

        foreach ($this->orderCarrierList as &$_carrier_order)
        {
            if (!empty($_carrier_order['other_price']))
            {
                $_carrier_order['other_price'] = Logistics_Api::parseDriverFee($_carrier_order['other_price']);
                $_carrier_order['other_price_json'] = json_encode($_carrier_order['other_price']);
            }
        }

        // 计算系统计算的搬运费
        $this->carrierFee = Logistics_Api::calCarryFee4Carrier($this->oid);

        //获得优惠
        $this->privilege = Privilege_Api::getOrderPrivilege($this->oid);
        if (!empty($this->privilege))
        {
            $am = new Activity_Promotion_Manjian();
            foreach ($this->privilege as &$p)
            {
                if ($p['type'] == Conf_Privilege::$TYPE_ACTIVITY_160413)
                {
                    $desc = '';
                    $arr = json_decode($p['info'], TRUE);
                    if (!empty($arr))
                    {
                        foreach ($arr as $pid => $info)
                        {
                            $price = $info['price'] / 100;
                            $desc .= "{$pid}优惠{$price}元;";
                        }
                    }
                    $p['info'] = $desc;
                }
                elseif ($p['type'] == Conf_Privilege::$TYPE_DISCOUNT)
                {
                    $discount_info = $am->getItem($p['activity_id']);
                    if ($discount_info['m_type'] == 1)
                    {
                        $p['info'] = '平台折扣-' . $discount_info['conf']['rate'] . '折';
                    }
                    else
                    {
                        $p['info'] = '指定用户折扣-' . $this->customer['customer']['discount_ratio'] . '折';
                    }
                    $p['privilege_url'] = '/activity/promotion_manjian_update.php?mode=show&id=' . $p['activity_id'];
                }
                elseif (in_array($p['type'], array(Conf_Privilege::$TYPE_MANJIAN_160512, Conf_Privilege::$TYPE_GIFT, Conf_Privilege::$TYPE_SPECIAL_PRICE)) && $p['activity_id'] > 0)
                {
                    $p['privilege_url'] = '/activity/promotion_manjian_update.php?mode=show&id=' . $p['activity_id'];
                    $p['info'] = $p['activity_id'];
                }
                elseif ($p['type'] == Conf_Privilege::$TYPE_COUPON || $p['type'] == Conf_Privilege::$TYPE_COUPON_VIP || $p['type'] == Conf_Privilege::$TYPE_FREIGHT)
                {
                    $oc = new Data_Dao('t_coupon');
                    $coupon_info = $oc->get($p['info']);
                    if ($coupon_info && $coupon_info['tid'] > 0)
                    {
                        $p['privilege_url'] = '/activity/coupon_update.php?mode=show&id=' . $coupon_info['tid'];
                    }
                }
                elseif ($p['type'] == Conf_Privilege::$TYPE_SPECIAL_GOODS)
                {
                    $p['info'] = '(sid:10349)';
                }
            }
        }

        // 添加销售人员数据
        if (!empty($this->order['saler_suid']))
        {
            $this->order['_saler'] = Admin_Api::getStaff($this->order['saler_suid']);
        }

        if (in_array($this->_uid, self::$EX_SUIDS))
        {
            $this->hasRole = TRUE;
        }

        foreach ($this->orderProducts as $product)
        {
            $this->originPriceTotal += $product['cost'] * $product['num'];
            //if (in_array($product['pid'], Conf_Order::$SAND_CEMENT_BRICK_PIDS))
            if (Shop_Api::isSandCementBrickBySkuinfo($product['sku']))
            {
                $this->sandPrice += $product['num'] * $product['price'];
            }
            else
            {
                $this->otherPrice += $product['num'] * $product['price'];
            }

            $this->totalWeight += $product['sku']['weight'] * $product['num'];
        }
        $this->sandPrice = round($this->sandPrice / 100, 2);
        $this->otherPrice = round($this->otherPrice / 100, 2);

        if ($this->order['step'] < Conf_Order::ORDER_STEP_SURE)
        {
            $this->lotteryRes = Activity_Lottery_Api::markLotteryWithOrder($this->order['cid'], $this->oid);
        }
        else
        {
            $this->lotteryRes = Activity_Lottery_Api::getLotteryWithOrder($this->order['cid'], $this->oid);
        }

        //配送日期，运费，搬运费再次确认
        if ($this->order['step'] < Conf_Order::ORDER_STEP_SURE)
        {
            $this->today = date('Y-m-d');
            list($this->deliveryDate, $deliveryTime) = explode(' ', $this->order['delivery_date']);
            $this->carryFee = Logistics_Api::calCarryFee($this->oid, $this->order['service'], $this->order['floor_num']);
            $this->freight = Logistics_Api::calFreightByAddress($this->oid, $this->order['city'], $this->order['district'], $this->order['ring_road'], $this->order['community_id'], $this->order['delivery_type']);
        }

        for ($i = 1; $i <= 30; $i++)
        {
            $this->floor[$i] = $i;
        }

        $orderPrivilege = Privilege_Api::getOrderPrivilege($this->oid);
        if (!empty($orderPrivilege))
        {
            foreach ($orderPrivilege as $privilege)
            {
                if ($privilege['type'] == Conf_Privilege::$TYPE_PRE_PAY && $privilege['amount'] > 0)
                {
                    $this->has96Privilege = TRUE;
                }
            }
        }

        // 获取订单排线信息
        if (!empty($this->order['line_id']))
        {
            $this->lineInfo = Logistics_Order_Api::getByLineId($this->order['line_id']);
            if (!empty($this->lineInfo))
            {
                $oidsInLine = explode(',', $this->lineInfo['oids']);
                $this->lineInfo['merge_order'] = array_diff($oidsInLine, array($this->oid));
                if (!empty($oidsInLine))
                {
                    //获取基础运费
                    $result = Order_Community_Api::getBaseDriverFeesByOids($oidsInLine);
                }
                else
                {
                    $result = array();
                }
            }
            else
            {
                $result = array();
            }
        }
        else
        {
            $result = Order_Community_Api::getBaseDriverFeesByOids(array($this->oid));
        }

        $rule = Conf_Driver::$DRIVER_FEE_RULES[Conf_Driver::$WAREHOUSE_DRIVER_FEE_RULES[$this->order['wid']]];
        if ($result['oid'] == $this->oid)
        {
            foreach (Conf_Driver::$CAR_MODEL as $key => $value)
            {
                $this->basePrices[$key] = $result['fee']['fee'][$key];
            }
        }
        else
        {
            foreach (Conf_Driver::$CAR_MODEL as $key => $value)
            {
                if ($this->order['city_id'] == Conf_City::LANGFANG)
                {
                    $this->basePrices[$key] = 0;
                }
                elseif ($this->order['city_id'] == Conf_City::TIANJIN)
                {
                    $this->basePrices[$key] = 0;
                }
                else
                {
                    $this->basePrices[$key] = $rule[$key]['second_order_fee'];
                }
            }
        }
        $maxCarrierFee = Logistics_Api::calCarryFee4Carrier($this->oid);
        switch ($this->order['service'])
        {
            case 1:
                $this->maxCarrierFee = $maxCarrierFee['ele'];
                break;
            case 2:
                $this->maxCarrierFee = $maxCarrierFee['common'] * $this->order['floor_num'];
                break;
        }
        $this->order['add_order'] = array_keys(Order_Api::getSonOrder($this->oid));

        //获取库存
        $sids = Tool_Array::getFields($this->orderProducts, 'sid');
        $ws = new Warehouse_Stock();
        if ($this->order['wid'] > 0)
        {
            $this->stocks = Tool_Array::list2Map($ws->getBulk($this->order['wid'], $sids), 'sid');
        }
        else
        {
            $this->stocks = array();
        }

        //评价
        $commentList = Comment_Api::getListByOrder($this->oid);
        $comment = array_shift($commentList['list']);
        if (!empty($comment))
        {
            $level = $comment['level'];
            $this->comment['level'] = Conf_Comment::$COMMENT_DESC[$level];
            if (!empty($comment['tag']))
            {
                $commentTag = explode(',', $comment['tag']);
                foreach ($commentTag as $tag)
                {
                    $this->comment['tags'][] = Conf_Comment::$COMMENT_TAGS[$level][$tag];
                }
            }
            $this->comment['note'] = $comment['note'];
            $this->comment['comment_all'] = $comment['comment_all'];
            $this->comment['comment_delivery'] = $comment['comment_delivery'];
            $this->comment['comment_carry'] = $comment['comment_carry'];
        }

        $t = time();
        $r = rand(100000, 999999);
        $s = rand(222222, 888888);
        $signStr = $this->oid . $t;
        $fmd5 = md5($signStr . 'hcsd8762j211%&31k}[//111!<>??:%$#@*(((^%%%');
        $sign = md5($fmd5 . $r);
        $payDetailUrl = sprintf("http://%s/order/detail_4_pay.php?oid=%d&st=%s&sr=%s&ss=%s&ssign=%s", C_H5_MAIN_HOST, $this->oid, $t, $r, $s, $sign);
        //$this->payImgSrc = sprintf('https://pan.baidu.com/share/qrcode?w=300&h=300&url=%s', urlencode($payDetailUrl));
        $this->payImgSrc = sprintf('https://b.bshare.cn/barCode?site=weixin&url=%s', urlencode($payDetailUrl));

        // 获取删除的商品列表
        $this->abnormalProducts = Order_Api::getOrderAbnormalProducts($this->oid);

        if ($this->order['city_id'] == Conf_City::LANGFANG)
        {
            $this->LangFangCarrierFeeRules = Conf_Coopworker::$LF_CARRIER_FEE_RULES;
        }
        //已回单并且是一个月之前配送
        if ($this->order['step'] == Conf_Order::ORDER_STEP_FINISHED && $this->order['delivery_date'] < date('Y-m-d H:i:s', strtotime('-1 month')))
        {
            $this->order['contact_phone'] = Str_Number::hideMobile($this->order['contact_phone']);
        }

        //毛利等计算
        $totalPrice = $totalCost = 0;
        foreach ($this->orderProducts as $product)
        {
            if ($product['status'] != Conf_Base::STATUS_NORMAL || $product['rid'] > 0)
            {
                continue;
            }

            $totalPrice += $product['num'] * $product['price'];
            $totalCost += $product['num'] * $product['cost'];
        }
        $grossIncome = $totalPrice - $totalCost;
        $this->profit['gross_income'] = round($grossIncome / 100, 2);
        $this->profit['gross_rate'] = round($grossIncome * 100 / $totalPrice, 2);

        $certificateion = Crm2_Certification_Api::getByCid($this->order['cid']);
        $this->isCertificate = $certificateion['step'];
        if (empty($certificateion) || $certificateion['step'] == Conf_User::CERTIFICATE_NEW || $certificateion['step'] == Conf_User::CERTIFICATE_DENY)
        {
            $this->isCertificate = Conf_User::CERTIFICATE_NEW;
        }

        if($this->order['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_EXCHANGED)
        {
            $this->order['_exchanged_info'] = Exchanged_Api::getExchanged($this->order['aftersale_id']);
        }elseif($this->order['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_TRAPS)
        {
            $this->order['_traps_info'] = Traps_Api::getTraps($this->order['aftersale_id']);
        }
        //=========xujianping begin=========
        //判断是否可以显示
        //1.修改运费和搬运费只能出库一天之内 如果跨月修改只能添加虚拟订单
        //2.出库之后不能修改优惠
        $shipTime = $this->order['ship_time'];//出库时间
        $currTime = time();//当前时间

        $shipTimeInt = $shipTime == '0000-00-00 00:00:00' ? 0 : strtotime($shipTime);

        $currMonthOrder = true;//是否当月订单
        $inOneDay = true; //是否在一天内
        $hasShipTime = $shipTimeInt > 0 ? true : false;//是否有发货时间
        $shipTimeMonth = date('m',$shipTimeInt);//出库时间的月份
        $currTimeMonth = date('m',$currTime);//当前时间的月份

        if ($shipTimeMonth != $currTimeMonth){ //判断当前月份是否和出库时间月份相同 不同代表跨月
            $currMonthOrder = false;
        }
        $shipTimeNextDayTimeStamp = strtotime('+1 day',$shipTimeInt);
        if ($currTime > $shipTimeNextDayTimeStamp){
            $inOneDay = false;
        }
        $this->timeArray = array('currMonthOrder'=>$currMonthOrder, 'hasShipTime'=>$hasShipTime, 'inOneDay'=>$inOneDay);
        //用户信息
        $this->uinfo = Crm2_Api::getUserList(array('cid'=>$this->order['cid']), 0, 1);

        //==============xujianping end================


        $this->addFootJs(array(
                             'js/apps/order.js', 'js/apps/order_log.js', 'js/apps/distance_fee.js', 'js/apps/edit_coopworker.js'
                         ));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $paymentTypesForFinance = Conf_Base::getPaymentTypes();

        // 添加商品的对话框
        $this->smarty->assign('payment_types', Conf_Base::getPaymentTypes());
        $this->smarty->assign('payment_types_finance', $paymentTypesForFinance);
        $this->smarty->assign('coopworker_payment_types', Conf_Base::getCoopWorkerPayentTypes($this->order['wid']));
        $this->smarty->assign('order', $this->order);
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('cate2_list_all', Conf_Sku::$CATE2);
        $this->smarty->assign('order_steps', Conf_Order::getOrderStepNames());
        $this->smarty->assign('delivery_types', Conf_Order::$DELIVERY_TYPES);
        $this->smarty->assign('coopworker_types', Conf_Base::getCoopworkerTypes());
        $this->smarty->assign('car_models', Conf_Driver::$CAR_MODEL);
        $this->smarty->assign('warehouse_list', Conf_Warehouse::getWarehouseByAttr('all'));
        $this->smarty->assign('carrier_fee', $this->carrierFee);
        $this->smarty->assign('customer', $this->customer['customer']);
        $this->smarty->assign('user', $this->user['user']);
        $this->smarty->assign('origin_order_products', $this->originOrderProducts);
        $this->smarty->assign('order_products', $this->orderProducts);
        $this->smarty->assign('refund_products', $this->refundProducts);
        $this->smarty->assign('privilege', $this->privilege);
        $this->smarty->assign('has_role', $this->hasRole);
        $this->smarty->assign('sand_price', $this->sandPrice);
        $this->smarty->assign('no_sand_price', $this->otherPrice);
        $this->smarty->assign('lottery_res', $this->lotteryRes);
        $this->smarty->assign('originPriceTotal', $this->originPriceTotal);
        $this->smarty->assign('reason', $this->reason);
        $this->smarty->assign('comment', $this->comment);
        $this->smarty->assign('profit', $this->profit);

        // 拣货小组
        $this->smarty->assign('driver_infos', $this->driverInfos);

        if (!empty($this->orderCarModelList))
        {
            $this->smarty->assign('order_car_model_list', $this->orderCarModelList);
        }
        $this->smarty->assign('order_driver_list', $this->orderDriverList);
        $this->smarty->assign('order_carrier_list', $this->orderCarrierList);
        $this->smarty->assign('max_carrier_fee', $this->maxCarrierFee);
        $this->smarty->assign('driver_fee_types', Conf_Driver::$DRIVER_FEE_TYPES);
        $this->smarty->assign('base_prices', $this->basePrices);
        $this->smarty->assign('car_models', Conf_Driver::$CAR_MODEL);
        $this->smarty->assign('decline_fee', Conf_Driver::$DRIVER_FEE_RULES[Conf_Driver::$WAREHOUSE_DRIVER_FEE_RULES[$this->order['wid']]]);

        $this->smarty->assign('all_salers', Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW));
        $this->smarty->assign('all_salers_on_the_job', Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW, 0));

        $this->smarty->assign('today', $this->today);
        $this->smarty->assign('delivery_date', $this->deliveryDate);
        $this->smarty->assign('carry_fee', $this->carryFee);
        $this->smarty->assign('freight', $this->freight);
        $this->smarty->assign('cur_carry_fee', $this->order['customer_carriage']);
        $this->smarty->assign('cur_freight', $this->order['freight']);
        $this->smarty->assign('floor', $this->floor);
        $this->smarty->assign('is_first_order', $this->isFirstOrder);
        $this->smarty->assign('has_96_privilege', $this->has96Privilege);
        $this->smarty->assign('line_info', $this->lineInfo);

        $this->smarty->assign('is_upgrade_warehouse', Conf_Warehouse::isUpgradeWarehouse($this->order['wid']));
        $this->smarty->assign('can_join_afternoon_activity', $this->canJoinAfternoonActivity);
        $this->smarty->assign('after_sale_place_order', Conf_Aftersale::$AFTER_SALE_PLACE_ORDER);
        $this->smarty->assign('stocks_list', $this->stocks);
        $this->smarty->assign('total_weight', $this->totalWeight);

        $this->smarty->assign('color_order', $this->colorOrder);

        $this->smarty->assign('del_and_picked_products', $this->abnormalProducts['del']);
        $this->smarty->assign('pay_img_src', $this->payImgSrc);

        $this->smarty->assign('lf_carrier_car_model_fees', $this->LangFangCarrierFeeRules);

        $this->smarty->assign('managing_modes', Conf_Base::getManagingModes());
        $this->smarty->assign('sales_privilege', Admin_Api::getSaleLeaderBySuid($this->_user, $this->order));
        $this->smarty->assign('gift_products', $this->giftProducts);
        $this->smarty->assign('discount_products', $this->discountProducts);
        $this->smarty->assign('activity_products_amount', $this->activity_products_amount);
        $this->smarty->assign('is_certificate', $this->isCertificate);


        $this->smarty->assign('timeArray', $this->timeArray);
        $uinfo = $this->uinfo['data'];
        $uinfo = array_values($uinfo);
        $uinfo = isset($uinfo[0]) ? $uinfo[0] : array();
        $this->smarty->assign('uinfo', $uinfo);

        $this->smarty->display('order/order_detail.html');
    }
}

$app = new App('pri');
$app->run();
