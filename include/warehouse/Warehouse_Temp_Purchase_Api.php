<?php

/**
 * 临时采购.
 */

class Warehouse_Temp_Purchase_Api extends Base_Api
{
    
    /////////////////////////// new: 临采 /////////////////////////////
    
    /**
     * 获取待采购清单.
     * 
     * @param array $search
     */
    public static function getWaitTmpPurchaseList($search)
    {
        if (!isset($search['wid']) || empty($search['wid']))
        {
            return array();
        }
        
        $oo = new Order_Order();
        
        $kind = sprintf('t_order_product op inner join t_order o on o.status=0 and o.wid=%d and o.delivery_date>="%s 00:00:00" and o.delivery_date<="%s 23:59:59" and op.oid=o.oid',
                        $search['wid'], $search['delivery_date'], $search['delivery_date']);

        $where = 'op.status=0 and op.rid=0 and op.outsourcer_id=0 and op.tmp_inorder_id=0 and op.wid='. $search['wid'];
        $where .= ' and op.vnum>op.tmp_inorder_num+op.refund_vnum';
        
        $where .= ' and sid not in ('. implode(',', Conf_Order::$Virtual_Skuid_4_Tmp_Purchase). ')';
        
        if ($search['wid'] == Conf_Warehouse::WID_3)
        {
            $where .= ' and pid not in ('. implode(',', Conf_Order::$SAND_CEMENT_BRICK_PIDS).')';
        }
        
        $where .= isset($search['pid']) && !empty($search['pid'])? ' and op.pid='.$search['pid']: '';
        $where .= isset($search['oid']) && !empty($search['oid'])? ' and op.oid='.$search['oid']: '';
        $order = 'order by o.delivery_date';
        $filed = array('o.oid','o.delivery_date','o.delivery_date_end','o.step','o.wid',
                       'op.pid','op.sid', 'op.num','op.vnum','op.tmp_bought_num','op.tmp_inorder_num', 'op.vnum_deal_type', 'op.refund_vnum', 'op.cost');

        $_productList = $oo->getByRawWhere($kind, $where, $filed, $order);
          
        $sids = array();
        $productList = array();
        foreach($_productList as $product)
        {
            $product['delivery_desc'] = Order_Helper::getShowDeliveryData($product['delivery_date'], $product['delivery_date_end']);
            $product['step_desc'] = Conf_Order::$ORDER_STEPS[$product['step']];
            $product['wait_inorder_num'] = $product['vnum']-$product['refund_vnum']-$product['tmp_inorder_num'];
            $sids[] = $product['sid'];
            $pids[] = $product['pid'];
            $productList[$product['pid']][] = $product;
        }
        
        $productInfos = array();
        $skuInfos = array();
        if (!empty($pids))
        {
            $sp = new Shop_Product();
            $productInfos = $sp->getBulk($pids);
            
            $ss = new Shop_Sku();
            $skuInfos = $ss->getBulk(array_unique($sids));
        }

        foreach ($productList as $k => $item)
        {
            foreach ($item as $k2 => $subItem) {
                $product = $productInfos[$subItem['pid']];
                if ($subItem['vnum_deal_type'] != 1 && $product['buy_type'] == Conf_Product::BUY_TYPE_COMMON)
                {
                    unset($productList[$k][$k2]);
                }
            }

            if (empty($productList[$k]))
            {
                unset($productList[$k]);
            }
        }

        return array('list'=>$productList, 'sku_infos'=>$skuInfos, 'product_infos'=>$productInfos);
    }

