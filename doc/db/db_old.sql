set names utf8;

/*-----------------------------------------------
 * 客户(公司)表: 如果工长没有公司名称,则写“姓名+师傅”
 *-----------------------------------------------*/
CREATE TABLE t_customer (
  cid               int             not null auto_increment,
  name              varchar(128)    not null default ''     comment '客户/公司名称',
  contact_uid       int             not null default 0      comment '联系人uid',                    /*@todo_del*/
  contact_name      varchar(48)     not null default ''     comment '联系人',                       /*@todo_del*/
  phone             varchar(64)     not null default ''     comment '联系人所有电话, 英文逗号分隔',     /*@todo_del*/
  qq                bigint          not null default 0      comment '联系人QQ',                     /*@todo_del*/
  weixin            varchar(32)     not null default ''     comment '联系人微信',                   /*@todo_del*/
  all_user_names    text            not null                comment '客户对应所有用户的名字， 冗余数据搜素使用, ","分隔',
  all_user_mobiles  text            not null                comment '客户对应所有用户的手机号， 冗余数据搜索使用, ","分隔',
  city              SMALLINT        not null default 0      comment '城市',
  district          int             not null default 0      comment '地区',
  area              int             not null default 0      comment '商圈',
  address           varchar(256)    not null default ''     comment '门店地址',
  note              text            not null                comment '其他备注',
  member_date       date            not null default 0      comment '登记会员日期',
  record_suid       int             not null default 0      comment '录入专员',
  sales_suid        int             not null default 0      comment '所属销售专员 (t_staff_user)',
  city_id           INT             not null default 0      comment '城市id',
  sales_suid2       int             not null default 0      comment '所属电话销售专员 (t_staff_user)',      /*@todo_del */
  identity          tinyint         not null default 0      comment '客户身份：1-工长，2-公司',
  level_for_saler   tinyint         not null default 0      comment '客户级别(来自销售) 1-小客户 2-大客户',
  sale_status       tinyint         not null default 0      comment '销售状态   1-私海用户(专有) 2-公海用户(公共) 3-内海(特殊运营) 4-待分配 99-非服务对象',
  lday_2_public     tinyint         not null default 0      comment '待掉公海的天数',
  chg_sstatus_time  timestamp       not null default 0      comment '修改销售状态的时间',
  mark_intend_time  timestamp       not null default 0      comment '电销标记为意向的时间',
  level_for_sys     tinyint         not null default 0      comment '系统定义的客户级别：5-vip客户 4-优质客户 3-普通客户 2-待观察客户 1-恶劣客户',
  mode              tinyint         not null default 0      comment '类型: 1-公司有门店, 2-公司无门店, 3-工长无门店',
  way               tinyint         not null default 0      comment '客户获取方式: 1-地推 2-网络 3-转介绍 4-缘故',
  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除, 2-封禁',
  kind              tinyint         not null default 0      comment '类型: 1-无意向下单, 2-有意向下单',     /*未使用*/
  code              varchar(256)    not null default ''     comment '销售码或者推荐客户手机',
  rcmd_cid          int             not null default 0      comment '推荐客户的cid',
  reg_source        int             not null default 0      comment '用户的注册来源，来自第三方注册，保存第三方的appid',
  source            tinyint         not null default 0      comment '客户来源',
  rival_desc        tinyint         not null default 0      comment '客户在竞争对手的下单情况',
  first_order_date  date            not null default 0      comment '首单日期',
  second_order_date date            not null default 0      comment '第二次下单日期，统计复购',
  last_order_date   date            not null default 0      comment '最后一次下单日期',
  order_num         int             not null default 0      comment '下单数 (必须是完成付款的有效订单)',
  online_order_num  int             not null default 0      comment '在线下单数',
  account_balance   int             not null default 0      comment '客户应付金额(欠款)',
  account_amount    int             not null default 0      comment '账户可使用的金额 (余额)',
  order_amount      int             not null default 0      comment '总购买额（返现使用）',
  total_amount      int             not null default 0      comment '总消费额',
  refund_amount     int             not null default 0      comment '总退款额',
  refund_num        int             not null default 0      comment '总退单数量',
  perpay_amount     int             not null default 0      comment '总预付',
  total_privilege   int             not null default 0      comment '总优惠',
  payment_due_date  DATE            NOT NULL default 0      comment '应结账日期',
  remind_count      INT             NOT NULL DEFAULT 0      comment '催款次数',
  last_remind_suid  INT             not null default 0      comment '最后催账人',
  last_remind_date  datetime        not null default 0      comment '最后催账日期',
  visit_due_date    DATE            NOT NULL DEFAULT 0      comment '应回访日期',
  bid               INT             not null default 0      comment '所属企业id',
  is_auto_save      tinyint         not null default 0      comment '是否自动添加的，是的话需要后期跟进信息',
  nick_name         varchar(50)     not null default ''     comment '称呼',
  age               int             not null default 0      comment '年龄',
  sex               tinyint         not null default 1      comment '0男1女',
  birth_place       varchar(50)     not null default ''     comment '籍贯',
  work_age          int             not null default 0      comment '工龄',
  interest          varchar(100)    not null default ''     comment '兴趣爱好',
  work_area         varchar(100)    not null default ''     comment '工作区域',
  email             varchar(50)     not null default ''     comment '邮箱',
  character_tag     varchar(100)    not null default ''     comment '性格标签',
  birthday          varchar(20)     not null default ''     comment '生日',


  has_duty          tinyint         not null default 2      comment '是否含税客户，1是，2不是',
  payment_days      INT             NOT NULL DEFAULT 0      comment '账期',
  payment_amount    int             not null default 0      comment '账额',
  contract_btime    DATE            not null default 0      comment '合同起始日期',
  contract_etime    DATE            not null default 0      comment '合同终止日期',
  discount_ratio    tinyint         not null default 0      comment '折扣比例',

  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (cid),
  index (name),
  index (district, area),
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
  phone             varchar(64)     not null default ''     comment '联系人所有电话, 英文逗号分隔',     /*@todo_del*/
  wx_openid         varchar(24)     not null default ''     comment '微信openid',
  qq                bigint          not null default 0      comment 'QQ号',
  weixin            varchar(32)     not null default ''     comment '联系人微信',
  email             varchar(128)    not null default ''     comment 'email',
  sex               tinyint         not null default 0      comment '性别 0-男 1-女',
  birthday          date            not null default 0      comment '生日',
  hometown          varchar(32)     not null default ''     comment '籍贯',
  children          varchar(256)    not null default ''     comment '子女情况; 无子女为空;',
  password          varchar(32)     not null default ''     comment '密码',
  salt              smallint        not null default 0      comment '密码salt',
  is_admin          tinyint         not null default 1      comment '是否为管理员',
  channel           VARCHAR(100)    not null default ''     comment '用户注册渠道',
  position          tinyint         not null default 0      comment '身份: 0-未知, 1-老板/经理, 2-员工',
  logurl            varchar(256)    not null default ''     comment '用户头像',
  grade             tinyint         not null default 1      comment '客户等级',
  frozen_point      int             not null default 0      comment '冻结积分',
  vaild_point       int             not null default 0      comment '有效积分',
  real_name         varchar(100)    not null default ''     comment '真实姓名',
  id_card_no        varchar(50)     not null default ''     comment '身份证号码',
  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除, 2-封禁',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (uid),
  index (cid),
  unique (mobile),
  index (wx_openid),
  index (qq)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=6000;


/*-----------------------------------------------
 * 优惠券
 *-----------------------------------------------*/
CREATE TABLE t_coupon (
  id                int             not null auto_increment comment '优惠券id',
  cid               int             not null default 0      comment '客户id',
  send_suid         int             not null default 0      comment '发券人id',
  oid               int             not null default 0      comment '使用了此优惠券的订单id',
  tid               int             not null default 0      comment '优惠类型券id',
  aid               INT             NOT NULL DEFAULT 0      COMMENT '优惠活动id',
  from_oid          int             NOT NULL DEFAULT 0      COMMENT '送券订单id',
  code              int             not null default 0      comment '优惠券编号',
  amount            int             not null default 0      comment '优惠券面值',
  occupied          tinyint         not null default 0      comment '优惠券占用状态，券的临时状态，记录客服标记状态',
  used              tinyint         not null default 0      comment '是否使用: 0-否, 1-已使用',
  type              tinyint         not null default 0      comment '券的类型: 1-新用户返券, 2-累计返券, 3-销售人员给老用户返券, 4-未下单用户营销返券',
  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除, 5-未审核',
  start_time        TIMESTAMP       NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '起始时间',
  deadline          timestamp       not null default 0      comment '使用截止日期',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  cate          tinyint         not null default 1      comment '优惠券类别，是现金券还是VIP现金券',
  PRIMARY KEY (id),
  index(cid),
  index(oid),
  index(tid),
  index(aid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*-----------------------------------------------
 * 优惠券申请
 *-----------------------------------------------*/
CREATE TABLE t_coupon_apply (
  id                int             not null auto_increment comment '优惠券id',
  cid               int             not null default 0      comment '客户id',
  sales_suid        int             not null default 0      comment '销售人员id',
  admin_suid        int             not null default 0      comment '审批人员id',
  reason            tinyint         not null default 0      comment '理由: 详见配置文件',
  status            tinyint         not null default 1      comment '状态: 1-未审核, 2-拒绝, 3-已通过/已发券, 4-删除, ',
  note              text            not null                comment '其他原因或备注',
  coupons           varchar(256)    not null default ''     comment '优惠券,json格式[{amount:,num:}]',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  index(cid),
  index(sales_suid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*-----------------------------------------------
 * 返现
 *-----------------------------------------------*/
CREATE TABLE t_cashback (
  cid               int             not null default 0      comment '客户id',
  order_amount      int             not null default 0      comment '已返现的消息金额',
  cashback          int             not null default 0      comment '优惠券总面值',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (cid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*-----------------------------------------------
 * 短信队列
 *-----------------------------------------------*/
CREATE TABLE t_sms_queue (
  id                int             not null auto_increment comment 'id',
  cid               int             not null default 0      comment '公司/客户id',
  mobile            varchar(64)     not null default ''     comment '手机号',
  sent              tinyint         not null default 0      comment '是否已发短信: 0-否, 1-是',
  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  words             text,
  PRIMARY KEY (id),
  index (sent)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*-----------------------------------------------
 * 客户历史记录 (没有使用)
 *-----------------------------------------------*/
CREATE TABLE t_customer_history (
  id                int             not null auto_increment,
  cid               int             not null default 0      comment '公司/客户id',
  type              tinyint         not null default 0      comment '类型: 定义见配置文件',
  ext_info          varchar(512)    not null default ''     comment '附注信息, json格式',
  pic_ids           varchar(256)    not null default ''     comment '照片id列表, 以逗号分隔',
  note              varchar(4096)   not null default ''     comment '备注',
  suid              int             not null default 0      comment '相关员工id',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  index (cid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=0;


/*-----------------------------------------------
 * 建筑工地
 *-----------------------------------------------*/
CREATE TABLE t_construction_site (
  id                int             not null auto_increment,
  cid               int             not null default 0      comment '客户id',
  uid               int             not null default 0      comment '用户id',
  contact_name      varchar(48)     not null default ''     comment '联系人',
  contact_phone     varchar(48)     not null default ''     comment '联系电话',
  contact_phone2    varchar(48)     not null default ''     comment '联系电话2',
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
  suid              int             not null default 0      comment '编辑地址的工作人员id',
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
  aftersale_type    int             not null default 0      comment '售后单据类型，2-退货单',
  aftersale_id      int             not null default 0      comment '售后单据id，退货单是rid',
  
  contact_name      varchar(48)     not null default ''     comment '联系人',
  contact_phone     varchar(48)     not null default ''     comment '联系电话',
  contact_phone2    varchar(48)     not null default ''     comment '联系电话',
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

  product_num       smallint        not null default 0      comment '商品品类数目',
  price             int             not null default 0      comment '总货价,不含运费 (单位:分)',
  freight           int             not null default 0      comment '运费 (单位:分)',
  customer_carriage int             not null default 0      comment '客户支付搬运费 (单位:分)',
  privilege         int             not null default 0      comment '优惠 (单位:分)',
  sale_privilege    int             not null default 0      comment '销售优惠',
  refund            int             not null default 0      comment '退款 (单位:分)',
  real_amount       int             not null default 0      comment '实际收款（单位:分; 财务收款）',
  privilege_note    varchar(128)    not null default ''     comment '优惠备注',
  payment_type      tinyint         not null default 0      comment '付款方式',
  paid              tinyint         not null default 0      comment '是否已收款',
  customer_payment_type int         not null default 0      comment '客户选择的支付方式，在线支付和货到付款',
--   driver_name       varchar(48)     not null default ''     comment '司机名称',
--   driver_phone      varchar(48)     not null default ''     comment '司机电话',
--   driver_money      int             not null default 0      comment '司机运费 (单位:分)',
--   driver_money_paid tinyint         not null default 0      comment '是否支付搬运费 0-否, 1-是',
--   carrier_name      varchar(128)    not null default ''     comment '搬运工名称',
--   carrier_phone     varchar(48)     not null default ''     comment '搬运工电话',
--   carrier_money     int             not null default 0      comment '搬运工费用 (单位:分)',
--   carrier_money_paid tinyint        not null default 0      comment '是否支付搬运费 0-否, 1-是',
  suid              int             not null default 0      comment '录单人员uid',
  sure_suid         int             not null default 0      comment '客服q确认uid',
  saler_suid        int             not null default 0      comment '销售销售人员id',
  sure_time         timestamp       not null default 0      comment '确认时间',
  picked_time       timestamp       not null default 0      comment '拣货时间',
  ship_time         timestamp       not null default 0      comment '出库时间',
  back_time         timestamp       not null default 0      comment '回单时间',
  pay_time          timestamp       not null default 0      comment '付款时间',
  
  note              varchar(4096)   not null default ''     comment '备注',
  bid               int             not null default 0      comment '订单所属企业',
  line_id           int             not null default 0      comment '排线id',
  has_print         tinyint         not null default 0      comment '是否已打印',
  has_duty          tinyint         not null default 2      comment '是否含税，1含，2不含',
  op_note           varchar(255)    not null default ''     comment '操作备注：xxx:n,yyy:m',
  customer_note     varchar(4096)   not null default ''     comment '客户写的备注',
  is_guaranteed     tinyint         not null default 2      comment '是否担保，1-担保，2-未担保',
  picking_group     varchar(64)     not null default ''     comment '拣货组 json',
   
  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除, 3-取消', 
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (oid),
  index (cid),
  index (delivery_date),
  index (ship_time),
  index (pay_time),
  index (ctime),
  index (community_id),
  index (saler_suid),
  index (source_oid),
  index (aftersale_id),
  index (back_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=6000;


/*-----------------------------------------------
 * 订单-商品清单表 (订单快照)
 *-----------------------------------------------*/
CREATE TABLE t_order_product (
  oid               int             not null default 0      comment '订单id',
  pid               int             not null default 0      comment '商品id',
  rid               int             not null default 0      comment '退货单id.退款才有.退款到num.',
  price             int             not null default 0      comment '商品单价(单位:分) 这里需要记单价,因为价格在变化',
  ori_price         int             not null default 0      comment '商品原价',
  privilgee         int             not null default 0      comment '优惠金额(单位:分)',
  cost              int             not null default 0      comment '下单时商品的成本',
  wid               int             not null default 0      comment '订单商品所属仓库',
  num               int             not null default 0      comment '购买数量 or 实际退货数量',
  vnum              int             not null default 0      comment '空采空配数量',
  apply_rnum        int             not null default 0      comment '申请退货数量',
  status            tinyint         not null default 0      comment '状态: 0-正常, 1-整个订单删除, 3-单独删除商品/取消',
  note              varchar(256)    not null default ''     comment '备注',
  sid               INT             not null default 0      comment 'sku id',
  city_id           INT             not null default 0      comment '多城市',
  location          varchar(80)     not null default ''     comment '货物的货位,售出货品可能来自多个货位',
  picked            INT             not null default 0      comment '已捡货数量 or 退货入库数量',
  damaged_num       int             not null default 0      comment '退货单损坏数量 入库完成后：计算报损数量=num-picked',
  tmp_bought_num    int             not null default 0      comment '临采已采数量 [字段不使用了]',
  tmp_inorder_num   int             not null default 0      comment '已做采购单的数量',
  tmp_inorder_id    int             not null default 0      comment '临采单id',
  refund_vnum       int             not null default 0      comment '空退数量',
  vnum_deal_type    tinyint         not null default 0      comment '空采处理方式 1-已外采',
  picked_time       TIMESTAMP       not null default 0      comment '拣货时间',
  managing_mode     TINYINT         NOT NULL DEFAULT 1      COMMENT '经营模式，1-自营，2-联营',
  outsourcer_id     int             not null default 0      comment '外包供应商id',
   
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
 * 退款单-基本信息表
 *-----------------------------------------------*/
CREATE TABLE t_refund (
  rid               int             not null auto_increment,
  cid               int             not null default 0      comment '客户id',
  uid               int             not null default 0      comment '用户id',
  oid               int             not null default 0      comment '订单id',
  wid               int             not null default 0      comment '仓库id',

  type              int             not null default 0      comment '退货方式：1-现场退货，2-单独退货，3-预约退货',
  rel_type          tinyint         not null DEFAULT 0      COMMENT '关联单据类型：1-订单，2-换货单',
  rel_oid           int             not null default 0      comment '关联订单',
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
  refund_coupon     varchar(200)    not null default ''     comment '可退优惠券配置',
  
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
 * 排线表
 *----------------------------------------------*/
CREATE TABLE t_order_line (
    id              int             not null auto_increment,
    wid             int             not null default 0      comment '仓库id',
    oids            varchar(80)     not null default ''     comment '订单id，逗号分隔',
    delivery_date   timestamp       not null default 0      comment '配送时间',
    address         varchar(256)    not null default ''     comment '工地地址',
    priority        tinyint         not null default 0      comment '配送优先级，数字越大优先级越高',
    car_models      varchar(64)     not null default ''     comment '车型 逗号分隔 D4:1:10000,D2:0:6000',
    driver_fee      int             not null default 0      comment '线路的运费(总)',
    locked          tinyint(2)      not null default 0      comment '线路是否锁定 0-锁定 1-解锁',
    suid            int             not null default 0      comment '操作人/排线人',
    step            tinyint         not null default 0      comment '0-未分配司机，1-部分分配司，2-已分配司',
    trans_scope     varchar(64)     not null default ''     comment '线路运输范围 逗号分隔',
    can_trash       tinyint(1)      not null default 0      comment '是否愿意拉垃圾',
    can_escort      tinyint(1)      not null default 0      comment '是否愿意押车',
    status          tinyint         not null default 0      comment '状态: 0-正常, 1-删除',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    index(delivery_date)

) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=10;


/*-----------------------------------------------
 * 司机排队列表
 *----------------------------------------------*/
CREATE TABLE t_driver_queue(
    id              int             not null auto_increment,
    line_id         int             not null default 0      comment '线路id',
    did             int             not null default 0      comment '司机的id',
    name            VARCHAR(100)    not null                comment '司机名字',
    wid             int             not null default 0      comment '仓库id',
    fee             INT             not null default 0      comment '运费',
    car_model       int             not null default 0      comment '车型',
    check_time      TIMESTAMP       not null default 0,
    alloc_time      TIMESTAMP       not null default 0      comment '分配时间',
    refuse_time     TIMESTAMP       not null default 0      comment '最后拒单时间',
    step            tinyint         not null default 0      comment '状态：1已签到，2已派单，3已接单，4已出库，5已送达',
    refuse_num      tinyint         not null default 0      comment '拒单次数',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    status          tinyint         not null default 0,
    PRIMARY KEY (id),
    index(wid),
    index(name),
    unique key (did)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 订单-历史记录
 *-----------------------------------------------*/
CREATE TABLE t_order_history (
  id                int             not null auto_increment,
  oid               int             not null default 0      comment '类型: 订单id',
  type              tinyint         not null default 0      comment '类型: 具体见配置文件',
  note              varchar(4096)   not null default ''     comment '备注',
  suid              int             not null default 0     comment '操作人',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  index (oid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=0;


/*-----------------------------------------------
 * 品牌表
 *-----------------------------------------------*/
CREATE TABLE t_brand (
  bid               int             not null auto_increment,
  name              varchar(32)     not null default ''     comment '品牌名称',
  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除, 2-下架',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (bid),
  unique (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=100;


/*-----------------------------------------------
 * 品牌所属分类表
 *-----------------------------------------------*/
CREATE TABLE t_cate_brand (
  bid               int                      not null,
  cate2             smallint unsigned        not null default 0      comment '二级分类',
  cate3             smallint unsigned        not null default 0      comment '三级分类',
  sortby            INT                      not null default 0      comment '排序字段，越大排得越靠前，相同排序按sid降序算',
  ctime             timestamp                not null default 0,
  mtime             timestamp                not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (cate2, cate3, bid),
  KEY (cate3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*-----------------------------------------------
 * 型号表
 *-----------------------------------------------*/
CREATE TABLE t_model (
  mid               int                      not null auto_increment,
  name              varchar(32)              not null default ''     comment '品牌名称',
  cate2             smallint unsigned        not null default 0      comment '二级分类',
  cate3             smallint unsigned        not null default 0      comment '三级分类',
  status            tinyint                  not null default 0      comment '状态: 0-正常, 1-删除, 2-下架',
  sortby            INT                      not null default 0      comment '排序字段，越大排得越靠前，相同排序按sid降序算',
  ctime             timestamp                not null default 0,
  mtime             timestamp                not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (mid),
  index (cate2),
  index (cate3),
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
  /*cate3             smallint unsigned        not null default 0      comment '三级分类',*/
  bid               int                      not null default 0      comment '品牌',
  /*mid               int                      not null default 0      comment '型号',*/
  unit              varchar(32)              not null default ''     comment '单位单元,比如: 卷,袋,根',
  package           varchar(64)              not null default ''     comment '包装,比如 100 米/卷',
  picking_note      varchar(100)             not null default ''     comment '包装说明',
  detail            text                     not null                comment '商品描述',
  status            tinyint                  not null default 0      comment '状态: 0-正常, 1-删除, 4-下架',
  /*sortby            INT                      not null default 0      comment '排序字段，越大排得越靠前，相同排序按sid降序算',*/
  /*carrier_fee       int                      not null default 0      comment '楼梯上楼费 单位：分',*/
  /*carrier_fee_ele   int                      not null default 0      comment '电梯上楼费 单位：分',*/
  mids              varchar(500)             not null default ''      comment '多个品牌，mid暂且不用',
  qrcode_type       tinyint                  not null default 1       comment '二维码类型，1品类码，2单品码',
  length            INT                      not null default 0       comment '长，单位厘米',
  width             INT                      not null default 0       comment '宽，单位厘米',
  height            INT                      not null default 0       comment '高，单位厘米',
  weight            INT                      not null default 0       comment '重量，单位克',
  type              tinyint                  not null default 1       comment '类型：普通，加工等',
  rel_sku           varchar(200)             not null default ''      comment '加工类型商品，组合关系 sku1:1,sku2:4,...',
  ctime             timestamp                not null default 0,
  mtime             timestamp                not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (sid),
  index (title),
  /*index (cate3),*/
  index (cate2)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=10000;


/*-----------------------------------------------
 * 商品表
 * 注意扩展: 可能会增加字段 title, pic_id 等
 *-----------------------------------------------*/
CREATE TABLE t_product (
  pid               int             not null auto_increment,
  sid               int             not null default 0      comment 'sku id',
  cost              int             not null default 0      comment '成本, 单位:分',
  price             int             not null default 0      comment '价格, 单位:分',
  work_price        int             not null default 0      comment '工装价',
  ori_price         INT             not null default 0      comment '原价',
  sales             int             not null default 0      comment '总销量',
  sales_type        tinyint         not null default 0      comment '销售类型: 0-正常 1-促销 2-热卖',
  buy_type          tinyint         not null default 1      comment '采购类型: 普采/临采商品',
  detail            text            not null                comment '商品描述',
  status            tinyint         not null default 0      comment '状态: 0-正常 1-删除 2-下架',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  sortby            INT             not null default 0      comment '排序字段，越大排得越靠前，相同排序按sid降序算',
  carrier_fee       int             not null default 0      comment '客户-楼梯上楼费 单位：分',
  carrier_fee_ele   int             not null default 0      comment '客户-电梯上楼费 单位：分',
  worker_ca_fee     int             not null default 0      comment '工人-楼梯上楼费 单位：分',
  worker_ca_fee_ele int             not null default 0      comment '工人-电梯上楼费 单位：分',
  city_id           INT             not null DEFAULT 0      comment '城市',
  frequency         INT UNSIGNED    NOT NULL DEFAULT '0'    COMMENT '销售频次',
  managing_mode     TINYINT         NOT NULL DEFAULT 1      COMMENT '经营模式，1-自营，2-联营',
  recommend_pids    varchar(200)    not null default ''     comment '关联商品',
  alias             varchar(128)             not null default ''     comment '别名',
  picking_note      varchar(100)             not null default ''     comment '包装说明',
  PRIMARY KEY (pid),
  UNIQUE KEY (sid, city_id),
  INDEX (frequency)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=10000;

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
 *  商品的出入货位的历史记录表
 *---------------------------------------------*/
CREATE TABLE t_sku_location_history(
    id              int(11)         not null auto_increment,
    sid             int             not null default 0          comment 'sku id',
    wid             int             not null default 0          comment '仓库id',
    src_loc         char(10)        not null default ''         comment '原货位',
    des_loc         char(10)        not null default ''         comment '目标货位',
    old_num         int             not null default 0          comment '原数数量',
    chg_num         int             not null default 0          comment '变化的数量',
    iid             int             not null default 0          comment '进出货单号',
    suid            int             not null default 0          comment '执行人',
    type            tinyint         not null default 0          comment '类型: 2-盘亏, 3-盘盈 等',
    status          tinyint         not null default 0          comment '状态: 0-正常, 1-删除',
    note            varchar(100)    not null default ''         comment '备注',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    index(wid, sid)

)ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=100;


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
  product_num       smallint        not null default 0      comment '商品品类数目',
  price             int             not null default 0      comment '总货价,不含运费 (单位:分)',
  freight           int             not null default 0      comment '运费 (单位:分)',
  privilege         int             not null default 0      comment '优惠 (单位:分)',
  privilege_note    varchar(128)    not null default ''     comment '优惠备注',
  note              text            not null                comment '备注',
  in_order_type     tinyint         not null default 1      comment '采购类型：1-普通采购，2-赠品入库',
  is_timeout        tinyint         not null default 0      comment '是否超时：0-否，1-是',
  rece_suid         int             not null default 0      comment '收货人（手动完全收货才有）',
  rece_time         timestamp       not null default 0      comment '收货时间(完全入库时间或者手动点完全收货时间)',
  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除, 2-取消',
  step              tinyint         not null default 0      comment '所处阶段: 1-未确认, 2-未收货, 3-已收货, 4-已付款',
  payment_type      tinyint         not null default 0      comment '付款方式: 1-现款, 2-转账',
  paid              tinyint         not null default 0      comment '是否已支付: 0-否, 1-是',
  source            tinyint         not null default 1      comment '采购单来源: 1-常规 2-临采 3-综合',
  invoice_ids       VARCHAR(200)    NOT NULL DEFAULT ''     COMMENT '发票id',
  managing_mode     TINYINT         NOT NULL DEFAULT 1      COMMENT '经营模式，1-自营，2-联营',
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
  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除, 2-取消',
  step              tinyint         not null default 0      comment '所处阶段: 1-已入库，2-已上架，3-部分上架',
  payment_type      tinyint         not null default 0      comment '付款方式: 1-现款, 2-转账',
  paid_source       tinyint         not null default 0      comment '款项来源: 1-民生私户123, 2-民生公户234, ....',
  paid              tinyint         not null default 0      comment '是否付款: 0-未付款, 1-已付款, 2-兑账未付',
  real_amount       int             not null default 0      comment '实际支付 (单位:分)',
  source            tinyint         not null default 1      comment '采购单来源: 1-常规 2-临采',
  managing_mode     TINYINT         NOT NULL DEFAULT 1      COMMENT '经营模式，1-自营，2-联营',
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
  managing_mode     TINYINT         NOT NULL DEFAULT 1      COMMENT '经营模式，1-自营，2-联营',

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
  status            tinyint     not null default 0          comment '状态: 0-正常, 1-取消',
  create_suid       int         not null default 0          comment '创建人id',
  stockout_suid     int         not null default 0          comment '出库人id',
  stockin_suid      int         not null default 0          comment '入库人id',
  shelved_suid      int         not null default 0          comment '上架人id',
  out_time          timestamp       not null default 0      comment '调拨出库时间',
  in_time           timestamp       not null default 0      comment '调拨入库时间',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  note              text            not null                comment '备注',
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
  managing_mode     TINYINT         NOT NULL DEFAULT 1      COMMENT '经营模式，1-自营，2-联营',
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
    cuid            int             not null default 0      comment '合作工人的id',
    oid             int             not null default 0      comment '订单id',
    obj_id          int             not null default 0      comment '对象id',
    obj_type        int             not null default 0      comment '对象类型',
    wid             int             not null default 0      comment '仓库id',
    city_id         int             not null default 0      comment '城市id',
    price           int             not null default 0      comment '支付金额（单位：分）',
    amount          int             not null default 0      comment '汇总金额（单位：分）',
    type            tinyint         not null default 0      comment '支付类型：1-运费 2-搬运费 10-奖励 11-罚款',
    user_type       tinyint         not null default 0      comment '工人类型：1-司机 2-搬运工',
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
    cuid            int             not null default 0      comment '合作工人的id',
    oid             int             not null default 0      comment '订单id',
    obj_id          int             not null default 0      comment '对象id',
    obj_type        int             not null default 0      comment '对象类型',
    wid             int             not null default 0      comment '仓库id',
    price           int             not null default 0      comment '支付金额（单位：分）',
    base_price      int             not null default 0      comment '基础费用',
    refer_price     int             not null default 0      comment '推荐费用',
    other_price     varchar(128)    not null default ''     comment '附加费用，多个类型费用集合',
    times           int             not null default 1      comment '运送趟数',
    money_note      text            not null                comment '运费备注',
    type            tinyint         not null default 0      comment '费用类型：1-运费 2-搬运费',
    user_type       tinyint         not null default 0      comment '工人类型：1-司机 2-搬运工',
    suid            int             not null default 0      comment '执行人',
    note            text            not null                comment '备注',
    occupied        tinyint         not null default 0      comment '是否被占用 0-否 1-是',
    status          tinyint         not null default 0      comment '状态：0-正常 1-删除',
    paid            tinyint         not null default 0      comment '是否支付：0-否 1-是',
    alloc_time      timestamp       not null default 0      comment '派单时间',
    confirm_time    timestamp       not null default 0      comment '订单确认时间',
    delivery_time   timestamp       not null default 0      comment '出库时间',
    arrival_time    timestamp       not null default 0      comment '送达客户的时间',
    statement_id    int             not null default 0      comment '结算单ID',
    finish_time     timestamp       not null default 0      comment '回单时间',
    car_model       tinyint         not NULL default 0      comment '车型',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    primary key (id),
    index(obj_id, obj_type),
    index(oid),
    index(ctime),
    index(cuid),
    index(statement_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
 * 库存表
 *-----------------------------------------------*/
CREATE TABLE t_stock (
  sid               int             not null default 0      comment 'sku id',
  wid               int             not null default 0      comment '仓库id',
  place             smallint        not null default 0      comment '货区',
  fring_cost        int             not null default 0      comment '附加成本，单位:分',
  cost              int             not null default 0      comment '成本, 单位:分',
  purchase_price    int             not null default 0      comment '采购价，单位：分',
  num               int             not null default 0      comment '库存数量(包含购买占用数量)',
  occupied          int             not null default 0      comment '购买占用数量',
  damaged_num       int             not null default 0      comment '残损、待盘点数量之和',
  alert_threshold   int             not null default 0      comment '预警数量',
  pre_buy           int             not null default 0      comment '预采购量',
  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除',
  need_stock        tinyint         not null default 0      comment '是否需要做库存 0-否, 1-是',
  wait_num          int             not null default 0      comment '在途数量',
  ave_sale_num      int             not null default 0      comment '平均销量 14天',
  recent_stat_sale  int             not null default 0      comment '最近统计销售，与平均销量关联',
  target_num        int             not null default 0      comment '目标存量',
  deliery_cycle     int             not null default 0      comment '货期，单位：小时',       
  outsourcer_id     int             not null default 0      comment '外包供应商id',

  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (sid, wid),
  index (outsourcer_id, wid, status)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*-----------------------------------------------
 * 库存变化历史
 *-----------------------------------------------*/
CREATE TABLE t_stock_history (
  id                int(11)         NOT NULL AUTO_INCREMENT,
  sid               int             not null default 0      comment 'sku id',
  wid               int             not null default 0      comment '仓库id',
  old_num           int             not null default 0      comment '原数量',
  old_occupied      int             not null default 0      comment '原占用',
  num               int             not null default 0      comment '库存数量(包含购买占用数量)',
  occupied          int             not null default 0      comment '购买占用数量',
  iid               int             not null default 0      comment '进出货单号',
  suid              int             not null default 0      comment '执行人',
  type              tinyint         not null default 0      comment '类型: 0-采购入库, 1-销售出库, 2-盘亏, 3-盘盈',
  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除',
  reason            tinyint         not null default 0      comment '盈亏原因',
  note              varchar(100)    not null default ''     comment '备注',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  index(sid, wid),
  index(ctime),
  index(iid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 先进先出（FIFO）库存成本队列，库存增加记录
 *-----------------------------------------------*/

CREATE TABLE t_fifo_cost_queue (
    id              int             not null auto_increment,
    sid             int             not null default 0      comment 'sku id',
    wid             int             not null default 0      comment '仓库id',
    num             int             not null default 0      comment '批次的数量',
    cost            int             not null default 0      comment '批次的成本: 分',
    in_id           int             not null default 0      comment '批次id: 入库/调拨等',
    in_type         tinyint         not null default 0      comment '批次类型: Conf_Warehouse::$Stock_History_Type',
    status          tinyint         not null default 0      comment '状态',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    index(sid, wid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 先进先出（FIFO）库存成本和入库批次对照表，库存减少记录
 *-----------------------------------------------*/
CREATE TABLE t_fifo_cost_history(
    id              int             not null auto_increment,
    sid             int             not null default 0      comment 'sku id',
    wid             int             not null default 0      comment '仓库id',
    num             int             not null default 0      comment '出库数量',
    cost            int             not null default 0      comment '批次成本',
    in_id           int             not null default 0      comment '入库类型id',
    in_type         tinyint         not null default 0      comment '入库类型',
    out_id          int             not null default 0      comment '出库类型id',
    out_type        tinyint         not null default 0      comment '出库类型',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    index (sid, wid),
    index (in_id, in_type),
    index (out_id, out_type),
    index (ctime)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 先进先出（FIFO）库存成本队列，库存增加记录
 *-----------------------------------------------*/

CREATE TABLE t_fifo_cost_queue_2017 (
    id              int             not null auto_increment,
    sid             int             not null default 0      comment 'sku id',
    wid             int             not null default 0      comment '仓库id',
    num             int             not null default 0      comment '批次的数量',
    cost            int             not null default 0      comment '批次的成本: 分',
    in_id           int             not null default 0      comment '批次id: 入库/调拨等',
    in_type         tinyint         not null default 0      comment '批次类型: Conf_Warehouse::$Stock_History_Type',
    status          tinyint         not null default 0      comment '状态',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    index(sid, wid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 先进先出（FIFO）库存成本和入库批次对照表，库存减少记录
 *-----------------------------------------------*/
CREATE TABLE t_fifo_cost_history_2017 (
    id              int             not null auto_increment,
    sid             int             not null default 0      comment 'sku id',
    wid             int             not null default 0      comment '仓库id',
    num             int             not null default 0      comment '出库数量',
    cost            int             not null default 0      comment '批次成本',
    in_id           int             not null default 0      comment '入库类型id',
    in_type         tinyint         not null default 0      comment '入库类型',
    out_id          int             not null default 0      comment '出库类型id',
    out_type        tinyint         not null default 0      comment '出库类型',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    index (sid, wid),
    index (in_id, in_type),
    index (out_id, out_type),
    index (ctime)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  wid               int             not null default 0     COMMENT '供应商所属仓库',
  type              tinyint(4)      NOT NULL DEFAULT '0'   COMMENT '类型: 1-厂家, 2-一批, 3-二批, 4-其他',
  book_note         text            NOT NULL               COMMENT '订货需求',
  note              text            NOT NULL               COMMENT '备注',
  create_suid       int             not null default 0     comment '添加人',
  status            tinyint(4)      NOT NULL DEFAULT '5'   COMMENT '状态: 0-已审核, 5-待审核, 6-驳回，4-停用',
  account_balance   int             not null default 0     COMMENT '支付账款',
  amount            int             not null default 0     COMMENT '余额',
  bank_info         varchar(128)    not null default ''    COMMENT '银行信息 格式：姓名-银行账号-开户行',
  public_bank       varchar(128)    not null default ''    COMMENT '公户银行 格式：姓名-银行账号-开户行',
  delivery_hours    int             not null default 0     COMMENT '送货周期，单位：小时',
  payment_days      tinyint         not null default 0     COMMENT '账期：天',
  invoice           tinyint(4)      not null default 1     COMMENT '是否提供发票 {1-不提供发票  2-可提供发票}',
  duty              tinyint         not null default 0     COMMENT '普票税点',
  special_duty      tinyint         not null default 0     COMMENT '专票税点',
  freight           int             not null default 0     comment '运费 (单位:分)',
  managing_mode     TINYINT         NOT NULL DEFAULT 1      COMMENT '经营模式，1-自营，2-联营',

  ctime             timestamp       NOT NULL DEFAULT '0000-00-00 00:00:00',
  mtime             timestamp       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (sid)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

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
  role              varchar(32)     not null default 0      comment '账号类型: 1-超级管理员 ... 详见配置 【字段下线】',
  roles             varchar(200)    not null default ''     comment '角色列表',
  department        INT             not null default 0      comment '部门',

  wid               int             not null default 0      comment '管理员所属的库房，!0: 只能查看指定库房 0:查看全部 【待下线】',
  wids              varchar(200)    not null default ''     comment '仓库串，多个城市之间用逗号(,)隔开',
  city_id           INT             not null DEFAULT 0      comment '多城市【待下线】',
  cities            varchar(200)    not null default ''     comment '城市串，多个城市之间用逗号(,)隔开',

  kind              tinyint         not null default 0      comment '类型：1-地推 2-兼职 3-电销 4-网销',
  leader_suid       int             not null default 0      comment '直接领导id',

  verify            varchar(200)    not null default ''     comment '身份验证信息',
  last_login_ip     varchar(50)     not null default ''     comment '最后登录ip',
  regid             varchar(128)    not null default ''     COMMENT '小米推送id',
  ding_id           varchar(20)     not null default ''     comment '钉钉UserId',
  ce_agent_num      varchar(50)     not null default ''     comment '呼叫中心坐席工号',
  ce_agent_pass     varchar(100)    not null default ''     comment '呼叫中心坐席密码',
  ce_agent_phone    varchar(50)     not null default ''     comment '呼叫中心坐席分机号',

  sex               tinyint         not null default 0      comment '性别 0-男 1-女',
  birthday          date            not null default 0      comment '生日',
  mobile            char(11)        not null default ''     comment '登陆手机号(必填)',
  wx_openid         varchar(24)     not null default ''     comment '微信openid',
  qq                int             not null default 0      comment 'QQ号',
  email             varchar(128)    not null default ''     comment 'email',
  password          varchar(32)     not null default ''     comment '密码',
  salt              smallint        not null default 0      comment '密码salt',
  status            tinyint         not null default 0      comment '状态: 0-正常 1-删除 2-封禁',
  is_simple_pwd     tinyint         not null default 0      comment '是否简单密码，0否1是',
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
  role              varchar(100)   NOT NULL DEFAULT '' COMMENT '权限名称',
  rkey              varchar(100)   not null default '' comment '唯一标识符',
  department        INT            not null default 0 comment '部门',
  permission        text           comment '角色权限列表，以“,”分隔',
  suid              INT            not null default 0 comment '',
  ctime             timestamp      NOT NULL DEFAULT '0000-00-00 00:00:00',
  mtime             timestamp      NOT NULL DEFAULT '0000-00-00 00:00:00',
  status            int            not null default 0,
  rel_role          varchar(255)   not null default '' comment '关联的角色id,多个用逗号隔开',
  PRIMARY KEY (id),
  UNIQUE KEY (rkey)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*-----------------------------------------------
 * 模糊搜索用表-其实是sku表的一个映射
 * 主要是因为fulltext只能建立在myisam表上（5.6版本之前），所以对应映射了一下
 * 另外需要把ft_min_word_len改为1
 * 修改方法：在my.cnf中添加ft_min_word_len=1然后重启mysql
 *-----------------------------------------------*/
 CREATE TABLE t_sku_fulltext (
  sid               int                      not null,
  title             varchar(500)             not null default ''     comment '标题',
  alias             varchar(500)             not null default ''     comment '别名',
  ctime             timestamp                not null default 0,
  mtime             timestamp                not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (sid),
  FULLTEXT(title, alias)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE t_statistics_order (
  id                INT           not null auto_increment,
  odate             INT           not null default 0 comment '日期，本日零点',
  total             INT           not null default 0 comment '订单总数',
  price             INT           not null default 0 comment '订单总金额（单位：分）',
  PRIMARY  KEY  (id),
  KEY (odate)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 客户(公司)回访表
 *-----------------------------------------------*/
CREATE TABLE t_customer_tracking (
  tid               INT             not null auto_increment,
  cid               int             not null default 0      comment '客户id',
  edit_suid         int             not null default 0      comment '回访记录编辑人(t_staff_user)',
  type              tinyint         not null default 1      comment '类型，1-销售添加回访记录；2-财务催账添加的记录; 3-客服售后回访，4-注册/录入 5-转移 6-下单 7-充值 8-退货',
  content           varchar(4096)   not null default ''     comment '内容',
  from_status       tinyint         not null default 0      comment '变更前状态',
  to_status         tinyint         not null default 0      comment '变更后状态',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (tid),
  key (cid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 微信账号-好材账号关联表
 *-----------------------------------------------*/
CREATE TABLE t_weixin_customer (
  id                INT                 not null auto_increment,
  cid               INT                 not null default 0      comment '用户id',
  openid            varchar(500)        not null default 0      comment '微信openid',
  ctime             timestamp           not null default current_timestamp,
  PRIMARY KEY (id),
  KEY (cid),
  KEY (openid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 微信账号-好材账号关联表
 *-----------------------------------------------*/
CREATE TABLE t_weixin_info (
  openid            varchar(100)    not NULL                comment '微信openid',
  uid               INT             not NULL                comment '用户id',
  nickname          VARCHAR(500)    not NULL                comment '微信昵称',
  sex               tinyint(1)      not NULL                comment '用户性别，0未知，1男，2女',
  city              VARCHAR(200)    not NULL                comment '用户所在城市',
  country           VARCHAR(200)    not NULL                comment '用户所在国家',
  province	        VARCHAR(200)    not NULL                comment '用户所在省份',
  language	        VARCHAR(100)    not null                comment '户的语言，简体中文为zh_CN',
  headimgurl	      VARCHAR(500)    not NULL                comment '用户头像',
  subscribe_time    INT             not NULL 	              comment '用户关注时间，为时间戳',
  unionid           INT             not NULL                comment '只有在用户将公众号绑定到微信开放平台帐号后，才会出现该字段',
  remark	          varchar(500)    not NULL                comment '公众号运营者对粉丝的备注',
  groupid	          INT             not NULL                comment '用户所在的分组ID',
  created_at        TIMESTAMP       not null default 0,
  last_login_at     TIMESTAMP       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  login_times       INT             not null,
  PRIMARY KEY (openid, uid)
) ENGINE=INNoDB DEFAULT CHARSET=utf8;

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
CREATE TABLE t_driver (
  did               INT             not null auto_increment,
  name              VARCHAR(100)    not null                comment '司机名字',
  mobile            VARCHAR(50)     not NULL                comment '司机电话',
  password          varchar(32)     not null default ''     comment '密码',
  salt              smallint        not null default 0      comment '密码salt',
  car_model         INT             not NULL default 0      comment '车型，见t_car_model表',
  source            INT             not null default 0      comment '来源，见t_dirver_source表',
  wid               INT             not null default 0      comment '仓库',
  order_num         INT             not null default 0      comment '订单数',
  status            TINYINT         not null default 0      comment '是否被删除 0-否 1-是 5-未审核',
  car_code          varchar(20)     not null default '-1'   comment '车牌号',
  can_carry         tinyint(1)      not null default 0      comment '是否愿意给搬东西',
  can_trash         tinyint(1)      not null default 0      comment '是否愿意拉垃圾',
  can_escort        tinyint(1)      not null default 0      comment '是否愿意押车',
  trans_scope       varchar(64)     not null default ''     comment '运输范围 逗号分隔',
  score             int             not null default 60     comment '评分',
  note              varchar(500)    not null default ''     comment '备注',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  city_id           INT             not null default 0      comment '多城市',
  real_name         varchar(30)     not null default ''     comment '收款人',
  card_num          varchar(25)     not null default ''     comment '银行卡号',
  bank_info         varchar(128)    not null default ''     comment '开户行',
  regid             varchar(128)    not null default ''     COMMENT '小米推送id',
  refuse_num        int             not null default 0      comment '连续拒单次数',
  car_province      char(3)         not null default ''     comment '车牌省份',
  car_number        char(6)         not null default ''     comment '车牌号',
  PRIMARY KEY (did),
  unique(mobile),
  index (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 搬运工表
 *-----------------------------------------------*/
CREATE TABLE t_carrier (
  cid               INT NOT NULL auto_increment,
  name              VARCHAR(100)    NOT NULL                comment '搬运工名字',
  mobile            VARCHAR(50)     not NULL                comment '电话',
  password          varchar(32)     not null default ''     comment '密码',
  salt              smallint        not null default 0      comment '密码salt',
  wid               INT             not null default 0      comment '仓库',
  order_num         INT             not null default 0      comment '订单数',
  status            TINYINT         not null default 0      comment '是否被删除 0-否 1-是 5-未审核',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  city_id           INT             not null default 0      comment '多城市',
  real_name         varchar(100)    not null default ''     comment '收款人',
  card_num          varchar(25)     not null default ''     comment '银行卡号',
  bank_info         varchar(128)    not null default ''     comment '开户行',
  PRIMARY KEY (cid),
  unique(mobile),
  index (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE t_admin_log (
  lid           INT NOT NULL auto_increment,
  admin_id      INT NOT NULL default 0          comment '管理员id',
  action_type   SMALLINT NOT NULL default 0       comment '操作类型',
  params        VARCHAR(500) NOT NULL default ''  comment '操作参数',
  mtime         timestamp     not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (lid),
  index (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE t_admin_log_2017 (
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

CREATE TABLE t_business(
    bid             int not         null auto_increment,
    bname           varchar(127)    not null default ''             comment '企业名称',
    cname           varchar(64)     not null default ''             comment '负责人名称',
    mobile          char(11)        not null default ''             comment '手机号，登录凭证',
    password        varchar(32)     not null default ''             comment '密码',
    salt            smallint        not null default 0              comment '密码salt',
    record_suid     int             not null default 0              comment '开发销售',
    sales_suid      int             not null default 0              comment '当前维护的销售',
    city            SMALLINT        not null default 0              comment '城市',
    district        int             not null default 0              comment '地区',
    area            int             not null default 0              comment '商圈',
    address         varchar(256)    not null default ''             comment '公司地址',
    products        text            not null                        comment '经营范围',
    is_pay          tinyint         not null default 0              comment '是否可以支付，转移余额等',
    status          tinyint         not null default 0              comment '0-有效 1-删除',
    note            text            not null                        comment '备注',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (bid),
    index(mobile),
    index(bname)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=2001;

/*-----------------------------------------------
 * 临时采购表 - 未采表
 *-----------------------------------------------*/

CREATE TABLE t_temporary_purchase (
  sid           int                      not null default 0      comment 'sku_id',
  title         varchar(224)             not null default ''     comment '标题',
  cate1         tinyint                  not null default 0      comment '一级分类',
  package       varchar(64)              not null default ''     comment '包装,比如 100 米/卷',
  unit          varchar(32)              not null default ''     comment '单位单元,比如: 卷,袋,根',
  cost          int                      not null default 0      comment '商品成本（单位：分）',
  num           int                      not null default 0      comment '需要采购的数量',
  wid           int                      not null default 0      comment '仓库',
  status        tinyint                  not null default 0      comment '采购商品状态 0-正常，1-删除',
  ctime         timestamp                not null default 0,
  mtime         timestamp                not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  unique(wid, sid),
  index(cate1),
  index(ctime)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 临时采购表 - 已采表
 *-----------------------------------------------*/

CREATE TABLE t_temporary_had_purchased (
  sid               int                 not null default 0          comment 'sku_id',
  cost              int                 not null default 0          comment '商品成本（单位：分）',
  temp_num          int                 not null default 0          comment '临采数量',
  in_order_num      int                 not null default 0          comment '下采购单对应的数量',
  wid               int                 not null default 0          comment '仓库',
  status            tinyint             not null default 0          comment '采购商品状态 0-正常，1-删除',
  buy_date          date                not null default 0          comment '采购日期',
  ctime             timestamp           not null default 0,
  mtime             timestamp           not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  unique(buy_date, wid, sid),
  index(ctime)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 用户限制表，用于记录用户时候已做过某些操作，比如是否领过了20160113的优惠券活动
 *-----------------------------------------------*/
CREATE TABLE t_customer_limit (
  lid               int                 not null auto_increment,
  cid               int                 not null default 0          comment 'customer id',
  lkey              int                 not null default 0          comment '操作key',
  val               int                 not null default 0          comment '限制值',
  ext               text                not null                    comment '扩展信息',
  ctime             timestamp           not null default 0,
  mtime             timestamp           not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY key (lid),
  UNIQUE (cid, lkey)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 手机号收集活动（薅羊毛）
 *-----------------------------------------------*/
CREATE TABLE t_collect_mobile (
  id                int                 not null auto_increment,
  cid               int                 not null default 0          comment 'customer id',
  mobile            varchar(20)         not null default 0          comment '操作key',
  amount            int                 not null default 0          comment '限制值',
  ctime             timestamp           not null default 0,
  mtime             timestamp           not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY key (id),
  UNIQUE (cid, mobile)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 订单数据统计
 *-----------------------------------------------*/
CREATE TABLE t_order_statistics (
  id                int       not null auto_increment,
  total_amount      int       not null default 0    comment '总销售额',
  refund            int       not null default 0    comment '退款额',
  product_cost      INT       not null default 0    comment '材料成本',
  carry_subsidy     INT       not null default 0    comment '搬运费补贴',
  freight_subsidy   INT       not null default 0    comment '运费补贴',
  privilege_subsidy INT       not null default 0    comment '优惠补贴',
  sdate             DATE      not null default 0    comment '日期',
  PRIMARY key (id),
  UNIQUE (sdate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 订单优惠信息
 *-----------------------------------------------*/
CREATE TABLE t_order_privilege (
  id                int           not null auto_increment,
  cid               int           not null default 0    comment 'cid',
  oid               int           not null default 0    comment '订单id',
  activity_id       int           not null default 0    comment '促销活动id',
  type              tinyint       not null default 0    comment '优惠类型',
  amount            INT           not null default 0    comment '优惠金额（单位：分）',
  old_amount        INT           not null default 0    comment '上一次优惠金额（单位：分）',
  info              varchar(500)  not null default '' comment '相关信息',
  ctime             timestamp     not null default 0,
  mtime             timestamp     not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY key (id),
  unique key (oid, type, activity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 订单操作日志
 *-----------------------------------------------*/
CREATE TABLE t_order_action_log (
  lid         INT NOT NULL auto_increment,
  oid         INT not null default 0 comment '订单id',
  admin_id    INT NOT NULL default 0            comment '管理员id',
  action_type SMALLINT NOT NULL default 0       comment '操作类型',
  params      VARCHAR(500) NOT NULL default ''  comment '操作参数',
  ctime timestamp not null default 0,
  mtime   timestamp     not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (lid),
  index (admin_id),
  index (oid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*-----------------------------------------------
 * 管理后台的任务系统
 *-----------------------------------------------*/
CREATE TABLE t_admin_task (
    tid             int             not null auto_increment,
    create_suid     int             not null default 0              comment '发起人的id',
    exec_suid       int             not null default 0              comment '执行人的id',
    exec_role       tinyint         not null default 0              comment '执行人的身份',
    title           varchar(512)    not null default ''             comment '任务标题',
    content         text            not null                        comment '任务内容',
    objtype         tinyint         not null default 0              comment '操作对象类型：1-订单 2-客户 ...',
    objid           int             not null default 0              comment '操作对象id， 与objtype对应',
    short_desc      int             not null default 0              comment '简述，系统枚举类型',
    pic_ids         text            not null                        comment '图片id列表, 以英文逗号分隔',
    exec_status     tinyint         not null default 0              comment '任务状态：1-创建 2-待处理 3-完成 4-关闭 10-删除',
    level           tinyint         not null default 0              comment '任务级别：1-正常 2-紧急',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (tid),
    index(objid),
    index(create_suid),
    index(exec_suid),
    index(exec_status),
    index(level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1000;

/*-----------------------------------------------
 * 管理后台的任务系统 - 操作历史记录
 *-----------------------------------------------*/
CREATE TABLE t_admin_task_history (
    id              int         not null auto_increment,
    tid             int         not null default 0              comment '任务id',
    suid            int         not null default 0              comment '操作者suid',
    exec_suid       int         not null default 0              comment '执行人suid',
    old_exec_status tinyint     not null default 0              comment '原执行状态',
    new_exec_status tinyint     not null default 0              comment '新执行状态',
    note            text        not null                        comment '备注',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    index(tid)

) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 auto_increment=1000;

/*-----------------------------------------------
 * 管理后台的任务系统 - 操作历史记录
 *-----------------------------------------------*/
CREATE TABLE t_cumulative_log (
    id              int         not null auto_increment,
    cid             int         not null default 0              comment '用户id',
    type            int         not null default 0              comment '返现类型',
    amount          int         not null default 0              comment '返现金额',
    ctime           timestamp   not null default 0,
    mtime           timestamp   not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    index(cid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 百度推送
 *-----------------------------------------------*/
CREATE TABLE t_user_channel (
    id              int           not null auto_increment,
    uid             int           not null default 0    comment '用户uid',
    device_type     int           not null default 0    comment '设备类型',
    device_token    varchar(255)  not null default ''   comment '设备标记',
    channel_id      varchar(255)  not null default ''   comment '推送凭据',
    user_id         varchar(255)  not null default ''   comment '百度user_id',
    ctime           timestamp     not null default 0,
    mtime           timestamp     not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    index(uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*-----------------------------------------------
 * 2016-05抽奖记录
 *-----------------------------------------------*/
CREATE TABLE t_lottery_record (
    id              int             not null auto_increment,
    cid             int             not null default 0    comment '用户id',
    uid             INT             not null default 0    comment 'uid',
    prize           INT             not null default 2    comment '奖项',
    has_send        tinyint         not null default 0    comment '是否已发放',
    oid             INT             not null default 0    comment '配送订单',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    index(cid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 用户位置（坐标是原始坐标，如果要使用百度坐标，需要转换）
 *-----------------------------------------------*/
CREATE TABLE t_user_weixin_location (
    id              int             not null auto_increment,
    cid             int             not null default 0    comment '用户id',
    open_id         varchar(255)    not null default ''   comment 'openid',
    lng             DECIMAL(11,8)   not null              comment '经度',
    lat             DECIMAL(11,8)   not null              comment '纬度',
    prec            DECIMAL(11,8)   not null              comment '精度',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    index(cid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 第三方SKU与好材SKU映射表
 *-----------------------------------------------*/
CREATE TABLE t_third_party_sku_mapping (
    id              int             not null auto_increment,
    mid             int             not null default 0    comment '第三方id',
    msid            int             not null default 0    comment '第三方sid',
    sid             int             not null default 0    comment '好材sid',
    mprice          int             not null default 0    comment '第三方售价',
    price           int             not null default 0    comment '好材给第三方的售价',
    status          tinyint         not null default 0    comment '状态',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    index(msid)
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
    freight         int             not null default 0    comment '建议运费（分）',
    note            text            not NULL              comment '备注',
    status          tinyint         not null default 0    comment '状态',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    index(cmid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 满减活动
 *-----------------------------------------------*/
CREATE TABLE t_manjian_activity (
    id              int             not null auto_increment,
    stime           timestamp       not null default 0    comment '开始时间',
    etime           timestamp       not null default 0    comment '结束时间',
    conf            text            not null              comment '满减配置',
    status          int             not null default 0    comment '状态',
    is_sand         INT             not null default 0    comment '是否包含砂石类',
    is_vip          INT             not null default 0    comment '有余额的用户能否参加',
    suid            INT             not null default 0    comment '录入人',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 邀请活动-邀请账号表
 *-----------------------------------------------*/
CREATE TABLE t_invite_customer (
    id              int             not null auto_increment,
    cid             int             not null default 0    comment '邀请人id',
    fcid            int             not null default 0    comment '被邀请人id',
    mobile          varchar(20)     not null default ''   comment '被邀请人手机号',
    name            varchar(100)    not null default ''   comment '被邀请人昵称',
    has_buy         tinyint         not null default 0    comment '是否已购买',
    ctime           timestamp       not null default 0    comment '被邀请人注册时间',
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 邀请活动-邀请奖励表
 *-----------------------------------------------*/
CREATE TABLE t_invite_reward (
    id              int             not null auto_increment,
    cid             int             not null default 0    comment '邀请人id',
    fcid            int             not null default 0    comment '被邀请人id',
    mobile          varchar(20)     not null default ''   comment '被邀请人手机号',
    oid             INT             not null default 0    comment '相关订单号',
    reward          int             not null default 0    comment '奖励金额',
    delivery_date   DATE            not null default 0    comment '发货时间',
    reward_date     DATE            not null default 0    comment '奖励预计发放时间',
    is_reward       tinyint         not null default 0    comment '是否已发放',
    ctime           timestamp       not null default 0    comment '被邀请人注册时间',
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE t_after_sale (
    id int not null auto_increment,
    type tinyint not null default 0 comment '售后单类型',
    typeid TINYINT NOT NULL DEFAULT 0 COMMENT '类型子级',
    objid varchar(100) not null default '' comment '相关订单oid',
    rid varchar(100) not null default '' comment '退款单，换货单，补货单id',
    contact_name varchar(50) not null default '' comment '反馈人',
    contact_mobile varchar(50) not null default '' comment '反馈电话',
    create_suid int not null default 0 comment '创建者',
    exec_suid int not null default 0 comment '执行人',
    duty_department int not null default 0 comment '责任部门',
    exec_status tinyint not null default 0 comment '执行步骤',
    status tinyint not null default 0 comment '状态',
    join_suids varchar(100) not null default '' comment '参加的管理员',
    ctime TIMESTAMP not null default CURRENT_TIMESTAMP,
    mtime TIMESTAMP not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    content varchar(200) comment '内容',
    fb_type tinyint not null default 0 comment '反馈人类型',
    fb_uid int not null default 0 comment '反馈人id',
    contact_way varchar(100) not null default '' comment '其他联系方式',
    pic_ids text NOT null DEFAULT '' COMMENT '图片id列表, 以英文逗号分隔',
    PRIMARY KEY (id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE t_after_sale_log (
    id int not null auto_increment,
    sid int not null default 0 comment '售后问题id',
    exec_department int not null default 0 comment '受理部门',
    exec_suid int not null default 0 comment '受理人',
    action tinyint not null default 0 comment '操作',
    content varchar(200) not null default '' comment '处理方案',
    after_step tinyint not null default 0 comment '处理后状态',
    ctime TIMESTAMP not null default 0,
    mtime TIMESTAMP not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    key (sid)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 订单操作日志
 *-----------------------------------------------*/
CREATE TABLE t_logistics_action_log (
  id          INT NOT NULL auto_increment,
  oid         INT not null default 0            comment '订单id',
  line_id     INT default 0                     comment '排线id',
  cuid        INT not null default 0            comment '司机/搬运工id',
  type        tinyint not null default 1        comment '1司机2搬运工',
  admin_id    INT NOT NULL default 0            comment '管理员id',
  action_type SMALLINT NOT NULL default 0       comment '操作类型',
  params      VARCHAR(500) NOT NULL default ''  comment '操作参数',
  ctime timestamp not null default 0,
  mtime   timestamp     not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  index (admin_id),
  index (oid),
  index (cuid),
  index (line_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 活动图片表
 *-----------------------------------------------*/
CREATE TABLE t_activity_picture (
  id                int(11)      NOT NULL AUTO_INCREMENT,
  name              varchar(64)  NOT NULL DEFAULT ''                                             COMMENT '活动名称',
  url               varchar(255) NOT NULL DEFAULT ''                                             COMMENT '图片url',
  platform          char(32)     NOT NULL DEFAULT '0'                                            COMMENT '平台: 1-微信商城, 2-App(多个平台之间用逗号隔开)',
  display_order     tinyint(4)   NOT NULL DEFAULT '0'                                            COMMENT '展示顺序',
  start_time        timestamp    NOT NULL DEFAULT '0000-00-00 00:00:00'                          COMMENT '开始时间',
  end_time          timestamp    NOT NULL DEFAULT '0000-00-00 00:00:00'                          COMMENT '结束时间',
  pic_tag           varchar(255) NOT NULL DEFAULT ''                                             COMMENT '图片url',
  type              tinyint(4)   NOT NULL DEFAULT '0'                                            COMMENT '类型：1-banner',
  suid              int(11)      NOT NULL DEFAULT '0'                                            COMMENT '最后修改用户id',
  status            tinyint(4)   NOT NULL DEFAULT '0'                                            COMMENT '图片状态：0-上线，4-下线',
  ctime             timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP                              COMMENT '添加图片时间',
  mtime             timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP  COMMENT '最后修改图片时间',
  activity_type     tinyint      NOT NULL DEFAULT '1'                                            COMMENT '活动类型:1-文章类,2-落地页类',
  commodity_sid     varchar(100) NOT NULL DEFAULT ''                                             COMMENT '商品的sid',
  city_id           VARCHAR(255) NOT NULL DEFAULT ''                                             COMMENT '城市id(多个平台之间用逗号隔开)',
  PRIMARY KEY (id),
  index (start_time),
  index (end_time)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*-----------------------------------------------
 * 司机账号-微信账号
 *-----------------------------------------------*/
CREATE TABLE t_weixin_coopworker (
  id                INT                 not null auto_increment,
  cuid              INT                 not null default 0      comment '司机/搬运工id',
  type              INT                 not null default 0      comment '1司机2搬运工',
  openid            varchar(500)        not null default 0      comment '微信openid',
  ctime             timestamp           not null default '0000-00-00 00:00:00',
  mtime             timestamp           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY (cuid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE t_user_regid (
  id           INT           NOT NULL auto_increment,
  uid          INT           NOT NULL DEFAULT 0      comment '用户id',
  device_id    varchar(100)  not null default ''      comment '设备id',
  regid        varchar(100)  not null default ''     comment '小米推送regid',
  ctime        TIMESTAMP     not null default '0000-00-00 00:00:00',
  mtime        timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  key (uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE t_hot_search (
  id           INT          NOT NULL auto_increment,
  keyword      VARCHAR(50)  NOT NULL DEFAULT 0      comment '热搜词',
  sortby       int          not null default 0      comment '排序',
  city_id      varchar(100) not null default ''     comment '可见城市',
  status       int          not null default 0,
  ctime        TIMESTAMP    not null default CURRENT_TIMESTAMP,
  mtime        timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 限时抢购商品表
 *-----------------------------------------------*/

CREATE TABLE t_flash_sale (
  id            INT           not null auto_increment,
  pid           INT           not null default 0      comment '商品id',
  fid           INT           not null default 0      comment '限时抢购活动id',
  sort          INT           not null default 0     comment '排序',
  cover         varchar(255)  not null DEFAULT ''     comment '图片url',
  platform      INT           not null DEFAULT 0      comment '活动平台，1-微信商城，2-app，3-微信商城和app',
  total_num     INT           not null default 0      comment '库存',
  sale_num      INT           not null default 0      comment '参加活动商品数量',
  sale_price    varchar(5)     not null default ''      comment '活动售价，单位是分',
  limit_count   INT           not null default 0      comment '每个用户限购数量',
  online        INT           not null default 0      comment '上下线状态，0-在线，1-不在线',
  start_time    timestamp     not null default '0000-00-00 00:00:00' comment '开始时间',
  end_time      timestamp     not null default '0000-00-00 00:00:00' comment '结束时间',
  ctime         timestamp     not null default '0000-00-00 00:00:00' ,
  mtime         timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  key (fid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 限时抢购活动表
 *-----------------------------------------------*/

CREATE TABLE t_flash_sale_activity (
  fid           INT           not null auto_increment,
  type          INT           not null default 0      comment '活动类型，1-限时抢购',
  city          varchar(100)  not null default ''     comment '活动城市',
  name          varchar(100)  not null default ''     comment '活动名称',
  rule          varchar(255)  not null default ''     comment '活动规则',
  platform      INT           not null DEFAULT 0      comment '活动平台，1-微信商城，2-app，3-微信商城和app',
  online        INT           not null default 0      comment '上下线状态，0-在线，1-在线',
  start_time    timestamp     not null default '0000-00-00 00:00:00' comment '开始时间',
  end_time      timestamp     not null default '0000-00-00 00:00:00' comment '结束时间',
  ctime         timestamp     not null default '0000-00-00 00:00:00' ,
  mtime         timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  status        tinyint       not null default 0,
  PRIMARY KEY (fid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 楼层活动表
 *-----------------------------------------------*/

CREATE TABLE t_floor_activity (
  fid           INT           not null auto_increment,
  city          varchar(100)  not null default ''    comment '城市',
  type          INT           not null default 0     comment '楼层类型',
  sort          INT           not null default 0    comment '排序',
  online        INT           not null default 0     comment '上下线状态，0-在线，1-不在线',
  ctime         timestamp     not null default '0000-00-00 00:00:00' ,
  mtime         timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  status        tinyint       not null default 0,
  PRIMARY KEY (fid),
  KEY (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 楼层图片表
 *-----------------------------------------------*/

CREATE TABLE t_floor_sale (
  sid           INT           not null auto_increment,
  fid           INT           not null default 0     comment '楼层id',
  type          INT           not null default 0     comment '图片类型，大小图',
  position      INT           not null default 0     comment '落地页类型，2-应用内商品，1-外部活动专题页',
  pid           INT           not null default 0     comment '商品id',
  sort          INT           not null default 0     comment '排序',
  mark          INT           not null default 0     comment '商品标识',
  url           VARCHAR(255)  not null default ''    comment '链接地址',
  detail        VARCHAR(255)  not null default ''    comment '描述',
  pic_url       varchar(255)  not null default ''    comment '商品图片',
  name          varchar(100)  not null default ''    comment '商品名称',
  sale_num      INT           not null default 0     comment '参加活动商品数量',
  online        INT           not null default 0     comment '上下线状态，0-在线，1-不在线',
  limit_count   INT           not null default 0     comment '每个用户限购数量',
  sale_price    varchar(5)    not null default ''    comment '活动售价，单位是分',
  activity_type tinyint       not null DEFAULT '1'   comment '活动类型:1-文章类,2-落地页类',
  commodity_sid varchar(100)  not null DEFAULT ''    comment '商品的sid',
  start_time    timestamp     not null default '0000-00-00 00:00:00' comment '开始时间',
  end_time      timestamp     not null default '0000-00-00 00:00:00' comment '结束时间',
  ctime         timestamp     not null default '0000-00-00 00:00:00' ,
  mtime         timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (sid),
  KEY (fid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 文章表
 *-----------------------------------------------*/

CREATE TABLE t_article (
  aid           INT           not null auto_increment,
  pic_url       varchar(255)  not null default ''    comment '图片链接',
  title         varchar(255)  not null default ''    comment '文章标题',
  content       longtext      not null               comment '文章内容',
  article_type  TINYINT       not null default 0     comment '文章类型',
  city_ids      varchar(100)  not NULL default ''     comment '城市id: 1-全部城市',
  ctime         timestamp     not null default '0000-00-00 00:00:00',
  mtime         timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (aid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * app用户问题反馈
 *-----------------------------------------------*/

CREATE TABLE t_user_fb (
  fid           INT           not null auto_increment,
  uid           INT           not null DEFAULT 0,
  cid           INT           not null DEFAULT 0,
  ensure_id         INT       not null DEFAULT 0  comment '解决人id',
  platform          INT       not null DEFAULT 0  comment '录入平台id',
  sale_id           INT       not null DEFAULT 0  comment '销售人员id',
  contact_name  varchar(48)   not null default '' comment '联系人',
  contact_phone varchar(48)   not null default '' comment '联系电话',
  content       text          not null comment '问题内容',
  solve         text          not null comment '解决方案',
  ensure_status TINYINT       not null default 0 comment '确认: 0-未确认，2-已确认',
  ctime         timestamp     not null default '0000-00-00 00:00:00' ,
  mtime         timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (fid),
  KEY (uid),
  KEY (cid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 用户消息推送
 *-----------------------------------------------*/

CREATE TABLE t_user_msg (
  mid           INT           not null auto_increment,
  uid           INT           not null DEFAULT 0,
  cid           INT           not null DEFAULT 0,
  m_type        INT           not null DEFAULT 0 COMMENT '消息类型，1-发货，2-订单完成，3-奖励到账通知，4-退款到账通知，5-系统通知（注册），6-系统通知（故障）',
  content       VARCHAR(500)  not null DEFAULT '' comment '问题内容',
  ctime         timestamp     not null default '0000-00-00 00:00:00' ,
  mtime         timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (mid),
  KEY (uid),
  KEY (cid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 快速下单订单表
 *-----------------------------------------------*/

CREATE TABLE t_quick_order (
  oid               INT           not null auto_increment,
  uid               INT           not null DEFAULT 0,
  cid               INT           not null DEFAULT 0,
  ensure_id         INT           not null DEFAULT 0  comment '确认人id',
  platform          INT           not null DEFAULT 0  comment '录入平台id',
  sale_id           INT           not null DEFAULT 0  comment '销售人员id',
  contact_name      varchar(48)   not null default '' comment '联系人',
  contact_phone     varchar(48)   not null default '' comment '联系电话',
  pic_url           varchar(200)  not null default '' comment '图片url',
  ensure_status     TINYINT       not null default 0 comment '确认: 0-未确认，2-已确认',
  status            TINYINT       not null default 0  comment '状态: 0-正常, 1-删除, 3-取消',
  ctime             timestamp     not null default '0000-00-00 00:00:00' ,
  mtime             timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (oid),
  KEY (uid),
  KEY (cid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 用户KVDB
 *-----------------------------------------------*/
CREATE TABLE t_user_kvstore (
  id                INT             not null auto_increment,
  uid               INT             not null default 0                        comment 'uid',
  name              VARCHAR(100)    not null default ''                       comment 'name',
  value             TIMESTAMP       not NULL default '0000-00-00 00:00:00'    comment '上一次请求时间',
  ctime             timestamp       not null default '0000-00-00 00:00:00',
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  key (name),
  key (uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 用户常买表
 *-----------------------------------------------*/
CREATE TABLE t_user_often_buy (
  id                INT             not null auto_increment,
  cid               INT             not null default 0,
  uid               INT             not null default 0,
  cate1             INT             not null default 0,
  sid               INT             not null default 0,
  pid               INT             not null default 0,
  city_id           INT             not null default 0,
  total             INT             not null default 0,
  ctime             timestamp       not null default '0000-00-00 00:00:00',
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  key (uid, pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 应收统计表 (客户欠款表）
 *-----------------------------------------------*/
CREATE TABLE t_statistics_receivables (
  sales_suid          int             not null default 0                 comment '销售专员 (t_staff_user)',
  record_date         date            not null default '0000-00-00'      comment '统计日期',
  record_month        char(7)         not null default '0000-00'         comment '统计月份',
  total_amount        int             not null default 0                 comment '总金额(单位分)',
  account_amount      int             not null default 0                 comment '有账期金额(单位分)',
  no_amount           int             not null default 0                 comment '无账期金额(单位分)',
  no_amount_receipt   int             not null default 0                 comment '无账期已回单金额(单位分)',
  no_amount_noreceipt int             not null default 0                 comment '无账期未回单金额(单位分)',
  ctime               timestamp       not null default '0000-00-00 00:00:00',
  mtime               timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (sales_suid,record_date),
  index (record_date),
  index (record_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 快捷入口表
 *-----------------------------------------------*/
CREATE TABLE t_shortcut (
  sid               INT             not null auto_increment,
  city              VARCHAR(100)    not null default ''                       comment '城市',
  name              VARCHAR(100)    not null default ''                       comment 'name',
  url               VARCHAR(100)    not null default ''                       comment '跳转链接',
  imgurl            VARCHAR(100)    not null default ''                       comment '图片地址',
  online            TINYINT         not null default 0                        comment '在线状态，0-在线，1-不在线',
  sort              INT             not null default 0                        comment '排序，顺序，越小越在前',
  start_time        TIMESTAMP       not NULL default '0000-00-00 00:00:00'    comment '开始时间',
  end_time          timestamp       not null default '0000-00-00 00:00:00'    ,
  ctime             timestamp       not null default '0000-00-00 00:00:00'    ,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (sid),
  key (name),
  key (online)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 *热门搜索词表
 *-----------------------------------------------*/
CREATE TABLE t_hotword (
  hid               INT             not null auto_increment,
  city              VARCHAR(100)    not null default ''                       comment '城市',
  value             VARCHAR(500)    not null default ''                       comment '热搜词',
  type              VARCHAR(500)    not null default ''                       comment '类型，1-',
  online            TINYINT         not null default 0                        comment '在线状态，0-在线，1-不在线',
  sort              INT             not null default 0                        comment '排序，顺序，越小越在前',
  start_time        TIMESTAMP       not NULL default '0000-00-00 00:00:00'    comment '开始时间',
  end_time          timestamp       not null default '0000-00-00 00:00:00'    ,
  ctime             timestamp       not null default '0000-00-00 00:00:00'    ,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (hid),
  key (online)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 *服务特点表
 *-----------------------------------------------*/
CREATE TABLE t_service_feature (
  sid               INT             not null auto_increment,
  city              VARCHAR(100)    not null default ''                       comment '城市',
  feature1          VARCHAR(50)     not null default ''                       comment '特点1',
  feature2          VARCHAR(50)     not null default ''                       comment '特点2',
  feature3          VARCHAR(50)     not null default ''                       comment '特点3',
  online            TINYINT         not null default 0                        comment '在线状态，0-在线，1-不在线',
  sort              INT             not null default 0                        comment '排序，顺序，越小越在前',
  start_time        TIMESTAMP       not NULL default '0000-00-00 00:00:00'    comment '开始时间',
  end_time          timestamp       not null default '0000-00-00 00:00:00'    ,
  ctime             timestamp       not null default '0000-00-00 00:00:00'    ,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (sid),
  key (online)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 满减活动表
 *-----------------------------------------------*/
CREATE TABLE t_promotion_manjian_activity (
  id                  int             not null auto_increment,
  title               varchar(200)    not null default ''                comment '活动名称',
  activity_type       TINYINT         NOT NULL DEFAULT 1                 COMMENT '活动类型(1:满减,2:送券,3:满减＋送券,4:折扣)',
  type_ids            varchar(100)    not null default ''                comment '活动同享类型',
  acitity_bear        TINYINT         not null default 1                 comment '活动费用承担方',
  m_type              tinyint         not null default 0                 comment '二级活动类型',  
  stime               timestamp       not null default 0                 comment '开始时间',
  etime               timestamp       not null default 0                 comment '结束时间',
  conf                text            not null default ''                comment '满减配置',
  city_ids            text            not null default ''                comment '活动城市',
  user_type           tinyint         not null default 0                 comment '参与用户(0:全部,1:部分)',
  user_type_extand    varchar(200)    not null default ''                comment '参与用户类型明细',
  user_count          int             not null default 0                 comment '参与次数(0:不限)',
  user_whitelist      text            not null default ''                comment '用户白名单',
  user_blacklist      text            not null default ''                comment '用户黑名单',
  goods_is_sand       tinyint         not null default 0                 comment '是否包含砂石类',
  goods_is_meichao    tinyint         not null default 0                 comment '是否包含美巢商品',
  goods_is_special    tinyint         not null default 0                 comment '是否包含特价商品',
  goods_is_hot        tinyint         not null default 0                 comment '是否包含热卖商品',
  goods_type          tinyint         not null default 0                 comment '参与商品(0:全部,1:部分,2:全不)',
  goods_cate_ids      varchar(200)    not null default ''                comment '参与商品分类ID(为空不限)',
  goods_brand_ids     varchar(200)    not null default ''                comment '参与品牌ID(为空不限)',
  goods_whitelist     text            not null default ''                comment '商品白名单',
  goods_blacklist     text            not null default ''                comment '商品黑名单',
  order_mode          TINYINT         not null default 0                 comment '下单方式(0:不限,1:仅自助下单)',
  pay_mode            TINYINT         not null default 0                 comment '支付方式(0:不限,1:仅在线支付)',
  delivery_time_type  tinyint         not null default 0                 comment '配送时间类型(0:无要求,1:有要求)',
  delivery_time_extand varchar(200)   not null default ''                comment '配送时间明细',
  activity_content    text            not null default ''                comment '活动说明',
  create_suid         int             not null default 0                 comment '录入者id',
  edit_suid           int             not null default 0                 comment '编辑者id',
  m_status            int             not null default 0                 comment '活动状态(0:上线,4:下线,1:删除)',
  ctime               timestamp       not null default '0000-00-00 00:00:00',
  mtime               timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  index (stime),
  index (etime),
  index (m_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1;

/*-----------------------------------------------
 * 优惠券类型表
 *-----------------------------------------------*/
CREATE TABLE t_promotion_coupon (
  id                 int              not null AUTO_INCREMENT,
  title              VARCHAR(200)     NOT NULL DEFAULT ''              COMMENT '优惠券名称',
  validity_type      TINYINT          NOT NULL DEFAULT 1               COMMENT '有效期类型(1:固定时间段,2:固定时长)',
  validity_extand    VARCHAR(200)     NOT NULL DEFAULT ''              COMMENT '有效期明细',
  limit_amount       INT              NOT NULL DEFAULT 0               COMMENT '使用最低额度',
  amount             INT              NOT NULL DEFAULT 0               COMMENT '优惠券面值',
  coupon_type        TINYINT          NOT NULL DEFAULT 1               COMMENT '优惠券类型(1:满减优惠券,2:vip现金券)',
  type_ids           varchar(100)     not null default ''              comment '活动同享类型',
  conf               VARCHAR(200)     NOT NULL DEFAULT ''              COMMENT '优惠券配置',
  contain_manjian    TINYINT          NOT NULL DEFAULT 1               COMMENT '可否与满减活动叠加',
  contain_sand       tinyint          not null default 1               comment '是否包含砂石类(1:是,0:否)',
  contain_meichao    tinyint          not null default 1               comment '是否包含美巢商品(1:是,0:否)',
  contain_special    tinyint          not null default 1               comment '是否包含特价商品(1:是,0:否)',
  contain_hot        tinyint          not null default 1               comment '是否包含热卖商品(1:是,0:否)',
  goods_whitelist    text             not null default ''              comment '商品白名单',
  goods_blacklist    text             not null default ''              comment '商品黑名单',
  coupon_content     text             not null default ''              comment '活动说明',
  c_status           int              not null default 0               comment '活动状态(0:上线,4:下线,1:删除)',
  create_suid        int              not null default 0               comment '录入者id',
  edit_suid          int              not null default 0               comment '编辑者id',
  ctime              timestamp        not null default '0000-00-00 00:00:00',
  mtime              timestamp        not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  index (coupon_type),
  index (c_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


/*-----------------------------------------------
 *GPS信息表
 *-----------------------------------------------*/
CREATE TABLE t_driver_location (
  id                INT             not null auto_increment,
  did               INT             not null default 0                        comment '司机id',
  line_id           INT             not null default 0                        comment '排线id',
  lat               DECIMAL(11,8)   not null default 0                        comment '纬度',
  lng               DECIMAL(11,8)   not null default 0                        comment '经度',
  oid               INT             not null default 0                        comment '订单id',
  cmd_id            INT             not null default 0                        comment '小区id',
  record_time       timestamp       not null default '0000-00-00 00:00:00'    comment '记录的时间',
  ctime             timestamp       not null default '0000-00-00 00:00:00'    ,
  PRIMARY KEY (id),
  key (did),
  key (line_id),
  key (oid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * APP版本管理表
 *-----------------------------------------------*/
CREATE TABLE t_app_version (
  id                INT             not null auto_increment,
  platform          tinyint         not null default 1                        comment '1安卓2ios',
  cate              INT             not null default 0                        comment 'app分类，1商城/2司机端/3wms，见Conf_App_Verison',
  dev               INT             not null default 0                        comment '环境，1正式2测试',
  channel           varchar(50)     not null default ''                       comment '渠道',
  version_code      INT             not null default 0                        comment '版本号，给程序使用，整数',
  version           varchar(20)     not null default ''                       comment '版本号，app显示用，如V2.1.0',
  is_force          tinyint         not null default 0                        comment '是否强制升级，0否1是',
  file              varchar(200)    not null default ''                       comment '文件地址',
  description       text            not null                                  comment '更新说明',
  suid              int             not null default 0                        comment '发布者',
  ctime             timestamp       not null default '0000-00-00 00:00:00'    ,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  status            INT             not null default 0,
  PRIMARY KEY (id),
  key (cate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 *后台消息表
 *-----------------------------------------------*/
CREATE TABLE t_message (
  id                int            NOT NULL AUTO_INCREMENT,
  has_read          tinyint        NOT NULL DEFAULT '0' COMMENT '是否已读(0:未读,1:已读)',
  m_type            tinyint        NOT NULL DEFAULT '1' COMMENT '消息类型(1:系统消息,2:售后工单,3:退货单,4:换货单,5:生日提醒,6:日程提醒)',
  typeid            int            UNSIGNED NOT NULL DEFAULT '0' COMMENT '单号id(售后工单,退货单,换货单)',
  content           varchar(1000)  NOT NULL DEFAULT '' COMMENT '消息内容',
  url               varchar(200)   NOT NULL DEFAULT '' COMMENT '链接地址',
  send_suid         int            NOT NULL DEFAULT '0' COMMENT '发送人',
  receive_suid      int            NOT NULL DEFAULT '0' COMMENT '接收人',
  ctime             timestamp      NOT NULL DEFAULT '0000-00-00 00:00:00',
  mtime             timestamp      NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (id),
  key (receive_suid, ctime)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 *换货单表
 *-----------------------------------------------*/
CREATE TABLE t_exchanged (
  eid                int            NOT NULL AUTO_INCREMENT,
  oid               int            NOT NULL DEFAULT '0' COMMENT '源订单id',
  aftersale_oid     int            NOT NULL DEFAULT '0' COMMENT '售后订单id',
  refund_id         int            NOT NULL DEFAULT '0' COMMENT '退货单id',
  cid               int            NOT NULL DEFAULT '0' COMMENT '客户id',
  uid               int            NOT NULL DEFAULT '0' COMMENT '用户id',
  saler_suid        int            NOT NULL DEFAULT '0' COMMENT '销售人员id',
  contact_name      varchar(48)    NOT NULL DEFAULT ''  COMMENT '联系人',
  contact_phone     varchar(48)    NOT NULL DEFAULT ''  COMMENT '联系电话',
  city_id           int            NOT NULL DEFAULT '0' COMMENT '城市id',
  address           varchar(256)   NOT NULL DEFAULT ''  COMMENT '送货地址',
  delivery_time     timestamp      NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '送货时间',
  wid               int            NOT NULL DEFAULT '0' COMMENT '仓库id',
  m_type            tinyint(4)     NOT NULL DEFAULT '1' COMMENT '换货类型(1:单独换货,2:随单换货)',
  exchanged_time    timestamp      NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '换货时间开始',
  exchanged_time_end  timestamp      NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '换货时间结束',
  exchanged_status  tinyint(4)     NOT NULL DEFAULT '0' COMMENT '状态(0:正常,1:删除,3:取消)',
  step              tinyint(4)     NOT NULL DEFAULT '1' COMMENT '所处阶段(1:已创建,2:已审核,3:已完成)',
  reason_id         tinyint(4)     NOT NULL DEFAULT '0' COMMENT '换货原因id一级',
  reason_second_id  tinyint(4)     NOT NULL DEFAULT '0' COMMENT '换货原因id二级',
  need_storage      tinyint(4)     NOT NULL DEFAULT '0' COMMENT '是否需要入库(0:否,1:是)',
  carry_fee         INT            NOT NULL DEFAULT 0   comment '搬运费',
  freight           INT            NOT NULL DEFAULT 0   comment '运费',
  privilege         int            not null DEFAULT 0   COMMENT '优惠',
  note              varchar(200)   NOT NULL DEFAULT ''  COMMENT '补充说明',
  content           text           NOT NULL             COMMENT '退货商品、换货商品json数组',
  suid              int            NOT NULL DEFAULT '0' COMMENT '录单人id',
  audit_suid        int            NOT NULL DEFAULT '0' COMMENT '审核人id',
  audit_time        timestamp      NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '审核时间',
  ctime             timestamp      NOT NULL DEFAULT '0000-00-00 00:00:00',
  mtime             timestamp      NOT NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (eid),
  index (oid),
  index (aftersale_oid),
  index (refund_id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 *盘库计划表
 *-----------------------------------------------*/
CREATE TABLE t_inventory_plan (
  pid               int            NOT NULL AUTO_INCREMENT,
  method            tinyint        NOT NULL DEFAULT  0  COMMENT '盘点方式(1:静态,2:动态)',
  attribute         tinyint        NOT NULL DEFAULT  0  COMMENT '盘点属性(1:盲盘,2:明盘)',
  times             tinyint        NOT NULL DEFAULT  0  COMMENT '盘点次数(1:初盘,2:复盘,3:三盘)',
  type              tinyint        NOT NULL DEFAULT  0  COMMENT '盘点类型(1:盘所有,2:按货位,3:按品牌)',
  start_location    varchar(80)    NOT NULL DEFAULT ''  COMMENT '开始货位',
  end_location      varchar(80)    NOT NULL DEFAULT ''  COMMENT '结束货位',
  brand_id          varchar(255)   NOT NULL DEFAULT ''  COMMENT '品牌id(多个之间用逗号隔开)',
  is_random         tinyint        NOT NULL DEFAULT  0  COMMENT '是否抽查(1:是,2:否)',
  random_num        smallint       NOT NULL DEFAULT  0  COMMENT '抽查数量',
  plan_type         tinyint        NOT NULL DEFAULT  0  COMMENT '计划类型(1:日常盘点,2:月度盘点,3:年终盘点)',
  step              tinyint        NOT NULL DEFAULT  1  COMMENT '计划状态(1:未开始,2:进行中,3:已分配,4:已完成)',
  status            tinyint        NOT NULL DEFAULT  0  COMMENT '状态',
  wid               int            NOT NULL DEFAULT  0  COMMENT '仓库id',
  suid              int            NOT NULL DEFAULT  0  COMMENT '操作人id',
  is_update         tinyint        NOT NULL DEFAULT  2  COMMENT '是否更新库存(1-,2-)',
  ctime             timestamp      NOT NULL DEFAULT '0000-00-00 00:00:00'   COMMENT '盘点创建时间',
  etime             timestamp      NOT NULL DEFAULT '0000-00-00 00:00:00'   COMMENT '盘点结束时间',
  mtime             timestamp      NOT NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (pid)

) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=100;

/*-----------------------------------------------
 *盘库任务表
 *-----------------------------------------------*/
CREATE TABLE t_inventory_task (
  tid               int            NOT NULL AUTO_INCREMENT,
  plan_id           int            NOT NULL DEFAULT  0  COMMENT '计划id',
  num               int            NOT NULL DEFAULT  0  COMMENT '任务中需盘点的sku数量',
  diff_num          int            NOT NULL DEFAULT  0  COMMENT '任务中盘点的差异数量',
  step              tinyint        NOT NULL DEFAULT  1  COMMENT '任务状态(1:未开始,2:已分配,3:进行中,4:已完成)',
  status            tinyint        NOT NULL DEFAULT  0  COMMENT '状态',
  times             tinyint        NOT NULL DEFAULT  0  COMMENT '盘点次数(1:初盘,2:复盘,3:三盘)',
  wid               int            NOT NULL DEFAULT  0  COMMENT '仓库id',
  suid              int            NOT NULL DEFAULT  0  COMMENT '操作人id',
  alloc_suid        int            NOT NULL DEFAULT  0  COMMENT '领取人id',
  ctime             timestamp      NOT NULL DEFAULT '0000-00-00 00:00:00'   COMMENT '任务创建时间',
  etime             timestamp      NOT NULL DEFAULT '0000-00-00 00:00:00'   COMMENT '任务结束时间',
  mtime             timestamp      NOT NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (tid),
  index(plan_id),
  index(alloc_suid)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*-----------------------------------------------
 *盘库商品表
 *-----------------------------------------------*/
CREATE TABLE t_inventory_products (
  sid               int            NOT NULL DEFAULT  0  COMMENT 'sku_id',
  plan_id           int            NOT NULL DEFAULT  0  COMMENT '盘点计划id',
  wid               int            NOT NULL DEFAULT  0  COMMENT '仓库id',
  location          char(10)       NOT NULL DEFAULT  '' COMMENT '货位',
  num               int            NOT NULL DEFAULT  0  COMMENT '库存数量',
  task_id1          int            NOT NULL DEFAULT  0  COMMENT '任务id1',
  task_id2          int            NOT NULL DEFAULT  0  COMMENT '任务id2',
  task_id3          int            NOT NULL DEFAULT  0  COMMENT '任务id3',
  is_picked1        tinyint        NOT NULL DEFAULT  0  COMMENT '是否提交1',
  is_picked2        tinyint        NOT NULL DEFAULT  0  COMMENT '是否提交2',
  is_picked3        tinyint        NOT NULL DEFAULT  0  COMMENT '是否提交3',
  first_num         int            NOT NULL DEFAULT  0  COMMENT '第一次盘点数量',
  second_num        int            NOT NULL DEFAULT  0  COMMENT '第二次盘点数量',
  third_num         int            NOT NULL DEFAULT  0  COMMENT '第三次盘点数量',
  is_deal           tinyint        NOT NULL DEFAULT  2  COMMENT '是否处理(1-已处理,2-未处理)',
  ctime             timestamp      NOT NULL DEFAULT '0000-00-00 00:00:00',
  mtime             timestamp      NOT NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  note              text           NOT NULL             COMMENT '备注',

  primary key(sid,plan_id,location),
  index(task_id1),
  index(task_id2),
  index(task_id3)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 *补漏单表
 *-----------------------------------------------*/
CREATE TABLE t_traps (
  tid               int            NOT NULL AUTO_INCREMENT,
  oid               int            NOT NULL DEFAULT '0' COMMENT '源订单id',
  aftersale_oid     int            NOT NULL DEFAULT '0' COMMENT '售后订单id',
  cid               int            NOT NULL DEFAULT '0' COMMENT '客户id',
  city_id           int            NOT NULL DEFAULT '0' COMMENT '城市id',
  wid               int            NOT NULL DEFAULT '0' COMMENT '仓库id',
  m_type            tinyint(4)     NOT NULL DEFAULT '1' COMMENT '补漏类型(1:单独补漏,2:预约补货)',
  traps_time        timestamp      NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '补漏时间开始',
  traps_time_end    timestamp      NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '补漏时间结束',
  traps_status      tinyint(4)     NOT NULL DEFAULT '0' COMMENT '状态(0:正常,1:删除)',
  step              tinyint(4)     NOT NULL DEFAULT '1' COMMENT '所处阶段(1:已创建,2:已审核,3:已完成)',
  reason_id         tinyint(4)     NOT NULL DEFAULT '0' COMMENT '补漏原因id一级',
  reason_second_id  tinyint(4)     NOT NULL DEFAULT '0' COMMENT '补漏原因id二级',
  need_storage      tinyint(4)     NOT NULL DEFAULT '0' COMMENT '是否需要入库(0:否,1:是)',
  carry_fee         INT            NOT NULL DEFAULT 0   comment '搬运费',
  freight           INT            NOT NULL DEFAULT 0   comment '运费',
  privilege         int            not null DEFAULT 0   COMMENT '优惠',
  note              varchar(200)   NOT NULL DEFAULT ''  COMMENT '补充说明',
  content           text           NOT NULL             COMMENT '补漏商品json数组',
  suid              int            NOT NULL DEFAULT '0' COMMENT '录单人id',
  audit_suid        int            NOT NULL DEFAULT '0' COMMENT '审核人id',
  ctime             timestamp      NOT NULL DEFAULT '0000-00-00 00:00:00',
  mtime             timestamp      NOT NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (tid),
  index (oid),
  index (cid),
  index (aftersale_oid)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 微信限制表，用于记录微信号做过某些操作，比如是否分享了20170224的分享抽奖活动
 *-----------------------------------------------*/
CREATE TABLE t_weixin_limit (
  lid               int                 not null auto_increment,
  openid            varchar(200)        not null default ''          comment '微信openid',
  lkey              int                 not null default 0          comment '操作key',
  val               int                 not null default 0          comment '限制值',
  ext               text                not null                    comment '扩展信息',
  ctime             timestamp           not null default 0,
  mtime             timestamp           not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY key (lid),
  UNIQUE (openid, lkey)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 订单评价
 *-----------------------------------------------*/
CREATE TABLE t_order_comment (
    id              int             not null auto_increment,
    oid             INT             not null default 0    comment '订单id',
    cid             INT             not null default 0    comment '订单cid',
    uid             INT             not null default 0    comment '订单uid',
    city_id         int             not null default 0    comment '城市id',
    level           INT             not null default 0    comment '评价级别，好中差',
    tag             varchar(50)     not null default 0    comment '评价标签',
    comment_all     tinyint         not null default 0    comment '整体评价',
    comment_delivery tinyint        not null default 0    comment '配送评价',
    comment_carry   tinyint         not null default 0    comment '搬运评价',
    note            varchar(500)    not null default ''   comment '客户输入评价',
    status          INT             not null default 0,
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    index(oid),
    index(cid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 取消订单原因
 *-----------------------------------------------*/
CREATE TABLE t_order_cancel_reason (
    id              int             not null auto_increment,
    oid             INT             not null default 0    comment '订单id',
    cid             INT             not null default 0    comment '订单cid',
    uid             INT             not null default 0    comment '订单uid',
    city_id         int             not null default 0    comment '城市id',
    reason          INT             not null default 0    comment '订单取消原因',
    status          INT             not null default 0,
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    index(oid),
    index(cid)
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

/*-----------------------------------------------
 * 队列：任务队列（时效性要求不高的任务）
 *-----------------------------------------------*/
CREATE TABLE t_queue_insensitive (
  id                int             NOT NULL AUTO_INCREMENT,
  info              text            NOT NULL                COMMENT '任务详情/处理条件',
  type              varchar(64)     NOT NULL DEFAULT 0      COMMENT '任务类型',
  suid              int             not null default 0      comment '任务发起人id',
  status            tinyint         NOT NULL DEFAULT 0      COMMENT '状态，0新任务等待处理  1处理成功  2处理失败', 

  file_path         varchar(255)    NOT NULL DEFAULT ''     COMMENT '文件位置',
  code              varchar(10)     NOT NULL DEFAULT ''     COMMENT '下载code码', 
  out_time          timestamp       NOT NULL DEFAULT 0      COMMENT '过期时间',

  ctime             timestamp       not null default 0      COMMENT '创建时间',
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP COMMENT '任务完成时间',
  
  PRIMARY KEY (id),
  index(suid, code)

) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8;

/*-----------------------------------------------
*入库结算单表
*-----------------------------------------------*/
CREATE TABLE t_stockin_statements(
  id                INT             NOT NULL AUTO_INCREMENT,
  supplier_id       INT             NOT NULL DEFAULT 0    COMMENT '供应商ID',
  payment_type      TINYINT         NOT NULL DEFAULT 0    COMMENT '付款方式，系统统一定义方式',
  paid              TINYINT         NOT NULL DEFAULT 0    COMMENT '是否已收款:0-未兑账,1-未付款,2-已付款',
  amount            INT             NOT NULL DEFAULT 0    COMMENT '金额（分）',
  suid              INT             NOT NULL DEFAULT 0    COMMENT '录单人ID',
  payer_suid        INT             NOT NULL DEFAULT 0    COMMENT '付款人ID',
  invoice_ids       VARCHAR(200)    NOT NULL DEFAULT ''   COMMENT '发票id',
  status          TINYINT             not null default 0,
  ctime           timestamp       not null default 0,
  mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  index(supplier_id)

)ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1001;

/*-----------------------------------------------
 * 其他出库单
 *-----------------------------------------------*/
CREATE TABLE t_other_stock_order (
    oid             int             not null auto_increment,
    wid             int             not null default  0      comment '仓库id',
    suid            int             not null default  0      comment '操作人',
    check_suid      int             not null default  0      comment '审核人',
    stock_suid      int             not null default  0      comment '出入库人',
    supplier_id     int             not null default  0      comment '供应商',
    order_type      tinyint         not null default  1      comment '订单类型1-其他出库,2-其他入库',
    type            tinyint         not null default  1      comment '领用单类型',
    step            tinyint         not null default  1      comment '单据状态',
    reason          tinyint         not null default  0      comment '原因类型',
    status          tinyint         not null default  0      comment '状态',
    note            text            not null                 comment '备注',
    ctime           timestamp       not null default 0,
    etime           timestamp       not null default 0       comment '完成时间',
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (oid)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 其他出库单商品
 *-----------------------------------------------*/
CREATE TABLE t_other_stock_products (
    oid             int             not null default 0      comment '其他出库单据id',
    sid             int             not null default 0      comment 'sku id',
    num             int             not null default 0      comment '商品数量',
    cost            int             not null default 0      comment '成本, 单位:分',
    shelved_num     int             not null default 0      comment '入库上架数量',
    from_location   varchar(100)    not null default ''     comment '货位，多货位存储货位+数量 ',
    managing_mode   TINYINT         NOT NULL DEFAULT 1      COMMENT '经营模式，1-自营，2-联营',
    note            varchar(100)    not null default ''     comment '备注',
    status          tinyint         not null default 0      comment '状态',

    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  primary key(oid, sid),
  index(sid)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 *  加工单：商品转换
 *-----------------------------------------------*/
CREATE TABLE t_processed_order (
    id              int             not null auto_increment,
    wid             tinyint         not null default 0          comment '仓库id',
    type            tinyint         not null default 0          comment '类型：组合售卖，整转零售，...',
    create_suid     int             not null default 0          comment '创建人id',
    status          tinyint         not null default 0          comment '状态',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id)

) ENGINE=INNODB DEFAULT CHARSET=utf8 auto_increment=1000;

/*-----------------------------------------------
 *  加工单商品表
 *-----------------------------------------------*/
CREATE TABLE t_processed_order_products (
    id              int             not null default 0          comment '加工单id',
    sid             int             not null default 0          comment 'sku id',
    type            tinyint         not null default 0          comment '商品属性：组合/整，部件/零',
    num             int             not null default 0          comment '数量',
    cost            int             not null default 0          comment '成本',
    location        varchar(80)     not null default ''         comment '货位，多货位存储货位+数量',

    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY(id, sid),
    index(sid)

) ENGINE=INNODB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 *  购物车表
 *-----------------------------------------------*/
CREATE TABLE t_cart (
    id              int             not null auto_increment,
    uid             int             not null default 0          comment 'uid',
    city_id         int             not null default 0          comment '城市id',
    sid             int             not null default 0          comment 'sku id',
    pid             int             not null default 0          comment 'product id',
    num             int             not null default 0          comment '数量',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    unique key (uid, pid)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 *  客户商品关联表
 *-----------------------------------------------*/
CREATE TABLE t_user_product_relation (
    uid             int             not null default 0          comment '客户uid',
    sid             int             not null default 0          comment 'sku id',
    pid             int             not null default 0          comment '商品id',
    city_id         int             not null default 0          comment '城市id',
    frequency       int             not null default 0          comment '购买频次',
    status          tinyint         not null default 0          comment '状态',
    
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (uid, pid),
    INDEX (sid),
    INDEX (frequency)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE t_out_trade (
    id            int             not null auto_increment,
    out_trade_no  varchar(100)    not null default ''   comment '支付宝/微信支付的订单号',
    oid           INT             not null default 0    comment '系统订单号',
    amount        INT             not null default 0    comment '本次支付金额',
    payment_type  INT             not null default 0    comment '支付方式',
    ctime         timestamp       not null default 0,
    mtime         timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX(oid),
    INDEX(out_trade_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 *  销售优惠记录表
 *-----------------------------------------------*/
create table t_sale_preferential_send_record(
  month               int             not null default 0      comment  '发放月份:201806',
  oid                 int             not null default 0      comment  '订单ID',
  send_suid           int             not null default 0      comment  '发放人',
  amount              int             not null default 0      comment  '优惠金额',
  sale_suid           int             not null default 0      comment  '所属销售',
  status              tinyint         not null default 0      comment  '状态',
  ctime               timestamp       not null default 0,
  mtime               timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (oid, month),
  INDEX (send_suid,ctime),
  INDEX (month,sale_suid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 *  销售优惠配置表
 *-----------------------------------------------*/
create table t_sale_privilege_config
(
  id      int   not null  auto_increment,
  month   int   not null default 0  comment '月份:201806',
  city_id int   not null default 0  comment '所属城市',
  suid    int   not null default 0  comment '销售id',
  total_amount  int not null default 0  comment '销售优惠总额',
  available_amount int not null default 0  comment '销售可用优惠',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  unique(month,suid,city_id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 *  客户实名认证信息
 *-----------------------------------------------*/
create table t_customer_certification(
  id                  int             auto_increment,
  cid                 int             not null default 0       comment '客户id',
  type                tinyint         not null default 1       comment '用户类型：个人/公司',
  real_name           varchar(50)     not null default ''      comment  '真实姓名',
  mobile              varchar(20)     not null default ''      comment  '手机号码',
  id_number           varchar(50)     not null default ''      comment  '身份证号码',
  band_card_number    varchar(50)     not null default ''      comment  '银行卡号',
  company_name        varchar(200)    not null default ''      comment  '公司名称',
  legal_person_name   varchar(50)     not null default ''      comment '法人姓名',
  legal_person_id_number  varchar(50) not null default ''      comment '法人身份证号码',
  social_credit_number    varchar(100) not null default ''     comment '统一社会信用代码',
  step                tinyint         not null default 1       comment '处理状态',
  reason              varchar(200)    not null default ''      comment '原因',
  status              tinyint         not null default 0      comment  '状态',
  ctime               timestamp       not null default 0,
  mtime               timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY (cid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 *  待管理员认证列表
 *-----------------------------------------------*/
create table t_certificate_list(
  id                  int             auto_increment,
  suid                int             not null default 0       comment '管理员id',
  cid                 int             not null default 0       comment '客户id',
  result              tinyint         not null default 1       comment '处理状态',
  reason              varchar(200)    not null default ''      comment '拒绝原因',
  deal_time           timestamp       not null default 0       comment '处理时间',
  status              tinyint         not null default 0       comment  '状态',
  ctime               timestamp       not null default 0,
  mtime               timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY (cid),
  KEY (suid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*-----------------------------------------------
 *  订单活动商品表
 *-----------------------------------------------*/
create table t_order_activity_product(
  pid      int    not null    default 0    comment '商品PID',
  sid      int    not null    default 0    comment 'Sku',
  oid      int    not null    default 0    comment '订单id',
  price    int    not null    default 0    comment '活动价',
  sale_price int  not null    default 0    comment '售价',
  num      int    not null    default 0    comment '商品数量',
  type     int    not null    default 0    comment '活动类型',
  activity_id INT NOT NULL DEFAULT 0    COMMENT '活动id',
  ctime               timestamp       not null default 0,
  mtime               timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (pid,oid,activity_id),
  INDEX (sid),
  INDEX (activity_id),
  INDEX (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 *  用户酷家乐方案表
 *-----------------------------------------------*/
create table t_user_kjl_design(
  id              int           not null auto_increment,
  uid             int           not null  default 0     comment '用户id',
  plan_id         varchar(50)   not null  default ''    comment '户型id',
  design_id       varchar(50)   not null  default ''    comment '方案id',
  community_name  varchar(200)  not null  default ''    comment '小区名',
  city            varchar(100)  not null  default ''    comment '城市信息',
  design_name     varchar(200)  not null  default ''    comment '方案名',
  src_area        FLOAT         not null  default 0     comment '建筑面积',
  spec_name       varchar(100)  not null  default ''    comment '户型名称',
  area            FLOAT         not null  DEFAULT 0     comment '套内面积',
  created         varchar(50)   not null  default ''    comment '创建时间',
  modified        varchar(50)   not null  default ''    comment '修改时间',
  plan_pic        varchar(255)  not null  default ''    comment '户型图',
  cover_pic       varchar(255)  not null  default ''    comment '封面图',
  ctime               timestamp       not null default 0,
  mtime               timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX (uid, design_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 *  加盟商账号体系
 *-----------------------------------------------*/
create table t_franchisee(
    fid                 int                 not null auto_increment,
    name                varchar(32)         not null default ''     comment '名称',
    city_id             int                 not null default 0      comment '城市id',
    wids                varchar(50)         not null default ''     comment '仓库ID，多仓库使用半角逗号分隔',
    mobile              char(11)            not null default ''     comment '手机号',
    password            varchar(32)         not null default ''     comment '密码',
    salt                smallint            not null default 0      comment '密码salt',

    status              tinyint             not null default 0      comment '状态',
    ctime               timestamp           not null default 0,
    mtime               timestamp           not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (fid),
    UNIQUE (mobile)

) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=100000;

/*-----------------------------------------------
 *  平台/轻模式：合作商账号体系
 *-----------------------------------------------*/
create table t_platform_seller(
    plfid               int                 not null auto_increment,
    wid                 int                 not null default 0          comment '关联仓库id',
    name                varchar(40)         not null default ''         comment '名称',
    mobile              char(11)            not null default ''         comment '手机',
    phone               varchar(32)         not null default ''         comment '固定电话，多个用逗号分隔',
    account_balance     int                 not null default 0          comment '账户余额，单位：分',
    service_rate        tinyint             not null default 0          comment '服务费率',
    
    status              tinyint             not null default 0          comment '状态',
    ctime               timestamp           not null default 0,
    mtime               timestamp           not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (plfid),
    UNIQUE (mobile)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=6000;

/*-----------------------------------------------
 *  商家轻模式结算表
 *-----------------------------------------------*/
create table t_seller_bill(
  bid             int        not null    AUTO_INCREMENT,
  wid             int        not null    default 0    comment '仓库ID',
  balance_date_start   date      not null default '0000-00-00'   comment '结算开始日期',
  balance_date_end     date      not null default '0000-00-00'   comment '结算结束日期',
  order_amount    int        not null    default 0    comment '订单总金额',
  product_amount  int        not null    DEFAULT 0    comment '货款总金额',
  refund_amount   int        not null    default 0    comment '退款总金额',
  ratio           int        not null    default 0    comment '扣点系数',
  bill_amount     int        not null    default 0    comment '结算金额',
  real_amount     int        not null    DEFAULT 0    comment '实付金额',
  suid            int        not null    DEFAULT 0    comment '结算人',
  step            INT        NOT NULL    DEFAULT 1    COMMENT '处理状态',
  payment_type    INT        not null    default 0    comment '支付方式',
  pay_time        timestamp  not null    default 0    comment '支付时间',
  note            varchar(200)  not null    DEFAULT ''   comment '备注',
  status          tinyint    not null    default 0    comment '状态',
  ctime           timestamp  not null    default 0,
  mtime           timestamp  not null    default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (bid),
  index(pay_time),
  UNIQUE (wid,balance_date_start,balance_date_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1000;

/*-----------------------------------------------
 *  商家轻模式结算关联表
 *-----------------------------------------------*/
create table t_seller_bill_receipt(
  id              int           not null auto_increment,
  bid           int             not null DEFAULT 0   COMMENT '结算单ID',
  objid         int             not null default 0   comment 'HC单据id',
  objtype       tinyint         not null default 0   comment 'HC单据类型(1:订单,2:退款)',
  pay_time      TIMESTAMP       NOT NULL DEFAULT 0   COMMENT '付款时间',
  delivery_time TIMESTAMP       NOT NULL DEFAULT 0   COMMENT '出库/入库时间',
  bill_amount   int             NOT NULL DEFAULT 0   COMMENT '货款/退款金额',
  status        tinyint         not null default 0   comment '状态',
  ctime         timestamp       not null default 0,
  mtime         timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE (objid,objtype)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE t_admin_login_log (
    id              int             not null auto_increment,
    suid            int             not null default 0          comment '管理员id',
    source          varchar(50)     not null default ''         comment '登录来源，后台/crm。。。',
    ip              INT UNSIGNED    not null default 0          comment '登录ip',
    agent           varchar(200)    not null default 0          comment '用户浏览器协议',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    index (suid)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 *  客户类型审核表
 *-----------------------------------------------*/
create table t_customer_identity_apply(
  cid           int      not null DEFAULT 0    COMMENT '客户ID',
  identity      TINYINT  not null DEFAULT 0    COMMENT '客户类型',
  suid          int      not null DEFAULT 0    COMMENT '处理人',
  step          TINYINT  not null DEFAULT 1    COMMENT '处理状态:1待处理,2已通过,3已驳回',
  ctime         timestamp       not null default 0,
  mtime         timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (cid)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 *  客户搜索户型记录表
 *-----------------------------------------------*/
create table t_customer_search_plan(
  id            int      not null auto_increment,
  cid           int      not null DEFAULT 0    COMMENT '客户ID',
  uid           int      not null default 0    comment 'uid',
  city_id       int      not null default 0    comment '城市',
  keyword       varchar(100)  not null default '' comment '搜索关键词',
  num           int      not null default 1  comment '搜次数',
  ctime         timestamp       not null default 0,
  mtime         timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  index (uid, keyword)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 *  推荐品牌配置表
 *-----------------------------------------------*/
create table t_city_brands(
  id            int      not null auto_increment,
  city_id       int      not null DEFAULT 0       comment '城市id',
  water_1       varchar(100) not null DEFAULT ''  comment '水',
  electric_2    varchar(100) not null default ''  comment '电',
  wood_3        varchar(100) not null default ''  comment '木',
  tile_4        varchar(100) not null default ''  comment '瓦',
  oil_5         varchar(100) not null default ''  comment '油',
  tools_6       varchar(100) not null default ''  comment '工具',

  ctime         timestamp       not null default 0,
  mtime         timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE (city_id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*------------------------------------------------
 *  供应商余额明细
 *------------------------------------------------*/
CREATE TABLE t_supplier_amount_history(
    id              int             not null AUTO_INCREMENT,
    sid             int             not null default 0          comment '供应商id',
    objid           int             not null default 0          comment '单据id: 采购单/入库单/退单(等)id',
    type            tinyint         not null default 0          comment '单据类型',
    oid             int             not null default 0          comment '对应订单id',
    city_id         int             not null default 0          comment '城市id',
    price           int             not null default 0          comment '支付金额（单位：分）',
    amount          int             not null default 0          comment '账户余额（单位：分）',
    suid            int             not null default 0          comment '执行人id',
    payment_type    tinyint         not null default 0          comment '付款方式',
    note            text            not null                    comment '备注',
    status          tinyint         not null default 0          comment '状态: 0-正常, 1-删除, 2-取消',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    primary key (id),
    index(sid),
    index(objid, type),
    index(ctime)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1000;

CREATE TABLE t_sales_manpfm(
    sale_day    date            not null default 0           comment '日期',
    suid        int             not null default 0           comment '销售suid',
    vaule       text            not null default ''          comment '业绩',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    primary key (sale_day,suid)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;