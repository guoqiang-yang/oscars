<?php
/**
 * 订单相关接口
 */
class Warehouse_Api extends Base_Api
{
	/*-------- 供应商相关 --------*/
	public static function getSupplierList(array $conf, $order, $start=0, $num=20)
	{
		$cc = new Warehouse_Supplier();
        
        if (isset($conf['wid']))
        {
            $wids = array($conf['wid'], 0);
            
            if ($conf['wid'] == Conf_Warehouse::WID_8)
            {
                $wids[] = Conf_Warehouse::WID_3;
                $conf['wid'] = $wids;
            }
        }
		$list = $cc->getList($conf, $total, $order, $start, $num, $sum);
		$hasMore = $total > $start + $num;

		self::_formatForView($list);
		return array('list'=> $list, 'total'=> $total, 'has_more'=> $hasMore, 'sum' => $sum);
	}

	private static function _formatForView(&$list)
	{
		foreach($list as &$item)
		{
			$item['_type'] = Conf_Supplier::getTypeName($item['type']);
		}
	}

	public static function getSupplier($sid)
	{
		$ws = new Warehouse_Supplier();
		$info = $ws->get($sid);

		if ($info['cate1'])
		{
			$cate1List = explode(',', $info['cate1']);
			foreach ($cate1List as $cate1)
			{
				$info['_cate1'][$cate1] = 1;
			}
		}
		return $info;
	}
    
    public static function getSupplierAndSkuList($sid)
    {
        $ws = new Warehouse_Supplier();
        $wssl = new Warehouse_Supplier_Sku_List();
        
        $ret['info'] = $ws->get($sid);
        $ret['products'] = $wssl->getSupplierSkuList($sid);
        
        return $ret;
    }
    
    /**
     * 为sku列表添加sku数据.
     * 
     * @param array $skuList
     */
    public static function appendSkuInfo4SkuList(&$skuList, $fieldName='sid')
    {
        $sids = Tool_Array::getFields($skuList, $fieldName);
        
        if (empty($sids)) return;
        
        $ss = new Shop_Sku();
        $skuInfos = $ss->getBulk($sids);
        
        foreach($skuList as &$info)
        {
            $skuid = $info['sku_id'];
            if (!array_key_exists($skuid, $skuInfos)) continue;
            
            $info['sku_info'] = $skuInfos[$skuid];
        }
    }

    public static function getSupplerByIds($sids)
    {
        $ws = new Warehouse_Supplier();
        return $ws->getBulk($sids);
    }

	public static function addSupplier(array $info)
	{
		$ws = new Warehouse_Supplier();
		$sid = $ws->add($info);
		return array('sid' => $sid);
	}

	public static function updateSupplier($sid, array $info)
	{
		$ws = new Warehouse_Supplier();
		$sid = $ws->update($sid, $info);
		return array('sid' => $sid);
	}

	public static function deleteSupplier($sid)
	{
		$ws = new Warehouse_Supplier();
		$sid = $ws->delete($sid);
		return array('sid' => $sid);
	}
	
	/**
	 * 取根据分类查询 仓库的商品列表的库存.
	 */
	public static function getProductsStockByCates($conf, $wid, $order='', $start=0, $num=20)
	{
		// 查询sku
		$ss = new Shop_Sku();
		$total = 0;
		if (isset($conf['keyword']) && !empty($conf['keyword']))
		{
			$list = $ss->search($conf['keyword'], $total, $start, $num, false);
		}
		else
		{
			$list = $ss->getList($conf, $total, $start, $num, false);
		}
		$sids = Tool_Array::getFields($list, 'sid');

		if (!empty($sids))
		{
			//取库存
			$ws = new Warehouse_Stock();
			$stocks = Tool_Array::list2Map($ws->getBulk($wid, $sids), 'sid');
		
			foreach($list as &$one)
			{
				$one['_stock']['num'] = isset($stocks[$one['sid']])? $stocks[$one['sid']]['num']: 0;
				$one['_stock']['occupied'] = isset($stocks[$one['sid']])? abs($stocks[$one['sid']]['occupied']): 0;
				$one['_stock']['damaged_num'] = isset($stocks[$one['sid']])? abs($stocks[$one['sid']]['damaged_num']): 0;
				$one['available_num'] = $one['_stock']['num'] - $one['_stock']['occupied'] - $one['_stock']['damaged_num'];

                $pic_ids = explode(',', $one['pic_ids']);
				$one['_pic'] = array(
                    'small' => Data_Pic::getPicUrlFromOss($pic_ids[0], 'small'),
                    'middle' => Data_Pic::getPicUrlFromOss($pic_ids[0], 'middle'),
                    'big' => Data_Pic::getPicUrlFromOss($pic_ids[0], 'big'),
				);
				
			}
		}

		return array('total'=>$total, 'data'=>$list);
	}
    
    /**
     * 修过有入库记录的采购单的仓库.
     */
    public static function changeInorderWidWithStockin($oid, $descWid, $adminInfo)
    {
        throw new Exception('由于库房已经升级，不能再修改！');
        // 取采购单详情
        $wio = new Warehouse_In_Order();
        $inorderInfo = $wio->get($oid);
        
        $srcWid = $inorderInfo['wid'];
        if (empty($inorderInfo) || $srcWid==$descWid)
        {
            throw new Exception('数据错误！');
        }
        
        // 获取采购单的入库单
        $wsi = new Warehouse_Stock_In();
        $stockInList = $wsi->getListOfOrder($inorderInfo['oid']);
        
        if (empty($stockInList))
        {
            throw new Exception('采购单没有入库，不能修改！请直接修改！');
        }
        
        // 获取入库商品，修过库存
        $wsip = new Warehouse_Stock_In_Product();
        $ws = new Warehouse_Stock();
        $wsh = new Warehouse_Stock_History();
        foreach($stockInList as $stockIn)
        {
            $products = $wsip->getProductsOfStockIn($stockIn['id']);
            
            foreach($products as $product)
            {
                // 获取商品库存
                $stocks = Tool_Array::list2Map($ws->getAll($product['sid']), 'wid');
                
                // 目的仓库， 增加库存
                $descUp['num'] = $product['num'];
                $ws->save($descWid, $product['sid'], array(), $descUp);
                
                $descHistory = array(
                    'sid' => $product['sid'],
                    'wid' => $descWid,
                    'old_num' => isset($stocks[$descWid])?$stocks[$descWid]['num']: 0,
                    'num' => $product['num'],
                    'iid' => $product['id'],
                    'type' => Conf_Warehouse::STOCK_HISTORY_ORDER_CHG_WID,
                    'suid' => $adminInfo['suid'],
                );
                $wsh->add($descWid, $product['sid'], $descHistory);
                
                // 原仓库， 减少库存
                $srcUp['num'] = 0-$product['num'];
                $ws->save($srcWid, $product['sid'], array(), $srcUp);
                
                $srcHistory = array(
                    'sid' => $product['sid'],
                    'wid' => $srcWid,
                    'old_num' => isset($stocks[$srcWid])? $stocks[$srcWid]['num']:0,
                    'num' => 0-$product['num'],
                    'iid' => $product['id'],
                    'type' => Conf_Warehouse::STOCK_HISTORY_ORDER_CHG_WID,
                    'suid' => $adminInfo['suid'],
                );
                $wsh->add($srcWid, $product['sid'], $srcHistory);
                
            }
            
            
            // 更新入库单仓库id
            $wsi->update($stockIn['id'], array('wid'=>$descWid));
        }
        
        // 更新采购单仓库id
        $wio->update($oid, array('wid'=>$descWid));
    }

	/*-------- 采购单相关 --------*/
	public static function getOrderList(array $conf, $order='', $start=0, $num=20)
	{
		$wio = new Warehouse_In_Order();
		$list = $wio->getList($conf, $total, $order, $start, $num);
		$hasMore = $total > $start + $num;

		$ws = new Warehouse_Supplier();
		$ws->appendInfos($list);

		Warehouse_View::formatOrders($list);
		self::_setBuyerInfos($list);

        $sum = 0;
		//$sum = $wio->getSumByConf($conf, 'price');

		return array('list' => $list, 'total' => $total, 'has_more' => $hasMore, 'sum' => $sum);
	}

	public static function getOrdersOfSupplier($sid, $order, $start=0, $num=20)
	{
		$wio = new Warehouse_In_Order();
		$list = $wio->getOrdersOfSupplier($sid, $total, $order, $start, $num);
		$hasMore = $total > $start + $num;

		Warehouse_View::formatOrders($list); //格式化订单信息 日期, 状态

		return array('list'=> $list, 'total'=> $total, 'has_more'=> $hasMore);
	}
	public static function getOrderBase($oid)
	{
		//获取订单信息
		$wio = new Warehouse_In_Order();
		$info = $wio->get($oid);
		Warehouse_View::formatOrder($info); //格式化订单信息 日期, 状态
		return $info;
	}
    
    /**
     * 解析临采商品的销售单信息.
     * 
     * @param string $salesOrderInfo
     * @param int $num 兼容老临采单数据
     */
    public static function parseTmpProductOrderInfo($salesOrderInfo, $num=0)
    {
        if (empty($salesOrderInfo))
        {
            return array();
        }
        
        $salesOrders = array();
        
        $oinfos = explode(',', $salesOrderInfo);
        
        foreach($oinfos as $o)
        {
            $oinfo = explode(':', $o);
            $salesNum = isset($oinfo[1])&&!empty($oinfo[1])? $oinfo[1]: $num;
            
            $salesOrders[$oinfo[0]] = $salesNum;
        }
        
        return $salesOrders;
    }
    
    public static function getOrderProducts($oid)
    {
        $op = new Warehouse_In_Order_Product();
		$products = $op->getProductsOfOrder($oid);
        
        $sids = Tool_Array::getFields($products, 'sid');
		$ss = new Shop_Sku();
        $skuInfos = $ss->getBulk($sids);
        
        $productsNew = array();
        foreach ($products as $product)
        {
            $sid = $product['sid'];
            $source = $product['source'];
            $product['sku'] = $skuInfos[$sid];
            $productsNew[$source][$sid] = $product;
        }
        
        return $productsNew;
    }

	public static function getOrderInfo($oid)
	{
		//获取订单信息
		$wio = new Warehouse_In_Order();
		$info = $wio->get($oid);
		Warehouse_View::formatOrder($info); //格式化订单信息 日期, 状态
        
		//补充供应商信息
		$sid = $info['sid'];
		$ws = new Warehouse_Supplier();
		$supplier = $ws->get($sid);
        
		//补充商品信息
		$op = new Warehouse_In_Order_Product();
		$products = $op->getProductsOfOrder($oid);
        $sids = Tool_Array::getFields($products, 'sid');
		$ss = new Shop_Sku();
        $skuInfos = $ss->getBulk($sids);
        
        //暂且去掉库存信息（可能没有用）
        //Warehouse_Api::appendStock($info['wid'], $skuInfos);
        
        //补充已入库信息
		$sumArr = self::getStockInSumOfOrder($oid);
        
        // 重新组织采购商品的数据
        $productsNew = array();
        $inorderSources = array();
        foreach ($products as $product)
        {
            $sid = $product['sid'];
            //$source = $product['source'];
            $source = $product['source']==Conf_In_Order::SRC_OUTSOURCER? Conf_In_Order::SRC_TEMPORARY: $product['source'];
            $product['sku'] = $skuInfos[$sid];
            $product['_stock_in'] = (isset($sumArr[$source])&&isset($sumArr[$source][$sid]))?
                         $sumArr[$source][$sid]: 0;   
            
            $productsNew[$source][$sid] = $product;
            
            if (!array_key_exists($source, $inorderSources))
            {
                $inorderSources[$source]['name'] = Conf_In_Order::$In_Order_Source[$source];
                $inorderSources[$source]['total_price'] = $product['price']*$product['num'];
                $inorderSources[$source]['stockin_num'] = 0; //完全入库的商品数量
                
            }
            else
            {
                $inorderSources[$source]['total_price'] += $product['price']*$product['num'];
            }
            
            // 统计完全入库的商品数量
            if($product['num'] == $product['_stock_in'])
            {
                $inorderSources[$source]['stockin_num']++;
            }
            
        }
        
        // 普采，临采单独计算起状态
        foreach($inorderSources as $s=>&$c)
        {
            if ($c['stockin_num'] == 0)
            {
                $c['step'] = $info['step'];
                if (in_array($info['status'], array(Conf_Base::STATUS_WAIT_AUDIT, Conf_Base::STATUS_UN_AUDIT)))
                {
                    $c['step_desc'] = Conf_Base::getInOrderStatusList($info['status']);
                }
                else
                {
                    $c['step_desc'] = Conf_In_Order::$ORDER_STEPS[$info['step']];
                }
            }
            else if ($c['stockin_num'] == count($productsNew[$s]))
            {
                $c['step'] = Conf_In_Order::ORDER_STEP_RECEIVED;
                $c['step_desc'] = Conf_In_Order::$ORDER_STEPS[Conf_In_Order::ORDER_STEP_RECEIVED];
            }
            else
            {
                $c['step'] = Conf_In_Order::ORDER_STEP_PART_RECEIVED;
                $c['step_desc'] = Conf_In_Order::$ORDER_STEPS[Conf_In_Order::ORDER_STEP_PART_RECEIVED];
            }
        }
        
		return array('info'=> $info, 'products'=> $productsNew, 'supplier' => $supplier, 'sources'=>$inorderSources);
	}

	public static function addOrder($sid, array $info, array $products = array())
	{
		$sid = intval($sid);
		assert($sid > 0);
        assert(!empty($info['wid']));
        
        // 补充商品销售价
        $hadEmptySalesPriceSids = array();
        $productsSalesPrices = 0;
        if (!empty($products))
        {
            $sp = new Shop_Product();
            $sids = Tool_Array::getFields($products, 'sid');
            $cityId = Conf_Warehouse::$WAREHOUSE_CITY_MAPPING[$info['wid']];
            $productInfo = Tool_Array::list2Map($sp->getBySku($sids, $cityId, 3), 'sid');

            $checkManagingMode = false;
            foreach ($products as &$_product)
            {
                if (empty($productInfo[$_product['sid']]))
                {
                    throw new Exception('商品(sid:'.$_product['sid'].')已删除！');
                }
                $_product['sale_price'] = $productInfo[$_product['sid']]['price'];
                
                $productsSalesPrices += $_product['sale_price']*$_product['num'];
                
                if ($_product['sale_price'] == 0)
                {
                    $hadEmptySalesPriceSids[] = $_product['sid'];
                }
                if (Conf_Base::switchForManagingMode())
                {
                    if ($info['managing_mode'] != $productInfo[$_product['sid']]['managing_mode'])
                    {
                        if (empty($productInfo[$_product['sid']]['managing_mode']))
                        {
                            throw new Exception('商品(pid:' . $_product['pid'] . ')经营模式属性不存在！');
                        }
                        $checkManagingMode = true;
                    }
                }
            }

            if (Conf_Base::switchForManagingMode())
            {
                if ($checkManagingMode)
                {
                    throw new Exception('商品的属性与供应商属性不一致！');
                }
            }
        }
        
        // 经销商退货，订货要余额充足
        Agent_Api::canDistributeGoods4Agent($info['wid'], 0, Conf_Agent::Agent_Type_Stock_In, $products, 'sale_price');
//        if (Conf_Warehouse::isAgentWid($info['wid']))
//        {
//            if (!empty($hadEmptySalesPriceSids))
//            {
//                throw new Exception('商品【'.implode(',', $hadEmptySalesPriceSids).'】售价为0元，不能调拨，请联系运营人员！');
//            }
//            
//            $aa = new Agent_Agent();
//            $agentInfo = $aa->getVaildAgentByWid($info['wid']);
//            if (empty($agentInfo))
//            {
//                throw new Exception('仓库：#'.$info['wid']. ' 经销商不存在');
//            }
//            
//            if ($productsSalesPrices > $agentInfo['account_balance'])
//            {
//                throw new Exception('经销商余额不足，创建采购单失败！');
//            }
//        }

		$info['sid'] = $sid;

        //===========xujianping - begin===========
        //获取供货商信息
        $supplier = self::getSupplierList(array('sid'=>$sid),'order by sid desc',0,1);
        //计算账期之后的时间
        $paymentDays = isset($supplier['list'][0]['payment_days']) ?$supplier['list'][0]['payment_days'] : 0;
        $paymentDaysDate = date("Y-m-d H:i:s",strtotime('+'.$paymentDays.' day',strtotime($info['delivery_date'])));
        //计算账期时间
        $info['payment_days_date'] = $paymentDaysDate;
        //===========xujianping - end===========

		$wio = new Warehouse_In_Order();
		$oid = $wio->add($info);

		if (!empty($products))
		{
			$op = new Warehouse_In_Order_Product();
            $op->update($oid, $products);

			self::_updateOrderTotalPrice($oid);
		}

		return $oid;
	}

