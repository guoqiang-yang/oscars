<?php
/**
 * order 配置
 */
class Conf_Stock
{
	/**
	 * 订单状态:  1-现金, 2-转账
	 */
	const
		PAYMENT_CASH = 1,			//现金
		PAYMENT_TRANSFER = 2,		//财务结
		PAYMENT_MONEY_FIRST = 3,	//现款后货
        PAYMENT_NO_NEED =4;         //无需付款

	/**
	 * @var array 付款方式
	 */
	public static $PAYMENT_TYPES = array(
		self::PAYMENT_CASH			=> '现金',
		self::PAYMENT_TRANSFER		=> '财务结',
		self::PAYMENT_MONEY_FIRST	=> '现款后货',
        self::PAYMENT_NO_NEED       => '无需付款',
	);

	public static function getPaymentName($type)
	{
		if (!isset(self::$PAYMENT_TYPES[$type]))
		{
			return '-';
		}
		return self::$PAYMENT_TYPES[$type];
	}

	//供货商审核人
    const SUPPLIER_CHECK_USER = 1336;   //李亮亮

    //其他出库审核人
    public static function getOtherStockOutCheckSuid($wid)
    {
        $checkSuid = array(
            Conf_Warehouse::WID_3 => 1002,      //吴小武
            Conf_Warehouse::WID_4 => 1424,      //张健
            Conf_Warehouse::WID_5 => 1002,      //吴小武
            Conf_Warehouse::WID_TJ1 => 1425,    //朱美玲
        );

        return $checkSuid[$wid];
    }
    
    //安全库存
    const DELIVERY_DAY = 4;             //货期
    const MIN_DAY_OF_STOCK = 1;       //最小库存天数=1天
    const ADJUST_FACTOR = 1;            //调整系数
    
    const DAYS_OF_STAT_WAIT_NUM = 7;    //统计在途商品的的天数
    
    //安全库存 - 调整系数
    public static $ADJUST_FACTOR_By_WID = array(
        Conf_Warehouse::WID_3 => 1,
        Conf_Warehouse::WID_4 => 1,
        Conf_Warehouse::WID_5 => 1,
        Conf_Warehouse::WID_6 => 1,
        Conf_Warehouse::WID_TJ1 => 1,
    );
    
    // 计算订货量特殊的sku-id 和 wid=>天数
    public static $SPECIAL_SKUID_4_QUANTITY = array(
        '10349' => array(3=>2, 4=>4),
        '10350' => array(3=>2, 4=>4),
        '10352' => array(3=>2, 4=>4),
        '10357' => array(3=>2, 4=>4),
        '10381' => array(3=>2, 4=>4),
        '10396' => array(3=>2, 4=>4),
        '10398' => array(3=>2, 4=>4),
        '10797' => array(3=>2, 4=>4),
        '11202' => array(3=>2, 4=>4),
        '13399' => array(3=>2, 4=>4),
    );
    
