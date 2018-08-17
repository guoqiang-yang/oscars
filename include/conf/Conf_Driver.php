<?php

/**
 * 司机配置
 */
class Conf_Driver
{
    const CAR_MODEL_SANLUN = 1,                 //三轮
          CAR_MODEL_XIAOXINGMIANBAO = 2,        //小型面包
          CAR_MODEL_ZHONGXINGMIANBAO = 3,       //中型面包
          CAR_MODEL_PINGDINGJINBEI = 4,         //平顶金杯
          CAR_MODEL_XIAOXINGXIANGHUO = 5,       //小型箱货
          CAR_MODEL_DAXINGXIANGHUO = 6,         //大型箱货
          CAR_MODEL_GAODINGJINBEI = 7,          //高顶金杯
          CAR_MODEL_XIAOXINGPINGBAN = 8,        //小型平板
          CAR_MODEL_YIWEIKE = 9,                //依维柯
          CAR_MODEL_ZHONGXINGPINGBAN = 10,      //中型平板
          CAR_MODEL_DAXINGPINGBAN = 11,         //大型平板
          CAR_MODEL_DIANDONGPINGBAN = 12;       //电动平板

	/**
	 * @var array 车型
	 */
	public static $CAR_MODEL = array(
		4 => '金杯',
        7 => '高顶金杯',
		2 => '小面',
		1 => '三轮',
		3 => '中面',
		5 => '小型箱货',
		6 => '大型箱货',
        8 => '小型平板',
        9 => '依维柯',
        10 => '中型平板',
        11 => '大型平板',
        12 => '电动平板',
	);

    /**
     * 司机配送范围.
     * 
     * @var 行政区号#xx 如果这有行政区号，#及其后面省略 
     */
    public static $TRANS_SCOPES = array(
        Conf_City::BEIJING => array(
            '110#2' => '二环',
            '110#3' => '三环',
            '13109' => '廊坊',
            '120'   => '天津',
        ),
    );
    
    public static $BJ_Limit_Car = array(
        '110#2', '110#3',
    );

	/**
	 * @var array 司机来源
	 */
	static $DRIVER_SOURCE = array(
		2 => '58',
		4 => '个体',
		3 => '云鸟',
		1 => '好材',
	);


	static $CAN_CARRY = array(
		1 => '不搬运',
		2 => '不搬沙石',
		3 => '都可搬运',
	);

	static $CAR_CODE = array(
		1 => '1',
		2 => '2',
		3 => '3',
		4 => '4',
		5 => '5',
		6 => '6',
		7 => '7',
		8 => '8',
		9 => '9',
		10 => '0',
	);

	static $CAR_PROVINCES = array('京', '津', '冀', '渝', '川', '鄂', '豫','鲁', '皖', '晋', '吉', '蒙', '辽', '陕', '粤');

	const STEP_EMPTY = 0;
	const STEP_CHECK_IN = 1;
	const STEP_ALLOC = 2;
	const STEP_ACCEPT = 3;
	const STEP_LEAVE = 4;
	const STEP_ARRIVE = 5;

	public static $STEP_DESC= array(
		self::STEP_CHECK_IN => '已签到',
		self::STEP_ALLOC => '已派单',
		self::STEP_ACCEPT => '已接单',
		self::STEP_LEAVE => '已出库',
		self::STEP_ARRIVE => '已送达',
	);

    const MSG_NEW_ORDER = 1;//新订单
    const MSG_REFUSE = 2;//拒单
    const MSG_SEND = 3;//发车

    //司机消息推送
    public static $MSG_PUSH = array(
      self::MSG_NEW_ORDER => array('title' => '您有新订单啦～', 'desc' => '点我去领取订单吧'),
      self::MSG_REFUSE => array('title' => '您的订单已超时～', 'desc' => '点我去查看详情吧'),
      self::MSG_SEND => array('title' => '您的订单待发车～', 'desc' => '点我去确认发车吧'),
    );

    /**
     *能添加修改司机信息的调度人员。
     *
     */
    public static $DRIVER_INFO_EDITOR = array(

        '1178' => '王杨',
        '1244' => '于小虎',
        //'1245' => '宋悦',
        '1204' => '蔺国强',
        '1035' => '齐晨阳',
        '1008' => '刘江涛',
        '1221' => '苏祖理',
        '1210' => '孟德超',
        '1331' => '王广英',
        '1119' => '皮特芳',
    );

