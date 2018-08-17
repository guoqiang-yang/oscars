-- MySQL dump 10.13  Distrib 5.5.35, for linux2.6 (x86_64)
--
-- Host: rdsemb2evemb2ev.mysql.rds.aliyuncs.com    Database: eduoduo
-- ------------------------------------------------------
-- Server version	5.5.18.1-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `subarea`
--

DROP TABLE IF EXISTS `subarea`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subarea` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `sort_order` bigint(20) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `area` bigint(20) DEFAULT NULL,
  `deleted` bit(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_jcayl4kgtry6ld8gre7xq9vuu` (`area`),
  CONSTRAINT `FK_jcayl4kgtry6ld8gre7xq9vuu` FOREIGN KEY (`area`) REFERENCES `area` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1106 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subarea`
--

LOCK TABLES `subarea` WRITE;
/*!40000 ALTER TABLE `subarea` DISABLE KEYS */;
INSERT INTO `subarea` VALUES (101,'双井/国贸',NULL,'shuangjing',1,NULL),(102,'三里屯/团结湖',NULL,'sanlitun',1,NULL),(103,'四惠/十里堡',NULL,'sihui',1,NULL),(104,'望京',NULL,'wangjing',1,NULL),(105,'三元桥/太阳宫',NULL,'sanyuanqiao',1,NULL),(106,'朝阳公园',NULL,'chaoyanggongyuan',1,NULL),(107,'潘家园',NULL,'panjiayuan',1,NULL),(108,'欢乐谷',NULL,'huanlegu',1,NULL),(109,'常营',NULL,'changying',1,NULL),(110,'管庄/定福庄',NULL,'guanzhuang',1,NULL),(111,'亚运村',NULL,'yayuncun',1,NULL),(112,'安贞',NULL,'anzhen',1,NULL),(113,'青年路',NULL,'qingnianlu',1,NULL),(114,'北苑',NULL,'beiyuan',1,NULL),(115,'朝阳门',NULL,'chaoyangmen',1,NULL),(116,'十八里店',NULL,'shibalidian',1,NULL),(117,'奥林匹克公园',NULL,'aolinpikegongyuan',1,NULL),(201,'公主坟/万寿路',NULL,'gongzhufen',2,NULL),(202,'五棵松',NULL,'wukesong',2,NULL),(203,'北下关',NULL,'beixiaguan',2,NULL),(204,'中关村',NULL,'zhongguancun',2,NULL),(205,'五道口',NULL,'wudaokou',2,NULL),(206,'学院路/学清路',NULL,'xueyuanlu',2,NULL),(207,'北太平庄',NULL,'beitaipingzhuang',2,NULL),(208,'长春桥',NULL,'changchunqiao',2,NULL),(209,'航天桥',NULL,'hangtianqiao',2,NULL),(210,'魏公村',NULL,'weigongcun',2,NULL),(211,'紫竹桥',NULL,'zizhuqiao',2,NULL),(212,'清河',NULL,'qinghe',2,NULL),(213,'上地',NULL,'shangdi',2,NULL),(214,'北部新区',NULL,'beibuxinqu',2,NULL),(301,'东直门',NULL,'dongzhimen',3,NULL),(302,'安定门',NULL,'andingmen',3,NULL),(303,'天坛',NULL,'tiantan',3,NULL),(304,'崇文门',NULL,'chongwenmen',3,NULL),(401,'复兴门',NULL,'fuxingmen',4,NULL),(402,'西直门',NULL,'xizhimen',4,NULL),(403,'菜市口',NULL,'caishikou',4,NULL),(404,'广安门',NULL,'guanganmen',4,NULL),(501,'六里桥',NULL,'liuliqiao',5,NULL),(502,'刘家窑',NULL,'liujiayao',5,NULL),(503,'木樨园',NULL,'muxiyuan',5,NULL),(504,'宋家庄',NULL,'songjiazhuang',5,NULL),(505,'马家堡',NULL,'majiapu',5,NULL),(506,'玉泉营',NULL,'yuquanying',5,NULL),(507,'右安门',NULL,'youanmen',5,NULL),(508,'方庄',NULL,'fangzhuang',5,NULL),(509,'长辛店',NULL,'changxindian',5,NULL),(510,'花乡',NULL,'huaxiang',5,NULL),(511,'北大地',NULL,'beidadi',5,NULL),(512,'卢沟桥',NULL,'lugouqiao',5,NULL),(513,'青塔',NULL,'qingta',5,NULL),(601,'八里桥',NULL,'baliqiao',6,NULL),(602,'新华大街',NULL,'xinhuadajie',6,NULL),(603,'通胡大街',NULL,'tonghudajie',6,NULL),(604,'梨园',NULL,'liyuan',6,NULL),(605,'宋庄',NULL,'songzhuang',6,NULL),(606,'次渠',NULL,'ciqu',6,NULL),(607,'潞苑',NULL,'luyuan',6,NULL),(608,'通州北苑',NULL,'tongzhoubeiyuan',6,NULL),(609,'玉桥',NULL,'yuqiao',6,NULL),(610,'果园',NULL,'guoyuan',6,NULL),(701,'县城内',NULL,'xianchengnei',7,NULL),(702,'沙河',NULL,'shahe',7,NULL),(703,'回龙观',NULL,'huilongguan',7,NULL),(704,'天通苑',NULL,'tiantongyuan',7,NULL),(705,'北七家',NULL,'beiqijia',7,NULL),(801,'鲁谷',NULL,'lugu',8,NULL),(802,'八角',NULL,'bajiao',8,NULL),(803,'古城',NULL,'gucheng',8,NULL),(804,'杨庄',NULL,'yangzhuang',8,NULL),(901,'燕山',NULL,'yanshan',9,NULL),(902,'良乡',NULL,'liangxiang',9,NULL),(903,'窦店',NULL,'doudian',9,NULL),(904,'阎村',NULL,'yancun',9,NULL),(905,'长阳',NULL,'changyang',9,NULL),(906,'房山城关',NULL,'fangshanchengguan',9,NULL),(1001,'黄村',NULL,'huangcun',10,NULL),(1002,'西红门',NULL,'xihongmen',10,NULL),(1003,'旧宫',NULL,'jiugong',10,NULL),(1004,'亦庄',NULL,'yizhuang',10,NULL),(1005,'采育',NULL,'caiyu',10,NULL),(1006,'榆垡',NULL,'yufa',10,NULL),(1101,'主城区内',NULL,'zhuchengqunei',11,NULL),(1102,'天竺',NULL,'tianzhu',11,NULL),(1103,'后沙峪',NULL,'houshayu',11,NULL),(1104,'李桥',NULL,'liqiao',11,NULL),(1105,'赵全营',NULL,'zhaoquanying',11,NULL);
/*!40000 ALTER TABLE `subarea` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-07-03 11:45:19
