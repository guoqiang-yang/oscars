<?php

/**
 * order 配置
 */
class Conf_Order
{
    /**
	 * 订单step:
	 */
	const
		ORDER_STEP_ALL = 127,    //所有
		ORDER_STEP_ALL_SURE = 126,//所有确认后的单子
		ORDER_STEP_EMPTY = 0,    //客户未确认
		ORDER_STEP_NEW = 1,      //客户已确认
		ORDER_STEP_SURE = 2,     //客服已确认
		ORDER_STEP_BOUGHT = 3,   //已采购
		ORDER_STEP_HAS_DRIVER = 4,//已安排司机
		ORDER_STEP_PICKED = 5,   //已配货
		ORDER_STEP_DELIVERED = 6,//客户已签收
		ORDER_STEP_FINISHED = 7; //已回单

	/**
	 * 配送方式.
	 */
	const DELIVERY_EXPRESS = 1;
	const DELIVERY_BY_YOURSELF = 2;
	const DELIVERY_QUICKLY = 3;

	public static $DELIVERY_TYPES = array(
		self::DELIVERY_EXPRESS => '送货上门',
		self::DELIVERY_BY_YOURSELF => '自提',
        self::DELIVERY_QUICKLY => '尽快送达',
	);
	
	// 上楼
	const SERVICE_NO_UPSTAIRS = 0;
	const SERVICE_UPSTAIRS = 1;
	const SERVICE_ELEVATOR = 2;
	
	public static $SERVICE_TYPES = array(
	   self::SERVICE_NO_UPSTAIRS => '不上楼',  
	   self::SERVICE_UPSTAIRS => '上楼',
	   self::SERVICE_ELEVATOR => '电梯上楼',
	);

	const UN_PAID = 0,      //未支付
		HAD_PAID = 1,     //已支付
		PART_PAID = 2;    //部分支付

	public static $PAY_STATUS = array(
		self::UN_PAID => '未支付',
		self::HAD_PAID => '已支付',
		self::PART_PAID => '部分支付',
	);

	const INTER_HOUR = 2;   //下单间隔小时

	const ORDER_MIN_FREIGHT_BEIJING = 1;     //北京六环
	const ORDER_MIN_FREIGHT_TIANJIN = 2;     //天津内环
	const ORDER_MIN_FREIGHT_WUHAN_JIANGBEI = 3;     //武汉中心城区江北
	const ORDER_MIN_FREIGHT_WUHAN_JIANGNAN = 4;     //武汉中心城区江南



	const ORDER_MIN_CARRY_FEE = 30;     //最少的搬运费
    const ORDER_CHENGDU_MIN_CARRY_FEE = 20; //成都最少的搬运费

	/**
	 * @var array 最少的运费
	 */

	public static $ORDER_MIN_FREIGHT = array(
		self::ORDER_MIN_FREIGHT_BEIJING => 29,	//北京六环最少的运费
		self::ORDER_MIN_FREIGHT_TIANJIN => 29,	//天津内环最少的运费
		self::ORDER_MIN_FREIGHT_WUHAN_JIANGBEI => 39,	//武汉中心城区江北最少的运费
		self::ORDER_MIN_FREIGHT_WUHAN_JIANGNAN => 59,	//武汉中心城区江南最少的运费
	);
	/**
	 * @var array 订单备注
	 */

	public static $ORDER_NOTE = array(
		array('value' => "板材需要截断"),
		array('value' => "需要带搬运工"),
		array('value' => "上次有货要退"),
	);
	/**
	 * @var array 付款方式
	 */
	public static $ORDER_STEPS = array(
		126 => '所有',
		0 => '客户未确认',
		1 => '未确认',
		2 => '已确认', //客服已确认
		3 => '未安排司机',   //已采购
		4 => '未出库', //已安排司机
		5 => '已出库', //已配货
		//6 => '已签收',
		7 => '已回单');

	public static $ORDER_STEPS_SHOW = array(
		0 => '待确认',
		1 => '待确认',
		2 => '待配货', //客服已确认
		3 => '待配货', //已采购
		4 => '待发货', //已安排司机
		5 => '待收货', //已配货
		//6 => '已签收',
		7 => '已完成');

	public static $PAY_STATUS_SHOW = array(
		self::UN_PAID => '待付款',
		self::HAD_PAID => '已付款',
		self::PART_PAID => '待付款',
	);

	const ORDER_STEP_OUT_UNSURE = 1,    //待确认
		ORDER_STEP_OUT_UNPAID = 2,    //待付款
		ORDER_STEP_OUT_PICKING = 3,   //拣货中, 出库中
		ORDER_STEP_OUT_SENDING = 4,   //已发货, 配送中
		ORDER_STEP_OUT_DELIVERED = 5, //已送达, 已完成
		ORDER_STEP_OUT_CANCEL = 6;    //已取消

