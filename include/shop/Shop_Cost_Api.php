<?php
/**
 * 商品成本.
 * 
 */

class Shop_Cost_Api extends Base_Api
{
    
    const FIFO_SWITCH_OFF = false;    
    /**
     * 队列成本数据.
     * 
     * @param int $sid
     * @param int $wid
     */
    public static function getCostInFifoQueue($sid, $wid)
    {
        if (empty($sid) || empty($wid)) return array();
        
        $sfc = new Shop_Fifo_Cost();
        
        $costs = $sfc->getVaildCostOfFifoQueue($sid, $wid);
        
        return $costs;
    }
    
    /**
     * 队列成本的使用历史.
     * 
     * @param int $sid
     * @param int $wid
     * @param int $start
     * @param int $num
     */
    public static function getFifoCostHistory($sid, $wid, $start=0, $num=20)
    {
        if (empty($sid) || empty($wid)) return array('data'=>array(), 'total'=>0);
        
        $sfc = new Shop_Fifo_Cost();
        
        $datas = $sfc->getCostOfFifoHistory($sid, $wid, $start, $num);
        $total = $sfc->getTotalOfFifoHistory($sid, $wid);
        
        return array('data' => $datas, 'total' => $total);
    }
    
    /**
     * 批量插入fifo queue数据（数量，成本）.
     * 
     * @param int $wid
     * @param array $billData {in_id, in_type}
     * @param array $productList   {sid, num, cost}
     */
    public static function enqueueSids4FifoCost($wid, $billData, $productList)
    {
        if (self::FIFO_SWITCH_OFF)
        {
            return;
        }
        
        $sfc = new Shop_Fifo_Cost();
        
        // check
        if (empty($wid)) 
            throw new Exception('缺少仓库信息');
        if (empty($billData)||!isset($billData['in_id'])||!isset($billData['in_type']))
            throw new Exception('单据信息异常');

        $fifoDatas = array();
        foreach($productList as $item)
        {
            if (empty($item['sid']) || empty($item['num']) || !isset($item['cost']))
                throw new Exception('入队信息缺失');
            
            $fifoDatas[] = array(
                'sid' => $item['sid'],
                'num' => $item['num'],
                'cost' => $item['cost'],
                'in_id' => $billData['in_id'],
                'in_type' => $billData['in_type'],
            );
        }
        
        // add
        foreach($fifoDatas as $fItem)
        {
            $sfc->insert($fItem['sid'], $wid, $fItem);
        }
        
        return true;
    }
    
    /**
     * 出库相关操作，从fifo queue消减数量和其对应的成本.
     * 
     * @param int $sid  sku_id
     * @param int $wid  仓库id
     * @param array $billDatas  出库相关单据信息 {out_id, out_type}
     * @param array $fifoCosts  self::getCostsWithSkuAndNums返回结果； value = _cost_fifo字段内容
     */
    public static function dequeue4FifoCost($sid, $wid, $billDatas, $fifoCosts)
    {
        if (self::FIFO_SWITCH_OFF)
        {
            return;
        }
        
        
        $sfc = new Shop_Fifo_Cost();
        
        if (!isset($billDatas['out_id']) || !isset($billDatas['out_type']))
        {
            throw new Exception('出队缺少单据信息');
        }
      
        if (empty($fifoCosts)) return;
        
        foreach ($fifoCosts as $fifoInfo)
        {   
            //fifo成本队列-出队
            $sfc->update4StockOut($fifoInfo['id'], $fifoInfo['num']);

            //写fifo历史
            $historyDatas = array(
                'num' => $fifoInfo['num'],
                'cost' => $fifoInfo['cost'],
                'in_id' => $fifoInfo['in_id'],
                'in_type' => $fifoInfo['in_type'],
                'out_id' => $billDatas['out_id'],
                'out_type' => $billDatas['out_type'],
            );
            $sfc->insertHistory($sid, $wid, $historyDatas);
        }
        
        return true;
    }
    
