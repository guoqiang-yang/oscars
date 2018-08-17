<?php

/**
 * 后台任务系统.
 */

class Conf_Admin_Task
{
    /**
     * 任务的执行状态
     */
    const
        ST_CREATE          = 1,       //创建
        ST_WAIT_DEAL       = 2,       //待处理
        ST_COMPLETE        = 3,       //已完成
        ST_CLOSE           = 4,       //已关闭
            
        ST_DELETE          = 10;      //已删除
    
    public static $Exec_Task_Desc = array(
        self::ST_CREATE         => '创建',
        self::ST_WAIT_DEAL      => '待处理',
        self::ST_COMPLETE       => '已完成',
        self::ST_CLOSE          => '已关闭',
        
        
        self::ST_DELETE         => '已删除',
    );
    
    /**
     * 操作对象的类型 && 简述
     */
    const
        OBJTYPE_ORDER               = 1,
        OBJTYPE_CUSTOMER            = 2,
        OBJTYPE_STOCK               = 3,
        OBJTYPE_IN_ORDER            = 4,
        OBJTYPE_STOCK_IN            = 5,
        OBJTYPE_MONEY_IN            = 6,
        OBJTYPE_MONEY_OUT           = 7,
        OBJTYPE_CUSTOMER_AMOUNT     = 8;
    
    public static $Objtype_Desc = array(
        self::OBJTYPE_ORDER                 => '订单',
        self::OBJTYPE_CUSTOMER              => '客户',
        self::OBJTYPE_STOCK                 => '库存',
        self::OBJTYPE_IN_ORDER              => '采购单',
        self::OBJTYPE_STOCK_IN              => '入库单',
        self::OBJTYPE_MONEY_IN              => '订单收款',
        self::OBJTYPE_MONEY_OUT             => '采购支付',
        self::OBJTYPE_CUSTOMER_AMOUNT       => '客户余额',
    );
    
    public static $Objtype_Link = array(
        self::OBJTYPE_ORDER             => '/order/order_detail.php?oid=',
        self::OBJTYPE_CUSTOMER          =>  array(
            'page'=>'/order/customer_list_cs.php?cid=', 'h5'=>'/crm/detail_customer.php?cid=',
        ),
    );
    
    public static $Short_Desc = array(
        self::OBJTYPE_ORDER => array(
            1 => '司机迟到',
            2 => '搬运工问题',
            3 => '漏配-错配',
            4 => '运费不对',
            5 => '搬运费不对',
            6 => '优惠不对',
            7 => '司机迟到',
            8 => '司机未收钱',
            9 => '产品质量问题',
            10 => '裁剪问题',
            11 => '迟配',
        ),
        
        self::OBJTYPE_CUSTOMER => array(
            1 => '客户下单',
        ),
        
        'default' => array(
            98 => '建议',
            99 => '其他',
        ),
    );
    
    /**
     * 默认执行人 - 主要H5使用.
     */
    public static $Default_Exec_Suid = array(
        self::OBJTYPE_ORDER => array(
            0 => array(1027), //df: 小纪
//            3 => array(1098, 1099),   // 销售助理
            3 => array(1172),   // 郝展
        ),
        
        'default' => array(1027),
    );
    
    /**
     * 任务级别
     */
    const
        TASK_LEVEL_NORMAL       = 1,
        TASK_LEVEL_CRITICAL     =2;
    
    public static $Task_Level = array(
        self::TASK_LEVEL_NORMAL     => '正常',
        self::TASK_LEVEL_CRITICAL   => '紧急',
    );
    
    
    public static function getShortDescOfObjtype($objtype=0)
    {
        $descs = array();
        
        if (array_key_exists($objtype, self::$Objtype_Desc))
        {
            $descs[$objtype] = array_key_exists($objtype, self::$Short_Desc)?
                   self::$Short_Desc[$objtype]+ self::$Short_Desc['default']: self::$Short_Desc['default']; 
        }
        else if ($objtype==0)   // 全部分类
        {
            foreach(self::$Objtype_Desc as $_objtype => $_desc)
            {
                $descs[$_objtype] = array_key_exists($_objtype, self::$Short_Desc)?
                   (self::$Short_Desc[$_objtype]+self::$Short_Desc['default']): self::$Short_Desc['default']; 
            }
        }
        
        return $descs;
    }
}