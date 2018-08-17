<?php

class Order_Picking_Api extends Base_Api
{
    /**
     * 商品是否自动拣货.
     */
    public static function isAutoPicked($wid, $pid)
    {
        $isAuto = FALSE;

        // 北京南库，北库砂石砖 自动拣货
        if (($wid == Conf_Warehouse::WID_3 || $wid == Conf_Warehouse::WID_4) && in_array($pid, Conf_Order::$SAND_CEMENT_BRICK_PIDS))
        {
            $isAuto = TRUE;
        }

        return $isAuto;
    }

    public static function getOrderProducts4Picking($oid)
    {
        $products = Order_Api::getOrderProducts($oid);

        Warehouse_Location_Api::parseLocationAndNum($products['products']);

        $groupProducts = array();
        foreach ($products['products'] as $p)
        {
            preg_match_all('#[A-z]#', $p['location'], $pickingArea);

            $pickingArea[0] = array_unique($pickingArea[0]);

            if (!empty($pickingArea[0]))
            {
                foreach ($pickingArea[0] as $_area)
                {
                    $p['show_buy_type'] = $p['product']['buy_type'];

                    if ($p['product']['buy_type'] == Conf_Product::BUY_TYPE_COMMON) //普采商品
                    {
                        $p['show_lack_num'] = $p['vnum'];
                        $p['show_tmp_num'] = 0;

                        $groupProducts[$_area][] = $p;
                    }
                    else
                    {
                        $p['show_lack_num'] = 0;
                        $p['show_tmp_num'] = $p['vnum'];

                        if ($p['num'] - $p['vnum'] > 0)
                        {
                            $groupProducts[$_area][] = $p;
                        }

                        if ($p['vnum'] > 0)
                        {
                            $groupProducts['空采'][] = $p;
                        }
                    }
                }
            }
            else
            {
                $p['show_buy_type'] = $p['product']['buy_type'];
                if ($p['product']['buy_type'] == Conf_Product::BUY_TYPE_COMMON)
                {
                    $p['show_lack_num'] = $p['vnum'];
                    $p['show_tmp_num'] = 0;
                }
                else
                {
                    $p['show_lack_num'] = 0;
                    $p['show_tmp_num'] = $p['vnum'];
                }

                $groupProducts['空采'][] = $p;
            }
        }

        ksort($groupProducts);

        return $groupProducts;
    }

