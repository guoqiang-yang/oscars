
set names utf8;

insert into t_brand (bid, name) values
(101, '顺其'),
(102, '力达'),
(103, '北字'),
(104, '巨泉'),
(105, '巨安'),
(106, '金德'),
(107, '日泰'),
(108, '九牧'),
(109, '特陶'),
(110, '京源'),
(111, '北京昆仑'),
(112, '昆仑兴业'),
(113, '慧远'),
(114, '天津小猫'),
(115, '联塑');


insert into t_cate_brand (bid, cate2, cate3) values
(106, 101, 10101),
(115, 101, 10101),
(101, 102, 10201),
(102, 103, 10301),
(103, 103, 10301),
(104, 104, 0),
(105, 104, 0),
(106, 104, 0),
(107, 104, 0),
(108, 104, 0),
(109, 104, 0),
(110, 104, 0),
(111, 201, 0),
(112, 201, 0),
(113, 201, 0),
(114, 201, 0);


insert into t_model (mid, name, cate2, cate3) values
(101, '20', 101, 10101),
(102, '25', 101, 10101);


insert into t_brand_model (bid, mid) values
(106, 101),
(106, 102);


insert into t_staff_user (suid, name, type) values
(1001, '吴会明', 1),
(1002, '吴小吴', 1),
(1003, '胡庆', 1),
(1004, '王申', 1);


insert into t_sku (sid, title, cate1, cate2, cate3, bid, mid, unit, package, detail) values
(10001, '联塑PPR等三通20#', 1, 101, 10101, 106, 101, '个', '', ''),
(10002, '联塑PPR等三通25#', 1, 101, 10101, 106, 102, '个', '', '');


insert into t_product (sid, pid, price, detail) values
(10001, 10001, 80, ''),
(10002, 10002, 80, '');


insert into t_construction_site( id, cid, district, area, address, note, last_order_date, order_num) values
(1, 6001, 5, 506, '则西路32号院12#806', '', '2015-06-25', 2);

insert into t_order (oid, cid, contact_name, contact_phone, delivery_date, district, area, construction, product_num, price, freight, status, step) values
(6001, 6001, '小刘', '13888888888', '2015-06-25', 5, 506,  1, 2, 1302, 0, 0, 1),
(6002, 6001, '小刘', '13888888888', '2015-06-25', 5, 506,  1, 2, 635, 0, 0, 1);


/*
|  1 | 朝阳      |       NULL | chaoyang    |    1 | NULL    |
|  2 | 海淀      |       NULL | haidian     |    1 | NULL    |
|  3 | 东城      |       NULL | dongcheng   |    1 | NULL    |
|  4 | 西城      |       NULL | xicheng     |    1 | NULL    |
|  5 | 丰台      |       NULL | fengtai     |    1 | NULL    |
|  6 | 通州      |       NULL | tongzhou    |    1 | NULL    |
|  7 | 昌平      |       NULL | changping   |    1 | NULL    |
|  8 | 石景山    |       NULL | shijingshan |    1 | NULL    |
|  9 | 房山      |       NULL | fangshan    |    1 | NULL    |
| 10 | 大兴      |       NULL | daxing      |    1 | NULL    |
| 11 | 顺义      |       NULL | shunyi      |    1 | NULL    |
*/


