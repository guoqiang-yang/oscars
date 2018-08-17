/*-----------------------------------------------
 * 统计中间结果 - 基础表(消费、用户、成本等) - 每天
 *-----------------------------------------------*/
CREATE TABLE t_statistics_base_per_day (
  id                int             not null auto_increment,
  `day`             date            not null,
  wid               INT             not null default 0      comment '仓库id',
  reg_num           INT             not null default 0      comment '注册用户数',
  buyer_num         INT             not null default 0      comment '购买用户数(按配送日期算)',
  new_buyer_num     INT             not null default 0      comment '[新用户]购买用户数(按配送日期算)',

  order_num         INT             not null default 0      comment '配送订单数：已出库',
  new_order_num     INT             not null default 0      comment '[新用户]配送订单数：已出库',
  addon_num         INT             not null default 0      comment '补单数：已出库',
  aftersale_num     INT             not null default 0      comment '售后单数：已出库',
  
  amount            INT             not null default 0      comment '订单总金额(应收,Accounts?)',
  --refund            INT             not null default 0      comment '退款金额(按订单配送日期算)',
  refund_num        INT             not null default 0      comment '退款单数',
  order_refund      INT             not null default 0      comment '订单退款金额（t_order里的refund字段）',
  cost              INT             not null default 0      comment '商品成本',
  new_amount        INT             not null default 0      comment '[新用户]订单总金额(应收,Accounts?)',
  --new_refund        INT             not null default 0      comment '[新用户]退款金额(按订单配送日期算)',
  
  freight           INT             not null default 0      comment '好材支付运费 (单位:分)',
  carriage          INT             not null default 0      comment '好材支付搬运费 (单位:分)',
  customer_freight  INT             not null default 0      comment '客户支付运费 (单位:分)',
  customer_carriage INT             not null default 0      comment '客户支付搬运费 (单位:分)',
  privilege         INT             not null default 0      comment '订单优惠 (单位:分)',

  ctime             timestamp       not null default '0000-00-00 00:00:00',
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  unique key (day, wid),
  KEY (wid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*-----------------------------------------------
 * 统计中间结果 - 基础表(消费、用户、成本等) - 每月
 *-----------------------------------------------*/
CREATE TABLE t_statistics_base_per_month (
  id                int             not null auto_increment,
  `month`           char(7)         not null,
  wid               INT             not null default 0      comment '仓库id',
  reg_num           INT             not null default 0      comment '注册用户数',
  buyer_num         INT             not null default 0      comment '购买用户数(按配送日期算)',
  order_num         INT             not null default 0      comment '配送订单数',
  amount            INT             not null default 0      comment '订单总金额(应收,Accounts?)',
  refund            INT             not null default 0      comment '退款金额(按退货提交财务日期算)',
  refund_num        INT             not null default 0      comment '退款单数',
  order_refund      INT             not null default 0      comment '订单退款金额（t_order里的refund字段）',
  cost              INT             not null default 0      comment '商品成本',
  new_buyer_num     INT             not null default 0      comment '[新用户]购买用户数(按配送日期算)',
  new_order_num     INT             not null default 0      comment '[新用户]配送订单数',
  new_amount        INT             not null default 0      comment '[新用户]订单总金额(应收,Accounts?)',
  new_refund        INT             not null default 0      comment '[新用户]退款金额(按订单配送日期算)',
  freight           INT             not null default 0      comment '好材支付运费 (单位:分)',
  carriage          INT             not null default 0      comment '好材支付搬运费 (单位:分)',
  customer_freight  INT             not null default 0      comment '客户支付运费 (单位:分)',
  customer_carriage INT             not null default 0      comment '客户支付搬运费 (单位:分)',
  privilege         INT             not null default 0      comment '订单优惠 (单位:分)',
  ctime             timestamp       not null default '0000-00-00 00:00:00',
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY (wid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



/*-----------------------------------------------
 * 统计中间结果 - 每个月的购买用户信息。 逗号分隔。
 *-----------------------------------------------*/
CREATE TABLE t_statistics_buyer_per_month (
  `month`           char(7)         not null,

  cids              MEDIUMTEXT      not null                comment '所有购买用户',
  new_cids          TEXT            not null                comment '新购买用户/首单客户',
  new_rebuy_cids    TEXT            not null                comment '新用户-且复购',
  weixin_reg_cids   TEXT            not null                comment '购买用户 - 通过微信注册',
  app_reg_cids      TEXT            not null                comment '购买用户 - 通过App注册',
  sales_reg_cids    MEDIUMTEXT      not null                comment '购买用户 - 通过销售注册',
  other_reg_cids    TEXT            not null                comment '购买用户 - 通过其他途径注册(客服,第三方平台)',

  ctime             timestamp       not null default '0000-00-00 00:00:00',
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*-----------------------------------------------
 * 统计中间结果 - sku进销存情况 - 每天
 * 库存, 进货, 销量, 品类数, 销售额, 销售成本；
 * 说明:
 *	【库】、【临】只是针对库存，普采有库存出入，临采无库存出入
 *   销售是根据t_order_product表的vnum判断； 采购根据采购单类型判断;
 *-----------------------------------------------*/
CREATE TABLE t_statistics_sku_per_day (
  `day`                date            not null,
  sid                  INT             not null default 0      comment 'sku id',
  wid                  INT             not null default 0      comment '仓库id',

  begin_stock          INT             not null default 0      comment '期初库存数量',

  bought_in_num        INT             not null default 0      comment '[进]【库】进货数量',
  bought_in_num_tmp    INT             not null default 0      comment '[进]【临】进货数量',
  check_in_num         INT             not null default 0      comment '[进]盘盈(盘库增加)',
  refund_in_num        INT             not null default 0      comment '[进]客户退货入库',
  other_in_num         INT             not null default 0      comment '[进]其他入库',

  sales_out_num        INT             not null default 0      comment '[出]【库】销售数量',
  sales_out_num_tmp    INT             not null default 0      comment '[出]【临】销售数量',
  check_out_num        INT             not null default 0      comment '[出]盘亏(盘库减少)',
  refund_out_num       INT             not null default 0      comment '[出]供应商退货出库',
  other_out_num        INT             not null default 0      comment '[出]其他出库',

  bought_in_cost       INT             not null default 0      comment '【库】进货采购总成本(单位:分)',
  bought_in_cost_tmp   INT             not null default 0      comment '【临】进货采购总成本(单位:分)',

  sales_amount         INT             not null default 0      comment '【库】销售总价(单位:分)',
  sales_cost           INT             not null default 0      comment '【库】销售总成本(单位:分)',

  sales_amount_tmp     INT             not null default 0      comment '【临】销售总价(单位:分)',
  sales_cost_tmp       INT             not null default 0      comment '【临】销售总成本(单位:分)',

  begin_stock_cost     INT             not null default 0      comment '期初库存成本',
  check_in_cost        INT             not null default 0      comment '[进]成本-盘盈(盘库增加)',
  refund_in_cost       INT             not null default 0      comment '[进]成本-客户退货入库',
  other_in_cost        INT             not null default 0      comment '[进]成本-其他入库',
  check_out_cost       INT             not null default 0      comment '[出]成本-盘亏(盘库减少)',
  refund_out_cost      INT             not null default 0      comment '[出]成本-供应商退货出库',
  other_out_cost       INT             not null default 0      comment '[出]成本-其他出库',
  managing_mode        TINYINT         NOT NULL DEFAULT 1      COMMENT '经营模式，1-自营，2-联营',

  ctime                timestamp       not null default '0000-00-00 00:00:00',
  mtime                timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`day`, sid, wid),
  INDEX(sid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*-----------------------------------------------
 * 统计中间结果 - 品类进销存情况
 * 库存, 进货, 销量, 品类数, 销售额, 销售成本；
 *-----------------------------------------------*/
CREATE TABLE t_statistics_cate_sku_per_day (
  `day`                date            not null,
  cate2                smallint unsigned        not null default 0      comment '二级分类',
  wid                  INT             not null default 0      comment '仓库id',

  begin_stock          INT             not null default 0      comment '期初库存数量',

  bought_in_num        INT             not null default 0      comment '[进]【库】进货数量',
  bought_in_num_tmp    INT             not null default 0      comment '[进]【临】进货数量',
  check_in_num         INT             not null default 0      comment '[进]盘盈(盘库增加)',
  refund_in_num        INT             not null default 0      comment '[进]客户退货入库',
  other_in_num         INT             not null default 0      comment '[进]其他入库',

  sales_out_num        INT             not null default 0      comment '[出]【库】销售数量',
  sales_out_num_tmp    INT             not null default 0      comment '[出]【临】销售数量',
  check_out_num        INT             not null default 0      comment '[出]盘亏(盘库减少)',
  refund_out_num       INT             not null default 0      comment '[出]供应商退货出库',
  other_out_num        INT             not null default 0      comment '[出]其他出库',

  bought_in_cost       INT             not null default 0      comment '【库】进货采购总成本(单位:分)',
  bought_in_cost_tmp   INT             not null default 0      comment '【临】进货采购总成本(单位:分)',

  sales_amount         INT             not null default 0      comment '【库】销售总价(单位:分)',
  sales_cost           INT             not null default 0      comment '【库】销售总成本(单位:分)',

  sales_amount_tmp     INT             not null default 0      comment '【临】销售总价(单位:分)',
  sales_cost_tmp       INT             not null default 0      comment '【临】销售总成本(单位:分)',

  sales_skus           TEXT            not null                comment '销售的sku id 列表',
  sales_skus_tmp       TEXT            not null                comment '【临】销售的sku id 列表',
  stock_skus           TEXT            not null                comment '库存的sku id 列表(所有期间有过库存的sku)',

  begin_stock_cost     INT             not null default 0      comment '期初库存成本',
  check_in_cost        INT             not null default 0      comment '[进]成本-盘盈(盘库增加)',
  refund_in_cost       INT             not null default 0      comment '[进]成本-客户退货入库',
  other_in_cost        INT             not null default 0      comment '[进]成本-其他入库',
  check_out_cost       INT             not null default 0      comment '[出]成本-盘亏(盘库减少)',
  refund_out_cost      INT             not null default 0      comment '[出]成本-供应商退货出库',
  other_out_cost       INT             not null default 0      comment '[出]成本-其他出库',

  ctime                      timestamp       not null default '0000-00-00 00:00:00',
  mtime                      timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`day`, cate2, wid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*-----------------------------------------------
 * 统计中间结果 - 品类进销存情况
 * 库存, 进货, 销量, 品类数, 销售额, 销售成本；
 *-----------------------------------------------*/
CREATE TABLE t_statistics_brand_sku_per_day (
  `day`                date            not null,
  bid                  int             not null default 0      comment '品牌id',
  wid                  INT             not null default 0      comment '仓库id',

  begin_stock          INT             not null default 0      comment '期初库存数量',

  bought_in_num        INT             not null default 0      comment '[进]【库】进货数量',
  bought_in_num_tmp    INT             not null default 0      comment '[进]【临】进货数量',
  check_in_num         INT             not null default 0      comment '[进]盘盈(盘库增加)',
  refund_in_num        INT             not null default 0      comment '[进]客户退货入库',
  other_in_num         INT             not null default 0      comment '[进]其他入库',

  sales_out_num        INT             not null default 0      comment '[出]【库】销售数量',
  sales_out_num_tmp    INT             not null default 0      comment '[出]【临】销售数量',
  check_out_num        INT             not null default 0      comment '[出]盘亏(盘库减少)',
  refund_out_num       INT             not null default 0      comment '[出]供应商退货出库',
  other_out_num        INT             not null default 0      comment '[出]其他出库',

  bought_in_cost       INT             not null default 0      comment '【库】进货采购总成本(单位:分)',
  bought_in_cost_tmp   INT             not null default 0      comment '【临】进货采购总成本(单位:分)',

  sales_amount         INT             not null default 0      comment '【库】销售总价(单位:分)',
  sales_cost           INT             not null default 0      comment '【库】销售总成本(单位:分)',

  sales_amount_tmp     INT             not null default 0      comment '【临】销售总价(单位:分)',
  sales_cost_tmp       INT             not null default 0      comment '【临】销售总成本(单位:分)',

  sales_skus           TEXT            not null                comment '销售的sku id 列表',
  sales_skus_tmp       TEXT            not null                comment '【临】销售的sku id 列表',
  stock_skus           TEXT            not null                comment '库存的sku id 列表(所有期间有过库存的sku)',

  begin_stock_cost     INT             not null default 0      comment '期初库存成本',
  check_in_cost        INT             not null default 0      comment '[进]成本-盘盈(盘库增加)',
  refund_in_cost       INT             not null default 0      comment '[进]成本-客户退货入库',
  other_in_cost        INT             not null default 0      comment '[进]成本-其他入库',
  check_out_cost       INT             not null default 0      comment '[出]成本-盘亏(盘库减少)',
  refund_out_cost      INT             not null default 0      comment '[出]成本-供应商退货出库',
  other_out_cost       INT             not null default 0      comment '[出]成本-其他出库',

  ctime             timestamp       not null default '0000-00-00 00:00:00',
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`day`, bid, wid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 仓库成本表
 * 搬运费，运费；
 *-----------------------------------------------*/
CREATE TABLE t_statistics_warehouse_cost (
  id                INT             not null auto_increment,
  `day`             date            not null,
  wid               INT             not null default 0      comment '仓库id',
  freight           INT             not null default 0      comment '好材支付运费 (单位:分)',
  carriage          INT             not null default 0      comment '好材支付搬运费 (单位:分)',
  ctime             timestamp       not null default '0000-00-00 00:00:00',
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY (`day`, wid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 *  库房费用表
 *-----------------------------------------------*/
CREATE TABLE t_statistics_warehouse_fee (
    id            int             not null auto_increment,
    wid           int             not null default 0    comment '库房id',
    `month`       char(7)         not null default ''   comment '月份',
    fixed_input   INT             not null default 0    comment '固定投入',
    staff_salary  INT             not null default 0    comment '人员工资',
    other_input   INT             not null default 0    comment '其他成本',
    offline_logistics_fee INT     not null default 0    comment '线下物流费用',
    suid          INT             not null default 0    comment '操作人',
    status        int             not null default 0,
    ctime         timestamp       not null default 0,
    mtime         timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY (`month`, wid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
 *销售月销售状况
 */
CREATE TABLE t_statistics_monthly_sales_individual (
    suid                  int             not null              comment '销售id',
    date_month            char(7)         not null DEFAULT ''   comment '月份',
    month_target          DOUBLE          not null default 0    comment '本月目标,单个销售',
    floor_target          DOUBLE          not null default 0    comment '底线目标,销售组',
    challenge_target      DOUBLE          not null default 0    comment '挑战目标,销售组',
    ctime             timestamp           not null default '0000-00-00 00:00:00',
    mtime             timestamp           not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (suid, date_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


