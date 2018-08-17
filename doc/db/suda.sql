set names utf8;

/*-----------------------------------------------
 * 发票使用的商品列表
 *-----------------------------------------------*/

CREATE TABLE t_invoice_product (
    pid         int             not null auto_increment,
    title       varchar(128)    not null default ''         comment '商品名称',
    spec        VARCHAR(60)     NOT NULL DEFAULT ''         COMMENT '规格',
    unit        VARCHAR(10)     NOT NULL DEFAULT ''         COMMENT '单位',
    city_id     int             not null default 0          comment '城市id',
    cate1       tinyint         not null default 0          comment '一级分类',
    cost        int             not null default 0          comment '成本：分',
    price       int             not null default 0          comment '售价：分，(=0,用成本算默认值)',
    num         int             not null default 0          comment '库存/商品数量',
    occupy      int             not null default 0          comment '占用数量',
    status      tinyint         not null default 0          comment '状态',
    ctime       timestamp       not null default 0,
    mtime       timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (pid),
    UNIQUE KEY (title, city_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=2000;

/*-----------------------------------------------
 * 进项发票
 *-----------------------------------------------*/
CREATE TABLE t_input_invoice(
    id              int             not null auto_increment,
    supplier_id     int             not null default 0          comment '供应商ID',
    name            varchar(128)    not null default ''         comment '开票公司的名称',
    invoice_type    tinyint         not null default 1          comment '发票类型:1-增值税发票,2-普通发票',
    title           varchar(128)    not null default ''         comment '开票名称',
    amount          int             not null default 0          comment '发票金额',
    invoice_day     date            not null default 0          comment '实际开票日期',
    batch           varchar(60)     not null default ''         comment '批次',
    number          varchar(60)     not null default ''         comment '票号',
    city_id         int             not null default 0          comment '开票城市id',
    bill_ids        text            not null                    comment '格式：结算单id:1,采购单id:2',
    create_suid     int             not null default 0          comment '制单人',
    audit_suid      int             not null default 0          comment '完成人',
    step            tinyint         not null default 1          comment '发票阶段：1-待确认,2-处理中,3-处理完成',
    status          tinyint         not null default 0          comment '状态',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    
	PRIMARY KEY (id),
	INDEX (supplier_id),
    INDEX (title),
    INDEX (number),
    INDEX (invoice_day)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1000;

/*-----------------------------------------------
 * 进项发票商品
 *-----------------------------------------------*/
CREATE TABLE t_input_invoice_product (
	  invoice_id      int             not null default 0          comment '发票id',
	  pid             int             not null default 0          comment '财务商品id',
	  num             int             not null default 0          comment '采购数量',
	  amount          int             not null default 0          comment '商品总价',
	  tax_rate        int             not null default 0          comment '税率',
	  tax_amount      int             not null default 0          comment '税额',
    status          tinyint         not null default 0          comment '状态',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (invoice_id, pid),
	INDEX (pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `t_in_order` ADD `invoice_ids` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '发票id';
ALTER TABLE `t_stockin_statements` ADD `invoice_ids` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '发票id';

/*-----------------------------------------------
 * 销项发票
 *-----------------------------------------------*/
CREATE TABLE t_output_invoice(
    id              int             not null auto_increment,
    cid             int             not null default 0          comment '客户CID',
    real_amount     int             not null default 0          comment '订单实付金额',
    invoice_amount  int             not null default 0          comment '开票金额',
    contract_number varchar(60)     not null default ''         comment '合同编号',
    city_id         int             not null default 0          comment '开票城市id',
    invoice_type    tinyint         not null default 1          comment '发票类型:1-普通发票,2-专用发票',
    title           varchar(128)    not null default ''         comment '开票名称',
    pay_company     varchar(128)    not null default ''         comment '付款单位',
    bill_ids        text            not null default ''         comment '开票订单',
    service_type    tinyint         not null default 1          comment '服务费是否开票:1-不开票,2-开票',
    service_amount  int             not null default 0          comment '服务费金额',
    content         varchar(1200)   not null default ''         comment '备注',
    invoice_day     date            not null                    comment '实际开票日期',
    batch           varchar(60)     not null default ''         comment '批次',
    number          varchar(60)     not null default ''         comment '票号',

    create_suid     int             not null default 0          comment '申请人',
    sale_audit_suid      int        not null default 0          comment '销售审核人',
    finance_audit_suid   int        not null default 0          comment '财务确认人',
    finish_suid     int             not null default 0          comment '开票人',
    step            tinyint         not null default 1          comment '发票阶段：1-待确认,2-驳回,3-销售已审核,4-财务已确认,5-已开票',
    status          tinyint         not null default 0          comment '状态',
    sale_time       timestamp       not null default 0          comment '销售审核时间',
    finance_time    timestamp       not null default 0          comment '财务审核时间',
    finish_time     TIMESTAMP       not null default 0          comment '已开票时间',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX (cid),
    INDEX (title),
    INDEX (number),
    INDEX (invoice_day)

) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1000;

/*-----------------------------------------------
 * 销项发票商品
 *-----------------------------------------------*/
CREATE TABLE t_output_invoice_product (
    invoice_id      int             not null default 0          comment '发票id',
    pid             int             not null default 0          comment '财务商品id',
    cost            int             not null default 0          comment '商品成本（单位:分）',
    price           int             not null default 0          comment '商品单价（单位:分）',
    num             int             not null default 0          comment '采购数量',
    status          tinyint         not null default 0          comment '状态',
    ctime           timestamp       not null default 0,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (invoice_id, pid),
    INDEX (pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `t_order` ADD `invoice_id` int NOT NULL DEFAULT 0 COMMENT '销售发票id';
