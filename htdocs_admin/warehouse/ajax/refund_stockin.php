<?php

/**
 * 入库单退货.
 * 
 * @author guoqiang yang
 * 
 * @todo 很多逻辑写的太深了，待重构！
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    const   OP_TYPE_CREATE = 'create',
            OP_TYPE_SHOW = 'show',
            OP_TYPE_CONFIRM = 'confirm';
    
    private $id;
    private $srid;
    private $optype;
    private $productList;
    private $stockInProduct;
    private $stockInInfo;
    private $response;
    private $refundPrice = 0;
    private $waitRefundProduct = array();
    
    
    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->srid = Tool_Input::clean('r', 'srid', TYPE_UINT);
        $this->optype = Tool_Input::clean('r', 'optype', TYPE_STR);
        $this->productList = Tool_Input::clean('r', 'product_list', TYPE_STR);
    }
    
    protected function checkPara()
    {
        if ( empty($this->id) || !in_array($this->optype, array(self::OP_TYPE_CREATE, self::OP_TYPE_SHOW)))
        {
            throw new Exception('参数错误！');
        }
    }
    
    protected function main()
    {
        // 初始化相应状态
        $this->response['st'] = 1;

        // 获取采购单信息
        $this->_getStockInInfo();
        
        // 第三方仓库/经销商仓库 不允许退货
        if (Conf_Warehouse::isAgentWid($this->stockInInfo['wid']))
        {
            throw new Exception('经销商入库单，请通过调拨退货！');
        }
        
        //锁库判断
        $lockedRet = Conf_Warehouse::isLockedWarehouse($this->stockInInfo['wid']);
        if ($lockedRet['st'])
        {
            throw new Exception($lockedRet['msg']);
        }
        
        if ($this->optype == self::OP_TYPE_SHOW)
        {
            $this->_showRefundStockin();
        }
        else if ($this->optype == self::OP_TYPE_CREATE)
        {
            $this->_createRefundStockin();
        }
        else if ($this->optype == self::OP_TYPE_CONFIRM)
        {
            $this->_confirmRefundStockin();
        }

        $this->response['id'] = $this->id;
    }
    
    protected function outputBody()
    {
		$response = new Response_Ajax();
		$response->setContent($this->response);
		$response->send();
        
		exit;
    }
    
    
    private function _getStockInInfo()
    {
        $this->stockInInfo = Warehouse_Api::getStockInInfo($this->id);
        
        // 入库单商品
        $wsip = new Warehouse_Stock_In_Product();
        $products = $wsip->getProductsOfStockIn($this->id);
        
        $sids = array_unique(Tool_Array::getFields($products, 'sid'));
        
        // 获取货位库存
        $wl = new Warehouse_Location();
        $stockOfLoc = $wl->getLocationsBySids($sids, $this->stockInInfo['wid'], 'actual');
        
        $hadRefundProducts = array();
        foreach($products as $_product)
        {
            $sid = $_product['sid'];
            
            if ($_product['srid'] != 0)
            {
                $hadRefundProducts[$sid] = isset($hadRefundProducts[$sid]) ? $hadRefundProducts[$sid] + $_product['num'] : $_product['num'];
            }
        }
        
        // 已建退货单，但是未出库，不能重复退货
        $refundBuyNoStockOut = array();
        if (!empty($hadRefundProducts))
        {
            $wsir = new Warehouse_Stock_In_Refund();
            
            $hadRefundInfos = $wsir->getByStockids(array($this->id));
            
            foreach($hadRefundInfos as $one)
            {
                if ($one['step'] != Conf_Stockin_Refund::UN_REFUND)
                {
                    continue;
                }
                
                foreach($products as $_pinfo)
                {
                    if ($_pinfo['srid'] == $one['srid'])
                    {
                        $refundBuyNoStockOut[$_pinfo['sid']] = !array_key_exists($_pinfo['sid'], $refundBuyNoStockOut)? $_pinfo['num']:
                                                $refundBuyNoStockOut[$_pinfo['sid']]+$_pinfo['num'];
                    }
                }
            }
        }
        
        
        // sku-info
        $ss = new Shop_Sku();
        $skuInfos = $ss->getBulk($sids);
        
        // 入库单商品信息
        foreach($products as $_p)
        {
            $sid = $_p['sid'];
            
            $willStockOut = array_key_exists($sid, $refundBuyNoStockOut)? $refundBuyNoStockOut[$sid]: 0;
            
            if ($_p['srid'] != 0)
            {
                if ($this->srid == $_p['srid'])
                {
                    foreach($stockOfLoc as $spinfo)
                    {
                        if ($sid==$spinfo['sid'] && $_p['location']==$spinfo['location'])
                        {
                            $_p['loc_num'] = $spinfo['num']-$willStockOut;
                            $_p['real_loc_num'] = $spinfo['num'];
                            continue;
                        }
                    }
                    $this->waitRefundProduct[] = $_p;
                }
                
                continue;
            }
            
            // 普采退货：只能充入库货位上退货
//            if($this->stockInInfo['source'] == Conf_In_Order::SRC_COMMON)
//            {
//                foreach($stockOfLoc as $spinfo)
//                {
//                    if ($sid==$spinfo['sid'] && $_p['location']==$spinfo['location'])
//                    {
//                        $_p['loc_num'] = $spinfo['num']-$willStockOut;
//                        $_p['real_loc_num'] = $spinfo['num'];
//                        continue;
//                    }
//                }
//            }
//             //临采退货：从实际货位上退货（默认只有一个货位, 临采商品不应该有多个货位）
//            else
//            {
//                foreach($stockOfLoc as $spinfo)
//                {
//                    if ($sid==$spinfo['sid'] && $spinfo['num']>0)
//                    {
//                        $_p['location'] = $spinfo['location'];
//                        $_p['loc_num'] = $spinfo['num']-$willStockOut;
//                        $_p['real_loc_num'] = $spinfo['num'];
//                        continue;
//                    }
//                }
//            }
            foreach($stockOfLoc as $spinfo)
            {
                if ($sid==$spinfo['sid'] && $spinfo['num']>0)
                {
                    $_p['location'] = $spinfo['location'];
                    $_p['loc_num'] = $spinfo['num']-$willStockOut;
                    $_p['real_loc_num'] = $spinfo['num'];
                    continue;
                }
            }
            
            $_p['had_refund'] = isset($hadRefundProducts[$sid]) ? $hadRefundProducts[$sid] : 0;
            $_p['sku'] = $skuInfos[$sid];
            $this->stockInProduct[$sid] = $_p;
        }
    }
    
    private function _showRefundStockin()
    {
        $this->smarty->assign('products', $this->stockInProduct);
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('cate2_list', Conf_Sku::$CATE2);
        
        $html = $this->smarty->fetch('warehouse/aj_refund_stockin.html');
        
        $this->response['html'] = $html;
    }
    
    private function _createRefundStockin()
    {
        $this->productList = json_decode($this->productList, true);
        
        // 校验退货商品的合法性
        if (!$this->_chkRefundStockin())
        {
            $this->response['st'] = 0;
            return;
        }
        
        // 创建入库退货单
        $datas = array(
            'stockin_id' => $this->stockInInfo['id'],
            'supplier_id' => $this->stockInInfo['sid'],
            'wid' => $this->stockInInfo['wid'],
            'price' => $this->refundPrice,
            'step' => Conf_Stockin_Refund::UN_REFUND,
            'note' => '',
            'suid' => $this->_uid,
        );

        if (Conf_Base::switchForManagingMode())
        {
            $sids = Tool_Array::getFields($this->productList, 'sid');
            $sp = new Shop_Product();
            $productInfo = Tool_Array::list2Map($sp->getBySku($sids, Conf_Warehouse::$WAREHOUSE_CITY_MAPPING[$this->stockInInfo['wid']], 3), 'sid');
            $managingModeFlag = 0;

            foreach ($this->productList as $_p)
            {
                if (empty($productInfo[$_p['sid']]['managing_mode']))
                {
                    throw new Exception('商品(pid:' . $_p['pid'] . ')经营模式属性不存在！');
                }
                if (empty($managingModeFlag))
                {
                    $managingModeFlag = $productInfo[$_p['sid']]['managing_mode'];
                }
                if ($managingModeFlag != $productInfo[$_p['sid']]['managing_mode'])
                {
                    throw new Exception('选择的商品经营模式不一致！');
                }
            }

            if ($managingModeFlag != $this->stockInInfo['managing_mode'])
            {
                throw new Exception('退货商品商品与入库单经营模式不一致！');
            }
            $datas['managing_mode'] = $this->stockInInfo['managing_mode'];
        }
        
        $wsir = new Warehouse_Stock_In_Refund();
        $srid = $wsir->create($datas);

        // 插入退货商品
        if (!empty($srid))
        {
            $wsip = new Warehouse_Stock_In_Product();
            foreach($this->productList as $_product)
            {
                $pdata = array(
                    'id' => $this->stockInInfo['id'],
                    'sid' => $_product['sid'],
                    'srid' => $srid,
                    'num' => $_product['num'],
                    'price' => $_product['price'],
                    'location' => $_product['loc'],
                );
                $wsip->insertRefund($pdata);
            }
            $this->response['srid'] = $srid;
        }
        else 
        {
            $this->response['st'] = 0;
        }

        //生成入库退货单日志
        $info = array(
            'admin_id' => $this->_uid,
            'obj_id' => $this->stockInInfo['oid'],
            'obj_type' => Conf_Admin_Log::OBJTYPE_IN_ORDER,
            'action_type' => 6,
            'params' => json_encode(array('srid' => $srid, 'id' => $this->stockInInfo['id'])),
            'wid' => $this->stockInInfo['wid'],
        );
        Admin_Common_Api::addAminLog($info);
    }
    
    // 校验退货商品的合法性
    private function _chkRefundStockin()
    {
        $canRefund = true;
        
        foreach($this->productList as $key => $_product)
        {
            $_sid = $_product['sid'];
            if ( !array_key_exists($_sid, $this->stockInProduct))
            {
                throw new Exception('退货失败：退货商品不在入库单列表里！');
            }
            
            if (empty($_product['loc']))
            {
                throw new Exception('入库单退货，货位不能为空, 请检查：sid：'.$_product['sid']);
            }
            
            $remaining = $this->stockInProduct[$_sid]['num'] - $this->stockInProduct[$_sid]['had_refund'];
            if ($_product['num'] > $remaining)
            {
                throw new Exception('超过可退数量：请检查 sid：'. $_product['sid']);
            }
            if ($_product['num'] > $this->stockInProduct[$_sid]['loc_num'])
            {
                throw new Exception('退货数量大于货位库存数量：请检查 sid：'. $_product['sid']);
            }
            
            if ($_product['loc'] != $this->stockInProduct[$_sid]['location'])
            {
                throw new Exception('退货库存货位不符，退货失败：请检查 sid：'. $_product['sid']);
            }
            
            // 检测商品的数量和单价
            if ($_product['num'] == 0 || $_product['price'] == 0)
            {
                unset($this->productList[$key]);
            }
            else
            {
                $this->refundPrice += $_product['price'] * $_product['num'];
            }
        }
        
        if (empty($this->productList)||$this->refundPrice==0)
        {
            throw new Exception('退货失败：退货单总价不能为0！');
        }
        
        // 新库，需要检查库位库存是否足够 addby:yangguoqiang 上面已经检测，故不检测了
        // 如果，可以库存人员自由输入货位，再打开该检测
        //$this->_checkLocationStock();
        
        return $canRefund;
    }
    
    // 检查库位库存
    private function _checkLocationStock()
    {
        foreach($this->productList as $p)
        {
            $_locInfo = Warehouse_Location_Api::getLocation($p['sid'], $p['loc'], $this->stockInInfo['wid']);
            $locInfo = current($_locInfo);
            
            if (empty($locInfo) || $p['num'] > $locInfo['num'])
            {
                throw new Exception('退货数量超过货架商品数量：请检查 sid：'.$p['sid']);
            }
        }
    }
    
    // 确认退货
    private function _confirmRefundStockin()
    {
        // 获取退货商品列表
        //$wsip = new Warehouse_Stock_In_Product;
        //$this->waitRefundProduct = $wsip->getProductsOfStockIn($this->id, $this->srid);
        
        if (empty($this->waitRefundProduct))
        {
            $this->response['st'] = 0;
            return;
        }
        
        foreach($this->waitRefundProduct as $one)
        {
            if ($one['num'] > $one['real_loc_num'])
            {
                throw new Exception('退货出库失败：skuid: '.$one['sid'].' 库存数量不足！');
            }
        }
        
        // 修改退货单状态
        $wsir = new Warehouse_Stock_In_Refund();
        $upData = array('step' => Conf_Stockin_Refund::HAD_REFUND, 'stockout_time'=>date('Y-m-d H:i:s'));
        $upRet = $wsir->update($this->srid, $upData);
        
        if ($upRet==0)
        {
            $this->response['st'] = 0;
            return ;
        }
        
        // 修改库存，出入库历史 (非临采)
        
        $this->_updateStock();
        
//        if ($this->stockInInfo['source']!=Conf_In_Order::SRC_TEMPORARY)
//        {
//            $this->_updateStock();
//        }
//        else
//        {
//            foreach($this->waitRefundProduct as $_p)
//            {
//                $this->refundPrice += $_p['price']*$_p['num'];
//            }
//        }
        
        // 修改财务应付
        $this->_updateFinance();
    }
    
    /**
     * 更新库存，出入库历史.
     */
    private function _updateStock()
    {
        $ws = new Warehouse_Stock();
        $wsh = new Warehouse_Stock_History();
        $wl = new Warehouse_Location();
        //$ooc = new Order_Occupied();
        
        $wid = $this->stockInInfo['wid'];
        
        // 取商品的库存，记录出入库历史使用
        $sids = Tool_Array::getFields($this->waitRefundProduct, 'sid');
        $productStockList = Tool_Array::list2Map($ws->getBulk($wid, $sids), 'sid');
        foreach ($this->waitRefundProduct as $product)
        {
            $changeData = array('num'=> 0-abs($product['num']));
            
            //更新库存数量
            $ws->update($wid, $product['sid'], array(), $changeData);
                
            //更新货位数量
            $wl->update($product['sid'], $product['location'], $wid, array(), $changeData);
            
            $historyData = array(
                'sid' => $product['sid'],
                'wid' => $wid,
                'old_num' => $productStockList[$product['sid']]['num'],
                'num' => 0-abs($product['num']),
                'iid' => $this->srid,
                'type' => Conf_Warehouse::STOCK_HISTORY_STOCKIN_REFUND,
                'suid' => $this->_uid,
            );
            $wsh->add($wid, $product['sid'], $historyData);
            
            $this->refundPrice += $product['price']* $product['num'];
            
            // 刷订单商品的占用
            //$ooc->refreshOccupiedOfSkuWhen($product['sid'], $wid);
        }
        
        //新刷新占用逻辑：addby:guoqiang/2017-06-12
        $wso = new Warehouse_Stock_Occupied();
        $wso->autoRefreshOccupied($wid, $sids);
    }
    
    /**
     * 更新财务支出，客户账户余额.
     */
    private function _updateFinance()
    {
        $type = Conf_Money_Out::STOCKIN_REFUND;
        $note = "入库单/退货单($this->id/$this->srid)";
        
        $this->stockInInfo['srid'] = $this->srid;
        
        Finance_Api::addMoneyOutHistory($this->stockInInfo, $this->refundPrice, $type, $note, $this->_uid);
        
    }
    
    
}

$app = new App();
$app->run();