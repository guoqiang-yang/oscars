<?php

class Conf_Stockin_Refund
{
    /**
     * 退货单状态.
     */
    const
        UN_REFUND       = 1,
        HAD_REFUND      = 2,
        REFUND_COMPLETED = 3;
    
    public static $Refund_Descs = array(
        self::UN_REFUND =>      '未退货',
        self::HAD_REFUND =>     '已退货',
        self::REFUND_COMPLETED => '已核销',
    );
}