	public static function updateOrder($suid, $oid, array $info, array $products = array())
	{
		//更新订单表
        $paidSource = 0;
        if(isset($info['paid_source']))
        {
            $paidSource = $info['paid_source'];
            unset($info['paid_source']);
        }
        
		$wio = new Warehouse_In_Order();
		if (!empty($info))
		{
			$wio->update($oid, $info);
		}
        
		//更新采购清单
		if (!empty($products))
		{
			$op = new Warehouse_In_Order_Product();
			$op->update($oid, $products);
			self::_updateOrderTotalPrice($oid);
		}

		//入库单也改变采购类型
        $upStockIn = array();
        if (isset($info['payment_type']))
        {
            $upStockIn['payment_type'] = $info['payment_type'];
        }
        // 采购单支付了，则入库单也改变支付状态
//        if (isset($info['paid']) && $info['paid']==1)
//        {
//            $upStockIn['paid'] = 1;
//            $upStockIn['paid_source'] = $paidSource;
//        }
        
        if (!empty($upStockIn))
        {
            $wsi = new Warehouse_Stock_In();
			$wsi->updateByOid($oid, $upStockIn);
        }
        
		return true;
	}

	public static function addProducts($oid, $source, array $products)
	{
		assert(!empty($oid));
		assert(!empty($products));
        assert(!empty($source));
        
        // 检测商品数量，是否小于已入库数量；如果小于报错
        $_stockInProductNums = self::getStockInSumOfOrder($oid);
        $stockInProductNums = array_key_exists($source, $_stockInProductNums)?$_stockInProductNums[$source]:array();
        foreach ($products as $k=>$product)
		{
			$sid = $product['sid'];
            if (array_key_exists($sid, $stockInProductNums) && $product['num'] < $stockInProductNums[$sid])
            {
                throw new Exception('Error: 采购数量不能小于入库数量！！');
            }
            
            if ($product['num'] == 0)
            {
                unset($products[$k]);
            }
        }
        
        // 采购单详情
        $wio = new Warehouse_In_Order();
        $inorderInfo = $wio->get($oid);
     
        // 取库存表中的数据，对比采购价，不同则更新采购价
        $ws = new Warehouse_Stock();
        $sid = Tool_Array::getFields($products, 'sid');
        $stockInfos = Tool_Array::list2Map($ws->getBulk($inorderInfo['wid'], $sid), 'sid');
             
		$op = new Warehouse_In_Order_Product();
		foreach ($products as $product)
		{
			$product['oid'] = $oid;
            $product['source'] = $source;
            
			$op->insert($product);
            
            // 更新库存采购价格
            if (!array_key_exists($sid, $stockInfos) || $stockInfos[$sid]['purchase_price']!=$product['price'])
            {
                $ws->update($inorderInfo['wid'], $sid, array('purchase_price'=>$product['price']), array());
            }
		}

		self::_updateOrderTotalPrice($oid);
        
        //刷新在途数量
        Warehouse_Security_Stock_Api::updateWaitNumByInorderId($oid, $inorderInfo['wid']);

		return true;
	}

	public static function updateOrderStatus($oid, $status)
	{
		$info = array('status' => $status);

		$wio = new Warehouse_In_Order();
		$wio->update($oid, $info);

		return true;
	}

	public static function deleteOrder($oid)
	{
		$wio = new Warehouse_In_Order();
		$wio->delete($oid);

		return true;
	}

	public static function deleteProduct($oid, $sid, $source)
	{
		$op = new Warehouse_In_Order_Product();
		$op->delete($oid, $sid, $source);

		self::_updateOrderTotalPrice($oid);

		return true;
	}
    
    /**
     * 修改临采商品.
     * 
     * @param array $delProductInfo 待删除的采购单的商品信息
     * @param array $inorderProducts    采购单商品列表
     * @param int $delNum 删除数量
     * @param int $chgType 操作类型
     */
    public static function changeTmpProduct($delProductInfo, $inorderProducts, $delNum, $chgType)
    {
        $allChgType = array(1, 2);
        if (!in_array($chgType, $allChgType))
        {
            throw new Exception('非法操作：【操作类型】非法！');
        }
        
        $salesOrders = Warehouse_Api::parseTmpProductOrderInfo($delProductInfo['sales_oids'], $delProductInfo['num']);
        if ($delNum==0 || ($delNum!=$delProductInfo['num'] && !in_array($delNum, $salesOrders)))
        {
            throw new Exception('非法操作：删除商品的数量异常！');
        }
        
        $oid = $delProductInfo['oid'];
        $sid = $delProductInfo['sid'];
        $wiop = new Warehouse_In_Order_Product();
        
        $isComplateDelProduct = false; //是否完全删除采购单商品
        $salesOids4DB = array(); //数据表中应该存储的订单信息
        
        // 1 删除临采商品
        $dealSalesOids = array();
        
        // 待处理的商品全部删除 or 全部转普采
        if($delNum == $delProductInfo['num'])
        {
            $wiop->delete($oid, $sid, Conf_In_Order::SRC_TEMPORARY);

            $dealSalesOids = array_keys($salesOrders);
            
            // 是否完全删除采购单商品：不存在普采商品；临采单只有一个商品&&全部删除；删除商品非转普采
            if(!array_key_exists(Conf_In_Order::SRC_COMMON, $inorderProducts)
               && count($inorderProducts[Conf_In_Order::SRC_TEMPORARY]) == 1
               && $chgType==1)
            {
                $isComplateDelProduct = true;
            }   
        }
        else // 部分删除 or 部分转普采
        {
            foreach($salesOrders as $_salesOid=>$_num)
            {
                if($_num == $delNum)
                {
                    $dealSalesOids[] = $_salesOid;
                }
                else
                {
                    $salesOids4DB[] = $_salesOid.':'.$_num; 
                }
            }
            $upInfo = array(
                'price' => $delProductInfo['price'],
                'num' => $delProductInfo['num']-$delNum,
                'sales_oids' => implode(',', $salesOids4DB),
            );
            $wiop->updateProduct($oid, $sid, $upInfo, Conf_In_Order::SRC_TEMPORARY);
        }
        
        // 2 转普采
        if ($chgType == 2)
        {
            if (array_key_exists(Conf_In_Order::SRC_COMMON, $inorderProducts)
                && array_key_exists($sid, $inorderProducts[Conf_In_Order::SRC_COMMON]))
            {
                $upInfo = array(
                    'price' => $delProductInfo['price'],
                    'num' => $inorderProducts[Conf_In_Order::SRC_COMMON][$sid]['num']+$delNum,
                );
                $wiop->updateProduct($oid, $sid, $upInfo, Conf_In_Order::SRC_COMMON);
            }
            else
            {
                $addInfo = array(
                    'oid' => $oid,
                    'sid' => $sid,
                    'price' => $delProductInfo['price'],
                    'num' => $delNum,
                    'source' => Conf_In_Order::SRC_COMMON,
                );
                $wiop->insert($addInfo);
            }
        }
        
        // 3 修改销售单中已做采购单的数量
        $oo = new Order_Order();
        foreach($dealSalesOids as $salesOid)
        {
            //$orderWhere = sprintf('status=0 and rid=0 and oid=%d and sid=%d', $salesOid, $sid);
            //$orderProduct = $oo->getOrderProductsByRawWhere($orderWhere, 0, 0, array('tmp_inorder_num'));
            //$salesUpInfo = array('tmp_inorder_num'=>max(0, $orderProduct['data'][0]['tmp_inorder_num']-$salesOrders[$salesOid]));
            $salesUpInfo = array('tmp_inorder_num'=>0, 'tmp_inorder_id'=>0);
            $oo->updateOrderProductBySid($salesOid, 0, $sid, $salesUpInfo);
        }
        
        // 4 更新采购单状态
        if ($isComplateDelProduct) // 采购单无商品了，删除采购单
        {
            self::deleteOrder($oid);
        }
        else
        {
            self::_updateOrderTotalPrice($oid);
        }
    }

	private static function _updateOrderTotalPrice($oid)
	{
		$price = 0;
		$num = 0;
        
        // 获取已经入库商品数量, 判断采购但是否完全入库, 并更新采购单状态
        $isComplateStockIn = true;
        $stockInProduts = self::getStockInSumOfOrder($oid);
        
		$op = new Warehouse_In_Order_Product();
		$products = $op->getProductsOfOrder($oid);
        
        $sources = array();
		foreach ($products as $product)
		{
			$price += $product['price'] * $product['num'];
			$num ++;
            
            // 检测入库状态
            if (!empty($stockInProduts) && $isComplateStockIn)
            {
                $sid = $product['sid'];
                $pdSource = $product['source'];
                $inorderNum = $product['num'];

                $hadStockInNum = 0;
                if ( array_key_exists($pdSource, $stockInProduts) 
                    && array_key_exists($sid, $stockInProduts[$pdSource]))
                {
                    $hadStockInNum = $stockInProduts[$pdSource][$sid];
                }

                if ($inorderNum > $hadStockInNum)
                {
                    $isComplateStockIn = false;
                }
            }
            
            $sources[] = $product['source'];
		}
        
		$wio = new Warehouse_In_Order();
        $order = $wio->get($oid);

        $info = array(
            'price' => $price,
            'product_num' => $num,
        );

        if ($order['step'] != Conf_In_Order::ORDER_STEP_NEW)
        {
            $info['step'] = empty($stockInProduts)? Conf_In_Order::ORDER_STEP_SURE:
                ($isComplateStockIn? Conf_In_Order::ORDER_STEP_RECEIVED: Conf_In_Order::ORDER_STEP_PART_RECEIVED);
        }
        
        $sources = array_unique($sources);
        if (count($sources) > 1){
            $info['source'] = Conf_In_Order::SRC_COMPOSITIVE;
        }
        else if (!empty($sources))
        {
            $info['source'] = $sources[0];
        }
        
		$wio->update($oid, $info);
	}

    public static function checkStockinOfInorderProduct($oid, $sid, $source)
    {
        $wsip = new Warehouse_Stock_In_Product();
        
        return $wsip->checkProductStockin($oid, $sid, $source);
    }
    
	/*-------- 入库单相关 --------*/
	public static function getStockInLists(array $conf, $order='', $start=0, $num=20)
	{
		$wio = new Warehouse_Stock_In();
		$list = $wio->getList($conf, $total, $order, $start, $num);
		$hasMore = $total > $start + $num;

		$ws = new Warehouse_Supplier();
		$ws->appendInfos($list);

		Warehouse_View::formatStockIns($list);
		self::_setBuyerInfos($list);
        
		//$sum = $wio->getSumByConf($conf, 'price');
		
		return array('list' => $list, 'total' => $total, 'has_more' => $hasMore, 'sum' => 0);
	}
    
    // 获取财务使用的入库单列表.
    public static function getStockinList4Finance($conf, $start=0, $num=20, $order='')
    {
        $wsi = new Warehouse_Stock_In();
        $wio = new Warehouse_In_Order();
        $stockinRes = $wsi->getListForFinance($conf, $start, $num, $order);
        self::_setBuyerInfos($stockinRes['data']);
        
        $inorderIds = Tool_Array::getFields($stockinRes['data'], 'oid');
        $inorderRes = $wio->getBulk($inorderIds);
        
        $groupStockin = array();
        foreach($stockinRes['data'] as $one)
        {
            if (!isset($groupStockin[$one['oid']]['info']))
            {
                $inorderRes[$one['oid']]['buyer_name'] = $one['buyer_name'];
                if ($one['paid'] == Conf_Stock_In::HAD_PAID ||$one['paid'] == Conf_Stock_In::FINANCE_ACCOUNT)
                {
                    $inorderRes[$one['oid']]['paid'] = 1;
                }
                
                $groupStockin[$one['oid']]['info'] = $inorderRes[$one['oid']];
            }
            
            $groupStockin[$one['oid']]['data'][] = $one;
        }
        
        return array('total'=>$stockinRes['total'], 'data'=>$groupStockin, 'sum_price'=>$stockinRes['sum_price']);
    }

	public static function getStockInListOfSupplier($sid, $order, $start=0, $num=20)
	{
		$wio = new Warehouse_Stock_In();
		$list = $wio->getOrdersOfSupplier($sid, $total, $order, $start, $num);
		$hasMore = $total > $start + $num;

		Warehouse_View::formatStockIns($list); //格式化订单信息 日期, 状态

		return array('list'=> $list, 'total'=> $total, 'has_more'=> $hasMore);
	}
	
	public static function getStockInInfo($id)
	{
		$wio = new Warehouse_Stock_In();
		$info = $wio->get($id);
		
		return $info;
	}
    
    public static function getStockinListByOid($oid)
    {
        $wio = new Warehouse_Stock_In();
        $infos = $wio->getListOfOrder($oid);
        
        return $infos;
    }
    
    public static function getStockInInfos($ids)
    {
        $wio = new Warehouse_Stock_In();
		$infos = $wio->getBulk($ids);
		
		return $infos;
    }

	public static function getStockInDetail($id)
	{
		//获取订单信息
		$wio = new Warehouse_Stock_In();
		$info = $wio->get($id);

		//补充供应商信息
		$sid = $info['sid'];
		$ws = new Warehouse_Supplier();
		$supplier = $ws->get($sid);

		//补充商品信息
		$op = new Warehouse_Stock_In_Product();
		$products = $op->getProductsOfStockIn($id, 0, $info['status']);

        //补充sku信息
        $sids = Tool_Array::getFields($products, 'sid');
        $ss = new Shop_Sku();
        $skuInfos = $ss->getBulk($sids);

        //按照是否退货分类
        $refunds = array();
        foreach($products as $_k => $_product)
        {
            $_srid = $_product['srid'];
            if ($_srid != 0)
            {
                $refunds[$_srid][] = $_product;
                unset($products[$_k]);
            }
            else
            {
                $sid = $_product['sid'];
                $products[$_k]['sku'] = $skuInfos[$sid];
            }
        }
        $products = Tool_Array::list2Map($products, 'sid');

        
        // 补充退货商品信息
        $refundPrice = 0;
        foreach ($refunds as &$oneRefund)
        {
            foreach($oneRefund as &$_rproduct)
            {
                $sid = $_rproduct['sid'];
                $_rproduct['sku'] = $skuInfos[$sid];
                
                $refundPrice += $_rproduct['num'] * $_rproduct['price'];
            }
        }
        
        $info['refund_price'] = $refundPrice;
        
		return array('info'=> $info, 'products'=> $products, 'customer' => $supplier, 'refund_products'=>$refunds);
	}
    
    public static function getStockInProducts($id)
    {
        $wsip = new Warehouse_Stock_In_Product();
        
        $products = array('products'=>array(), 'refunds'=>array());
        
        if (is_array($id))
        {
            $_products = $wsip->getProductsByIds($id);
        }
        else
        {
            $_products= $wsip->getProductsOfStockIn($id);
        }
        
        $ss = new Shop_Sku();
        $sids = Tool_Array::getFields($_products, 'sid');
        $skuInfos = $ss->getBulk(array_unique($sids));
        
        foreach($_products as $_k => &$_product)
        {
            $_srid = $_product['srid'];
            if ($_srid != 0)
            {
                $products['refunds'][] = $_product;
                unset($_products[$_k]);
            }
            $_product['title'] = $skuInfos[$_product['sid']]['title'];
        }
        $products['products'] = $_products;
        
        return $products;
    }
    
