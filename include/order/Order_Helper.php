<?php

/**
 * 订单助手功能类
 *
 * 只是一个功能类，不是API，也不是func，所以不继承
 */
class Order_Helper
{
    /**
     * 计算订单用户应付金额
     *
     * @param $order
     * @param $minZero
     *
     * @return int
     */
    public static function calOrderNeedToPay($order, $minZero = TRUE)
    {
        $total = self::calOrderTotalPrice($order);
        $needToPay = $total - $order['real_amount'];

        if ($minZero)
        {
            $needToPay < 0 && $needToPay = 0;
        }

        return $needToPay;
    }

    /**
     * 计算订单总金额
     *
     * @param $order
     *
     * @return int
     */
    public static function calOrderTotalPrice($order)
    {
        $total = $order['price'] + $order['freight'] + $order['customer_carriage'] - $order['privilege'] - $order['refund'];
        $total < 0 && $total = 0;

        return $total;
    }

    /**
     * 计算订单应付总金额
     *
     * @param $order
     *
     * @return int
     */
    public static function calOrderPayableTotalPrice($order)
    {
        $total = $order['price'] + $order['freight'] + $order['customer_carriage'] - $order['privilege'];
        $total < 0 && $total = 0;

        return $total;
    }
    
    public static function calCustomerPrice($cid, $duty, $price)
    {
        $_duty = $duty==Conf_Base::HAS_DUTY? Conf_Base::getDuty($cid): 0;
            
        return round($price / (1 - $_duty));
    }

    /**
     * 实时计算订单商品的价钱.
     * 
     * @param int $oid
     */
    public static function getOrderProductsPriceWithRealTime($oid)
    {
        $oo = new Order_Order();
        $order = $oo->get($oid);
        $orderProducts = $oo->getProductsOfOrder($oid);
        
        $sp = new Shop_Product();
        $pids = Tool_Array::getFields($orderProducts, 'pid');
        $products = $sp->getBulk($pids);
        Shop_Api::decoratorProducts($products, $order['cid'], $order['city_id'], Conf_Activity_Flash_Sale::PALTFORM_BOTH,'real');
        
        $realTimePrice = 0;
        $changeInfos = array();
        $duty = ($order['has_duty']==Conf_Base::HAS_DUTY)? Conf_Base::getDuty($order['cid']) :0;
        
        foreach($orderProducts as $item)
        {
            $cPrice = $products[$item['pid']]['sale_price'];

            $realTimePrice += round($cPrice/(1-$duty)) * $item['num'];
            
            if ($cPrice != $item['price'])
            {
                $changeInfos[] = $item['pid'].':'.($item['price']/100).':'.($cPrice/100);
            }
        }
        
        //$duty = ($order['has_duty']==Conf_Base::HAS_DUTY)? Conf_Base::DUTY_20161129: 0;
        //$duty = ($order['has_duty']==Conf_Base::HAS_DUTY)? Conf_Base::getDuty($order['cid']) :0;
        //$realTimePrice = round($realTimePrice/(1-$duty));
        
        return array(
            'is_chg' => $order['price']==$realTimePrice? false: true,
            's_price' => $order['price'],
            'c_price' => $realTimePrice,
            'chg_info' => $changeInfos,
        );
    }
    
    /**
     * 检查订单和订单商品的城市的一致性
     * @param array $orderInfo      订单信息
     * @param array $orderProducts  订单已添加商品
     * @param array $newProducts    订单新添加商品
     */
    public static function checkOrderAndProductCity($orderInfo, $orderProducts, $newProducts=array())
    {
        if (empty($orderInfo) || (empty($orderProducts) && empty($newProducts) && $orderInfo['aftersale_type'] != Conf_Order::AFTERSALE_TYPE_REFUND))
        {
            return array('errno'=>1, 'errmsg'=>'sorry');
        }
   
        // 新添加商品
        $productCitys = array();
        foreach($newProducts as $_pinfo)
        {
            if (empty($_pinfo['city_id']))
            {
                return array('errno'=>1, 'errmsg'=>'添加商品没有城市属性！');
            }
            $productCitys[] = $_pinfo['city_id'];
        }
        // 原订单的商品
        foreach($orderProducts as $_opinfo)
        {
            $productCitys[] = $_opinfo['city_id'];
        }
        
        if (count(array_unique($productCitys)) > 1)
        {
            return array('errno'=>1, 'errmsg'=>'订单的商品不属于同一个城市！');
        }
        
        if (!empty($productCitys) && $productCitys[0] != $orderInfo['city_id'])
        {
            return array('errno'=>1, 'errmsg'=>'商品的城市属性和订单的城市属性不一致！');
        }
        
        return array('errno'=>0);
    }
    
     
    /**
     * 是否是加盟商订单.
     * 
     * 后期通过db查询输出结果
     */
    public static function isFranchiseeOrder($wid, $cityId=0)
    {
        $isOwn = Conf_Warehouse::isOwnWid($wid, $cityId);
        $isWAT = Conf_Warehouse::isAgentWid($wid);
        $isCoop = Conf_Warehouse::isCoopWid($wid);
        
        return !$isOwn && !$isWAT &&!$isCoop;
    }
    
