<?php
/**
 * 用户配置.
 */

class Conf_User
{
	/**
	 * 销售绩效在kvdb中的key格式. salesmanpfm_$date_$cid
	 */
	const KEYTMPL_SALESMAN_PERFORMANCE = 'salesmanpfm_%s_%d';

    /**
     * 客户推荐品牌在kvdb中的key格式. customer_brand_$cid_$cityid
     */
    const KEYTMPL_CUSTOMER_BRAND = 'customer_brand_%d_%d';

    /**
     * 合作客户来源
     */
    const SRC_OPEN_COOPERATION = 50;
    
	/**
	 * 客户介绍来源.
	 */
	public static $Introduce_Source = array(
		1 => '街边店',
		2 => '新小区',
		3 => '亲朋推荐',
		4 => '工长聚集地', 
		5 => '客户转介绍',
		6 => '电销网销',
		7 => '媒体广告',
		8 => '工地',
		9 => '老小区',
        
        self::SRC_OPEN_COOPERATION => '第三方合作',   //第三方合作用户，通过openapi接口下单注册
		99 => '其他',
	);
	
	/**
	 * 客户在竞争对手的下单情况描述.
	 */
	public static $Desc_In_Rival = array(
		1 => '竞争对手没涉及',
		2 => '竞争对手影响较弱',
		3 => '竞争对手在抢客户',
	);
    
    
    /**
     * 客户类型
     */
//    const CUSTOMER_KIND_ALL = 0,
//          CUSTOMER_KIND_UNORDERED = 1,
//          CUSTOMER_KIND_ORDERED = 2;
//    
//    public static $Customer_KINDs_Desc = array(
//        self::CUSTOMER_KIND_ALL         => '全部',
//        self::CUSTOMER_KIND_UNORDERED   => '未下单客户',
//        self::CUSTOMER_KIND_ORDERED     => '已下单客户',
//    );
    
    /**
     * 用户注册来源
     */
    
    const CUSTOMER_REG_SHOP     = 101,
          CUSTOMER_REG_CS       = 102,
          CUSTOMER_REG_SALER    = 103,
          CUSTOMER_REG_APP_ANDROID  = 104,
          CUSTOMER_REG_APP_IOS      = 105,
          CUSTOMER_REG_QINGYUN      = 106;  //清运APP
    
    public static $Customer_Reg_Source = array(
        self::CUSTOMER_REG_SHOP         => '商城',
        self::CUSTOMER_REG_CS           => '客服',
        self::CUSTOMER_REG_SALER        => '销售',
        self::CUSTOMER_REG_APP_ANDROID  => 'Android',
        self::CUSTOMER_REG_APP_IOS      => 'IOS',
        self::CUSTOMER_REG_QINGYUN      => '清运APP',
    );


    
    /**
     * 客户身份.
     */
    
    const CRM_IDENTITY_PERSONAL     = 1,
          CRM_IDENTITY_COMPANY      = 2,
          CRM_IDENTITY_NEW          = 3;
    
    public static $Crm_Identity = array(
        self::CRM_IDENTITY_PERSONAL     => '工长',
        self::CRM_IDENTITY_COMPANY      => '公司',
        self::CRM_IDENTITY_NEW          => '新客户',
    );
    
    /**
     * 客户级别（销售标注 CRM一期）.
     */
    
    const SALER_LEVEL_NEW             = 1,
          SALER_LEVEL_FIRST           = 2,
          SALER_LEVEL_CASH_SALE       = 3,
          SALER_LEVEL_GOOD            = 4,
          SALER_LEVEL_CASH_CONTRACT   = 5,
          SALER_LEVEL_CHARGE_CONTRACT = 6;
    
    public static $Crm_Level_BySaler = array(
        self::SALER_LEVEL_NEW           => 'L0(未下单客户)',
        self::SALER_LEVEL_FIRST         => 'L1(首单客户)',
        self::SALER_LEVEL_CASH_SALE     => 'L2(现销客户)',
        self::SALER_LEVEL_GOOD          => 'L3(优质客户)',
        self::SALER_LEVEL_CASH_CONTRACT => 'L4(现销合同客户)',
        self::SALER_LEVEL_CHARGE_CONTRACT => 'L5(赊销合同客户)'
    );
	
    /**
     * 客户销售级别（CRM二期）
     */
    const SALES_LEVEL_NOCALL        = 0,
          SALES_LEVEL_NOINTEND      = 1,
          SALES_LEVEL_INTEND        = 2,
          SALES_LEVEL_WILLORDER     = 3,
          SALES_LEVEL_HADORDER      = 4;
    
    public static $Crm_Sales_Levels = array(
        self::SALES_LEVEL_NOCALL     => '未打电话',
        self::SALES_LEVEL_NOINTEND   => '没有意向',
        self::SALES_LEVEL_INTEND     => '有意向',
        self::SALES_LEVEL_WILLORDER  => '准备下单',
        self::SALES_LEVEL_HADORDER   => '已下单',
    );
    