    /**
     * 获取供应商的商品列表.
     * 
     * @param int $sid
     * @param array $conf
     * @param string $order
     * @param int $start
     * @param int $num  $num=0 取全部
     */
    public static function getSupplierProductList($sid, $conf, $field=array('*'), $order='', $start=0, $num=20)
    {
        $wsi = new Warehouse_Stock_In();
        $wsir = new Warehouse_Stock_In_Refund();
        
        $stockInList = $wsi->getSupplierOrderByConf($sid, $conf, $field, $order, $start, $num);
        
        // 获取入库单的退货单
        $refundList = array();
        $stockInIds = Tool_Array::getFields($stockInList['data'], 'id');
        if (!empty($stockInIds))
        {
            $refundList = $wsir->getByStockids($stockInIds);
        }
        
        $refundInfo = array();
        foreach($refundList as $one)
        {
            $_id = $one['stockin_id'];
            if (!array_key_exists($_id, $refundInfo))
            {
                $refundInfo[$_id] = array(
                    'num' => 0,
                    'price' => 0,
                );
            }
            $refundInfo[$_id]['num']++;
            $refundInfo[$_id]['price'] += $one['price'];
        }
        
        foreach($stockInList['data'] as &$one)
        {
            $id = $one['id'];
            if (array_key_exists($id, $refundInfo))
            {
                $one['refund_num'] = $refundInfo[$id]['num'];
                $one['refund_price'] = $refundInfo[$id]['price'];
            }
            else
            {
                $one['refund_num'] = 0;
                $one['refund_price'] = 0;
            }
            $one['will_pay'] = $one['price']-$one['refund_price']-$one['real_amount'];
        }
        
        return $stockInList;
    }
    
	public static function addStockIn($suid, $sid, array $info, array $products = array(), $inOrderType = Conf_In_Order::IN_ORDER_TYPE_ORDER)
	{
		$sid = intval($sid);
		$oid = $info['oid'];
		assert($sid > 0);
		assert($oid > 0);
		$info['sid'] = $sid;

        if (isset($info['source']) && !empty($info['source']))
        {
            $source = $info['source'];
        }
        else
        {
            throw new Exception('采购类型异常，请联系技术人员修复！！');
        }
        
        if (empty($info['wid']))
        {
            throw new Exception('采购单仓库ID异常！');
        }
        
		//检查条件
		self::_ifCanAddStockIn($oid, $source, $products, $isComplateStockIn, $inOrderType);
        
		//添加基本信息
		$wsi = new Warehouse_Stock_In();
		$wio = new Warehouse_In_Order();
        $orderInfo = $wio->getVaildOrder($oid);

        if (empty($orderInfo))
        {
            throw new Exception('采购单不存在，请刷新页面查看！！');
        }
        
		$info['buyer_uid'] = $orderInfo['buyer_uid'];
        $info['stockin_suid'] = $suid;
        $info['step'] = Conf_Stock_In::STEP_STOCKIN;
        $info['source'] = $source;
        if (Conf_Base::switchForManagingMode())
        {
            $info['managing_mode'] = $orderInfo['managing_mode'];
        }
		$id = $wsi->add($info);

		//添加商品列表
		assert (!empty($products));
		$op = new Warehouse_Stock_In_Product();
		$op->update($id, $products);
        
		//后续操作 (1)更新价格汇总 (2)将采购单设置为完成 
		$wid = $info['wid'];
        
		self::_updateStockInTotalPrice($id);
        
        //更新采购单状态
        $upInorderInfo['step'] = $isComplateStockIn? Conf_In_Order::ORDER_STEP_RECEIVED: Conf_In_Order::ORDER_STEP_PART_RECEIVED;

        if (Conf_In_Order::ORDER_STEP_RECEIVED == $upInorderInfo['step'])
        {
            $upInorderInfo['rece_time'] = date('Y-m-d H:i:s');
            $order = Warehouse_Api::getOrderInfo($oid);
            $delivery_date = $order['info']['delivery_date'];
            $delivery_hours = $order['supplier']['delivery_hours'];
            $now = date('Y-m-d H:i:s');
            ((strtotime($now) - strtotime($delivery_date)) - $delivery_hours * 3600 ) > 0 ? $upInorderInfo['is_timeout'] = 1 : $upInorderInfo['is_timeout'] = 0;
        }
        $wio->update($oid, $upInorderInfo);
        
        // 普采采购单
        // (1)更新库存 (2)新库：将商品放入到虚拟货架
        if ($source == Conf_In_Order::SRC_COMMON)
        {
            self::_checkUpdateStock($suid, $wid, $id, $products);
            self::_setSku2VirtualLocation($wid, $products, Conf_Warehouse::VFLAG_STOCK_IN);
        }
        
        // 更新在途数量
        Warehouse_Security_Stock_Api::updateWaitNumByStockinId($id, $wid);
        
		return $id;
	}

	/**
	 * 检查是否能添加进货单. 条件：进货单进货数量不能超过采购单数量.
	 *
	 * @param $oid
     * @param $source
	 * @param $products
	 * @throws Exception
	 */
	private static function _ifCanAddStockIn($oid, $source, &$products, &$isComplateStockIn, $inOrderType = Conf_In_Order::IN_ORDER_TYPE_ORDER)
	{
		//采购订单相关商品
		$wiop = new Warehouse_In_Order_Product();
		$_orderProducts = $wiop->getProductsOfOrder($oid);
        
        $orderProducts = array();
        foreach($_orderProducts as $p)
        {
            if ($p['source'] == $source)
            {
                $orderProducts[$p['sid']] = $p;
            }
        }
        
		//已入库商品
		$_otherStockInList = self::getStockInSumOfOrder($oid);
        $otherStockInList = array_key_exists($source, $_otherStockInList)? $_otherStockInList[$source]: array();
        
		//对每个要添加的商品, 做检查
		foreach ($products as &$product)
		{
			$sid = $product['sid'];
            if (empty($orderProducts[$sid]))
            {
                throw new Exception('订单中不存在该商品，请刷新页面查看！');
            }
            
            $_hadStockInNum = array_key_exists($sid, $otherStockInList)?$otherStockInList[$sid]: 0;
			if ($orderProducts[$sid]['num'] < intval($_hadStockInNum) +  $product['num'])
			{
				$sp = new Shop_Sku();
				$product = $sp->get($sid);
				throw new Exception(sprintf('"%s" 入库数量，超过了采购单对应的数量', $product['title']));
			}

			if ($inOrderType != Conf_In_Order::IN_ORDER_TYPE_GIFT)
            {
                // 补齐商品的采购价钱
                $product['price'] = $orderProducts[$sid]['price'];
            }
            else
            {
                $product['price'] = 0;
            }
		}
        
        // 检测该次是否为完全入库
        $isComplateStockIn = true;
        foreach($_orderProducts as $pd)
        {
            $sid = $pd['sid'];
            $pdSource = $pd['source'];
            $inorderNum = $pd['num'];
            
            $hadStockInNum = 0;
            if ( array_key_exists($pdSource, $_otherStockInList) 
                && array_key_exists($sid, $_otherStockInList[$pdSource]))
            {
                $hadStockInNum = $_otherStockInList[$pdSource][$sid];
            }
            
            $willStockInNum = 0;
            if ($pdSource==$source)
            {
                foreach($products as $p)
                {
                    if ($p['sid'] == $sid)
                    {
                        $willStockInNum = $p['num'];
                        break;
                    }
                }
            }
            
            if ($inorderNum > $hadStockInNum+$willStockInNum)
            {
                $isComplateStockIn = false;
                break;
            }
        }
	}

	public static function getStockInSumOfOrder($oid)
	{
		$wsi = new Warehouse_Stock_In();
		$stockInList = $wsi->getListOfOrder($oid);
		$ids = Tool_Array::getFields($stockInList, 'id');
        
		//入库单汇总
		$wsip = new Warehouse_Stock_In_Product();
		$stockInListProducts = $wsip->getProductsOfStockIn($ids);

        $sumArr = array();
		foreach ($stockInListProducts as $product)
		{
            if ($product['srid'] != 0)
            {
                continue;
            }
            
            $source = $stockInList[$product['id']]['source'];
			$sid = $product['sid'];
            
            if (!isset($sumArr[$source][$sid]))
            {
                $sumArr[$source][$sid] = $product['num'];
            }
            else
            {
                $sumArr[$source][$sid] += $product['num'];
            }
		}
        
		return $sumArr;
	}

	public static function updateStockIn($suid, $id, array $info, array $products = array())
	{
		$wio = new Warehouse_Stock_In();

		if (!empty($info))
		{
			$wio->update($id, $info);
		}

		if (!empty($products))
		{
			$op = new Warehouse_Stock_In_Product();
			$op->update($id, $products);
			self::_updateStockInTotalPrice($id);
		}

		return true;
	}
    
    // 通过采购单ID更新入库单
    public static function updateStockinByOid($oid, $info)
    {
        $wio = new Warehouse_Stock_In();
        
        $wio->updateByOid($oid, $info);
    }

    public static function addStockInProducts($id, array $products)
	{
		assert(!empty($id));
		assert(!empty($products));

		$op = new Warehouse_Stock_In_Product();
		foreach ($products as $product)
		{
			$product['id'] = $id;
			$op->insert($product);
		}

		self::_updateStockInTotalPrice($id);

		return true;
	}

	public static function updateStockInStatus($id, $status)
	{
		$info = array('status' => $status);

		$wio = new Warehouse_Stock_In();
		$wio->update($id, $info);

		return true;
	}

	public static function deleteStockIn($id)
	{
		$wio = new Warehouse_Stock_In();
		$wio->delete($id);

		return true;
	}

	public static function deleteStockInProduct($id, $pid)
	{
		$op = new Warehouse_Stock_In_Product();
		$op->delete($id, $pid);

		self::_updateStockInTotalPrice($id);

		return true;
	}

	private static function _updateStockInTotalPrice($id)
	{
		$price = 0;
		$op = new Warehouse_Stock_In_Product();
		$products = $op->getProductsOfStockIn($id);
		foreach ($products as $product)
		{
			$price += $product['price']*$product['num'];
		}

		$wio = new Warehouse_Stock_In();
		$info = array('price' => $price);
		$wio->update($id, $info);
	}
    
    private static function _setSku2VirtualLocation($wid, $products, $flag)
    {
        if (!array_key_exists($flag, Conf_Warehouse::$Virtual_Flags))
        {
            throw new Exception('虚拟货位标识不存在！');
        }
        
        if (!Conf_Warehouse::isUpgradeWarehouse($wid))
        {
            return;
        }
        
        $location = Conf_Warehouse::$Virtual_Flags[$flag]['flag'];
        
        $wl = new Warehouse_Location();
        foreach($products as $one)
        {
            if ($one['num'] > 0)
            {
                $wl->add($location, $wid, $one['sid'], $one['num']);
            }
        }
    }

	/*-------- 库存相关 --------*/
	public static function saveStock($wid, $sid, $stock)
	{
		$ws = new Warehouse_Stock();
		$ws->save($wid, $sid, $stock);
		return true;
	}

	public static function getStockDetail($wid, $sid)
	{
        if (empty($wid) || empty($sid))
        {
            throw new Exception('参数错误！');
        }
        
		$ss = new Shop_Sku();
		$sku = $ss->get($sid);

		$sp = new Shop_Product();
		//$product = $sp->get($sid);
        $cityIds = Conf_Warehouse::$WAREHOUSE_CITY_MAPPING;
        $product = $sp->getByUniqKey($sid, $cityIds[$wid]);
		
		$ws = new Warehouse_Stock();	
        $stock[$wid] = $ws->get($wid, $sid);
        $stock[$wid]['cost'] = $stock[$wid]['cost']!=0? $stock[$wid]['cost']: (!empty($product['cost'])? $product['cost']: 0);
        
		return array('stock' => $stock, 'sku' => $sku, 'product' => $product);
	}

    /**
     * 
     * @param type $sids
     * @param type $wids
     */
    public static function getStockBySidsWids($sids, $wids=array())
    {
        if (empty($sids)) return;
        
        if (is_numeric($wids)) $wids = array($wids);
        
        $where = array(
            'status' => Conf_Base::STATUS_NORMAL,
            'sid' => $sids,
        );
        if (!empty($wids))
        {
            $where['wid'] = $wids;
        }
        
        $ws = new Warehouse_Stock();
        $data = $ws->getListByWhere($where, array('*'), 0, 0);
        
        $resData = array();
        foreach($sids as $_sid)
        {
            foreach($wids as $_wid)
            {
                $resData[$_sid][$_wid] = array();
            }
            
            foreach($data as $k => $stockData)
            {
                if ($stockData['sid'] == $_sid)
                {
                    $resData[$_sid][$stockData['wid']] = $stockData;
                    unset($data[$k]);
                }
            }
        }
        
        return $resData;
    }
    
	public static function appendStock($wid, array &$skuList)
	{
		if (empty($skuList))
		{
			return;
		}
        $sids = Tool_Array::getFields($skuList, 'sid');

		$ws = new Warehouse_Stock();
		$_stocks = $ws->getBulk($wid, $sids);

		$stocks = array();
		foreach ($_stocks as $_stock)
		{
			$stocks[$_stock['sid']][$_stock['wid']] = $_stock;
		}
		
		$warehouseList = Conf_Warehouse::$WAREHOUSES;
		
		// 添加信息：商品的库存信息，成本数据
		// 成本：首先使用库存对应的成本，如果没有使用商品表中得成本的数据
		foreach ($skuList as &$sku)
		{
			$sid = $sku['sid'];
			$sku['_stock'] = !empty($stocks[$sid]) ? $stocks[$sid] : array();
			
			// 补齐库存输出
			if ($wid == 0)
			{
				foreach ($warehouseList as $_wid => $_wname)
				{
					if(!isset($sku['_stock'][$_wid]))
					{
                        $sku['_stock'][$_wid] = array();
					}
				}
			} 
			else 
			{
				if(!isset($sku['_stock'][$wid]))
				{
					$sku['_stock'][$wid] = array();
				}
			}
		}
	}
    
