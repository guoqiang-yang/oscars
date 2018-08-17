<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 16/12/6
 * Time: 15:22
 */
class Conf_Statics
{
    const TYPE_ORDER_LOGISTICS = 1;     //订单物流时间点
    const TYPE_SALES_DETAIL = 2;        //销售明细
    const TYPE_STOCK_IN_DETAIL = 3;     //入库明细
    const TYPE_INVENTORY_DETAIL = 4;    //盘库明细
    const TYPE_PRODUCT_DETAIL = 5;      //商品信息
    const TYPE_ORDER_LOGISTICS_FEE = 6; //订单物流费用
    const TYPE_USER_PURCHASE_DAY = 7;   //用户购买-按天（不显示在导出，不用配置$TYPE）
    const TYPE_USER_PURCHASE_MONTH = 8; //用户购买-按月（不显示在导出，不用配置$TYPE）
    const TYPE_ORDER_FEE_DAY = 9;       //订单费用-按天（不显示在导出，不用配置$TYPE）
    const TYPE_ORDER_FEE_MONTH = 10;    //订单费用-按天（不显示在导出，不用配置$TYPE）
    const TYPE_USER_RE_PURCHASE = 11;   //用户复购（不显示在导出，不用配置$TYPE）
    const TYPE_WAREHOUSE_COST = 12;     //仓库成本（不显示在导出，不用配置$TYPE）
    const TYPE_UNPAID_ORDER = 13;       //未付款订单
    const TYPE_STOCK_DETAIL = 14;       //期末库存
    const TYPE_FIRST_ORDER = 15;        //每日首单
    const TYPE_SECURITY_STOCK = 16;     //安全库存
    const TYPE_SALES_PERFORMANCE_KA = 17;   //KA组销售业绩
    const TYPE_OHTER_STOCK_OUT_ORDER_SELF_USE = 18;  //自用明细
    const TYPE_OHTER_STOCK_OUT_ORDER_BROKEN = 19;    //报损明细
    const TYPE_NOT_BACK_INTERVAL = 20;  //未回单时长
    const TYPE_CUSTOMER_SCORE = 21;    //用户积分
    const TYPE_CUSTOMER_SCORE_DETAIL = 22;      //用户积分明细
    const TYPE_FINANCE_INFO = 23;       //借贷信息
    const TYPE_SKU_STOCK_INFO = 24;     //库存sku排行
    const TYPE_SKU_REFUND_DETAIL = 25;  //商品退货明细
    const TYPE_OTHER_STOCK_PRODUCT = 26;  //其他出入库商品
    const TYPE_STOCK_IN_REFUND_PRODUCT = 27; //入库退货单商品
    const TYPE_NO_STOCK_SKU = 28;      //无库存sku
    const TYPE_IN_OUT_DIFF = 29;       //看库存进出差异，比如查库存增长原因用
    const TYPE_KA_CUSTOMERS = 30;       //KA用户
    const TYPE_SKU_LAST_IN = 31;        //SKU最后一次采购进货日期、采购量的统计数据
    const TYPE_SKU_DELIVERY_DETAIL = 32;    //8号库出库明细
    const TYPE_SKU_DETAIL_INFO = 33;    //sku详细数据
    const TYPE_STOCK_SHIFT_DETAIL = 34;

    public static $TYPE = array(
        self::TYPE_ORDER_LOGISTICS => '订单物流时间',
        self::TYPE_ORDER_LOGISTICS_FEE => '订单物流费用',
        self::TYPE_SALES_DETAIL => 'sku销售明细',
        self::TYPE_STOCK_IN_DETAIL => 'sku采购入库明细',
        self::TYPE_STOCK_SHIFT_DETAIL => 'sku调拨明细',
        self::TYPE_INVENTORY_DETAIL => 'sku盘库明细',
        self::TYPE_PRODUCT_DETAIL => '商品信息(按城市)',
        self::TYPE_SKU_DETAIL_INFO => '商品信息(按仓库)',
        self::TYPE_UNPAID_ORDER => '欠款订单',
//        self::TYPE_STOCK_DETAIL => '期末库存',
        self::TYPE_FIRST_ORDER => '首单数据',
        self::TYPE_SECURITY_STOCK => '安全库存',
        self::TYPE_SALES_PERFORMANCE_KA => 'KA组销售业绩',
        self::TYPE_OHTER_STOCK_OUT_ORDER_SELF_USE => '自用明细',
        self::TYPE_OHTER_STOCK_OUT_ORDER_BROKEN => '报损明细',
        self::TYPE_NOT_BACK_INTERVAL => '未回单统计',
        self::TYPE_CUSTOMER_SCORE => '用户积分',
        self::TYPE_CUSTOMER_SCORE_DETAIL => '用户积分明细',
//        self::TYPE_FINANCE_INFO => '客户贷款及贴息',
        self::TYPE_SKU_REFUND_DETAIL => 'sku退货明细',
        self::TYPE_OTHER_STOCK_PRODUCT => '其他出入库商品',
        self::TYPE_STOCK_IN_REFUND_PRODUCT => '入库退货单商品',
        self::TYPE_NO_STOCK_SKU => '无库存sku',
        self::TYPE_IN_OUT_DIFF => '库房进出货差异',
        self::TYPE_KA_CUSTOMERS => 'ka客户信息',
        self::TYPE_SKU_LAST_IN => 'sku最后采购信息',
        self::TYPE_SKU_DELIVERY_DETAIL => '南北库商品出库明细',
    );