    /**
     * 获取外包待采购清单.
     *
     * @param array $search
     */
    public static function getWaitTmpOutsourcerPurchaseList($search)
    {
        if (!isset($search['wid']) || empty($search['wid']))
        {
            return array('list' => array(),'sku_infos' => array(), 'product_infos' => array());
        }

        $oo = new Order_Order();
        $or = new Order_Refund();
//        $productList = array();
//        $sids = array();
//        $pids = array();
//        $where = sprintf('wid=%d and step>=5 and ship_time>="%s 00:00:00" and ship_time<="%s 23:59:59"',
//            $search['wid'], $search['bdate'], $search['edate']);
//        $orderList = $oo->getListRawWhereWithoutTotal($where, array(), 0, 0,array('oid'));
//        if(!empty($orderList))
//        {
//            $oids = array();
//            $key = 0;
//            foreach ($orderList as $item)
//            {
//                $key++;
//                $oids[] = $item['oid'];
//                if($key%1000==0 || $key == count($orderList))
//                {
//                    $_where = sprintf('oid in (%s) and wid=%d and status=0 and rid=0 and tmp_inorder_id=0 and vnum>tmp_inorder_num+refund_vnum', implode(',',$oids),$search['wid']);
//                    if(empty($search['outsourcer_id']))
//                    {
//                        $_where .= ' and outsourcer_id>0';
//                    }else{
//                        $_where .= sprintf(' and outsourcer_id=%d',$search['outsourcer_id']);
//                    }
//                    $filed = array('pid','sid', 'num','vnum','tmp_inorder_num', 'refund_vnum','cost','outsourcer_id');
//                    $res = $oo->getOrderProductsByWhere($_where,0,0,'', array(), $filed);
//                    foreach($res['data'] as $product)
//                    {
//                        if(empty($productList[$product['sid']]))
//                        {
//                            $productList[$product['sid']] = array(
//                                'sid' => $product['sid'],
//                                'pid' => $product['pid'],
//                                'wait_inorder_num' => $product['vnum']-$product['refund_vnum']-$product['tmp_inorder_num'],
//                                'refund_num' => 0,
//                                'amount' => $product['cost']*($product['vnum']-$product['refund_vnum']-$product['tmp_inorder_num'])
//                            );
//                            $sids[] = $product['sid'];
//                            $pids[] = $product['pid'];
//                        }else{
//                            $productList[$product['sid']]['wait_inorder_num'] += $product['vnum']-$product['refund_vnum']-$product['tmp_inorder_num'];
//                            $productList[$product['sid']]['amount'] += $product['cost']*($product['vnum']-$product['refund_vnum']-$product['tmp_inorder_num']);
//                        }
//                    }
//                    $oids = array();
//                }
//
//            }
//        }
        
//        $hOrder = new Data_Dao('t_order');
//        $oWhere = sprintf('wid=%d and step>=5 and ship_time>="%s 00:00:00" and ship_time<="%s 23:59:59"', $search['wid'], $search['bdate'], $search['edate']);
//        $orderList = $hOrder->setFields(array('oid'))->order('oid')->getListWhere($oWhere);
//        $firstOrder = current($orderList);
//        $lastOrder = end($orderList);
        
        $kind = 't_order_product FORCE INDEX(PRIMARY)';
        $where = sprintf('oid in (select oid from t_order where wid=%d and step>=5 and ship_time>="%s 00:00:00" and ship_time<="%s 23:59:59")',
            $search['wid'], $search['bdate'], $search['edate']);
//        $where = sprintf('oid between %d and %d', $firstOrder['oid'], $lastOrder['oid']);
        $where .= sprintf(' and wid=%d and status=0 and rid=0 and tmp_inorder_id=0',$search['wid']);
        if(empty($search['outsourcer_id']))
        {
            $where .= ' and outsourcer_id>0';
        }else{
            $where .= sprintf(' and outsourcer_id=%d',$search['outsourcer_id']);
        }
        
        $where .= ' and vnum>tmp_inorder_num+refund_vnum';

        $where .= isset($search['pid']) && !empty($search['pid'])? ' and pid='.$search['pid']: '';
        $where .= isset($search['oid']) && !empty($search['oid'])? ' and oid='.$search['oid']: '';
        
        $filed = array('pid','sid', 'num','vnum','tmp_inorder_num', 'refund_vnum','cost','outsourcer_id');

        $_productList = $oo->getByRawWhere($kind, $where, $filed);

        $sids = array();
        $productList = array();
        foreach($_productList as $product)
        {
            if(empty($productList[$product['sid']]))
            {
                $productList[$product['sid']] = array(
                    'sid' => $product['sid'],
                    'pid' => $product['pid'],
                    'wait_inorder_num' => $product['vnum']-$product['refund_vnum']-$product['tmp_inorder_num'],
                    'refund_num' => 0,
                    'amount' => $product['cost']*($product['vnum']-$product['refund_vnum']-$product['tmp_inorder_num'])
                );
                $sids[] = $product['sid'];
                $pids[] = $product['pid'];
            }else{
                $productList[$product['sid']]['wait_inorder_num'] += $product['vnum']-$product['refund_vnum']-$product['tmp_inorder_num'];
                $productList[$product['sid']]['amount'] += $product['cost']*($product['vnum']-$product['refund_vnum']-$product['tmp_inorder_num']);
            }
        }

        $kind = 't_order_product FORCE INDEX(rid)';
        $where = sprintf('rid>0 and rid in (select rid from t_refund where wid=%d and status=0 and stockin_time>="%s 00:00:00" and stockin_time<="%s 23:59:59")',
            $search['wid'], $search['bdate'], $search['edate']);
        $where .= sprintf(' and wid=%d and status=0 and rid>0 and tmp_inorder_id=0 ',$search['wid']);
        if(empty($search['outsourcer_id'])) {
            $where .= ' and outsourcer_id>0';
        }else{
            $where .= sprintf(' and outsourcer_id=%d', $search['outsourcer_id']);
        }

        $filed = array('pid','sid', 'picked','damaged_num','cost','outsourcer_id');
        $_refundList = $or->getByRawWhere($kind, $where, $filed);

        if(!empty($_refundList))
        {
            foreach ($_refundList as $product)
            {
                if(empty($productList[$product['sid']]))
                {
                    $productList[$product['sid']] = array(
                        'sid' => $product['sid'],
                        'pid' => $product['pid'],
                        'wait_inorder_num' => 0,
                        'refund_num' => $product['picked'] + $product['damaged_num'],
                        'amount' => -($product['picked'] + $product['damaged_num']) * $product['cost']
                    );
                    $sids[] = $product['sid'];
                    $pids[] = $product['pid'];
                }else{
                    $productList[$product['sid']]['refund_num'] += $product['picked'] + $product['damaged_num'];
                    $productList[$product['sid']]['amount'] -= ($product['picked'] + $product['damaged_num']) * $product['cost'];
                }
            }
        }

        $productInfos = array();
        $skuInfos = array();
        if (!empty($pids))
        {
            $sp = new Shop_Product();
            $productInfos = $sp->getBulk(array_unique($pids));

            $ss = new Shop_Sku();
            $skuInfos = $ss->getBulk(array_unique($sids));
        }
        return array('list'=>$productList, 'sku_infos'=>$skuInfos, 'product_infos'=>$productInfos);
    }
    
