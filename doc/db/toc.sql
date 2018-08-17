/*-----------------------------------------------
 * 工长详情表
 *-----------------------------------------------*/
CREATE TABLE t_toc_forman (
  fid               int             not null auto_increment,
  cid               int             not null default 0      comment '关联cid',
  uid               INT             not null default 0      comment '关联uid',
  name              varchar(50)     not null default ''     comment '姓名',
  logo              varchar(200)    not null default ''     comment '头像',
  work_age          tinyint         not null default 0      comment '工龄',
  birthplace        varchar(100)    not null DEFAULT ''     comment '籍贯',
  attestation       varchar(200)    not null default ''     comment '认证信息',
  address           varchar(200)    not null default ''     comment '地址',
  intro             varchar(200)    not null default ''     comment '自我介绍',
  work_community    varchar(500)    not null default ''     comment '装修过的小区',
  workarea          varchar(100)    not null default ''     comment '接单范围',
  status            tinyint         not null default 0      comment '状态: 0-正常, 1-删除, 2-封禁',
  suid              INT             not null default 0      comment '添加人',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (fid),
  index (cid),
  index (uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * banner表
 *-----------------------------------------------*/
CREATE TABLE t_toc_banner (
  id                int             not null auto_increment,
  city_id           INT             not null default 0      comment '城市',
  title             varchar(100)    not null default ''     comment 'banner标题',
  imgurl            varchar(100)    not null default ''     comment 'banner图地址',
  url               varchar(300)    not null default ''     comment 'banner图链接',
  start_time        TIMESTAMP       not null default 0      comment '开始时间',
  end_time          TIMESTAMP       not null default 0      comment '结束时间',
  status            tinyint         not null default 0,
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 案例表
 *-----------------------------------------------*/
CREATE TABLE t_toc_case (
  id                int             not null auto_increment,
  fid               int             not null default 0      comment '工长id',
  house_style       INT             not null default 0      comment '风格',
  house_type        INT             not null default 0      comment '户型',
  house_space       INT             not null default 0      comment '空间',
  house_area        INT             not null default 0      comment '面积',
  cover             varchar(200)    not null default ''     comment '封面',
  title             varchar(100)    not null default ''     comment '简介',
  description       text                                    comment '富文本内容',
  suid              int             not null default 0      comment '录入人',
  index_sortby      INT             not null default 0      comment '首页显示权重',
  city_id           int             not null default 0      comment '城市',
  status            tinyint         not null default 0,
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  index (fid),
  index (house_style),
  index (house_type),
  index (house_space),
  index(index_sortby),
  index (house_area)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 装修百科表
 *-----------------------------------------------*/
CREATE TABLE t_toc_wiki (
  id                int             not null auto_increment,
  fid               INT             not null default 0      comment '工长id',
  title             varchar(100)    not null default ''     comment '简介',
  sub_title         varchar(300)    not null default ''     comment '副标题',
  cover             varchar(200)    not null default ''     comment '封面',
  design            tinyint         not null default 0      comment '设计',
  fit_step          tinyint         not null default 0      comment '装修阶段',
  main_material     tinyint         not null default 0      comment '主材',
  other_material    tinyint         not null default 0      comment '辅材',
  description       text,
  suid              int             not null default 0      comment '录入人',
  status            tinyint         not null default 0,
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  index (fid),
  index (design),
  index (fit_step),
  index (main_material),
  index (other_material)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*-----------------------------------------------
 * 预约列表
 *-----------------------------------------------*/
CREATE TABLE t_toc_appointment (
  id                int             not null auto_increment,
  case_id           INT             not null default 0      comment '案例id',
  fid               int             not null default 0      comment '对应工长id',
  uid               INT             not null default 0      comment '提交用户id',
  city              varchar(100)    not null default ''     comment '城市',
  district          varchar(100)    not null default ''     comment '地区',
  area              varchar(100)    not null default ''     comment '区域',
  name              varchar(50)     not null default ''     comment '提交者姓名',
  mobile            varchar(20)     not null default ''     comment '提交者手机号',
  house_style       tinyint         not null default 0      comment '装修风格',
  house_type        tinyint         not null default 0      comment '装修户型',
  house_area        tinyint         not null default 0      comment '房屋面积',
  budget            tinyint         not null default 0      comment '装修预算',
  fit_time          DATE            not null default 0      comment '装修时间',
  note              varchar(200)    not null default ''     comment '特殊要求',
  hc_note           text,
  saler_suid        int             not null default 0      comment '分配销售',
  step              tinyint         not null default 1      comment '状态',
  status            tinyint         not null default 0,
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  index (fid),
  index (uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;