    // sku的货期
    public static $SPECIAL_DELIVERY_DAY_4_SKUID = array(
        '10663' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '10665' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '10666' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '10667' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '10908' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '10911' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '10912' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '10913' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '10914' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '10941' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '10942' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '10943' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '11004' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '11318' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '11319' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '11320' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '11325' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '11326' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '11346' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '11347' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '11604' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '11667' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '11792' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '12189' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '12190' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '12380' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '12530' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '12531' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '12532' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '12533' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '12629' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '12651' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13085' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13086' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13566' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13567' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13568' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13569' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13570' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13571' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13575' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13576' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13577' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13578' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13579' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13580' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13581' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13582' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13583' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13584' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13585' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13586' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13399' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13400' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13401' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13402' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),
        '13403' => array('wids'=>array(3,4,5,6,7,21), 'days'=>5),     
    );
    
    
    //安全库存零售对应
    public static $SKU_SALES_CONVERT = array(
        10117 => 11826,
        10558 => 12379,
        10559 => 11411,
        10663 => 12306,
        10911 => 12380,
        10912 => 11792,
        10913 => 11347,
        10914 => 11346,
        10941 => 12190,
        10943 => 12189,
        11515 => 11191,
        11640 => 10201,
        12032 => 12302,
        12428 => 10208,
        13327 => 11356,
        11826 => 10117,
        12379 => 10558,
        11411 => 10559,
        12306 => 10663,
        12380 => 10911,
        11792 => 10912,
        11347 => 10913,
        11346 => 10914,
        12190 => 10941,
        12189 => 10943,
        11191 => 11515,
        10201 => 11640,
        12302 => 12032,
        10208 => 12428,
        11356 => 13327,
    );
    

    //盘点方式
    const STOCKTAKING_METHOD_STATIC = 1;    //静态盘点

    public static $STOCKTAKING_METHODS = array(
        self::STOCKTAKING_METHOD_STATIC => '静态盘点',
    );

    //计划类型
    const STOCKTAKING_PLAN_DAY = 1,     //日常盘点
        STOCKTAKING_PLAN_MONTH = 2,   //月度盘点
        STOCKTAKING_PLAN_YEAR = 3;    //年终盘点

    public static $STOCKTAKING_PLANS = array(
        self::STOCKTAKING_PLAN_DAY => '日常盘点',
        self::STOCKTAKING_PLAN_MONTH => '月度盘点',
        self::STOCKTAKING_PLAN_YEAR => '年终盘点',
    );

    //盘点方式
    const STOCKTAKING_ATTRIBUTE_HIDDEN = 1;     //盲盘

    public static $STOCKTAKING_ATTRIBUTES = array(
        self::STOCKTAKING_ATTRIBUTE_HIDDEN => '盲盘',
    );

    //盘点次数
    const STOCKTAKING_TIME_FIRST = 1,   //初盘
        STOCKTAKING_TIME_SECOND = 2,  //复盘
        STOCKTAKING_TIME_THIRD = 3;   //三盘

    public static $STOCKTAKING_TIMES = array(
        self::STOCKTAKING_TIME_FIRST => '初盘',
        self::STOCKTAKING_TIME_SECOND => '复盘',
        self::STOCKTAKING_TIME_THIRD => '三盘',
    );

    //盘点类型
    const STOCKTAKING_TYPE_ALL = 1,         //盘全部
        STOCKTAKING_TYPE_BY_LOCATION = 2, //按货位
        STOCKTAKING_TYPE_BY_BRAND = 3;    //按品牌

    public static $STOCKTAKING_TYPES = array(
        self::STOCKTAKING_TYPE_ALL => '盘全部',
        self::STOCKTAKING_TYPE_BY_LOCATION => '按货位',
        self::STOCKTAKING_TYPE_BY_BRAND => '按品牌',
    );

    //计划状态
    const STOCKTAKING_PLAN_STEP_NOT_START = 1,       //未开始
        STOCKTAKING_PLAN_STEP_ONGOING = 2,         //进行中
        STOCKTAKING_PLAN_STEP_FINISHED = 3;        //已完成

    public static $STOCKTAKING_PLAN_STEPS = array(
        self::STOCKTAKING_PLAN_STEP_NOT_START => '未开始',
        self::STOCKTAKING_PLAN_STEP_ONGOING => '进行中',
        self::STOCKTAKING_PLAN_STEP_FINISHED => '盘点完成',
    );

    //任务状态
    const STOCKTAKING_TASK_STEP_NOT_START = 1,       //未分配
        STOCKTAKING_TASK_STEP_ALLOCATED = 2,         //已分配
        STOCKTAKING_TASK_STEP_ONGOING = 3,           //进行中
        STOCKTAKING_TASK_STEP_FINISHED = 4;          //已完成

    public static $STOCKTAKING_TASK_STEPS = array(
        self::STOCKTAKING_TASK_STEP_NOT_START => '未分配',
        self::STOCKTAKING_TASK_STEP_ALLOCATED => '已分配',
        self::STOCKTAKING_TASK_STEP_ONGOING => '进行中',
        self::STOCKTAKING_TASK_STEP_FINISHED => '已完成',
    );

    const OTHER_STOCK_ORDER_TYPE_OUT = 1,
        OTHER_STOCK_ORDER_TYPE_IN = 2;

    public static function getOtherStockOrderTypes()
    {
        $types = array(
            self::OTHER_STOCK_ORDER_TYPE_OUT => '其他出库单',
            self::OTHER_STOCK_ORDER_TYPE_IN => '其他入库单',
        );

        return $types;
    }


    const OTHER_STOCK_OUT_TYPE_SELF_USE = 1,         //自用
          OTHER_STOCK_OUT_TYPE_BROKEN = 2,           //报损
          OTHER_STOCK_OUT_TYPE_CHANGE = 3,           //换货出库
          OTHER_STOCK_OUT_TYPE_REFUND = 4;           //第三方退货

    const OTHER_STOCK_IN_TYPE_CHANGE = 1;           //换货入库

    public static function getOtherStockTypes($orderType = self::OTHER_STOCK_ORDER_TYPE_OUT)
    {
        $types = array(
            self::OTHER_STOCK_ORDER_TYPE_OUT => array(
                self::OTHER_STOCK_OUT_TYPE_SELF_USE => '自用',
                self::OTHER_STOCK_OUT_TYPE_BROKEN => '报损',
                self::OTHER_STOCK_OUT_TYPE_CHANGE => '换货出库',
                self::OTHER_STOCK_OUT_TYPE_REFUND => '第三方退货',
            ),
            self::OTHER_STOCK_ORDER_TYPE_IN=> array(
                self::OTHER_STOCK_IN_TYPE_CHANGE => '换货入库'
            ),
        );

        return $types[$orderType];
    }

    public static function getOtherStockOrderReasons($orderType = self::OTHER_STOCK_ORDER_TYPE_OUT)
    {
        $reasons = array(
            self::OTHER_STOCK_ORDER_TYPE_OUT => array(
                self::OTHER_STOCK_OUT_TYPE_SELF_USE => array(
                    1 => '仓储自用',
                    2 => '部门自用',
                ),
                self::OTHER_STOCK_OUT_TYPE_BROKEN => array(
                    1 => '仓储作业破损',
                    2 => '售后退货破损',
                    3 => '库内自然损耗',
                    4 => '司机退货破损',
                    5 => '到货破损',
                    6 => '调拨破损',
                    7 => '质量问题',
                    8 => '装卸破损',
                    9 => '运输破损',
                ),
                self::OTHER_STOCK_OUT_TYPE_CHANGE => array(
                    1 => '供应商换货出库',
                ),
                self::OTHER_STOCK_OUT_TYPE_REFUND => array(
                    1 => '第三方退货',
                ),
            ),
            self::OTHER_STOCK_ORDER_TYPE_IN => array(
                self::OTHER_STOCK_IN_TYPE_CHANGE => array(
                    1 => '供应商换货入库',
                ),
            ),
        );

        return $reasons[$orderType];
    }