    /**
     * 检测临采商品是否合法.
     * 
     * @param array $productList {sid:xx, oid:xxx, vnum:xxx}
     */
    public static function isLegalTmpPurchase($productList, $wid)
    {
        $res = array('st'=>0, 'msg'=>'');
        
        foreach($productList as $p)
        {
            $oids[] = $p['oid'];
            $sids[] = $p['sid'];
        }
        if (empty($oids)||empty($sids))
        {
            $res['st'] = 10;
            $res['msg'] = '商品列表不能为空';
            
            return $res;
        }
        
        $oo = new Order_Order();
        $where = sprintf('status=0 and rid=0 and oid in (%s) and sid in (%s)',
                implode(',', $oids), implode(',', $sids));
        $orderProducts = $oo->getOrderProductsByRawWhere($where);
        
        $legalNum = 0;
        foreach($productList as &$pinfo)
        {
            foreach($orderProducts['data'] as $k => $opinfo)
            {
                if ($opinfo['oid']==$pinfo['oid'] && $opinfo['sid']==$pinfo['sid'] && $opinfo['wid']==$wid)
                {
                    $needBuy = $opinfo['vnum']-$opinfo['refund_vnum']-$opinfo['tmp_inorder_num'];
                    
                    if ($pinfo['vnum']==$needBuy)
                    {
                        $legalNum++;
                        unset($orderProducts['data'][$k]);
                    }
                    else
                    {
                        $res['st'] = 11;
                        $res['msg'] = '请刷新！订单商品被修改：订单-'.$pinfo['oid'].' SKUID-'.$pinfo['sid'];

                        return $res;
                    }
                }
            }
        }
        
        if ($legalNum != count($productList))
        {
            $res['st'] = 12;
            $res['msg'] = '订单商品被修改，请刷新重新选择！';
        }
        
        return $res;   
    }
    
    
    
    /////////////////////////// 【老的临采逻辑】 /////////////////////////////////
    