    /**
     * 库存预警（new).
     */
    public static function getAlertList($search, $start=0, $num=20)
    {
        $field = array('sid', 'wid', 'num', 'occupied', 'damaged_num', 'wait_num', 'ave_sale_num', 'target_num', 'deliery_cycle');
        
        $subWhere = '';
        if (isset($search['cate1']) && !empty($search['cate1']))
        {
            $subWhere = 'select sid from t_sku where status=0 and cate1='. $search['cate1'];
        }
        if (!empty($subWhere) && isset($search['cate2']) && !empty($search['cate2']))
        {
            $subWhere .= ' and cate2='. $search['cate2'];
        }
        else if (isset($search['cate2']) && !empty($search['cate2']))
        {
            $subWhere = 'select sid from t_sku where status=0 and cate2='. $search['cate2'];
        }
        if (!empty($subWhere) && isset($search['bid']) && !empty($search['bid']))
        {
            $subWhere .= ' and bid='. $search['bid'];
        }
        else if (isset($search['bid']) && !empty($search['bid']))
        {
            $subWhere = 'select sid from t_sku where status=0 and bid='. $search['bid'];
        }
        
        $ws = new Warehouse_Stock();
        $order = 'order by ave_sale_num desc';
        $where = 'status=0 and wid='. $search['wid'];
        if (!empty($subWhere))
        {
            $where .= ' and sid in ('. $subWhere. ')';
        }

        //取出所有商品，按sid，城市id获取临采/普采属性
        $sp = new Shop_Product();
        $productAll = $sp->getAll();
        $sidCityBuyTypeMap = array();
        foreach ($productAll as $product)
        {
            if ($product['status'] != Conf_Base::STATUS_NORMAL)
            {
                continue;
            }
            $sid = $product['sid'];
            $cityId = $product['city_id'];
            $buyType = $product['buy_type'];

            $sidCityBuyTypeMap[$sid][$cityId] = $buyType;
        }
        
        // 取出全部，在内存中排序
        $sortType = 'down';
        $alertList = $ws->getList($where, $field, $order, 0, 0);
		if (!empty($alertList['data']))
		{
			$sids = Tool_Array::getFields($alertList['data'], 'sid');
			$ss = new Shop_Sku();
			$skuInfos = Tool_Array::list2Map($ss->getBulk($sids), 'sid');
		
			foreach($alertList['data'] as $k => &$one)
			{
                $wid = $one['wid'];
                $sid = $one['sid'];
                $cityId = Conf_Warehouse::getCityByWarehouse($wid);
                //跳过临采的
                if (!isset($sidCityBuyTypeMap[$sid][$cityId]) || $sidCityBuyTypeMap[$sid][$cityId] == Conf_Product::BUY_TYPE_TEMP)
                {
                    unset($alertList['data'][$k]);
                    continue;
                }

                $one['short_quantity'] = Warehouse_Security_Stock_Api::calShortQuantity($one);
                
                if ($one['short_quantity'] <= 0)
                {
                    unset($alertList['data'][$k]);
                    continue;
                }
                
                $one['order_point'] = Warehouse_Security_Stock_Api::calOrderPoint($one);
                $one['order_quantity'] = Warehouse_Security_Stock_Api::calOrderQuantity($one);
                $one['turn_day'] = Warehouse_Security_Stock_Api::calTurnDayByCurrentStock($one);
                $one['delivery_day'] = $one['deliery_cycle']/24;
                
                if (!empty($search['sortby']) && $search['sortby']=='ave_sale')
                {
                    $one['_sort_val'] = $one['ave_sale_num'];
                }
                else if (!empty($search['sortby']) && $search['sortby']=='short_quantity')
                {
                    $one['_sort_val'] = $one['short_quantity'];
                }
                else if (!empty($search['sortby']) && $search['sortby'] == 'stock_num')
                {
                    $one['_sort_val'] = $one['num'];
                    $sortType = 'up';
                }
                else
                {
                    $one['_sort_val'] = $one['turn_day'];
                    $sortType = 'up';
                }
                
				$skuinfo = $skuInfos[$one['sid']];
                Shop_Helper::formatPic($skuinfo);
                
				$one['pinfo'] = array(
					'title' => $skuinfo['title'],
					'alias' => $skuinfo['alias'],
					'pic' => $skuinfo['_pic']['small'],
				);
			}
		}
        
        if ($sortType == 'up')
        {
            usort($alertList['data'], array('self', '_sortItemUp'));
        }
        else
        {
            usort($alertList['data'], array('self', '_sortItem'));
        }
        $total = count($alertList['data']);
        $alertList['data'] = array_slice($alertList['data'], $start, $num);
        $alertList['total'] = $total;
        
        return $alertList;
    }
    
    /**
     * 获取大库库存和货位库存不等的列表.
     */
    public static function getDiffListStock2Location($wid)
    {
        $ws = new Warehouse_Stock();
        $list = $ws->getDiffListStock2Location($wid);
        
        if (!empty($list))
        {
            $wl = new Warehouse_Location();
            $sids = Tool_Array::getFields($list, 'sid');
            $virtualNums = $wl->statVirtualNumBySids($sids, $wid, 0);

            foreach($list as &$info)
            {
                $info['un_shelved_num'] = array_key_exists($info['sid'], $virtualNums)? $virtualNums[$info['sid']]:0;
            }
        }
        
        return $list;
    }
    
    public static function getHistoryList($conf, $start=0, $num=20)
    {
        $wsh = new Warehouse_Stock_History();
        $ss = new Shop_Sku();
        $as = new Admin_Staff();
        
        $where = '1=1';
        if (!empty($conf['wid'])){
            if (is_array($conf['wid']))
            {
                $where .= sprintf(' and wid in (%s)', implode(',', $conf['wid']));
            }
            else
            {
                $where .= ' and wid='. $conf['wid'];
            }
        }
        if (!empty($conf['reason'])){
            $where .= ' and reason='. $conf['reason'];
        }
        if (!empty($conf['sid'])){
            $where .= ' and sid='. $conf['sid'];
        }
        if (!empty($conf['bdate']))
        {
            $where .= ' and ctime>="'. $conf['bdate'].' 00:00:00"';
        }
        if (!empty($conf['edate']))
        {
            $where .= ' and ctime<="'. $conf['edate'].' 23:59:59"';
        }
        if ($conf['type'] != -1){   //-1查找全部
            if ($conf['type'] == 999)
            {
                $where .= ' and type in (2,3)';
            }
            else
            {
                $where .= ' and type='. $conf['type'];
            }
        }
        
        $order = 'order by id desc';
        $ret = $wsh->getHistoryList($where, array('*'), $order, $start, $num);
        
        if ($ret['total'] != 0){
            $skuids = array_unique(Tool_Array::getFields($ret['data'], 'sid'));
            $sdata = Tool_Array::list2Map($ss->getBulk($skuids), 'sid');

            $uids = array_unique(Tool_Array::getFields($ret['data'], 'suid'));
            $udata = Tool_Array::list2Map($as->getUsers($uids), 'suid');

            foreach($ret['data'] as &$info){
                $_uid = $info['suid'];
                $_sid = $info['sid'];
                $info['type_name'] = Conf_Warehouse::$Stock_History_Type[$info['type']];
                $info['pinfo'] = array(
                    'title' => $sdata[$_sid]['title'],
                    'pic' => Data_Pic::getPicUrl($sdata[$_sid]['pic_ids'], 'middle'),
                    'alias' => $sdata[$_sid]['alias'],
                );
                $info['uinfo'] = array(
                    'name' => $udata[$_uid]['name'],
                    'role' => $udata[$_uid]['role'],
                );
            }
        }
        
        return $ret;
    }

    // 更新盘库数据
    public static function updateChkStock($uid, $wid, $sid, $num, $remark)
    {
        $ws = new Warehouse_Stock();
        $wsh = new Warehouse_Stock_History();
        
        //更新前库存
        $oldStocks = $ws->get($wid, $sid);
        
        if (empty($oldStocks))
        { //赋默认值
            $oldStocks['num'] = 0;
            $oldStocks['occupied'] = 0;
        }
        
        $changeNum = $num-$oldStocks['num'];
        if ($changeNum == 0)
        {
            return $oldStocks['num'];
        }
        
        $type = $changeNum>0? Conf_Warehouse::STOCK_HISTORY_CHK_GAIN: Conf_Warehouse::STOCK_HISTORY_CHK_LOSS;
        
        // 保存库存
        $change = array('num' => $changeNum);
		$ws->save($wid, $sid, array(), $change);
        
        // 保存历史记录
        $history = array(
            'old_num' => $oldStocks['num'],
            'old_occupied' => $oldStocks['occupied'],
            'num' => $changeNum,
            'suid' => $uid,
            'type' => $type,
            'note' => $remark,
        );

        $wsh->add($wid, $sid, $history);
        
        //返回最新库存
        return $num;
    }
    
	public static function getPickingOrderDetail($oid, $wid=Conf_Warehouse::WID_3)
	{
		$oo = new Order_Order();
		
		$search = array(
			'delivery_date' => date('Y-m-d', time()),
			'step' => Conf_Order::ORDER_STEP_HAS_DRIVER, //未配货
			'wid' => $wid,
		);

		$total = 0;
		$orderList = $oo->getOrderListByConf($search, $total, array(), 0, 200); //取出全部符合要求的订单
		
		$isSearched = false;
		$orderInfo = array();
		$preOid = $sufOid = 0;
		if ($total)
		{
			foreach($orderList as $key => $oinfo)
			{
				if (empty($oid))
				{
					$oid = $oinfo['oid'];
					$isSearched = true;
					break;
				} else if ($oinfo['oid'] == $oid)
				{
					$isSearched = true;
					break;
				}
			}
			
			if ($isSearched)
			{ // 待拣货订单在列表中
				$orderInfo = $orderList[$key];
				$preOid = $key>0? $orderList[$key-1]['oid']: 0;
				$sufOid = $key<count($orderList)? $orderList[$key+1]['oid']: 0;
			} else {
				$preOid = $orderList[0]['oid'];
				$sufOid = $orderList[1]['oid'];
			}
		} else { // 所有订单已经拣货完成
			$preOid = 0;
			$sufOid = 0;
		}
		$curOid = $oid;
		$productInfos = array();
		
		if (!empty($curOid))
		{
			if (empty($orderInfo))
			{
				$orderInfo = $oo->get($curOid);
			}

			$productInfos = self::_groupOrderProductsByCate1($curOid);
		}
		
		$ret = array(
			'orderInfo' => $orderInfo,
			'productInfos' => $productInfos,
			'curOid' => $curOid,
			'preOid' => $preOid,
			'sufOid' => $sufOid,
		);
		
		return $ret;
	}

    public static function isSkuInStock($sid)
    {
        $ws = new Warehouse_Stock();
        $list = $ws->getAll($sid);

        return count($list) > 0;
    }

    public static function getSkuStocks($sid)
    {
        $ws = new Warehouse_Stock();

        return $ws->getAll($sid);
    }


	private static function _checkUpdateStock($suid, $wid, $id, array $products)
	{
		$ws = new Warehouse_Stock();
		$wsh = new Warehouse_Stock_History();

		//获取原库存
		$sids = Tool_Array::getFields($products, 'sid');
		$oldStocks = $ws->getBulk($wid, $sids);
		$oldStocks = Tool_Array::list2Map($oldStocks, 'sid');

		foreach ($products as $product)
		{
			$sid = $product['sid'];

			// 保存库存
			$change = array('num' => $product['num']);
			$ws->save($wid, $sid, array(), $change);

			// 保存历史记录
			$history = array(
				'old_num' => isset($oldStocks[$sid]) ? $oldStocks[$sid]['num'] : 0,
				'old_occupied' => isset($oldStocks[$sid]) ? $oldStocks[$sid]['occupied'] :0,
				'num' => $product['num'],
				'iid' => $id,
				'suid' => $suid,
				'type' => Conf_Warehouse::STOCK_HISTORY_IN,
			);
			$wsh->add($wid, $sid, $history);
		}
	}
	
	
	/**
	 * 补充采购人信息 && 补充操作员信息.
	 */
	private static function _setBuyerInfos(&$list)
	{
		// 取操作员信息
		$buyerInfos = array();
		if (!empty($list))
		{
            $staffs = array();
            foreach($list as $one)
            {
                $staffs[] = $one['buyer_uid'];
                
                if (isset($one['stockin_suid']) && !empty($one['stockin_suid']))
                {
                    $staffs[] = $one['stockin_suid'];
                }
                if (isset($one['shelved_suid']) && !empty($one['shelved_suid']))
                {
                    $staffs[] = $one['shelved_suid'];
                }
                if (isset($one['check1_suid']) && !empty($one['check1_suid']))
                {
                    $staffs[] = $one['check1_suid'];
                }
                if (isset($one['check2_suid']) && !empty($one['check2_suid']))
                {
                    $staffs[] = $one['check2_suid'];
                }
            }
            
			$as = new Admin_Staff();
			$buyerInfos = Tool_Array::list2Map($as->getUsers(array_unique($staffs)),'suid');
		}
		
		foreach($list as &$one)
		{
			$one['buyer_name'] = '';
			if (array_key_exists($one['buyer_uid'], $buyerInfos))
			{
				$one['buyer_name'] = $buyerInfos[$one['buyer_uid']]['name'];
			} 
            if (array_key_exists($one['stockin_suid'], $buyerInfos))
			{
				$one['stockin_name'] = $buyerInfos[$one['stockin_suid']]['name'];
			} 
            if (array_key_exists($one['shelved_suid'], $buyerInfos))
			{
				$one['shelved_name'] = $buyerInfos[$one['shelved_suid']]['name'];
			} 
            if (array_key_exists($one['check1_suid'], $buyerInfos))
            {
                $one['check1_name'] = $buyerInfos[$one['check1_suid']]['name'];
            }
            if (array_key_exists($one['check2_suid'], $buyerInfos))
            {
                $one['check2_name'] = $buyerInfos[$one['check2_suid']]['name'];
            }
		}
	}
	
	/**
	 * 获取订单的分类的产品信息
	 */
	private static function _groupOrderProductsByCate1($oid)
	{
		$oo = new Order_Order();
		$ss = new Shop_Sku();
		$products = Tool_Array::list2Map($oo->getProductsOfOrder($oid), 'pid');
		$pids = array_keys($products);
	
		$pdatas = array();
		if (!empty($pids))
		{
			$fields = array('title', 'cate1', 'cate2', 'bid', 'mid', 'unit', 'package', 'detail');
			$ss->appendSkuInfos($products, $fields, 'pid');
			

			foreach($products as $info)
			{
				$_cate1 = $info['cate1'];
				$pdatas[$_cate1][] = $info;
			}
		}
		
		return $pdatas;
	}
	
	
	/*************************** 移库单 **************************************/
	
	public static function getStockShiftList($conf, $start=0, $num=20, $field=array('*'), $order='')
	{
		$wss = new Warehouse_Stock_Shift();
		$shiftList = $wss->getList($conf, $field, $order, $start, $num);
		
        $suids = array();
        $getFields = array('create_suid', 'stockout_suid', 'stockin_suid', 'shelved_suid');
        foreach($shiftList['data'] as $one)
        {
            foreach($getFields as $_f)
            {
                if ($one[$_f])
                {
                    $suids[] = $one[$_f];
                }
            }
        }
        
		if (!empty($suids))
		{
			$as = new Admin_Staff();
			$admindInfos = Tool_Array::list2Map($as->getUsers(array_unique($suids)), 'suid');
            
			foreach($shiftList['data'] as &$data)
			{
                foreach($getFields as $_f)
                {
                    $data[$_f.'_name'] = '';
                    if (array_key_exists($data[$_f], $admindInfos))
                    {
                        $data[$_f.'_name'] = $admindInfos[$data[$_f]]['name'];
                    }
                }
                
                $data['_is_upgrade_wid'] = Conf_Warehouse::isUpgradeWarehouse($data['des_wid']);
			}
		}
        
		return $shiftList;
	}
	
	public static function createStockShift($data)
	{
		$wss = new Warehouse_Stock_Shift();
		$ret = $wss->insert($data);
		
		return $ret;
	}
    