//    public static $OTHER_STOCK_OUT_REASONS = array(
//        self::OTHER_STOCK_OUT_TYPE_SELF_USE => array(
//            1 => '仓储自用',
//            2 => '部门自用',
//        ),
//        self::OTHER_STOCK_OUT_TYPE_BROKEN => array(
//            1 => '仓储作业破损',
//            2 => '售后退货破损',
//            3 => '库内自然损耗',
//            4 => '司机退货破损',
//            5 => '到货破损',
//            6 => '调拨破损',
//            7 => '质量问题',
//            8 => '装卸破损',
//            9 => '运输破损',
//        ),
//    );

    public static $STOCK_OUT_HISTORY_REASON_CONVERT = array(
        self::OTHER_STOCK_OUT_TYPE_SELF_USE => array(
            1 => 20,
            2 => 21,
        ),
        self::OTHER_STOCK_OUT_TYPE_BROKEN => array(
            1 => 11,
            2 => 12,
            3 => 13,
            4 => 14,
            5 => 15,
            6 => 16,
            7 => 17,
            8 => 18,
            9 => 19,
        ),
        self::OTHER_STOCK_OUT_TYPE_CHANGE => array(
            1 => 22,
        ),
        self::OTHER_STOCK_OUT_TYPE_REFUND => array(
            1 => 24,
        ),
    );

    const OTHER_STOCK_OUT_ORDER_STEP_CREATE = 1,        //已创建
          OTHER_STOCK_OUT_ORDER_STEP_UN_AUDIT = 2,      //已驳回
          OTHER_STOCK_OUT_ORDER_STEP_WAIT_AUDIT = 3,    //待审核
          OTHER_STOCK_OUT_ORDER_STEP_AUDITED = 4,       //已审核
          OTHER_STOCK_OUT_ORDER_STEP_PART_SHELVED = 5,  //部分上架
          OTHER_STOCK_OUT_ORDER_STEP_FINISH = 6;        //已完成

    public static $OTHER_STOCK_OUT_ORDER_STEPS = array(
        self::OTHER_STOCK_OUT_ORDER_STEP_CREATE => '已创建',
        self::OTHER_STOCK_OUT_ORDER_STEP_UN_AUDIT => '已驳回',
        self::OTHER_STOCK_OUT_ORDER_STEP_WAIT_AUDIT => '待审核',
        self::OTHER_STOCK_OUT_ORDER_STEP_AUDITED => '已审核',
        self::OTHER_STOCK_OUT_ORDER_STEP_PART_SHELVED => '部分上架',
        self::OTHER_STOCK_OUT_ORDER_STEP_FINISH => '已完成',
    );
    
    const PROCESSED_ORDER_COM_SALE = 1,     //组合售卖
          PROCESSED_ORDER_CONVERT = 2;      //零整转换
    
    public function getProcessedOrderTypes() {
        
        return array(
            self::PROCESSED_ORDER_COM_SALE => '组合售卖',
            self::PROCESSED_ORDER_CONVERT  => '整转零售',
        );
    }
    
}