    /**
     * 获取待采购清单.
     * 
     * @param array $search
     * @param string $type {wait_buy-待采； alert-预警}
     */
    public static function getTmpPurchaseListByType($search, $type)
    {
        if (!isset($search['wid']) || empty($search['wid']))
        {
            return array();
        }
        if (!in_array($type, array('wait_buy', 'alert')))
        {
            return array();
        }
        
        $oo = new Order_Order();
        
        if (isset($search['oid']) && !empty($search['oid']))
        {
            $where = 'status=0 and rid=0 and oid='.$search['oid'].' and wid='. $search['wid'];
            $where .= $type=='wait_buy'? ' and vnum>tmp_bought_num': ' and vnum<tmp_bought_num';
            $where .= isset($search['pid']) && !empty($search['pid'])? ' and pid='.$search['pid']: '';
            
            $ret = $oo->getOrderProductsByRawWhere($where);
            $_productList = $ret['data'];
        }
        else
        {
            $kind = sprintf('t_order_product op inner join t_order o on o.status=0 and o.wid=%d and o.delivery_date like "%s%%" and op.oid=o.oid',
                            $search['wid'], $search['delivery_date']);
            
            $where = 'op.status=0 and op.rid=0 and op.wid='. $search['wid'];
            $where .= $type=='wait_buy'? ' and op.vnum>op.tmp_bought_num': ' and op.vnum<op.tmp_bought_num';
            $where .= isset($search['pid']) && !empty($search['pid'])? ' and op.pid='.$search['pid']: '';
            
            $_productList = $oo->getByRawWhere($kind, $where);
        }
        
        $oids= $sids = array();
        $productList = array();
        $statSidNum = array();
        foreach($_productList as $product)
        {
            // 3，4号库的沙石砖类，不进入临采的待采购列表
            if (($search['wid']==Conf_Warehouse::WID_3 || $search['wid']==Conf_Warehouse::WID_4)
                && in_array($product['pid'], Conf_Order::$SAND_CEMENT_BRICK_PIDS))
            {
                continue;
            }
            
            $oids[] = $product['oid'];
            $sids[] = $product['sid'];
            $productList[$product['oid']][] = $product;
            
            if (!array_key_exists($product['sid'], $statSidNum))
            {
                $statSidNum[$product['sid']] = 0;
            }
            $statSidNum[$product['sid']] += $product['vnum'];
        }
        
        $orderField = array('oid', 'delivery_date', 'step');
        $orderInfos = $oo->getBulk(array_unique($oids), $orderField, array('delivery_date','asc'));
        
        $ss = new Shop_Sku();
        $skuInfos = $ss->getBulk(array_unique($sids));
        
        // 对临采商品列表，根据配送时间排序
        foreach ($orderInfos as $oid => &$orderInfo)
        {
            $timstamp = strtotime($orderInfo['delivery_date']);
			$hour = date('G', $timstamp);
            $orderInfo['delivery_desc'] = date('Y年m月d日', $timstamp) . ' ' . Conf_Order::$DELIVERY_TIME[$hour];
            $orderInfo['product'] = $productList[$oid];
        }
        
        return array('list'=>$orderInfos, 'sku_infos'=>$skuInfos, 'stat_buy_num'=>$statSidNum);
    }
    
    
    /**
     * 取临采已采购清单.
     */
    public static function getTmpBoughtList($search)
    {
        if (!isset($search['wid']) || empty($search['wid']))
        {
            return array();
        }
        
        $oo = new Order_Order();
        
        // 获取某个订单临采清单
        $where = 'status=0 and rid=0 and vnum>0 and tmp_bought_num>0 and vnum>tmp_inorder_num';
        
        if (isset($search['pid']) && !empty($search['pid']))
        {
            $where .= ' and pid='. $search['pid'];
        }
        
        if (isset($search['oid']) && !empty($search['oid']))
        {
            $where = 'status=0 and rid=0 and vnum>0 and tmp_bought_num>0 and vnum>tmp_inorder_num';
            $where .= ' and oid='. $search['oid']. ' and wid='. $search['wid'];
            $where .= isset($search['pid']) && !empty($search['pid'])? ' and pid='. $search['pid']: '';
            
            $ret = $oo->getOrderProductsByRawWhere($where);
            $_productList = $ret['data'];
        }
        else
        {
            $kind = sprintf('t_order_product op inner join t_order o on o.status=0 and o.wid=%d and o.delivery_date like "%s%%" and op.oid=o.oid',
                            $search['wid'], $search['delivery_date']);
            
            $where = 'op.status=0 and op.rid=0 and op.vnum>0 and op.tmp_bought_num>0 and op.vnum>op.tmp_inorder_num';
            $where .= isset($search['pid']) && !empty($search['pid'])? ' and op.pid='.$search['pid']: '';
            
            $_productList = $oo->getByRawWhere($kind, $where);
        }
        
        
        if (empty($_productList))
        {
           return array('list'=>array(), 'orders'=>array(), 'sku_infos'=>array(), 'stat_buy_num'=>array());
        }
        
        $oids= $sids = array();
        $productList = array();
        $statNumBySid = array();
        foreach($_productList as $product)
        {
            // 3，4号库的沙石砖类，不进入临采的待采购列表
            if (($search['wid']==Conf_Warehouse::WID_3 || $search['wid']==Conf_Warehouse::WID_4)
                && in_array($product['pid'], Conf_Order::$SAND_CEMENT_BRICK_PIDS))
            {
                continue;
            }
            
            $oids[] = $product['oid'];
            $sids[] = $product['sid'];
            
            $product['need_buy'] = $product['vnum'] - $product['tmp_bought_num'];
            $product['wait_inorder'] = $product['vnum'] -  $product['tmp_inorder_num'];
            $productList[$product['sid']][] = $product;
            
            if (!array_key_exists($product['sid'], $statNumBySid))
            {
                $statNumBySid[$product['sid']] = 0;
            }
            $statNumBySid[$product['sid']] += $product['vnum'];
        }
        
        $orderField = array('oid', 'delivery_date', 'step');
        $orderInfos = $oo->getBulk(array_unique($oids), $orderField, array('delivery_date','asc'));
        
        $ss = new Shop_Sku();
        $ws = new Warehouse_Stock();
        $skuInfos = $ss->getBulk(array_unique($sids));
        $stocks = Tool_Array::list2Map($ws->getBulk($search['wid'], array_unique($sids)), 'sid');
        
        // 补充采购价字段
        foreach($skuInfos as &$info)
        {
            $sid = $info['sid'];
            $cost = 0;
            
            if (array_key_exists($sid, $stocks))
            {
                $cost = !empty($stocks[$sid]['purchase_price'])?
                        $stocks[$sid]['purchase_price']:$stocks[$sid]['cost'];
            }
            $info['cost'] = $cost;
        }
        
        // 对临采商品列表，根据配送时间排序
        foreach ($orderInfos as &$orderInfo)
        {
            $timstamp = strtotime($orderInfo['delivery_date']);
			$hour = date('G', $timstamp);
            $orderInfo['delivery_desc'] = date('Y年m月d日', $timstamp) . ' ' . Conf_Order::$DELIVERY_TIME[$hour];
        }
        
        return array('list'=>$productList, 'orders'=>$orderInfos, 'sku_infos'=>$skuInfos, 'stat_buy_num'=>$statNumBySid);
    }
    
