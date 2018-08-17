<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 16/7/13
 * Time: 14:50
 */
class Conf_Aftersale
{
	const STATUS_CREATE = 1;
	const STATUS_AFTER_CREATE = 2;
	const STATUS_NEW = 3;
	const STATUS_UNDEAL = 4;
	const STATUS_DEAL = 5;
	const STATUS_FINISH = 6;
	public static $STATUS = array(
		self::STATUS_CREATE=> '已创建',
		self::STATUS_AFTER_CREATE=> '(new)待处理',
		self::STATUS_NEW => '待处理',
		self::STATUS_UNDEAL => '处理中',
		self::STATUS_DEAL => '已处理',
		self::STATUS_FINISH => '已关闭',
	);

	const TYPE_REFUND = 1;
	const TYPE_EXCHANGE = 2;
	const TYPE_ADDON = 3;
	const TYPE_COMPLAIN = 4;
	const TYPE_CALL_BACK = 5;
	const TYPE_OTHER = 6;

	public static $TYPE = array(
		self::TYPE_REFUND => '退货单',
		self::TYPE_EXCHANGE => '换货单',
		self::TYPE_ADDON => '补漏单',
		self::TYPE_COMPLAIN => '投诉',
		self::TYPE_CALL_BACK => '回访',
		self::TYPE_OTHER => '其他',
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
//            12 => '退货单',
//            13 => '换货单',
//            14 => '补漏单',
            15 => '投诉',
            16 => '回访',
        ),

        self::OBJTYPE_CUSTOMER => array(
            1 => '客户下单',
        ),

        'default' => array(
            98 => '建议',
            99 => '其他',
        ),
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

	const FB_CLIENT = 1;
	const FB_DRIVER = 2;
	const FB_REMOVER = 3;
	const FB_STAFF = 4;
	public static $FB_TYPE = array(
		self::FB_CLIENT => '客户',
		self::FB_DRIVER => '司机',
		self::FB_REMOVER => '搬运工',
		self::FB_STAFF => '工作人员',
	);
	public static $CATE_ORDER = array(1, 2, 3, 4);
	public static $CATE_CUSTOMER = array(5);
	public static $SALE_EXEC = array(1099,1098);
	const DEPARTMENT_CS = 1;
	const DEPARTMENT_SALE = 2;
	const DEPARTMENT_WAREHOUSE3 = 3;
	const DEPARTMENT_WAREHOUSE4 = 4;
	const DEPARTMENT_WAREHOUSE5 = 5;
	const DEPARTMENT_FINACE = 6;
	const DEPARTMENT_AFTERSALE = 7;
	const DEPARTMENT_THEC = 8;
    const DEPARTMENT_CQ = 9;

