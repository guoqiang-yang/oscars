<?php

/**
 * 仓库模块 - 库存：辅助类.
 * 
 */

class Warehouse_Stock_Helper
{
    
    /**
     * 是否有足够的货位库存.
     * 
     * @param array $products   {sid:xxx, ...}
     * @param int $wid
     * @param array $distributionLocs   货位库存分配结果
     * 
     */
    public static function chkHasEnoughLocStock($products, $wid, &$distributionLocs)
    {
        $distributionLocs = Warehouse_Location_Api::distributeNumFromLocation($products, $wid,1,true);
        
        if (!is_array($distributionLocs))
        {
            throw new Exception('系统内部错误，请联系管理员');
        }
        
        // 检测库存
        $ExceptionSids = array();
        foreach ($distributionLocs as $sid => $p)
        {
            if(isset($p['vnum']) && $p['vnum'] > 0)
            {
                $ExceptionSids[] = $sid;
            }
        }
        
        if(!empty($ExceptionSids))
        {
            throw new Exception('sid（'.implode(',',$ExceptionSids).'）货架库存不足或者仓库没有该商品，请添加！');
        }
        
        return true;
    }
    
    
}