    /**
     * 批量获取sku成本：根据skus和【数量】获取对应的成本.
     * 
     * @rule
     *      首先：从队列t_fifo_cost中取，并返回分配后的结果
     *      然后：如果队列t_fifo_cost为空，从t_stock上获取sid在wid的成本
     *      最后：如果t_stock上为0，取t_product上sid在wid所在城市的成本
     * 
     * @param int $wid
     * @param array $skuList    {sid, num}
     */
    public static function getCostsWithSkuAndNums($wid, $skuList)
    {
        if (empty($wid) || empty($skuList)) return false;
        
        foreach($skuList as $item)
        {
            if (empty($item['sid']) || empty($item['num'])) return false;
        }
        
        $leftSids = $leftFromStock = $leftFromProduct = array();
        
        //1：从t_fifo_cost获取队列记录，并计算成本
        $skuCosts = self::getCostFromFIFOQueue($wid, $skuList, $leftSids);
        
        //2：如果队列t_fifo_cost为空，从t_stock上获取sid在wid的成本
        $costFromStock = self::getCostsFromStock($wid, $leftSids, $leftFromStock);
        
        //3：如果t_stock上为0，取t_product上sid在wid所在城市的成本
        $costFromProduct = self::getCostsFromProduct($wid, $leftFromStock, $leftFromProduct);
        
        foreach ($costFromStock as $_sid => $_stockCost)
        {
            $skuCosts[$_sid]['cost'] = $_stockCost;
        }
        
        foreach($costFromProduct as $_sid => $_productCost)
        {
            $skuCosts[$_sid]['cost'] = $_productCost;
        }
        
        return $skuCosts;
    }
    
    /**
     * 获取库均成本.
     * 
     * @param int $wid
     * @param array $sids
     */
    public static function getAveCost($wid, $sids)
    {
        if (empty($wid) || empty($sids)) return false;
        
        $skuCosts = array();
        $leftSids = $leftFromStock = $leftFromProduct = array();
        
        //1. fifo队列的库均成本
        if (!self::FIFO_SWITCH_OFF)
        {
            $sfc = new Shop_Fifo_Cost();
            $fifoCost = $sfc->getAveCost($wid, $sids);

            foreach ($sids as $_sid)
            {
                if (array_key_exists($_sid, $fifoCost) && $fifoCost[$_sid]>0)
                {
                    $skuCosts[$_sid] = $fifoCost[$_sid];
                }
                else
                {
                    $skuCosts[$_sid] = 0;    //给默认值
                    $leftSids[] = $_sid;
                }
            }
        }
        else
        {
            $leftSids = $sids;
        }
        
        //2：如果队列t_fifo_cost为空，从t_stock上获取sid在wid的成本
        $costFromStock = self::getCostsFromStock($wid, $leftSids, $leftFromStock);
        
        //3：如果t_stock上为0，取t_product上sid在wid所在城市的成本
        $costFromProduct = self::getCostsFromProduct($wid, $leftFromStock, $leftFromProduct);
        
        foreach ($costFromStock as $_sid => $_stockCost)
        {
            $skuCosts[$_sid] = $_stockCost;
        }
        
        foreach($costFromProduct as $_sid => $_productCost)
        {
            $skuCosts[$_sid] = $_productCost;
        }
        
        return $skuCosts;
    }
    
    /**
     * 通过t_stock/t_product表获取成本，不取库均成本.
     * 
     * @param int $wid
     * @param array $sids
     */
    public static function getSimpleCost($wid, $sids)
    {
        if (empty($wid) || empty($sids)) return false;
        
        $skuCosts = array();
        $leftFromStock = $leftFromProduct = array();
        
        //1：从t_stock上获取sid在wid的成本
        $costFromStock = self::getCostsFromStock($wid, $sids, $leftFromStock);
        
        //2：如果t_stock上为0，取t_product上sid在wid所在城市的成本
        $costFromProduct = self::getCostsFromProduct($wid, $leftFromStock, $leftFromProduct);
        
        foreach ($costFromStock as $_sid => $_stockCost)
        {
            $skuCosts[$_sid] = $_stockCost;
        }
        
        foreach($costFromProduct as $_sid => $_productCost)
        {
            $skuCosts[$_sid] = $_productCost;
        }
        
        return $skuCosts;
    }
    