	const MAX_REFUSE_NUM = 2;
	const CHECK_IN_DISTANCE = 500;
	const ARRIVE_DISTANCE = 3000;
	const ACCEPT_MAX_INTERVAL = 600;   //10 minutes

    // 获取车牌的最后一位
    public static function getCarLastNum($carCode)
    {
        if (intval($carCode)==-1 || empty($carCode))
        {
            return -1;
        }
        
        $lastCarNumber = substr($carCode, -1, 1);
        
        if (!is_numeric($lastCarNumber))
        {
            $lastCarNumber = 0;
        }
        
        return $lastCarNumber;
    }
    
    /**
     * 车辆今天是否限行.
     * 
     * @param type $lastCarNumber 车牌号 or 车牌尾号
     */
    public static function isLimitCar($lastCarNumber, $deliveryDate='')
    {
        // 2017年假日 格式：YYYYMMDD
        $holiday = array('20161001');
        
        // 限行配置：车牌尾号 => 星期
        $limitConf = array(
            5 => 3,
            0 => 3,
            6 => 4,
            1 => 4,
            7 => 5,
            2 => 5,
            8 => 1,
            3 => 1,
            9 => 2,
            4 => 2,
        );
        
        // 未配置车牌尾号，不限行
        if ($lastCarNumber == -1 || empty($lastCarNumber))
        {
            return false;
        }
        
        // 假期不限行
        $day = date('Ymd', strtotime($deliveryDate) );
        
        if (in_array($day, $holiday))
        {
            return false;
        }
        
        $lastCarNumber = substr($lastCarNumber, -1, 1);
        
        if (!is_numeric($lastCarNumber))
        {
            $lastCarNumber = 0;
        }
        
        if (!empty($deliveryDate))
        {
            $week = date('w', strtotime($deliveryDate) );
        }
        else
        {
            $week = date('w');
        }
        
        return $week==$limitConf[$lastCarNumber]? true: false;
    }

    /**
     * 通过调度排线判断是否限行.
     * 
     * @param type $driverInfo
     * @param type $lineInfo
     * @return boolean
     */
    public static function isLimitForOrderLine($driverInfo, $lineInfo)
    {
        $oo = new Order_Order();
        $oids = explode(',', $lineInfo['oids']);
        $cmids = array_unique(Tool_Array::getFields($oo->getBulk($oids, array('*')), 'community_id'));
        
        $oc = new Order_Community();
        $communityInfos = $oc->getBulk($cmids);
        $ringRoads = Tool_Array::getFields($communityInfos, 'ring_road');

        $cityId = Conf_Warehouse::$WAREHOUSE_CITY_MAPPING[$lineInfo['wid']];
        $limitArea = Conf_Area::getLimitArea($cityId);

        $isLimitCar = Conf_Driver::isLimitCar($driverInfo['car_code'], $lineInfo['delivery_date']);
        $unLimitRoads = array_intersect($ringRoads, $limitArea);
        
        // 限行不能进入限行区域
        if ($isLimitCar && !empty($unLimitRoads)) 
        {
            return false;
        }
        
        return true;
    }
    
    const DRIVER_FEE_TYPE_TRASH = 1,        //拉垃圾
          DRIVER_FEE_TYPE_SECOND_RING = 2,  //二环订单
          DRIVER_FEE_TYPE_REWARD = 3,       //奖励
          DRIVER_FEE_TYPE_FINE = 4,         //处罚
          DRIVER_FEE_TYPE_OTHER = 5;        //其他费用

    //附加运费类型
    public static $DRIVER_FEE_TYPES = array(
        self::DRIVER_FEE_TYPE_TRASH => '拉垃圾',
        self::DRIVER_FEE_TYPE_SECOND_RING => '二环订单',
        self::DRIVER_FEE_TYPE_REWARD => '奖励',
        self::DRIVER_FEE_TYPE_FINE => '处罚',
        self::DRIVER_FEE_TYPE_OTHER => '其他费用',
    );