    /**
     * 保存临采的 已采购商品的数量.
     * 
     * @param type $oid
     * @param type $pid
     * @param type $buyNum
     */
    public static function saveHadBought($oid, $pid, $buyNum)
    {
        $oo = new Order_Order();
        
        $products = $oo->getProductsOfOrder($oid);
        
        $productInfo = array();
        foreach($products as $pinfo)
        {
            if ($pinfo['pid'] == $pid)
            {
                $productInfo = $pinfo;
                break;
            }
        }
        
        if (empty($productInfo))
        {
            throw new Exception('订单商品不存在，可能是客户修改了订单商品，请刷新页面重新查看！');
        }
        if ($buyNum != ($productInfo['vnum']-$productInfo['tmp_bought_num']) )
        {
            throw new Exception('采购数量有变动，可能是客户修改了订单商品，请刷新页面重新查看！');
        }
        
        $chgData = array('tmp_bought_num'=>$buyNum);
        $oo->updateOrderProductInfo($oid, $pid, 0, array(), $chgData);
    }
    
    /**
     * 获取为做临采单但是已经采购的列表.
     * 
     * @param int $wid
     * @param string $exceptDeliveryDate 
     * @param int $num
     */
    public static function getTmpBoughtNoInorder($wid, $exceptDeliveryDate, $num=20)
    {
        //获取给定时间一个月内
        $date = date('Y-m-d', strtotime($exceptDeliveryDate)-30*24*3600);
        $date .= ' 00:00:00';
        $where = 'ctime>="'. $date. '" and status=0 and rid=0 and wid='.$wid .' and vnum>tmp_inorder_num and tmp_bought_num>0 group by oid';
        
        $field = array('oid', 'count(1)');
        $oo = new Order_Order();
        $ret = $oo->getOrderProductsByRawWhere($where, 0, 0, $field);
        
        $oids = Tool_Array::getFields($ret['data'], 'oid');
        
        $leftList = array();
        
        if (empty($oids))
        {
            return $leftList;
        }
        
        $orderField = array('oid', 'delivery_date', 'step');
        $orderInfos = $oo->getBulk(array_unique($oids), $orderField, array('delivery_date','asc'));
        
        foreach($ret['data'] as $one)
        {
            $deliveryData = explode(' ', $orderInfos[$one['oid']]['delivery_date']);
            
            if ($deliveryData[0] == $exceptDeliveryDate)
            {
                continue;
            }
            
            if (array_key_exists($deliveryData[0], $leftList))
            {
                $leftList[$deliveryData[0]] += $one['count(1)'];
            }
            else
            {
                $leftList[$deliveryData[0]] = $one['count(1)'];
            }
            
            if (count($leftList) >= $num)
            {
                break;
            }
        }
        
        return $leftList;
    }

