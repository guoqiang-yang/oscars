<?php

/**
 * 基本配置
 */
class Conf_Base
{
    const
        WEB_TOKEN_EXPIRED = 7776000,    //90天
        APP_TOKEN_EXPIRED = 315360000,    //10年
        WEB_TOKEN_EXPIRED_REMEMBER = 2592000,        //30天
        MOBILE_TOKEN_EXPIRED = 7776000;    //90天
    
    /**
     * Cookie Key.
     */
    const 
        COKEY_VERIFY_SA = '_admin_verify',
        COKEY_SUID_SA   = '_admin_uid',
        COKEY_CITY_SA   = '_admin_city';
    /**
     * 通用status
     */
    const
        STATUS_NORMAL = 0,      //正常
        STATUS_DELETED = 1,     //删除
        STATUS_BANNED = 2,      //封禁
        STATUS_CANCEL = 3,      //取消
        STATUS_OFFLINE = 4,     //下线
        STATUS_WAIT_AUDIT = 5,  //未审核
        STATUS_UN_AUDIT = 6,    //驳回
        STATUS_ALL = 127;       //全部
    
    
    
    
    
    
    
    
    
    
    
    
    /**
     * 付款方式:
     */
    const
        PT_ONLINE = 1,    //在线付款
        PT_CASH = 2,      //现金
        PT_POS = 3,       //POS机刷卡
        PT_TRANSFER = 4,  //银行转账
        PT_WEIXIN = 5,      //微信转账
        PT_WEIXIN_ONLINE = 6,  //在线支付
        PT_ALIPAY = 7,      //支付宝
        PT_BALANCE = 8,      //余额支付
        //PT_WEIXIN_BALANCE = 9,      //微信+余额支付
        //PT_ALIPAY_BALANCE = 10,     //支付宝+余额支付
        PT_CHEQUE = 11,     //支票
        PT_HC_ACCOUNT = 12, //公账户收款
        PT_WEIXIN_NATIVE = 13,  //微信扫码
        PT_HC_ACCOUNT_CMBC_8567 = 14,   //公账户收款-民生8567
        PT_HC_ACCOUNT_RCB_8850 = 15,    //公账户收款-农商8850
        PT_HC_ACCOUNT_BCM_1678 = 16,    //公账户收款-交通1678
        PT_WEIXIN_APP = 17,     //微信App支付
        //PT_WEIXIN_APP_BALANCE = 18,     //微信App+余额支付
        PT_HEMA = 19,       //河马支付
        PT_CREDIT_PAY = 20, //信用支付
        PT_CQ_BASE_CMB_0501 = 21,       //重庆基本户-招商0501
        PT_CD_PUBLIC_0201   = 22,       //成都公户0201
        PT_TJ_LZ_0701 = 23,             //天津乐赞0701
        PT_BJHZ_CCB_0698 = 24,          //北京好住_建行_0698
        PT_QD_CMB_0628 = 25,            //青岛招商-0628
        PT_FRANCHIEE_PAY = 98,          //加盟商支付
        PT_PAY_BACK = 99,   //客户补偿
        PT_RESERVE_FUND = 101;  //备用金，司机结算使用

    const PRIVATE_SPDB_8336 = 103,
        PRIVATE_CCB_5306 = 104,
        PRIVATE_CCB_1129 = 105,
        PRIVATE_SPDB_0735 = 106,
        PRIVATE_CMBC_0957 = 107,
        PUBLIC_CMBC = 108;
    
    const CUSTOMER_PT_ONLINE_PAY = 1,     //在线支付（客户端使用）
        CUSTOMER_PT_OFFLINE_PAY = 2;    //货到付款（客户端使用）

    static $COOPWORKER_PAYMENT_TYPES = array(
        self::PT_CASH => '现金',
        self::PT_WEIXIN => '微信转账',
        self::PT_RESERVE_FUND => '备用金',
    );
    /**
     * 仓库司机结款.
     */
    public static $BANK_2_WAREHOUSE_DRIVER_FEE = array(
        Conf_Warehouse::WID_3 => self::PRIVATE_SPDB_8336,
        //Conf_Warehouse::WID_4 => self::PRIVATE_CCB_5306,
        Conf_Warehouse::WID_4 => self::PRIVATE_CMBC_0957,
        Conf_Warehouse::WID_5 => self::PRIVATE_SPDB_0735,
    );
    public static $BANK_DESC = array(
        self::PRIVATE_SPDB_8336 => '浦发私户-8336',
        //2017年不使用
        self::PRIVATE_CMBC_0957 => '民生-0957',
        //2017年不使用
        self::PRIVATE_CCB_5306 => '中信-5306',
        //2017年不使用
        self::PRIVATE_CCB_1129 => '中信-1129',
        //2017年不使用
        self::PRIVATE_SPDB_0735 => '浦发私户-0735',
        //2017年不使用
        self::PUBLIC_CMBC => '民生公户',
        self::PT_CQ_BASE_CMB_0501 => '招商基本户0501_渝',
        self::PT_CD_PUBLIC_0201 => '成都公户0201',
        self::PT_TJ_LZ_0701     => '天津乐赞0701',
        self::PT_FRANCHIEE_PAY  => '加盟商支付',
        self::PT_QD_CMB_0628    => '青岛招商-0628',
    );