    public static function getPickingList($search, $start = 0, $num = 20, $future = FALSE)
    {
        $pickingList = array(
            'data' => array(),
            'total' => 0
        );
        $oids = array();

        if (!empty($search['picking_area']))
        {
            $search['area'] = implode(',', $search['picking_area']);
        }

        $oo = new Order_Order();
        if (empty($search['oid']))
        {
            $wheres = self::_genWhere4PickingList($search, $future);
            
            $field = array(
                'pid', 'oid', 'num', 'vnum', 'sid',
                'picked', 'vnum_deal_type', 'location'
            );
            //$ret = $oo->getOrderProductsByRawWhere($wheres['where'], $start, $num, $field);
            $ret['data'] = $oo->getByRawWhere('t_order_product FORCE INDEX(PRIMARY)', $wheres['where'], $field, '', $start, $num);
            $oids = Tool_Array::getFields($ret['data'], 'oid');
            $pids = Tool_Array::getFields($ret['data'], 'pid');
            $sids = Tool_Array::getFields($ret['data'], 'sid');
            $products = Shop_Api::getProductInfos($pids);
            $oidsUndeal = array();
            $stocks = Warehouse_Api::getStockAllBySids($sids);
            $widStockMap = array();
            foreach ($stocks as $stock)
            {
                $wid = $stock['wid'];
                $sid = $stock['sid'];
                $widStockMap[$sid][$wid] = $stock['num'];
            }

            if ($search['picking_type'] == 1)   //已拣货
            {
                foreach ($ret['data'] as $item)
                {
                    $product = $products[$item['pid']]['product'];
                    //普采
                    $num = $item['num'];
                    $vnum = $item['vnum'];
                    $shouldPickNum = $num;
                    $isInArea = false;
                    if ($product['buy_type'] == 1)
                    {
                        if ($item['vnum_deal_type'] == 1)
                        {
                            $shouldPickNum = $num - $vnum;
                        }

                        $isInArea = FALSE !== strpos($item['location'], $search['area']);
                    }
                    else
                    {
                        $shouldPickNum = $num - $vnum;

                        $isInArea = $search['area'] == '临' || FALSE !== strpos($item['location'], $search['area']);
                    }

                    if ($shouldPickNum != $item['picked'] && $isInArea)
                    {
                        $oids = array_diff($oids, array($item['oid']));
                    }
                }
            }
            else
            {
                foreach ($ret['data'] as $item)
                {
                    $product = $products[$item['pid']]['product'];
                    //普采
                    $num = $item['num'];
                    $vnum = $item['vnum'];
                    $shouldPickNum = $num;
                    $isInArea = false;
                    if ($product['buy_type'] == 1)
                    {
                        if ($item['vnum_deal_type'] == 1)
                        {
                            $shouldPickNum = $num - $vnum;
                        }
                        $isInArea = FALSE !== strpos($item['location'], $search['area']);
                    }
                    else
                    {
                        $shouldPickNum = $num - $vnum;

                        $isInArea = $search['area'] == '临' || FALSE !== strpos($item['location'], $search['area']);
                    }

                    if (empty($search['area']) || ($shouldPickNum != $item['picked'] && $isInArea))
                    {
                        $oidsUndeal[] = $item['oid'];
                    }
                }

                $oids = array_filter(array_unique($oidsUndeal));
            }

            $pickingList['total'] = $oo->getTotalByWhere($wheres['total_where']);
        }
        else
        {
            $oids[] = $search['oid'];
            $pickingList['total'] = 1;
        }

        // 获取订单详情
        $orderInfos = $oo->getBulk($oids);

        $orderProducts = array();
        $_opInfos = $oo->getProductsByOids($oids);
        foreach ($_opInfos as $idx => $one)
        {
            if ($one['rid'] == 0)
            {
                $orderProducts[$one['oid']][] = $one;
            }
            unset($_opInfos[$idx]);
        }

        foreach ($orderInfos as &$oinfo)
        {
            Order_Helper::formatOrder($oinfo);

            $oinfo['picking_area'] = array();
            $isComplate = TRUE;
            foreach ($orderProducts[$oinfo['oid']] as $p)
            {
                preg_match_all('#[A-z]#', $p['location'], $pickingArea);
                $product = $products[$p['pid']]['product'];
                //普采
                $lincaiArr = array();
                $wid = $oinfo['wid'];
                $sid = $p['sid'];
                $stock = $widStockMap[$sid][$wid];
                $area = array();
                if (($product['buy_type'] == 2 && $stock <= 0) && empty($p['location']))
                {
                    $lincaiArr = array('临');
                }
                else
                {
                    if ($product['buy_type'] == 2 && $stock > 0 && $stock < $p['num'] && !empty($p['location']))
                    {
                        $lincaiArr = array('临');
                    }
                    $area = array_merge($oinfo['picking_area'], $pickingArea[0]);
                }
                $product = $products[$p['pid']]['product'];
                //普采
                $num = $p['num'];
                $vnum = $p['vnum'];
                $shouldPickNum = $num;
                if ($product['buy_type'] == 1)
                {
                    if ($p['vnum_deal_type'] == 1)
                    {
                        $shouldPickNum = $num - $vnum;
                    }
                }
                else
                {
                    $shouldPickNum = $num - $vnum;
                }

                $oinfo['picking_area'] = array_unique(array_merge($area, $lincaiArr));
                if ($isComplate && ($shouldPickNum != $p['picked']))
                {
                    $isComplate = FALSE;
                }
            }
            $oinfo['is_complate'] = $isComplate;
            sort($oinfo['picking_area']);
        }

        $pickingList['data'] = $orderInfos;

        return $pickingList;
    }

