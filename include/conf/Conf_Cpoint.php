<?php

/**
 * 积分商城相关配置.
 */

class Conf_Cpoint
{
    
    /**
     * 积分商品库存历史类型.
     */
    const History_Type_Incr     = 1,
          History_Type_Decr     = 2,
          History_Type_Exchg    = 3;
    
    public static function getStockHistoryType()
    {
        return array(
            self::History_Type_Incr     => '增加库存',
            self::History_Type_Decr     => '减少库存',
            self::History_Type_Exchg    => '兑换出库',
        );
    }
    
    /**
     * 积分类型.
     */
    const Point_Type_Order          = 1,
          Point_Type_Refund         = 2,
          Point_Type_Exchange       = 3,
          Point_Type_Activity       = 4,
          Point_Type_Unfreeze       = 5,
          Point_Type_Admin_Adjust   = 6;

    
    public static function getPointTypes()
    {
        return array(
            self::Point_Type_Order      => array('desc'=>'订单',        'act'=>'购物',   'url'=>'/order/order_detail.php?oid='),
            self::Point_Type_Refund     => array('desc'=>'退货单',      'act'=>'退货',    'url'=>'/order/edit_refund_new.php?rid='),
            self::Point_Type_Exchange   => array('desc'=>'兑换单',      'act'=>'兑换商品', 'url'=>'/activity/cpoint_product_exchange_record.php?oid='),
            self::Point_Type_Activity   => array('desc'=>'活动赠送',    'act'=>'赠送',     'url'=>''),
            self::Point_Type_Unfreeze   => array('desc'=>'积分解冻',    'act'=>'解冻',     'url'=>'/order/order_detail.php?oid='),
            self::Point_Type_Admin_Adjust   => array('desc'=>'后台调整',    'act'=>'调整',     'url'=>''),
        );
    }


  /**
     * 积分商品分类.
     */
    public static function getCate1()
    {
        return array(
            1 => '3C电器',
            2 => '茶酒冲饮',
            3 => '家居家纺',
            4 => '服装服饰',
            5 => '日用百货',
            6 => '优惠券',
        );
    }
    
    /**
     * 快递公司.
     */
    public static function getExpress()
    {
        return array(
            1 => '顺丰',
            2 => '中通',
            3 => '申通',
            4 => '圆通',
            5 => '百世汇通',
            6 => '韵达',
            7 => '天天',
        );
    }

    public static function getExpireDate()
    {
        return date('Y-m-t');
    }
    
}