    public static function getCoopWorkerPayentTypes($wid = 0, $getAll = FALSE)
    {
        $base = array(
            self::PT_CASH => self::$PAYMENT_TYPES[self::PT_CASH],
            self::PT_WEIXIN => self::$PAYMENT_TYPES[self::PT_WEIXIN],
            self::PT_TRANSFER => self::$PAYMENT_TYPES[self::PT_TRANSFER],
        );

        if ($getAll)
        {
            foreach (self::$BANK_DESC as $bankId => $desc)
            {
                $base[$bankId] = $desc;
            }
        }
        else if (array_key_exists($wid, self::$BANK_2_WAREHOUSE_DRIVER_FEE))
        {
            $bankId = self::$BANK_2_WAREHOUSE_DRIVER_FEE[$wid];
            $base[$bankId] = self::$BANK_DESC[$bankId];
        }

        return $base;
    }



    /**
     * @var array 付款方式
     */
    static $PAYMENT_TYPES = array(
        //1 => '在线付款',
        2 => '现金',
        3 => 'POS机刷卡',
        4 => '银行转账',
        5 => '微信转账',
        6 => '微信支付',
        7 => '支付宝支付',
        8 => '余额支付',
        //9 => '微信+余额支付',
        //10 => '支付宝+余额支付',
        11 => '支票',
        12 => '公账户转账',     //公户-民生8567
        //13 => '微信扫码',
        14 => '公户-民生8567',
        15 => '公户-农商8850',
        16 => '公户-交通1678',
        17 => '微信App支付',
        //18 => '微信App+余额支付',
        19 => '河马支付',
        20 => '信用支付',
        21 => '招商基本户0501_渝',
        22 => '成都公户0201',
        23 => '天津乐赞0701',
        24 => '北京好住_建行_0698',
        25 => '青岛招商-0628',
        
        98 => '加盟商支付',
        99 => '补偿专用',
    );
    static $CUSTOMER_PAYMENT_TYPE = array(
        self::CUSTOMER_PT_ONLINE_PAY => '在线支付',
        self::CUSTOMER_PT_OFFLINE_PAY => '货到付款',
    );
    /**
     * 第三方合作用户类型
     */
    const
        COOPWORKER_DRIVER = 1,  //司机
        COOPWORKER_CARRIER = 2; //搬运工
    const DUTY = 0.04;          //税点
    const DUTY_20161129 = 0.08;     //新税点
    const NEW_DUTY_START = '2016-11-29 11:10:00';
    const HAS_DUTY = 1;
    const NO_DUTY = 2;
    /**
     * token的身份标识.
     *
     * 标记token角色.
     */
    const
        TOKEN_TYPE_STAFF = 's',  //HC员工
        TOKEN_TYPE_CUSTOMER = 'c',  //客户
        TOKEN_TYPE_DRIVER = 'd',  //司机
        TOKEN_TYPE_CARIER = 'ca', //搬运工
        TOKEN_TYPE_BUSINESS = 'b';  //企业
    const AMAP_KEY = 'dfa22b02695ad2ce1044d0bde79b35bf';
    const BAIDU_KEY = '7d2cfe641388eff3b681c534fb7ff1e5';
    const APP_SECRET = ' 08:09:07';

    public static function getCssJsHost()
    {
        return C_H5_IMG_HOST;
    }

    public static function getMainHost()
    {
        return C_H5_MAIN_HOST;
    }

    public static function getDriverHost()
    {
        return 'm.co.haocaisong.cn';
    }

    public static function getBaseHost()
    {
        return BASE_HOST;
    }

    public static function getAdminHost()
    {
        return ADMIN_HOST;
    }

    public static function getCoopworkerHost()
    {
        return COOPWORDER_H5_HOST;
    }

    public static function getQiyeHost()
    {
        return QY_HOST;
    }

    public static function getAdminH5Host()
    {
        return ADMIN_HOST_H5;
    }