    public static $TYPE_USER = array(
        self::TYPE_ORDER_LOGISTICS => array(1170,1284,1178,1331,1221,1210,1204,1008,1174,1566),
        self::TYPE_SALES_DETAIL => array(1646,1315,1604,1609,1018,1038,1231,1594,1170,1336,1127,1233,1372,1001,1284,1144,1300,1515,1516,1528,1451,1596,1592,1618,1617,1659,1548,1659,1664,1620),
        self::TYPE_STOCK_IN_DETAIL => array(1517,1367,1018,1038,1231,1170,1336,1366,1178,1331,1144,1566,1594,1620),
        self::TYPE_INVENTORY_DETAIL => array(1170,1336,1178,1331,1144,1566),
        self::TYPE_PRODUCT_DETAIL => array(1646,1604,1609,1594,1170,1336,1127,1233,1178,1331,1144,1267,1451,1515,1516,1528,1618,1617,1548,1659,1664),
        self::TYPE_ORDER_LOGISTICS_FEE => array(1410,1635,1284,1178,1331,1336,1221,1210,1204,1008,1174,1170,1566,1517,1624,1523),
        self::TYPE_UNPAID_ORDER => array(1231,1038,1492,1530,1627),
        self::TYPE_STOCK_DETAIL => array(1231,1566),
        self::TYPE_STOCK_SHIFT_DETAIL => array(1620),
        self::TYPE_FIRST_ORDER => array(1175,1172,1100,1078),
        self::TYPE_SECURITY_STOCK => array(1653,1646,1604,1609,1594,1336,1170,1326,1315,1372,1314,1162,1001,1178,1331,1144, 1431, 1433, 1515,1516,1528,1487,1520,1579,1566,1230,1451,1618,1617,1494,1659,1548,1659,1664),
        self::TYPE_SALES_PERFORMANCE_KA => array(),
        self::TYPE_OHTER_STOCK_OUT_ORDER_SELF_USE => array(),
        self::TYPE_OHTER_STOCK_OUT_ORDER_BROKEN => array(),
        self::TYPE_CUSTOMER_SCORE => array(1078, 1300),
        self::TYPE_CUSTOMER_SCORE_DETAIL => array(1078, 1300),
        self::TYPE_NOT_BACK_INTERVAL  => array(1078, 1300,1424,1410,1342,1216,1207,1204,1174,1144,1068,1035),
        self::TYPE_SKU_REFUND_DETAIL => array(1170,1336,1127,1233,1372,1001,1284,1144),
        self::TYPE_OTHER_STOCK_PRODUCT => array(1170,1336,1366,1178,1331,1144,1566,1492),
        self::TYPE_STOCK_IN_REFUND_PRODUCT => array(1170,1336,1366,1178,1331,1144),
        self::TYPE_NO_STOCK_SKU => array(1528, 1516, 1515, 1336),
        self::TYPE_IN_OUT_DIFF => array(1431, 1433),
        self::TYPE_KA_CUSTOMERS => array(1098),
        self::TYPE_SKU_LAST_IN => array(1646,1451),
        self::TYPE_SKU_DELIVERY_DETAIL => array(1018,1144),
        self::TYPE_SKU_DETAIL_INFO => array(1646,1596,1609,1592,1451,1433,1659,1594,1548,1659,1664),
    );
}