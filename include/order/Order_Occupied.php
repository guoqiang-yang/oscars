<?php
/**
 * 商品库存占用相关逻辑.
 *
 * @relation
 *  1 销售订单
 *  2 调拨单
 * 
 * @rule （优先保证销售订单）
 *  1 分配占用：优先销售订单，其次调拨单
 *  2 抢占占用：优先调拨单，其次销售订单
 * 
 * Created by PhpStorm.
 * User: wangshen
 * Date: 16/12/13
 * Time: 17:32
 */


class Order_Occupied
{
    
    /**
     * 更新占用 - 拣货单详情页.
     * 
     * @uses 拣货单页，更新缺货
     * @scene 
     *      1 商品损坏导致时间库存不足，不能出库
     *      2 实际库存和系统库存不一致，不能出库
     * 
     * @param int $oid
     * @param int $sid
     * @param int $vnum
     */
    public function updateOrderVnum($oid, $sid, $vnum)
    {
        $oo = new Order_Order();
        $pwhere = sprintf('status=0 and oid=%d and sid=%d and rid=0', $oid, $sid);
        $pfield = array('oid', 'pid', 'sid', 'num', 'vnum', 'picked', 'wid', 'location');
        $products = $oo->getOrderProductsByRawWhere($pwhere, 0, 0, $pfield);
        
        if (empty($products['data']) || count($products['data'])>1)
        {
            throw new Exception('订单商品异常！');
        }
        
        $num = $products['data'][0]['num'];
        $oldVnum = $products['data'][0]['vnum'];
        $wid = $products['data'][0]['wid'];
        
        if ($vnum==0 || $vnum>$num || $vnum<$oldVnum)
        {
            throw new Exception('更新缺货数量异常，请核对！');
        }
     
        if ($vnum == $oldVnum) return;
        
        // 更新占用
        $wl = new Warehouse_Location();
        
        Warehouse_Location_Api::parseLocationAndNum($products['data']);
        $diffVnum = $vnum-$oldVnum;
        $locationInfo = $products['data'][0]['_location'];
        $_diffVnum = $diffVnum;
        foreach($locationInfo as $i => &$loc)
        {
            if ($_diffVnum <= 0) break;
            
            $locReduceOccupiedNum = min($loc['num'], $_diffVnum);
            $_diffVnum -= $locReduceOccupiedNum;
            
            // 货位占用减少
            $wl->update($sid, $loc['loc'], $wid, array(), array('occupied'=>0-$locReduceOccupiedNum));
            
            // 更新订单占用
            if ($loc['num'] > $locReduceOccupiedNum) // 货位还需拣货
            {
                $loc['num'] -= $locReduceOccupiedNum;
                $loc['vnum'] += $locReduceOccupiedNum;
            }
            else //无需此货位拣货
            {
                unset($locationInfo[$i]);
            }
        }
        
        // 更新总库存占用
        $ws = new Warehouse_Stock();
        $ws->update($wid, $sid, array(), array('occupied'=>0-$diffVnum));
        
        // 更新订单商品信息
        $_locationInfo[$sid] = array();
        foreach($locationInfo as $_loc)
        {
            $_locationInfo[$sid][] = $_loc;
        }
        $newLocationInfo = Warehouse_Location_Api::genLocationAndNum($_locationInfo);
        $upOrderProductInfo = array(
            'vnum' => $vnum,
            'picked' => 0,
            'location' => array_key_exists($sid, $newLocationInfo)? $newLocationInfo[$sid]:'',
        );
        $oo->updateOrderProductBySid($oid, 0, $sid, $upOrderProductInfo);
    }
    