	public static function updateStockShift($ssid, $data, $adminInfo)
	{
        // 更新移库单
		$wss = new Warehouse_Stock_Shift();
        $wssp = new Warehouse_Stock_Shift_Product();
		$shiftInfo = $wss->getById($ssid);

        if (isset($data['src_wid']) && $data['src_wid']!=$shiftInfo['src_wid'])
        {
            throw new Exception('移库单创建后，不能修改【出库仓库】');
        }
        if (isset($data['des_wid']) && $data['step']>=Conf_Stock_Shift::STEP_STOCK_OUT 
            && $data['des_wid'] != $shiftInfo['des_wid'])
        {
            throw new Exception('移库单已经入库，不能修改【入库仓库】');
        }
        if (isset($data['des_wid']) && $data['des_wid']==$shiftInfo['src_wid'])
        {
            throw new Exception('出入库的ID相同，请不要开玩笑！');
        }
        
        // 更新移库单商品
        if ($data['step'] > Conf_Stock_Shift::STEP_CREATE)
        {
            $products = $wssp->get($ssid);
            $skuIds = Tool_Array::getFields($products, 'sid');
        }
        
        // 调拨单修改仓库，更新商品的售价
        if (isset($data['des_wid']) && $data['des_wid'] != $shiftInfo['des_wid'] && $shiftInfo['step']==Conf_Stock_Shift::STEP_CREATE)
        {
            $products = $wssp->get($ssid);
            $skuIds = Tool_Array::getFields($products, 'sid');
            $sp = new Shop_Product();
            $productInfo = Tool_Array::list2Map($sp->getBySku($skuIds, Conf_Warehouse::$WAREHOUSE_CITY_MAPPING[$data['des_wid']], 7), 'sid');

            foreach ($products as $_product)
            {
                $wssp->update($ssid, $_product['sid'], array('price' => $productInfo[$_product['sid']]['price']));
            }
        }

        $ws = new Warehouse_Stock();
        $wsh = new Warehouse_Stock_History();
        if (!empty($products) && $data['step'] == Conf_Stock_Shift::STEP_STOCK_OUT)	//移库 - 出库
        {
            if ($shiftInfo['step'] >= Conf_Stock_Shift::STEP_STOCK_OUT)
            {
                throw new Exception('移库单已经出库，请刷新查看！');
            }

            //$ooc = new Order_Occupied();

            // 取更新前库存记录
            $oldStocks = Tool_Array::list2Map($ws->getBulk($shiftInfo['src_wid'], $skuIds), 'sid');
            Warehouse_Location_Api::parseLocationAndNum($products, 'from_location');
            self::_checkStockShiftForStockOut($data['src_wid'], $products);
 
            // 更新调拨单状态
            $ret = $wss->update($ssid, $data);
            if (!$ret)
            {
                throw new Exception('调拨单出库异常！');
            }
            
            $totalSalesPrice = 0;
            foreach($products as $one)
            {
                $change = array('num' => 0-$one['num'], 'occupied' => 0 - abs($one['num']));
                $upRet = $ws->update($shiftInfo['src_wid'], $one['sid'], array(), $change);

                // 写出入库历史
                $stockHistoryData = self::_genStockHistoryDataForShift($data['step'], $one, $oldStocks[$one['sid']], $adminInfo);
                $upHistoryRet = $wsh->add($shiftInfo['src_wid'], $one['sid'], $stockHistoryData);

                // 新库逻辑：更新货架库存
                $wl = new Warehouse_Location();
                foreach($one['_from_location'] as $loc)
                {
                    $chgData = array('num'=>(0-abs($loc['num'])), 'occupied' => (0 - abs($loc['num'])));
                    $wl->update($one['sid'], $loc['loc'], $shiftInfo['src_wid'], array(), $chgData);
                }

                // 刷新销售单商品的占用【调拨出库已经做了占用，故不需要再做自动刷新 addby:guoqiang/2017-06-12】
                //$ooc->refreshOccupiedOfSkuWhen($one['sid'], $shiftInfo['src_wid']);

                $totalSalesPrice += $one['num'] * $one['price'];
            }

            // 更新在途
            Warehouse_Security_Stock_Api::updateWaitNumByStockshiftId($ssid, $shiftInfo['des_wid']);
            
            // 经销商：
            if (Conf_Warehouse::isAgentWid($shiftInfo['des_wid']))  //调入经销商仓库：扣减经销商余额（扣款）
            {
                $aa = new Agent_Agent();
                $agentInfo = $aa->getVaildAgentByWid($shiftInfo['des_wid']);
                if (empty($agentInfo))
                {
                    throw new Exception('仓库：#'.$shiftInfo['des_wid']. ' 经销商不存在');
                }
                Agent_Api::addAgentAmountHistoryByAid($agentInfo['aid'], Conf_Agent::Agent_Type_StockShift_In, 0-$totalSalesPrice, $adminInfo['suid'], 0, $ssid);
            }
            
            // 使用COST-FIFO队列刷新订单成本
            $refreshCostDatas = Shop_Cost_Api::getCostsWithSkuAndNums($data['src_wid'], $products);
            $billInfo = array('out_id'=>$ssid, 'out_type'=>Conf_Warehouse::STOCK_HISTORY_STOCK_SHIFT_OUT);
            foreach($refreshCostDatas as $_sid => $fifoCosts)
            {
                if (empty($fifoCosts['_cost_fifo'])) continue;
                
                $wssp->update($ssid, $_sid, array('cost' => $fifoCosts['cost']));
                Shop_Cost_Api::dequeue4FifoCost($_sid, $shiftInfo['src_wid'], $billInfo, $fifoCosts['_cost_fifo']);
            }
        }
        else if (!empty($products)  && $data['step'] == Conf_Stock_Shift::STEP_STOCK_IN)	//移库 - 入库
        {
            if ($shiftInfo['step'] >= Conf_Stock_Shift::STEP_STOCK_IN)
            {
                throw new Exception('移库单已经入库，请刷新查看！');
            }

            $data['in_time'] = date('Y-m-d H:i:s');
            $ssDao = new Data_Dao('t_stock_shift');
            $res = $ssDao->updateWhere(array('ssid' => $ssid, 'step' => Conf_Stock_Shift::STEP_STOCK_OUT), $data);
            if (!$res)
            {
                throw new Exception('移库单已经入库，请刷新查看！');
            }
            // 取更新前库存记录
            $oldStocks = Tool_Array::list2Map($ws->getBulk($shiftInfo['des_wid'], $skuIds), 'sid');

            $totalSalesPrice = 0;
            foreach($products as $one)
            {
                $change = array('num' => $one['num']);
                $upRet = $ws->save($shiftInfo['des_wid'], $one['sid'], array(), $change);

                // 写出入库历史
                $_oldStocks = isset($oldStocks[$one['sid']])? $oldStocks[$one['sid']]: array();
                $stockHistoryData = self::_genStockHistoryDataForShift($data['step'], $one, $_oldStocks, $adminInfo);
                $upHistoryRet = $wsh->add($shiftInfo['des_wid'], $one['sid'], $stockHistoryData);

                $totalSalesPrice += $one['num'] * $one['price'];
            }

            // 新库逻辑：货物记录到虚拟货位上
            if (Conf_Warehouse::isUpgradeWarehouse($shiftInfo['des_wid']))
            {
                self::_setSku2VirtualLocation($shiftInfo['des_wid'], $products, Conf_Warehouse::VFLAG_SHIFT);
            }

            // 更新在途
            Warehouse_Security_Stock_Api::updateWaitNumByStockshiftId($ssid, $shiftInfo['des_wid']);

            // 经销商：
            if (Conf_Warehouse::isAgentWid($shiftInfo['src_wid'])) //经销商仓调出，并其他仓调入，增加经销商余额（支付）
            {
                $aa = new Agent_Agent();
                $agentInfo = $aa->getVaildAgentByWid($shiftInfo['src_wid']);
                if (empty($agentInfo))
                {
                    throw new Exception('仓库：#'.$shiftInfo['src_wid']. ' 经销商不存在');
                }
                Agent_Api::addAgentAmountHistoryByAid($agentInfo['aid'], Conf_Agent::Agent_Type_StockShift_Out, $totalSalesPrice, $adminInfo['suid'], 0, $ssid);
            }
            
            // 将入库商品写入到COST-FIFO队列
            $billInfo = array('in_id'=>$ssid, 'in_type'=>  Conf_Warehouse::STOCK_HISTORY_STOCK_SHIFT_IN);
            Shop_Cost_Api::enqueueSids4FifoCost($shiftInfo['des_wid'], $billInfo, $products);
            
        }
        
        return true;
    }
    
    // 调拨单出库时，检测货位库存是否足够
    private static function _checkStockShiftForStockOut($wid, $products)
    {
        $wl = new Warehouse_Location();
        $sids = Tool_Array::getFields($products, 'sid');
        $locations = $wl->getLocationsBySids($sids, $wid, 'actual');

        foreach($products as $pinfo)
        {
            if ($pinfo['vnum'] != 0)
            {
                throw new Exception('调拨商品sid:'.$pinfo['sid'].' 缺货，不能出库！请尝试刷新占用！');
            }
            foreach($pinfo['_from_location'] as $plinfo)
            {
                $loc = $plinfo['loc'];
                $num = $plinfo['num'];
                $sid = $pinfo['sid'];
                $chkFlag = false;
                
                foreach($locations as $k => $locInfo)
                {
                    if ($sid==$locInfo['sid'] && $loc==$locInfo['location']
                        && $num<=$locInfo['num'])
                    {
                        $chkFlag = true;
                        unset($locations[$k]);
                    }
                }
                
                if (!$chkFlag)
                {
                    throw new Exception('对不起，库存不足，不能出库：sid：'.$sid.' 货位：'.$loc);
                }
            }

        }
    }
	
	// 写出入库历史记录
	private static function _genStockHistoryDataForShift($shiftTyep, $shiftData, $rawData, $adminInfo)
	{
		$currNum = isset($rawData['num'])? $rawData['num']: 0;
		$currOccupied  = isset($rawData['old_occupied'])? $rawData['old_occupied']: 0;
		$addData = array(
			'old_num' => $currNum,
			'old_occupied' => $currOccupied,
			'num' => ($shiftTyep==Conf_Stock_Shift::STEP_STOCK_OUT)? 0-$shiftData['num']: $shiftData['num'],
			'occupied' => 0,
			'iid' => $shiftData['ssid'],
			'suid' => $adminInfo['suid'],
			'type' => $shiftTyep==Conf_Stock_Shift::STEP_STOCK_OUT? Conf_Warehouse::STOCK_HISTORY_STOCK_SHIFT_OUT:
									Conf_Warehouse::STOCK_HISTORY_STOCK_SHIFT_IN,
		);
		
		return $addData;
	}


	public static function addStockShiftProducts($ssid, $products)
	{
		// get stock_shift_info
		$wss = new Warehouse_Stock_Shift();
		$shiftInfo = $wss->getById($ssid);
        $wid = $shiftInfo['src_wid'];
        
        Warehouse_Stock_Helper::chkHasEnoughLocStock($products, $wid, $distributionLocs);
        
        $sids = Tool_Array::getFields($products, 'sid');
        $cityId = Conf_Warehouse::$WAREHOUSE_CITY_MAPPING[$wid];
        Shop_Product_Helper::chkHasOfflineProductsBySids($sids, $cityId);
        
        $wssp = new Warehouse_Stock_Shift_Product();
        $wssp->add($ssid, $products);
		
		return true;
	}
	
	public static function getStockShiftInfo($ssid)
	{
		$wss = new Warehouse_Stock_Shift();
		$wssp = new Warehouse_Stock_Shift_Product();
		$ss = new Shop_Sku();
		
		$wssInfo = $wss->getById($ssid);
		$wssInfo['products'] = $wssp->get($ssid, 'order by from_location');
		
		$sids = Tool_Array::getFields($wssInfo['products'], 'sid');
		
		if (!empty($sids))
		{
			$skuInfos = Tool_Array::list2Map($ss->getBulk($sids), 'sid');

			$allCate1 = Conf_Sku::$CATE1;
			$allCate2 = Conf_Sku::$CATE2;
			foreach($wssInfo['products'] as &$one)
			{
				$one['title'] = $skuInfos[$one['sid']]['title'];
				$one['unit'] = $skuInfos[$one['sid']]['unit'];

				$cate1 = $skuInfos[$one['sid']]['cate1'];
				$cate2 = $skuInfos[$one['sid']]['cate2'];
				$one['cate_desc'] = $allCate1[$cate1]['name'].' # '. $allCate2[$cate1][$cate2]['name'];
			}
            
            Warehouse_Location_Api::parseLocationAndNum($wssInfo['products'], 'from_location');
		}
		return $wssInfo;
	}
	
	/**
	 * 取消调拨单.
	 * 
	 * @param int $ssid
	 */
	public static function cannelStockShift($ssid)
	{
		$wss = new Warehouse_Stock_Shift();
        $wssp = new Warehouse_Stock_Shift_Product();
		$shiftInfo = $wss->getById($ssid);
        
        if ($shiftInfo['status'] == Conf_Base::STATUS_NORMAL)
        {
            throw new Exception('调拨已经发起不能删除');
        }
        
        $data['status'] = Conf_Base::STATUS_DELETED;
			
        $wss->update($ssid, $data);
        $wssp->delShiftAllProduct($ssid);
		
        return true;
	}
	
	/**
	 * 删除移库单商品.
	 * 
	 * @param int $ssid
	 * @param int $sid
	 */
	public static function delStockShiftProduct($ssid, $sid)
	{
		$wssp = new Warehouse_Stock_Shift_Product();
        
        $ret = $wssp->del($ssid, $sid);

		return $ret;
	}

	public static function getStockByWidAndSids($wid, $sids)
	{
		if (empty($sids)) {
			return array();
		}

		$ws = new Warehouse_Stock();
		$stocks = Tool_Array::list2Map($ws->getBulk($wid, $sids), 'sid');

		return $stocks;
	}

	public static function getStockAllBySids($sids)
    {
        if(empty($sids))
        {
            return array();
        }
        $ws = new Warehouse_Stock();
        $stocks = Tool_Array::sortByField($ws->getAllBySids($sids),'sid','asc');
        return $stocks;
    }

	public static function getOrderInfos($oids)
    {
        $wio = new Warehouse_In_Order();
        $data = $wio->getBulk($oids);

        return $data;
    }

    /*
     * 判断订单商品库存是否足够
     * @param int $oid
     * @param int $wid
     */
    public static function checkOrderProductsStockByOid($oid, $wid)
    {
        if(empty($wid) || $wid == 0)
        {
            throw new Exception('请选择仓库');
        }
        
        // 第三方的仓库
        if (Conf_Warehouse::isCoopWid($wid) || Order_Helper::isFranchiseeOrder($wid))
        {
            return;
        }
        
        $productList = Order_Api::getOrderProducts($oid);
        $sids = Tool_Array::getFields($productList['products'], 'sid');
        $orderProducts = Tool_Array::list2Map($productList['products'], 'sid');
        $stockInfos = self::getStockByWidAndSids($wid, $sids);
        
        // 重庆仓，交易负库存，负占用 【临加: guoqiang/20171205】
        if ($wid==Conf_Warehouse::WID_CQ1 || $wid==Conf_Warehouse::WID_CQ_5001 ||$wid==Conf_Warehouse::WID_CHD1)
        {
            foreach($orderProducts as  $_sid => $_pinfo)
            {
                if (in_array($_sid,Conf_Order::$Virtual_Skuid_4_Tmp_Purchase))   // 虚拟商品
                {
                    continue;
                }
                if ($stockInfos[$_sid]['num']<0 || $stockInfos[$_sid]['occupied']<0)
                {
                    throw new Exception('skuid: '. $_sid. ' 库存异常！库存or占用为负数，请联系仓储和技术人员及时处理！');
                }
            }

            return;
        }
        
        foreach($orderProducts as  $_sid => $_pinfo)
        {
            if (in_array($_sid,Conf_Order::$Virtual_Skuid_4_Tmp_Purchase))   // 虚拟商品
            {
                continue;
            }
            if ($stockInfos[$_sid]['num']<0 || $stockInfos[$_sid]['occupied']<0)
            {
                throw new Exception('skuid: '. $_sid. ' 库存异常！库存or占用为负数，请联系仓储和技术人员及时处理！');
            }
            
            $_vaildStockNum = 0;
            $_outSourcerId = 0;
            if (!empty($stockInfos[$_sid]))
            {
                $_vaildStockNum = $stockInfos[$_sid]['num'] - $stockInfos[$_sid]['occupied'] - $stockInfos[$_sid]['damaged_num'];
                $_outSourcerId = $stockInfos[$_sid]['outsourcer_id'];
            }
            
            if (Conf_Warehouse::isAgentWid($wid))   //经销商仓库
            {
                if($_pinfo['product']['buy_type'] == Conf_Product::BUY_TYPE_TEMP)
                {
                    continue;
                }
                if($_pinfo['num'] > $_vaildStockNum 
                   && $_pinfo['vnum_deal_type'] != Conf_Warehouse::ORDER_VNUM_FLAG_LACK 
                   && $_pinfo['vnum_deal_type'] != Conf_Warehouse::ORDER_VNUM_FLAG_LATER)
                {
                    throw new Exception('经销商：商品【'.$_pinfo['sku']['title'].'】skuid:'.$_sid.',库存不足');
                }
            }
            else
            {
                if($_pinfo['product']['buy_type'] == Conf_Product::BUY_TYPE_TEMP)     // 临采
                {
                    continue;
                }
                
                if($_outSourcerId == 0 && $_pinfo['num'] > $_vaildStockNum 
                   && $_pinfo['vnum_deal_type'] != Conf_Warehouse::ORDER_VNUM_FLAG_LACK 
                   && $_pinfo['vnum_deal_type'] != Conf_Warehouse::ORDER_VNUM_FLAG_LATER)
                {
                    throw new Exception('自营仓：商品【'.$_pinfo['sku']['title'].'】skuid:'.$_sid.',库存不足');
                }
            }
            
        }
    
    }

