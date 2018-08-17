alter table t_order modify delivery_date datetime not null default 0;

alter table t_order add service int(11) not null default 0;

alter table t_order add customer_note varchar(4096) not null default '';

alter table t_customer add sales_suid2 int not null default 0;

update t_construction_site set district = 0, area = 0;

alter table t_construction_site add city SMALLINT not null default 0;

alter table t_order add floor_num TINYINT not null default 0;

alter table t_customer add payment_days INT NOT NULL DEFAULT 0;
alter table t_customer add payment_due_date DATE NOT NULL default 0;
alter table t_customer add remind_count INT NOT NULL DEFAULT 0;
alter table t_customer add visit_due_date DATE NOT NULL DEFAULT 0;

alter table t_customer_tracking add type tinyint not null default 1;

alter table t_order add has_print tinyint not null default 0;
update t_order set has_print = 1;

alter table t_driver add car_code varchar(20) not null default '-1';
alter table t_driver add can_carry tinyint(1)not null default 0;
alter table t_driver add score int not null default 60;
alter table t_driver add note varchar(500) not null default '';

alter table t_customer add last_remind_suid INT not null default 0;
alter table t_customer add last_remind_date datetime not null default 0;

alter table t_customer add bid int not null default 0;
alter table t_order add bid INT not null default 0;

alter table t_customer add is_auto_save tinyint not null default 0;

alter table t_sku add mids varchar(500) not null default '';
update t_sku set mids=mid;

alter table t_model add sortby INT not null default 0;
alter table t_cate_brand add sortby INT not null default 0;

alter table t_order add city SMALLINT not null default 0;
alter table t_order add address varchar(256) not null default '';

alter table t_coupon add cate tinyint not null default 1;

alter table t_order add sure_time timestamp not null default 0;
alter table t_order add ship_time timestamp not null default 0;
alter table t_order add back_time timestamp not null default 0;
alter table t_order add pay_time timestamp not null default 0;

/*修改sku，product的逻辑*/
alter table t_product add sortby INT not null default 0;
alter table t_product add carrier_fee int not null default 0;
alter table t_product add carrier_fee_ele int not null default 0;
alter table t_order_product add sid INT not null default 0;
alter table t_in_order_product change pid sid int not null default 0;
alter table t_in_order_product drop primary key, add PRIMARY KEY (oid, sid);
alter table t_stock_in_product change pid sid int not null default 0;
alter table t_stock_in_product drop PRIMARY KEY, ADD PRIMARY KEY (id, sid, srid);
alter table t_order add city_id INT not null default 101;
alter table t_order_product add city_id INT not null default 101;
alter table t_product add city_id INT not null DEFAULT 101;
alter table t_staff_user add city_id INT not null DEFAULT 0;
alter table t_driver add city_id INT not null DEFAULT 101;
alter table t_carrier add city_id INT not null DEFAULT 101;
alter table t_refund add city_id INT not null default 101;
alter table t_order modify wid int not null default 0;
alter table t_order_product modify wid int not null default 0;
alter table t_in_order modify wid int not null default 0;
alter table t_stock_in modify wid int not null default 0;
alter table t_stock_in_refund modify wid int not null default 0;
alter table t_stock_shift modify src_wid int not null default 0;
alter table t_stock_shift modify des_wid int not null default 0;
alter table t_refund modify wid int not null default 0;
alter table t_money_in_history modify wid int not null default 0;
alter table t_coopworker_money_out_history modify wid int not null default 0;
alter table t_coopworker_order modify wid int not null default 0;
alter table t_stock modify wid int not null default 0;
alter table t_stock_history modify wid int not null default 0;
alter table t_supplier modify wid int not null default 0;
alter table t_staff_user modify wid int not null default 0;
alter table t_temporary_purchase modify wid int not null default 0;
alter table t_temporary_had_purchased modify wid int not null default 0;
alter table t_product add UNIQUE KEY (sid, city_id);
alter table t_product drop key sid;
update t_product as p set carrier_fee = (select carrier_fee from t_sku as s where s.sid = p.sid);
update t_product as p set carrier_fee_ele = (select carrier_fee_ele from t_sku as s where s.sid = p.sid);
update t_product as p set sortby = (select sortby from t_sku as s where s.sid = p.sid);
update t_order_product set sid = pid;

alter table t_customer add refund_amount INT not null default 0;

alter table t_order add has_duty tinyint not null default 2;
alter table t_customer add has_duty tinyint not null default 2;

alter table t_order_privilege add cid int not null default 0;
update t_order_privilege as p set cid = (select cid from t_order where oid = p.oid);

alter table t_customer_limit add ext text not null after val;

alter table t_lottery_record add has_send tinyint not null default 2 after prize;
alter table t_lottery_record add oid INT not null default 0 after has_send;

alter table t_sku add qrcode_type tinyint not null default 1;

alter table t_order_product add picked INT not null default 0;

alter table t_product add ori_price INT not null default 0 after price;

alter table t_refund add refund_carry_fee  INT not null default 0;
alter table t_refund add refund_freight INT not null default 0;
alter table t_refund add verify_suid INT not null default 0;
alter table t_refund add refund_privilege INT  not null default 0;
alter table t_refund add refund_to_amount INT not null default 0;
alter table t_refund add refund_coupon varchar(200) not null default '';
alter table t_refund add reason int not null default 0;