    public static $Grouped_Sales_Levels = array(
        Conf_Admin::JOB_KIND_TELE_SALE => array(self::SALES_LEVEL_NOCALL, self::SALES_LEVEL_NOINTEND),  //电销
        Conf_Admin::JOB_KIND_BDS_SALE  => array(self::SALES_LEVEL_NOCALL, self::SALES_LEVEL_NOINTEND, self::SALES_LEVEL_INTEND, self::SALES_LEVEL_WILLORDER), //BDS
        Conf_Admin::JOB_KIND_BMS_SALE  => array(self::SALES_LEVEL_HADORDER), //BMS
    );
    
    /**
     * 客户运营规则 - 销售.
     */
    public static $Crm_Level_BySaler_Rule = array(
        
    );
    
    /**
     * 客户所处的销售状态.
     */
    
    const CRM_SALE_ST_PRIVATE       = 1,
          CRM_SALE_ST_PUBLIC        = 2,
          CRM_SALE_ST_INNER         = 3,
          CRM_SALE_ST_HALFFREE      = 4,
    
            
          CRM_SALE_ST_ABANDON       = 99;
    
    public static $Customer_Sale_Status = array(
        self::CRM_SALE_ST_PRIVATE           => '私海',
        self::CRM_SALE_ST_PUBLIC            => '公海',
        self::CRM_SALE_ST_INNER             => '内海',      //仅总监可见
        self::CRM_SALE_ST_HALFFREE          => '待分配',    //由于市场人员离职等原因遗留客户
        
        
        self::CRM_SALE_ST_ABANDON           => '无效客户',
    );
    
    /**
     * 客户的系统级别
     */
    
    const
          CRM_SYS_LEVEL_WORK        = 6,
          CRM_SYS_LEVEL_VIP         = 5,
          CRM_SYS_LEVEL_BETTER      = 4,
          CRM_SYS_LEVEL_COMMON      = 3,
          CRM_SYS_LEVEL_OBSERVE     = 2,
          CRM_SYS_LEVEL_BAD         = 1;
    
    public static $Customer_Sys_Level_Descs = array(
        self::CRM_SYS_LEVEL_WORK        => '工装客户',
        self::CRM_SYS_LEVEL_VIP         => 'VIP客户',
        //self::CRM_SYS_LEVEL_BETTER      => '优质客户',
        self::CRM_SYS_LEVEL_COMMON      => '普通客户',
        //self::CRM_SYS_LEVEL_OBSERVE     => '待观察客户',
        self::CRM_SYS_LEVEL_BAD         => '恶劣客户',
    );
    
    
    // 企业用户类别
    const 
        BUSINESS_SYS_LEVEL_WORK = 6,
        BUSINESS_SYS_LEVEL_COMMON = 3;
    
    public static $Business_Sys_Level_Descs = array(
        self::BUSINESS_SYS_LEVEL_COMMON => '普通企业',
        self::BUSINESS_SYS_LEVEL_WORK => '工装企业',
    );
    
    
    /**
     * 客户回访/跟踪的类型.
     */
    //1-销售添加回访记录；2-财务催账添加的记录; 3-客服售后回访
    const CT_SALE_RECORD            = 1,
          CT_FINANCE_RECORD         = 2,
          CT_CSERVICE_RECORD        = 3,
          CT_BIND_USER              = 4,
          CT_UNBIND_USER            = 5,
          CT_CHG_SALE_ST            = 9,
          CT_CHG_SALE_LEVEL         = 10,
          CT_MERGE_CUSTOMER         = 11,
          CT_CUSTOMER_LEVEL         = 12;
    
    
    public static $Customer_Tracking_Types = array(
        self::CT_SALE_RECORD        => '销售回访',
        self::CT_FINANCE_RECORD     => '财务记录',
        self::CT_CSERVICE_RECORD    => '售后回访',
        self::CT_BIND_USER          => '绑定用户',
        self::CT_UNBIND_USER        => '解绑用户',
        self::CT_CHG_SALE_ST        => '修改销售状态',
        self::CT_CHG_SALE_LEVEL     => '修改销售级别',
        self::CT_MERGE_CUSTOMER     => '客户合并',
        self::CT_CUSTOMER_LEVEL     => '客户类别',
    );

    /**
     * 客户的活动级别定义
     */
    const AT_NEW_LEVEL    = 1,
          AT_OLD_LEVEL    = 2,
          AT_VIP_LEVEL    = 3;

    public static $Activity_User_Types = array(
        self::AT_NEW_LEVEL    =>'新用户',
        self::AT_OLD_LEVEL    =>'老用户',
        self::AT_VIP_LEVEL    =>'VIP用户',
    );
    
    /*
     * 会员等级
     */
    const Member_Bronze  = 1,
          Member_Silver  = 2,
          Member_Glod    = 3,
          Member_Diamond = 4;
    public static function getMemberGrade()
    {
        return array(
            self::Member_Bronze  => '铜牌会员',
            self::Member_Silver  => '银牌会员',
            self::Member_Glod    => '黄金会员',
            self::Member_Diamond => '钻石会员',
        );
    }
    