    /*
     * 判断所给商品库存是否足够
     */
    public static function checkProductsStockByProducts($products, $wid)
    {
        if(empty($wid) || $wid == 0)
        {
            throw new Exception('请选择仓库');
        }
        if(empty($products))
        {
            throw new Exception('请添加商品');
        }
        $pids = Tool_Array::getFields($products, 'pid');
        $sp = new Shop_Product();
        $products_list = $sp->getBulk($pids);
        $products_list = Tool_Array::list2Map($products_list, 'sid');


        // 获取sku信息
        $sids = Tool_Array::getFields($products_list, 'sid');
        $title_products = Shop_Api::getSkuInfos($sids);
        $productsWarehouseList = self::getStockByWidAndSids($wid, $sids);
        foreach ($productsWarehouseList as $item)
        {
            if($products_list[$item['sid']]['buy_type'] == 2 || in_array($item['sid'],Conf_Order::$Virtual_Skuid_4_Tmp_Purchase))
            {
                continue;
            }
            $num = $item['num']-$item['occupied'];
            $pid = $products_list[$item['sid']]['pid'];
            if($products[$pid]['num']>$num)
            {
                throw new Exception('商品【'.$title_products[$item['sid']]['title'].'】skuid:'.$item['sid'].',库存不足');
            }
        }
    }

    
    
    private function _sortItem($a, $b)
    {
        if ($a['_sort_val'] == $b['_sort_val'])
        {
            return 0;
        }
        return ($a['_sort_val'] > $b['_sort_val']) ? -1 : 1;
    }
    
    private function _sortItemUp($a, $b)
    {
        if ($a['_sort_val'] == $b['_sort_val'])
        {
            return 0;
        }
        return ($a['_sort_val'] > $b['_sort_val']) ? 1 : -1;
    }

    /**
     * 创建盘点计划
     *
     * @param array $info
     *
     * @return array
     */
    public static function addInventoryPlan($info)
    {
        $wip = new Warehouse_Inventory_Plan();
        $pid = $wip->add($info);

        return array('pid' => $pid);
    }

    public static function getInventoryPlan($pid)
    {
        $wip = new Warehouse_Inventory_Plan();
        $data = $wip->get($pid);

        return $data;
    }

    public static function getInventoryPlanProducts($pid, $times = Conf_Stock::STOCKTAKING_TIME_FIRST)
    {
        assert($pid);

        $wip = new Warehouse_Inventory_Plan();
        $plan = $wip->get($pid);

        if (empty($plan))
        {
            return array();
        }

        $data = array();
        if ($plan['step'] == Conf_Stock::STOCKTAKING_PLAN_STEP_NOT_START)
        {
            $wl = new Warehouse_Location();
            $where = sprintf('status=%d AND wid=%d AND location NOT LIKE "VFLoc%%"', Conf_Base::STATUS_NORMAL, $plan['wid']);

            if ($plan['type'] == Conf_Stock::STOCKTAKING_TYPE_BY_LOCATION)      //按货位
            {
                if (!empty($plan['start_location'])) {
                    $where .= sprintf(' and location >= "%s"', $plan['start_location']);
                }
                if (!empty($plan['end_location'])) {
                    $where .= sprintf(' and location <= "%s"', $plan['end_location']);
                }
            }
            else if ($plan['type'] == Conf_Stock::STOCKTAKING_TYPE_BY_BRAND)    //按品牌
            {
                $ss = new Shop_Sku();
                $ssWhere = sprintf('bid in (%s)', trim($plan['brand_id']));
                $skuInfos = $ss->getListByWhere($ssWhere, array('sid'));
                $sids = implode(',', Tool_Array::getFields($skuInfos, 'sid'));
                $where .= sprintf(' and sid in (%s)', $sids);
            }

            $num = 0;
            $order = array('location', 'asc');
            if ($plan['is_random'] == 1)        //抽盘
            {
                $order = array('rand()');
                $num = $plan['random_num'];
            }

            $data = $wl->getRawWhere($where, 0, $num, array('*'), $order);
        }

        if ($plan['step'] == Conf_Stock::STOCKTAKING_PLAN_STEP_ONGOING)
        {
            $searchWhere = sprintf('plan_id = %d', $pid);
            if ($times == Conf_Stock::STOCKTAKING_TIME_FIRST)
            {
                $searchWhere .= ' and task_id1 = 0 and task_id2 = 0 and task_id3 = 0';
            }
            else if ($times == Conf_Stock::STOCKTAKING_TIME_SECOND)
            {
                $searchWhere .= ' and task_id2 = 0 and task_id3 = 0 and is_picked1 = 1 and first_num <> num';
            }
            else if ($times == Conf_Stock::STOCKTAKING_TIME_THIRD)
            {
                $searchWhere .= ' and task_id3 = 0 and is_picked1 = 1 and is_picked2 = 1 and second_num <> num';
            }

            $wips = new Warehouse_Inventory_Products();
            $data = $wips->getListRawWhere($searchWhere, $total, array('location', 'asc'), 0 ,0);
        }

        if ($plan['step'] == Conf_Stock::STOCKTAKING_PLAN_STEP_FINISHED)
        {
            $data = array();
        }

        return $data;
    }

    public static function getInventoryPlanList($conf, $order, $start=0, $num=5)
    {
        $wip = new Warehouse_Inventory_Plan();
        $data = $wip->getList($conf, $total, $order, $start, $num);

        return array('list' => $data, 'total' => $total);
    }

    public static function updateInventoryPlan($pid, $data)
    {
        $wip = new Warehouse_Inventory_Plan();
        $ret = $wip->update($pid, $data);

        return $ret;
    }

    public static function addInventoryPlanProducts($pid, $wid, $products)
    {
        assert(!empty($products));
        $wip = new Warehouse_Inventory_Products();
        $ret = $wip->batchAdd($pid, $wid, $products);

        return $ret;
    }

    public static function createInventoryTask($suid, $pid, $wid, $times, $products, $allocMethod, $allocNum)
    {
        $wit = new Warehouse_Inventory_Task();
        $wip = new Warehouse_Inventory_Products();
        $num = count($products);

        $ret = false;
        if ($allocMethod == 1)
        {
            $taskNum = $allocNum;

            if ($num < $taskNum)
            {
                return false;
            }

            $ret = $wit->batchAdd($suid, $pid, $wid, $times, $taskNum);

            if (empty($ret))
            {
                return false;
            }

            $conf = array(
                'plan_id' => $pid,
                'times' => $times,
                'num' => 0,
            );

            $perTaskNum = floor($num / $taskNum);

            $taskInfo = $wit->getList($conf, $total, array('tid', 'asc'), 0, 0);
            $tidList = array_unique(Tool_Array::getFields($taskInfo, 'tid'));

            foreach ($tidList as $key => $tid)
            {
                $taskProducts = array_slice($products, $key * $perTaskNum, $perTaskNum);
                $wip->updateProduct($pid, $times, $tid, $taskProducts);
                $wit->update($tid, array('num' => count($taskProducts)));
            }

            if ($perTaskNum * $taskNum < $num)
            {
                $tidNum = count($tidList);
                $tid = $tidList[$tidNum - 1];
                $taskProducts = array_slice($products, $tidNum * $perTaskNum, $num - $perTaskNum * $taskNum);

                $wip->updateProduct($pid, $times, $tid, $taskProducts);
                $wit->update($tid, array('num' => count($taskProducts) + $perTaskNum));
            }
        }
        else if ($allocMethod == 2)
        {
            $productNum = $allocNum;
            if ($allocNum > $num)
            {
                return false;
            }
            $info = array(
                'suid' => $suid,
                'plan_id' => $pid,
                'wid' => $wid,
                'times' => $times,
            );
            $ret = $wit->add($info);

            if (empty($ret))
            {
                return false;
            }

            $taskProducts = array_slice($products, 0, $productNum);
            $wip->updateProduct($pid, $times, $ret, $taskProducts);
            $wit->update($ret, array('num' => count($taskProducts)));
        }

        return $ret;
    }

    public static function getInventoryTaskListByPid($pid, $times = '')
    {
        $wit = new Warehouse_Inventory_Task();
        $conf = array('plan_id' => $pid);
        if (!empty($times))
        {
            $conf['times'] = $times;
        }
        $data = $wit->getList($conf, $total, '', 0, 0);

        return $data;
    }

    public static function getInventoryTaskListBySuid($suid, $order = '')
    {
        $wit = new Warehouse_Inventory_Task();
        $conf = array('alloc_suid' => $suid);
        $data = $wit->getList($conf, $total, $order, 0, 0);

        return $data;
    }

    public static function updateInventoryTask($where, $updata)
    {
        $wit = new Warehouse_Inventory_Task();
        $ret = $wit->updateWhere($where, $updata);

        return $ret;
    }

    public static function getTaskProductsByArea($tid, $area)
    {
        $wit = new Warehouse_Inventory_Products();

        $where = "(task_id1 = $tid or task_id2 = $tid or task_id3 = $tid) and location like '%$area%'";
        $data = $wit->getListRawWhere($where, $total, array(), 0, 0);

        return $data;
    }

    public static  function submitTaskProductNum($tid, $sid, $location, $times, $num)
    {
        $wit = new Warehouse_Inventory_Task();
        $task = $wit->get($tid);

        $task_id = 0;
        $is_picked = '';
        switch ($times)
        {
            case 1:
                $task_id = 'task_id1';
                $is_picked = 'is_picked1';
                break;
            case 2:
                $task_id = 'task_id2';
                $is_picked = 'is_picked2';
                break;
            case 3:
                $task_id = 'task_id3';
                $is_picked = 'is_picked3';
                break;
        }
        $wips = new Warehouse_Inventory_Products();
        $ret = $wips->updateTaskProductPickedNum($tid, $sid, $location, $times, $num);

        $sidConf = array(
            $task_id => $tid,
            'sid' => $sid,
            'location' => $location,
        );
        $product = $wips->getList($sidConf, 0, 0, '');

        $info = array();
        if ($num != $product['list'][0]['num'])
        {
            $info['diff_num'] = $task['diff_num'] + 1;
        }

        if ($task['step'] == Conf_Stock::STOCKTAKING_TASK_STEP_ALLOCATED)
        {
            $info['step'] = Conf_Stock::STOCKTAKING_TASK_STEP_ONGOING;

        }

        //判断任务是否完成
        $searWhere = $is_picked . ' = 0 and ' . $task_id . ' = ' . $tid;
        $productList = $wips->getListRawWhere($searWhere, $total, '', 0, 0, array('sid'));

        if (count($productList) == 0)
        {
            $info['step'] = Conf_Stock::STOCKTAKING_TASK_STEP_FINISHED;
            $info['etime'] = date('Y-m-d H:i:s');
        }

        if (!empty($info))
        {
            $wit->update($tid, $info);
        }

        //判断初盘是否完成
        $wip = new Warehouse_Inventory_Plan();
        $plan = $wip->get($task['plan_id']);

        $firstWhere = 'is_picked1 = 0 and plan_id = ' . $task['plan_id'];
        $firstUnFinishProducts = $wips->getListRawWhere($firstWhere, $total, '', 0, 0, array('sid'));

        if ($plan['times'] == Conf_Stock::STOCKTAKING_TIME_FIRST)
        {
            $unFinishedProducts = $firstUnFinishProducts;
        }
        else if ($plan['times'] == Conf_Stock::STOCKTAKING_TIME_SECOND)
        {
            if (count($firstUnFinishProducts) == 0)
            {
                $secondWhere = 'is_picked1 = 1 and num <> first_num and is_picked2 = 0 and plan_id = ' . $task['plan_id'];
                $unFinishedProducts = $wips->getListRawWhere($secondWhere, $total, '', 0, 0, array('sid'));
            }
        }
        else if ($plan['times'] == Conf_Stock::STOCKTAKING_TIME_THIRD)
        {
            if (count($firstUnFinishProducts) == 0)
            {
                $secondWhere = 'is_picked1 = 1 and num <> first_num and is_picked2 = 0 and plan_id = ' . $task['plan_id'];
                $secondUnFinishProducts = $wips->getListRawWhere($secondWhere, $total, '', 0, 0, array('sid'));

                if (count($secondUnFinishProducts) == 0)
                {
                    $thirdWhere = 'is_picked2 = 1 and num <> second_num and is_picked3 = 0 and plan_id = ' . $task['plan_id'];
                    $unFinishedProducts = $wips->getListRawWhere($thirdWhere, $total, '', 0, 0, array('sid'));
                }
            }
        }

        if (isset($unFinishedProducts) && count($unFinishedProducts) == 0)
        {
            $info = array(
                'step' => Conf_Stock::STOCKTAKING_PLAN_STEP_FINISHED,
                'etime' => date('Y-m-d H:i:s'),
            );
            $wip->update($task['plan_id'], $info);
        }

        return $ret;
    }

    public static function getInventoryTaskListByWhere($where, $total, $fields, $order, $start=0, $num=20)
    {
        $wit = new Warehouse_Inventory_Task();
        $data = $wit->getListWhere($where, $total, $fields,$order, $start, $num);

        return $data;
    }

    public static function getDiffTaskListByConf($conf)
    {
        assert(!empty($conf));

        $where = sprintf('status = %d and diff_num <> 0', Conf_Base::STATUS_NORMAL);
        if (!empty($conf['wid']))
        {
            $where .= sprintf(' and wid = %d', $conf['wid']);
        }
        if (!empty($conf['pid']))
        {
            $where .= sprintf(' and plan_id = %d', $conf['pid']);
        }
        if ($conf['is_deal'] == 1)
        {
            $where .= ' and note is not null';
        }
        else if ($conf['is_deal'] == 2)
        {
            $where .= ' and note is null';
        }

        $wit = new Warehouse_Inventory_Task();
        $data = $wit->getListWhere($where, $total, array('*'), $order='', 0, 0);

        return $data;
    }

    public static function getDiffProductList($searchConf)
    {
        assert($searchConf['pid']);
        $wips = new Warehouse_Inventory_Products();
        $wip = new Warehouse_Inventory_Plan();
        $plan = $wip->get($searchConf['pid']);

        if (empty($plan))
        {
            return array();
        }

        $picked_num = '';
        $is_picked = '';
        switch ($plan['times'])
        {
            case 1:
                $is_picked = 'is_picked1';
                $picked_num = 'first_num';
                break;
            case 2:
                $is_picked = 'is_picked2';
                $picked_num = 'second_num';
                break;
            case 3:
                $is_picked = 'is_picked3';
                $picked_num = 'third_num';
                break;
        }

        $where = sprintf('plan_id = %d and %s = 1 and %s <> num', $searchConf['pid'], $is_picked, $picked_num);
        if (!empty($searchConf['is_deal']))
        {
            $where .=  ' and is_deal = '. $searchConf['is_deal'];
        }

        $data = $wips->getListRawWhere($where, $total, '', 0, 0, array('*'));

        $sids = array_unique(Tool_Array::getFields($data, 'sid'));

        $skuInfo = Shop_Api::getSkuInfos($sids);
        foreach ($data as &$product)
        {
            $product['title'] = $skuInfo[$product['sid']]['title'];
            $product['unit'] = $skuInfo[$product['sid']]['unit'];
            $product['diff_num'] = $product["$picked_num"] - $product['num'];
            $product['last_num'] = $product["$picked_num"];
        }
        return $data;
    }

    public static function updateDiffProductNum($pid, $sid, $location, $num, $note)
    {
        $wips = new Warehouse_Inventory_Products();
        $wip = new Warehouse_Inventory_Plan();
        $wit = new Warehouse_Inventory_Task();
        $plan = $wip->get($pid);

        $conf = array(
            'pid' => $pid,
            'sid' => $sid,
            'location' => $location,
        );
        $product = $wips->getList($conf, 0, 0, '');
        $product = $product['list'][0];

        $info = self::_getPlanProductInfo($plan['times']);
        if ($product['num'] == $num)
        {
            $where = sprintf('tid = %d', $product[$info['task_id']]);
            $change = array('diff_num' =>  -1);
            $wit->updateWhere($where, array(), $change);
        }

        $ret = $wips->updateTaskProductPickedNum($product[$info['task_id']], $sid, $location, $plan['times'], $num, $note);

        return $ret;
    }

    public static function getInventoryTaskByTid($tid)
    {
        $wit = new Warehouse_Inventory_Task();
        $data = $wit->get($tid);

        return $data;
    }