    /**
     * 是否是好材自营订单.
     */
    public static function isOwnOrder($wid, $cityId=0)
    {
        return Conf_Warehouse::isOwnWid($wid, $cityId);
    }
    
    
    /**
     * 格式化订单，填充一下数据
     *
     * @param array $order
     */
    public static function formatOrder(array &$order)
    {
        //阶段信息
        $order['_step'] = Conf_Order::getOrderStepName($order['step']);
        if ($order['status'] == Conf_Base::STATUS_NORMAL)
        {
            $order['step_show'] = Conf_Order::getOrderStepShowName($order['step']);
        }
        else
        {
            $order['step_show'] = '已取消';
        }

        //日期信息
        if ($order['delivery_date'] == '0000-00-00')
        {
            $order['_delivery_date'] = '-';
        }
        else
        {
            $startDate = date('Y年m月d日', strtotime($order['delivery_date']));
            $startTime = date('G点', strtotime($order['delivery_date']));
            $endDate = date('Y年m月d日', strtotime($order['delivery_date_end']));
            $endTime = date('G点', strtotime($order['delivery_date_end']));
            if ($startDate == $endDate)
            {
                $order['_delivery_date'] = $startDate . ' ' . $startTime . '-' . $endTime;
            }
            else
            {
                $order['_delivery_date'] = $startDate . ' ' . $startTime . '-' . $endDate . ' ' . $endTime;
            }
            $order['_delivery_time_start'] = date('G', strtotime($order['delivery_date']));
            $order['_delivery_time_end'] = date('G', strtotime($order['delivery_date_end']));
            $order['_delivery_date_base'] = date('Y-m-d', strtotime($order['delivery_date_end']));
        }

        // 仓库名称
        $order['_warehouse_name'] = isset(Conf_Warehouse::$WAREHOUSES[$order['wid']]) ? Conf_Warehouse::$WAREHOUSES[$order['wid']] : '';
        $order['user_need_to_pay'] = Order_Helper::calOrderNeedToPay($order);
        $order['total_order_price'] = Order_Helper::calOrderTotalPrice($order);
        $order['_city'] = !empty($order['city']) && !empty($order['city_id']) ? Conf_Area::$CITY[$order['city']] : Conf_Area::$CITY[$order['city_id']];
        $order['_district'] = !empty($order['city']) && !empty($order['city_id']) ? Conf_Area::$DISTRICT[$order['city']][$order['district']] : Conf_Area::$DISTRICT[$order['city_id']][$order['district']];
        $order['_area'] = Conf_Area::$AREA[$order['district']][$order['ring_road']];

        $order['_oid'] = date('Ymd', strtotime($order['ctime'])) . '-' . $order['oid'];
        $order['has_finish'] = FALSE;
        if ($order['step'] == Conf_Order::ORDER_STEP_FINISHED && $order['paid'] == Conf_Order::HAD_PAID)
        {
            $order['has_finish'] = TRUE;
        }

        // 配货地址
        $addresses = explode(Conf_Area::Separator_Construction, $order['address'], 2);
        if (count($addresses) == 2)
        {
            $order['_community_name'] = $addresses[0];
            $order['_address'] = $addresses[1];
            $order['address'] = $addresses[0] . $addresses[1];
        }
        else
        {
            $order['_community_name'] = '';
            $order['_address'] = $order['address'];
        }

        $order['address_show'] = $order['_city'] . $order['_district'] . $order['_area'] . $order['address'];

        //拣货小组解析
        $order['_picking_group'] = json_decode($order['picking_group'], TRUE);

        //是否社区店仓库订单
        $order['is_community_warehouse'] = FALSE;
        if (Conf_Warehouse::isCommunityWarehouse($order['wid']))
        {
            $order['is_community_warehouse'] = TRUE;
        }

        $order['_delivery_type'] = Conf_Order::$DELIVERY_TYPES[$order['delivery_type']];
        $order['is_franchiess'] = self::isFranchiseeOrder($order['wid'], $order['city_id']);
    }

    /**
     * 格式化多个订单
     *
     * @param array $orders
     */
    public static function formatOrders(array &$orders)
    {
        foreach ($orders as &$order)
        {
            self::formatOrder($order);
        }
    }

    /**
     * 格式化退款单
     *
     * @param array $refund
     */
    public static function formatRefund(array &$refund)
    {
        //阶段信息
        $refund['_step'] = Conf_Refund::getRefundStepName($refund['step']);
    }

    /**
     * 格式化退款单列表
     *
     * @param array $refunds
     */
    public static function formatRefunds(array &$refunds)
    {
        foreach ($refunds as &$refund)
        {
            self::formatRefund($refund);
            $refund['_is_upgrade_wid'] = Conf_Warehouse::isUpgradeWarehouse($refund['wid']);
        }
    }

    /**
     * 根据管理员获取订单下一个可用状态
     *
     * @param $staff
     * @param $order
     *
     * @return int
     */
    public static function calOrderNextStep($order)
    {
        $step = $order['step'];
        $nextStep = 0;
        switch ($step)
        {
            case Conf_Order::ORDER_STEP_EMPTY :         //0 客户未确认
                break;
            case Conf_Order::ORDER_STEP_NEW :           //1 客户已确认
                $nextStep = Conf_Order::ORDER_STEP_SURE;
                break;
            case Conf_Order::ORDER_STEP_SURE :          //2 客服已确认
                $nextStep = Conf_Order::ORDER_STEP_HAS_DRIVER;
                break;
            case Conf_Order::ORDER_STEP_BOUGHT :        //3 已采购
                $nextStep = Conf_Order::ORDER_STEP_HAS_DRIVER;
                break;
            case Conf_Order::ORDER_STEP_HAS_DRIVER :    //4 已安排司机
                $nextStep = Conf_Order::ORDER_STEP_PICKED;
                break;
            case Conf_Order::ORDER_STEP_PICKED :        //5 已出库
                $nextStep = Conf_Order::ORDER_STEP_FINISHED;
                break;
            case Conf_Order::ORDER_STEP_FINISHED :      //7 已完成,已收款
            default:
                break;
        }
        
        return $nextStep;
    }
    
    public static function calOrderNextStep4Franchisee($order)
    {
        $step = $order['step'];
        $nextStep = 0;
        switch ($step)
        {
            case Conf_Order::ORDER_STEP_EMPTY :         //0 客户未确认
                break;
            case Conf_Order::ORDER_STEP_NEW :           //1 客户已确认
                $nextStep = Conf_Order::ORDER_STEP_HAS_DRIVER;
                break;
            case Conf_Order::ORDER_STEP_SURE :          //2 客服已确认
            case Conf_Order::ORDER_STEP_BOUGHT :        //3 已采购
            case Conf_Order::ORDER_STEP_HAS_DRIVER :    //4 已安排司机
                $nextStep = Conf_Order::ORDER_STEP_PICKED;
                break;
            case Conf_Order::ORDER_STEP_PICKED :        //5 已出库
                $nextStep = Conf_Order::ORDER_STEP_FINISHED;
                break;
            case Conf_Order::ORDER_STEP_FINISHED :      //7 已完成,已收款
            default:
                break;
        }
        
        return $nextStep;
    }
    
