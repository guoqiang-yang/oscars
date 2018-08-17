<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $sid;
    private $price;
    private $num;
    private $source;
    private $productInorder;
    private $orderInfo;
    private $stockInList;
    private $financeChg = array();
    private $IsPaidstockIn = false;
    private $sridInfos;
    
    private $lastStockInTime = '';
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->price = intval(strval(Tool_Input::clean('r', 'price', TYPE_NUM))*100);
        $this->num = Tool_Input::clean('r', 'num', TYPE_UINT);
        $this->source = Tool_Input::clean('r', 'source', TYPE_UINT);
        
        $this->orderInfo = Warehouse_Api::getOrderInfo($this->oid);
        
        // 采购单对应的入库单
        $res = Warehouse_Api::getStockInLists(array('oid' => $this->oid));
		$this->stockInList = $res['list'];
        
        foreach($this->stockInList as $_stockIn)
        {
            $this->IsPaidstockIn = $this->IsPaidstockIn || $_stockIn['paid'];
            
            $stockInTime = substr($_stockIn['ctime'], 0, 10);
            $this->lastStockInTime = $stockInTime>$this->lastStockInTime? $stockInTime: $this->lastStockInTime;
        }
    }
    
    protected function checkPara()
    {
        if (empty($this->oid) || empty($this->sid))
        {
            throw new Exception('参数错误！');
        }
        if (empty($this->source))
        {
            throw new Exception('采购单类型异常，请联系技术人员!!');
        }
        
        if ($this->IsPaidstockIn)
        {
            throw new Exception('入库单已经付款/已兑账，不能在修改！');
        }
        
        if (!array_key_exists($this->source, $this->orderInfo['products']) ||
            !array_key_exists($this->sid, $this->orderInfo['products'][$this->source]))
        {
            throw new Exception('修改商品不在该采购单中！');
        }
        
        $this->productInorder = $this->orderInfo['products'][$this->source][$this->sid];
        
        if ($this->num < $this->productInorder['_stock_in'])
        {
            throw new Exception('商品的采购数量不能小于入库数量！');
        }
        
        // 临采采购单，不能修改商品的数量
        if ($this->orderInfo['info']['source'] == Conf_In_Order::SRC_TEMPORARY)
        {
            if ($this->num != $this->productInorder['num'])
            {
                throw new Exception('【临采】不能修改商品的数量！');
            }
        }
        
        if ($this->_uid != 1029 && $this->_uid!=1336)
        {
            foreach($this->orderInfo['products'] as $pinfosBySource)
            {
                foreach($pinfosBySource as $sid => $pinfo)
                {
                    //已入库 && 入库时间不是今天，不允许修改
                    if ($sid==$this->sid && $pinfo['_stock_in']!=0 && $this->lastStockInTime!=date('Y-m-d'))
                    {
                        throw new Exception('应财务需求：采购商品已经入库不能修改！如需调整，找技术咨询！');
                    }
                }
            }
        }
    }
    
    protected function checkAuth()
    {
        parent::checkAuth();
    }
    
    protected function main()
    {
        // 不需要更新，直接返回
        if ($this->num==$this->productInorder['num']
            && $this->price==$this->productInorder['price'])
        {
            return;
        }
        if ($this->price!=$this->productInorder['price'] && $this->_uid!=1029)
        {
            throw new Exception('不能修改采购单商品单价');
        }
        
        if(Conf_Warehouse::isAgentWid($this->orderInfo['info']['wid']))
        {
            if (!empty($this->stockInList))
            {
                throw new Exception('经销商采购单，已经入库，不能修改');
            }
            
            if ($this->num != $this->productInorder['num'])
            {
                $modifyProduct = array(
                    array(
                        'sid' => $this->sid,
                        'num' => $this->num-$this->productInorder['num'],
                        'sale_price' => $this->productInorder['sale_price'],
                    ),
                );
                Agent_Api::canDistributeGoods4Agent($this->orderInfo['info']['wid'], $this->oid, Conf_Agent::Agent_Type_Stock_In, $modifyProduct, 'sale_price');
               
//                $aa = new Agent_Agent();
//                $agentInfo = $aa->getVaildAgentByWid($this->orderInfo['info']['wid']);
//                if (empty($agentInfo))
//                {
//                    throw new Exception('仓库：#'.$this->orderInfo['info']['wid']. ' 经销商不存在');
//                }
//                
//                $productsSalesPrices = 0;
//                foreach($this->orderInfo['products'] as $productWithSource)
//                {
//                    foreach($productWithSource as $_p)
//                    {
//                        $productsSalesPrices += $_p['num']*$_p['sale_price'];
//                    }
//                }
//                $productsSalesPrices += ($this->num-$this->productInorder['num'])*$this->productInorder['sale_price'];
//                if ($productsSalesPrices > $agentInfo['account_balance'])
//                {
//                    throw new Exception('经销商余额不足，创建采购单失败！');
//                }
            }
        }
        
        
        
        // 更新 采购数据 in_order
        $this->_updateInorder();
        
        // 更新 入库数据 财务数据 stock_in finance
        if ($this->price!=$this->productInorder['price']
            && $this->productInorder['_stock_in'] > 0
            && !empty($this->stockInList) )
        {
            $this->_updateStockIn();
            $this->_updateFinance();
        }
        
        //刷新在途数量
        Warehouse_Security_Stock_Api::updateWaitNumByWidSid($this->orderInfo['info']['wid'], array($this->sid));

        $info = array(
            'admin_id' => $this->_uid,
            'obj_id' => $this->oid,
            'obj_type' => Conf_Admin_Log::OBJTYPE_IN_ORDER,
            'action_type' => 8,
            'params' => json_encode(array('sid' => $this->sid, 'from_price' => $this->orderInfo['products'][1][$this->sid]['price']/10/10,
            'to_price' => $this->price/10/10, 'from_num' => $this->orderInfo['products'][1][$this->sid]['num'], 'to_num' => $this->num)),
            'wid' => $this->orderInfo['info']['wid'],
        );
        Admin_Common_Api::addAminLog($info);
    }
    
    protected function outputBody()
    {
        $result = array('st'=>1, 'oid'=>$this->oid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
    }
    
    private function _updateInorder()
    {
        $product = array(
            'num' => $this->num,
            'price' => $this->price,
        );
        
        // 采购单价钱
        $moreChgPrice = 0;
        
        // 更新采购单商品
        $wiop = new Warehouse_In_Order_Product();
        $wiop->updateProduct($this->oid, $this->sid, $product, $this->source);
        
        // 更新了采购商品的价格，同时更新库存中的采购价
        if ($this->price != $this->productInorder['price'])
        {
//            $ss = new Warehouse_Stock();
//            $wid = $this->orderInfo['info']['wid'];
//            $stockInfo = $ss->get($wid, $this->sid);
//
//            if ($this->price != $stockInfo['purchase_price'])
//            {
//                $ws = new Warehouse_Stock();
//                $ws->update($wid, $this->sid, array('purchase_price'=>$this->price), array());
//            }
            
            // 如果修改了价钱，修改其他采购类型的商品价钱
            foreach($this->orderInfo['products'] as $_source => $_products)
            {
                if ($_source!=$this->source && array_key_exists($this->sid, $_products))
                {
                    $upProduct = array(
                        'num' => $_products[$this->sid]['num'],
                        'price' => $this->price,
                    );
                    $wiop->updateProduct($this->oid, $this->sid, $upProduct, $_source);
                    $moreChgPrice += ($this->price-$_products[$this->sid]['price'])*$_products[$this->sid]['num'];
                }
            }
            
        }
        
        // 更新采购单
        $priceDiff = $moreChgPrice + $this->num * $this->price - $this->productInorder['num'] * $this->productInorder['price'];
        $upOrderData['price'] = $this->orderInfo['info']['price'] + $priceDiff;
        
        if ($this->num == 0)
        {
            $upOrderData['product_num'] = $this->orderInfo['info']['product_num']-1;
        }
        
        // 如果修改了数量，更新采购单状态为 待收货 状态
        if ($this->num > $this->productInorder['num'])
        {
            if (!empty($this->stockInList))
            {
                $upOrderData['step'] = Conf_In_Order::ORDER_STEP_PART_RECEIVED;
            }
        }
        
        $wio = new Warehouse_In_Order();
        $wio->update($this->oid, $upOrderData);
    }
    
    
    private function _updateStockIn()
    {
        // 获取入库单商品列表
        $this->stockInList = Tool_Array::list2Map($this->stockInList, 'id');
        
        $stockInIds = Tool_Array::getFields($this->stockInList, 'id');
        $wsip = new Warehouse_Stock_In_Product();
        $wsp = new Warehouse_Stock_In();
        $stockInProducts = $wsip->getProductsByIds($stockInIds);
        
        foreach($stockInProducts as $product)
        {
            if ($product['sid'] == $this->sid && $product['srid'] == 0)
            {
                $priceDiff = $this->price - $product['price'];
                
                // 更新入库单商品表单价
                $pinfo = array(
                    'price' => $priceDiff,
                );
                $wsip->updateProduct($product['id'], $this->sid, array(), $pinfo);
                
                // 更新入库单数据
                $info = array(
                    'price' => $this->stockInList[$product['id']]['price'] + $priceDiff*$product['num'],
                );
                $wsp->update($product['id'], $info);
                
                $this->financeChg[$product['id']] = $priceDiff * $product['num'];
            }

            if ($product['sid'] == $this->sid && $product['srid'] != 0)
            {
                $this->sridInfos[$product['srid']] = $priceDiff * $product['num'];
            }
        }
        
    }
    
    private function _updateFinance()
    {
        $supplier = $this->orderInfo['info']['sid'];

        if (!empty($this->financeChg))
        {
            $type = Conf_Money_Out::PURCHASE_IN_STORE;

            foreach ($this->financeChg as $stockId => $priceDiff) {
                Finance_Api::modifyFinanceOutPrice($supplier, $stockId, $type, 0, $priceDiff);
            }
        }

        if (!empty($this->sridInfos))
        {
            $sridType = Conf_Money_Out::STOCKIN_REFUND;
            foreach ($this->sridInfos as $srid => $sridPriceDiff)
            {
                Finance_Api::modifyFinanceOutPrice($supplier, $srid, $sridType, 0, 0 - $sridPriceDiff);
            }

        }
    }
}

$app = new App();
$app->run();