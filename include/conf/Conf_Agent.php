<?php

/**
 * 经销商相关配置.
 */

class Conf_Agent
{
    
    /**
     * 经销商流水类型.
     */
    const Agent_Type_Stored            = 1,
          Agent_Type_Withdrawals       = 2,
          Agent_Type_Day               = 3,
          Agent_Type_Month             = 4,
          Agent_Type_Stock_In          = 5,
          Agent_Type_StockShift_In     = 6,
          Agent_Type_StockShift_Out    = 7;
    
    public static function getAgentAmountHistoryTypes()
    {
        return array(
            self::Agent_Type_Stored          => array('desc'=>'储值',     'url'=>''),
            self::Agent_Type_Withdrawals     => array('desc'=>'提现',     'url'=>''),
            self::Agent_Type_Day             => array('desc'=>'销售日结',  'url'=>'/finance/agent_bill_day_detail.php?bid='),
            self::Agent_Type_Month           => array('desc'=>'月结返点',  'url'=>'/finance/agent_bill_cashback_detail.php?id='),
            self::Agent_Type_Stock_In        => array('desc'=>'采购入库',  'url'=>'/warehouse/edit_stock_in.php?id='),
            self::Agent_Type_StockShift_In   => array('desc'=>'调拔入库',  'url'=>'/warehouse/stock_shift_detail.php?ssid='),
            self::Agent_Type_StockShift_Out  => array('desc'=>'调拔出库',  'url'=>'/warehouse/stock_shift_detail.php?ssid='),
        );
    }

    const Agent_Bill_Step_Create = 1,   //已创建
          Agent_Bill_Step_Audit = 2,    //已审核
          Agent_Bill_Step_Complate = 3; //已完成
    
    /**
     * 经销商返点比例
     */
    const Agent_Rebate_6 = 0.06,
          Agent_Rebate_8 = 0.08;

    public static function getWidMapRule()
    {
         return $rebate_Warehouse= array(
             Conf_Warehouse::WID_CQ_5001 => self::Agent_Rebate_6,
         );

    }

    /**
     * 搬运费返点计算规则
     * {bSalary}: 基本薪资
     * {carryNum}: 搬运数量
     * {workDays}: 工作天数
     * {payDays}: 计薪日
     * {formula}: 规则
     */
    const Agent_Carriage_Rule_1 = '{bSalary}*{carryNum}/10';

    /**
     * 经销商月结返点类型
     */
    const Agent_Cashback_Type_Order = 1,
          Agent_Cashback_Type_Carriage = 2;

    public static function getAgentCashbackTypes()
    {
        return $type = array(
            self::Agent_Cashback_Type_Order => '货款返点',
            self::Agent_Cashback_Type_Carriage => '搬运费补贴',
        );
    }

    /**
     * 经销商日结与HC订单/退单类型
     */
    const Agent_Bill_Type_Order = 1,
          Agent_Bill_Type_Refund = 2;
}