    public static function getOrderNextStep($staff, $order)
    {
        $step = $order['step'];
        $nextStep = 0;
        
//        $roles = explode(',', $staff['roles']);
//        $pr = new Permission_Role();
//        $roleInfos = $pr->getBulk($roles);
//        $rkeysArr = Tool_Array::getFields($roleInfos, 'rkey');
        
        $roleIds = explode(',', $staff['roles']);
        $rkeysArr = Permission_Api::getRolesRkey($roleIds);
        
        switch ($step)
        {
            case Conf_Order::ORDER_STEP_EMPTY :// 0,    //客户未确认
                break;
            case Conf_Order::ORDER_STEP_NEW :// 1,      //客户已确认
                if (in_array(Conf_Admin::ROLE_CS_NEW, $rkeysArr) || in_array(Conf_Admin::ROLE_ADMIN_NEW, $rkeysArr) || in_array(Conf_Admin::ROLE_AFTER_SALE_NEW, $rkeysArr) 
                    || (in_array(Conf_Admin::ROLE_SALES_NEW, $rkeysArr) && ($order['suid'] == $staff['suid']||in_array($order['saler_suid'], $staff['team_member']))))
                {
                    $nextStep = Conf_Order::ORDER_STEP_SURE;
                }
                break;
            case Conf_Order::ORDER_STEP_SURE :// 2,     //客服已确认
                if (in_array(Conf_Admin::ROLE_LM_NEW, $rkeysArr) || in_array(Conf_Admin::ROLE_BUYER_NEW, $rkeysArr) || in_array(Conf_Admin::ROLE_ADMIN_NEW, $rkeysArr))
                {
                    $nextStep = Conf_Order::ORDER_STEP_HAS_DRIVER; //modifyby guoqiangyang 2017-01-12
                }
                break;
            case Conf_Order::ORDER_STEP_BOUGHT :// 3,  //已采购
                if (in_array(Conf_Admin::ROLE_LM_NEW, $rkeysArr) || in_array(Conf_Admin::ROLE_ADMIN_NEW, $rkeysArr) || in_array(Conf_Admin::ROLE_CS_NEW, $rkeysArr))
                {
                    $nextStep = Conf_Order::ORDER_STEP_HAS_DRIVER;
                }
                break;
            case Conf_Order::ORDER_STEP_HAS_DRIVER :// 4,  //已安排司机
                if (in_array(Conf_Admin::ROLE_LM_NEW, $rkeysArr) || in_array(Conf_Admin::ROLE_WAREHOUSE_NEW, $rkeysArr) || in_array(Conf_Admin::ROLE_ADMIN_NEW, $rkeysArr) || in_array(Conf_Admin::ROLE_WAREHOUSE_MANAGER, $rkeysArr))
                {
                    $nextStep = Conf_Order::ORDER_STEP_PICKED;
                }
                break;
            case Conf_Order::ORDER_STEP_PICKED :// 5,   //已出库
                $nextStep = Conf_Order::ORDER_STEP_FINISHED;
                break;
            case Conf_Order::ORDER_STEP_FINISHED :// 7; //已完成,已收款
            default:
                break;
        }
        
        return $nextStep;
    }

    /**
     * 获取退款单下一状态
     *
     * @param $staff
     * @param $step
     *
     * @return array
     */
    public static function getRefundNextStep($staff, $step)
    {
        $buttonText = '';
        $nextStep = 0;

//        $roles = explode(',', $staff['roles']);
//        $pr = new Permission_Role();
//        $roleInfos = $pr->getBulk($roles);
//        $rkeysArr = Tool_Array::getFields($roleInfos, 'rkey');
        
//        $roleIds = explode(',', $staff['roles']);
//        $rkeysArr = Permission_Api::getRolesRkey($roleIds);

        switch ($step)
        {
            case Conf_Refund::REFUND_STEP_NEW :// 1,      //未确认
//                if (in_array(Conf_Admin::ROLE_ADMIN_NEW, $rkeysArr) || in_array(Conf_Admin::ROLE_AFTER_SALE_NEW, $rkeysArr))
//                {
                    $buttonText = '审核通过';
                    $nextStep = Conf_Refund::REFUND_STEP_SURE;
//                }
                break;
            case Conf_Refund::REFUND_STEP_SURE :// 2,     //已确认
//                if (in_array(Conf_Admin::ROLE_WAREHOUSE_NEW, $rkeysArr) || in_array(Conf_Admin::ROLE_ADMIN_NEW, $rkeysArr))
//                {
                    $buttonText = '确认入库';
                    $nextStep = Conf_Refund::REFUND_STEP_IN_STOCK;
//                }
                break;
            default:
                break;
        }

        return array('text' => $buttonText, 'next_step' => $nextStep);
    }

    /**
     * 获取换货单下一状态
     *
     * @param $staff
     * @param $step
     *
     * @return array
     */
    public static function getExchangedNextStep($staff, $step)
    {
        $buttonText = '';
        $nextStep = 0;

//        $roles = explode(',', $staff['roles']);
//        $pr = new Permission_Role();
//        $roleInfos = $pr->getBulk($roles);
//        $rkeysArr = Tool_Array::getFields($roleInfos, 'rkey');
        $roleIds = explode(',', $staff['roles']);
        $rkeysArr = Permission_Api::getRolesRkey($roleIds);
        
        switch ($step)
        {
            case Conf_Exchanged::EXCHANGED_STEP_NEW :// 1,      //未确认
                if (in_array(Conf_Admin::ROLE_ADMIN_NEW, $rkeysArr) || in_array(Conf_Admin::ROLE_AFTER_SALE_NEW, $rkeysArr))
                {
                    $buttonText = '审核通过';
                    $nextStep = Conf_Exchanged::EXCHANGED_STEP_SURE;
                }
                break;
            default:
                break;
        }

        return array('text' => $buttonText, 'next_step' => $nextStep);
    }

    /**
     * 获取补漏单下一状态
     *
     * @param $staff
     * @param $step
     *
     * @return array
     */
    public static function getTrapsNextStep($staff, $step)
    {
        $buttonText = '';
        $nextStep = 0;

//        $roles = explode(',', $staff['roles']);
//        $pr = new Permission_Role();
//        $roleInfos = $pr->getBulk($roles);
//        $rkeysArr = Tool_Array::getFields($roleInfos, 'rkey');
        
        $roleIds = explode(',', $staff['roles']);
        $rkeysArr = Permission_Api::getRolesRkey($roleIds);
        
        switch ($step)
        {
            case Conf_Traps::TRAPS_STEP_NEW :// 1,      //未确认
                if (in_array(Conf_Admin::ROLE_ADMIN_NEW, $rkeysArr) || in_array(Conf_Admin::ROLE_AFTER_SALE_NEW, $rkeysArr))
                {
                    $buttonText = '审核通过';
                    $nextStep = Conf_Traps::TRAPS_STEP_SURE;
                }
                break;
            default:
                break;
        }

        return array('text' => $buttonText, 'next_step' => $nextStep);
    }

