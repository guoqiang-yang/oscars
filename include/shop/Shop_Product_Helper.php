<?php

/**
 * 商城 - 商品：辅助类.
 * 
 */

class Shop_Product_Helper
{
    
    /**
     * 是否存在下线的商品.
     * 
     * @param array $sids
     * @param int $cityId
     */
    public static function chkHasOfflineProductsBySids($sids, $cityId)
    {
        if (empty($sids) || empty($cityId))
        {
            throw new Exception('数据异常：skuid 或 city_id为空');
        }
        
        $sp = new Shop_Product();
        $productInfo = Tool_Array::list2Map($sp->getBySku($sids, $cityId, Conf_Product::PRODUCT_STATUS_ONLINE), 'sid');
        
        $ExceptionSids = array();
        foreach($sids as $_sid)
        {
            if (empty($productInfo[$_sid]))
            {
                $ExceptionSids[] = $_sid;
            }
        }
        
        if (!empty($ExceptionSids))
        {
            throw new Exception('sid（'.implode(',', $ExceptionSids).'）对应商品已经下架，请先上架！');
        }
        
    }
    
    
}