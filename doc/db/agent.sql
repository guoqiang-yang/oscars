
-- 存放WAT-经销商相关的数据表

/*-----------------------------------------------
 * 经销商信息表
 *-----------------------------------------------*/
CREATE TABLE t_agent (
    aid                 int                 not null auto_increment,
    wid                 int                 not null default 0          comment '关联仓库id',
    name                varchar(40)         not null default ''         comment '名称',
    mobile              char(11)            not null default ''         comment '手机',
    phone               varchar(32)         not null default ''         comment '固定电话，多个用逗号分隔',
    account_balance     int                 not null default 0          comment '账户余额，单位：分',
    base_salary         int                 not null default 0          comment '固定薪资，补充费用的基数，单位：分',
    days_of_monthly     tinyint             not null default 0          comment '每月工作天数',           

    status              tinyint             not null default 0          comment '状态',
    ctime               timestamp           not null default 0,
    mtime               timestamp           not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (aid),
    UNIQUE (mobile)

)ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=6000;

/*-----------------------------------------------
 * 经销商每日账单 - 每日销售统计
 *-----------------------------------------------*/
CREATE TABLE t_agent_bill_day (
    bid                 int                 not null auto_increment,
    aid                 int                 not null default 0          comment '经销商id',
    `day`               date                not null default 0          comment '日期',
    wid                 int                 not null default 0          comment '仓库',
    price               int                 not null default 0          comment '总金额 单位:分',
    order_price         int                 not null default 0          comment '订单金额 单位:分',
    refund_price        int                 not null default 0          comment '退单金额 单位:分',
    step                tinyint             not null default 0          comment '流转状态',
    
    ctime               timestamp           not null default 0,
    mtime               timestamp           not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (bid),
    UNIQUE (`day`, wid),
    INDEX (aid)

)ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1000;

/*-----------------------------------------------
 * 经销商每日账单与HC订单/退单映射表
 *-----------------------------------------------*/
CREATE TABLE t_agent_bill_2_order (
    bid                 int             not null default 0              comment '经销商账单id',
    objid               int             not null default 0              comment 'HC单据id',
    objtype             tinyint         not null default 0              comment 'HC单据类型',
    pay_day             date            not null default 0              comment '支付日期',
    distance            int             not null default 0              comment '小区到仓库距离',
    rule                varchar(32)     not null default ''             comment '返点规则',
    
    status              tinyint         not null default 0              comment '状态',
    ctime               timestamp       not null default 0,
    mtime               timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE (bid, objid, objtype),
    INDEX (objid),
    INDEX (pay_day)

)ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*-----------------------------------------------
 * 经销商每月账单 - 返点
 *-----------------------------------------------*/
CREATE TABLE t_agent_bill_cashback (
    id              int             not null auto_increment,
    aid             int             not null default 0                  comment '经销商id',
    month_flag      char(12)        not null default ''                 comment 'YYYYmmddmmdd',
    begin_day       date            not null default 0                  comment '开始时间：日',
    end_day         date            not null default 0                  comment '结算时间：日',
    wid             int             not null default 0                  comment '仓库',
    type            tinyint         not null default 0                  comment '返现类型：订单，退单，其他补贴等',
    price           int             not null default 0                  comment '金额 单位：元',
    adjust          int             not null default 0                  comment '调整金额',
    rule            varchar(200)    not null default ''                 comment '返点规则',
    note            text            not null                            comment '备注',
    step            tinyint         not null default 0                  comment '流转状态',
    status          tinyint         not null default 0                  comment '状态',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE (month_flag, wid, type),
    INDEX (begin_day, end_day)

)ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1000;

/*-----------------------------------------------
 * 经销商财务流水
 *-----------------------------------------------*/
CREATE TABLE t_agent_amount_history (
    id              int             not null auto_increment,
    aid             int             not null default 0              comment '经销商id',
    objid           int             not null default 0              comment '单据id',
    objtype         tinyint         not null default 0              comment '单据类型',
    price           int             not null default 0              comment '金额 单位：分',
    amount          int             not null default 0              comment '账户余额（单位：分）',
    suid            int             not null default 0              comment '执行人id',
    payment_type    tinyint         not null default 0              comment '付款方式',
    note            text            not null                        comment '备注',
    status          tinyint         not null default 0              comment '状态',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX (aid),
    INDEX (objid)
    
)ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1000;