    /**
     * 获取退款下一操作按钮
     *
     * @param $staff
     * @param $order
     *
     * @return string
     */
    public static function getRefundButtonHtml($staff, $order)
    {
        $step = $order['step'];
        $ret = self::getRefundNextStep($staff, $step);
        $buttonText = $ret['text'];
        $nextStep = $ret['next_step'];

        if ($buttonText)
        {
            $buttonHtml = sprintf('<a href="javascript:void(0);" class="btn btn-primary btn-lg _j_chg_refund_step" data-next_step="%d" style="margin-right:20px;">%s</a>', $nextStep, $buttonText);
        }

        return $buttonHtml;
    }

    /**
     * 获取换货单下一操作按钮
     *
     * @param $staff
     * @param $order
     *
     * @return string
     */
    public static function getExchangedButtonHtml($staff, $order)
    {
        $step = $order['step'];
        $ret = self::getExchangedNextStep($staff, $step);
        $buttonText = $ret['text'];
        $nextStep = $ret['next_step'];

        if ($buttonText)
        {
            $buttonHtml = sprintf('<a href="javascript:void(0);" class="btn btn-primary btn-lg _j_chg_exchanged_step" data-next_step="%d" style="margin-right:20px;">%s</a>', $nextStep, $buttonText);
        }

        return $buttonHtml;
    }

    /**
     * 获取补漏单下一操作按钮
     *
     * @param $staff
     * @param $order
     *
     * @return string
     */
    public static function getTrapsButtonHtml($staff, $order)
    {
        $step = $order['step'];
        $ret = self::getTrapsNextStep($staff, $step);
        $buttonText = $ret['text'];
        $nextStep = $ret['next_step'];

        if ($buttonText)
        {
            $buttonHtml = sprintf('<a href="javascript:void(0);" class="btn btn-primary btn-lg _j_chg_traps_step" data-next_step="%d" style="margin-right:20px;">%s</a>', $nextStep, $buttonText);
        }

        return $buttonHtml;
    }

    /**
     * 统计订单商品中特殊商品（沙子，水泥，砖）的数量和金额
     *
     * @param $orderProducts
     *
     * @return array
     */
    public static function statSpecialProducts($orderProducts)
    {
        $amount = 0;
        $total = 0;

        $isSandCementBrick = Shop_Api::isSandCementBrickByPids(array_keys($orderProducts));

        foreach ($orderProducts as $pid => $product)
        {
            //if (in_array($pid, Conf_Order::$SAND_CEMENT_BRICK_PIDS))
            if ($isSandCementBrick[$pid])
            {
                $amount += $product['num'] * $product['price'];
                $total += $product['num'];
            }
        }

        return array('amount' => $amount, 'total' => $total);
    }

    /**
     * 统计订单商品中非沙石类的商品金额和数量
     *
     * @param $orderProducts
     *
     * @return array
     */
    public static function statCommonProducts($orderProducts)
    {
        $amount = 0;
        $total = 0;
        $isSandCementBrick = Shop_Api::isSandCementBrickByPids(array_keys($orderProducts));

        foreach ($orderProducts as $pid => $product)
        {
            //if (!in_array($pid, Conf_Order::$SAND_CEMENT_BRICK_PIDS))
            if (!$isSandCementBrick[$pid])
            {
                $amount += $product['num'] * $product['price'];
                $total += $product['num'];
            }
        }

        return array('amount' => $amount, 'total' => $total);
    }