    public static function getPaymentTypes()
    {
        return self::$PAYMENT_TYPES;
    }

    public static function getCustomerStatus()
    {
        $arr = array(
            self::STATUS_NORMAL => '正常',
            self::STATUS_WAIT_AUDIT => '未审核',
            self::STATUS_BANNED => '封禁',
            self::STATUS_DELETED => '删除',
            self::STATUS_ALL => '全部',
        );

        return $arr;
    }

    public static $STATUS = array(
        0 => '正常',
        1 => '已删除',
        2 => '封禁',
        3 => '已取消',
    );
    const TYPE_TOUCHUAN = 0;
    const TYPE_NOTICE = 1;
    public static $DEVICE_TYPE = array(
        'android' => 3,
        'ios' => 4,
    );

    public static function getStatus($status)
    {
        assert(isset(self::$STATUS[$status]));

        return self::$STATUS[$status];
    }

    public static function getOrderStatusList()
    {
        return array(

            self::STATUS_NORMAL => '正常',
            self::STATUS_DELETED => '已删除',
            self::STATUS_CANCEL => '已取消',
            self::STATUS_ALL => '所有',
        );
    }

    public static function getCoopworkerTypes()
    {
        return array(
            self::COOPWORKER_DRIVER => '司机',
            self::COOPWORKER_CARRIER => '搬运工',
        );
    }

    public static function getCoopworkerFeeTypes()
    {
        return array(
            self::COOPWORKER_DRIVER => '运费',
            self::COOPWORKER_CARRIER => '搬运费',
        );
    }

    public static $HOT_SEARCH = array(
        '美巢墙锢',
        '伟星PPR',
        '好材线管',
        '袋装沙子',
        '轻体砖',
        '钻牌水泥',
        '小猫电线'
    );

    public static function getSupplierStatusList()
    {
        return array(
            self::STATUS_WAIT_AUDIT => '待审核',
            self::STATUS_NORMAL => '审核通过',
            self::STATUS_UN_AUDIT => '已驳回',
            self::STATUS_OFFLINE => '停用',
        );
    }

    public static function getInOrderStatusList($status = '')
    {
        $list = array(
            self::STATUS_NORMAL => '审核通过',
            self::STATUS_WAIT_AUDIT => '待审核',
            self::STATUS_UN_AUDIT => '已驳回',
        );

        $ret = empty($status)? $list: $list[$status];
        return $ret;
    }

    /**
     * 获取在线支付方式
     *
     * @return array
     */
    public static function getOnlinePaymentType()
    {
        return array(
            Conf_Base::PT_ALIPAY,
            Conf_Base::PT_BALANCE,
            Conf_Base::PT_WEIXIN_ONLINE,
//            Conf_Base::PT_WEIXIN_BALANCE,
//            Conf_Base::PT_ALIPAY_BALANCE,
            Conf_Base::PT_WEIXIN_APP,
//            Conf_Base::PT_WEIXIN_APP_BALANCE,
        );
    }

    /**
     * 经营模式
     */

    const MANAGING_MODE_SELF = 1,
          MANAGING_MODE_POOL = 2;

    public static function getManagingModes()
    {
        return array(
            self::MANAGING_MODE_SELF => '自营',
            self::MANAGING_MODE_POOL => '联营',
        );
    }

    /**
     * 经营模式开关，联营上线后使用
     */

    public static function switchForManagingMode()
    {
        return true;
    }
    
    public static function getDuty($cid=0)
    {
        // cid => duty
        $specalDuty = array(
            '91627' => 0.04,
            '91741' => 0.04,
            '94966' => 0.04,
            '94968' => 0.04,
            '99097' => 0.04,
            '99603' => 0.04,
            '103129' => 0.04,
        );
        
        $baseDuty = date('Y-m-d H:i:s')>=self::NEW_DUTY_START? self::DUTY_20161129: self::DUTY;
        
        return array_key_exists($cid, $specalDuty)? $specalDuty[$cid]: $baseDuty;
    }

    /**
     * 文章类型
     */
    public static function articlePolicyType()
    {
        return array(
            self::TYPE_OPERATION_ACT => '运营活动',
            self::TYPE_UPSTAIRS_POL=> '上楼政策',
            self::TYPE_REGISTRATION_AGR=>'注册协议',
            self::TYPE_SERVICE_POL=>'服务政策',
            self::TYPE_FAVOURED_POL=>'优惠政策',
            self::TYPE_DISTRIBUTION_POL=>'配送政策',
            self::TYPE_AFTERSALE_POL=>'售后服务',
        );
    }

}
