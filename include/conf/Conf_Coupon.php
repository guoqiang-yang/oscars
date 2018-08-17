<?php

/**
 * 基本配置
 */
class Conf_Coupon
{
	/*
	 * 优惠券类型
	 */
	const
		TYPE_NEW = 1,      //新用户
		TYPE_CASHBACK = 2, //累计消费返点
		TYPE_SALES = 3,    //销售客情维护
		TYPE_MARKETING = 4,//营销活动
		TYPE_RECOMMEND = 5,//推荐客户
		TYPE_ANHUI = 6,    //安徽工长等特殊亲友优惠(历史遗留,现在应该没用了)
		TYPE_BUCHANG = 7,  //因服务问题对客户的补偿
		TYPE_PRE_PAY = 8,   //预付款返VIP现金券
		TYPE_AFTERNOON = 9,   //12-15点配送赠送
		TYPE_WAKEUP = 10,   //12-15点配送赠送
        TYPE_GUOQING = 11,  //国庆活动
		TYPE_TIANJIN_NEW_ORDER = 12,  //天津首单返
        TYPE_DOUBLE11 = 13, //双11活动
		TYPE_FIRST_LOGIN_APP = 14;  //首次登陆app

	public static $couponTypes = array(
		self::TYPE_NEW => '新用户',
		self::TYPE_CASHBACK => '累计消费',
		self::TYPE_SALES => '销售客情维护',
		self::TYPE_MARKETING => '营销活动',
		self::TYPE_RECOMMEND => '客户推荐',
		self::TYPE_ANHUI => '关系客户优惠',
        self::TYPE_BUCHANG => '后台发放',
		self::TYPE_PRE_PAY => '预付款返现',
		self::TYPE_AFTERNOON => '运营配送活动赠送',
		self::TYPE_WAKEUP => '唤醒活动',
        self::TYPE_GUOQING => '国庆活动',
		self::TYPE_TIANJIN_NEW_ORDER => '天津首单返券',
        self::TYPE_DOUBLE11 => '双11活动',
        self::TYPE_FIRST_LOGIN_APP => '首次登陆App',
	);

	/**
	 * 申请优惠券状态
	 */
	const
		ST_UNAUDIT = 1,        //未审核
		ST_REJECT = 2,        //驳回
		ST_PASS = 3;        //批准

	public static $applyCouponStatus = array(
		self::ST_UNAUDIT => '未审核',
		self::ST_REJECT => '驳回',
		self::ST_PASS => '批准',
	);

	const CATE_COUPON_COMMON = 1;        //满减类型优惠券，有订单额度限制，例如满500才能用1张50的优惠券
	const CATE_COUPON_VIP = 2;        //现金券，相当于现金，买即可用，例如买55块钱东西可以用50的现金券
//	const CATE_COUPON_SAND = 3;         //10元券，砂石类都可用
//	const CATE_COUPON_PACKAGE = 4;      //打包的优惠券
//	const CATE_COUPON_AFTERNOON = 5;       //下午配送奖励的优惠券
    const CATE_COUPON_FRIGHT = 3;      //运费券

	public static $Coupon_Rules = array(
		self::CATE_COUPON_COMMON => '满500可使用',
		self::CATE_COUPON_VIP => '不限金额使用',
//		self::CATE_COUPON_SAND => '砂石类可用',
	);

	public static $cateName = array(
		self::CATE_COUPON_COMMON => '现金券',
		self::CATE_COUPON_VIP => 'VIP现金券',
//		self::CATE_COUPON_SAND => '优惠券',
	);

    public static $couponName = array(
        self::CATE_COUPON_COMMON => '优惠券',
        self::CATE_COUPON_VIP => 'VIP现金券',
        self::CATE_COUPON_FRIGHT => '运费券',
    );

	const COMMON_COUPON_PRICE = 50;
	const SAND_COUPON_PRICE = 10;

	//累计消费返现记录中type标记
	const CUMULATIVE_SEND_50000 = 5000000;      //5万（单位分）
	const CUMULATIVE_SEND_200000 = 20000000;    //20万（单位分）

	//累计消费返现阶梯
	public static $CUMULATIVE_STEP = array(
		self::CUMULATIVE_SEND_50000 => array('amount' => 50, 'num' => 10),        //5万返500
		self::CUMULATIVE_SEND_200000 => array('amount' => 50, 'num' => 60),       //20万返3000
	);

	public static $excludeCids = array(
		//百积木
		8248,
		7242,
		7241,
		7240,
		6999,
		7124,
		8921,
		//绿豆家装
		8250,
		8085,
		7906,
		8896,
		9457,
		10352,
		9532,
		9457,
		9378,
		11137,
		11197,
		11356,
		11137,
		11762,
		//3M空间
		9078,
		9052,
		8729,
		9936,
		9570,
		11454,
		//3好同创
		//9711,9254,
		//有返点的公司
		9736,
		//卖家
		8503,
		8733,
		8822,
		8717,
		8923,
		8924,
		9000,
		9346,
		8822,
		11519,
		8327,
		//沙子水泥电线
		//7168,8620,9535,10067,10026,9711,
		//个人回扣
		10343,
		//欠款太多
		7083,
		//坏账
		9920,
		8570,
		//有账期.开发票，加收2个点
		11063,
		12793,
	);