    public static function consumeInterval4MemberGrade()
    {
        return array(
            self::Member_Bronze  => array('min'=>0,     'max'=>5000),
            self::Member_Silver  => array('min'=>5000,  'max'=>10000),
            self::Member_Glod    => array('min'=>10000, 'max'=>25000),
            self::Member_Diamond => array('min'=>25000, 'max'=>-1),
        );
    }


    /**
     * 会员等级对应的积分系数.
     */
    public static function getCoeff4MemberGrade($grade='')
    {
        $coeffs = array(
            self::Member_Bronze  => 0.5,
            self::Member_Silver  => 0.7,
            self::Member_Glod    => 1,
            self::Member_Diamond => 1.5,
        );
        
        if (!empty($grade) && array_key_exists($grade, self::getMemberGrade()))
        {
            return $coeffs[$grade];
        }
        else 
        {
            return 0;
        }
    }
    
    /**
     * 无积分的客户.
     */
    public static function isNoPointCustomers($cid)
    {
        $cids = array(
            17707, 10344, 8142,  64140,
            14866, 9938, 10115, 19132, 25255,
            25148, 28702, 28635, 10713, 16668, 9925,  7670,  11618, 6463,  29424, 
            29852, 29854, 29596, 27714, 33450, 40490, 10680, 11808, 33226, 7218,  
            11297, 42144, 41364, 7330,  43090, 43097, 28177, 42598, 9599,  38130, 
            33737, 13712, 22003, 43582, 43553, 41930, 11676, 10978, 14064, 8354, 
            43289, 43660, 43889, 10839, 28311, 19599, 8304,  44545, 44674, 44723,
            44782, 45103, 12876, 39540, 17004, 10397, 16855, 45189, 44625, 50460, 
            9922,  20006, 13740, 10280, 28326, 42961, 14049, 17562, 14430, 41946, 
            8305,  9311,  14951, 49713, 16858, 20108, 44677, 6755,  11603, 25050, 
            6206 , 9454 , 13419, 9369,  55819, 13788, 14331, 55678, 7550,  51376,
            56651, 19888, 28086, 57451, 10000, 38856, 57938, 36251, 8729 , 16788, 
            58729, 28081, 58881, 17407, 59620, 59171, 17495, 10020, 9841,  59496, 
            22985, 15235, 58869, 37555, 33751, 57342, 64213, 9405,  7824,  58359, 
            64821, 64163, 66458, 66329, 27792, 6214,  43104, 15695, 21104, 67836, 
            10244, 38562, 6964,  13508, 66837, 8214,  46468, 34619, 55890, 26067, 
            26516, 75404, 14233, 74672, 61303, 75669, 13656, 8248,  18259, 54548, 
            56862, 10344, 27510, 44005, 10463, 82244, 27219, 17707, 25033, 52646, 
            75606, 75610, 56804, 45334, 74591, 45939, 45479, 75741, 75494, 51528, 
            82203, 75293, 81443, 82823, 10917, 13566, 13271, 17004, 27265, 70130, 
            7293,  8142,  82999, 30940, 9859 , 81024, 64536,
            84922, 64999, 55162, 49760, 58483, 55401, 54734,
            88020, 13915, 58451, 59080, 87330, 46306, 83775, 8712, 58869,
            22243, 9551, 25637, 9936, 88715, 42601, 9901, 23263, 12681, 43937, 
            59496, 88985, 88923, 12519, 6318, 16718, 11808, 16855, 83600, 88976
        );
        
        return in_array($cid, $cids)? true: false;
    }
    
	public static $DOUBLE_PRINT_CIDS = array(
		11297
	);
	public static $NO_PRIVILEGE_PRINT_CIDS = array(
		9925
	);

	const CERTIFICATE_NEW = 1;
	const CERTIFICATE_IN_PROCESS = 2;
	const CERTIFICATE_PASS = 3;
	const CERTIFICATE_DENY = -1;

	private static $_CERTIFICATE_DESC = array(
	    self::CERTIFICATE_NEW => '未认证',
        self::CERTIFICATE_IN_PROCESS => '认证中',
        self::CERTIFICATE_PASS => '已认证',
        self::CERTIFICATE_DENY => '认证未通过',
    );

	public static function getCertificationDesc()
    {
        return self::$_CERTIFICATE_DESC;
    }

    const CERTIFICATE_RESULT_UNDEAL = 1;
    const CERTIFICATE_RESULT_PASS = 2;
    const CERTIFICATE_RESULT_DENY = 3;

    private static $_CERTIFICATE_RESULT_DESC = array(
        self::CERTIFICATE_RESULT_UNDEAL => '待处理',
        self::CERTIFICATE_RESULT_PASS => '通过',
        self::CERTIFICATE_RESULT_DENY => '拒绝',
    );

    public static function getCertificationResultDesc()
    {
        return self::$_CERTIFICATE_RESULT_DESC;
    }

    const CERTIFICATE_DEADLINE = '2017-11-30';
}