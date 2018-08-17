<?php

/**
 * 采购API.
 */

class Warehouse_Inorder_Api extends Base_Api
{
    
    /**
     * 取供应商采购过的历史商品.
     * 
     * @param int $supplierId   供应商id
     * @param int $lastNum      最近num条记录(最近num个入库单）
     */
    public static function getProductHistoryForSupplier($supplierId, $wid, $lastNum=30)
    {
        $productList = array();
        $wsi = new Warehouse_Stock_In();
        
        $order = 'order by id desc';
        $stockInList = $wsi->getOrdersOfSupplier($supplierId, $total, $order, 0 , $lastNum);
        
        if ($total == 0)
        {
            return $productList;
        }
        
        // 取入库单商品
        $stockInIds = Tool_Array::getFields($stockInList, 'id');
        $wsip = new Warehouse_Stock_In_Product();
        $products = $wsip->getProductsByIds($stockInIds, array('sid'));
        
        // 取商品信息
        $sids = array_unique(Tool_Array::getFields($products, 'sid'));

        // 获取库存
        $ws = new Warehouse_Stock();
        $stocks = Tool_Array::list2Map($ws->getBulk($wid, $sids), 'sid');
        
        // output
        $skuList = Shop_Api::getSkuInfos($sids);
        foreach ($skuList as &$sku)
        {
            $sku['_stock'] = $stocks[$sku['sid']];
        }
        
        return $skuList;
    }
    
    /**
     * 获取未入库的采购单商品列表.
     * 
     * @param int $oid 采购单ID
     */
    public static function getInorderProudctsNumButUnstockin($oid)
    {
        // 获取采购单的商品列表
        $wiop = new Warehouse_In_Order_Product();
        $inorderProducts = Tool_Array::list2Map($wiop->getProductsOfOrder($oid), 'sid');
        
        // 初始化新增变量
        foreach($inorderProducts as &$p)
        {
            $p['stockin_num'] = 0;
            $p['un_stockin_num'] = $p['num'];
            $p['stockin_num_c'] = 0;
            $p['stockin_num_t'] = 0;
            $p['stockin_ids'] = array();
        }
        
        // 获取采购单的对应的入库单的商品列表
        $wsip = new Warehouse_Stock_In_Product();
         
        $kind = 't_stock_in_product as p, t_stock_in as s';
        $field = array('p.*', 's.source');
        $where = 'p.status=0 and p.id=s.id and p.id in (select id from t_stock_in where status=0 and oid='.$oid.')';
        $stockinProducts = $wsip->getByRawWhere($where, $kind, $field);
        
        foreach($stockinProducts as $one)
        {
            $sid = $one['sid'];
            $inorderProducts[$sid]['stockin_num'] += $one['num'];
            $inorderProducts[$sid]['un_stockin_num'] -= $one['num'];
            
            if ($one['source'] == Conf_In_Order::SRC_COMMON)
            {
                $inorderProducts[$sid]['stockin_num_c'] += $one['num'];
                $inorderProducts[$sid]['stockin_ids'][] = array(
                    'id' => $one['id'],
                    'n' => '集入',
                );
            }
            else if ($one['source'] == Conf_In_Order::SRC_TEMPORARY)
            {
                $inorderProducts[$sid]['stockin_num_t'] += $one['num'];
                $inorderProducts[$sid]['stockin_ids'][] = array(
                    'id' => $one['id'],
                    'n' => '临入',
                );
            }
        }
        
        // 获取sku信息
        $ss = new Shop_Sku();
        $sids = array_keys($inorderProducts);
        $skuInfos = $ss->getBulk($sids);
        
        return array('products'=>$inorderProducts, 'skuinfos'=>$skuInfos);
    }

    //获取某个供应商可开票的采购单
    public static function getAllCanBillOrdersOfSupplier($sid)
    {
        $sid = intval($sid);
        assert($sid > 0);
        $wio = new Warehouse_In_Order();
        return $wio->getAllCanBillOrdersOfSupplier($sid);
    }
}