    /**
     * 统计订单的占用，并更新到库存表，货位库存表.
     * 
     * @rule
     *      - 统计：订单客服确认并且未出库
     *      - 更新：库存表占用
     *             货位库存表占用
     * 
     * @param int $sid
     * @param int $wid
     */
    public function statAndUpdateOccupied($sid, $wid)
    {
        return 'sorry!!\n Waiting Stock_Shift Please!!\n';
        exit;
        
        if (empty($sid) || empty($wid)) return false;
        
        //获取订单
        $field = array('oid', 'sid', 'num', 'vnum', 'location');
        $where = sprintf('status=0 and rid=0 and wid=%d and sid=%d and oid in '.
                    ' (select oid from t_order where status=0 and wid=%d and step>=%d and step<%d)',
                $wid, $sid, $wid, Conf_Order::ORDER_STEP_SURE, Conf_Order::ORDER_STEP_PICKED);
        
        $oo = new Order_Order();
        $products = $oo->getByRawWhere('t_order_product', $where, $field);
        
        Warehouse_Location_Api::parseLocationAndNum($products);
        
        
        $stockOccupied = 0;
        $locationOccupied = array();
        foreach($products as $one)
        {
            if (empty($one['location'])) continue;
            
            foreach($one['_location'] as $l)
            {
                if ($l['num'] <= 0) continue;
                
                $loc = $l['loc'];
                if (!array_key_exists($loc, $locationOccupied))
                {
                    $locationOccupied[$loc] = $l['num'];
                }
                else
                {
                    $locationOccupied[$loc] += $l['num'];
                }
                
                $stockOccupied += $l['num'];
            }
        }   
        
        if ($stockOccupied >= 0)
        {
            $ws = new Warehouse_Stock();
            $wl = new Warehouse_Location();
            
            $ws->update($wid, $sid, array('occupied'=>$stockOccupied), array());
            
            foreach($locationOccupied as $loc => $num)
            {
                $wl->update($sid, $loc, $wid, array('occupied'=>$num));
            }
        }
        
        return true;
    }
    
    /**
     * 通过sid获取被订单占用的商品.
     * 
     * 使用sid，是因为在t_order_product表中，sid有索引，pid是联合索引
     * 
     * @uses 查商品在订单的占用
     * 
     * @param int $sid
     * @param int $wid
     */
    public function getOccupiedOrderProductsBySid($sid, $wid)
    {
        if(empty($sid) || empty($wid))
        {
            return array();
        }
        
        $subWhere = sprintf('select oid from t_order where status=0 and wid=%d and step>=%d and step<%d',
                $wid, Conf_Order::ORDER_STEP_SURE, Conf_Order::ORDER_STEP_PICKED);
        $where = sprintf('op.status=0 and op.rid=0 and op.wid=%d and op.sid=%d and op.oid in (%s) and op.oid=o.oid',
                $wid, $sid, $subWhere);
        $field = array('op.oid', 'op.pid', 'op.sid', 'op.num', 'op.vnum', 'op.picked', 'op.location',
                        'o.delivery_date', 'o.delivery_date_end');
        
        $kind = 't_order o, t_order_product op';
        $order = 'order by o.delivery_date';
        
        $oo = new Order_Order();
        $ret = $oo->getByRawWhere($kind, $where, $field, $order);
        
        return $ret;
    }


    /**
	 * 刷新指定库房的某个sku，所有待出库订单中的库存占用和临采等信息
	 * 【函数使用前提条件】：库存、货架上的库存已经更新完毕
	 *
	 * @param $sid skuid
	 * @param $wid 仓库id
     * 
	 */
	public function refreshOccupiedOfSkuWhen($sid, $wid)
	{
		//1.获取所有未出库的订单
		$orderProducts = $this->_getSortedOrderProduct($sid, $wid);
        
        // 刷占用
        $this->_refresh($sid, $wid, $orderProducts);
        
//		//2.获取原有各货位上的库存
//		$leftStockNum = 0;  //剩余库存数量
//        $waitUpdateLocsOccupied = array();  //等待更新占用的货位
//		$locations = $this->_getLocationMap($sid, $wid, $leftStockNum, $waitUpdateLocsOccupied);
//        
//		//3.更新每个订单的占用
//        $oo = new Order_Order();
//		foreach ($orderProducts as $orderProduct)
//		{
//            //3.1 计算占用 && 获取更新订单商品的数据
//            $data4UpOrder = $this->_calOccupiedAndGetUpOrderData($orderProduct, $locations, $leftStockNum, $waitUpdateLocsOccupied);
//            
//			//3.2 如果空采数有变，更新t_order_product占用
//            if($data4UpOrder['vnum']!=$orderProduct['vnum'] || $data4UpOrder['location']!=$orderProduct['location'] || $data4UpOrder['picked']!=$orderProduct['picked'])
//            {
//                $oo->updateOrderProductBySid($orderProduct['oid'], 0, $sid, $data4UpOrder);
//            }
//		}
//
//		//4.更新库存t_stock / 货位占用t_sku_2_location
//        $this->_updateStockAndLocation($sid, $wid, $waitUpdateLocsOccupied);
	}

