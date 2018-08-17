<?php

/**
 * 客户消费,预存等统计.
 *
 * @author guoqiang yang
 * @date 2017-04-12 
 */

class Crm2_Stat_Api extends Base_Api
{
    
    /**
     * 统计一个客户的所有消费数据.
     * 
     * @param type $cid
     */
    public static function statAllConsume4Customer($cid)
    {
        $statFromOrder = self::statOrderDatas4Customer($cid);
        $statFromRefund = self::statRefundDatas4Customer($cid);
        $statFromAmount = self::statCustomerAmount4Customer($cid);
        
        return array_merge($statFromOrder, $statFromRefund, $statFromAmount);
    }
    
    /**
     * 为客户统计订单相关的消费数据.
     * 
     * @param int $cid
     * 
     * @todo 因为数据不是很大，暂且一次获取用户的全部订单
     */
    public static function statOrderDatas4Customer($cid)
    {
        $oo = new Order_Order();
        $where = sprintf('status=0 and cid=%d and step>=%d and source_oid=0 and aftersale_id=0', $cid, Conf_Order::ORDER_STEP_PICKED);
        $order = array('oid', 'asc');
        $fields = array('oid', 'price', 'freight', 'customer_carriage', 'privilege', 'refund', 'real_amount', 'paid', 'source', 'ctime');
        $orderList = $oo->getListRawWhereWithoutTotal($where, $order, 0, 0, $fields);

        $datas = array(
            'first_order_date'  => '0000-00-00',    //首单日期
            'second_order_date' => '0000-00-00',    //第二次下单日期
            'last_order_date'   => '0000-00-00',    //最后下单时间/最后订单时间
            'order_num'         => 0,               //总单数
            'online_order_num'  => 0,               //在线订单数
            //'account_balance'   => 0,               //客户总应付 （欠款）
            'order_amount'      => 0,               //总购买额:订单商品总金额
            'total_amount'      => 0,               //总消费额:货款+运费+搬运费-优惠（订单应收）
            'total_privilege'   => 0,               //总优惠
        );
        
        $counter = 0;
        $customerInfo = Crm2_Api::getCustomerInfo($cid);
        $datas['order_num'] = count($orderList);
        foreach($orderList as $oinfo)
        {
            $counter++;
            
            if ($counter == 1)
            {
                $datas['first_order_date'] = substr($oinfo['ctime'], 0, 10);
                $customerInfo['customer']['level_for_saler'] == Conf_User::SALER_LEVEL_NEW && $datas['level_for_saler'] = Conf_User::SALER_LEVEL_FIRST;
            }
            else if ($counter == 2)
            {
                $datas['second_order_date'] = substr($oinfo['ctime'], 0, 10);
                $customerInfo['customer']['level_for_saler'] == Conf_User::SALER_LEVEL_FIRST && $datas['level_for_saler'] = Conf_User::SALER_LEVEL_CASH_SALE;
            }else if($counter > 2)
            {
                unset($datas['level_for_saler']);
            }
            
            if ($counter == $datas['order_num'])
            {
                $datas['last_order_date'] = substr($oinfo['ctime'], 0, 10);
            }
            
            // 未完全支付，计算欠款; 此处不需要更新，在记录客户财务流水时已经更新了
//            if ($oinfo['paid'] != Conf_Order::HAD_PAID)
//            {
//                $datas['account_balance'] += $oinfo['price']+$oinfo['freight']+$oinfo['customer_carriage']
//                                            -$oinfo['privilege']-$oinfo['refund']-$oinfo['real_amount'];
//            }
            
            // 在线订单数
            $onlineType = array(Conf_Order::SOURCE_WEIXIN, Conf_Order::SOURCE_APP_ANDROID, Conf_Order::SOURCE_APP_IOS);
            if (in_array($oinfo['source'], $onlineType))
            {
                $datas['online_order_num']++;
            }
            
            $datas['order_amount'] += $oinfo['price'];
            $datas['total_amount'] += $oinfo['price']+$oinfo['freight']+$oinfo['customer_carriage']-$oinfo['privilege'];
            $datas['total_privilege'] += $oinfo['privilege'];
        }
        
        return $datas;
    }
    
    /**
     * 为客户统计退单相关的数据.
     * 
     * @param int $cid
     */
    public static function statRefundDatas4Customer($cid)
    {
        $or = new Order_Refund();
        
        $data = $or->statRefundDatas4Customers(array($cid));
        
        return array(
            'refund_amount'     => !empty($data)? $data[0]['refund_amount']: 0,         //总退款额
            'refund_num'        => !empty($data)? $data[0]['refund_order_num']: 0,      //总退款数
        );
    }
    
    /**
     * 【批量】为客户统计退单相关的数据.
     * 
     * @param type $cids
     */
    public static function statRefundDatas4Customers($cids)
    {
        $or = new Order_Refund();
        
        $datas = Tool_Array::list2Map($or->statRefundDatas4Customers($cids), 'cid');
        
        $res = array();
        foreach($cids as $cid)
        {
            $res[$cid] = array(
                'refund_amount'     => isset($datas[$cid])? $datas[$cid]['refund_amount']: 0,
                'refund_num'        => isset($datas[$cid])? $datas[$cid]['refund_order_num']: 0,
            );
        }
        
        return $res;
    }
    
    /**
     * 统计客户预存数据.
     * 
     * @param type $cid
     */
    public static function statCustomerAmount4Customer($cid, $resFields=array('*'))
    {
        $dfFields = array(
            'account_amount',       //客户总余额
            'perpay_amount',        //总预付
        );
        
        if ($resFields != array('*'))
        {
            $dfFields = array_intersect($dfFields, $resFields);
        }
        
        if (empty($dfFields)) return false;
        
        
        $fca = new Finance_Customer_Amount();
        
        // 客户账户余额
        $lastest = array();
        if (in_array('account_amount', $dfFields))
        {
            $lastest = $fca->getRecentOfUser($cid);
            $res['account_amount'] = !empty($lastest)? $lastest['amount']: 0;
        }
        
        // 总预存金额
        if (in_array('perpay_amount', $dfFields))
        {
            if (in_array('account_amount', $dfFields) && empty($lastest))
            {
                $res['perpay_amount'] = 0;
            }
            else
            {
                $paField = array('sum(price) as total_price');
                $paWhere = sprintf('status=0 and cid=%d and type=%d', $cid, Conf_Finance::CRM_AMOUNT_TYPE_PREPAY);

                $perpayAmountRes = $fca->getListByWhere($paWhere, 0, 0, $paField);
                $res['perpay_amount'] = !empty($perpayAmountRes)? $perpayAmountRes[0]['total_price']: 0;
            }
        }
        
        return $res;
    }
    
}