    //拼单,第二单起各车型每单运费（单位：分）
    public static $SECOND_ORDER_PRICE = array(
        1 => 0,
        2 => 1000,
        3 => 1000,
        4 => 2000,
        5 => 0,
        6 => 0,
        7 => 2000,
        8 => 0,
    );

    //一单多趟，第二趟起每趟运费减少金额数(单位：分)
    public static $SECOND_TIMES_DECLINE_PRICE = array(
        Conf_City::BEIJING => array(
            1 => 2000,
            2 => 2000,
            3 => 2000,
            4 => 3000,
            5 => 2000,
            6 => 2000,
            7 => 3000,
            8 => 2000,
        ),
        Conf_City::TIANJIN => array(
            1 => 2250,
            2 => 2250,
            3 => 2250,
            4 => 3500,
            5 => 2250,
            6 => 2250,
            7 => 3500,
            8 => 2250,
        ),
    );

    const DRIVER_FEE_RULE_1 = 1,
          DRIVER_FEE_RULE_2 = 2,
          DRIVER_FEE_RULE_3 = 3,
          DRIVER_FEE_RULE_4 = 4,
          DRIVER_FEE_RULE_5 = 5;

    public static $DRIVER_FEE_RULES = array(
        self::DRIVER_FEE_RULE_1 => array(
            self::CAR_MODEL_SANLUN => array(
                'base'    => 4000,           //起步价（5公里）单位：分
                'increase'   => 400,         //超过5公里，但是不超过15公里，每公里增加的运费 单位：分
                'decline' => 0,              //超过15公里，每公里增加的运费减少金额 单位：分
                'min_increase' => 0,          //超过15公里，每公里增加的运费最低限 单位：分
                'decline_fee' => 2000,       //一单多趟，第二趟起，每趟减少的运费 单位：分
                'second_order_fee' => 1000,  //拼单,第二单起各车型每单运费（单位：分）
            ),
            self::CAR_MODEL_XIAOXINGMIANBAO => array(
                'base'    => 4000,
                'increase'   => 400,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 2000,
                'second_order_fee' => 1000,
            ),
            self::CAR_MODEL_ZHONGXINGMIANBAO => array(
                'base'    => 4800,
                'increase'   => 450,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 2250,
                'second_order_fee' => 1000,
            ),
            self::CAR_MODEL_PINGDINGJINBEI => array(
                'base'    => 6000,
                'increase'   => 600,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 3000,
                'second_order_fee' => 2000,
            ),
            self::CAR_MODEL_XIAOXINGXIANGHUO => array(
                'base'    => 4800,
                'increase'   => 400,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 2000,
                'second_order_fee' => 1000,
            ),
            self::CAR_MODEL_DAXINGXIANGHUO => array(
                'base'    => 7000,
                'increase'   => 600,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 3000,
                'second_order_fee' => 2000,
            ),
            self::CAR_MODEL_GAODINGJINBEI => array(
                'base'    => 6000,
                'increase'   => 600,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 3000,
                'second_order_fee' => 2000,
            ),
            self::CAR_MODEL_XIAOXINGPINGBAN => array(
                'base'    => 4800,
                'increase'   => 450,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 2250,
                'second_order_fee' => 1000,
            ),
            self::CAR_MODEL_YIWEIKE => array(
                'base'    => 6000,
                'increase'   => 600,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 3000,
                'second_order_fee' => 2000,
            ),
            self::CAR_MODEL_ZHONGXINGPINGBAN => array(
                'base'    => 5000,
                'increase'   => 450,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 2250,
                'second_order_fee' => 1000,
            ),
            self::CAR_MODEL_DAXINGPINGBAN => array(
                'base'    => 7000,
                'increase'   => 600,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 3000,
                'second_order_fee' => 2000,
            ),
        ),
        self::DRIVER_FEE_RULE_2 => array(
            self::CAR_MODEL_XIAOXINGMIANBAO => array(
                'base'    => 3500,
                'increase'   => 350,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 1750,
                'second_order_fee' => 1000,
            ),
            self::CAR_MODEL_ZHONGXINGMIANBAO => array(
                'base'    => 4000,
                'increase'   => 350,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 1750,
                'second_order_fee' => 1000,
            ),
            self::CAR_MODEL_PINGDINGJINBEI => array(
                'base'    => 6000,
                'increase'   => 500,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 2500,
                'second_order_fee' => 2000,
            ),
            self::CAR_MODEL_XIAOXINGXIANGHUO => array(
                'base'    => 4500,
                'increase'   => 350,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 1750,
                'second_order_fee' => 1000,
            ),
            self::CAR_MODEL_DAXINGXIANGHUO => array(
                'base'    => 7000,
                'increase'   => 550,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 2750,
                'second_order_fee' => 2000,
            ),
            self::CAR_MODEL_GAODINGJINBEI => array(
                'base'    => 6000,
                'increase'   => 500,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 2500,
                'second_order_fee' => 2000,
            ),
            self::CAR_MODEL_XIAOXINGPINGBAN => array(
                'base'    => 4500,
                'increase'   => 350,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 1750,
                'second_order_fee' => 1000,
            ),
            self::CAR_MODEL_YIWEIKE => array(
                'base'    => 6000,
                'increase'   => 500,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 2500,
                'second_order_fee' => 2000,
            ),
            self::CAR_MODEL_ZHONGXINGPINGBAN => array(
                'base'    => 4800,
                'increase'   => 400,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 2000,
                'second_order_fee' => 1000,
            ),
            self::CAR_MODEL_DAXINGPINGBAN => array(
                'base'    => 6500,
                'increase'   => 500,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 2500,
                'second_order_fee' => 2000,
            ),
        ),
        self::DRIVER_FEE_RULE_3 => array(
            self::CAR_MODEL_SANLUN => array(
                'base'    => 4000,           //起步价（5公里）单位：分
                'increase'   => 400,         //超过5公里，但是不超过15公里，每公里增加的运费 单位：分
                'decline' => 0,              //超过15公里，每公里增加的运费减少金额 单位：分
                'min_increase' => 0,          //超过15公里，每公里增加的运费最低限 单位：分
                'decline_fee' => 2000,       //一单多趟，第二趟起，每趟减少的运费 单位：分
                'second_order_fee' => 1000,  //拼单,第二单起各车型每单运费（单位：分）
            ),
            self::CAR_MODEL_XIAOXINGMIANBAO => array(
                'base'    => 4000,
                'increase'   => 400,
                'decline' => 20,
                'min_increase' => 300,
                'decline_fee' => 2000,
                'second_order_fee' => 1000,
            ),
            self::CAR_MODEL_ZHONGXINGMIANBAO => array(
                'base'    => 4800,
                'increase'   => 450,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 2250,
                'second_order_fee' => 1000,
            ),
            self::CAR_MODEL_PINGDINGJINBEI => array(
                'base'    => 6000,
                'increase'   => 600,
                'decline' => 30,
                'min_increase' => 400,
                'decline_fee' => 3000,
                'second_order_fee' => 2000,
            ),
            self::CAR_MODEL_XIAOXINGXIANGHUO => array(
                'base'    => 4800,
                'increase'   => 400,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 2000,
                'second_order_fee' => 1000,
            ),
            self::CAR_MODEL_DAXINGXIANGHUO => array(
                'base'    => 7000,
                'increase'   => 600,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 3000,
                'second_order_fee' => 2000,
            ),
            self::CAR_MODEL_GAODINGJINBEI => array(
                'base'    => 6000,
                'increase'   => 600,
                'decline' => 30,
                'min_increase' => 400,
                'decline_fee' => 3000,
                'second_order_fee' => 2000,
            ),
            self::CAR_MODEL_XIAOXINGPINGBAN => array(
                'base'    => 4800,
                'increase'   => 450,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 2250,
                'second_order_fee' => 1000,
            ),
            self::CAR_MODEL_YIWEIKE => array(
                'base'    => 6000,
                'increase'   => 600,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 3000,
                'second_order_fee' => 2000,
            ),
            self::CAR_MODEL_ZHONGXINGPINGBAN => array(
                'base'    => 5000,
                'increase'   => 450,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 2250,
                'second_order_fee' => 1000,
            ),
            self::CAR_MODEL_DAXINGPINGBAN => array(
                'base'    => 7000,
                'increase'   => 600,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 3000,
                'second_order_fee' => 2000,
            ),
        ),
        self::DRIVER_FEE_RULE_4 => array(
            self::CAR_MODEL_PINGDINGJINBEI => array(
                'base'    => 6000,           //起步价（5公里）单位：分
                'increase'   => 500,         //超过5公里，但是不超过15公里，每公里增加的运费 单位：分
                'decline' => 0,              //超过15公里，每公里增加的运费减少金额 单位：分
                'min_increase' => 0,          //超过15公里，每公里增加的运费最低限 单位：分
                'decline_fee' => 2500,       //一单多趟，第二趟起，每趟减少的运费 单位：分
                'second_order_fee' => 2000,  //拼单,第二单起各车型每单运费（单位：分）
            ),
            self::CAR_MODEL_ZHONGXINGMIANBAO => array(
                'base'    => 4000,
                'increase'   => 400,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 1750,
                'second_order_fee' => 1000,
            ),
        ),
        self::DRIVER_FEE_RULE_5 => array(
            self::CAR_MODEL_SANLUN => array(
                'base'    => 4000,           //起步价（5公里）单位：分
                'increase'   => 400,         //超过5公里，但是不超过15公里，每公里增加的运费 单位：分
                'decline' => 0,              //超过15公里，每公里增加的运费减少金额 单位：分
                'min_increase' => 0,          //超过15公里，每公里增加的运费最低限 单位：分
                'decline_fee' => 2000,       //一单多趟，第二趟起，每趟减少的运费 单位：分
                'second_order_fee' => 1000,  //拼单,第二单起各车型每单运费（单位：分）
            ),
            self::CAR_MODEL_XIAOXINGMIANBAO => array(
                'base'    => 3500,
                'increase'   => 400,
                'decline' => 20,
                'min_increase' => 300,
                'decline_fee' => 2000,
                'second_order_fee' => 1000,
            ),
            self::CAR_MODEL_ZHONGXINGMIANBAO => array(
                'base'    => 4800,
                'increase'   => 450,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 2250,
                'second_order_fee' => 1000,
            ),
            self::CAR_MODEL_PINGDINGJINBEI => array(
                'base'    => 5000,
                'increase'   => 600,
                'decline' => 30,
                'min_increase' => 400,
                'decline_fee' => 3000,
                'second_order_fee' => 2000,
            ),
            self::CAR_MODEL_XIAOXINGXIANGHUO => array(
                'base'    => 4800,
                'increase'   => 400,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 2000,
                'second_order_fee' => 1000,
            ),
            self::CAR_MODEL_DAXINGXIANGHUO => array(
                'base'    => 7000,
                'increase'   => 600,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 3000,
                'second_order_fee' => 2000,
            ),
            self::CAR_MODEL_GAODINGJINBEI => array(
                'base'    => 5000,
                'increase'   => 600,
                'decline' => 30,
                'min_increase' => 400,
                'decline_fee' => 3000,
                'second_order_fee' => 2000,
            ),
            self::CAR_MODEL_XIAOXINGPINGBAN => array(
                'base'    => 4800,
                'increase'   => 450,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 2250,
                'second_order_fee' => 1000,
            ),
            self::CAR_MODEL_YIWEIKE => array(
                'base'    => 6000,
                'increase'   => 600,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 3000,
                'second_order_fee' => 2000,
            ),
            self::CAR_MODEL_ZHONGXINGPINGBAN => array(
                'base'    => 4500,
                'increase'   => 450,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 2250,
                'second_order_fee' => 1000,
            ),
            self::CAR_MODEL_DAXINGPINGBAN => array(
                'base'    => 7000,
                'increase'   => 600,
                'decline' => 0,
                'min_increase' => 0,
                'decline_fee' => 3000,
                'second_order_fee' => 2000,
            ),
        ),
    );