	/**
	 * 强刷某一单某一个商品的sku占用【强制刷新】.
     * 
	 * @param $sid
	 * @param $oid
	 */
	public function forceRefreshSkuOccupiedOfOrder($sid, $oid)
	{
		$oo = new Order_Order();
		$order = $oo->get($oid);
		$wid = $order['wid'];
		//1.获取所有未出库的订单
		$orderProducts = $this->_getSortedOrderProduct($sid, $wid);
        
		//找到要强刷的订单，放在前头，这样就会优先刷新这个订单
		$findOrderProducts = array();
		foreach ($orderProducts as $k => $p) {
			if ($p['oid'] == $oid) {
				$findOrderProducts[] = $p;
				unset($orderProducts[$k]);
				break;
 			}
		}
		$_orderProducts = array_merge($findOrderProducts, $orderProducts);
        
        // 刷占用
        $this->_refresh($sid, $wid, $_orderProducts);
        
	}
    
    // 刷新占用主逻辑
    private function _refresh($sid, $wid, $orderProducts)
    {
        
        //2.获取原有各货位上的库存
		$leftStockNum = 0;  //剩余库存数量
        $waitUpdateLocsOccupied = array();  //等待更新占用的货位
		$locations = $this->_getLocationMap($sid, $wid, $leftStockNum, $waitUpdateLocsOccupied);
        
		//3.更新每个订单的占用
        $oo = new Order_Order();
		foreach ($orderProducts as $orderProduct)
		{
            //3.1 计算占用 && 获取更新订单商品的数据
            $data4UpOrder = $this->_calOccupiedAndGetUpOrderData($orderProduct, $locations, $leftStockNum, $waitUpdateLocsOccupied);
            
			//3.2 如果空采数有变，更新t_order_product占用
            if($data4UpOrder['vnum']!=$orderProduct['vnum'] || $data4UpOrder['location']!=$orderProduct['location'] || $data4UpOrder['picked']!=$orderProduct['picked'])
            {
                $oo->updateOrderProductBySid($orderProduct['oid'], 0, $sid, $data4UpOrder);
            }
		}

		//4.更新库存t_stock / 货位占用t_sku_2_location
        $this->_updateStockAndLocation($sid, $wid, $waitUpdateLocsOccupied);
        
    }
    
	/**
	 * @param $sid
	 * @param $wid
	 * @param $totalNum
     * @param $initWaitUpDateLocs 初始化等待更新占用的货位
	 * @return array
	 */
	private function _getLocationMap($sid, $wid, &$totalNum, &$initWaitUpDateLocs)
	{
		$resMap = array();
		$totalNum = 0;

        // 获取实际货位库存
		$wl = new Warehouse_Location();
		$locations = $wl->getBySid($sid, $wid, 'actual');
        
		foreach ($locations as $location)
		{
			if ($location['num'] > 0)
			{
				$totalNum += $location['num'];
				$resMap[$location['location']] = $location;
			}
            
            $initWaitUpDateLocs[$location['location']] = 0;
		}
        
		return $resMap;
	}

	public function getSortedOrderProduct($sid, $wid)
	{
		return $this->_getSortedOrderProduct($sid, $wid);
	}

	private function _getSortedOrderProduct($sid, $wid)
	{
		// 获取订单商品列表
		$oo = new Order_Order();
		$orderProducts = $oo->getOrderProductOfPicking($sid, $wid);
		if (empty($orderProducts))
		{
			return array();
		}
        
		//筛选
		$pickedOrderProducts = $notPickedOrderProducts = array();
		foreach ($orderProducts as $orderProduct)
		{
			if ($orderProduct['picked'] > 0)
			{
				$pickedOrderProducts[] = $orderProduct;
			}
			else
			{
				$notPickedOrderProducts[] = $orderProduct;
			}
		}

		//排序合并
		usort($pickedOrderProducts, array($this,'_orderProductCmpByPickedNum'));    //按拣货多少排序
		usort($notPickedOrderProducts, array($this,'_orderProductCmpByDate'));    //按配送时间排序

		return array_merge($pickedOrderProducts, $notPickedOrderProducts);
	}