    private function _getPlanProductInfo($times)
    {
        $task_id = 0;
        $picked_num = '';
        $is_picked = '';
        switch ($times)
        {
            case 1:
                $task_id = 'task_id1';
                $is_picked = 'is_picked1';
                $picked_num = 'first_num';
                break;
            case 2:
                $task_id = 'task_id2';
                $is_picked = 'is_picked2';
                $picked_num = 'second_num';
                break;
            case 3:
                $task_id = 'task_id3';
                $is_picked = 'is_picked3';
                $picked_num = 'third_num';
                break;
        }

        return array('task_id' => $task_id, 'is_picked' => $is_picked, 'picked_num' => $picked_num);
    }
    
    /**
     * 通过盘点计划/任务/分类1/分类2 获取盘点商品.
     * 
     * @param int $planId
     * @param int $taskId
     * @param int $cate1
     * @param int $cate2
     * @param int $start
     * @param tyintpe $num
     * @param bool $withMoreInfo
     */
    public static function getInventoryProductsByCate($planId, $taskId, $cate1=0, $cate2=0, $start=0, $num=20, $withMoreInfo=false)
    {
        if (empty($planId)) return array();
        
        $inventoryWhere = array();
        if (!empty($taskId))
        {
            $wit = new Warehouse_Inventory_Task();
            $taskInfo = $wit->get($taskId);

            if ($planId!=$taskInfo['plan_id'] || $taskInfo['status']!=Conf_Base::STATUS_NORMAL)
            {
                return array();
            }
            $inventoryWhere = 'task_id'.$taskInfo['times']. '='. $taskId;
        }
        else
        {
            $inventoryWhere = 'plan_id='. $planId;
        }
        
        // 获取商品
        $wip = new Warehouse_Inventory_Products();
        $where = $inventoryWhere;
        if (!empty($cate1))
        {
            $where = $inventoryWhere. ' and sid in (select sid from t_sku where cate1='. $cate1. ')';
        }
        if (!empty($cate2))
        {
            $where = $inventoryWhere. ' and sid in (select sid from t_sku where cate1='. $cate1. ' and cate2='.$cate2. ')';
        }
        
        $productInfo = $wip->getListRawWhere($where, $total, '', $start, $num);
        
        if ($withMoreInfo && $total)
        {
            $ss = new Shop_Sku();
            $sids = Tool_Array::getFields($productInfo, 'sid');
            $skuInfos = $ss->getBulk($sids);

            foreach($productInfo as &$one)
            {
                $one['skuInfo'] = $skuInfos[$one['sid']];
            }
        }
        
        //获取全部一级分类，二级分类
        $cates = array();
        $ss = new Shop_Sku();
        $cateWhere = 'sid in (select sid from t_inventory_products where '. $inventoryWhere. ') group by cate2';
        $cateRet = $ss->getListByWhere($cateWhere, array('cate1', 'cate2'), false);
        
        // 一级分类
        $allCate1 = Conf_Sku::$CATE1;
        $cates['cate1'][0] = array(
            'name' => '全部',
            'html_class' => $cate1==0? 'active': '',
            'query' => '',
        );
        foreach ($cateRet as $cateItem)
        {
            $cates['cate1'][$cateItem['cate1']] = array(
                'name' => $allCate1[$cateItem['cate1']]['name'],
                'html_class' => $cateItem['cate1']==$cate1? 'active': '',
                'query' => '&cate1='. $cateItem['cate1'],
            );
        }
        
        // 二级分类
        $allCate2 = Conf_Sku::$CATE2;
        $cates['cate2'][0] = array(
            'name' => '全部',
            'html_class' => $cate2==0? 'active': '',
            'query' => '&cate1='.$cate1,
        );
        foreach($cateRet as $cateItem)
        {
            if ($cate1!=0 && $cate1!=$cateItem['cate1']) continue;
                
            $cates['cate2'][$cateItem['cate2']] = array(
                'name' => $allCate2[$cateItem['cate1']][$cateItem['cate2']]['name'],
                'html_class' => $cate2==$cateItem['cate2']? 'active': '',
                'query' => '&cate1='.$cateItem['cate1']. '&cate2='.$cateItem['cate2'],
            );
        }
        
        return array('total'=>$total, 'list'=>$productInfo, 'cates'=>$cates);
    }
    
    /**
     * 通过盘点计划/任务 获取盘点商品.
     * 
     * @param int $planId
     * @param int $taskId
     * @param int $start
     * @param int $num
     * @param bool $withMoreInfo
     */
    public static function getInventoryProducts($planId, $taskId=0, $start=0, $num=20, $withMoreInfo=false)
    {
        $productInfo = array();
        if (empty($taskId))
        {
            $productInfo = self::getInventoryProductsByPlanId($planId, $start, $num);
        }
        else
        {
            $productInfo = self::getInventoryProductsByTaskId($planId, $taskId, $start, $num);
        }
        
        if ($withMoreInfo && $productInfo['total']>0)
        {
            $ss = new Shop_Sku();
            $sids = Tool_Array::getFields($productInfo['list'], 'sid');
            $skuInfos = $ss->getBulk($sids);
            
            foreach($productInfo['list'] as &$one)
            {
                $one['skuInfo'] = $skuInfos[$one['sid']];
            }
        }
        
        return $productInfo;
    }
    
    protected static function getInventoryProductsByPlanId($planId, $start=0, $num=20)
    {
        $wip = new Warehouse_Inventory_Products();
        
        $conf = array(
            'plan_id' => $planId,
        );
        $productInfos = $wip->getList($conf, $start, $num);
        
        return $productInfos;
    }
    
    protected static function getInventoryProductsByTaskId($planId, $taskId, $start=0, $num=20)
    {
        $wit = new Warehouse_Inventory_Task();
        $taskInfo = $wit->get($taskId);
        
        if ($planId!=$taskInfo['plan_id'] || $taskInfo['status']!=Conf_Base::STATUS_NORMAL)
        {
            return array();
        }
        
        $wip = new Warehouse_Inventory_Products();
        
        $conf = array(
            'task_id'.$taskInfo['times'] => $taskId,
        );
        $productInfos = $wip->getList($conf, $start, $num);
        
        return $productInfos;
    }

    public static function addOtherStockOutOrder($info)
    {
        $wosoo = new Warehouse_Other_Stock_Out_Order();
        $ret = $wosoo->add($info);

        return $ret;
    }

    public static function getOtherStockOutProductInfo($wid, $products)
    {
        $locations = Warehouse_Location_Api::distributeNumFromLocation($products, $wid);
        if (!is_array($locations))
        {
            $errmsg = array(
                '-1' => '仓库不支持新库逻辑',
                '-2' => '仓库没有该商品，请添加！',
                '-3' => '货架库存不足',
            );
            throw new Exception(array_key_exists($locations, $errmsg)?
                $errmsg[$locations]:'添加商品失败');
        }

        $sids = Tool_Array::getFields($products, 'sid');
        if (!empty($sids))
        {
            $ss = new Shop_Sku();
            $skuInfos = Tool_Array::list2Map($ss->getBulk($sids), 'sid');
            $allCate1 = Conf_Sku::$CATE1;
            $allCate2 = Conf_Sku::$CATE2;
            foreach($products as &$one)
            {
                $one['title'] = $skuInfos[$one['sid']]['title'];
                $one['unit'] = $skuInfos[$one['sid']]['unit'];

                $cate1 = $skuInfos[$one['sid']]['cate1'];
                $cate2 = $skuInfos[$one['sid']]['cate2'];
                $one['cate_desc'] = $allCate1[$cate1]['name'].' # '. $allCate2[$cate1][$cate2]['name'];
            }
        }

        foreach($products as &$p)
        {
            $p['from_location'] = $locations[$p['sid']];
        }

        Warehouse_Location_Api::parseLocationAndNum($products, 'from_location');

        return $products;
    }

    public static function saveOtherStockOutOrder($suid, $info)
    {
        $info['suid'] = $suid;
        $wosoo = new Warehouse_Other_Stock_Out_Order();
        $oid = $wosoo->add($info);

        return $oid;
    }

    public static function saveOtherStockOutOrderProducts($oid, $products)
    {
        assert($oid);
        assert($products);

        $wosoo = new Warehouse_Other_Stock_Out_Order();
        $wosoop = new Warehouse_Other_Stock_Out_Products();
        
        $order = $wosoo->get($oid);

        $sids = Tool_Array::getFields($products, 'sid');
        if ($order['order_type'] == Conf_Stock::OTHER_STOCK_ORDER_TYPE_OUT)
        {
            $oldProducts = $wosoop->getList(array('oid' => $oid, 'status' => Conf_Base::STATUS_NORMAL));

            $costList = Shop_Api::getCostBySids($sids, $order['wid']);
            if (!empty($oldProducts))
            {
                $oldSids = Tool_Array::getFields($oldProducts, 'sid');
                $diffSids = array_intersect($oldSids, $sids);
                $diff = implode('、', $diffSids);

                if (count($diffSids) > 0)
                {
                    throw new Exception('商品' . $diff . '已添加，请在下面商品清单中修改数量！');
                }

            }

            $locations = Warehouse_Location_Api::distributeNumFromLocation($products, $order['wid']);

            if (!is_array($locations))
            {
                $errmsg = array(
                    '-1' => '仓库不支持新库逻辑',
                    '-2' => '仓库没有该商品，请添加！',
                    '-3' => '货架库存不足',
                );
                throw new Exception(array_key_exists($locations, $errmsg)?
                    $errmsg[$locations]:'添加商品失败');
            }

            foreach($products as &$p)
            {
                $p['cost'] = $costList[$p['sid']];
                $p['from_location'] = $locations[$p['sid']];
            }
        }

        //获取经营模式
        $sp = new Shop_Product();
        $productInfo = Tool_Array::list2Map($sp->getBySku($sids, Conf_Warehouse::$WAREHOUSE_CITY_MAPPING[$order['wid']], 7), 'sid');
        foreach($products as &$p)
        {
            $p['managing_mode'] = $productInfo[$p['sid']]['managing_mode'];
        }
        
        $wosoop->add($oid, $products);

        return true;
    }