alter table t_driver add refuse_num int not null default 0;
alter table t_driver_queue add alloc_time TIMESTAMP not null default 0;
alter table t_driver_queue add fee INT not null default 0;
alter table t_coopworker_order add alloc_time timestamp not null default 0;

alter table t_order add delivery_date_end datetime not null default 0 after delivery_date;

alter table t_order add picked_time TIMESTAMP not null default 0;

alter table t_order add customer_payment_type INT not null default 0 comment '客户选择的支付方式，在线支付和货到付款';
alter table t_coopworker_order add finish_time timestamp not null default 0 comment '回单时间';
update t_coopworker_order as co set finish_time = (select back_time from t_order where oid = co.oid);

ALTER TABLE `t_order_privilege` ADD `activity_id` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '促销活动id' AFTER `oid`, ADD INDEX `activity_id` (`activity_id`);
ALTER TABLE `t_coupon` ADD `tid` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '优惠券类型表' AFTER `oid`, ADD INDEX `coupon_tid` (`tid`);
ALTER TABLE `t_coupon` ADD `aid` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '优惠活动id' AFTER `tid`, ADD INDEX `coupon_aid` (`aid`);
ALTER TABLE `t_coupon` ADD `start_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '起始时间' AFTER `status`;
ALTER TABLE `t_coupon` ADD `from_oid` INT NOT NULL DEFAULT '0' COMMENT '送券订单id' AFTER `aid`, ADD INDEX `order_oid` (`from_oid`);

ALTER TABLE `t_after_sale` ADD `typeid` TINYINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '问题类型子级' AFTER `type`, ADD INDEX `typeid` (`typeid`);

alter table t_order_product add picked_time TIMESTAMP not null default 0 comment '拣货时间';

alter table t_sku add length INT not null default 0 comment '长，单位毫米';
alter table t_sku add width INT not null default 0 comment '宽，单位毫米';
alter table t_sku add height INT not null default 0 comment '高，单位毫米';
alter table t_sku add weight INT not null default 0 comment '重量，单位克';

alter table t_staff_user add roles varchar(200) not null default '' comment '角色列表';
alter table t_staff_user add department INT not null default 0 comment '部门';

alter table t_role add rkey              varchar(100)   not null default '' comment '唯一标识符';
alter table t_role add UNIQUE KEY (rkey);

ALTER TABLE t_refund ADD COLUMN carry_fee INT NOT NULL DEFAULT 0 comment '退货搬运费';
ALTER TABLE t_refund ADD COLUMN freight INT NOT NULL DEFAULT 0 comment '退货运费';

ALTER TABLE t_after_sale ADD COLUMN pic_ids text NOT null DEFAULT '' COMMENT '图片id列表, 以英文逗号分隔';

alter table t_refund add audit_time timestamp not null default 0 comment '退款单审核通过时间';
alter table t_refund add to_finance_time timestamp not null default 0 comment '提交财务时间';


alter table t_statistics_base_per_month add refund_num INT not null default 0 comment '退款单数';
alter table t_statistics_base_per_month add order_refund INT not null default 0 comment '订单退款金额（t_order里的refund字段）';

alter table t_statistics_base_per_day add refund_num INT not null default 0 comment '退款单数';
alter table t_statistics_base_per_day add order_refund INT not null default 0 comment '订单退款金额（t_order里的refund字段）';

ALTER TABLE t_stock_in ADD COLUMN statement_id  INT NOT NULL DEFAULT 0 COMMENT '结算单id' AFTER wid;
ALTER TABLE t_stock_in ADD INDEX(`statement_id`);

alter table t_statistics_buyer_per_month add new_rebuy_cids TEXT not null comment '新用户-且复购';

ALTER TABLE `t_in_order` ADD `invoice_ids` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '发票id';
ALTER TABLE `t_stockin_statements` ADD `invoice_ids` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '发票id';

alter table t_customer add nick_name         varchar(50)     not null default ''     comment '称呼';
alter table t_customer add age               int             not null default 0      comment '年龄';
alter table t_customer add sex               tinyint         not null default 1      comment '0男1女';
alter table t_customer add birth_place       varchar(50)     not null default ''     comment '籍贯';
alter table t_customer add work_age          int             not null default 0      comment '工龄';
alter table t_customer add interest          varchar(100)    not null default ''     comment '兴趣爱好';
alter table t_customer add work_area         varchar(100)    not null default ''     comment '工作区域';
alter table t_customer add email             varchar(50)    not null default ''     comment '邮箱';
alter table t_customer add character_tag         varchar(100)    not null default ''     comment '性格标签';
alter table t_customer add birthday          varchar(20)     not null default ''     comment '生日';

alter table t_staff_user add regid             varchar(128)    not null default ''     COMMENT '小米推送id';

ALTER TABLE `t_product` ADD `frequency` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '销售频次' AFTER `buy_type`, ADD INDEX `frequency` (`frequency`);
UPDATE t_sku SET bid=208 WHERE bid=0;


alter table t_order_comment add comment_all tinyint not null default 0 comment '整体评价';
alter table t_order_comment add comment_delivery tinyint not null default 0 comment '配送评价';
alter table t_order_comment add comment_carry   tinyint not null default 0 comment '搬运评价';

alter table t_staff_user add ce_agent_num varchar(50) not null default '' comment '呼叫中心坐席工号';
alter table t_staff_user add ce_agent_pass varchar(100) not null default '' comment '呼叫中心坐席密码';
alter table t_staff_user add ce_agent_phone varchar(50) not null default '' comment '呼叫中心坐席分机号';