	private function _orderProductCmpByDate($a,$b)
	{
		if($a['delivery_date_end'] == $b['delivery_date_end'])
		{
			return 0;
		}
		return($a['delivery_date_end']>$b['delivery_date_end']) ? 1 : -1;   //倒序，最先配送的订单优先
	}
	private function _orderProductCmpByPickedNum($a,$b)
	{
		if($a['picked'] == $b['picked'])
		{
			return 0;
		}
		return($a['picked']<$b['picked']) ? -1 : 1;   //正序, 为了减少出问题的订单数
	}
    
    /**
     * 计算占用 && 获取更新订单商品的数据.
     * 
     * @param type $orderProduct
     * @param type $locations
     * @param type $leftStockNum
     * @param type $waitUpdateLocsOccupied
     */
    private function _calOccupiedAndGetUpOrderData($orderProduct, &$locations, &$leftStockNum, &$waitUpdateLocsOccupied)
    {
        $rebuildLocs4OrderProduct = array();
        $occupiedNum4OrderProduct = 0;
        
        if ($leftStockNum > 0)  //3.1.1 如果还有库存可分配
        {
            foreach($locations as &$locData)
            {
                $loc = $locData['location'];
                $waitNum = $orderProduct['num']-$occupiedNum4OrderProduct;   //待分配数量

                if ($locData['num'] >= $waitNum) //该货位足够分配
                {
                    $occupiedNum4OrderProduct += $waitNum;
                    $rebuildLocs4OrderProduct[$orderProduct['sid']][] = array(
                        'loc' => $loc,
                        'num' => $waitNum,
                    );
                    
                    $waitUpdateLocsOccupied[$loc] += $waitNum;
                    $locData['num'] -= $waitNum;
                    break;
                }
                else    //该货位不够分配
                {
                    $occupiedNum4OrderProduct += $locData['num'];
                    $rebuildLocs4OrderProduct[$orderProduct['sid']][] = array(
                        'loc' => $loc,
                        'num' => $locData['num'],
                    );
                    
                    $waitUpdateLocsOccupied[$loc] += $locData['num'];
                    $locData['num'] = 0;
                }
            }
        }
        else   //3.1.2 如果没有剩余库存可分配
        {
            $occupiedNum4OrderProduct = 0;
        }
        
        // 剩余的总库存
        $leftStockNum -= $occupiedNum4OrderProduct;
        
        // 生成更新订单商品的数据
        $newVnum = $orderProduct['num'] - $occupiedNum4OrderProduct;
        $strLoc = '';
        if (!empty($rebuildLocs4OrderProduct))
        {
            $genLocs = Warehouse_Location_Api::genLocationAndNum($rebuildLocs4OrderProduct);
            $strLoc = $genLocs[$orderProduct['sid']];
        }
        $data4UpOrder = array(
            'vnum' => $newVnum,
            'location' => $strLoc,
            'picked' => $this->_calPicked4UpOrder($orderProduct, $strLoc, $newVnum),
            'vnum_deal_type' => $newVnum==0? 0: $orderProduct['vnum_deal_type'],
        );
        
        return $data4UpOrder;
    }
    
    private function _calPicked4UpOrder($orderProduct, $loc, $vnum)
    {
        $wid = $orderProduct['wid'];
        
        $pickedNum = 0;
        
        if (Order_Picking_Api::isAutoPicked($wid, $orderProduct['pid']))
        {
            $pickedNum = $orderProduct['num'] - $vnum;
        }
        else if ($orderProduct['picked'] <= $orderProduct['num']-$vnum)
        {
            $pickedNum = $orderProduct['picked'];
        }
        else
        {
            $pickedNum = 0; //重新拣货
        }
        
        return $pickedNum;
    }

	/**
	 * @param $sid
	 * @param $wid
	 * @param array $waitUpDataLocs 原有各货位 (location => $occupiedNum)
	 */
	private function _updateStockAndLocation($sid, $wid, $waitUpDataLocs)
	{
		$ws = new Warehouse_Stock();
        $wl = new Warehouse_Location();
        
        $totalOccupiedNum = 0;
        
        foreach($waitUpDataLocs as $_loc=>$_occupiedNum)
        {
            $wl->update($sid, $_loc, $wid, array('occupied'=>$_occupiedNum));
            $totalOccupiedNum += $_occupiedNum;
        }
        
        $ws->update($wid, $sid, array('occupied'=>$totalOccupiedNum), array());
	}
    
    
}