<?php
/**
 * order 配置
 */
class Conf_In_Order
{
	/**
	 * 订单状态:1待审批, 2待收货, 3部分收货, 4完全收货
	 */
	const
		ORDER_STEP_NEW = 1,      //已创建
		ORDER_STEP_SURE = 2,     //待收货
        ORDER_STEP_PART_RECEIVED = 3, //部分收货
		ORDER_STEP_RECEIVED = 4; //完全收货

	/**
	 * @var array 付款方式
	 */
	public static $ORDER_STEPS = array(
		1 => '已创建',
		2 => '待收货',
        3 => '部分收货',
		4 => '完全收货',
    );

    /**
     * 临时采购账号配置 - 自营
     */
    public static $Temporary_Purchase_Suppliers = array(
        Conf_Warehouse::WID_3 => 93,    //老南库，新南库
        Conf_Warehouse::WID_4 => 177,   //北库
        Conf_Warehouse::WID_5 => 229,   //东库，关闭
        Conf_Warehouse::WID_TJ1 => 272, //天津河西仓
        Conf_Warehouse::WID_6 => 292,   //西库，关闭
        Conf_Warehouse::WID_8 => 93,    //新南库
        Conf_Warehouse::WID_WH1 => 305, //武汉，关闭
        Conf_Warehouse::WID_LF1 => 459, //廊坊-自营
        Conf_Warehouse::WID_TJ2 => 552, //天津津南库
        Conf_Warehouse::WID_CQ1 => 511, //重庆总仓
        Conf_Warehouse::WID_CQ_5001 => 493, //重庆分仓-渝北-5001
        Conf_Warehouse::WID_CHD1 => 604,    //成都总仓
        Conf_Warehouse::WID_QD1 => 689, //青岛仓
    );

    /**
     * 临采供应商账号 - 联营
     */
    public static $Temporary_Purchase_Joint_Suppliers = array(
        Conf_Warehouse::WID_3 => 399,   //南库
        Conf_Warehouse::WID_4 => 398,   //北库
        Conf_Warehouse::WID_8 => 399,   //新南库
        Conf_Warehouse::WID_TJ1 => 400, //天津河西库
        Conf_Warehouse::WID_TJ2 => 553, //天津津南库
        Conf_Warehouse::WID_LF1 => 458, //廊坊-第三方-李加利
        Conf_Warehouse::WID_CQ1 => 512, //重庆总仓
        Conf_Warehouse::WID_CQ_5001 => 494, //重庆分仓-渝北-5001
        Conf_Warehouse::WID_CHD1 => 603,    //成都总仓
    );
    
    /**
     * 仓库接货人.
     */
    public static $Products_Receivers = array(
        Conf_Warehouse::WID_3 => array('suid'=>1218, 'name'=>'李辉', 'mobile'=>'18367632661', 'addr'=>'北京丰台区纪家庙南里169号'),
        Conf_Warehouse::WID_4 => array('suid'=>1207, 'name'=>'李雪飞', 'mobile'=>'18141921322', 'addr'=>'北京市朝阳区来广营同鑫九鼎'),
//        Conf_Warehouse::WID_5 => array('suid'=>1147, 'name'=>'王涛',  'mobile'=>'18631796862', 'addr'=>'北京市通州区上店村419号'),
//        Conf_Warehouse::WID_6 => array('suid'=>1243, 'name'=>'刘怀川',  'mobile'=>'13520853054', 'addr'=>'北京市海淀区'),
        Conf_Warehouse::WID_TJ1 => array('suid'=>1110, 'name'=>'朱美玲',  'mobile'=>'18519600670', 'addr'=>'天津市河西区'),
//        Conf_Warehouse::WID_WH1 => array('suid'=>1185, 'name'=>'谢浩', 'mobile'=>'13675585505', 'addr'=>'湖北省武汉市硚口区下双墩路'),
        Conf_Warehouse::WID_CQ1 => array('suid'=>1549, 'name'=>'朱峰', 'mobile'=>'18744914245', 'addr'=>'重庆市沙坪坝区梨树湾5号'),
        Conf_Warehouse::WID_CQ_5001 => array('suid'=>1580, 'name'=>'肖鹏', 'mobile'=>'15310236871', 'addr'=>'中铁物流礼嘉平厂仓储园c62号'),
    );

    
    const SRC_COMMON = 1,
          SRC_TEMPORARY = 2,
          SRC_COMPOSITIVE = 3,
          SRC_OUTSOURCER = 4;
    public static $In_Order_Source = array(
        self::SRC_COMMON        => '普采',
        self::SRC_TEMPORARY     => '临采',
        self::SRC_COMPOSITIVE   => '综合采购',
        self::SRC_OUTSOURCER    => '外包临采',
    );
    
	public static function getOrderStepName($step)
	{
		if (!isset(self::$ORDER_STEPS[$step])) return '-';
		return self::$ORDER_STEPS[$step];
	}

	public static function getOrderStepNames()
	{
		return self::$ORDER_STEPS;
	}

	const IN_ORDER_TYPE_ORDER = 1,
          IN_ORDER_TYPE_GIFT  = 2;

	public static $IN_ORDER_TYPES = array(
        self::IN_ORDER_TYPE_ORDER => '普通采购',
        self::IN_ORDER_TYPE_GIFT  => '赠品入库',
    );
}
