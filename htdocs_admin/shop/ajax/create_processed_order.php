<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    
    private $wid;
    private $combinSid;
    private $type;
    private $location;
    private $num;
    private $partSkuinfos;
    
    // 整转零售
    private $splitCombinSkus;
    private $splitPartSkus;
    
    // 加工单id
    private $processedOrderId;
    
    protected function getPara()
    {
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->combinSid = Tool_Input::clean('r', 'combin_sid', TYPE_UINT);
        $this->type = Tool_Input::clean('r', 'type', TYPE_UINT);
        $this->location = Tool_Input::clean('r', 'location', TYPE_STR);
        $this->num = Tool_Input::clean('r', 'num', TYPE_NUM);
        $this->partSkuinfos = json_decode(Tool_Input::clean(r, 'parts', TYPE_STR), true);
        
        $this->splitCombinSkus = json_decode(Tool_Input::clean('r', 'split_combinskus', TYPE_STR), true);
        $this->splitPartSkus = json_decode(Tool_Input::clean('r', 'split_partskus', TYPE_STR), true);
    }
    
    protected function checkAuth()
    {
        parent::checkAuth('/shop/processing_products');
    }

    protected function checkPara()
    {
        $wl = new Warehouse_Location();
        
        if ($this->type == Conf_Stock::PROCESSED_ORDER_COM_SALE)
        {
            if (empty($this->wid) || empty($this->combinSid) 
                || empty($this->location) || empty($this->num) || empty($this->partSkuinfos))
            {
                throw new Exception('参数异常');
            }
            
            $chkLoc = $wl->checkLocaton($this->location, false);
            if (!$chkLoc)
            {
                throw new Exception('货位不合法, 请检查');
            }
        }
        else if ($this->type == Conf_Stock::PROCESSED_ORDER_CONVERT)
        {
            if (empty($this->wid) || empty($this->splitCombinSkus) || empty($this->splitPartSkus))
            {
                throw new Exception('参数异常');
            }
            
            foreach($this->splitPartSkus as &$one)
            {
                $chkLoc = $wl->checkLocaton($one['location'], false);
                if (!$chkLoc)
                {
                    throw new Exception('货位不合法, 请检查');
                }
            }
        }
        else
        {
            throw new Exception('加工单类型异常');
        }
        
    }

    protected function main()
    {
        $spo = new Shop_Processed_Order();
        $spop = new Shop_Processed_Order_Products();
        $info = array('create_suid' => $this->_uid);
        
        // 记录Cost-FIFO的待更新数据
        $out4FIFOCost = $in4FIFOCost = array();
        
        if ($this->type == Conf_Stock::PROCESSED_ORDER_COM_SALE)
        {
            // 核对可生产的商品，重组商品数据
            $chkRes = $spop->checkAndDistributeLocation4Combin($this->combinSid, $this->wid, $this->num, $this->partSkuinfos);
            
            if (!$chkRes)
            {
                throw new Exception('货位，库存分配异常');
            }

            // 处理成本
            $combinSidCost = 0;
            $out4FIFOCost = Shop_Cost_Api::getCostsWithSkuAndNums($this->wid, $this->partSkuinfos);
            foreach($this->partSkuinfos as $_sid => &$_pinfo)
            {
                $_pinfo['cost'] = $out4FIFOCost[$_sid]['cost'];
                $combinSidCost += $_pinfo['cost'] * abs($_pinfo['num']);
            }
            
            // 生成加工单商品
            $products = $this->partSkuinfos;
            $products[$this->combinSid] = array(
                'sid' => $this->combinSid,
                'num' => $this->num,
                'type' => Shop_Processed_Order_Products::TYPE_COMBIN,
                'location' => $this->location,
                'cost' => round($combinSidCost/$this->num),
            );
            
            $in4FIFOCost[] = $products[$this->combinSid];
        }
        else if($this->type == Conf_Stock::PROCESSED_ORDER_CONVERT)
        {
            $chkRes = $spop->checkAndDistributeLocation4Split($this->wid, $this->splitCombinSkus, $this->splitPartSkus);
            
            if (!$chkRes)
            {
                throw new Exception('货位，库存分配异常');
            }
            
            // 处理成本
            $out4FIFOCost = Shop_Cost_Api::getCostsWithSkuAndNums($this->wid, array($this->splitCombinSkus));
            $this->splitCombinSkus['cost'] = $out4FIFOCost[$this->splitCombinSkus['sid']]['cost'];
            
            if (count($this->splitPartSkus) == 1) // A -> nB (拆箱)
            {
                $this->splitPartSkus[0]['cost'] = $this->splitCombinSkus['cost']*abs($this->splitCombinSkus['num'])/$this->splitPartSkus[0]['num'];
            }
            else // A -> nB + mC +... (拆解组合商品)
            {
                // 获取库均成本
                $aveSumCost = 0;
                $aveCosts = Shop_Cost_Api::getAveCost($this->wid, Tool_Array::getFields($this->splitPartSkus, 'sid'));
                foreach($this->splitPartSkus as $splitItem)
                {
                    $aveSumCost += $splitItem['num'] * $aveCosts[$splitItem['sid']];
                }
                $aveSumCost = $aveSumCost/abs($this->splitCombinSkus['num']);
                
                foreach($this->splitPartSkus as &$spInfo)
                {
                    $spInfo['cost'] = round($aveCosts[$spInfo['sid']]*$this->splitCombinSkus['cost']/$aveSumCost);
                }
            }
            $in4FIFOCost = $this->splitPartSkus;
            
            $products = array_merge(array($this->splitCombinSkus), $this->splitPartSkus);
        }
        
        $poRet = $spo->createProcessedOrder($this->wid, $this->type, $info, $products);
        if ($poRet['errno'] != 0)
        {
            throw new Exception($poRet['errmsg']);
        }
        $this->processedOrderId = $poRet['data']['id'];
        
        // 更新库存，货位库存，出入库历史
        $historyType = $this->type==Conf_Stock::PROCESSED_ORDER_COM_SALE? 
                        Conf_Warehouse::STOCK_HISTORY_COMBIN: Conf_Warehouse::STOCK_HISTORY_SPLIT;
        Warehouse_Location_Api::parseLocationAndNum($products);
        
        $waitRefreshSids = array();
        foreach($products as $pinfo)
        {
            foreach($pinfo['_location'] as $linfo)
            {
                $_num = $pinfo['num']<0? 0-abs($linfo['num']): abs($linfo['num']);
                Warehouse_Location_Api::updateLocationStockWithHistory($pinfo['sid'], $linfo['loc'],
                                $this->wid, $_num, $historyType, $this->_uid, $poRet['data']['id']);
            }
            $waitRefreshSids[] = $pinfo['sid'];
        }
        
        //新刷新占用逻辑：addby:guoqiang/2017-06-12
        $wso = new Warehouse_Stock_Occupied();
        $wso->autoRefreshOccupied($this->wid, $waitRefreshSids);
        
        // Cost-FIFO
        $outBillInfo = array('out_id'=>$this->processedOrderId, 'out_type'=>$historyType);
        $inBillInfo = array('in_id'=>  $this->processedOrderId, 'in_type'=>$historyType);
        // Cost-FIFO 出队
        foreach($out4FIFOCost as $_sid => $fifoCosts)   
        {
            if (empty($fifoCosts['_cost_fifo'])) continue;

            Shop_Cost_Api::dequeue4FifoCost($_sid, $this->wid, $outBillInfo, $fifoCosts['_cost_fifo']);
        }
        // Cost-FIFO 入队
        Shop_Cost_Api::enqueueSids4FifoCost($this->wid, $inBillInfo, $in4FIFOCost);
    }
    
    protected function outputBody()
    {
        $response = new Response_Ajax();
		$response->setContent(array('st'=>0, 'data'=>array('id'=>  $this->processedOrderId)));
		$response->send();
        
		exit;
    }
    
}

$app = new App();
$app->run();