    /**
     * @param      $search
     * @param bool $future
     *
     * @return array
     */
    private static function _genWhere4PickingList($search, $future = FALSE)
    {
        if (is_array($search['wid']))
        {
            $search['wid'] = join(',', $search['wid']);
        }
        $where = 'status=0 and rid=0 and wid in('.$search['wid'].')';
        $deliveryTime = $search['delivery_date'];
        $deliveryTime .= !empty($search['delivery_time']) ? (' ' . $search['delivery_time'] . ':00:00') : '';
        $_deliveryTime = date('Y-m-d H:00:00', strtotime($deliveryTime));
        if ($future)
        {
            $subQuery = sprintf('select oid from t_order where status=0 and step>=%d and step<%d and wid in(%s) and delivery_date>="%s"', Conf_Order::ORDER_STEP_SURE, Conf_Order::ORDER_STEP_PICKED, $search['wid'], $_deliveryTime);

            $totalWhere = sprintf('status=0 and step>=%d and step<%d and wid in(%s) and delivery_date >= "%s"', Conf_Order::ORDER_STEP_SURE, Conf_Order::ORDER_STEP_PICKED, $search['wid'], $_deliveryTime);
            if (!empty($search['aftersale_type']))
            {
                $subQuery .= sprintf(' and aftersale_type = %d', $search['aftersale_type']);
                $totalWhere .= sprintf(' and aftersale_type = %d', $search['aftersale_type']);
            }
        }
        else
        {
            $subQuery = sprintf('select oid from t_order where status=0 and step>=%d and step<%d and wid in(%s) and delivery_date>="%s 00:00:00"', Conf_Order::ORDER_STEP_SURE, Conf_Order::ORDER_STEP_PICKED, $search['wid'], $deliveryTime);

            $totalWhere = sprintf('status=0 and step>=%d and step<%d and wid in(%s) and delivery_date>="%s 00:00:00"', Conf_Order::ORDER_STEP_SURE, Conf_Order::ORDER_STEP_PICKED, $search['wid'], $deliveryTime);
            if (!empty($search['aftersale_type']))
            {
                $subQuery .= sprintf(' and aftersale_type = %d', $search['aftersale_type']);
                $totalWhere .= sprintf(' and aftersale_type = %d', $search['aftersale_type']);
            }
        }
        $where .= ' and oid in (' . $subQuery . ')';

        return array(
            'where' => $where,
            'total_where' => $totalWhere
        );
    }

    /**
     * 刷新拣货单上的商品 出货位置和数量.
     *
     * 由于入库商品上架不及时，造成的销售入库商品需要空采问题
     *
     * @param $oid
     * @param $pid
     *
     * @throws Exception
     */
    public static function refreshPickingProduct($oid, $pid)
    {
        $oo = new Order_Order();
        $orderInfo = $oo->get($oid);

        if (empty($orderInfo) || $orderInfo['status'] != Conf_Base::STATUS_NORMAL || $orderInfo['step'] < Conf_Order::ORDER_STEP_SURE || $orderInfo['step'] >= Conf_Order::ORDER_STEP_PICKED)
        {
            throw new Exception('非法操作：该订单商品不能刷新！');
        }

        $sid = 0;
        $dealProduct = array();
        $products = $oo->getProductsOfOrder($oid);
        foreach ($products as $p)
        {
            if ($p['pid'] == $pid && $p['rid'] == 0)
            {
                $sid = $p['sid'];
                $dealProduct = array($p);
                break;
            }
        }

        if (empty($sid))
        {
            throw new Exception('非法操作：刷新的商品不在订单中！');
        }

        $wl = new Warehouse_Location();

        // 释放自己的占用
        Warehouse_Location_Api::parseLocationAndNum($dealProduct);
        if (isset($dealProduct[0]['_location']) && !empty($dealProduct[0]['_location']))
        {
            foreach ($dealProduct[0]['_location'] as $loc)
            {
                $change = array('occupied' => (0 - $loc['num']));
                $wl->update($sid, $loc['loc'], $orderInfo['wid'], array(), $change);
            }
        }

        // 重新计算可以占用的库存, 并占用
        $occupiedProduct = Warehouse_Location_Api::distributeNumFromLocation($dealProduct, $orderInfo['wid'], 1, TRUE);

        if (!empty($occupiedProduct[$sid]['raw_loc']))
        {
            foreach ($occupiedProduct[$sid]['raw_loc'] as $rawLoc)
            {
                $wlChgData = array('occupied' => $rawLoc['num']);
                $wl->update($sid, $rawLoc['loc'], $orderInfo['wid'], array(), $wlChgData);
            }

            //更新订单中 该商品的货位
            $upData = array(
                'location' => $occupiedProduct[$sid]['loc'],
                'vnum' => $occupiedProduct[$sid]['vnum'],
            );
            $oo->updateOrderProductInfo($oid, $pid, 0, $upData);
        }
        else
        {
            $upData = array(
                'location' => '',
                'vnum' => $occupiedProduct[$sid]['vnum'],
            );
            $oo->updateOrderProductInfo($oid, $pid, 0, $upData);
        }
    }