    /**
     * 临采单完成.
     * 
     * @param int $oid
     * @param int $staff
     */
    public static function complateTmpInorder($oid, $staff)
    {
        $wio = new Warehouse_In_Order();
        $wsi = new Warehouse_Stock_In();
        $wiop = new Warehouse_In_Order_Product();
        $wsip = new Warehouse_Stock_In_Product();
        
        $orderInfo = $wio->get($oid);
        $stockList = $wsi->getListOfOrder($oid);
        $productOfOrder = $wiop->getProductsOfOrder($oid);
        
        // 筛选临采商品
        $willStockInTotalPrice = 0;
        $productOfCommonOrder = array();
        foreach($productOfOrder as $k=>$pinfo)
        {
            if ($pinfo['source'] != Conf_In_Order::SRC_TEMPORARY && $pinfo['source'] != Conf_In_Order::SRC_OUTSOURCER)
            {
                $productOfCommonOrder[] = $pinfo;
                unset($productOfOrder[$k]);
            }
            else
            {
                $willStockInTotalPrice += $pinfo['num']*$pinfo['price'];
            }
        }
        
        // 筛选临采入库单
        foreach($stockList as $stockinInfo)
        {
            if ($stockinInfo['source'] == Conf_In_Order::SRC_TEMPORARY || $stockinInfo['source'] == Conf_In_Order::SRC_OUTSOURCER)
            {
                throw new Exception('临采单已经完成入库了，不能再操作！');
            }
        }
        
        $suid = $staff['suid'];
        
        if ($orderInfo['buyer_uid'] != $suid && $staff['role']!=Conf_Admin::ROLE_ADMIN)
        {
            throw new Exception('临采订单，只能创建者点击完成');
        }
        if (empty($productOfOrder))
        {
            throw new Exception('临采单商品为空，不能操作！');
        }
        
        // 临采单入库
        $stockinInfo = array(
            'oid' => $oid,
            'sid' => $orderInfo['sid'],
            'wid' => $orderInfo['wid'],
            'price' => $willStockInTotalPrice,
            'step' => Conf_Stock_In::STEP_STOCKIN,
            'buyer_uid' => $orderInfo['buyer_uid'],
            'stockin_suid' => $suid,
            'source' => Conf_In_Order::SRC_TEMPORARY,
            'note' => '临采单完成 并 自动入库',
        );

        if (Conf_Base::switchForManagingMode())
        {
            $stockinInfo['managing_mode'] = $orderInfo['managing_mode'];
        }

        $id = $wsi->add($stockinInfo);
        $totalSalesPrice = 0;
        foreach($productOfOrder as $p)
        {
            $product = array(
                'id' => $id,
                'sid' => $p['sid'],
                'price' => $p['price'],
                'sale_price' => $p['sale_price'],
                'num' => $p['num'],
            );
            $wsip->insert($product);
            $totalSalesPrice += $p['sale_price'] * $p['num'];
        }
        
        // 更新采购单状态
        // 临采完全入库，检查普采是否完全入库
        if (empty($productOfCommonOrder)) // 只有临采
        {
            $inorderInfo['step'] = Conf_In_Order::ORDER_STEP_RECEIVED;
        }
        else // 综合采购单
        {
            $hadStockInNum = Warehouse_Api::getStockInSumOfOrder($oid);
            
            if (!empty($hadStockInNum[Conf_In_Order::SRC_COMMON]))
            {
                $inorderInfo['step'] = Conf_In_Order::ORDER_STEP_RECEIVED;
                $hadStockInNumOfCommon = $hadStockInNum[Conf_In_Order::SRC_COMMON];
                foreach($productOfCommonOrder as $pinfo)
                {
                    if ( !array_key_exists($pinfo['sid'], $hadStockInNumOfCommon)
                        || $pinfo['num'] != $hadStockInNumOfCommon[$pinfo['sid']])
                    {
                        $inorderInfo['step'] = Conf_In_Order::ORDER_STEP_PART_RECEIVED;
                        break;
                    }
                }
            }
            else
            {
                $inorderInfo['step'] = Conf_In_Order::ORDER_STEP_PART_RECEIVED;
            }
        }
        
        $wio->update($oid, $inorderInfo);
        
        // 财务流水
        $stockinInfo['id'] = $id;
        $type = Conf_Money_Out::PURCHASE_IN_STORE;
        $note = '采购单ID：'. $stockinInfo['oid'];
        Finance_Api::addMoneyOutHistory($stockinInfo, $stockinInfo['price'], $type, $note, $suid, 0);

        if (Conf_Warehouse::isAgentWid($orderInfo['wid']))
        {
            $aa = new Agent_Agent();
            $agentInfo = $aa->getVaildAgentByWid($orderInfo['wid']);
            if (empty($agentInfo))
            {
                throw new Exception('仓库：#'.$orderInfo['wid']. ' 经销商不存在');
            }
            Agent_Api::addAgentAmountHistoryByAid($agentInfo['aid'], Conf_Agent::Agent_Type_Stock_In, 0-$totalSalesPrice, $staff['suid'], 0, $id);
        }
        
        //临采入库，写FIFO-Cost 历史表
        $sfc = new Shop_Fifo_Cost();
        foreach($productOfOrder as $item)
        {
            if (Shop_Cost_Api::FIFO_SWITCH_OFF) break;
            
            $_salesOidAndNum = explode(',', $item['sales_oids']);
            
            foreach($_salesOidAndNum as $ii)
            {
                list($_saleOid, $_vnum) = explode(':', $ii);
                
                if (empty($_saleOid) || empty($_vnum)) continue;
                
                $historyDatas = array(
                    'num' => $_vnum,
                    'cost' => $item['price'],
                    'in_id' => $id,
                    'in_type' => Conf_Warehouse::STOCK_HISTORY_IN,
                    'out_id' => $_saleOid,
                    'out_type' => Conf_Warehouse::STOCK_HISTORY_OUT,
                );
                
                $sfc->insertHistory($item['sid'], $orderInfo['wid'], $historyDatas);
            }
        }
        
    }

    /**
     * 完成临采入库.
     * 
     * @param int $inOrderOid 采购单id
     * @param array $products {oid:销售单id sid:skuid num:数量}
     * @param array $suid HC员工id
     */
    public static function complateTmpStockin($inOrderOid, $products, $suid)
    {
        if (empty($inOrderOid)||empty($products))
        {
            throw new Exception('common:params error');
        }
        $oids = $sids = array();
        foreach($products as $p)
        {
            if ($p['num'] == 0)
            {
                throw new Exception('入库商品数量不能为空：sid: '.$p['sid']);
            }
            $oids[] = $p['oid'];
            $sids[] = $p['sid'];
        }
        
        //采购单详情
        $wio = new Warehouse_In_Order();
        $inorderInfo = $wio->get($inOrderOid);
        
        //采购入库信息
        $inorderProductsInfos = Warehouse_Inorder_Api::getInorderProudctsNumButUnstockin($inOrderOid);
        
        //销售单中商品空采数量
        $productsInSalesOrder = self::getSalesOrderTmpPurchaseBySidsOids($sids, $oids);
        
        // 检测是否可以入库：入库数量<=采购单可入库数量 && 入库数量<=销售单空采可入库数量
        //                采购单数量<入库单数量：入库数量更新为采购单数量
        self::_checkCanTmpStockIn($products, $inorderProductsInfos, $productsInSalesOrder, $isComplateStockin, $stockinTotalPrice);
        
        // 临采单入库
        $wsi = new Warehouse_Stock_In();
        $wsip = new Warehouse_Stock_In_Product();
        $stockinInfo = array(
            'oid' => $inOrderOid,
            'sid' => $inorderInfo['sid'],
            'wid' => $inorderInfo['wid'],
            'price' => $stockinTotalPrice,
            'step' => Conf_Stock_In::STEP_STOCKIN,
            'buyer_uid' => $inorderInfo['buyer_uid'],
            'stockin_suid' => $suid,
            'source' => Conf_In_Order::SRC_TEMPORARY,
            'note' => '采购单[普]，临采入库',
        );
        $id = $wsi->add($stockinInfo);
        
        foreach($products as $p)
        {
            $product = array(
                'id' => $id,
                'sid' => $p['sid'],
                'price' => $p['price'],
                'num' => $p['num'],
            );
            $wsip->insert($product);
        }
        
        // 更新采购单状态
        $inorderInfo['step'] = $isComplateStockin?Conf_In_Order::ORDER_STEP_RECEIVED:Conf_In_Order::ORDER_STEP_SURE;
        $wio->update($inOrderOid, $inorderInfo);
        
        // 财务流水
        $stockinInfo['id'] = $id;
        $type = Conf_Money_Out::PURCHASE_IN_STORE;
        $note = '采购单ID：'. $stockinInfo['oid'];
        Finance_Api::addMoneyOutHistory($stockinInfo, $stockinInfo['price'], $type, $note, $suid, 0);
        
        // 回写销售单已采购单额数量
        $oo = new Order_Order();
        foreach($products as $pp)
        {
            $chgData = array('tmp_inorder_num' => $pp['num']);
            $oo->updateOrderProductInfo($pp['oid'], $pp['pid'], 0, array(), $chgData);
        }
    }
        
