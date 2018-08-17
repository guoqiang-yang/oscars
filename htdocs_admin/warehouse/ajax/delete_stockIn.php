<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    const DEL_TYPE_STOCKIN = 'del_stockin';
    const DEL_TYPE_PRODUCT = 'del_product';
    
    private $id;
    private $sid;
    private $optype;
    
    private $stockInInfo;
    private $stockInProduct;
    
    private $waitDelProducts;
    private $financeReduce = 0;
    
    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->optype = Tool_Input::clean('r', 'optype', TYPE_STR);
        
        // 入库单详情
        $this->stockInInfo = Warehouse_Api::getStockInDetail($this->id);
        $this->stockInProduct = $this->stockInInfo['products'];
    }
    
    protected function checkPara()
    {
        throw new Exception('功能下线，有问题请联系技术部门！');
        
        if (empty($this->id) || ($this->optype==self::DEL_TYPE_PRODUCT&&empty($this->sid))
            || ($this->optype!=self::DEL_TYPE_PRODUCT&&$this->optype!=self::DEL_TYPE_STOCKIN))
        {
            throw new Exception('参数错误');
        }
        
        if ( ($this->optype==self::DEL_TYPE_PRODUCT && !array_key_exists($this->sid, $this->stockInProduct))
             || ($this->optype==self::DEL_TYPE_STOCKIN && empty($this->stockInProduct)))
        {
            throw new Exception('入库单商品异常，联系管理员修复！');
        }
        
        if ($this->optype == self::DEL_TYPE_PRODUCT && !empty($this->stockInProduct[$this->sid]['location']))
        {
            throw new Exception('商品已经上架，不能再入库单中删除！');
        }
        
        if ($this->optype == self::DEL_TYPE_STOCKIN && $this->stockInInfo['info']['step']!=Conf_Stock_In::STEP_STOCKIN)
        {
            throw new Exception('入库单不能删除！');
        }
        
        if ($this->stockInInfo['info']['paid'] != 0)
        {
            throw new Exception('入库单已兑账/付款，不能删除！！');
        }
        
        if ($this->_uid != 1029)
        {
            if (substr($this->stockInInfo['info']['ctime'], 0, 10)!=date('Y-m-d'))
            {
                throw new Exception('应财务需求，只能删除当天的入库单！如有问题，请联系技术！');
            }
        }
    }
    
    protected function main()
    {
        // 删除入库单商品 && 入库单只有一个商品，则直接删除该入库单
        if ($this->optype==self::DEL_TYPE_PRODUCT && count($this->stockInProduct)==1)
        {
            $this->optype = self::DEL_TYPE_STOCKIN;
        }
        
        $this->waitDelProducts = $this->optype==self::DEL_TYPE_STOCKIN? $this->stockInProduct:
                                    array($this->stockInProduct[$this->sid]);
        
        // 删除入库单，入库商品列表
        $this->_delStockIn();
        
        // 非临采入库
        if ($this->stockInInfo['info']['source'] != Conf_In_Order::SRC_TEMPORARY)
        {
            // 更新虚拟货位上商品的数量
            $this->_delNumFromVirtualLocation();

            // 删除库存，库存历史
            $this->_updateStock();
        }
        else
        {
            // 计算删除商品的总价钱，更新财务流水使用
            foreach($this->waitDelProducts as $_p)
            {
                $this->financeReduce += $_p['price']*$_p['num'];
            }
        }
        
        // 更新财务支出，客户账户余额
        $this->_updateFinance();
        
        // 更新在途数量
        Warehouse_Security_Stock_Api::updateWaitNumByInorderId(
                $this->stockInInfo['info']['oid'], $this->stockInInfo['info']['wid']);
        
    }
    
    protected function outputBody()
    {
        $result = array('st'=>1, 'optype'=> $this->optype);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
    }
    
    /**
     * 删除入库单，入库商品列表，更新采购单状态.
     */
    private function _delStockIn()
    {
        $wsip = new Warehouse_Stock_In_Product();
        $wsi = new Warehouse_Stock_In();
        $wio = new Warehouse_In_Order();
        
        if ($this->optype == self::DEL_TYPE_STOCKIN)    // 删除入库单
        {
            $wsi->delete($this->id);

            //删除入库单日志
            $info = array(
                'admin_id' => $this->_uid,
                'obj_id' => $this->stockInInfo['info']['oid'],
                'obj_type' => Conf_Admin_Log::OBJTYPE_IN_ORDER,
                'action_type' => 4,
                'params' => json_encode(array('id' => $this->id)),
                'wid' => $this->stockInInfo['info']['wid'],
            );
            Admin_Common_Api::addAminLog($info);
        }
        else if ($this->optype == self::DEL_TYPE_PRODUCT) //删除入库商品，修改入库单价钱
        {
            $p = $this->stockInProduct[$this->sid];
            $change = array('price'=>0-abs($p['price']*$p['num']));
            $wsi->update($this->id, array(), $change);
        }
        
        // 删除商品
        $wsip->delete($this->id, $this->sid);

        //删除入库单商品日志
        if ($this->optype == self::DEL_TYPE_PRODUCT)
        {
            $info = array(
                'admin_id' => $this->_uid,
                'obj_id' => $this->stockInInfo['info']['oid'],
                'obj_type' => Conf_Admin_Log::OBJTYPE_IN_ORDER,
                'action_type' => 10,
                'params' => json_encode(array('sid' => $this->sid, 'id' => $this->id)),
                'wid' => $this->stockInInfo['info']['wid'],
            );
            Admin_Common_Api::addAminLog($info);
        }
        
        // 采购单状态设置为【待收货】
        if ($this->optype==self::DEL_TYPE_PRODUCT)
        {
            $upOrderData = array('step'=>Conf_In_Order::ORDER_STEP_PART_RECEIVED);
        }
        else
        {
            $_stockInList = $wsi->getListOfOrder($this->stockInInfo['info']['oid']);
            if (count($_stockInList) == 0)  //采购单只对应一个入库单，并且被删除
            {
                $upOrderData = array('step'=>Conf_In_Order::ORDER_STEP_SURE);
            }
            else
            {
                $upOrderData = array('step'=>Conf_In_Order::ORDER_STEP_PART_RECEIVED);
            }
        }
        
        $wio->update($this->stockInInfo['info']['oid'], $upOrderData);
        
        // 处理Cost-FIFO：直接删除：临采-删除history；普采-没上架没销售，删除fifo-cost
        $sfc = new Shop_Fifo_Cost();
        $fifoDealSid = $this->optype==self::DEL_TYPE_STOCKIN? 0: $this->sid;
        if ($this->stockInInfo['info']['source'] != Conf_In_Order::SRC_TEMPORARY)   // 非临采
        {
            $sfc->deleteFifoCostByBatch($this->id, Conf_Warehouse::STOCK_HISTORY_IN, $fifoDealSid);
        }
        else if ($this->stockInInfo['info']['source'] == Conf_In_Order::SRC_TEMPORARY)
        {
            $sfc->deleteFifoHistoryByBatch($this->id, Conf_Warehouse::STOCK_HISTORY_IN, $fifoDealSid);
        }
    }

    /**
     * 更新虚拟货位上商品的数量.
     */
    private function _delNumFromVirtualLocation()
    {
        $wid = $this->stockInInfo['info']['wid'];
        
        if (!Conf_Warehouse::isUpgradeWarehouse($wid))
        {
            return;
        }
        
        $wl = new Warehouse_Location();
        
        foreach($this->waitDelProducts as $skuinfo)
        {
            $location = Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_STOCK_IN]['flag'];
            $chgData = array('num'=>(0-abs($skuinfo['num'])));
            $wl->update($skuinfo['sid'], $location, $wid, array(), $chgData);
        }
    }

    /**
     * 删除库存，库存历史.
     */
    private function _updateStock()
    {
        $ws = new Warehouse_Stock();
        $wsh = new Warehouse_Stock_History();
        
        $wid = $this->stockInInfo['info']['wid'];
        
        // 取商品的库存，记录出入库历史使用
        $sids = Tool_Array::getFields($this->waitDelProducts, 'sid');
        $productStockList = Tool_Array::list2Map($ws->getBulk($wid, $sids), 'sid');
        
        foreach ($this->waitDelProducts as $product)
        {
            $changeData = array('num'=> 0-abs($product['num']));
            $ws->update($wid, $product['sid'], array(), $changeData);
            
            $historyData = array(
                'sid' => $product['sid'],
                'wid' => $wid,
                'old_num' => $productStockList[$product['sid']]['num'],
                'num' => 0-abs($product['num']),
                'iid' => $this->id,
                'type' => Conf_Warehouse::STOCK_HISTORY_STOCKIN_DEL,
                'suid' => $this->_uid,
            );
            $wsh->add($wid, $product['sid'], $historyData);
            
            $this->financeReduce += $product['price']* $product['num'];
        }
    }
    
    /**
     * 更新财务支出，客户账户余额.
     */
    private function _updateFinance()
    {
        $supplier = $this->stockInInfo['info']['sid'];
        $type = Conf_Money_Out::PURCHASE_IN_STORE;
        $isDel = $this->optype==self::DEL_TYPE_STOCKIN? true: false;
        $priceDiff = 0-abs($this->financeReduce);
        
        Finance_Api::modifyFinanceOutPrice($supplier, $this->id, $type, 0, $priceDiff, $isDel);
    }
}

$app = new App();
$app->run();