    /**
     * 格式化订单，以其数据符合app接口定义
     *
     * @param $list
     *
     * @return array
     */
    public static function formatAppOrderList($list, $uid)
    {
        //获得账户余额

        $newList = array();
        if (empty($list))
        {
            return $newList;
        }
        $oids = Tool_Array::getFields($list, 'oid');
        $pro = Order_Api::getPidsbyOids($oids);
        $spids = array_unique(Tool_Array::getFields($pro, 'pid'));
        $proInfo = Shop_Api::getProductInfos($spids);
        $refunds = Order_Api::getRefundList(array('oid' => $oids), 0, 0);
        $refundsMap = array();
        $refundPrivileges = array();
        $refundPrice = array();
        $today = strtotime('today');
        if (!empty($refunds['list']))
        {
            foreach ($refunds['list'] as $item)
            {
                if ($item['paid'] != Conf_Order::HAD_PAID)
                {
                    continue;
                }
                $refundsMap[$item['oid']][] = $item['rid'];
                $refundPrivileges[$item['oid']] += $item['refund_privilege'] + $item['adjust'];
                $refundPrice[$item['oid']] += $item['price'] + $item['refund_freight'] + $item['refund_carry_fee'];
            }
        }
        $refundFees = Refund_Api::getRefundFeeByOids($oids, TRUE);
        $commentList = Comment_Api::getList(array('oid' => $oids), 0, 0);
        $commentListMap = array();
        if (!empty($commentList['list']))
        {
            $commentListMap = Tool_Array::list2Map($commentList['list'], 'oid');
        }

        $products = array();
        foreach ($pro as $v)
        {
            if (!isset($proInfo[$v['pid']]['sku']['_pic']['middle']))
            {
                $imgurl = 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/app_icon/default_pic.png';
            }
            else
            {
                $imgurl = $proInfo[$v['pid']]['sku']['_pic']['middle'];
            }
            $products[] = array(
                'oid' => $v['oid'], 'pid' => $v['pid'], 'num' => (int)$v['num'], 'imgurl' => $imgurl,
            );
        }

        $zitiAddress = Conf_Warehouse::getZitiAddress();
        foreach ($list as $oid => $order)
        {
            $orderPrice = $order['price'] + $order['freight'] + $order['customer_carriage'] - $order['privilege'];
            $orderPrice = $orderPrice - $refundPrice[$oid];
            $orderPrice = $orderPrice + $refundPrivileges[$oid] + $refundFees[$oid]['freight'] + $refundFees[$oid]['carry_fee'];
            $oid = $order['oid'];
            $payInfo = Order_Api::getOrderPayInfo($oid, $uid);
            $account_amount = $payInfo['account_amount'];
            $newOrder = array(
                'oid' => $order['oid'],
                'paid' => $order['paid'],
                'city' => $order['_city'],
                'city_id' => $order['city_id'],
                'status' => $order['status'],
                'district' => $order['_district'],
                'address' => $order['address'],
                'real_amount' => (string)(($order['price'] + $order['freight'] + $order['customer_carriage'] - $order['privilege']) / 100),
                'total_price' => (string)($orderPrice / 100),
                'will_pay_amount' => (string)(round($payInfo['user_need_to_pay'] / 100, 2)),
                'ocode' => date('Ymd', strtotime($order['ctime'])) . '-' . $order['oid'],
            );
            foreach ($products as $k => $v)
            {
                if ($v['oid'] == $order['oid'])
                {
                    $newOrder['item'][] = $v;
                }
            }
            if (!isset($newOrder['item']))
            {
                $newOrder['item'] = array();
            }
            $newOrder['need_to_pay'] = (string)($account_amount > $orderPrice ? 0 : round(($orderPrice - $account_amount) / 100, 2));
            $newOrder['use_account'] = (string)($account_amount > $orderPrice ? round($orderPrice / 100, 2) : round(($account_amount) / 100, 2));
            $newOrder['account_amount'] = (string)round($account_amount / 100, 2);

            if ($order['status'] == Conf_Base::STATUS_NORMAL && $order['step'] < Conf_Order::ORDER_STEP_SURE)
            {
                $newOrder['type_title'] = '待确认';
            }
            else if ($order['status'] == Conf_Base::STATUS_NORMAL && $order['step'] < Conf_Order::ORDER_STEP_HAS_DRIVER)
            {
                $newOrder['type_title'] = '待配货';
            }
            else if ($order['status'] == Conf_Base::STATUS_NORMAL && $order['step'] < Conf_Order::ORDER_STEP_FINISHED)
            {
                $newOrder['type_title'] = '待收货';
            }
            else if ($order['status'] == Conf_Base::STATUS_NORMAL && $order['step'] == Conf_Order::ORDER_STEP_FINISHED)
            {
                $newOrder['type_title'] = '已完成';
            }
            else if ($order['status'] == Conf_Base::STATUS_CANCEL && $order['step'] > 0)
            {
                $newOrder['type_title'] = '已取消';
            }

            if (!empty($refundsMap[$order['oid']]))
            {
                $newOrder['type_title'] = '有退货';
            }

            if ($order['status'] == Conf_Base::STATUS_NORMAL && $order['paid'] != 1)
            {
                $newOrder['where_to_go'] = 1;
            }
            else if (($order['paid'] == 1 && $order['step'] != Conf_Order::ORDER_STEP_PICKED && $order['status'] == Conf_Base::STATUS_NORMAL) || $order['status'] == Conf_Base::STATUS_CANCEL)
            {
                $newOrder['where_to_go'] = 2;
            }
            else if ($order['paid'] == 1 && $order['step'] == Conf_Order::ORDER_STEP_PICKED && $order['status'] == Conf_Base::STATUS_NORMAL)
            {
                $newOrder['where_to_go'] = 3;
            }

            //订单详情页底部的两个个按钮显示
            //按钮1：不显示（0），取消订单（1），申请退货（2）
            //按钮2：不显示（0），去支付（1），再次购买（2），订单评价（3），查看物流（4）
            $buttonData = self::getButtonsByOrder($order, $commentListMap[$order['oid']], FALSE);
            $newOrder['button_1'] = $buttonData['button_1'];
            $newOrder['button_2'] = $buttonData['button_2'];

            if ($order['ship_time'] > 0)
            {
                list($day, $time) = explode(' ', $order['ship_time']);
                if ($today - strtotime($day) > 14 * 86400)
                {
                    $newOrder['can_refund'] = FALSE;
                }
                else
                {
                    $newOrder['can_refund'] = TRUE;
                }
            }
            else
            {
                $newOrder['can_refund'] = TRUE;
            }
            
            if ($order['city_id'] == Conf_City::CHONGQING && $order['delivery_type'] == Conf_Order::DELIVERY_BY_YOURSELF)
            {
                $newOrder['address'] = $zitiAddress[$order['wid']]['address'];
            }
            $newOrder['is_franchisee'] = self::isFranchiseeOrder($order['wid'], $order['city_id']);
            
            $newList[$oid] = $newOrder;
        }
        krsort($newList);
        //将未支付的优先显示
        $order_unpaid = array();
        $order_paid = array();
        foreach ($newList as $order)
        {
            if ($order['paid'] != 1 && $order['status'] == Conf_Base::STATUS_NORMAL)
            {
                $order_unpaid[] = $order;
            }
            else
            {
                $order_paid[] = $order;
            }
        }
        $ret = array_merge($order_unpaid, $order_paid);

        return $ret;
    }

