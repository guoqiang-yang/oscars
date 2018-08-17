set names utf8;

/*-----------------------------------------------
 * 客户(公司)表: 如果工长没有公司名称,则写“姓名+师傅”
 *-----------------------------------------------*/
CREATE TABLE t_customer (
  cid               int             not null auto_increment,
  bid               int             not null default 0      comment '所属企业id',
  name              varchar(128)    not null default ''     comment '客户/公司名称',
  all_user_names    text            not null                comment '客户对应所有用户的名字， 冗余数据搜素使用, ","分隔',
  all_user_mobiles  text            not null                comment '客户对应所有用户的手机号， 冗余数据搜索使用, ","分隔',
  city_id           int             not null default 0      comment '城市id',
  address           varchar(256)    not null default ''     comment '门店地址',
  note              text            not null                comment '其他备注',
  record_suid       int             not null default 0      comment '录入专员',
  sales_suid        int             not null default 0      comment '所属销售专员 (t_staff_user)',
  identity          tinyint         not null default 0      comment '客户身份：1-工长，2-公司',
  sale_status       tinyint         not null default 0      comment '销售状态   1-私海用户(专有) 2-公海用户(公共) 3-内海(特殊运营) 99-非服务对象',
  chg_sstatus_time  timestamp       not null default 0      comment '修改销售状态的时间',
  level_for_sys     tinyint         not null default 0      comment '系统定义的客户级别：5-vip客户 4-优质客户 3-普通客户 2-待观察客户 1-恶劣客户',
  source            tinyint         not null default 0      comment '客户来源',

  tax_point         tinyint         not null default 0      comment '税点：0：不含税；8：税率0.08',

  first_order_date  date            not null default 0      comment '首单日期',
  second_order_date date            not null default 0      comment '第二次下单日期，统计复购',
  last_order_date   date            not null default 0      comment '最后一次下单日期',
  order_num         int             not null default 0      comment '下单数 (必须是完成付款的有效订单)',
  online_order_num  int             not null default 0      comment '在线下单数',
  account_balance   int             not null default 0      comment '客户应付金额(欠款)',
  account_amount    int             not null default 0      comment '账户可使用的金额 (余额)',
  total_amount      int             not null default 0      comment '总消费额',
  refund_amount     int             not null default 0      comment '总退款额',
  refund_num        int             not null default 0      comment '总退单数量',

  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除, 2-封禁',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (cid),
  index (all_user_names),
  index (sales_suid),
  index (record_suid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=6000;


/*-----------------------------------------------
 * (客户登陆)用户账号表
 *-----------------------------------------------*/
CREATE TABLE t_user (
  uid               int             not null auto_increment,
  cid               int             not null default 0      comment '对应客户id, 可能多个user管理同一个客户账号',
  name              varchar(32)     not null default ''     comment '姓名',
  mobile            char(11)        not null default ''     comment '登陆手机号(必填)',
  email             varchar(128)    not null default ''     comment 'email',
  sex               tinyint         not null default 0      comment '性别 0-男 1-女',
  birthday          date            not null default 0      comment '生日',
  hometown          varchar(32)     not null default ''     comment '籍贯',
  password          varchar(32)     not null default ''     comment '密码',
  salt              smallint        not null default 0      comment '密码salt',
  is_admin          tinyint         not null default 1      comment '是否为管理员',

  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除, 2-封禁',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (uid),
  unique (mobile),
  index (cid),

) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=6000;


/*-----------------------------------------------
 * 订单表
 *-----------------------------------------------*/
CREATE TABLE t_order (
  oid               int             not null auto_increment,
  source_oid        int             not null default 0      comment '源oid (补货单才有，表示是哪一单的补货)',
  cid               int             not null default 0      comment '客户id',
  uid               int             not null default 0      comment '用户id',

  wid               int             not null default 0      comment '仓库ID',
  step              tinyint         not null default 0      comment '所处阶段: 见 Conf_Order',
  source            int             not null default 0      comment '第三方合作下单使用，保存第三方的appid',
  city_id           int             not null default 0      comment '订单城市 影响：运营政策等',
  
  contact_name      varchar(48)     not null default ''     comment '联系人',
  contact_phone     varchar(48)     not null default ''     comment '联系电话',
  delivery_date     datetime        not null default 0      comment '送货日期-开始',
  delivery_date_end datetime        not null default 0      comment '送货日期-结束',
  delivery_type     tinyint         not null default 1      comment '配送方式: 1-物流 2-自提 3-加急',
  district          int             not null default 0      comment '城区',
  area              int             not null default 0      comment '范围',
  city              smallint        not null default 0      comment '城市',
  address           varchar(256)    not null default ''     comment '工地地址',
  construction      int             not null default 0      comment '工地ID',
  community_id      int             not null default 0      comment '小区ID',
  service           int             not null default 0      comment '所需服务，例如是否需要搬运',
  floor_num         tinyint         not null default 0      comment '搬运楼层',

  price             int             not null default 0      comment '总货价,不含运费 (单位:分)',
  freight           int             not null default 0      comment '运费 (单位:分)',
  customer_carriage int             not null default 0      comment '客户支付搬运费 (单位:分)',
  privilege         int             not null default 0      comment '优惠 (单位:分)',
  privilege_note    varchar(128)    not null default ''     comment '优惠备注',
  refund            int             not null default 0      comment '退款 (单位:分)',
  real_amount       int             not null default 0      comment '实际收款（单位:分; 财务收款）',
  payment_type      tinyint         not null default 0      comment '付款方式',
  paid              tinyint         not null default 0      comment '是否已收款',

  suid              int             not null default 0      comment '录单人员uid',
  sure_suid         int             not null default 0      comment '客服q确认uid',
  saler_suid        int             not null default 0      comment '销售销售人员id',
  sure_time         timestamp       not null default 0      comment '确认时间',
  picked_time       timestamp       not null default 0      comment '拣货时间',
  ship_time         timestamp       not null default 0      comment '出库时间',
  back_time         timestamp       not null default 0      comment '回单时间',
  pay_time          timestamp       not null default 0      comment '付款时间',
  
  line_id           int             not null default 0      comment '排线id',
  tax_point         tinyint         not null default 0      comment '同 t_customer:tax_point',
  op_note           varchar(255)    not null default ''     comment '操作备注：xxx:n,yyy:m',
  customer_note     varchar(4096)   not null default ''     comment '客户写的备注',
  note              varchar(4096)   not null default ''     comment '备注',
 
  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除, 3-取消', 
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (oid),
  index (cid),
  index (delivery_date),
  index (ship_time),
  index (pay_time),
  index (ctime),
  index (saler_suid),
  index (source_oid),
  index (back_time)

) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=6000;

/*-----------------------------------------------
 * 退款单-基本信息表
 *-----------------------------------------------*/
CREATE TABLE t_refund (
  rid               int             not null auto_increment,
  cid               int             not null default 0      comment '客户id',
  uid               int             not null default 0      comment '用户id',
  oid               int             not null default 0      comment '订单id',
  wid               int             not null default 0      comment '仓库id',

  price             int             not null default 0      comment '退货款汇总 (单位:分)',
  damaged_price     int             not null default 0      comment '报损金额 (单位:分)',
  adjust            int             not null default 0      comment '金额调整',
  
  step              tinyint         not null default 0      comment '所处阶段',
  paid              tinyint         not null default 0      comment '是否退款 0-未退款，1-已退款',
  city_id           int             not null default 0      comment '城市id',
  refund_carry_fee  int             not null default 0      comment '退回-搬运费',
  refund_freight    int             not null default 0      comment '退回-运费',
  carry_fee         int             NOT NULL DEFAULT 0      comment '收取-退货搬运费',
  freight           int             NOT NULL DEFAULT 0      comment '收取-退货运费',
  refund_privilege  int             not null default 0      comment '少退-优惠',
  refund_to_amount  int             not null default 0      comment '退入余额款额',
  
  audit_time        timestamp       not null default 0      comment '退款单审核通过时间',
  stockin_time      timestamp       not null default 0      comment '退货入库时间',
  to_finance_time   timestamp       not null default 0      comment '提交财务时间',
  paid_time         timestamp       not null default 0      comment '财务退款时间',
  
  reason_type       int             not null default 0      comment '原因类型',
  reason            int             not null default 0      comment '原因',
  suid              int             not null default 0      comment '录单人id',
  received_suid     int             not null default 0      comment '入库人id',
  shelved_suid      int             not null default 0      comment '上架人id',
  verify_suid       int             not null default 0      comment '审核人id',
  note              text            not null                comment '备注',
  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除, 2-取消',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (rid),
  index (oid),
  index (cid),
  index (step),
  index (ctime),
  index (stockin_time),
  index (paid_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=6000;


/*-----------------------------------------------
 * 订单-商品清单表 (订单快照)
 *-----------------------------------------------*/
CREATE TABLE t_order_product (
  oid               int             not null default 0      comment '订单id',
  pid               int             not null default 0      comment '商品id',
  sid               INT             not null default 0      comment 'sku id',
  rid               int             not null default 0      comment '退货单id',
  wid               int             not null default 0      comment '订单商品所属仓库',
  location          varchar(80)     not null default ''     comment '货物的货位,售出货品可能来自多个货位',

  price             int             not null default 0      comment '商品单价(单位:分) 这里需要记单价,因为价格在变化',
  cost              int             not null default 0      comment '下单时商品的成本',
  privilgee         int             not null default 0      comment '优惠金额(单位:分)',

  num               int             not null default 0      comment '购买数量 or 实际退货数量',
  vnum              int             not null default 0      comment '空采空配数量',
  picked            INT             not null default 0      comment '已捡货数量 or 退货入库数量',
  picked_time       TIMESTAMP       not null default 0      comment '拣货时间',
  refund_vnum       int             not null default 0      comment '空退数量',
  apply_rnum        int             not null default 0      comment '申请退货数量',
  damaged_num       int             not null default 0      comment '退货单损坏数量 入库完成后：计算报损数量=num-picked',

  tmp_inorder_num   int             not null default 0      comment '已做采购单的数量',
  tmp_inorder_id    int             not null default 0      comment '临采单id',
  vnum_deal_type    tinyint         not null default 0      comment '空采处理方式 1-已外采',
  outsourcer_id     int             not null default 0      comment '外包供应商id',

  note              varchar(256)    not null default ''     comment '备注',
  status            tinyint         not null default 0      comment '状态: 0-正常, 1-整个订单删除, 3-单独删除商品/取消',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (oid, pid, rid),
  index(ctime),
  index(rid),
  index(sid),
  index(tmp_inorder_id),
  index(outsourcer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*-----------------------------------------------
 * 建筑工地
 *-----------------------------------------------*/
CREATE TABLE t_construction_site (
  id                int             not null auto_increment,
  cid               int             not null default 0      comment '客户id',
  uid               int             not null default 0      comment '用户id',
  community_id      int             not null default 0      comment '小区ID',
  city              SMALLINT        not null default 0      comment '城市',
  district          int             not null default 0      comment '城区',
  area              int             not null default 0      comment '商圈',
  address           varchar(256)    not null default ''     comment '工地地址',
  community_name    varchar(32)     not null default ''     comment '小区名称-冗余字段',
  community_addr    varchar(256)    not null default ''     comment '小区地址-冗余',
  ring_road         tinyint         not null default 0      comment '环路信息',
  lng               DECIMAL(11,8)   not null                comment '经度',
  lat               DECIMAL(11,8)   not null                comment '纬度',
  note              varchar(4096)   not null default ''     comment '备注',
  last_order_date   date            not null default 0      comment '最后一次下单日期',
  order_num         smallint        not null default 0      comment '下单数 (必须是完成付款的有效订单)',
  space             smallint        not null default 0      comment '房屋面积',
  type              tinyint         not null default 0      comment '类型: 1-工装, 2-家装',

  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  index (cid, last_order_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=0;

/*-----------------------------------------------
 * 城市小区
 *-----------------------------------------------*/
CREATE TABLE t_community (
    cmid            int             not null auto_increment,
    name            varchar(64)     not null default ''   comment '小区名称',
    pinyin_name     varchar(128)    not null default ''   comment '小区名称 拼音',
    alias           varchar(128)    not null default ''   comment '小区别名',
    city_id         int             not null default 0    comment '城市id',
    city            varchar(32)     not null default ''   comment '城市',
    district_id     INT             not null default 0    comment '城区id',
    district        varchar(32)     not null default ''   comment '城区',
    address         varchar(255)    not null default ''   comment '地址',
    area            varchar(64)     not null default ''   comment '所属区域：如 北七家，东四等',
    is_inside       tinyint         not null default 0    comment '是否在标记界限内：如六环内/外',
    ring_road       tinyint         not null default 0    comment '环路信息',
    house_type      varchar(128)    not null default ''   comment '房屋类型：住宅，写字楼等',
    building_type   varchar(64)     not null default ''   comment '建筑类型：板楼 塔楼',
    build_size      int             not null default 0    comment '建筑面积，平方米',
    lng             DECIMAL(11,8)   not null              comment '经度',
    lat             DECIMAL(11,8)   not null              comment '纬度',
    note            varchar(255)    not null default ''   comment '备注',
    pics            text            not null              comment '小区实地图片，半角逗号分隔',
    status          tinyint         not null default 0    comment '状态: 0-正常, 1-删除 5-未审核',
    suid            int             not null default 0    comment '添加人',
    edit_suid       int             not null default 0    comment '最后编辑人',
    merge_to_cmid   int             not null default 0    comment '合并到哪个小区',

    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (cmid),
    index(name),
    index(alias),
    index(city_id, district_id, ring_road)

) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1000;


/*-----------------------------------------------
 * 品牌表
 *-----------------------------------------------*/
CREATE TABLE t_brand (
  bid               int             not null auto_increment,
  name              varchar(32)     not null default ''     comment '品牌名称',
  cate2             smallint unsigned        not null default 0      comment '二级分类',
  sortby            INT                      not null default 0      comment '排序字段，越大排得越靠前，相同排序按sid降序算'

  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除, 2-下架',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (bid),
  unique (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=100;

/*-----------------------------------------------
 * 型号表
 *-----------------------------------------------*/
CREATE TABLE t_model (
  mid               int                      not null auto_increment,
  name              varchar(32)              not null default ''     comment '品牌名称',
  cate2             smallint unsigned        not null default 0      comment '二级分类',
  sortby            INT                      not null default 0      comment '排序字段，越大排得越靠前，相同排序按sid降序算',

  status            tinyint                  not null default 0      comment '状态: 0-正常, 1-删除, 2-下架',
  ctime             timestamp                not null default 0,
  mtime             timestamp                not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (mid),
  index (cate2),
  index (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=100;


/*-----------------------------------------------
 * sku/spu表 （暂时sku,spu相同）
 *-----------------------------------------------*/
CREATE TABLE t_sku (
  sid               int                      not null auto_increment,
  title             varchar(224)             not null default ''     comment '标题',
  alias             varchar(128)             not null default ''     comment '别名',
  pic_ids           varchar(224)             not null default ''     comment '图片id列表, 以英文逗号分隔',
  cate1             tinyint                  not null default 0      comment '一级分类',
  cate2             smallint unsigned        not null default 0      comment '二级分类',
  bid               int                      not null default 0      comment '品牌',
  unit              varchar(32)              not null default ''     comment '单位单元,比如: 卷,袋,根',
  package           varchar(64)              not null default ''     comment '包装,比如 100 米/卷',
  picking_note      varchar(100)             not null default ''     comment '包装说明',
  detail            text                     not null                comment '商品描述',
  mids              varchar(500)             not null default ''      comment '多个品牌，mid暂且不用',
  length            INT                      not null default 0       comment '长，单位厘米',
  width             INT                      not null default 0       comment '宽，单位厘米',
  height            INT                      not null default 0       comment '高，单位厘米',
  weight            INT                      not null default 0       comment '重量，单位克',
  type              tinyint                  not null default 1       comment '类型：普通，加工等',
  rel_sku           varchar(200)             not null default ''      comment '加工类型商品，组合关系 sku1:1,sku2:4,...',

  status            tinyint                  not null default 0      comment '状态: 0-正常, 1-删除, 4-下架',
  ctime             timestamp                not null default 0,
  mtime             timestamp                not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (sid),
  index (title),
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=10000;


/*-----------------------------------------------
 * 商品表
 * 注意扩展: 可能会增加字段 title, pic_id 等
 *-----------------------------------------------*/
CREATE TABLE t_product (
  pid               int             not null auto_increment,
  sid               int             not null default 0      comment 'sku id',
  city_id           INT             not null DEFAULT 0      comment '城市',
  alias             varchar(128)    not null default ''     comment '城市-别名',
  cost              int             not null default 0      comment '成本, 单位:分',
  price             int             not null default 0      comment '价格, 单位:分',
  work_price        int             not null default 0      comment '工装价',

  sales_type        tinyint         not null default 0      comment '销售类型: 0-正常 1-促销 2-热卖',
  buy_type          tinyint         not null default 1      comment '采购类型: 普采/临采商品',
  detail            text            not null                comment '商品描述',
  picking_note      varchar(100)    not null default ''     comment '包装说明',

  sortby            INT             not null default 0      comment '排序字段，越大排得越靠前，相同排序按sid降序算',
  carrier_fee       int             not null default 0      comment '客户-楼梯上楼费 单位：分',
  carrier_fee_ele   int             not null default 0      comment '客户-电梯上楼费 单位：分',
  worker_ca_fee     int             not null default 0      comment '工人-楼梯上楼费 单位：分',
  worker_ca_fee_ele int             not null default 0      comment '工人-电梯上楼费 单位：分',
  
  status            tinyint         not null default 0      comment '状态: 0-正常 1-删除 2-下架',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    
  PRIMARY KEY (pid),
  UNIQUE KEY (sid, city_id),

) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=10000;

/*-----------------------------------------------
 * 库存表
 *-----------------------------------------------*/
CREATE TABLE t_stock (
  sid               int             not null default 0      comment 'sku id',
  wid               int             not null default 0      comment '仓库id',
  fring_cost        int             not null default 0      comment '附加成本，单位:分',
  cost              int             not null default 0      comment '成本, 单位:分',
  num               int             not null default 0      comment '库存数量(包含购买占用数量)',
  occupied          int             not null default 0      comment '购买占用数量',
  damaged_num       int             not null default 0      comment '残损、待盘点数量之和',

  wait_num          int             not null default 0      comment '在途数量',
  ave_sale_num      int             not null default 0      comment '平均销量 14天',
  recent_stat_sale  int             not null default 0      comment '最近统计销售，与平均销量关联',
  target_num        int             not null default 0      comment '目标存量',
  deliery_cycle     int             not null default 0      comment '货期，单位：小时',       
  outsourcer_id     int             not null default 0      comment '外包供应商id',

  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (sid, wid),
  index (outsourcer_id, wid, status)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*-----------------------------------------------
 * 库存变化历史 （盘点单？？？？）
 *-----------------------------------------------*/
CREATE TABLE t_stock_history (
  id                int(11)         NOT NULL AUTO_INCREMENT,
  sid               int             not null default 0      comment 'sku id',
  wid               int             not null default 0      comment '仓库id',
  old_num           int             not null default 0      comment '原数量',
  num               int             not null default 0      comment '库存数量(包含购买占用数量)',
  iid               int             not null default 0      comment '进出货单号',
  suid              int             not null default 0      comment '执行人',
  type              tinyint         not null default 0      comment '类型: 0-采购入库, 1-销售出库, 2-盘亏, 3-盘盈',
  reason            tinyint         not null default 0      comment '盈亏原因',

  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除',
  note              varchar(100)    not null default ''     comment '备注',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  index(sid, wid),
  index(ctime),
  index(iid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*-----------------------------------------------
 *  sku与货位的mapping表，
 *  并记录货位的商品数量等信息
 *
 *  数据表暂且支持 一对多 sku -> location
 *------------------------------------------------*/
CREATE TABLE t_sku_2_location (
    id          int             not null auto_increment,
    sid         int             not null default 0          comment '商品的sku',
    location    char(10)        not null default ''         comment '货位标识 eg: 区-架-层-位（A-01-10-99）',
    wid         int             not null default 0          comment '仓库id',
    num         int             not null default 0          comment '货位商品数量',
    occupied    int             not null default 0          comment '货位商品的占用数量',
    status      tinyint         not null default 0          comment '状态: 0-正常 1-删除',

    ctime       timestamp       not null default 0,
    mtime       timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY (sid, location, wid),
    index (location),
    index (wid)

)ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=100;


/*-----------------------------------------------
 * 供应商列表
 *-----------------------------------------------*/
CREATE TABLE t_supplier (
  sid               int(11)         NOT NULL AUTO_INCREMENT,
  name              varchar(127)    NOT NULL DEFAULT ''    COMMENT '名称',
  alias_name        varchar(127)    NOT NULL DEFAULT ''    COMMENT '别名',
  contact_name      varchar(48)     NOT NULL DEFAULT ''    COMMENT '联系人',
  phone             varchar(64)     NOT NULL DEFAULT ''    COMMENT '联系人所有电话, 英文逗号分隔',
  city              varchar(64)     NOT NULL DEFAULT ''    COMMENT '供应商城市，多个用逗号分隔',
  address           varchar(256)    not null default ''    COMMENT '地址',
  products          varchar(1024)   not null default ''    COMMENT '经营范围',
  cate1             varchar(32)     not null default ''    COMMENT '分类',
  type              tinyint(4)      NOT NULL DEFAULT '0'   COMMENT '类型: 1-厂家, 2-一批, 3-二批, 4-其他',
  book_note         text            NOT NULL               COMMENT '订货需求',
  account_balance   int             not null default 0     COMMENT '支付账款',
  
  bank_info         varchar(128)    not null default ''    COMMENT '银行信息 格式：姓名-银行账号-开户行',
  public_bank       varchar(128)    not null default ''    COMMENT '公户银行 格式：姓名-银行账号-开户行',
  delivery_hours    int             not null default 0     COMMENT '送货周期，单位：小时',
  payment_days      tinyint         not null default 0     COMMENT '账期：天',
  duty              tinyint         not null default 0     COMMENT '普票税点',
  special_duty      tinyint         not null default 0     COMMENT '专票税点',
  
  note              text            NOT NULL               COMMENT '备注',
  create_suid       int             not null default 0     comment '添加人',
  status            tinyint(4)      NOT NULL DEFAULT '5'   COMMENT '状态: 0-已审核, 5-待审核, 6-驳回，4-停用',
  ctime             timestamp       NOT NULL DEFAULT '0000-00-00 00:00:00',
  mtime             timestamp       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (sid)
  
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8;

/*----------------------------------------------------
 * 供应商的商品列表（sku列表）
 *----------------------------------------------------*/
CREATE TABLE t_supplier_sku_list(
    id                  int             NOT NULL AUTO_INCREMENT,
    supplier_id         int(11)         NOT NULL DEFAULT 0         COMMENT '供应商id',
    sku_id              int             NOT NULL DEFAULT 0         COMMENT 'sku id', 
    purchase_price      int             NOT NULL DEFAULT 0         COMMENT '采购价，单位：分',
    status              tinyint         NOT NULL DEFAULT 0         COMMENT '状态: 0-正常, 1-删除',
    ctime               timestamp       NOT NULL DEFAULT '0000-00-00 00:00:00',
    mtime               timestamp       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE (supplier_id, sku_id),
    index(sku_id)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 采购订单
 *-----------------------------------------------*/
CREATE TABLE t_in_order (
  oid               int             not null auto_increment,
  sid               int             not null default 0      comment '厂家、供应商id',
  buyer_uid         int             not null default 0      comment '采购人id',
  contact_name      varchar(48)     not null default ''     comment '联系人',
  contact_phone     varchar(48)     not null default ''     comment '联系电话',
  delivery_date     timestamp       not null default 0      comment '送货日期',
  wid               int             not null default 0      comment '仓库id',
  price             int             not null default 0      comment '总货价,不含运费 (单位:分)',
  freight           int             not null default 0      comment '运费 (单位:分)',
  privilege         int             not null default 0      comment '优惠 (单位:分)',
  privilege_note    varchar(128)    not null default ''     comment '优惠备注',
  in_order_type     tinyint         not null default 1      comment '采购类型：1-普通采购，2-赠品入库',

  rece_suid         int             not null default 0      comment '收货人（手动完全收货才有）',
  rece_time         timestamp       not null default 0      comment '收货时间(完全入库时间或者手动点完全收货时间)',

  step              tinyint         not null default 0      comment '所处阶段: 1-未确认, 2-未收货, 3-已收货, 4-已付款',
  payment_type      tinyint         not null default 0      comment '付款方式: 1-现款, 2-转账',
  paid              tinyint         not null default 0      comment '是否已支付: 0-否, 1-是',
  source            tinyint         not null default 1      comment '采购单来源: 1-常规 2-临采 3-综合',

  note              text            not null                comment '备注',
  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除, 2-取消',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (oid),
  index (sid),
  index (step),
  index (ctime)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=6000;


/*-----------------------------------------------
 * 采购订单-商品清单表
 *-----------------------------------------------*/
CREATE TABLE t_in_order_product (
  oid               int             not null default 0      comment '订单id',
  sid               int             not null default 0      comment 'sku id',
  source            tinyint         not null default 1      comment '采购类型 1-普采 2-临采',
  price             int             not null default 0      comment '商品单价(单位:分) 这里需要记单价,因为价格在变化',
  sale_price        int             not null default 0      comment '商品售价(单位:分)',
  num               int             not null default 0      comment '购买数量',
  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除',
  sales_oids        varchar(64)     not null default ''     comment '销售订单的id，临采单使用，多个oid逗号分隔',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (oid, sid, source),
  index(sid)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*-----------------------------------------------
 * 入库单
 *-----------------------------------------------*/
CREATE TABLE t_stock_in (
  id                int             not null auto_increment,
  sid               int             not null default 0      comment '厂家、供应商id',
  oid               int             not null default 0      comment '订单id',
  wid               int             not null default 0      comment '仓库id',
  statement_id      int             not null DEFAULT 0      COMMENT '结算单id',
  buyer_uid         int             not null default 0      comment '采购人id',
  stockin_suid      int             not null default 0      comment '入库人id',
  shelved_suid      int             not null default 0      comment '上架人id',
  price             int             not null default 0      comment '总货价,不含运费 (单位:分)',
  step              tinyint         not null default 0      comment '所处阶段: 1-已入库，2-已上架，3-部分上架',
  payment_type      tinyint         not null default 0      comment '付款方式: 1-现款, 2-转账',
  paid_source       tinyint         not null default 0      comment '款项来源: 1-民生私户123, 2-民生公户234, ....',
  paid              tinyint         not null default 0      comment '是否付款: 0-未付款, 1-已付款, 2-兑账未付',
  real_amount       int             not null default 0      comment '实际支付 (单位:分)',
  source            tinyint         not null default 1      comment '采购单来源: 1-常规 2-临采',

  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除, 2-取消',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  note              text            not null                comment '备注',

  PRIMARY KEY (id),
  index (sid),
  index (step),
  index (statement_id),
  index (ctime)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=6000;


/*-----------------------------------------------
 * 入库单-商品清单表
 *-----------------------------------------------*/
CREATE TABLE t_stock_in_product(
  id                int             not null default 0      comment '入库单id',
  sid               int             not null default 0      comment 'sku id',
  srid              int             not null default 0      comment '入库退货单id',
  price             int             not null default 0      comment '商品单价(单位:分) 这里需要记单价,因为价格在变化',
  sale_price        int             not null default 0      comment '商品售价(单位:分)',
  num               int             not null default 0      comment '购买数量',
  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除',
  location          varchar(16)     not null default ''     comment '货物的货位',

  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id, sid, srid),
  index (sid),
  index (srid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*----------------------------------------------------
 *  入库退货单
 *---------------------------------------------------*/
CREATE TABLE t_stock_in_refund(
  srid              int             not null auto_increment,
  stockin_id        int             not null default 0      comment '入库单id',
  statement_id        int           not null default 0      comment '结算单id',
  supplier_id       int             not null default 0      comment '供应商',
  wid               int             not null default 0      comment '仓库id',
  price             int             not null default 0      comment '总退货价，单位（分）',
  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除, 2-取消',
  step              tinyint         not null default 0      comment '所处阶段: 1-未退货, 2-已退货,',
  suid              int             not null default 0      comment '操作员id',
  stockout_time     timestamp       not null default 0      comment '退货出库时间',

  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  note              text            not null                comment '备注',

  PRIMARY KEY (srid),
  index (supplier_id),
  index (stockin_id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1000;

/*-----------------------------------------------
 *  移库单
 *----------------------------------------------*/
CREATE TABLE t_stock_shift(
  ssid              int         not null auto_increment,
  src_wid           int         not null default 0          comment '原仓库id',
  des_wid           int         not null default 0          comment '目的仓库id',
  step              tinyint     not null default 0          comment '所处阶段 1-未出库 2-已出库 3-已入库 4-已上架',
  create_suid       int         not null default 0          comment '创建人id',
  stockout_suid     int         not null default 0          comment '出库人id',
  stockin_suid      int         not null default 0          comment '入库人id',
  shelved_suid      int         not null default 0          comment '上架人id',
  out_time          timestamp       not null default 0      comment '调拨出库时间',
  in_time           timestamp       not null default 0      comment '调拨入库时间',  
  note              text            not null                comment '备注',

  status            tinyint     not null default 0          comment '状态: 0-正常, 1-取消',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (ssid),
  index(ctime)

) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=6000;

/*-----------------------------------------------
 *  移库单 - 商品清单表
 *----------------------------------------------*/
CREATE TABLE t_stock_shift_product(
  ssid              int             not null default 0      comment '移库单id',
  sid               int             not null default 0      comment 'sku id',
  num               int             not null default 0      comment '数量',
  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除',
  from_location     varchar(100)    not null default ''     comment '移出货物的货位,json',
  to_location       varchar(16)     not null default ''     comment '移入货物的货位',
  abnormal_num      int             not null default 0      comment '异常数量',
  abnormal_location varchar(64)     not null default ''     comment '异常货位',
  cost              int             not null default 0      comment '成本, 单位:分',
  price             int             not null default 0      comment '价格, 单位:分',
  vnum              int             not null default 0      comment '空采空配数量',

  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (ssid, sid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*-----------------------------------------------
 * 付款单记录 - 供应商账目明细表（客户：供应商）
 *-----------------------------------------------*/
CREATE TABLE t_money_out_history(
    id              int             not null AUTO_INCREMENT,
    sid             int             not null default 0      comment '供应商id',
    objid           int             not null default 0      comment '单据id',
    wid             int             not null default 0      comment '仓库id',
    price           int             not null default 0      comment '支付金额（单位：分）',
    amount          int             not null default 0      comment '需要支付金额（单位：分）',
    type            tinyint         not null default 0      comment '支付金额类型: 0-订单支付, 1-财务付款, 2-财务调账',
    payment_type    tinyint         not null default 0      comment '付款方式: 1-现款, 2-转账',
    paid_source     tinyint         not null default 0      comment '款项来源: 1-民生私户123, 2-民生公户234, ....',
    suid            int             not null default 0      comment '执行人',
    note            text            not null                comment '备注',

    status          tinyint         not null default 0      comment '状态: 0-正常, 1-删除, 2-取消',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    primary key (id),
    index(ctime),
    index(objid),
    index(sid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*------------------------------------------------
 *  收款单记录 - 客户账目明细表 （对象：客户）
 *------------------------------------------------*/
CREATE TABLE t_money_in_history(
    id              int             not null AUTO_INCREMENT,
    cid             int             not null default 0      comment '客户id',
    uid             int             not null default 0      comment '用户id',
    objid           int             not null default 0      comment '单据id: 订单/退款单(等)id',
    oid             int             not null default 0      comment '销售单id',
    wid             int             not null default 0      comment '仓库id',
    city_id         int             not null default 0      comment '城市id',
    price           int             not null default 0      comment '支付金额（单位：分）',
    amount          int             not null default 0      comment '汇总金额（单位：分）',
    type            tinyint         not null default 0      comment '支付金额类型: 0-订单支付, 1-退款单支付, 2-财务收款, 3-财务调账',
    suid            int             not null default 0      comment '执行人',
    payment_type    tinyint         not null default 0      comment '付款方式',
    note            text            not null                comment '备注',

    status          tinyint         not null default 0      comment '状态: 0-正常, 1-删除, 2-取消',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    primary key (id),
    index(ctime),
    index(objid),
    index(cid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**----------------------------------------------
 *  客户账户金额历史表 - 余额流水
 ----------------------------------------------*/
CREATE TABLE t_customer_amount_history(
    id              int             not null AUTO_INCREMENT,
    cid             int             not null default 0          comment '客户id',
    uid             int             not null default 0          comment '用户id',
    objid           int             not null default 0          comment '单据id: 订单/退款单(等)id',
    oid             int             not null default 0          comment '对应订单id',
    city_id         int             not null default 0          comment '城市id',
    price           int             not null default 0          comment '支付金额（单位：分）',
    amount          int             not null default 0          comment '账户余额（单位：分）',
    type            int             not null default 0          comment '支付金额类型：1-预存 2-返现 3-支付 4-退款 5-提现',
    suid            int             not null default 0          comment '执行人id',
    saler_suid      int             not null default 0          comment '客户对应的销售，如：标记预存款对应的销售',
    payment_type    tinyint         not null default 0          comment '付款方式',
    note            text            not null                    comment '备注',

    status          tinyint         not null default 0          comment '状态: 0-正常, 1-删除, 2-取消',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    primary key (id),
    index(cid),
    index(objid),
    index(ctime)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1000;




/**----------------------------------------------
 * 第三方合作工人支出表 - 司机，搬运工
 *-----------------------------------------------*/
CREATE TABLE t_coopworker_money_out_history(
    id              int             not null AUTO_INCREMENT,
    cuid            int             not null default 0      comment '工人id',
    oid             int             not null default 0      comment '订单id',
    wid             int             not null default 0      comment '仓库id',
    city_id         int             not null default 0      comment '城市id',
    price           int             not null default 0      comment '支付金额（单位：分）',
    amount          int             not null default 0      comment '汇总金额（单位：分）',
    type            tinyint         not null default 0      comment '支付类型：1-运费 2-搬运费 10-奖励 11-罚款',
    suid            int             not null default 0      comment '执行人',
    payment_type    tinyint         not null default 0      comment '付款方式, 系统统一定义方式',
    note            text            not null                comment '备注',

    status          tinyint         not null default 0      comment '状态：0-正常 1-删除',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    primary key (id),
    index(ctime),
    index(cuid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 订单和司机/搬运工的映射表
 *-----------------------------------------------*/
CREATE TABLE t_coopworker_order (
    id              int             not null AUTO_INCREMENT,
    cuid            int             not null default 0      comment '工人id',
    car_model       tinyint         not NULL default 0      comment '车型',
    oid             int             not null default 0      comment '订单id',
    wid             int             not null default 0      comment '仓库id',
    price           int             not null default 0      comment '支付金额（单位：分）',
    base_price      int             not null default 0      comment '基础费用',
    refer_price     int             not null default 0      comment '推荐费用',
    other_price     varchar(128)    not null default ''     comment '附加费用，多个类型费用集合',
    times           int             not null default 1      comment '运送趟数',
    money_note      text            not null                comment '运费备注',
    type            tinyint         not null default 0      comment '费用类型：1-运费 2-搬运费',
    suid            int             not null default 0      comment '执行人',
    paid            tinyint         not null default 0      comment '是否支付：0-否 1-是',

    alloc_time      timestamp       not null default 0      comment '派单时间',
    confirm_time    timestamp       not null default 0      comment '订单确认时间',
    delivery_time   timestamp       not null default 0      comment '出库时间',
    arrival_time    timestamp       not null default 0      comment '送达客户的时间',
    finish_time     timestamp       not null default 0      comment '回单时间',
    statement_id    int             not null default 0      comment '结算单ID',
    note            text            not null                comment '备注',

    status          tinyint         not null default 0      comment '状态：0-正常 1-删除',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    primary key (id),
    index(oid),
    index(ctime),
    index(cuid),
    index(statement_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=100;

/*-----------------------------------------------
 * 司机/搬运工结算单表
 *-----------------------------------------------*/
CREATE TABLE t_coopworker_statement (
    id              int             not null AUTO_INCREMENT,
    cuid            int             not null default 0      comment '合作工人的id',
    user_type       tinyint         not null default 0      comment '工人类型：1-司机 2-搬运工',
    suid            int             not null default 0      comment '执行人',
    sure_suid       int             not null default 0      comment '确认人',
    check_suid      int             not null default 0      comment '审核人',
    price           int             not null default 0      comment '总结算费用,单位:分',
    batch           char(20)        not null default ''     comment '批次号',
    wid             int             not null default 0      comment '仓库id',
    step            tinyint         not null default 1      comment '结算单状态:1-已创建 2-已确认 3-驳回 4-已审核 5-已支付',
    status          tinyint         not null default 0      comment '状态：0-正常 1-删除',
    pay_time        timestamp       not null default 0      comment '结算日期',
    payment_type    tinyint         not null default 0      comment '付款方式, 系统统一定义方式',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    primary key (id),
    index(batch),
    index(cuid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=100;


/*-----------------------------------------------
 * 图片表
 *-----------------------------------------------*/
CREATE TABLE t_picture (
  pid               int             not null auto_increment,
  pictag            varchar(64)     not null default '' comment '图片pictag(id.type)',
  width             int(11)         not null default 0,
  height            int(11)         not null default 0,
  srcinfo           varchar(256)    not null default '' comment '来源信息(json格式:src_pictag, 裁剪相关信息等)',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000;


/*-----------------------------------------------
 * 后台用户账号表
 *-----------------------------------------------*/
CREATE TABLE t_staff_user (
  suid              int             not null auto_increment,
  name              varchar(32)     not null default ''     comment '姓名',
  pinyin            varchar(128)    not null default ''     comment '姓名拼音',
  mobile            char(11)        not null default ''     comment '登陆手机号(必填)',
  password          varchar(32)     not null default ''     comment '密码',
  salt              smallint        not null default 0      comment '密码salt',

  roles             varchar(200)    not null default ''     comment '角色列表',
  department        INT             not null default 0      comment '部门',
  wids              varchar(200)    not null default ''     comment '仓库串，多个城市之间用逗号(,)隔开',
  cities            varchar(200)    not null default ''     comment '城市串，多个城市之间用逗号(,)隔开',
  leader_suid       int             not null default 0      comment '直接领导id',

  verify            varchar(200)    not null default ''     comment '身份验证信息',
  last_login_ip     varchar(50)     not null default ''     comment '最后登录ip',

  sex               tinyint         not null default 0      comment '性别 0-男 1-女',
  birthday          date            not null default 0      comment '生日',
  wx_openid         varchar(24)     not null default ''     comment '微信openid',
  qq                int             not null default 0      comment 'QQ号',
  email             varchar(128)    not null default ''     comment 'email',

  status            tinyint         not null default 0      comment '状态: 0-正常 1-删除 2-封禁',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    
  PRIMARY KEY (suid),
  index (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1000;

/*-----------------------------------------------
 * 权限角色表
 *-----------------------------------------------*/
CREATE TABLE t_role (
  id                int            NOT NULL AUTO_INCREMENT,
  role              varchar(100)   NOT NULL DEFAULT ''  comment '权限名称',
  rkey              varchar(100)   not null default ''  comment '唯一标识符',
  department        INT            not null default 0   comment '部门',
  permission        text           not null             comment '角色权限列表，以“,”分隔',
  suid              INT            not null default 0   comment '',
  rel_role          varchar(255)   not null default '' comment '关联的角色id,多个用逗号隔开',

  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  status            int            not null default 0,

  PRIMARY KEY (id),
  UNIQUE KEY (rkey)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*-----------------------------------------------
 * KVDB
 *-----------------------------------------------*/
CREATE TABLE t_kvstore (
  id                INT             not null auto_increment,
  name              VARCHAR(100)    not null                comment 'name',
  value             VARCHAR(800)    not NULL                comment 'value',
  expire_at         TIMESTAMP       not NULL default 0      comment '过期时间',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  index (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 司机信息表
 *-----------------------------------------------*/
CREATE TABLE t_cooperworker (
  cuid              int             not null auto_increment,
  name              varchar(100)    not null                comment '工人名字',
  mobile            varchar(50)     not null                comment '工人电话',
  type              smallint        not null default 1      comment '工人类型：{1：司机；2：搬运工}',
  password          varchar(32)     not null default ''     comment '密码',
  salt              smallint        not null default 0      comment '密码salt',

  city_id           INT             not null default 0      comment '城市',
  wid               INT             not null default 0      comment '仓库',

  -- 司机必选
  car_model         INT             not NULL default 0      comment '车型',
  car_province      char(3)         not null default ''     comment '车牌省份',
  car_number        char(6)         not null default ''     comment '车牌号',

  real_name         varchar(30)     not null default ''     comment '收款人',
  card_num          varchar(25)     not null default ''     comment '银行卡号',
  bank_info         varchar(128)    not null default ''     comment '开户行',
  score             int             not null default 60     comment '评分',

  note              varchar(500)    not null default ''     comment '备注',
  status            TINYINT         not null default 0      comment '是否被删除 0-否 1-是 5-未审核',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (cuid),
  unique(mobile),
  index (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1000;

/*-----------------------------------------------
 * 系统操作日志表
 *-----------------------------------------------*/
CREATE TABLE t_admin_log (
    lid             int             not null auto_increment,
    admin_id        int             not null default 0          comment '管理员id',
    obj_id          int             not null default 0          comment '操作对象id',
    obj_type        int             not null default 0          comment '对象类型',
    action_type     smallint        not null default 0          comment '操作类型',
    params          varchar(1000)   not null default ''         comment '内容',
    city_id         int             not null default 0          comment '城市id',
    wid             int             not null default 0          comment '仓库id',
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (lid),
    index (obj_id, obj_type),
    index (admin_id)

)ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 订单操作日志
 *-----------------------------------------------*/
CREATE TABLE t_order_action_log (
  lid               int             not null auto_increment,
  oid               int             not null default 0          comment '订单id',
  admin_id          int             not null default 0          comment '管理员id',
  action_type       smallint        not null default 0          comment '操作类型',
  params            varchar(500)    not null default ''         comment '操作参数',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (lid),
  index (admin_id),
  index (oid)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 小区-库房距离，以及各个车型价格表
 *-----------------------------------------------*/
CREATE TABLE t_community_distance_fee (
    id              int             not null auto_increment,
    cmid            int             not null default 0    comment '小区id',
    wid             int             not null default 0    comment '仓库id',
    car_model       int             not null default 0    comment '车型id',
    distance        int             not null default 0    comment '距离（米）',
    note            text            not NULL              comment '备注',

    status          tinyint         not null default 0    comment '状态',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE (cmid, wid)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 队列
 *-----------------------------------------------*/
CREATE TABLE t_queue (
    id              int             not null auto_increment,
    type            int             not null default 0,
    info            text            not null              comment 'data',
    status          tinyint(4)      not null default 0    comment '0-pending 1-finished 2-failed',
    ctime           timestamp       not null default current_timestamp,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    primary key (id),
    index (type, status)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