    public static $WAREHOUSE_DRIVER_FEE_RULES = array(
        Conf_Warehouse::WID_3 => self::DRIVER_FEE_RULE_3,
        Conf_Warehouse::WID_4 => self::DRIVER_FEE_RULE_3,
        Conf_Warehouse::WID_5 => self::DRIVER_FEE_RULE_5,
        Conf_Warehouse::WID_6 => self::DRIVER_FEE_RULE_3,
        Conf_Warehouse::WID_7 => self::DRIVER_FEE_RULE_3,
        Conf_Warehouse::WID_8 => self::DRIVER_FEE_RULE_3,
        Conf_Warehouse::WID_TJ1 => self::DRIVER_FEE_RULE_2,
        Conf_Warehouse::WID_WH1 => self::DRIVER_FEE_RULE_4,
    );

    //廊坊
    const LF_DISTANCE_WITHIN_FIVE_KM = 1,       //5公里以内
          LF_DISTANCE_MORE_THAN_FIVE_KM = 2;        //5公里以上

    //廊坊各车型运费规则
    public static $LANGFANG_CAR_MODEL_FEE_RULES = array(
        //电动平板，5公里以内一车20，5公里以上一车40
        self::CAR_MODEL_DIANDONGPINGBAN => array(
            self::LF_DISTANCE_WITHIN_FIVE_KM => 2000,
            self::LF_DISTANCE_MORE_THAN_FIVE_KM => 4000,
        ),
        //金杯，每公里5元
        self::CAR_MODEL_PINGDINGJINBEI => 500,
    );