    /**
     * 批量：从先进先出队列获取sku的成本.
     * 
     * @param type $wid         仓库id
     * @param type $skuList     待获取成本的sku列表
     * @param array $leftSids   未获取到cost的sku
     */
    public static function getCostFromFIFOQueue($wid, $skuList, &$leftSids=array())
    {
        if (self::FIFO_SWITCH_OFF)
        {
            $leftSids = Tool_Array::getFields($skuList, 'sid');
            return;
        }
        
        $skuCosts = array();
        $sfc = new Shop_Fifo_Cost();
        $ws = new Warehouse_Stock();
        
        $sids = Tool_Array::getFields($skuList, 'sid');
        
        //获取外包商的skuid, 并过滤掉，外包商的商品成本统一从t_stock上获取
        $outsourcerWhere = sprintf('wid=%d and sid in (%s) and status=0 and outsourcer_id!=0', $wid, implode(',', $sids));
        $stockInfo = $ws->getByWhere($outsourcerWhere, array('sid'), 0, 0);
        $outsourcerIds = Tool_Array::getFields($stockInfo, 'sid');
        $sids = array_diff($sids, $outsourcerIds);
        
        $fields = array('id', 'sid', 'num', 'cost', 'in_id', 'in_type');
        $fifoDatas = $sfc->getBulkVaildCostsOfFifoQueue($wid, $sids, $fields, true);
        
        foreach($skuList as $item)
        {
            $_sid = $item['sid'];
            if (!array_key_exists($_sid, $fifoDatas))   //fifo队列没有记录
            {
                $leftSids[] = $item['sid']; 
                $skuCosts[$_sid]['cost'] = 0;
                $skuCosts[$_sid]['_cost_fifo'] = array();
                
                continue;
            }
            
            // fifo返回的结果已经是按照时间升序的（即：先进先出）
            $needNum = abs($item['num']);
            
            $_tCost = $_tNnum = 0;
            foreach($fifoDatas[$_sid] as $fifoId => $fifoItem)
            {
                $chgNum = min($needNum, $fifoItem['num']);
                $skuCosts[$_sid]['_cost_fifo'][] = array(
                    'id' => $fifoId,
                    'num' => $chgNum,
                    'cost' => $fifoItem['cost'],
                    'in_id' => $fifoItem['in_id'],
                    'in_type' => $fifoItem['in_type'],
                );
                $_tCost += $chgNum*$fifoItem['cost'];
                $_tNnum += $chgNum;
                $needNum -= $chgNum;
                
                if ($needNum <=0 ) break;
            }
            $skuCosts[$_sid]['cost'] = $_tNnum!=0? round($_tCost/$_tNnum): 0;
        }
        
        return $skuCosts;
    }
    
    /**
     * 批量：从t_stock按仓库获取sku成本.
     * 
     * @param int $wid
     * @param array $skuIds
     * @param array $leftSids
     */
    public static function getCostsFromStock($wid, $skuIds, &$leftSids=array())
    {
        if (empty($skuIds)) return;
        
        $skuCost = array();
        $ws = new Warehouse_Stock();
        
        $stocks = Tool_Array::list2Map($ws->getBulk($wid, $skuIds, array('sid', 'cost')), 'sid', 'cost');
        
        foreach ($skuIds as $_sid)
        {
            if (array_key_exists($_sid, $stocks) && $stocks[$_sid]>0)
            {
                $skuCost[$_sid] = $stocks[$_sid];
            }
            else
            {
                $skuCost[$_sid] = 0;
                $leftSids[] = $_sid;
            }
        }
        
        return $skuCost;
    }
    
    /**
     * 批量：从t_product按城市获取sku的成本.
     * 
     * @param int $wid
     * @param array $skuIds
     * @param array $leftSids
     */
    public static function getCostsFromProduct($wid, $skuIds, &$leftSids=array())
    {
        if (empty($skuIds)) return;
        
        $skuCost = array();
        $ss = new Shop_Product();
        
        $cityId = Conf_Warehouse::getCityByWarehouse($wid);
        
        $products = Tool_Array::list2Map($ss->getBySku($skuIds, $cityId, 3), 'sid', 'cost');
        
        foreach ($skuIds as $_sid)
        {
            if (array_key_exists($_sid, $products) && $products[$_sid]>0)
            {
                $skuCost[$_sid] = $products[$_sid];
            }
            else
            {
                $skuCost[$_sid] = 0;
                $leftSids[] = $_sid;
            }
        }
        
        return $skuCost;
    }
    
    
}