    /**
     * 格式化订单详情，以其数据符合app接口定义
     *
     * @param $order
     *
     * @return array
     */
    public static function formatAppOrderDetail($order)
    {
        $newOrder = array();
        if (empty($order))
        {
            return $newOrder;
        }

        $service = '不上楼';
        if ($order['service'] == 1)
        {
            $service = '送货上楼 电梯上楼';
        }
        else if ($order['service'] == 2)
        {
            $service = '送货上楼 楼梯上楼';
        }

        $price = ($order['price'] == 0) ? $order['price'] : '￥' . round($order['price'] / 100, 2);
        $customer_carriage = ($order['customer_carriage'] == 0) ? $order['customer_carriage'] : '￥' . round($order['customer_carriage'] / 100, 2);
        $freight = ($order['freight'] == 0) ? $order['freight'] : '￥' . round($order['freight'] / 100, 2);
        $privilege = ($order['privilege'] == 0) ? $order['privilege'] : '￥' . round($order['privilege'] / 100, 2);

        $refundPrice = $refundPrivilege = $refundCarryFee = $refundFreight = $adjust = 0;
        //退款
        $refundList = Order_Api::getRefundList(array('oid' => $order['oid']), 0, 0);
        if (!empty($refundList['list']))
        {
            foreach ($refundList['list'] as $refund)
            {
                if ($refund['paid'] != Conf_Order::HAD_PAID)
                {
                    continue;
                }
                $refundPrice += $refund['price'] + $refund['refund_freight'] + $refund['refund_carry_fee'];
                $refundPrivilege += $refund['refund_privilege'];
                $adjust += $refund['adjust'];
            }
        }
        $refundPriceShow = ($refundPrice == 0) ? 0 : '￥' . round($refundPrice / 100, 2);
        $refundPrivilegeShow = ($refundPrivilege == 0) ? 0 : '￥' . round($refundPrivilege / 100, 2);

        $refundFee = Refund_Api::getRefundFeeByOid($order['oid'], TRUE);
        $refundCarryFeeShow = ($refundFee['carry_fee'] == 0) ? 0 : '￥' . round($refundFee['carry_fee'] / 100, 2);
        $refundFreightShow = ($refundFee['freight'] == 0) ? 0 : '￥' . round($refundFee['freight'] / 100, 2);

        $adjustShow = ($adjust == 0) ? 0 : '￥' . round($adjust / 100, 2);

        $newOrder = array(
            'oid' => $order['oid'],
            'paid' => $order['paid'],
            'ctime' => $order['ctime'],
            'ocode' => $order['_oid'],
            'city_id' => $order['city_id'],
            'contact_name' => $order['contact_name'],
            'customer_note' => $order['customer_note'],
            'contact_phone' => $order['contact_phone'],
            'customer_payment_type' => $order['customer_payment_type'],
            'address' => $order['_city'] . $order['_district'] . ' ' . $order['address'],
            '_delivery_date' => $order['_delivery_date'],
            '_service' => $service,
            'pay_way' => !empty($order['customer_payment_type']) ? Conf_Base::$CUSTOMER_PAYMENT_TYPE[$order['customer_payment_type']]:($order['paid']==Conf_Order::UN_PAID ? '未付款': '余额支付'),
            'fee_items' => array(
                array('name' => '累计货款', 'value' => $price, 'type' => 1), array('name' => '搬运费', 'value' => $customer_carriage, 'type' => 2), array('name' => '运费', 'value' => $freight, 'type' => 3), array('name' => '优惠', 'value' => $privilege, 'type' => 4),
            ),
        );

        if (!empty($refundList['list']))
        {
            $newOrder['fee_items'][] = array('name' => '退货金额', 'value' => $refundPriceShow, 'type' => 4);
            $newOrder['fee_items'][] = array('name' => '优惠退款', 'value' => $refundPrivilegeShow, 'type' => 3);
            $newOrder['fee_items'][] = array('name' => '退货搬运费', 'value' => $refundCarryFeeShow, 'type' => 3);
            $newOrder['fee_items'][] = array('name' => '退货运费', 'value' => $refundFreightShow, 'type' => 3);
            $newOrder['fee_items'][] = array('name' => '少退货款', 'value' => $adjustShow, 'type' => 3);
        }

        $newOrder['total_order_price'] = $order['price'] + $order['freight'] + $order['customer_carriage'] - $order['privilege'];
        $newOrder['total_order_price'] = $newOrder['total_order_price'] - $refundPrice;
        $newOrder['total_order_price'] = $newOrder['total_order_price'] + $refundPrivilege + $refundFee['carry_fee'] + $refundFee['freight'] + $adjust;

        //不区分货到付款和在线支付
        //下单
        if (($order['status'] == Conf_Base::STATUS_NORMAL && $order['step'] < Conf_Order::ORDER_STEP_SURE) || $order['status'] == Conf_Base::STATUS_CANCEL)
        {
            $newOrder['delivery_state'] = 1;
        }
        //确认
        else if ($order['status'] == Conf_Base::STATUS_NORMAL && $order['step'] < Conf_Order::ORDER_STEP_HAS_DRIVER)
        {
            $newOrder['delivery_state'] = 2;
        }
        //配货
        else if ($order['status'] == Conf_Base::STATUS_NORMAL && $order['step'] < Conf_Order::ORDER_STEP_PICKED)
        {
            $newOrder['delivery_state'] = 3;
        }
        //出库
        else if ($order['status'] == Conf_Base::STATUS_NORMAL && $order['step'] < Conf_Order::ORDER_STEP_FINISHED)
        {
            $newOrder['delivery_state'] = 4;
        }
        //完成
        else if ($order['status'] == Conf_Base::STATUS_NORMAL && $order['step'] == Conf_Order::ORDER_STEP_FINISHED)
        {
            $newOrder['delivery_state'] = 5;
        }
        else
        {
            $newOrder['delivery_state'] = 6;
        }
        //支付状态
        if ($order['paid'] == 1)
        {
            $newOrder['is_paid'] = '已支付';
        }
        else if ($order['paid'] == 2)
        {
            $newOrder['is_paid'] = '部分付款';
        }
        else
        {
            $newOrder['is_paid'] = '未付款';
        }

        if ($newOrder['customer_payment_type'] == Conf_Base::CUSTOMER_PT_ONLINE_PAY)
        {
            $newOrder['pay_way'] = '在线支付';
        }
        else if ($newOrder['customer_payment_type'] == Conf_Base::CUSTOMER_PT_OFFLINE_PAY)
        {
            $newOrder['pay_way'] = '货到付款';
        }
        $newOrder['user_need_to_pay'] = (string)($order['user_need_to_pay'] / 100);
        $newOrder['is_franchisee'] = self::isFranchiseeOrder($order['wid'], $order['city_id']);
        $newOrder['is_show_step_flow'] = $newOrder['is_franchisee']? false: true;

        return $newOrder;
    }