	public static $ORDER_STEP_OUT_DESC = array(
		self::ORDER_STEP_OUT_UNSURE => '待确认',
		self::ORDER_STEP_OUT_SENDING => '待付款',
		self::ORDER_STEP_OUT_PICKING => '出库中',
		self::ORDER_STEP_OUT_SENDING => '配送中',
		self::ORDER_STEP_OUT_DELIVERED => '已完成',
		self::ORDER_STEP_OUT_CANCEL => '已取消',
	);
	public static $IN_STEPS_2_OUT_STEPS = array(
		self::ORDER_STEP_EMPTY => self::ORDER_STEP_OUT_UNSURE,
		self::ORDER_STEP_NEW => self::ORDER_STEP_OUT_UNSURE,
		self::ORDER_STEP_SURE => self::ORDER_STEP_OUT_PICKING,
		self::ORDER_STEP_BOUGHT => self::ORDER_STEP_OUT_PICKING,
		self::ORDER_STEP_HAS_DRIVER => self::ORDER_STEP_OUT_PICKING,
		self::ORDER_STEP_PICKED => self::ORDER_STEP_OUT_SENDING,
		self::ORDER_STEP_DELIVERED => self::ORDER_STEP_OUT_DELIVERED,
		self::ORDER_STEP_FINISHED => self::ORDER_STEP_OUT_DELIVERED,
	);

	const MAX_COUPON_PRIVILEGE = 300;  //最多优惠300元

	const PRIVILEGE_COUPON = '10';
	public static $Order_Privilege_Types = array(
		self::PRIVILEGE_COUPON => array('ename' => 'coupon', 'cname' => '优惠券优惠'),
		'11' => array('ename' => 'firstOrder', 'cname' => '首单优惠'),
		'12' => array('ename' => 'carry', 'cname' => '搬运优惠'),
	);

    //空采标记的Flag定义
    const VNUM_FLAG_LACK = 1; //标记外采
    
	public static $AUTO_PAID_EXCEPT_CUSTOMERS = array(
		16957, //首都建设
        58657, //鞠老板
        14064, 117550, 118100, 10473, 109903
	);

	/**
	 * 沙子，水泥，砖分类的商品id.
	 *
	 * 订单销售明细筛选分类使用 ProductOrder
	 */
	public static $SAND_CEMENT_BRICK_PIDS = array(
        10874, 10370, 10373, 10794, 11427, 10264, 10265,   //北京
        10371, 10372, 10917, 11603, 11076, 11664, 11930,
		10735, 10734, 10733, 10732, 10731,
        10260, 11492, 11479, 10259,
        10267, 11905,
        12956, 13169, 13170, 
        12043,
        13377,
        
//        15183, 14210, 14213, 14211, 14212, 14216, 15157,    //天津
//        14214, 14225, 14219, 14220, 14221, 14215,
//        14227, 14226, 14224, 14223, 14222,  //轻体转
//        0,  //玻镁板
//        0,  //硅酸钙板
//        14090, 
//        14218, //水泥自流平
        
	);
    
	//订单来源
	const SOURCE_KEFU = 0;              //客服下单
    const SOURCE_AFTER_SALE = 1;        //售后下单
	const SOURCE_SANKONGJIAN = 10001;   //三空间，这个值定义在了Conf_Openapi中
	const SOURCE_WEIXIN = 10002;        //微信商城
	const SOURCE_APP_ANDROID = 10003;   //app-android
	const SOURCE_APP_IOS = 10004;       //app-android
	const SOURCE_ANDROID_CRM = 10005;   //crm-android
	const SOURCE_IOS_CRM = 10006;       //crm-android
    const SOURCE_JINGDONG = 10007;      //京东

	//第三方的订单source要和Conf_Merchant里面的配置保持一致
	const SOURCE_QIANG_GONG_ZHANG = 1001;   //抢工长
    
    const SOURCE_JIASHIFEN = 20004;     //家十分

    //订单来源描述
	public static $SOURCE_DESC = array(
		self::SOURCE_KEFU => '客服',
        self::SOURCE_AFTER_SALE => '售后',
		self::SOURCE_SANKONGJIAN => '三空间',
		self::SOURCE_WEIXIN => '微信商城',
		self::SOURCE_APP_ANDROID => '安卓客户端',
		self::SOURCE_QIANG_GONG_ZHANG => '抢工长',
		self::SOURCE_APP_IOS => 'ios客户端',
        self::SOURCE_JIASHIFEN => '家十分',
	    self::SOURCE_ANDROID_CRM => '安卓-CRM',
	    self::SOURCE_IOS_CRM => 'IOS-CRM',
        self::SOURCE_JINGDONG => '京东订单',
	);