    /**
     * 标记空采商品.
     *
     *  普采商品的缺货标记.
     *
     * @param int $oid
     * @param int $pid
     * @param int $flag {lack-标缺货}
     *
     * @throws Exception
     */
    public static function markFlag4Vnum($oid, $pid, $flag)
    {
        $allFlags = array('lack');

        $oo = new Order_Order();
        $orderInfo = $oo->get($oid);

        if (empty($orderInfo) || $orderInfo['status'] != Conf_Base::STATUS_NORMAL || $orderInfo['step'] < Conf_Order::ORDER_STEP_SURE || $orderInfo['step'] >= Conf_Order::ORDER_STEP_PICKED)
        {
            throw new Exception('非法操作：该订单商品不能标记！');
        }

        $sp = new Shop_Product();
        $pinfo = $sp->get($pid);
        if ($flag == 'lack' && $pinfo['buy_type'] != Conf_Product::BUY_TYPE_COMMON)
        {
            throw new Exception('非普采商品，不能标记缺货！');
        }

        $dealProduct = array();
        $products = $oo->getProductsOfOrder($oid);
        foreach ($products as $p)
        {
            if ($p['pid'] == $pid && $p['rid'] == 0)
            {
                $dealProduct = $p;
                break;
            }
        }

        if (empty($dealProduct))
        {
            throw new Exception('非法操作：标记的商品不在订单中！');
        }
        if ($dealProduct['vnum'] <= 0)
        {
            throw new Exception('非法操作：商品没有缺货，不能标记！');
        }

        switch ($flag)
        {
            case 'lack':
                $upData = array('vnum_deal_type' => Conf_Warehouse::ORDER_VNUM_FLAG_LACK);
                break;

            default:
                $upData = array();
                break;
        }

        if (!empty($upData))
        {
            $oo->updateOrderProductInfo($oid, $pid, 0, $upData);
        }
    }