    public static function fromatDriverOrder($order, $wid, $uid)
    {
        Order_Helper::formatOrder($order);
        $product = Order_Api::getOrderProducts($order['oid']);
        $ocode = date('Ymd', strtotime($order['ctime'])) . $order['oid'];

        $service = '不上楼';
        if ($order['service'] == 1)
        {
            $service = '送货上楼 电梯上楼';
        }
        else if ($order['service'] == 2)
        {
            $service = '送货上楼 楼梯上楼';
        }

        //支付状态
        if ($order['paid'] == 1)
        {
            $is_paid = '已支付';
        }
        else if ($order['paid'] == 2)
        {
            $is_paid = '部分付款';
        }
        else
        {
            $order['paid'] = 2;
            $is_paid = '未付款';
        }

        $deliver_time = $order['_delivery_date'];
        $address = $order['_city'] . $order['_district'] . ' ' . $order['address'];

        //商品数量
        $product_num = 0;
        foreach ($product['products'] as $product)
        {
            $product_num += $product['num'];
        }

        $community = Order_Community_Api::get($order['community_id']);
        $community_Fee = Order_Community_Api::getDistanceAndFeeListNew($order['community_id'], $wid);
        $lat = $community['lat'];//纬度
        $lng = $community['lng'];//经度
        $price = $order['total_order_price'];

        //销售信息
        if ($order['saler_suid'])
        {
            $sale = Admin_Api::getStaff($order['saler_suid']);
        }
        else
        {
            $sale['mobile'] = '';
        }

        //客户和收货人信息
        $user = Crm2_Api::getUserInfo($order['uid'], TRUE, FALSE);

        $isFirst = '';
        if ($user['customer']['level_for_sys'] == Conf_User::CRM_SYS_LEVEL_VIP)
        {
            $isFirst = 'VIP';
        }
        $isFirst = Order_Api::isFristOrder($order['oid'], $order) ? '首单' : $isFirst;

        $contact = array();
        /*if (!empty($user['user']['mobile']))
        {
            $phones = explode(',', $user['customer']['all_user_mobiles']);
            foreach ($phones as $phone)
            {
                $contact[] = array('name' => '客户', 'phone' => $phone);
            }
        }*/
        $contact[] = array('name' => '接货人', 'phone' => $order['contact_phone']);

        $driver_orders = Logistics_Coopworker_Api::getByOids(array($order['oid']));
        $is_arrive = 1;
        foreach ($driver_orders as $driver_order)
        {
            if ($driver_order['arrival_time'] == '0000-00-00 00:00:00' && $driver_order['cuid'] == $uid)
            {
                $is_arrive = 2;
            }
        }

        return array(
            'oid' => $order['oid'], 
            'ocode' => $order['oid'], 
            'title' => $isFirst, 
            'deliver_time' => $deliver_time, 
            'address' => $address, 
            'product_num' => $product_num, 
            'distance' => (string)$community_Fee['distance'], 
            'price' => (string)round($price / 100, 2), 
            'service' => $service, 
            'lat' => $lat, 
            'lon' => $lng, 
            'step' => $order['step'], 
            'paid' => $order['paid'], 
            'is_arrive' => $is_arrive, 
            'url' => 'http://'. DRIVER_HOST .'/v1.0/order/detail', 
            'is_paid' => $is_paid,
            'saler_phone' => array($sale['mobile']), 'contact_phone' => $contact,
        );
    }

    public static function formatAppOrderProduct($products, $refundProducts)
    {
        $newProducts = array();

        if (empty($products))
        {
            return $newProducts;
        }

        $refundNumMap = array();
        if (!empty($refundProducts))
        {
            foreach ($refundProducts as $refundPro)
            {
                if($refundPro['num'] == 0)
                {
                    continue;
                }
                $has_refund_num = $refundPro['picked']+$refundPro['damaged_num'];
                if($has_refund_num > 0)
                {
                    $refundNumMap[$refundPro['pid']] += $has_refund_num;
                }else{
                    $refundNumMap[$refundPro['pid']] += $refundPro['apply_rnum'];
                }
            }
        }

        foreach ($products as $product)
        {
            $newProduct = array(
                'pid' => $product['product']['pid'],
                'title' => $product['sku']['title'],
                'alias' => '',
                '_model' => $product['sku']['package'],
                'price' => (string)($product['ori_price'] / 100),
                'num' => $product['num'],
                'unit' => $product['sku']['unit'],
                'pic' => $product['sku']['_pic'],
                'icon' => '',
            );

            $newProduct['refund_num'] = intval($refundNumMap[$product['product']['pid']]);

            if (!empty($product['sku']['alias']))
            {
                $newProduct['alias'] = $product['sku']['alias'];
            }

            if (!empty($product['product']['sales_type']))
            {
                $newProduct['icon'] = WWW_HOST . '/i/' . $product['product']['sales_type'] . '.png';
            }

            $newProducts[] = $newProduct;
        }

        return $newProducts;
    }

    public static function calProductTypeAndAmount($products)
    {
        $data = array(
            'common_list' => array(), 'common_amount' => 0, 'sand_list' => array(), 'sand_amount' => 0, 'total' => 0,
        );

        if (empty($products))
        {
            return $data;
        }

        $pids = array();
        foreach ($products as $p)
        {
            if (!empty($p['_package']))
            {
                foreach ($p['_package'] as $pack)
                {
                    $pids[] = $pack['pid'];
                }
            }
            else
            {
                $pids[] = $p['pid'];
            }
        }

        $isSandCementBrick = Shop_Api::isSandCementBrickByPids($pids);
        foreach ($products as $k => $p)
        {
            if (!empty($p['_package']))
            {
                $price = 0;
                foreach ($p['_package'] as $pack)
                {
                    $pid = $pack['pid'];
                    $price = $pack['num'] * $pack['price'];
                    if ($pack['rid'] > 0)
                    {
                        $price = 0 - $price;
                    }

                    if ($isSandCementBrick[$pid] != 1)
                    {
                        $data['common_list'][$k] = $pack;
                        $data['common_amount'] += $price;
                    }
                    else
                    {
                        $data['sand_list'][$k] = $pack;
                        $data['sand_amount'] += $price;
                    }
                }
            }
            else
            {
                $pid = isset($p['product']) ? $p['product']['pid'] : $p['pid'];
                $price = $p['price'] * $p['num'];
                if ($p['rid'] > 0)
                {
                    $price = 0 - $price;
                }

                if ($isSandCementBrick[$pid] != 1)
                {
                    $data['common_list'][$k] = $p;
                    $data['common_amount'] += $price;
                }
                else
                {
                    $data['sand_list'][$k] = $p;
                    $data['sand_amount'] += $price;
                }
            }

            $data['total'] += $price;
        }

        return $data;
    }