    /**
     * 检查待入库的入库单商品是否可以入库.
     * 
     * @param array $products
     * @param array $productsFromInorder
     * @param array $productsFromSalesOrder
     * @param bool  $isComplateStockin 是否为完全入库
     * @param int $stockinTotalPrice 入库单总价
     */
    private static function _checkCanTmpStockIn(&$products, $productsFromInorder, $productsFromSalesOrder, &$isComplateStockin, &$stockinTotalPrice)
    {
        $stockinTotalPrice = 0;
        foreach($products as &$p)
        {
            $sid = $p['sid'];
            if ( !array_key_exists($sid, $productsFromInorder['products'])
                || !array_key_exists($p['oid'].'#'.$sid, $productsFromSalesOrder) )
            {
                throw new Exception('异常：入库商品不存在');
            }
            if ($productsFromSalesOrder[$p['oid'].'#'.$sid]['un_tmp_inorder_num']<$p['num'])
            {
                throw new Exception('异常：销售单空采可入库数量不足');
            }
            
            // 采购单数量<临采入库数量：入库数量更新为采购数量
            if ($productsFromInorder['products'][$sid]['un_stockin_num']<$p['num'])
            {
                //throw new Exception('异常：采购单商品数量不足');
                $p['num'] = $productsFromInorder['products'][$sid]['un_stockin_num'];
            }
            
            $p['price'] = $productsFromInorder['products'][$sid]['price'];
            $stockinTotalPrice += $p['price']*$p['num'];
            
            if ($productsFromInorder['products'][$sid]['un_stockin_num'] == $p['num'])
            {
                unset($productsFromInorder['products'][$sid]);
            }
        }
        
        // 是否为完全入库
        $isComplateStockin = true;
        foreach($productsFromInorder['products'] as $pp)
        {
            if ($pp['un_stockin_num'] > 0)
            {
                $isComplateStockin = false;
                break;
            }
        }
    }
    
    /**
     * 根据sku-id取销售单的临采商品.
     * 
     * @param array $sids
     * @param int $wid
     * @param string $time
     *      搜索范围：配送时间：[$time-2天, $time+2天]
     * @param string 销售单id
     */
    public static function getSalesOrderTmpPurchaseBySids($sids, $wid, $time='')
    {
        if (empty($sids) || !is_array($sids) || empty($wid))
        {
            return $sids;
        }
        
        $kind = 't_order_product as p, t_order as o';
        $field = array('p.*', 'o.delivery_date');
        
        $days = 10;
        $time = !empty($time)? $time: date('Y-m-d');
        $btime = date('Y-m-d 00:00:00', strtotime($time)-$days*24*3600);
        $etime = date('Y-m-d 00:00:00', strtotime($time)+$days*24*3600);
        $where = sprintf('p.status=0 and p.rid=0 and p.oid in (%s) and sid in (%s) and p.oid=o.oid',
                    'select oid from t_order where status=0 and step>=5 and delivery_date>="'.$btime.'" and delivery_date<="'.$etime.'"',
                    implode(',', $sids));
        
        $oo = new Order_Order();
        $ret = $oo->getByRawWhere($kind, $where, $field);
        
        $products = array();
        foreach ($ret as $one)
        {
            $unDealNum = min($one['vnum'], $one['tmp_bought_num']) - $one['tmp_inorder_num'];
            $one['un_tmp_inorder_num'] = $unDealNum>0? $unDealNum: 0;
            
            $timstamp = strtotime($one['delivery_date']);
			$hour = date('G', $timstamp);
            $one['delivery_desc'] = date('Y年m月d日', $timstamp) . ' ' . Conf_Order::$DELIVERY_TIME[$hour];
            
            $products[$one['sid']][] = $one;
        }
        
        return $products;
    }
    