    //天津
    public static $TIANJIN_CAR_MODEL_FEE_RULES = array(
        self::CAR_MODEL_XIAOXINGMIANBAO => array(
            '5km' => 3300,
            '10km' => 4000,
            '15km' => 5500,
            'more_than_15km' => array(
                'base' => 6500,
                'increase' => 300,
            ),
            'second_order_fee' => 1000,
        ),
        self::CAR_MODEL_PINGDINGJINBEI => array(
            '5km' => 5800,
            '10km' => 6800,
            '15km' => 8000,
            'more_than_15km' => array(
                'base' => 9000,
                'increase' => 400,
            ),
            'second_order_fee' => 2000,
        ),
        self::CAR_MODEL_GAODINGJINBEI => array(
            '5km' => 5800,
            '10km' => 6800,
            '15km' => 8000,
            'more_than_15km' => array(
                'base' => 9000,
                'increase' => 400,
            ),
            'second_order_fee' => 2000,
        ),
    );

    //重庆  成都与重庆相差一个小型平板车型
    public static $CHONGQING_CAR_MODEL_FEE_RULES = array(
        self::CAR_MODEL_XIAOXINGMIANBAO => array(
            '10km' => 5000,
            'more_than_10km_per_km' => 400,
            'more_than_20km_per_km' => 300,
            'second_order_fee' => 500,
        ),
        self::CAR_MODEL_PINGDINGJINBEI => array(
            '10km' => 7000,
            'more_than_10km_per_km' => 500,
            'more_than_20km_per_km' => 400,
            'second_order_fee' => 1000,
        ),
        self::CAR_MODEL_ZHONGXINGMIANBAO => array(
            '10km' => 6000,
            'more_than_10km_per_km' => 450,
            'more_than_20km_per_km' => 350,
            'second_order_fee' => 800,
        ),
        self::CAR_MODEL_XIAOXINGPINGBAN => array(
            '10km' => 5500,
            'more_than_10km_per_km' => 450,
            'more_than_20km_per_km' => 350,
            'second_order_fee' => 800,
        ),
    );

    //青岛
    public static $QINGDAO_CAR_MODEL_FEE_RULES = array(
        self::CAR_MODEL_XIAOXINGMIANBAO => array(
            '10km' => 4500,
            'more_than_10km_per_km' => 400,
            'more_than_20km_per_km' => 300,
            'second_order_fee' => 500,
        ),
        self::CAR_MODEL_ZHONGXINGMIANBAO => array(
            '10km' => 5500,
            'more_than_10km_per_km' => 450,
            'more_than_20km_per_km' => 350,
            'second_order_fee' => 800,
        ),
        self::CAR_MODEL_XIAOXINGPINGBAN => array(
            '10km' => 5500,
            'more_than_10km_per_km' => 450,
            'more_than_20km_per_km' => 350,
            'second_order_fee' => 800,
        ),
        self::CAR_MODEL_DAXINGPINGBAN => array(
            '10km' => 6500,
            'more_than_10km_per_km' => 500,
            'more_than_20km_per_km' => 400,
            'second_order_fee' => 1000,
        ),
        self::CAR_MODEL_PINGDINGJINBEI => array(
            '10km' => 6500,
            'more_than_10km_per_km' => 500,
            'more_than_20km_per_km' => 400,
            'second_order_fee' => 1000,
        ),

    );
}