    /**
     * 获取订单商品按砂石类＼美巢类＼特价类＼热卖类
     *
     * @param $products
     *
     * @return array
     */
    public static function getOrderProductsPidsByType($products, $pids)
    {
        $data = array(
            'sand_list' => array(), 'meichao_list' => array(), 'special_list' => array(), 'hot_list' => array(),
        );

        if (empty($products))
        {
            return $data;
        }

        $tmp_pids_list = Shop_Api::isSandCementBrickByPids($pids);

        foreach ($products as $k => $p)
        {
            $pid = $p['product']['pid'];
            if ($tmp_pids_list[$pid] == 1)
            {
                $data['sand_list'][] = $pid;
            }
            if ($p['sku']['bid'] == Conf_Sku::MEICHAO_BRAND_ID)
            {
                $data['meichao_list'][] = $pid;
            }
            if ($p['product']['sales_type'] == Conf_Sku::PRODUCT_SALES_SPECIAL)
            {
                $data['special_list'][] = $pid;
            }
            if ($p['product']['sales_type'] == Conf_Sku::PRODUCT_SALES_HOT)
            {
                $data['hot_list'][] = $pid;
            }
        }

        return $data;
    }

    public static function getShowDeliveryData($deliveryDate, $deliveryDateEnd = '')
    {
        $startDate = date('Y年m月d日', strtotime($deliveryDate));
        $startTime = date('G点', strtotime($deliveryDate));

        $endTime = '';
        if (!empty($deliveryDateEnd))
        {
            $endTime = date('G点', strtotime($deliveryDateEnd));
        }

        return $startDate . ' ' . $startTime . (!empty($endTime) ? '-' . $endTime : '');
    }

    public static function getButtonsByOrder($order, $comment = array(), $isDetail = TRUE)
    {
        if (empty($comment))
        {
            $commentList = Comment_Api::getListByOrder($order['oid']);
            $comment = array_shift($commentList['list']);
        }

        //订单详情页底部的两个个按钮显示
        //按钮1：不显示（0），取消订单（1），申请退货（2）
        //按钮2：不显示（0），去支付（1），再次购买（2），订单评价（3），查看物流（4）
        $data = array();
        $data['button_1'] = 0;
        $data['button_2'] = 0;
        if ($order['status'] == Conf_Base::STATUS_CANCEL)
        {
            $data['button_2'] = 2;
        }
        else
        {
            if ($order['paid'] != Conf_Order::HAD_PAID)
            {
                $data['button_2'] = 1;
                if ($order['step'] < Conf_Order::ORDER_STEP_SURE)
                {
                    $data['button_1'] = 1;
                }
                else if ($order['step'] >= Conf_Order::ORDER_STEP_PICKED)
                {
                    $data['button_1'] = 2;
                }
            }
            else
            {
                if ($order['step'] < Conf_Order::ORDER_STEP_SURE && $order['paid'] == Conf_Order::UN_PAID)
                {
                    $data['button_1'] = 1;
                }
                else if ($order['step'] >= Conf_Order::ORDER_STEP_PICKED)
                {
                    $data['button_1'] = 2;
                }

                if ($order['step'] == Conf_Order::ORDER_STEP_PICKED && !$isDetail)
                {
                    $data['button_2'] = 4;
                }
                else if ($order['step'] == Conf_Order::ORDER_STEP_FINISHED && empty($comment))
                {
                    $data['button_2'] = 3;
                }
                else
                {
                    $data['button_2'] = 2;
                }
            }
        }
        if($order['aftersale_type'] > 0)
        {
            $data['button_1'] = 0;
            if($data['button_2'] == 2)
            {
                $data['button_2'] = 0;
            }
        }

        return $data;
    }

    public static function getWidByCityAndAddress($cityId, $address)
    {
        $wid = 0;
        if ($cityId == Conf_City::LANGFANG)
        {
            $wid = Conf_Warehouse::WID_LF1;
        }
        else
        {
            $beianheAddrs = array('北安河小区', '安河家园');
            foreach ($beianheAddrs as $addr)
            {
                if (strpos($address, $addr) !== false)
                {
                    $wid = Conf_Warehouse::WID_101;
                    break;
                }
            }
        }

        return $wid;
    }
    
    /**
     * 当前操作人是否可以处理订单.
     * 
     *  - 加盟商订单，只有加盟商自己可以确认, HC-staff 不能确认
     *  - HC订单，HC-staff自己处理
     */
    public static function canDealOrder($orderInfo, $staff)
    {
        $suid = isset($staff['fid'])? $staff['fid']: $staff['suid'];
        
        //if ($suid == 1029) return true;
        
        $isFranchiseeOrder = Order_Helper::isFranchiseeOrder($orderInfo['wid'], $orderInfo['city_id']);
        if ($isFranchiseeOrder)
        {
            if ($suid<=Conf_Admin::SELF_STAFF_SUID || $staff['city_id']!=$orderInfo['city_id'])
            {
                throw new Exception('加盟商订单，只能有加盟商自行处理');
            }
        }
        else
        {
            if ($suid>Conf_Admin::SELF_STAFF_SUID)
            {
                throw new Exception('好材自营订单，只能有好材人员处理');
            }
        }
        
        return true;
    }

    /**
     * 获取时间筛选
     *
     */
    public static function getOrderDateList()
    {
        $end_year = date('Y',time());
        $start_year = '2015';
        $dataList = array(
            '3_month' => array('name' => '近三个月', 'delivery_date_begin' => date('Y-m-d', strtotime('-3 month')), 'delivery_date_end' => ''),
            'today' => array('name' => '今天', 'delivery_date_begin' => date('Y-m-d', time()), 'delivery_date_end' => date('Y-m-d', time())),
            'tomorrow' => array('name' => '明天', 'delivery_date_begin' => date('Y-m-d', strtotime('+1 day')), 'delivery_date_end' => date('Y-m-d', strtotime('+1 day'))),
            'yesterday' => array('name' => '昨天', 'delivery_date_begin' => date('Y-m-d', strtotime('-1 day')), 'delivery_date_end' => date('Y-m-d', strtotime('-1 day'))),
        );
        for($i=$end_year;$i>=$start_year;$i--)
        {
            $key = $i.'_year';
            $dataList[$key] = array('name' => $i.'年', 'delivery_date_begin' => $i.'-01-01', 'delivery_date_end' => date('Y-m-d', strtotime(($i+1).'-01-01')-3600));
        }
        return $dataList;
    }
}