	public static $DEPARTMENT = array(
		self::DEPARTMENT_CS => '客服部',
		self::DEPARTMENT_SALE => '销售部',
		self::DEPARTMENT_FINACE => '财务部',
		self::DEPARTMENT_WAREHOUSE3 => '玉泉营库房(3号库)',
		self::DEPARTMENT_WAREHOUSE4 => '来广营库房(4号库)',
		self::DEPARTMENT_WAREHOUSE5 => '通州库房(5号库)',
        self::DEPARTMENT_CQ => '重庆',
		self::DEPARTMENT_AFTERSALE => '售后部',
		self::DEPARTMENT_THEC => '技术部',
	);
	public static $DEPARTMENT_DEFAULT = array(
		1027 => array('name' => '客服部', 'department' => self::DEPARTMENT_CS),
		1321 => array('name' => '客服部', 'department' => self::DEPARTMENT_CS),
		1098 => array('name' => '销售部', 'department' => self::DEPARTMENT_SALE),
		1018 => array('name' => '财务部', 'department' => self::DEPARTMENT_FINACE),
		1110 => array('name' => '玉泉营库房(3号库)', 'department' => self::DEPARTMENT_WAREHOUSE3),
		1207 => array('name' => '来广营库房(4号库)', 'department' => self::DEPARTMENT_WAREHOUSE4),
		1144 => array('name' => '通州库房(5号库)', 'department' => self::DEPARTMENT_WAREHOUSE5),
		1172 => array('name' => '售后部', 'department' => self::DEPARTMENT_AFTERSALE),
	);
	public static $WORK_GROUP= array(

		1098 => array('name' => '朱梦瑶(销售部)', 'department' => self::DEPARTMENT_SALE),
		1099 => array('name' => '王燕(销售部)', 'department' => self::DEPARTMENT_SALE),

		1027 => array('name' => '纪星利(客服部)', 'department' => self::DEPARTMENT_CS),
		1321 => array('name' => '张明良(客服部)', 'department' => self::DEPARTMENT_CS),

		1110 => array('name' => '段罡罡(3号库)', 'department' => self::DEPARTMENT_WAREHOUSE3),
		1001 => array('name' => '吴会明(3号库)', 'department' => self::DEPARTMENT_WAREHOUSE3),
		1022 => array('name' => '姚国全(3号库财务)', 'department' => self::DEPARTMENT_WAREHOUSE3),
		1048 => array('name' => '李富国(3号库)', 'department' => self::DEPARTMENT_WAREHOUSE3),
		1174 => array('name' => '贾永生(3号库)', 'department' => self::DEPARTMENT_WAREHOUSE3),


		1207 => array('name' => '李雪飞(4号库)', 'department' => self::DEPARTMENT_WAREHOUSE4),
		1210 => array('name' => '孟德超(4号库)', 'department' => self::DEPARTMENT_WAREHOUSE4),
		1035 => array('name' => '齐晨阳(4号库)', 'department' => self::DEPARTMENT_WAREHOUSE4),
		1084 => array('name' => '赵伟(4号库)', 'department' => self::DEPARTMENT_WAREHOUSE4),
		1037 => array('name' => '胡结满(4号库财务)', 'department' => self::DEPARTMENT_WAREHOUSE4),

		1144 => array('name' => '王涛(5号库)', 'department' => self::DEPARTMENT_WAREHOUSE5),
		1147 => array('name' => '李辉(5号库)', 'department' => self::DEPARTMENT_WAREHOUSE5),
		1153 => array('name' => '胡静(5号库财务)', 'department' => self::DEPARTMENT_WAREHOUSE5),
		1149 => array('name' => '席君(5号库)', 'department' => self::DEPARTMENT_WAREHOUSE5),
		1119 => array('name' => '皮特芳(5号库)', 'department' => self::DEPARTMENT_WAREHOUSE5),

		1018 => array('name' => '张月月(财务部)', 'department' => self::DEPARTMENT_FINACE),

		1172 => array('name' => '郝展(售后主管)', 'department' => self::DEPARTMENT_AFTERSALE),
		1175 => array('name' => '吴雪梅(售后主管)', 'department' => self::DEPARTMENT_AFTERSALE),

	);

	public static $COPY_DEPARTMENT = array(
		self::DEPARTMENT_CS => array('dname'=>'客服部','value'=>'1027'),
		self::DEPARTMENT_SALE =>  array('dname'=>'销售部','value'=>'1098,1099'),
		self::DEPARTMENT_FINACE =>  array('dname'=>'财务部','value'=>'1018'),
		//self::DEPARTMENT_WAREHOUSE3 =>  array('dname'=>'玉泉营库房(3号库)','value'=>'1110,1022,1001,1048,1174'),
		self::DEPARTMENT_WAREHOUSE4 =>  array('dname'=>'来广营库房(4号库)','value'=>'1207,1210,1035,1084,1037'),
		//self::DEPARTMENT_WAREHOUSE5 =>  array('dname'=>'通州库房(5号库)','value'=>'1144,1119,1149,1153,1147'),
        self::DEPARTMENT_CQ => array('dname'=>'重庆', 'value' => '1596,1548,1523,1566'),
		self::DEPARTMENT_AFTERSALE =>  array('dname'=>'售后部','value'=>'1172,1175'),
	);

    //可以下单的售后
    public static $AFTER_SALE_PLACE_ORDER = array(
        1172 => '郝展',
        1212 => '刘义',
        1239 => '尚海森',
        1213 => '黄娜颖',
        1175 => '吴雪梅',
        1238 => '李方方',
        1245 => '宋悦',
    );
}