    /**
     * 通过订单id，skuid获取销售的临采商品的数量.
     * 
     * @param type $sids
     * @param type $oids
     */
    public static function getSalesOrderTmpPurchaseBySidsOids($sids, $oids)
    {
        $kind = 't_order_product';
        $where = sprintf('status=0 and rid=0 and sid in (%s) and oid in (%s)',
                implode(',', $sids), implode(',', $oids));
        $oo = new Order_Order();
        $ret = $oo->getByRawWhere($kind, $where);
        
        $product = array();
        foreach($ret as $p)
        {
            $k = $p['oid'].'#'.$p['sid'];
            $unDealNum = min($p['vnum'], $p['tmp_bought_num']) - $p['tmp_inorder_num'];
            $product[$k] = array(
                'un_tmp_inorder_num' => $unDealNum>0? $unDealNum: 0,    //可做临采入库的数量
            );
        }
        
        return $product;
    }
    

    ////////////////////////////  old: to delete /////////////////////////////
    /**************************** 临采 -- 待采购 *******************************/
    
    /**
     * 将销售单种的【虚采】商品加入到临采单中.
     * 
     * @param array $datas
     *      {sid:商品sku_id; num:数量; cost:采购价; wid:仓库id}
     */
    public static function addProducts2TempPurchase($datas)
    {
        if (empty($datas))
        {
            return;
        }
        
        $sids = Tool_Array::getFields($datas, 'sid');
        
        $ss = new Shop_Sku();
        $skuInfos = Tool_Array::list2Map($ss->getBulk($sids), 'sid');
        
        $wtp = new Warehouse_Temporary_Purchase();
        foreach($datas as $_data)
        {
            if (empty($_data['sid']) || empty($_data['wid'])
               || !array_key_exists($_data['sid'], $skuInfos))
            {
                continue;
            }
            
            $_sid = $_data['sid'];
            $inserData = $_data;
            $inserData['title'] = $skuInfos[$_sid]['title'];
            $inserData['cate1'] = $skuInfos[$_sid]['cate1'];
            $inserData['package'] = $skuInfos[$_sid]['package'];
            $inserData['unit'] = $skuInfos[$_sid]['unit'];
            
            $wtp->save($inserData);
        }
    }
    
    /**
     * 释放临时采购单商品.
     * 
     * 当【客服确认】后，需要回退订单状态修改商品时，释放已经加入到临采单种的商品
     * 
     * @param array $datas
     *      {sid:商品sku_id; num:数量; wid:仓库id}
     */
    public static function releaseProduct2TempPurchase($datas)
    {
        if (empty($datas))
        {
            return;
        }
        
        $wtp = new Warehouse_Temporary_Purchase();
        foreach($datas as $_data)
        {
            if (empty($_data['sid'])||empty($_data['wid']))
            {
                continue;
            }
            
            $changeField = array(
                'num' => 0-abs($_data['num']),
            );
            
            $wtp->update($_data['sid'], $_data['wid'], array(), $changeField);
        }
        
    }
    
    public static function delProduct2TempPurchase($sid, $wid)
    {
        $wtp = new Warehouse_Temporary_Purchase();
        
        $upData = array(
            'num' => 0,
            'status' => 1,
        );
        $wtp->update($sid, $wid, $upData);
    }
    
    /**
     * 获取待采购商品列表.
     * 
     * @param int $wid
     * @param int $cate1
     */
    public static function getWaitPurchaseProductList($wid=0, $cate1=0, $start=0, $num=0)
    {
        $wtp = new Warehouse_Temporary_Purchase();
        
        $productList = $wtp->get($wid, $cate1, $start, $num);
        
        return $productList;
    }
    
    
    
    /**************************** 临采 -- 已采购 *******************************/
    
    /**
     * 将待采购商品标记为已采购.
     * 
     * @param array $data
     *      {sid:商品sku_id; temp_num:采购数量; cost:采购价; wid:仓库id; buy_date:选填，默认今天}
     */
    public static function addProducts2TempHadPurchased($data)
    {
        if (empty($data))
        {
            return;
        }
        
        $wthp = new Warehouse_Temporary_Had_Purchase();
        $wtp = new Warehouse_Temporary_Purchase();
        
        // 写入 已采购表
        $wthp->save($data);

        // 将临采表中的数量减除
        $chgField = array(
            'num'=>0-abs($data['temp_num']),
        );
        $wtp->update($data['sid'], $data['wid'], array(), $chgField);
        
    }
    
    public static function getHadPurchasedProductList($buyDate, $wid=0, $start=0, $num=0, $order='')
    {
        $wthp = new Warehouse_Temporary_Had_Purchase();
        $productList = $wthp->get($buyDate, $wid, $start, $num, $order);
        
        $sids = Tool_Array::getFields($productList['data'], 'sid');
        
        if (!empty($sids))
        {
            $ss = new Shop_Sku();
            $skuInfos = Tool_Array::list2Map($ss->getBulk($sids), 'sid');
            
            foreach($productList['data'] as &$_product)
            {
                $_product['sku_info'] = $skuInfos[$_product['sid']];
            }
        }
        
        return $productList;
    }
}