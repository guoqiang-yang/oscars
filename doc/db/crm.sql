-- 存放CRM系统相关的表结构信息
/*-----------------------------------------------
 * 客户拜访表
 *-----------------------------------------------*/
CREATE TABLE t_customer_visit (
    id              int             not null auto_increment,
    cid             int             not null default 0          comment '客户cid',
    suid            int             not null default 0          comment '销售suid',
    city_id         int             not null default 0          comment '城市ID',
    visit_time      timestamp       not null default 0          comment '拜访时间',
    visit_type      tinyint         not null default 1          comment '拜访类型(1:现场拜访 2:电话拜访，3:微信拜访，4:短信拜访)',
    address         varchar(200)    not null default ''         comment '拜访地址',
    lng             DECIMAL(11,8)   not null                    comment '经度',
    lat             DECIMAL(11,8)   not null                    comment '纬度',
    content         varchar(2000)   not null default ''         comment '拜访反馈',
    pic_ids         text            not null default ''         comment '图片地址,半角逗号分隔',
    status          tinyint         not null default 0          comment '状态',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX (cid),
    INDEX (suid),
    INDEX (visit_time)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 销售日程表
 *-----------------------------------------------*/
CREATE TABLE t_sale_schedule (
    id              int             not null auto_increment,
    suid            int             not null default 0          comment '销售suid',
    schedule_time   timestamp       not null default 0          comment '日程时间',
    remind_tag      tinyint         not null default 0          comment '提醒时间（1小时前，2小时前……）',
    remind_time     timestamp       not null default 0          comment '提醒时间',
    content         varchar(2000)   not null default ''         comment '内容',
    cid             int             not null default 0          comment '关联客户',
    has_remind      tinyint         not null default 0          comment '是否已提醒',
    vid             int             not null default 0          comment '关联拜访id',
    status          tinyint         not null default 0          comment '状态',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX (suid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 亲朋好友表
 *-----------------------------------------------*/
CREATE TABLE t_customer_relative (
    crid              int             not null auto_increment,
    cid               int             not null default 0          comment '客户id',
    name              VARCHAR(50)     not null default ''         comment '姓名',
    nick_name         VARCHAR(50)     not null default ''         comment '称呼',
    age               INT             not null default 0          comment '年龄',
    sex               tinyint         not null default 0          comment '性别',
    relation          varchar(50)     not null default ''         comment '关系',
    interest          varchar(200)    not null default ''         comment '兴趣爱好',
    shape             varchar(100)    not null default ''         comment '身体状况',
    trade             varchar(100)    not null default ''         comment '从事行业',
    note              varchar(200)    not null default ''         comment '其他信息',
    mobile            varchar(50)     not null default ''         comment '电话',
    weixin            varchar(50)     not null default ''         comment '微信',
    qq                varchar(30)     not null default ''         comment 'qq',
    email             varchar(50)     not null default ''         comment '邮箱',
    status            tinyint         not null default 0          comment '状态',
    ctime             timestamp       not null default 0,
    mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (crid),
    INDEX (cid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



/*-----------------------------------------------
 * 客户积分商品表
 *-----------------------------------------------*/
CREATE TABLE t_cpoint_product (
    pid             int             not null auto_increment,
    title           varchar(40)     not null default ''             comment '商品名称',
    cate1           tinyint         not null default 0              comment '一级分类',
    price           int             not null default 0              comment '市场价 单位:分',
    cost            int             not null default 0              comment '成本 单位:分',
    point           int             not null default 0              comment '兑换积分',
    stock_num       int             not null default 0              comment '库存数量',
    exchg_num       int             not null default 0              comment '兑换数量',
    stime           date            not null default 0              comment '开始时间',
    etime           date            not null default 0              comment '结束时间',
    pics            text            not null                        comment '图片，半角逗号分隔',
    abstract        varchar(300)    not null default ''             comment '商品摘要',
    detail          text            not null                        comment '商品描述',
    member_level    varchar(20)     not null default ''             comment '兑换等级，半角逗号分隔',
    create_suid     int             not null default 0              comment '创建人',
    modify_suid     int             not null default 0              comment '最后修改人',
    status          tinyint         not null default 0              comment '状态',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,           

    PRIMARY KEY (pid),
    INDEX (title),
    INDEX (stime),
    INDEX (etime)

)ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1000;

/*-----------------------------------------------
 * 客户积分流水表
 *-----------------------------------------------*/
CREATE TABLE t_cpoint_history (
    id              int             not null auto_increment,
    cid             int             not null default 0              comment '客户id',
    uid             int             not null default 0              comment 'user id',
    objid           int             not null default 0              comment '单据id(积分来源)',
    oid             int             not null default 0              comment '销售单id，冗余数据',
    objtype         tinyint         not null default 0              comment '单据类型',
    unfreeze_point  int             not null default 0              comment '解冻积分',
    chg_point       int             not null default 0              comment '变更积分，增:正，减:负',
    vaild_point     int             not null default 0              comment '[总]可用积分',
    frozen_point    int             not null default 0              comment '[总]冻结积分',
    total_point     int             not null default 0              comment '[总]变更后的总积分',
    had_unfreeze    tinyint         not null default 0              comment '是否已经解冻',
    note            varchar(100)    not null default ''             comment '备注',
    suid            int             not null default 0              comment '执行人',
    status          tinyint         not null default 0              comment '状态',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX (cid),
    INDEX (uid),
    INDEX (oid),
    INDEX (ctime)
    
)ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1000;


/*-----------------------------------------------
 * 客户积分商品库存历史表
 *-----------------------------------------------*/
CREATE TABLE t_cpoint_stock_history (
    id              int             not null auto_increment,
    pid             int             not null default 0              comment '商品id',
    chg_num         int             not null default 0              comment '变更库存，增:正，减:负',
    num             int             not null default 0              comment '变更后库存',
    objid           int             not null default 0              comment '兑换对象id',
    objtype         tinyint         not null default 0              comment '兑换对象类型',
    suid            int             not null default 0              comment '操作人',
    status          tinyint         not null default 0              comment '状态: 0-正常, 1-删除',
    note            varchar(128)    not null default ''             comment '备注',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX (pid),
    INDEX (objid, objtype)
    
)ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1000;

/*-----------------------------------------------
 * 客户积分订单表
 *-----------------------------------------------*/
CREATE TABLE t_cpoint_order (
    oid             int             not null auto_increment,
    cid             int             not null default 0              comment '客户id',
    uid             int             not null default 0              comment 'user id',
    point           int             not null default 0              comment '兑换积分',
    contact_name    varchar(30)     not null default ''             comment '联系人',
    contact_phone   varchar(30)     not null default ''             comment '联系方式',
    city            varchar(100)    not null default ''             comment '省市',
    district        varchar(100)    not null default ''             comment '地区',
    area            varchar(100)    not null default ''             comment '地区',
    address         varchar(100)    not null default ''             comment '地址',
    express         tinyint         not null default 0              comment '快递公司',
    tracking_num    varchar(32)     not null default ''             comment '快递单号',
    freight         int             not null default 0              comment '运费 (单位:分)',
    note            varchar(150)    not null default ''             comment '备注',
    status          tinyint         not null default 0              comment '状态: 0-正常, 1-删除',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (oid),
    INDEX (cid),
    INDEX (uid),
    INDEX (tracking_num),
    INDEX (ctime)

)ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=5000;

/*-----------------------------------------------
 * 客户积分订单商品表
 *-----------------------------------------------*/
CREATE TABLE t_cpoint_order_product (
    oid             int             not null default 0              comment '订单id',
    cid             int             not null default 0              comment '客户id，冗余，查询使用',
    pid             int             not null default 0              comment '商品id',
    num             int             not null default 0              comment '数量',
    point           int             not null default 0              comment '兑换积分',
    price           int             not null default 0              comment '市场价',
    cost            int             not null default 0              comment '成本',
    status          tinyint         not null default 0              comment '状态',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (oid, pid),
    INDEX (cid),
    INDEX (ctime)

)ENGINE=InnoDB DEFAULT CHARSET=utf8;