    public static function formatPickingInfoByArea($pickingProducts, $area)
    {
        $result = array();
        $list = $pickingProducts[$area];

        //订单是否有当前区的商品
        if (empty($list))
        {
            $showArea = '';
            foreach (Conf_Warehouse::$WAREHOUSE_PICKING_AREA as $item)
            {
                if ($item['id'] == $area)
                {
                    $showArea = $item['name'];
                    break;
                }
            }
            $msg = sprintf('该拣货单在%s区无商品！', $showArea);
            throw new Exception($msg);
        }

        //有商品，格式化商品信息
        foreach ($list as $product)
        {
            if (!empty($product['_location']))
            {
                foreach ($product['_location'] as $location)
                {
                    //应拣货数量 = 总数 - 缺货数 （临采）
                    //应拣货数量 = 总数 （普采缺货，未外采，保证未外采的情况下拣货单不能完成）
                    //应拣货数量 = 总数 - 缺货数 （普采缺货，已外采）
                    $num = intval($product['num']);
                    $vnum = $product['show_buy_type'] == 1 ? intval($product['show_lack_num']) : intval($product['show_tmp_num']);
                    $shouldPickNum = $num;
                    if ($product['show_buy_type'] == 1)
                    {
                        if ($product['vnum_deal_type'] == 1)
                        {
                            $shouldPickNum = $num - $vnum;
                        }
                    }
                    else
                    {
                        $shouldPickNum = $num - $vnum;
                    }

                    $item = array(
                        'pid' => $product['product']['pid'],
                        'sid' => $product['sku']['sid'],
                        'location' => $location['loc'],
                        'num' => $num,
                        'vnum' => $vnum,
                        'title' => $product['sku']['title'],
                        'cur_num' => intval($product['picked']),
                        'unit' => $product['sku']['unit'],
                        'tag' => self::_getTagByProduct($product),
                        'note' => $product['note'],
                        'should_pick_num' => $shouldPickNum,
                        'picked_time' => $product['picked_time'],
                    );

                    $result[] = $item;
                }
            }
            else
            {    //临采区
                $shouldPickNum = 0;
                $item = array(
                    'pid' => $product['product']['pid'],
                    'sid' => $product['sku']['sid'],
                    'location' => '',
                    'num' => intval($product['num']),
                    'vnum' => $product['show_buy_type'] == 1 ? intval($product['show_lack_num']) : intval($product['show_tmp_num']),
                    'title' => $product['sku']['title'],
                    'cur_num' => intval($product['picked']),
                    'unit' => $product['sku']['unit'],
                    'tag' => self::_getTagByProduct($product),
                    'note' => $product['note'],
                    'should_pick_num' => $shouldPickNum,
                    'picked_time' => $product['picked_time'],
                );

                $result[] = $item;
            }
        }

        $result = self::_sortProductList($result);

        return $result;
    }

    /**
     * 拣货单商品排序
     * 规则：
     *      未完成拣货的，按货位从小到大排序；
     *      完成拣货的，按拣货时间从早到晚排序
     *
     * @param $result
     *
     * @return array
     */
    private function _sortProductList($result)
    {
        $complateList = $uncomplateList = array();

        foreach ($result as $item)
        {
            if ($item['cur_num'] == $item['should_pick_num'])
            {
                $complateList[] = $item;
            }
            else
            {
                $uncomplateList[] = $item;
            }
        }

        usort($uncomplateList, array(
            'self',
            '_sortUnomplateList'
        ));
        usort($complateList, array(
            'self',
            '_sortComplateList'
        ));

        return array_merge($uncomplateList, $complateList);
    }

    private static function _sortComplateList($a, $b)
    {
        if ($a['picked_time'] == $b['picked_time'])
        {
            return 0;
        }

        return $a['picked_time'] > $b['picked_time'] ? 1 : -1;
    }

    private static function _sortUnomplateList($a, $b)
    {
        if ($a['location'] == $b['location'])
        {
            return 0;
        }

        return $a['location'] > $b['location'] ? 1 : -1;
    }

    private function _getTagByProduct($product)
    {
        $tag = '';

        //普采
        if ($product['show_buy_type'] == 1)
        {
            if ($product['show_lack_num'] > 0)
            {
                if ($product['vnum_deal_type'] == 1)
                {
                    $tag = '（已外采）';
                }
                else
                {
                    $tag = '（缺）';
                }
            }
        }
        else
        {    //临采
            if ($product['show_tmp_num'] > 0)
            {
                if ($product['vnum_deal_type'] == 1)
                {
                    $tag = '（已外采）';
                }
                else
                {
                    $tag = '（临）';
                }
            }
        }

        return $tag;
    }
}