	/**
	 * 打包优惠券
	 * 500元  3张
	 * 300元  2张
	 * 200元  2张
	 * 100元  2张
	 * 60元   2张
	 * 30元   2张
	 */
	public static $PACKAGE_COUPON = array(
		500 => 3,
		300 => 2,
		200 => 2,
		100 => 2,
		60 => 2,
		30 => 2,
	);

	public static $MEILIJIA_COUPON = array(
		500 => 5,
		300 => 4,
		200 => 3,
		100 => 2,
		60 => 4,
		30 => 2,
	);

	/*
	 *  单笔订单满800元，可用1张30元；
		单笔订单满1500元，可用1张60元；
		单笔订单满2500元，可用1张100元；
		单笔订单满4500元，可用1张200元；
		单笔订单满6500元，可用1张300元；
		单笔订单满12000元，可用1张500元；
		Coupon_Coupon::_getCouponAmountByOrderAmount
	 */
	public static $THRESHOLD = array(
		20 => 500,
		30 => 800,
		50 => 1000,
		60 => 1500,
		100 => 2500,
		200 => 4500,
		300 => 6500,
		500 => 12000,
	);

    /*
	 *  单笔订单满300元，可用1张10元；
		单笔订单满400元，可用1张15元；
		单笔订单满500元，可用1张20元；
		单笔订单满800元，可用1张40元；
		单笔订单满1000元，可用1张50元；
        单笔订单满2000元，可用1张100元；
        单笔订单满5000元，可用1张200元；
		单笔订单满10000元，可用1张400元；
		Coupon_Coupon::_getCouponAmountByOrderAmount
	 */
    public static $DOUBLE11 = array(
        10 => 300,
        15 => 400,
        20 => 500,
        40 => 800,
        50 => 1000,
        100 => 2000,
        200 => 5000,
        400 => 10000,
    );


	/**
	 * 短信模板.
	 */
	public static $smsTemplate = array(
		'apply_coupon' => '您领取的%d元优惠券，已发到您的账户，详情请致电 400-058-5788。我们会继续提升服务，谢谢您的支持',
		'new_order' => '好材送您%d元代金券，已发到您的账户。详情请致电 400-058-5788',
		'accumulative' => '尊敬的VIP客户，恭喜您已达到返现标准，请登录好材账户查询返现金额，购买享受多重优惠，详情咨询4001582400退订回t',
	);

	/**
	 * 给一个消费金额，返回总共应该返多少券
	 *
	 * @param $orderAmount 单位:分
	 * @return array
	 */
	public static function getCashback($orderAmount)
	{
		$threshold = 0;
		$cashback = 0;
		$orderAmount = $orderAmount / 100;

		if (empty($threshold))
		{
			$threshold = $orderAmount - $orderAmount % 5000;
		}

		if ($orderAmount >= 40000)
		{
			$factor = 250;
			$cashback += $factor * (floor(($orderAmount - 40000) / 5000) + 1);
			$orderAmount = 39999;
		}
		if ($orderAmount >= 20000)
		{
			$factor = 200;
			$cashback += $factor * (floor(($orderAmount - 20000) / 5000) + 1);
			$orderAmount = 19999;
		}
		if ($orderAmount >= 5000)
		{
			$factor = 150;
			$cashback += $factor * (floor(($orderAmount - 5000) / 5000) + 1);
		}

		$ret = array(
			'threshold' => intval($threshold),
			'cashback' => $cashback,
		);

		return $ret;
	}

	/**
	 * @param $amount 单位:分
	 * @return array
	 */
	public static function createCouponList($amount)
	{
		$num100 = floor($amount / 100);
		$num50 = floor($amount % 100 / 50);

		return array(100 => 0, 50 => $num50 + 2 * $num100);
	}

	/**
	 * @param $orderAmount 单位:分
	 * @return array
	 */
	public static function createNewCustomerCouponList($orderAmount)
	{
		//最多700
		if ($orderAmount > 7000)
			$orderAmount = 7000;

		$num100 = floor($orderAmount / 1000);
		$num40 = $orderAmount % 1000 >= 500 ? 1 : 0;
		$coupons = array(50 => 2 * $num100 + $num40);

		return $coupons;
	}

	public static function getCouponAmountCanUse($orderAmount)
	{
		$num100 = floor($orderAmount / 1000);
		$num50 = $orderAmount % 1000 >= 500 ? 1 : 0;

		return min(100 * $num100 + 50 * $num50, Conf_Order::MAX_COUPON_PRIVILEGE);
	}

	/**
     * 发放VIP现金券
     */
	public static function getVipCouponList()
    {
        return array(
            '1'  => '50元现金券',
            '33' => '30元现金券',
            '34' => '20元现金券',
            '35' => '10元现金券',
            '117' => '5元现金券',
            '118' => '1元现金券',
            '81' => '100元积分优惠券',
            '85' => '50元预存返现现金券',
            '86' => '100元预存返现现金券',
            '87' => '10元合同返点现金券',
            '88' => '20元合同返点现金券',
            '89' => '50元合同返点现金券',
            '90' => '100元合同返点现金券',
            '119' => '北京五月活动10元优惠券',
            '120' => '北京五月活动20元优惠券',
            '121' => '北京五月活动30元优惠券',
            '122' => '北京5月活动50元现金券',
            '123' => '北京5月活动80元现金券',
            '124' => '北京5月活动100元现金券',
            '125' => '北京6月活动100元优惠券(伟星满800赠送)',
        );
    }

}
