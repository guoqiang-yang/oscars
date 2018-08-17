/*------------------------------------------------
 *  贷款申请表
 *------------------------------------------------*/
CREATE TABLE t_finance_apply(
    id              int             not null AUTO_INCREMENT,
    cid             int             not null default 0      comment '客户id',
    type            tinyint         not null default 0      comment '客户类型（公司/个人）',
    real_name       varchar(100)    not null default ''     comment '姓名',
    mobile          varchar(20)     not null default ''     comment '手机号',
    id_card_no      varchar(50)     not null default ''     comment '身份证号码',
    bank_card       varchar(50)     not null default ''     comment '银行卡号',
    company         varchar(200)    not null default ''     comment '公司名称',
    social_code     varchar(100)    not null default ''     comment '统一社会信用代码',
    legal_person_name varchar(100)  not null default ''     comment '法人姓名',
    tp_due_date     int             not null default 0      comment '第三方授信期限',
    tp_total_amount INT             not null default 0      comment '第三方授信额度',
    tp_deal_time    TIMESTAMP       not null default 0      comment '第三方授信时间',
    hc_due_date     INT             not null default 0      comment '好材授信期限',
    hc_total_amount INT             not null DEFAULT 0      comment '好材授信额度',
    hc_deal_time    TIMESTAMP       not null default 0      comment '好材通过时间',
    hc_crdit_time   TIMESTAMP       not null default 0      comment '好材授信时间',
    suid            INT             not null default 0      comment '操作人',
    step            tinyint         not null default 1      comment '步骤（申请，通过，拒绝等）',
    status          tinyint         not null default 0      comment '状态: 0-正常, 1-删除, 2-取消',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    primary key (id),
    index(cid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE t_finance_customer_account(
    id                  int       not null AUTO_INCREMENT,
    cid                 INT       not null default 0      comment 'cid',
    total_amount        INT       not null default 0      comment '总额度（单位分）',
    available_amount    INT       not null default 0      comment '可用额度（单位分）',
    trade_password      varchar(100)  not null default '' comment '交易密码',
    status              tinyint   not null default 0,
    ctime               timestamp       not null default 0,
    mtime               timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    unique (cid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/**----------------------------------------------
 *  金融账户流水表
 ----------------------------------------------*/
CREATE TABLE t_finance_amount_history(
    id              int             not null AUTO_INCREMENT,
    cid             int             not null default 0          comment '客户id',
    uid             int             not null default 0          comment '用户id',
    objid           int             not null default 0          comment '单据id: 订单/退款单(等)id',
    objtype         int             not null default 0          comment '单据类型',
    oid             int             not null default 0          comment '订单id',
    price           int             not null default 0          comment '支付金额（单位：分）',
    tmp_price       int             not null default 0          comment '临时额度支付金额（单位：分）',
    self_price      int             not null default 0          comment '存款消费金额',
    amount          int             not null default 0          comment '当前共用金额（单位：分）',
    sync_time       timestamp       not null default 0          comment '同步金融公司时间',
    is_paid         tinyint         not null default 0          comment '是否还款',
    paid_time       timestamp       not null default 0          comment '还款时间',
    payment_type    tinyint         not null default 0          comment '支付方式',
    suid            int             not null default 0          comment '执行人id',
    note            text            not null                    comment '备注',
    status          tinyint         not null default 0          comment '状态: 0-正常, 1-删除, 2-取消',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    primary key (id),
    index(cid),
    index(objid),
    index(oid),
    index(ctime)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**----------------------------------------------
 *  贴息表
 ----------------------------------------------*/
CREATE TABLE t_finance_accrual_history(
    id              int             not null AUTO_INCREMENT,
    cid             int             not null default 0          comment '客户id',
    uid             int             not null default 0          comment '用户id',
    oid             int             not null default 0          comment '订单id',
    accrual         int             not null default 0          comment '利息（单位：分）',
    price           int             not null default 0          comment '消费金额（单位：分）',
    consume_date    TIMESTAMP       not null default 0          comment '消费时间',
    `day`           varchar(20)     not null default ''         comment '日期',
    status          tinyint         not null default 0          comment '状态: 0-正常, 1-删除, 2-取消',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    primary key (id),
    index(cid),
    index(oid),
    index(`day`),
    UNIQUE KEY (oid, `day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;