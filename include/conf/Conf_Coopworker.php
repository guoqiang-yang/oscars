<?php

class Conf_Coopworker
{
    // 订单排线，车型标识符
    const Orderline_CarModel_Flag = 'D';
    const Orderline_CarModel_Sp1 = ':';
    const Orderline_CarModel_Sp2 = ',';
    
    // 订单排线Step
    const ORDER_LINE_NO_DRIVER = 0,     //未安排司机
          ORDER_LINE_PART_DRIVER = 1,   //部分安排司机
          ORDER_LINE_HAD_DRIVER = 2;    //已安排司机
    const STATEMENT_STEP_CREATE = 1,    //已创建
          STATEMENT_STEP_SURE = 2,      //已确认
          STATEMENT_STEP_CHECK = 4,     //已审核
          STATEMENT_STEP_PAID = 5;      //已支付

    public static $Order_Line_Step_Descs = array(
        self::ORDER_LINE_NO_DRIVER =>   '未分配',
        self::ORDER_LINE_PART_DRIVER => '部分分配',
        self::ORDER_LINE_HAD_DRIVER  => '完成分配',
    );
    
    /**
     * 第三方工人的费用类型.
     */
    public static $Coopworker_Fee_Types = array(
        '1'     => '运费',
        '2'     => '搬运费',
        '10'    => '奖励',
        '11'    => '罚款',
    );

    /**
     *
     * 结算单步骤。
     */
    public static $Statement_Step = array(
        self::STATEMENT_STEP_CREATE => '已创建',
        self::STATEMENT_STEP_SURE   => '已确认',
        self::STATEMENT_STEP_CHECK  => '已审核',
        self::STATEMENT_STEP_PAID   => '已支付',
    );

    const OBJ_TYPE_ORDER =1,
          OBJ_TYPE_REFUND_ORDER =2;

    public static $OBJ_TYPES = array(
        self::OBJ_TYPE_ORDER => '订单',
        self::OBJ_TYPE_REFUND_ORDER => '退货单',
    );

    //廊坊搬运工费用
    public static $LF_CARRIER_FEE_RULES = array(
        Conf_Driver::CAR_MODEL_DIANDONGPINGBAN => 3000,     //电动平板车30元一车
        Conf_Driver::CAR_MODEL_PINGDINGJINBEI => 5000,      //金杯50元一车
    );

    //修改运费、搬运费模板(每套模板有对应的计算规则)
    public static function getEditCoopworkerFlagForWid($wid, $type)
    {
        assert(!empty($type) && !empty($wid));

        $conf = array(
            //运费
            '3-1' => 'driver_1',
            '4-1' => 'driver_1',
            '5-1' => 'driver_1',
            '8-1' => 'driver_1',
            '21-1' => 'driver_3',
            '22-1' => 'driver_4',
            '41-1' => 'driver_2',
            '51-1' => 'driver_5',
            '5001-1' => 'driver_5',
            '61-1' => 'driver_5',
            '71-1' => 'driver_4',

            //搬运费
            '3-2' => 'carrier_1',
            '4-2' => 'carrier_1',
            '5-2' => 'carrier_1',
            '8-2' => 'carrier_1',
            '21-2' => 'carrier_1',
            '22-2' => 'carrier_3',
            '41-2' => 'carrier_2',
            '51-2' => 'carrier_1',
            '5001-2' => 'carrier_1',
            '61-2' => 'carrier_1',
            '71-2' => 'carrier_1',
        );

        return $conf[$wid.'-'.$type];
    }
}