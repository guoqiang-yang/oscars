set names utf8;

/**
一、索引页
1 水  http://wap.koudaitong.com/v2/showcase/tag?alias=1el68umli
2 电  http://wap.koudaitong.com/v2/showcase/tag?alias=10ms3740d
3 木  http://wap.koudaitong.com/v2/showcase/tag?alias=etyqiwnd
4 瓦  http://wap.koudaitong.com/v2/showcase/tag?alias=1cjkxmx7i
5 油  http://wap.koudaitong.com/v2/showcase/tag?alias=y7qvukr5
6 漆  http://wap.koudaitong.com/v2/showcase/tag?alias=187mgowv
7 劳保  http://wap.koudaitong.com/v2/showcase/tag?alias=dhqtxwl2
8 工具  http://wap.koudaitong.com/v2/showcase/tag?alias=v7zh51if


二、图片
1 目录
/work/haocai/pic/youzan/

2 格式
{pid}_small.jpg
{pid}_big_0.jpg
...
{pid}_big_n.jpg

**/



CREATE TABLE t_youzan_product (
  pid               int             not null auto_increment,
  title             varchar(224)    not null default ''     comment '标题',
  refer             varchar(224)    not null default ''     comment 'refer url',
  url               varchar(224)    not null default ''     comment '页面url',
  small_pic         varchar(224)    not null default ''     comment '缩略图url',
  cate1             tinyint         not null default 0      comment '一级分类',
  price             int             not null default 0      comment '价格, 单位:分',
  detail            text            not null                comment '商品描述',
  status            tinyint         not null default 0      comment '状态: 0-未抓详情 1-已抓详情',
  ctime             timestamp       not null default 0,
  mtime             timestamp       not null default current_timestamp,
  PRIMARY KEY (pid),
  index (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1;

