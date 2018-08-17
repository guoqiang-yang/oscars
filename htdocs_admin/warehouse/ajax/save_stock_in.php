<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $id;
    private $oid;
    private $source;
	private $productStr;
	private $stockIn = array();
	private $products = array();
    
    private $inorderInfo = array();
    private $productsPurchasePrices = 0;//入库单商品总采购价
    private $productsSalesPrices = 0;   //商品的销售价钱

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->source = Tool_Input::clean('r', 'source', TYPE_UINT);
		$this->productStr = Tool_Input::clean('r', 'product_str', TYPE_STR);
        
        if (!empty($this->id))
        {
            throw new Exception('操作异常：该入口关闭，请联系（杨国强 18210336687）');
        }
	}

	protected function checkPara()
	{
        //锁库判断
        $lockedRet = Conf_Warehouse::isLockedWarehouse($this->stockIn['wid']);
        if ($lockedRet['st'])
        {
            throw new Exception($lockedRet['msg']);
        }
        
		if (empty($this->oid))
		{
			throw new Exception('order:empty order id');
		}
	}
    
	protected function main()
	{
        //检测&&生成入库单的信息和入库单商品
        $this->_chkAndGenStockInInfo();

        if ($this->inorderInfo['info']['source'] == Conf_In_Order::SRC_COMMON && $this->inorderInfo['info']['payment_type'] == Conf_Stock::PAYMENT_TRANSFER && $this->inorderInfo['info']['in_order_type'] == Conf_In_Order::IN_ORDER_TYPE_ORDER)
        {
            $stockDao = new Data_Dao('t_stock_in');
            $stockInfo = $stockDao->getListWhere(sprintf(' oid=%d ', $this->oid));
            if (!empty($stockInfo))
            {
                throw new Exception('该单为普采、财务结订单，只能入库一次！');
            }
        }

        //新建入库单
        $this->id = Warehouse_Api::addStockIn($this->_uid, $this->stockIn['sid'], $this->stockIn, $this->products, $this->inorderInfo['info']['in_order_type']);

        //生成入库单日志
        $info = array(
            'admin_id' => $this->_uid,
            'obj_id' => $this->stockIn['oid'],
            'obj_type' => Conf_Admin_Log::OBJTYPE_IN_ORDER,
            'action_type' => 3,
            'params' => json_encode(array('id' => $this->id)),
            'wid' => $this->stockIn['wid'],
        );
        Admin_Common_Api::addAminLog($info);

        // 获取入库单详情
        $stockInInfo = Warehouse_Api::getStockInInfo($this->id);
        $stockInInfo['wid'] = $stockInInfo['wid'] == 0 ? $this->stockIn['wid']: $stockInInfo['wid'];

        if ($this->inorderInfo['info']['in_order_type'] != Conf_In_Order::IN_ORDER_TYPE_GIFT)
        {
            // 写付款单记录表 - 入库
            $type = Conf_Money_Out::PURCHASE_IN_STORE;
            $note = '采购单ID：'. $stockInInfo['oid'];
            Finance_Api::addMoneyOutHistory($stockInInfo, $stockInInfo['price'], $type, $note, $this->_uid, 0);
        }

        // 经销商的采购入库，记录财务流水
        if (Conf_Warehouse::isAgentWid($stockInInfo['wid']))
        {
            $aa = new Agent_Agent();
            $agentInfo = $aa->getVaildAgentByWid($stockInInfo['wid']);
            if (empty($agentInfo))
            {
                throw new Exception('仓库：#'.$stockInInfo['wid']. ' 经销商不存在');
            }
            Agent_Api::addAgentAmountHistoryByAid($agentInfo['aid'], Conf_Agent::Agent_Type_Stock_In, 0-$this->productsSalesPrices, $this->_uid, 0, $this->id);
        }
        
        // 入库采购价写入FIFO-Queue
        $this->_insertCost2FifoQueue();
	}

	protected function outputPage()
	{
		$result = array('id' => $this->id, 'oid' => $this->stockIn['oid']);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
        
		exit;
	}
    
    
	private function parseProducts($str)
	{
		// 解析字符串
		$products = array();
		$items = array_filter(explode(',', $str));
        
		foreach ($items as $item)
		{
			list($sid, $num, $price) = explode(":", $item);
            
            if ($num <= 0) continue;
            
			$products[] = array('sid' => $sid, 'num' => $num, 'id' => $this->id, 'price' => 100*$price);
		}
        
		if (empty($products))
		{
			return array();
		}

		return $products;
	}
    
    private function _chkAndGenStockInInfo()
    {
        $this->inorderInfo = Warehouse_Api::getOrderInfo($this->oid);
        
        //## 入库单商品
        $this->products = $this->parseProducts($this->productStr);
        $stockinSids = Tool_Array::getFields($this->products, 'sid');
        $inorderSids = array_keys($this->inorderInfo['products'][$this->source]);
        $diffSids = array_diff($stockinSids, $inorderSids);

        if (empty($this->products) || !empty($diffSids))
        {
            throw new Exception('入库商品异常，请核实！');
        }
        
        // 补充入库单的销售价格
        $wiop = new Warehouse_In_Order_Product();
        $inOrderProducts = Tool_Array::list2Map($wiop->getProductsOfOrder($this->oid, array('sid', 'sale_price')), 'sid');
        foreach ($this->products as &$_product)
        {
            $_product['sale_price'] = $inOrderProducts[$_product['sid']]['sale_price'];
            $this->productsSalesPrices += $_product['sale_price'] * $_product['num'];
            $this->productsPurchasePrices += $_product['price'] * $_product['num'];
        }
        
        //## 入库单数据
        $this->stockIn = array(
            'oid' => $this->oid,
            'step' => Conf_Stock_In::STEP_STOCKIN,
            'sid' => $this->inorderInfo['info']['sid'],
            'wid' => $this->inorderInfo['info']['wid'],
            'payment_type' => $this->inorderInfo['info']['payment_type'],
            'source' => $this->source,
            'note' => Tool_Input::clean('r', 'note', TYPE_STR),
		);
        // 礼品入库
        if ($this->inorderInfo['info']['in_order_type'] == Conf_In_Order::IN_ORDER_TYPE_GIFT)
        {
            $this->stockIn['paid'] = Conf_Stock_In::HAD_PAID;
            $this->stockIn['price'] = 0;
        }
        //现款后货 入库时判断 采购单实付支付，如果支付则入库单支付完成
//        if ($this->stockIn['payment_type'] == Conf_Stock::PAYMENT_MONEY_FIRST &&
//            $this->inorderInfo['info']['paid'] == Conf_Stock_In::HAD_PAID)
//        {
//            $this->stockIn['paid'] = Conf_Stock_In::HAD_PAID;
//            $this->stockIn['real_amount'] = $this->productsPurchasePrices;
//        }
        
    }
    
    /**
     * 入库采购价写入FIFO-Queue.
     * 
     * 非赠品入库，采购价记入未FIFO的成本
     * 赠品入库，使用库均成本记入FIFO的成本
     * 临采入库，【不写】入FIFO的成本
     */
    private function _insertCost2FifoQueue()
    {
        //临采入库，不写FIFO-Queue
        if ($this->source == Conf_In_Order::SRC_TEMPORARY) return;
        
        //构建FIFO入库数据
        $fifoQueueDatas = array();
        $billInfo = array('in_id'=>$this->id, 'in_type'=>Conf_Warehouse::STOCK_HISTORY_IN);
        
        // 补充入库商品的采购价
        if ($this->inorderInfo['info']['in_order_type'] != Conf_In_Order::IN_ORDER_TYPE_GIFT)   //非赠品入库
        {
            //查询附加成本
            $ws = new Warehouse_Stock();
            $_stockInfos = $ws->getBulk($this->stockIn['wid'], Tool_Array::getFields($this->products, 'sid'));
            $stockInfos = Tool_Array::list2Map($_stockInfos, 'sid', 'fring_cost');
            foreach ($this->products as &$item)
            {
                $cost = $this->inorderInfo['products'][$this->source][$item['sid']]['price'];
                $cost += intval($stockInfos[$item['sid']]);
                
                $fifoQueueDatas[] = $this->_genFifoQueueData($item, $cost);
            }
        }
        else //赠品入库
        {
            $aveCosts = Shop_Cost_Api::getAveCost($this->stockIn['wid'], Tool_Array::getFields($this->products, 'sid'));
            
            foreach ($this->products as &$item)
            {
                $aveCost = $aveCosts[$item['sid']];
                $cost = $aveCost>0? $aveCost: $this->inorderInfo['products'][$this->source][$item['sid']]['price'];
                
                $fifoQueueDatas[] = $this->_genFifoQueueData($item, $cost);
            }
        }
        
        Shop_Cost_Api::enqueueSids4FifoCost($this->stockIn['wid'], $billInfo, $fifoQueueDatas);
    }
    
    private function _genFifoQueueData($product, $cost)
    {
        return array(
            'sid' => $product['sid'],
            'num' => $product['num'],
            'cost' => $cost,
        );
    }
}

$app = new App('pri');
$app->run();

