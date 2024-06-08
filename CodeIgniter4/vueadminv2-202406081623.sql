-- MySQL dump 10.13  Distrib 8.3.0, for macos13.6 (x86_64)
--
-- Host:     Database: vueadminv2
-- ------------------------------------------------------
-- Server version	8.3.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `adm_audit_role`
--

DROP TABLE IF EXISTS `adm_audit_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adm_audit_role` (
  `position_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '职位编码:GM(总经理), DGM(副总经理), DM(部门经理),STAFF(普通员工)',
  `position_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '职位名称',
  `user_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '用户ID',
  `dept_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '部门ID，一个用户ID可以分管多个部门'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='审批角色表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adm_audit_role`
--

LOCK TABLES `adm_audit_role` WRITE;
/*!40000 ALTER TABLE `adm_audit_role` DISABLE KEYS */;
INSERT INTO `adm_audit_role` VALUES ('DGM','副总经理','2','4'),('DM','部门经理','3','4'),('DGM','副总经理','2','5');
/*!40000 ALTER TABLE `adm_audit_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adm_leave_form`
--

DROP TABLE IF EXISTS `adm_leave_form`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adm_leave_form` (
  `form_id` bigint NOT NULL AUTO_INCREMENT COMMENT '请假单编号',
  `employee_id` bigint NOT NULL COMMENT '员工编号：即提交这个请假申请的员工的编号',
  `form_type` int NOT NULL COMMENT '请假类型：1-公休假 2-病假 3-婚假 4-产假 5-事假',
  `start_time` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT '请假起始时间',
  `end_time` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT '请假结束时间',
  `reason` varchar(128) COLLATE utf8mb4_general_ci NOT NULL COMMENT '请假原因',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '当前数据的创建时间',
  `state` varchar(32) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'processing-正在审批 approved-审批通过 refused-审批被驳回',
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='请假流程表单';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adm_leave_form`
--

LOCK TABLES `adm_leave_form` WRITE;
/*!40000 ALTER TABLE `adm_leave_form` DISABLE KEYS */;
INSERT INTO `adm_leave_form` VALUES (1,4,1,'2024-06-10','2024-06-14','我要去旅行','2024-06-08 15:31:45','processing');
/*!40000 ALTER TABLE `adm_leave_form` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adm_notice`
--

DROP TABLE IF EXISTS `adm_notice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adm_notice` (
  `notice_id` bigint NOT NULL AUTO_INCREMENT,
  `receiver_id` bigint NOT NULL COMMENT '消息接收者id；表示这条消息是要发给谁的',
  `content` varchar(255) COLLATE utf8mb4_general_ci NOT NULL COMMENT '消息内容',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notice_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='消息通知表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adm_notice`
--

LOCK TABLES `adm_notice` WRITE;
/*!40000 ALTER TABLE `adm_notice` DISABLE KEYS */;
INSERT INTO `adm_notice` VALUES (1,3,'lisi员工已发起请假申请，请您审批。','2024-06-08 15:31:45');
/*!40000 ALTER TABLE `adm_notice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adm_process_flow`
--

DROP TABLE IF EXISTS `adm_process_flow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adm_process_flow` (
  `process_id` bigint NOT NULL AUTO_INCREMENT COMMENT '处理任务编号',
  `form_id` bigint NOT NULL COMMENT '表单编号',
  `operator_id` bigint NOT NULL COMMENT '经办人编号',
  `action` varchar(32) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'apply-申请 audit-审批',
  `result` varchar(32) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'approved-同意 refused-驳回',
  `reason` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '审批意见;说明为什么同意，或者为什么驳回',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '这条记录的创建时间',
  `audit_time` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '审批时间',
  `order_no` int NOT NULL COMMENT '任务的序号',
  `state` varchar(32) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'ready-准备 process-正在处理 complete-处理完成 cancel-取消',
  `is_last` int NOT NULL COMMENT '是否是整个工作流程的最后一个节点; 0-否1-是',
  PRIMARY KEY (`process_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='审批任务流程表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adm_process_flow`
--

LOCK TABLES `adm_process_flow` WRITE;
/*!40000 ALTER TABLE `adm_process_flow` DISABLE KEYS */;
INSERT INTO `adm_process_flow` VALUES (1,1,4,'apply','','','2024-06-08 15:31:45','',1,'complete',0),(2,1,3,'audit','','','2024-06-08 15:31:45','',2,'process',0),(3,0,2,'audit','','','2024-06-08 15:31:45','',3,'process',1);
/*!40000 ALTER TABLE `adm_process_flow` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `article`
--

DROP TABLE IF EXISTS `article`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `article` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '文章id',
  `title` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `author` varchar(32) NOT NULL,
  `pageviews` varchar(512) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `display_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `article`
--

LOCK TABLES `article` WRITE;
/*!40000 ALTER TABLE `article` DISABLE KEYS */;
INSERT INTO `article` VALUES (1,'hello','go','288','2020-04-04 23:14:09'),(2,'wordl','qq','8000','2020-04-04 23:14:25');
/*!40000 ALTER TABLE `article` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `keys`
--

DROP TABLE IF EXISTS `keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `keys` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `key` varchar(40) NOT NULL,
  `level` int NOT NULL,
  `ignore_limits` tinyint(1) NOT NULL DEFAULT '0',
  `is_private_key` tinyint(1) NOT NULL DEFAULT '0',
  `ip_addresses` text,
  `date_created` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `keys`
--

LOCK TABLES `keys` WRITE;
/*!40000 ALTER TABLE `keys` DISABLE KEYS */;
INSERT INTO `keys` VALUES (1,0,'oocwo8cs88g4c8w8c08ow00ss844cc4osko0s0ks',10,1,0,NULL,1551173554),(2,0,'00kgsog84kooc44kgwkwccow48kggc48s4gcwwcg',0,1,0,NULL,1551173554);
/*!40000 ALTER TABLE `keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uri` varchar(255) NOT NULL,
  `method` varchar(6) NOT NULL,
  `params` text,
  `api_key` varchar(40) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `time` int NOT NULL,
  `rtime` float DEFAULT NULL,
  `authorized` varchar(1) NOT NULL,
  `response_code` smallint DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_dept`
--

DROP TABLE IF EXISTS `sys_dept`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_dept` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pid` int DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL COMMENT '机构名称',
  `aliasname` varchar(255) DEFAULT NULL,
  `listorder` int DEFAULT '99',
  `status` tinyint DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_dept`
--

LOCK TABLES `sys_dept` WRITE;
/*!40000 ALTER TABLE `sys_dept` DISABLE KEYS */;
INSERT INTO `sys_dept` VALUES (1,0,'总公司','',100,1),(2,1,'甘肃分公司','',99,1),(3,2,'敦煌分公司','',99,1),(4,3,'研发部门','',99,1),(5,3,'测试部门','',100,1);
/*!40000 ALTER TABLE `sys_dept` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_menu`
--

DROP TABLE IF EXISTS `sys_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_menu` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pid` int DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `path` varchar(255) NOT NULL,
  `component` varchar(255) DEFAULT NULL,
  `type` tinyint DEFAULT NULL COMMENT '0:目录，1:菜单, 3:功能/按钮/操作',
  `title` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `redirect` varchar(255) DEFAULT '' COMMENT 'redirect: noredirect           if `redirect:noredirect` will no redirect in the breadcrumb',
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `condition` varchar(255) DEFAULT '' COMMENT '规则表达式，为空表示存在就验证，不为空表示按照条件验证',
  `listorder` int DEFAULT NULL,
  `create_time` int DEFAULT NULL,
  `update_time` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_menu`
--

LOCK TABLES `sys_menu` WRITE;
/*!40000 ALTER TABLE `sys_menu` DISABLE KEYS */;
INSERT INTO `sys_menu` VALUES (1,0,'Sys','/sys','Layout',0,'系统管理','sysseting','/sys/menu',0,1,'',99,NULL,NULL),(2,1,'SysMenu','/sys/menu','sys/menu/index',1,'菜单管理','menu1','',0,1,'',100,NULL,NULL),(3,1,'SysRole','/sys/role','sys/role/index',1,'角色管理','role','',0,1,'',110,NULL,NULL),(4,1,'SysUser','/sys/user','sys/user/index',1,'用户管理','user','',0,1,'',105,NULL,NULL),(5,0,'Flow','/flow','Layout',0,'审批流程','cascader','/flow/leave',0,1,'',100,NULL,NULL),(6,2,'','/sys/menu/post','',2,'添加','','',0,1,'',90,NULL,NULL),(7,2,'','/sys/menu/put','',2,'编辑','','',0,1,'',95,NULL,NULL),(8,2,'','/sys/menu/delete','',2,'删除','','',0,1,'',99,NULL,NULL),(9,2,'','/sys/menu/get','',2,'查看','','',0,1,'',80,NULL,NULL),(10,5,'FlowLeave','/flow/leave','flow/leave/index',1,'请假申请','slider','',0,1,'',99,NULL,NULL),(11,5,'FlowLeaveAudit','/flow/leaveaudit','/flow/leaveaudit/index',1,'请假审批','unocss','',0,1,'',100,NULL,NULL),(12,1,'SysIcon','/sys/icon','svg-icons/index',1,'图标管理','icon','',0,1,'',120,NULL,NULL),(13,3,'','/sys/role/get','',2,'查看','','',0,1,'',90,NULL,NULL),(14,3,'','/sys/role/post','',2,'添加','','',0,1,'',91,NULL,NULL),(15,3,'','/sys/role/put','',2,'编辑','','',0,1,'',92,NULL,NULL),(16,3,'','/sys/role/delete','',2,'删除','','',0,1,'',101,NULL,NULL),(17,4,'','/sys/user/get','',2,'查看','','',0,1,'',96,NULL,NULL),(18,4,'','/sys/user/post','',2,'添加','','',0,1,'',97,NULL,NULL),(19,4,'','/sys/user/put','',2,'编辑','','',0,1,'',99,NULL,NULL),(20,4,'','/sys/user/delete','',2,'删除','','',0,1,'',100,NULL,NULL),(21,3,'','/sys/role/saveroleperm/post','',2,'角色授权','','',0,1,'',120,NULL,NULL),(23,1,'SysDept','/sys/dept','sys/dept/index',1,'部门管理','dept2','',0,1,'',115,NULL,NULL),(24,23,'','/sys/dept/get','',2,'查看','','',0,1,'',99,NULL,NULL),(25,23,'','/sys/dept/post','',2,'添加','','',0,1,'',100,NULL,NULL),(26,23,'','/sys/dept/put','',2,'编辑','','',0,1,'',102,NULL,NULL),(27,23,'','/sys/dept/delete','',2,'删除','','',0,1,'',104,NULL,NULL),(28,1,'SysLog','/sys/log','sys/log/index',1,'系统日志','date','',0,1,'',125,NULL,NULL),(29,28,'','/sys/log/get','',2,'查看','','',0,1,'',99,NULL,NULL),(30,10,'','/flow/leave/get','',2,'查看','','',0,1,'',99,NULL,NULL),(31,10,'','/flow/leave/post','',2,'添加','','',0,1,'',100,NULL,NULL),(32,10,'','/flow/leave/delete','',2,'删除','','',0,1,'',105,NULL,NULL);
/*!40000 ALTER TABLE `sys_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_perm`
--

DROP TABLE IF EXISTS `sys_perm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_perm` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '权限ID',
  `perm_type` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '权限类型：menu:菜单路由类,role:角色类,file:文件类',
  `r_id` int NOT NULL COMMENT '实际基础表的关联id，如菜单表ID，角色表ID，文件表ID等',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='系统权限表\r\n\r\n基础表（菜单表，角色表，文件表及其他需要权限控制的表）每新增一个记录，此表同时插入一条对应记录，如\r\nsys_menu表加入一条记录，此处需要对应加入  类型 menu 的 r_id 为menu id的记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_perm`
--

LOCK TABLES `sys_perm` WRITE;
/*!40000 ALTER TABLE `sys_perm` DISABLE KEYS */;
INSERT INTO `sys_perm` VALUES (1,'role',1),(2,'menu',1),(3,'menu',2),(4,'menu',3),(5,'menu',4),(6,'menu',5),(7,'menu',6),(8,'menu',7),(9,'menu',8),(10,'menu',9),(11,'menu',10),(12,'menu',11),(13,'menu',12),(14,'menu',13),(15,'menu',14),(16,'menu',15),(17,'menu',16),(18,'menu',17),(19,'menu',18),(20,'menu',19),(21,'menu',20),(27,'menu',21),(29,'menu',23),(30,'menu',24),(31,'menu',25),(32,'menu',26),(33,'menu',27),(34,'dept',1),(35,'dept',2),(36,'dept',3),(37,'dept',4),(38,'role',2),(39,'menu',28),(40,'menu',29),(41,'dept',5),(42,'menu',30),(43,'menu',31),(44,'menu',32),(45,'role',3);
/*!40000 ALTER TABLE `sys_perm` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_perm_type`
--

DROP TABLE IF EXISTS `sys_perm_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_perm_type` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '权限类型',
  `r_table` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '类型对应的基础表，如sys_menu,sys_role,sys_file等',
  `title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '类型标题',
  `remark` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT '类型注释说明',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COMMENT='权限类型对照表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_perm_type`
--

LOCK TABLES `sys_perm_type` WRITE;
/*!40000 ALTER TABLE `sys_perm_type` DISABLE KEYS */;
INSERT INTO `sys_perm_type` VALUES (1,'role','sys_role','角色类',NULL),(2,'menu','sys_menu','菜单类',NULL),(3,'file','sys_file','文件类',NULL),(4,'dept','sys_dept','部门类',NULL);
/*!40000 ALTER TABLE `sys_perm_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_role`
--

DROP TABLE IF EXISTS `sys_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_role` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `pid` int DEFAULT '0',
  `status` tinyint DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  `scope` tinyint DEFAULT '0' COMMENT '部门数据权限范围\r\n0.全部数据权限 \r\n1.部门数据权限\r\n2.部门及以下数据权限\r\n3.仅本人数据权限\r\n4.自定数据权限\r\n\r\n当为自定义数据权限4 时，角色权限会在sys_role_perm里写入对应的部门权限，这里部门也抽象成一种权限和角色一样，其他情况会在代码里sql直接进行处理\r\n\r\n数据权限\r\n_在实际开发中，需要设置用户只能查看哪些部门的数据，这种情况一般称为数据权限。_',
  `create_time` int DEFAULT NULL,
  `update_time` int DEFAULT NULL,
  `listorder` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_role`
--

LOCK TABLES `sys_role` WRITE;
/*!40000 ALTER TABLE `sys_role` DISABLE KEYS */;
INSERT INTO `sys_role` VALUES (1,'超级管理员',0,1,'拥有网站最高管理员权限！',0,1329633709,1329633709,1),(2,'审批管理员',0,1,'',4,1584524771,NULL,99),(3,'员工',0,1,'',3,1717689941,NULL,99);
/*!40000 ALTER TABLE `sys_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_role_perm`
--

DROP TABLE IF EXISTS `sys_role_perm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_role_perm` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_id` int NOT NULL,
  `perm_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`),
  KEY `perm_id` (`perm_id`),
  CONSTRAINT `sys_role_perm_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `sys_role` (`id`),
  CONSTRAINT `sys_role_perm_ibfk_2` FOREIGN KEY (`perm_id`) REFERENCES `sys_perm` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_role_perm`
--

LOCK TABLES `sys_role_perm` WRITE;
/*!40000 ALTER TABLE `sys_role_perm` DISABLE KEYS */;
INSERT INTO `sys_role_perm` VALUES (1,1,1),(2,1,2),(3,1,3),(4,1,4),(5,1,5),(6,1,6),(7,1,7),(8,1,8),(9,1,9),(10,1,10),(11,1,11),(12,1,12),(13,1,13),(14,1,14),(15,1,15),(16,1,16),(17,1,17),(18,1,18),(19,1,19),(20,1,20),(21,1,21),(30,1,27),(38,1,29),(39,1,30),(40,1,31),(41,1,32),(42,1,33),(43,1,34),(44,1,35),(45,1,36),(46,1,37),(47,1,38),(48,2,2),(49,2,5),(50,2,18),(51,2,19),(52,2,20),(53,2,21),(57,2,35),(58,2,37),(59,2,36),(60,1,39),(61,1,40),(66,1,41),(67,1,42),(68,1,43),(69,1,44),(70,1,45),(71,3,6),(72,3,11),(73,3,42),(74,3,43),(75,2,6),(76,2,11),(77,2,42),(78,2,43),(79,2,12);
/*!40000 ALTER TABLE `sys_role_perm` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_user`
--

DROP TABLE IF EXISTS `sys_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `tel` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `sex` smallint DEFAULT NULL,
  `dept_id` int DEFAULT NULL COMMENT '部门ID',
  `position_code` varchar(100) DEFAULT NULL COMMENT '职位编码:GM(总经理), DGM(副总经理), DM(部门经理),STAFF(普通员工)',
  `last_login_ip` varchar(16) DEFAULT NULL,
  `last_login_time` int DEFAULT NULL,
  `create_time` int DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `listorder` int DEFAULT '1000',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_user`
--

LOCK TABLES `sys_user` WRITE;
/*!40000 ALTER TABLE `sys_user` DISABLE KEYS */;
INSERT INTO `sys_user` VALUES (1,'admin','21232f297a57a5a743894a0e4a801fc3','admin','111@gmail.com','https://wpimg.wallstcn.com/f778738c-e4f8-4870-b634-56703b4acafe.gif',0,NULL,NULL,'127.0.0.1',1493103488,1487868050,1,1),(2,'zhaoer','21232f297a57a5a743894a0e4a801fc3','','49727546@qq.com','https://gw.alipayobjects.com/zos/antfincdn/aPkFc8Sj7n/method-draw-image.svg',NULL,3,'DGM',NULL,NULL,1554800129,1,1002),(3,'zhangsan','21232f297a57a5a743894a0e4a801fc3','','','',NULL,4,'DM','',NULL,1554803362,1,1003),(4,'lisi','21232f297a57a5a743894a0e4a801fc3',NULL,'',NULL,NULL,4,'STAFF',NULL,NULL,1717345461,1,1005),(5,'wangwu','21232f297a57a5a743894a0e4a801fc3',NULL,'2@a.com',NULL,NULL,5,'STAFF',NULL,NULL,1717823386,1,1006);
/*!40000 ALTER TABLE `sys_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_user_role`
--

DROP TABLE IF EXISTS `sys_user_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_user_role` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `role_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `role_id` (`role_id`),
  CONSTRAINT `sys_user_role_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `sys_user` (`id`),
  CONSTRAINT `sys_user_role_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `sys_role` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_user_role`
--

LOCK TABLES `sys_user_role` WRITE;
/*!40000 ALTER TABLE `sys_user_role` DISABLE KEYS */;
INSERT INTO `sys_user_role` VALUES (1,1,1),(4,3,2),(6,4,3),(7,2,1),(8,5,3);
/*!40000 ALTER TABLE `sys_user_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'vueadminv2'
--
/*!50003 DROP FUNCTION IF EXISTS `getChildLst` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `getChildLst`(`rootId` int) RETURNS varchar(1000) CHARSET utf8mb3
    DETERMINISTIC
BEGIN 
DECLARE sTemp VARCHAR(1000); 

DECLARE sTempChd VARCHAR(1000); 

 SET sTemp = '$'; 
 SET sTempChd =cast(rootId as CHAR); 
 
 WHILE sTempChd is not null DO 
   SET sTemp = concat(sTemp,',',sTempChd); 
    SELECT group_concat(Id) INTO sTempChd FROM sys_menu where FIND_IN_SET(pid,sTempChd)>0; 
 END WHILE; 
  RETURN sTemp; 
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-06-08 16:23:22