	public static $DELIVERY_TIME = array(
		9 => '12点前',
		12 => '12点到18点',
		18 => '18点到21点',
	);

	public static $DELIVERY_TIME_NEW = array(
		8 => array('desc' => '12点前', 'start' => 8, 'end' => 12),
		12 => array('desc' => '12点到15点', 'start' => 12, 'end' => 15),
		15 => array('desc' => '15点到18点', 'start' => 15, 'end' => 18),
		18 => array('desc' => '18点到21点', 'start' => 18, 'end' => 21),
	);

	public static $ZITI_TIME_CHONGQING = array(
        8 => array('desc' => '8点到9点', 'start' => 8, 'end' => 9),
        9 => array('desc' => '9点到10点', 'start' => 9, 'end' => 10),
        10 => array('desc' => '10点到11点', 'start' => 10, 'end' => 11),
        11 => array('desc' => '11点到12点', 'start' => 11, 'end' => 12),
        12 => array('desc' => '12点到13点', 'start' => 12, 'end' => 13),
        13 => array('desc' => '13点到14点', 'start' => 13, 'end' => 14),
        14 => array('desc' => '14点到15点', 'start' => 14, 'end' => 15),
        15 => array('desc' => '15点到16点', 'start' => 15, 'end' => 16),
        16 => array('desc' => '16点到17点', 'start' => 16, 'end' => 17),
        17 => array('desc' => '17点到18点', 'start' => 17, 'end' => 18),
    );

	public static $DELIVERY_TIME_ADMIN = array(
		8, 9, 10, 11, 12, 13, 14,
		15, 16, 17, 18, 19, 20, 21
	);

    /**
     * 订单优先级描述
     */
    public static $Priority_Desc = array(
        0 => '普通订单',
        1 => 'VIP客户订单',
        2 => '已付款订单',
        3 => '首单',
        
        5 => '加急订单',
    );
    
    /**
     * 订单优先级地图坐标.
     * 
     * 数字越大，同颜色下级别越高
     */
    public static $Order_Priority_Mapimg = array(
        'red' => array( //红：最高级
            0 => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/common/r0.png',
            1 => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/common/r1.png',
            2 => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/common/r2.png',
            3 => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/common/r3.png',
            5 => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/common/r4.png',
        ),
        'orange' => array(  //橙：次高
            0 => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/common/o0.png',
            1 => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/common/o1.png',
            2 => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/common/o2.png',
            3 => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/common/o3.png',
            5 => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/common/o4.png',
        ),
        'yellow' => array(  //黄：低
            0 => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/common/y0.png',
            1 => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/common/y1.png',
            2 => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/common/y2.png',
            3 => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/common/y3.png',
            5 => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/common/y4.png',
        ),
        'blue' => array(    //蓝：最低
            0 => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/common/b0.png',
            1 => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/common/b1.png',
            2 => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/common/b2.png',
            3 => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/common/b3.png',
            5 => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/common/b4.png',
        ),
    );

	public static $NAISHUINIZI_PIDS = array(
		10797, 10401, 10382, 11202, 10400, 10394, 10915, 11809, 12327,  //北京
		14148, 0    , 14150,    //天津
	);

	public static $MEICHAO_PIDS = array(
		//北京
		10295,10293,10353,11489,11703,11129,12906,12905,11522,
		10357,10387,10386,12616,10381,10398,10797,10395,10382,
		//天津
		14335,14336,15144,15145,
		15156,14146,14147,14148,14149,14150
	);
    
    // 虚拟商品的skuid列表，仅临采使用
    public static $Virtual_Skuid_4_Tmp_Purchase = array(
        14436, 13605, 13140, 12904,     //虚拟商品
        13081, 13080,                   //拉渣土
    );

	public static function getOrderStepName($step)
	{
		assert(isset(self::$ORDER_STEPS[$step]));

		return self::$ORDER_STEPS[$step];
	}

	public static function getOrderStepNames()
	{
		return self::$ORDER_STEPS;
	}

	public static function getOrderStepShowName($step)
	{
		assert(isset(self::$ORDER_STEPS_SHOW[$step]));

		return self::$ORDER_STEPS_SHOW[$step];
	}

    const AFTERSALE_TYPE_EXCHANGED = 1,
          AFTERSALE_TYPE_REFUND = 2,
          AFTERSALE_TYPE_TRAPS = 3;


