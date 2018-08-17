<?php

class Conf_Product
{
    
    /**
     * 商品的采购属性.
     */
    const BUY_TYPE_COMMON = 1;
    const BUY_TYPE_TEMP = 2;
    
    /**
     * 商品的销售属性.
     */
    const SALES_TYPE_COMMON = 0;
    const SALES_TYPE_PROMOTION = 1;
    const SALES_TYPE_HOT = 2;

    const PRODUCT_STATUS_ONLINE = 1;
    const PRODUCT_STATUS_OFFLINE = 2;
    const PRODUCT_STATUS_DELETED = 4;
    
    /**
     * 商品临采属性描述.
     * 
     * @param int $showNameType
     */
    public static function getBuyTypeDesc($showNameType=1)
    {
        if ($showNameType == 2)
        {
            return array(
                self::BUY_TYPE_COMMON => '普',
                self::BUY_TYPE_TEMP => '临',
            );
        }
        else
        {
            return array(
                self::BUY_TYPE_COMMON => '普采商品',
                self::BUY_TYPE_TEMP => '临采商品',
            );
        }
    }
    
    /**
     * 商品销售属性描述.
     */
    
    public static function getSalesTypeDesc()
    {
        return array(
            self::SALES_TYPE_COMMON => '普通',
            self::SALES_TYPE_PROMOTION => '促销',
            self::SALES_TYPE_HOT => '热卖',
        );
    }
    
}