    public static function saveOtherStockOutOrderBrokenProducts($oid, $products)
    {
        assert($oid);
        assert($products);

        $wosoo = new Warehouse_Other_Stock_Out_Order();
        $wosoop = new Warehouse_Other_Stock_Out_Products();
        $order = $wosoo->get($oid);

        if (!($order['order_type'] == Conf_Stock::OTHER_STOCK_ORDER_TYPE_OUT && $order['type'] == Conf_Stock::OTHER_STOCK_OUT_TYPE_BROKEN))
        {
            throw new Exception('单据类型错误！');
        }

        if ($order['order_type'] == Conf_Stock::OTHER_STOCK_ORDER_TYPE_OUT && $order['type'] == Conf_Stock::OTHER_STOCK_OUT_TYPE_BROKEN)
        {
            $oldProducts = $wosoop->getList(array('oid' => $oid, 'status' => Conf_Base::STATUS_NORMAL));

            $sids = Tool_Array::getFields($products, 'sid');
            $costList = Shop_Api::getCostBySids($sids, $order['wid']);

            if (!empty($oldProducts))
            {
                $oldSids = Tool_Array::getFields($oldProducts, 'sid');
                $diffSids = array_intersect($oldSids, $sids);
                $diff = implode('、', $diffSids);

                if (count($diffSids) > 0)
                {
                    throw new Exception('商品' . $diff . '已添加，请在下面商品清单中修改数量！');
                }
            }

            //获取经营模式
            $sp = new Shop_Product();
            $productInfo = Tool_Array::list2Map($sp->getBySku($sids, Conf_Warehouse::$WAREHOUSE_CITY_MAPPING[$order['wid']], 7), 'sid');
            
            foreach($products as &$p)
            {
                $p['cost'] = $costList[$p['sid']];
                $p['from_location'] = Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_DAMAGED];
                $p['managing_mode'] = $productInfo[$p['sid']]['managing_mode'];
            }
            
            $wosoop->add($oid, $products);

            return true;
        }
    }

    public static function updateOtherStockOutOrderProducts($oid, $products)
    {
        assert($oid);
        assert($products);

        $wosoo = new Warehouse_Other_Stock_Out_Order();
        $wosoop = new Warehouse_Other_Stock_Out_Products();
        $order = $wosoo->get($oid);
        $op = $wosoop->getList(array('oid' => $oid));
        $list = Tool_Array::list2Map($op, 'sid');

        foreach ($products as $_sid => $_product)
        {
            $loc = '';
            $num = 0;
            foreach ($_product as $_loc => $item)
            {
                $_num = $item['num'];
                $_note = $item['note'];
                if ($_num != 0)
                {
                    $loc .= $_loc . ':' . $_num . ',';
                    $num += $_num;
                }
                else
                {
                    unset($_product[$_loc]);
                }
            }

            $conf = array(
                'oid' => $oid,
                'sid' => $_sid,
            );
            $info = array(
                'from_location' => trim($loc, ','),
                'num' => $num,
                'note' => $_note
            );

            if ($num == 0)
            {
                throw new Exception('商品总数为0时，请直接删除该商品！');
            }

            if ($order['step'] == Conf_Stock::OTHER_STOCK_OUT_ORDER_STEP_AUDITED && $num != $list[$_sid]['num'])
            {
                throw new Exception('商品数量不等于已审核数量！');
            }

            if ($order['order_type'] == Conf_Stock::OTHER_STOCK_ORDER_TYPE_OUT)
            {
                if ( count($_product) == 1)
                {
                    $info['from_location'] = substr($loc, 0, 10);
                }
            }

            $wosoop->updateByConf($conf, $info);
        }


        return true;
    }

    public static function updateOtherStockInOrderProducts($oid, $product, $suid)
    {
        assert($oid);
        assert($product);

        $wosoo = new Warehouse_Other_Stock_Out_Order();
        $order = $wosoo->get($oid);
        $wosoop = new Warehouse_Other_Stock_Out_Products();
        $op = $wosoop->getList(array('oid' => $oid, 'sid' => $product['sid']));
        $list = Tool_Array::list2Map($op, 'sid');
        
        $allowNum = intval($product['num']) - ($list[$product['sid']]['num'] - $list[$product['sid']]['shelved_num']);
        if ($allowNum > 0)
        {
            throw new Exception('上架总数不能大于该商品入库总数！');
        }
        $conf = array(
            'oid' => $oid,
            'sid' => $product['sid'],
        );
        $info = array(
            'shelved_num' => $product['num'] + $list[$product['sid']]['shelved_num'],
            'from_location' => $product['loc'],
            'cost' => $product['cost'],
        );
        $wosoop->updateByConf($conf, $info);

        $data = array('stock_suid' => $suid);

        if ($order['step'] == Conf_Stock::OTHER_STOCK_OUT_ORDER_STEP_AUDITED)
        {
            $data['step'] = Conf_Stock::OTHER_STOCK_OUT_ORDER_STEP_PART_SHELVED;
        }
        $wosoo->update($oid, $data);

        return true;
    }

    public static function checkOtherStockInOrderShelvedStatus($oid)
    {
        $wosoo = new Warehouse_Other_Stock_Out_Order();
        $wosoop = new Warehouse_Other_Stock_Out_Products();
        $products = $wosoop->getList(array('oid' => $oid));
        $isFinish = true;
        foreach ($products as $product) {
            if ($product['num'] != $product['shelved_num'])
            {
                $isFinish = false;
            }
        }

        if ($isFinish)
        {
            $wosoo->update($oid, array('step' => Conf_Stock::OTHER_STOCK_OUT_ORDER_STEP_FINISH));
        }

        return true;
    }

    public static function getOtherStockOutOrderList($searConf, $order, $start, $num)
    {
        $wosoo = new Warehouse_Other_Stock_Out_Order();
        $data = $wosoo->getList($searConf, $total, $order, $start, $num);

        if (!empty($data))
        {
            $oids = Tool_Array::getFields($data, 'oid');
            $wosoop = new Warehouse_Other_Stock_Out_Products();
            $products = $wosoop->getList(array('oid' => $oids));

            foreach ($products as $_product)
            {
                $data[$_product['oid']]['cost'] += $_product['cost'] * $_product['num'];
            }
        }

        return array('total' => $total, 'list' => $data);
    }

    public static function getOtherStockOutOrderDetail($oid)
    {
        assert($oid);

        $wosoo = new Warehouse_Other_Stock_Out_Order();
        $wosoop = new Warehouse_Other_Stock_Out_Products();

        $order = $wosoo->get($oid);
        $order['products'] = $wosoop->getList(array('oid' => $oid, 'status' => Conf_Base::STATUS_NORMAL));

        $sids = Tool_Array::getFields($order['products'], 'sid');

        $cityId = Conf_Warehouse::$WAREHOUSE_CITY_MAPPING[$order['wid']];
        $pids = Shop_Api::getProductsBySids($sids, $cityId);
        $pids = Tool_Array::list2Map($pids, 'sid');

        $ss = new Shop_Sku();
        $skus = $ss->getBulk($sids);

        $cate1 = Conf_Sku::$CATE1;
        $buyTypes = Conf_Product::getBuyTypeDesc(2);
        foreach ($order['products'] as &$product)
        {
            $product['buy_type'] = $buyTypes[$pids[$product['sid']]['buy_type']];
            $product['title'] = $skus[$product['sid']]['title'];
            $product['unit'] = $skus[$product['sid']]['unit'];
            $product['cate_desc'] = $cate1[$skus[$product['sid']]['cate1']]['name'];
        }
        Warehouse_Location_Api::parseLocationAndNum($order['products'], 'from_location');

        if (!empty($sids)) {
            $wl = new Warehouse_Location();
            $locationInfo = $wl->getBySid($sids, $order['wid'], 'actual');

            $locationList = array();
            foreach ($locationInfo as $_location) {
                $locationList[$_location['sid']][$_location['location']] = $_location['num'];
            }
            $order['loc_list'] = $locationList;
        }

        foreach ($order['products'] as &$_product)
        {
            $list = array();
            foreach ($_product['_from_location'] as $_loc => $value)
            {
                $loc = explode(':', $value['loc']);
                $list[$loc[0]] = $value;
            }
            $_product['_from_location'] = $list;
        }


        return $order;
    }

    public static function getOtherStockOutOrderBrokenDetail($oid)
    {
        assert($oid);

        $wosoo = new Warehouse_Other_Stock_Out_Order();
        $wosoop = new Warehouse_Other_Stock_Out_Products();

        $order = $wosoo->get($oid);
        $order['products'] = $wosoop->getList(array('oid' => $oid, 'status' => Conf_Base::STATUS_NORMAL));

        $sids = Tool_Array::getFields($order['products'], 'sid');

        $cityId = Conf_Warehouse::$WAREHOUSE_CITY_MAPPING[$order['wid']];
        $pids = Shop_Api::getProductsBySids($sids, $cityId);
        $pids = Tool_Array::list2Map($pids, 'sid');

        $ss = new Shop_Sku();
        $skus = $ss->getBulk($sids);

        $cate1 = Conf_Sku::$CATE1;
        $buyTypes = Conf_Product::getBuyTypeDesc(2);
        foreach ($order['products'] as &$product)
        {
            $product['buy_type'] = $buyTypes[$pids[$product['sid']]['buy_type']];
            $product['title'] = $skus[$product['sid']]['title'];
            $product['unit'] = $skus[$product['sid']]['unit'];
            $product['cate_desc'] = $cate1[$skus[$product['sid']]['cate1']]['name'];
        }

        $loc = Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_DAMAGED]['flag'];

        $wl = new Warehouse_Location();
        $where = array('sid' => $sids, 'location' => Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_DAMAGED]['flag'], 'wid' => $order['wid']);
        $locations = Tool_Array::list2Map($wl->getRawWhere($where, 0, 0), 'sid');

        if (!empty($sids)) {
            foreach ($order['products'] as $_p) {
                $order['loc_list'][$_p['sid']] = array($loc => $locations[$_p['sid']]['num']);
            }
        }

        foreach ($order['products'] as &$_product)
        {
            $_product['_from_location'][$loc] = array('loc' => $loc, 'num' => $_product['num'], 'vnum' => 0);
        }

        return $order;
    }

    public static function changeOtherStockOutOrder($oid, $execType, $suid, $orderType)
    {
        assert($oid);
        assert($execType);
        $wosoo = new Warehouse_Other_Stock_Out_Order();
        $order = $wosoo->get($oid);

        $as = new Admin_Staff();
        $staffList = Tool_Array::list2Map($as->getAll(), 'suid');
        $typeList = Conf_Stock::getOtherStockTypes($orderType);

        $ret = false;
        $msg = '';
        if ($execType == 'del')
        {
            $info = array(
                'status' => Conf_Base::STATUS_DELETED
            );
            $wosoop = new Warehouse_Other_Stock_Out_Products();
            $productRet = $wosoop->update($oid, $info);
            $msg = '删除出库单成功！';

            $orderRet = $wosoo->update($oid, $info);
            $ret = $productRet && $orderRet;
        }
        elseif ($execType == 'wait_audit')
        {
            $info = array(
                'step' => Conf_Stock::OTHER_STOCK_OUT_ORDER_STEP_WAIT_AUDIT
            );
            $msg = '操作成功！';

            $ret = $wosoo->update($oid, $info);

            if ($order['step'] == Conf_Stock::OTHER_STOCK_OUT_ORDER_STEP_UN_AUDIT)
            {
                $str = '修改并提交被驳回的';
            }
            else
            {
                $str = '提交';
            }
            // 先去掉：原因 审核人员变动大，导致会出现异常 addby guoqiang/20170925
//            $messageData = array(
//                'm_type' => 1,
//                'typeid' => 0,
//                'content' => $staffList[$order['suid']]['name'] . $str . '【'.$typeList[$order['type']].'单ID:'.$oid.'】；需要审核处理。',
//                'send_suid' => $suid,
//                'receive_suid' => Conf_Stock::getOtherStockOutCheckSuid($order['wid']),
//                'url' => '/warehouse/other_stock_out_order_detail.php?oid='.$oid,
//            );
//            Admin_Message_Api::create($messageData);
        }
        elseif ($execType == 'un_audit')
        {
            $info = array(
                'step' => Conf_Stock::OTHER_STOCK_OUT_ORDER_STEP_UN_AUDIT,
                'check_suid' => $suid,
            );
            $msg = '驳回成功！';

            $ret = $wosoo->update($oid, $info);

            $messageData = array(
                'm_type' => 1,
                'typeid' => 0,
                'content' => '驳回【'.$typeList[$order['type']].'单ID:'.$oid .'】',
                'send_suid' => $suid,
                'receive_suid' => $order['suid'],
                'url' => '/warehouse/other_stock_out_order_detail.php?oid='.$oid,
            );
            Admin_Message_Api::create($messageData);
        }
        elseif ($execType == 'audit')
        {
            $info = array(
                'step' => Conf_Stock::OTHER_STOCK_OUT_ORDER_STEP_AUDITED,
                'check_suid' => $suid,
            );
            $msg = '审核成功！';
            
            $ret = $wosoo->update($oid, $info);

            $messageData = array(
                'm_type' => 1,
                'typeid' => 0,
                'content' => '【'.$typeList[$order['type']].'单ID:'.$oid .'】审核通过',
                'send_suid' => $suid,
                'receive_suid' => $order['suid'],
                'url' => '/warehouse/other_stock_out_order_detail.php?oid='.$oid,
            );
            Admin_Message_Api::create($messageData);
        }
        elseif ($execType == 'finish')
        {
            $upRet = false;
            $upHistoryRet = false;
            //$ooc = new Order_Occupied();
            $ws = new Warehouse_Stock();
            $wsh = new Warehouse_Stock_History();
            $wosoo = new Warehouse_Other_Stock_Out_Order();
            $wosoop = new Warehouse_Other_Stock_Out_Products();

            //获取出库单及出库商品商品信息
            $order = $wosoo->get($oid);
            $products = $wosoop->getList(array('oid' => $oid));
            $skuIds = Tool_Array::getFields($products, 'sid');
            
            // 取更新前库存记录
            $oldStocks = Tool_Array::list2Map($ws->getBulk($order['wid'], $skuIds), 'sid');
            Warehouse_Location_Api::parseLocationAndNum($products, 'from_location');
            
            if ($order['order_type'] == Conf_Stock::OTHER_STOCK_ORDER_TYPE_OUT && $order['type'] == Conf_Stock::OTHER_STOCK_OUT_TYPE_BROKEN)
            {
                $wl = new Warehouse_Location();
                $where = array('sid' => $skuIds, 'location' => Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_DAMAGED]['flag'], 'wid' => $order['wid']);
                $locations = Tool_Array::list2Map($wl->getRawWhere($where, 0, 0), 'sid');

                foreach ($products as $_p)
                {
                    if ($_p['from_location'] != Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_DAMAGED]['flag'])
                    {
                        throw new Exception('报损货位错误！');
                    }
                    if ($_p['num'] > $locations[$_p['sid']]['num'])
                    {
                        throw new Exception('残损货位,商品(sid:' . $_p['sid'] . ')数量不足，不能出库!');
                    }
                }
            }
            else
            {
                self::_checkStockShiftForStockOut($order['wid'], $products);
            }

            foreach($products as $one)
            {
                $change = array('num' => 0 - abs($one['num']));
                if ($order['order_type'] == Conf_Stock::OTHER_STOCK_ORDER_TYPE_OUT && $order['type'] == Conf_Stock::OTHER_STOCK_OUT_TYPE_BROKEN)
                {
                    $change['damaged_num'] = 0 - abs($one['num']);
                }
                $upRet = $ws->update($order['wid'], $one['sid'], array(), $change);

                // 写出入库历史
                $reason = Conf_Stock::$STOCK_OUT_HISTORY_REASON_CONVERT[$order['type']][$order['reason']];
                $stockHistoryData = self::_genStockHistoryDataForOtherStockOutOrder($one, $oldStocks[$one['sid']], $suid, $reason);
                $upHistoryRet = $wsh->add($order['wid'], $one['sid'], $stockHistoryData);

                // 新库逻辑：更新货架库存
                if (Conf_Warehouse::isUpgradeWarehouse($order['wid']))
                {
                    $wl = new Warehouse_Location();
                    foreach($one['_from_location'] as $loc)
                    {
                        $chgData = array('num'=>(0-abs($loc['num'])));
                        $wl->update($one['sid'], substr($loc['loc'], 0, 10), $order['wid'], array(), $chgData);
                    }
                }

                // 刷新销售单商品的占用
                //$ooc->refreshOccupiedOfSkuWhen($one['sid'], $order['wid']);
            }

            //更新出库单状态
            $info = array(
                'step' => Conf_Stock::OTHER_STOCK_OUT_ORDER_STEP_FINISH,
                'stock_suid' => $suid,
                'etime' => date('Y-m-d H:i:s'),
            );
            $msg = '操作成功';
            $ret = $wosoo->update($oid, $info);
            
            //新刷新占用逻辑：addby:guoqiang/2017-06-12
            $wso = new Warehouse_Stock_Occupied();
            $wso->autoRefreshOccupied($order['wid'], $skuIds);

            //Cost-FIFO 出队，并更新单据成本
            $refreshCostDatas = Shop_Cost_Api::getCostsWithSkuAndNums($order['wid'], $products);
            $billInfo = array('out_id'=>$oid, 'out_type'=>Conf_Warehouse::STOCK_HISTORY_OTHER_STOCK_OUT);
            foreach($refreshCostDatas as $_sid => $fifoCosts)
            {
                if (empty($fifoCosts['_cost_fifo'])) continue;
                
                $wosoop->updateByConf(array('oid'=>$oid, 'sid'=>$_sid), array('cost'=>$fifoCosts['cost']));
                Shop_Cost_Api::dequeue4FifoCost($_sid, $order['wid'], $billInfo, $fifoCosts['_cost_fifo']);
            }
            
            $ret = $ret && $upRet && $upHistoryRet;
        }

        return array('ret' => $ret, 'msg' => $msg);
    }

    public function delOtherStockOutProduct($oid, $sid)
    {
        $where = array(
            'oid' => $oid,
            'sid' => $sid,
        );
        $wosoop = new Warehouse_Other_Stock_Out_Products();
        $ret = $wosoop->deleteWhere($where);

        return $ret;
    }

    public static function updateOtherStockOutOrder($oid, $info)
    {
        $wosoo = new Warehouse_Other_Stock_Out_Order();
        $ret = $wosoo->update($oid, $info);

        return $ret;
    }

    // 写出入库历史记录
    private static function _genStockHistoryDataForOtherStockOutOrder($otherData, $rawData, $suid, $reason)
    {
        $currNum = isset($rawData['num']) ? $rawData['num'] : 0;
        $currOccupied = isset($rawData['old_occupied']) ? $rawData['old_occupied'] : 0;
        $addData = array(
            'old_num' => $currNum,
            'old_occupied' => $currOccupied,
            'num' => 0 - $otherData['num'],
            'occupied' => 0,
            'iid' => $otherData['oid'],
            'suid' => $suid,
            'type' => Conf_Warehouse::STOCK_HISTORY_OTHER_STOCK_OUT,
            'reason' => $reason
        );

        return $addData;
    }

    public static function getRefundProductsBySrid($srid)
    {
        $wsip = new Warehouse_Stock_In_Product();
        $products = $wsip->getByRawWhere(array('srid' => $srid));

        //补充sku信息
        $sids = Tool_Array::getFields($products, 'sid');
        $ss = new Shop_Sku();
        $skuInfos = $ss->getBulk($sids);

        foreach ($products as &$_product)
        {
            $_product['sku'] = $skuInfos[$_product['sid']];
        }

        return $products;
    }

    /**
     * 获取某个供货商商品{$sids}最近{$num}次的入库信息（例：某个供货商某些sid最近5次入库价格）
     *
     * @param $supplierId
     * @param $sids
     * @param $start
     * @param $num
     * @param array $field
     * @return array
     */
    public static function getSupplierLatestSkuListFromStockIn($supplierId, $sids, $start, $num, $field=array('*'))
    {
        $products = array();
        foreach ($sids as $_sid) {
            $where = sprintf('status = 0 and srid = 0 and id in (select id from t_stock_in where status = 0 and sid = %d)
                and sid = %d', $supplierId, $_sid);
            $order = 'order by ctime desc';
            $wsip = new Warehouse_Stock_In_Product();
            $products[$_sid] = $wsip->getByRawWhere($where, '', $field, $order, $start, $num);
        }

        return $products;
    }

    /**
     * 更新供货商退货商品单价
     *
     * @param $srid
     * @param $sid
     * @param $price
     */

    public static function updateSupplierRefundProductPrice($srid, $sid, $price)
    {
        if (empty($srid) || empty($sid) || empty($price))
        {
            return false;
        }
        
        // 更新商品单价
        $product['price'] = $price;
        $wsip = new Warehouse_Stock_In_Product();
        $wsip->updateRefundProduct($srid, $sid, $product);

        // 更新入库退单价钱
        $where = array(
            'srid' => $srid,
            'status' => Conf_Base::STATUS_NORMAL
        );
        $totalPrice = $wsip->getSumByWhere('num*price', $where);

        $data = array(
            'price' => $totalPrice,
        );
        $wsir = new Warehouse_Stock_In_Refund();
        $wsir->update($srid, $data);
    }

    /**
     * 查询指定城市下的外包商信息
     *
     * @param $cityId
     * @return array
     */
    public static function getOutSourcerList($cityId)
    {
        $stockDao = new Data_Dao('t_stock');
        $ws = new Warehouse_Supplier();

        $cityMap = Conf_Warehouse::$WAREHOUSE_CITY_MAPPING;

        $wids = array();
        foreach ($cityMap as $wid => $city)
        {
            if ($city == $cityId)
            {
                $wids[] = $wid;
            }
        }

        $where = sprintf(" wid in(%s) and outsourcer_id>0 ", join(',', $wids));
        $stockList = $stockDao->setFields(array('outsourcer_id', 'wid'))->getListWhere($where);

        if (empty($stockList))
        {
            return array();
        }

        $supplierInfo = array();
        foreach ($stockList as $item)
        {
            $info = $ws->get($item['outsourcer_id']);

            $supplierInfo[$item['outsourcer_id']] = array('wid' => $item['wid'], 'outsourcer_id' => $item['outsourcer_id'], 'name' => $info['name']);

        }

        return $supplierInfo;
    }

}