	public static $AFTERSALE_TYPES = array(
	    self::AFTERSALE_TYPE_EXCHANGED => '换货单',
        self::AFTERSALE_TYPE_REFUND => '退货单',
        self::AFTERSALE_TYPE_TRAPS => '补漏单',
    );

	//虚拟小区id
	const VIRTUAL_COMMUNITY_ID = 33633;
    //推荐品牌
    public static $CUSTOMER_RECOMMEND_BRANDS = array(
        Conf_City::BEIJING => array(
            1 => array('132' => '伟星', '101' => '联塑', '100' => '金德', '134' => '日丰'),
            2 => array('298' => '德瑞', '110' => '朝阳昆仑', '101' => '联塑', '112' => '慧远'),
            3 => array('239' => '鲁班', '124' => '龙牌', '125' => '泰山', '157' => '金秋枫叶'),
            4 => array('236' => '钻牌', '128' => '东方雨虹', '237' => '金隅', '151' => '唐姆'),
            5 => array('129' => '美巢', '139' => '山鹅', '146' => '玉桐', '143' => '好材'),
            6 => array('143' => '好材', '257' => '朝美'),
        ),
        Conf_City::TIANJIN => array(
            1 => array('132' => '伟星', '101' => '联塑', '100' => '金德', '134' => '日丰'),
            2 => array('298' => '德瑞', '101' => '联塑', '143' => '好材', '113' => '天津小猫'),
            3 => array('239' => '鲁班', '124' => '龙牌', '125' => '泰山', '157' => '金秋枫叶'),
            4 => array('236' => '钻牌', '128' => '东方雨虹', '237' => '金隅', '151' => '唐姆'),
            5 => array('129' => '美巢', '146' => '玉桐', '143' => '好材', '167' => '熊猫'),
            6 => array('143' => '好材', '257' => '朝美'),
        ),
        Conf_City::LANGFANG => array(
            1 => array('132' => '伟星', '101' => '联塑', '134' => '日丰'),
            2 => array('101' => '联塑', '112' => '慧远', '114' => '秋叶原', '113' => '天津小猫'),
            3 => array('239' => '鲁班', '124' => '龙牌', '157' => '金秋枫叶', '129' => '美巢'),
            4 => array('236' => '钻牌', '128' => '东方雨虹', '151' => '唐姆'),
            5 => array('129' => '美巢', '139' => '山鹅', '146' => '玉桐', '167' => '熊猫'),
            6 => array('257' => '朝美'),
        ),
        Conf_City::CHONGQING => array(
            1 => array('132' => '伟星', '312' => '得亿', '247' => '金牛', '196' => '天力'),
            2 => array('315' => '鸽牌', '312' => '得亿', '114' => '秋叶原'),
            3 => array('125' => '泰山', '186' => '哥俩好'),
            4 => array('317' => '德卡森', '313' => '劳亚尔', '203' => '德高'),
            5 => array('138' => '多乐士','130' => '立邦', '318' => '宏漆'),
            6 => array('168' => '潜水艇', '143' => '好材', '146' => '玉桐'),
        ),
        Conf_City::CHENGDU => array(
            1 => array('441' => '川路', '334' => '多联', '132' => '伟星', '219' => '保利'),
            2 => array('443' => '塔牌', '442' => '特变', '114' => '秋叶原', '324' => '渝丰'),
            3 => array('124' => '龙牌', '125' => '泰山', '325' => '春雨'),
            4 => array('203' => '德高', '128' => '东方雨虹', '297' => '拉法基'),
            5 => array('138' => '多乐士', '130' => '立邦', '226' => '303', '452' => '乐诚'),
            6 => array('208' => '其他'),
        ),
    );
    /**
     * 默认推荐品牌
     * param int $city_id
     * return array 推荐品牌
     */
    public static function getCustomerRecommendBrands($city_id){
        return self::$CUSTOMER_RECOMMEND_BRANDS[$city_id];
    }
    //主材、辅材对应关系
    public static $CATE_RELATION_LIST = array(
        101 => 110,
        110 => 101,
        11001 => 101,
        11002 => 101,
        11003 => 101,
        11004 => 101,
        102 => 111,
        111 => 102,
        11101 => 102,
        11102 => 102,
        11103 => 102,
        11104 => 102,
        204 => 214,
        214 => 204,
        21401 => 204,
        21402 => 204,
        21403 => 204,
        21404 => 204,
    );

    /**
     * 获取主材、辅材cate对应关系
     * @return array
     */
    public static function getCateRelationOfRecommend()
    {
        return self::$CATE_RELATION_LIST;
    }

    public static function getOrderOperateNote()
    {
        return array(
            'nopprice', //打印订单不显示价格
            'ffee',
            'cfee',
            'prfee'
        );
    }
}
