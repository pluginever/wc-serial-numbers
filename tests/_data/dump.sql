-- MySQL dump 10.13  Distrib 8.0.31, for macos13.0 (arm64)
--
-- Host: localhost    Database: acceptance_tests
-- ------------------------------------------------------
-- Server version	8.0.31

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
-- Table structure for table `wp_actionscheduler_actions`
--

DROP TABLE IF EXISTS `wp_actionscheduler_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_actionscheduler_actions` (
  `action_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hook` varchar(191) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `scheduled_date_gmt` datetime DEFAULT '0000-00-00 00:00:00',
  `scheduled_date_local` datetime DEFAULT '0000-00-00 00:00:00',
  `args` varchar(191) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `schedule` longtext COLLATE utf8mb4_unicode_520_ci,
  `group_id` bigint unsigned NOT NULL DEFAULT '0',
  `attempts` int NOT NULL DEFAULT '0',
  `last_attempt_gmt` datetime DEFAULT '0000-00-00 00:00:00',
  `last_attempt_local` datetime DEFAULT '0000-00-00 00:00:00',
  `claim_id` bigint unsigned NOT NULL DEFAULT '0',
  `extended_args` varchar(8000) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`action_id`),
  KEY `hook` (`hook`),
  KEY `status` (`status`),
  KEY `scheduled_date_gmt` (`scheduled_date_gmt`),
  KEY `args` (`args`),
  KEY `group_id` (`group_id`),
  KEY `last_attempt_gmt` (`last_attempt_gmt`),
  KEY `claim_id_status_scheduled_date_gmt` (`claim_id`,`status`,`scheduled_date_gmt`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_actionscheduler_actions`
--

LOCK TABLES `wp_actionscheduler_actions` WRITE;
/*!40000 ALTER TABLE `wp_actionscheduler_actions` DISABLE KEYS */;
INSERT INTO `wp_actionscheduler_actions` VALUES (5,'action_scheduler/migration_hook','pending','2022-12-28 16:04:05','2022-12-28 16:04:05','[]','O:30:\"ActionScheduler_SimpleSchedule\":2:{s:22:\"\0*\0scheduled_timestamp\";i:1672243445;s:41:\"\0ActionScheduler_SimpleSchedule\0timestamp\";i:1672243445;}',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',0,NULL);
/*!40000 ALTER TABLE `wp_actionscheduler_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_actionscheduler_claims`
--

DROP TABLE IF EXISTS `wp_actionscheduler_claims`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_actionscheduler_claims` (
  `claim_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date_created_gmt` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`claim_id`),
  KEY `date_created_gmt` (`date_created_gmt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_actionscheduler_claims`
--

LOCK TABLES `wp_actionscheduler_claims` WRITE;
/*!40000 ALTER TABLE `wp_actionscheduler_claims` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_actionscheduler_claims` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_actionscheduler_groups`
--

DROP TABLE IF EXISTS `wp_actionscheduler_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_actionscheduler_groups` (
  `group_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`group_id`),
  KEY `slug` (`slug`(191))
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_actionscheduler_groups`
--

LOCK TABLES `wp_actionscheduler_groups` WRITE;
/*!40000 ALTER TABLE `wp_actionscheduler_groups` DISABLE KEYS */;
INSERT INTO `wp_actionscheduler_groups` VALUES (1,'action-scheduler-migration');
/*!40000 ALTER TABLE `wp_actionscheduler_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_actionscheduler_logs`
--

DROP TABLE IF EXISTS `wp_actionscheduler_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_actionscheduler_logs` (
  `log_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `action_id` bigint unsigned NOT NULL,
  `message` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `log_date_gmt` datetime DEFAULT '0000-00-00 00:00:00',
  `log_date_local` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`log_id`),
  KEY `action_id` (`action_id`),
  KEY `log_date_gmt` (`log_date_gmt`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_actionscheduler_logs`
--

LOCK TABLES `wp_actionscheduler_logs` WRITE;
/*!40000 ALTER TABLE `wp_actionscheduler_logs` DISABLE KEYS */;
INSERT INTO `wp_actionscheduler_logs` VALUES (1,5,'action created','2022-12-28 16:03:05','2022-12-28 16:03:05');
/*!40000 ALTER TABLE `wp_actionscheduler_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_commentmeta`
--

DROP TABLE IF EXISTS `wp_commentmeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_commentmeta` (
  `meta_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` bigint unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`meta_id`),
  KEY `comment_id` (`comment_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_commentmeta`
--

LOCK TABLES `wp_commentmeta` WRITE;
/*!40000 ALTER TABLE `wp_commentmeta` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_commentmeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_comments`
--

DROP TABLE IF EXISTS `wp_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_comments` (
  `comment_ID` bigint unsigned NOT NULL AUTO_INCREMENT,
  `comment_post_ID` bigint unsigned NOT NULL DEFAULT '0',
  `comment_author` tinytext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `comment_author_email` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_author_url` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_author_IP` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_content` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `comment_karma` int NOT NULL DEFAULT '0',
  `comment_approved` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '1',
  `comment_agent` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'comment',
  `comment_parent` bigint unsigned NOT NULL DEFAULT '0',
  `user_id` bigint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`comment_ID`),
  KEY `comment_post_ID` (`comment_post_ID`),
  KEY `comment_approved_date_gmt` (`comment_approved`,`comment_date_gmt`),
  KEY `comment_date_gmt` (`comment_date_gmt`),
  KEY `comment_parent` (`comment_parent`),
  KEY `comment_author_email` (`comment_author_email`(10)),
  KEY `woo_idx_comment_type` (`comment_type`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_comments`
--

LOCK TABLES `wp_comments` WRITE;
/*!40000 ALTER TABLE `wp_comments` DISABLE KEYS */;
INSERT INTO `wp_comments` VALUES (1,1,'A WordPress Commenter','wapuu@wordpress.example','https://wordpress.org/','','2022-12-28 16:02:49','2022-12-28 16:02:49','Hi, this is a comment.\nTo get started with moderating, editing, and deleting comments, please visit the Comments screen in the dashboard.\nCommenter avatars come from <a href=\"https://en.gravatar.com/\">Gravatar</a>.',0,'1','','comment',0,0);
/*!40000 ALTER TABLE `wp_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_links`
--

DROP TABLE IF EXISTS `wp_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_links` (
  `link_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `link_url` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_name` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_image` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_target` varchar(25) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_description` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_visible` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'Y',
  `link_owner` bigint unsigned NOT NULL DEFAULT '1',
  `link_rating` int NOT NULL DEFAULT '0',
  `link_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `link_rel` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_notes` mediumtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `link_rss` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`link_id`),
  KEY `link_visible` (`link_visible`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_links`
--

LOCK TABLES `wp_links` WRITE;
/*!40000 ALTER TABLE `wp_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_options`
--

DROP TABLE IF EXISTS `wp_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_options` (
  `option_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `option_name` varchar(191) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `option_value` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `autoload` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`option_id`),
  UNIQUE KEY `option_name` (`option_name`),
  KEY `autoload` (`autoload`)
) ENGINE=InnoDB AUTO_INCREMENT=296 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_options`
--

LOCK TABLES `wp_options` WRITE;
/*!40000 ALTER TABLE `wp_options` DISABLE KEYS */;
INSERT INTO `wp_options` VALUES (1,'siteurl','http://acceptance-tests.test','yes'),(2,'home','http://acceptance-tests.test','yes'),(3,'blogname','Acceptance Tests','yes'),(4,'blogdescription','','yes'),(5,'users_can_register','0','yes'),(6,'admin_email','manik@pluginever.com','yes'),(7,'start_of_week','1','yes'),(8,'use_balanceTags','0','yes'),(9,'use_smilies','1','yes'),(10,'require_name_email','1','yes'),(11,'comments_notify','1','yes'),(12,'posts_per_rss','10','yes'),(13,'rss_use_excerpt','0','yes'),(14,'mailserver_url','mail.example.com','yes'),(15,'mailserver_login','login@example.com','yes'),(16,'mailserver_pass','password','yes'),(17,'mailserver_port','110','yes'),(18,'default_category','1','yes'),(19,'default_comment_status','open','yes'),(20,'default_ping_status','open','yes'),(21,'default_pingback_flag','1','yes'),(22,'posts_per_page','10','yes'),(23,'date_format','F j, Y','yes'),(24,'time_format','g:i a','yes'),(25,'links_updated_date_format','F j, Y g:i a','yes'),(26,'comment_moderation','0','yes'),(27,'moderation_notify','1','yes'),(28,'permalink_structure','/%postname%/','yes'),(29,'rewrite_rules','a:160:{s:11:\"^wp-json/?$\";s:22:\"index.php?rest_route=/\";s:14:\"^wp-json/(.*)?\";s:33:\"index.php?rest_route=/$matches[1]\";s:21:\"^index.php/wp-json/?$\";s:22:\"index.php?rest_route=/\";s:24:\"^index.php/wp-json/(.*)?\";s:33:\"index.php?rest_route=/$matches[1]\";s:17:\"^wp-sitemap\\.xml$\";s:23:\"index.php?sitemap=index\";s:17:\"^wp-sitemap\\.xsl$\";s:36:\"index.php?sitemap-stylesheet=sitemap\";s:23:\"^wp-sitemap-index\\.xsl$\";s:34:\"index.php?sitemap-stylesheet=index\";s:48:\"^wp-sitemap-([a-z]+?)-([a-z\\d_-]+?)-(\\d+?)\\.xml$\";s:75:\"index.php?sitemap=$matches[1]&sitemap-subtype=$matches[2]&paged=$matches[3]\";s:34:\"^wp-sitemap-([a-z]+?)-(\\d+?)\\.xml$\";s:47:\"index.php?sitemap=$matches[1]&paged=$matches[2]\";s:7:\"shop/?$\";s:27:\"index.php?post_type=product\";s:37:\"shop/feed/(feed|rdf|rss|rss2|atom)/?$\";s:44:\"index.php?post_type=product&feed=$matches[1]\";s:32:\"shop/(feed|rdf|rss|rss2|atom)/?$\";s:44:\"index.php?post_type=product&feed=$matches[1]\";s:24:\"shop/page/([0-9]{1,})/?$\";s:45:\"index.php?post_type=product&paged=$matches[1]\";s:47:\"category/(.+?)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:52:\"index.php?category_name=$matches[1]&feed=$matches[2]\";s:42:\"category/(.+?)/(feed|rdf|rss|rss2|atom)/?$\";s:52:\"index.php?category_name=$matches[1]&feed=$matches[2]\";s:23:\"category/(.+?)/embed/?$\";s:46:\"index.php?category_name=$matches[1]&embed=true\";s:35:\"category/(.+?)/page/?([0-9]{1,})/?$\";s:53:\"index.php?category_name=$matches[1]&paged=$matches[2]\";s:17:\"category/(.+?)/?$\";s:35:\"index.php?category_name=$matches[1]\";s:44:\"tag/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?tag=$matches[1]&feed=$matches[2]\";s:39:\"tag/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?tag=$matches[1]&feed=$matches[2]\";s:20:\"tag/([^/]+)/embed/?$\";s:36:\"index.php?tag=$matches[1]&embed=true\";s:32:\"tag/([^/]+)/page/?([0-9]{1,})/?$\";s:43:\"index.php?tag=$matches[1]&paged=$matches[2]\";s:14:\"tag/([^/]+)/?$\";s:25:\"index.php?tag=$matches[1]\";s:45:\"type/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?post_format=$matches[1]&feed=$matches[2]\";s:40:\"type/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?post_format=$matches[1]&feed=$matches[2]\";s:21:\"type/([^/]+)/embed/?$\";s:44:\"index.php?post_format=$matches[1]&embed=true\";s:33:\"type/([^/]+)/page/?([0-9]{1,})/?$\";s:51:\"index.php?post_format=$matches[1]&paged=$matches[2]\";s:15:\"type/([^/]+)/?$\";s:33:\"index.php?post_format=$matches[1]\";s:35:\"product/[^/]+/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:45:\"product/[^/]+/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:65:\"product/[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:60:\"product/[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:60:\"product/[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:41:\"product/[^/]+/attachment/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:24:\"product/([^/]+)/embed/?$\";s:40:\"index.php?product=$matches[1]&embed=true\";s:28:\"product/([^/]+)/trackback/?$\";s:34:\"index.php?product=$matches[1]&tb=1\";s:48:\"product/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:46:\"index.php?product=$matches[1]&feed=$matches[2]\";s:43:\"product/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:46:\"index.php?product=$matches[1]&feed=$matches[2]\";s:36:\"product/([^/]+)/page/?([0-9]{1,})/?$\";s:47:\"index.php?product=$matches[1]&paged=$matches[2]\";s:43:\"product/([^/]+)/comment-page-([0-9]{1,})/?$\";s:47:\"index.php?product=$matches[1]&cpage=$matches[2]\";s:32:\"product/([^/]+)(?:/([0-9]+))?/?$\";s:46:\"index.php?product=$matches[1]&page=$matches[2]\";s:24:\"product/[^/]+/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:34:\"product/[^/]+/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:54:\"product/[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:49:\"product/[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:49:\"product/[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:30:\"product/[^/]+/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:22:\"^wc-api/v([1-3]{1})/?$\";s:51:\"index.php?wc-api-version=$matches[1]&wc-api-route=/\";s:24:\"^wc-api/v([1-3]{1})(.*)?\";s:61:\"index.php?wc-api-version=$matches[1]&wc-api-route=$matches[2]\";s:24:\"^wc-auth/v([1]{1})/(.*)?\";s:63:\"index.php?wc-auth-version=$matches[1]&wc-auth-route=$matches[2]\";s:32:\"category/(.+?)/wc-api(/(.*))?/?$\";s:54:\"index.php?category_name=$matches[1]&wc-api=$matches[3]\";s:29:\"tag/([^/]+)/wc-api(/(.*))?/?$\";s:44:\"index.php?tag=$matches[1]&wc-api=$matches[3]\";s:33:\"product/([^/]+)/wc-api(/(.*))?/?$\";s:48:\"index.php?product=$matches[1]&wc-api=$matches[3]\";s:39:\"product/[^/]+/([^/]+)/wc-api(/(.*))?/?$\";s:51:\"index.php?attachment=$matches[1]&wc-api=$matches[3]\";s:50:\"product/[^/]+/attachment/([^/]+)/wc-api(/(.*))?/?$\";s:51:\"index.php?attachment=$matches[1]&wc-api=$matches[3]\";s:55:\"product-category/(.+?)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?product_cat=$matches[1]&feed=$matches[2]\";s:50:\"product-category/(.+?)/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?product_cat=$matches[1]&feed=$matches[2]\";s:31:\"product-category/(.+?)/embed/?$\";s:44:\"index.php?product_cat=$matches[1]&embed=true\";s:43:\"product-category/(.+?)/page/?([0-9]{1,})/?$\";s:51:\"index.php?product_cat=$matches[1]&paged=$matches[2]\";s:25:\"product-category/(.+?)/?$\";s:33:\"index.php?product_cat=$matches[1]\";s:52:\"product-tag/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?product_tag=$matches[1]&feed=$matches[2]\";s:47:\"product-tag/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?product_tag=$matches[1]&feed=$matches[2]\";s:28:\"product-tag/([^/]+)/embed/?$\";s:44:\"index.php?product_tag=$matches[1]&embed=true\";s:40:\"product-tag/([^/]+)/page/?([0-9]{1,})/?$\";s:51:\"index.php?product_tag=$matches[1]&paged=$matches[2]\";s:22:\"product-tag/([^/]+)/?$\";s:33:\"index.php?product_tag=$matches[1]\";s:12:\"robots\\.txt$\";s:18:\"index.php?robots=1\";s:13:\"favicon\\.ico$\";s:19:\"index.php?favicon=1\";s:48:\".*wp-(atom|rdf|rss|rss2|feed|commentsrss2)\\.php$\";s:18:\"index.php?feed=old\";s:20:\".*wp-app\\.php(/.*)?$\";s:19:\"index.php?error=403\";s:18:\".*wp-register.php$\";s:23:\"index.php?register=true\";s:32:\"feed/(feed|rdf|rss|rss2|atom)/?$\";s:27:\"index.php?&feed=$matches[1]\";s:27:\"(feed|rdf|rss|rss2|atom)/?$\";s:27:\"index.php?&feed=$matches[1]\";s:8:\"embed/?$\";s:21:\"index.php?&embed=true\";s:20:\"page/?([0-9]{1,})/?$\";s:28:\"index.php?&paged=$matches[1]\";s:17:\"wc-api(/(.*))?/?$\";s:29:\"index.php?&wc-api=$matches[2]\";s:41:\"comments/feed/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?&feed=$matches[1]&withcomments=1\";s:36:\"comments/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?&feed=$matches[1]&withcomments=1\";s:17:\"comments/embed/?$\";s:21:\"index.php?&embed=true\";s:26:\"comments/wc-api(/(.*))?/?$\";s:29:\"index.php?&wc-api=$matches[2]\";s:44:\"search/(.+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:40:\"index.php?s=$matches[1]&feed=$matches[2]\";s:39:\"search/(.+)/(feed|rdf|rss|rss2|atom)/?$\";s:40:\"index.php?s=$matches[1]&feed=$matches[2]\";s:20:\"search/(.+)/embed/?$\";s:34:\"index.php?s=$matches[1]&embed=true\";s:32:\"search/(.+)/page/?([0-9]{1,})/?$\";s:41:\"index.php?s=$matches[1]&paged=$matches[2]\";s:29:\"search/(.+)/wc-api(/(.*))?/?$\";s:42:\"index.php?s=$matches[1]&wc-api=$matches[3]\";s:14:\"search/(.+)/?$\";s:23:\"index.php?s=$matches[1]\";s:47:\"author/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?author_name=$matches[1]&feed=$matches[2]\";s:42:\"author/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?author_name=$matches[1]&feed=$matches[2]\";s:23:\"author/([^/]+)/embed/?$\";s:44:\"index.php?author_name=$matches[1]&embed=true\";s:35:\"author/([^/]+)/page/?([0-9]{1,})/?$\";s:51:\"index.php?author_name=$matches[1]&paged=$matches[2]\";s:32:\"author/([^/]+)/wc-api(/(.*))?/?$\";s:52:\"index.php?author_name=$matches[1]&wc-api=$matches[3]\";s:17:\"author/([^/]+)/?$\";s:33:\"index.php?author_name=$matches[1]\";s:69:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:80:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]\";s:64:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$\";s:80:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]\";s:45:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/embed/?$\";s:74:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&embed=true\";s:57:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/page/?([0-9]{1,})/?$\";s:81:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&paged=$matches[4]\";s:54:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/wc-api(/(.*))?/?$\";s:82:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&wc-api=$matches[5]\";s:39:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$\";s:63:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]\";s:56:\"([0-9]{4})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:64:\"index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]\";s:51:\"([0-9]{4})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$\";s:64:\"index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]\";s:32:\"([0-9]{4})/([0-9]{1,2})/embed/?$\";s:58:\"index.php?year=$matches[1]&monthnum=$matches[2]&embed=true\";s:44:\"([0-9]{4})/([0-9]{1,2})/page/?([0-9]{1,})/?$\";s:65:\"index.php?year=$matches[1]&monthnum=$matches[2]&paged=$matches[3]\";s:41:\"([0-9]{4})/([0-9]{1,2})/wc-api(/(.*))?/?$\";s:66:\"index.php?year=$matches[1]&monthnum=$matches[2]&wc-api=$matches[4]\";s:26:\"([0-9]{4})/([0-9]{1,2})/?$\";s:47:\"index.php?year=$matches[1]&monthnum=$matches[2]\";s:43:\"([0-9]{4})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?year=$matches[1]&feed=$matches[2]\";s:38:\"([0-9]{4})/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?year=$matches[1]&feed=$matches[2]\";s:19:\"([0-9]{4})/embed/?$\";s:37:\"index.php?year=$matches[1]&embed=true\";s:31:\"([0-9]{4})/page/?([0-9]{1,})/?$\";s:44:\"index.php?year=$matches[1]&paged=$matches[2]\";s:28:\"([0-9]{4})/wc-api(/(.*))?/?$\";s:45:\"index.php?year=$matches[1]&wc-api=$matches[3]\";s:13:\"([0-9]{4})/?$\";s:26:\"index.php?year=$matches[1]\";s:27:\".?.+?/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:37:\".?.+?/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:57:\".?.+?/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\".?.+?/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\".?.+?/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:33:\".?.+?/attachment/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:16:\"(.?.+?)/embed/?$\";s:41:\"index.php?pagename=$matches[1]&embed=true\";s:20:\"(.?.+?)/trackback/?$\";s:35:\"index.php?pagename=$matches[1]&tb=1\";s:40:\"(.?.+?)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:47:\"index.php?pagename=$matches[1]&feed=$matches[2]\";s:35:\"(.?.+?)/(feed|rdf|rss|rss2|atom)/?$\";s:47:\"index.php?pagename=$matches[1]&feed=$matches[2]\";s:28:\"(.?.+?)/page/?([0-9]{1,})/?$\";s:48:\"index.php?pagename=$matches[1]&paged=$matches[2]\";s:35:\"(.?.+?)/comment-page-([0-9]{1,})/?$\";s:48:\"index.php?pagename=$matches[1]&cpage=$matches[2]\";s:28:\"(.?.+?)/order-pay(/(.*))?/?$\";s:52:\"index.php?pagename=$matches[1]&order-pay=$matches[3]\";s:33:\"(.?.+?)/order-received(/(.*))?/?$\";s:57:\"index.php?pagename=$matches[1]&order-received=$matches[3]\";s:25:\"(.?.+?)/orders(/(.*))?/?$\";s:49:\"index.php?pagename=$matches[1]&orders=$matches[3]\";s:29:\"(.?.+?)/view-order(/(.*))?/?$\";s:53:\"index.php?pagename=$matches[1]&view-order=$matches[3]\";s:28:\"(.?.+?)/downloads(/(.*))?/?$\";s:52:\"index.php?pagename=$matches[1]&downloads=$matches[3]\";s:31:\"(.?.+?)/edit-account(/(.*))?/?$\";s:55:\"index.php?pagename=$matches[1]&edit-account=$matches[3]\";s:31:\"(.?.+?)/edit-address(/(.*))?/?$\";s:55:\"index.php?pagename=$matches[1]&edit-address=$matches[3]\";s:34:\"(.?.+?)/payment-methods(/(.*))?/?$\";s:58:\"index.php?pagename=$matches[1]&payment-methods=$matches[3]\";s:32:\"(.?.+?)/lost-password(/(.*))?/?$\";s:56:\"index.php?pagename=$matches[1]&lost-password=$matches[3]\";s:34:\"(.?.+?)/customer-logout(/(.*))?/?$\";s:58:\"index.php?pagename=$matches[1]&customer-logout=$matches[3]\";s:37:\"(.?.+?)/add-payment-method(/(.*))?/?$\";s:61:\"index.php?pagename=$matches[1]&add-payment-method=$matches[3]\";s:40:\"(.?.+?)/delete-payment-method(/(.*))?/?$\";s:64:\"index.php?pagename=$matches[1]&delete-payment-method=$matches[3]\";s:45:\"(.?.+?)/set-default-payment-method(/(.*))?/?$\";s:69:\"index.php?pagename=$matches[1]&set-default-payment-method=$matches[3]\";s:25:\"(.?.+?)/wc-api(/(.*))?/?$\";s:49:\"index.php?pagename=$matches[1]&wc-api=$matches[3]\";s:31:\".?.+?/([^/]+)/wc-api(/(.*))?/?$\";s:51:\"index.php?attachment=$matches[1]&wc-api=$matches[3]\";s:42:\".?.+?/attachment/([^/]+)/wc-api(/(.*))?/?$\";s:51:\"index.php?attachment=$matches[1]&wc-api=$matches[3]\";s:24:\"(.?.+?)(?:/([0-9]+))?/?$\";s:47:\"index.php?pagename=$matches[1]&page=$matches[2]\";s:27:\"[^/]+/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:37:\"[^/]+/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:57:\"[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\"[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\"[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:33:\"[^/]+/attachment/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:16:\"([^/]+)/embed/?$\";s:37:\"index.php?name=$matches[1]&embed=true\";s:20:\"([^/]+)/trackback/?$\";s:31:\"index.php?name=$matches[1]&tb=1\";s:40:\"([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?name=$matches[1]&feed=$matches[2]\";s:35:\"([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?name=$matches[1]&feed=$matches[2]\";s:28:\"([^/]+)/page/?([0-9]{1,})/?$\";s:44:\"index.php?name=$matches[1]&paged=$matches[2]\";s:35:\"([^/]+)/comment-page-([0-9]{1,})/?$\";s:44:\"index.php?name=$matches[1]&cpage=$matches[2]\";s:25:\"([^/]+)/wc-api(/(.*))?/?$\";s:45:\"index.php?name=$matches[1]&wc-api=$matches[3]\";s:31:\"[^/]+/([^/]+)/wc-api(/(.*))?/?$\";s:51:\"index.php?attachment=$matches[1]&wc-api=$matches[3]\";s:42:\"[^/]+/attachment/([^/]+)/wc-api(/(.*))?/?$\";s:51:\"index.php?attachment=$matches[1]&wc-api=$matches[3]\";s:24:\"([^/]+)(?:/([0-9]+))?/?$\";s:43:\"index.php?name=$matches[1]&page=$matches[2]\";s:16:\"[^/]+/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:26:\"[^/]+/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:46:\"[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:41:\"[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:41:\"[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:22:\"[^/]+/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";}','yes'),(30,'hack_file','0','yes'),(31,'blog_charset','UTF-8','yes'),(32,'moderation_keys','','no'),(33,'active_plugins','a:0:{}','yes'),(34,'category_base','','yes'),(35,'ping_sites','http://rpc.pingomatic.com/','yes'),(36,'comment_max_links','2','yes'),(37,'gmt_offset','0','yes'),(38,'default_email_category','1','yes'),(39,'recently_edited','','no'),(40,'template','twentytwentythree','yes'),(41,'stylesheet','twentytwentythree','yes'),(42,'comment_registration','0','yes'),(43,'html_type','text/html','yes'),(44,'use_trackback','0','yes'),(45,'default_role','subscriber','yes'),(46,'db_version','53496','yes'),(47,'uploads_use_yearmonth_folders','1','yes'),(48,'upload_path','','yes'),(49,'blog_public','1','yes'),(50,'default_link_category','2','yes'),(51,'show_on_front','posts','yes'),(52,'tag_base','','yes'),(53,'show_avatars','1','yes'),(54,'avatar_rating','G','yes'),(55,'upload_url_path','','yes'),(56,'thumbnail_size_w','150','yes'),(57,'thumbnail_size_h','150','yes'),(58,'thumbnail_crop','1','yes'),(59,'medium_size_w','300','yes'),(60,'medium_size_h','300','yes'),(61,'avatar_default','mystery','yes'),(62,'large_size_w','1024','yes'),(63,'large_size_h','1024','yes'),(64,'image_default_link_type','none','yes'),(65,'image_default_size','','yes'),(66,'image_default_align','','yes'),(67,'close_comments_for_old_posts','0','yes'),(68,'close_comments_days_old','14','yes'),(69,'thread_comments','1','yes'),(70,'thread_comments_depth','5','yes'),(71,'page_comments','0','yes'),(72,'comments_per_page','50','yes'),(73,'default_comments_page','newest','yes'),(74,'comment_order','asc','yes'),(75,'sticky_posts','a:0:{}','yes'),(76,'widget_categories','a:0:{}','yes'),(77,'widget_text','a:0:{}','yes'),(78,'widget_rss','a:0:{}','yes'),(79,'uninstall_plugins','a:0:{}','no'),(80,'timezone_string','','yes'),(81,'page_for_posts','0','yes'),(82,'page_on_front','0','yes'),(83,'default_post_format','0','yes'),(84,'link_manager_enabled','0','yes'),(85,'finished_splitting_shared_terms','1','yes'),(86,'site_icon','0','yes'),(87,'medium_large_size_w','768','yes'),(88,'medium_large_size_h','0','yes'),(89,'wp_page_for_privacy_policy','3','yes'),(90,'show_comments_cookies_opt_in','1','yes'),(91,'admin_email_lifespan','1687795369','yes'),(92,'disallowed_keys','','no'),(93,'comment_previously_approved','1','yes'),(94,'auto_plugin_theme_update_emails','a:0:{}','no'),(95,'auto_update_core_dev','enabled','yes'),(96,'auto_update_core_minor','enabled','yes'),(97,'auto_update_core_major','enabled','yes'),(98,'wp_force_deactivated_plugins','a:0:{}','yes'),(99,'initial_db_version','53496','yes'),(100,'wp_user_roles','a:7:{s:13:\"administrator\";a:2:{s:4:\"name\";s:13:\"Administrator\";s:12:\"capabilities\";a:114:{s:13:\"switch_themes\";b:1;s:11:\"edit_themes\";b:1;s:16:\"activate_plugins\";b:1;s:12:\"edit_plugins\";b:1;s:10:\"edit_users\";b:1;s:10:\"edit_files\";b:1;s:14:\"manage_options\";b:1;s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:6:\"import\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:8:\"level_10\";b:1;s:7:\"level_9\";b:1;s:7:\"level_8\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;s:12:\"delete_users\";b:1;s:12:\"create_users\";b:1;s:17:\"unfiltered_upload\";b:1;s:14:\"edit_dashboard\";b:1;s:14:\"update_plugins\";b:1;s:14:\"delete_plugins\";b:1;s:15:\"install_plugins\";b:1;s:13:\"update_themes\";b:1;s:14:\"install_themes\";b:1;s:11:\"update_core\";b:1;s:10:\"list_users\";b:1;s:12:\"remove_users\";b:1;s:13:\"promote_users\";b:1;s:18:\"edit_theme_options\";b:1;s:13:\"delete_themes\";b:1;s:6:\"export\";b:1;s:18:\"manage_woocommerce\";b:1;s:24:\"view_woocommerce_reports\";b:1;s:12:\"edit_product\";b:1;s:12:\"read_product\";b:1;s:14:\"delete_product\";b:1;s:13:\"edit_products\";b:1;s:20:\"edit_others_products\";b:1;s:16:\"publish_products\";b:1;s:21:\"read_private_products\";b:1;s:15:\"delete_products\";b:1;s:23:\"delete_private_products\";b:1;s:25:\"delete_published_products\";b:1;s:22:\"delete_others_products\";b:1;s:21:\"edit_private_products\";b:1;s:23:\"edit_published_products\";b:1;s:20:\"manage_product_terms\";b:1;s:18:\"edit_product_terms\";b:1;s:20:\"delete_product_terms\";b:1;s:20:\"assign_product_terms\";b:1;s:15:\"edit_shop_order\";b:1;s:15:\"read_shop_order\";b:1;s:17:\"delete_shop_order\";b:1;s:16:\"edit_shop_orders\";b:1;s:23:\"edit_others_shop_orders\";b:1;s:19:\"publish_shop_orders\";b:1;s:24:\"read_private_shop_orders\";b:1;s:18:\"delete_shop_orders\";b:1;s:26:\"delete_private_shop_orders\";b:1;s:28:\"delete_published_shop_orders\";b:1;s:25:\"delete_others_shop_orders\";b:1;s:24:\"edit_private_shop_orders\";b:1;s:26:\"edit_published_shop_orders\";b:1;s:23:\"manage_shop_order_terms\";b:1;s:21:\"edit_shop_order_terms\";b:1;s:23:\"delete_shop_order_terms\";b:1;s:23:\"assign_shop_order_terms\";b:1;s:16:\"edit_shop_coupon\";b:1;s:16:\"read_shop_coupon\";b:1;s:18:\"delete_shop_coupon\";b:1;s:17:\"edit_shop_coupons\";b:1;s:24:\"edit_others_shop_coupons\";b:1;s:20:\"publish_shop_coupons\";b:1;s:25:\"read_private_shop_coupons\";b:1;s:19:\"delete_shop_coupons\";b:1;s:27:\"delete_private_shop_coupons\";b:1;s:29:\"delete_published_shop_coupons\";b:1;s:26:\"delete_others_shop_coupons\";b:1;s:25:\"edit_private_shop_coupons\";b:1;s:27:\"edit_published_shop_coupons\";b:1;s:24:\"manage_shop_coupon_terms\";b:1;s:22:\"edit_shop_coupon_terms\";b:1;s:24:\"delete_shop_coupon_terms\";b:1;s:24:\"assign_shop_coupon_terms\";b:1;}}s:6:\"editor\";a:2:{s:4:\"name\";s:6:\"Editor\";s:12:\"capabilities\";a:34:{s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;}}s:6:\"author\";a:2:{s:4:\"name\";s:6:\"Author\";s:12:\"capabilities\";a:10:{s:12:\"upload_files\";b:1;s:10:\"edit_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:4:\"read\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:12:\"delete_posts\";b:1;s:22:\"delete_published_posts\";b:1;}}s:11:\"contributor\";a:2:{s:4:\"name\";s:11:\"Contributor\";s:12:\"capabilities\";a:5:{s:10:\"edit_posts\";b:1;s:4:\"read\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:12:\"delete_posts\";b:1;}}s:10:\"subscriber\";a:2:{s:4:\"name\";s:10:\"Subscriber\";s:12:\"capabilities\";a:2:{s:4:\"read\";b:1;s:7:\"level_0\";b:1;}}s:8:\"customer\";a:2:{s:4:\"name\";s:8:\"Customer\";s:12:\"capabilities\";a:1:{s:4:\"read\";b:1;}}s:12:\"shop_manager\";a:2:{s:4:\"name\";s:12:\"Shop manager\";s:12:\"capabilities\";a:92:{s:7:\"level_9\";b:1;s:7:\"level_8\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:4:\"read\";b:1;s:18:\"read_private_pages\";b:1;s:18:\"read_private_posts\";b:1;s:10:\"edit_posts\";b:1;s:10:\"edit_pages\";b:1;s:20:\"edit_published_posts\";b:1;s:20:\"edit_published_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"edit_private_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:17:\"edit_others_pages\";b:1;s:13:\"publish_posts\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_posts\";b:1;s:12:\"delete_pages\";b:1;s:20:\"delete_private_pages\";b:1;s:20:\"delete_private_posts\";b:1;s:22:\"delete_published_pages\";b:1;s:22:\"delete_published_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:19:\"delete_others_pages\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:17:\"moderate_comments\";b:1;s:12:\"upload_files\";b:1;s:6:\"export\";b:1;s:6:\"import\";b:1;s:10:\"list_users\";b:1;s:18:\"edit_theme_options\";b:1;s:18:\"manage_woocommerce\";b:1;s:24:\"view_woocommerce_reports\";b:1;s:12:\"edit_product\";b:1;s:12:\"read_product\";b:1;s:14:\"delete_product\";b:1;s:13:\"edit_products\";b:1;s:20:\"edit_others_products\";b:1;s:16:\"publish_products\";b:1;s:21:\"read_private_products\";b:1;s:15:\"delete_products\";b:1;s:23:\"delete_private_products\";b:1;s:25:\"delete_published_products\";b:1;s:22:\"delete_others_products\";b:1;s:21:\"edit_private_products\";b:1;s:23:\"edit_published_products\";b:1;s:20:\"manage_product_terms\";b:1;s:18:\"edit_product_terms\";b:1;s:20:\"delete_product_terms\";b:1;s:20:\"assign_product_terms\";b:1;s:15:\"edit_shop_order\";b:1;s:15:\"read_shop_order\";b:1;s:17:\"delete_shop_order\";b:1;s:16:\"edit_shop_orders\";b:1;s:23:\"edit_others_shop_orders\";b:1;s:19:\"publish_shop_orders\";b:1;s:24:\"read_private_shop_orders\";b:1;s:18:\"delete_shop_orders\";b:1;s:26:\"delete_private_shop_orders\";b:1;s:28:\"delete_published_shop_orders\";b:1;s:25:\"delete_others_shop_orders\";b:1;s:24:\"edit_private_shop_orders\";b:1;s:26:\"edit_published_shop_orders\";b:1;s:23:\"manage_shop_order_terms\";b:1;s:21:\"edit_shop_order_terms\";b:1;s:23:\"delete_shop_order_terms\";b:1;s:23:\"assign_shop_order_terms\";b:1;s:16:\"edit_shop_coupon\";b:1;s:16:\"read_shop_coupon\";b:1;s:18:\"delete_shop_coupon\";b:1;s:17:\"edit_shop_coupons\";b:1;s:24:\"edit_others_shop_coupons\";b:1;s:20:\"publish_shop_coupons\";b:1;s:25:\"read_private_shop_coupons\";b:1;s:19:\"delete_shop_coupons\";b:1;s:27:\"delete_private_shop_coupons\";b:1;s:29:\"delete_published_shop_coupons\";b:1;s:26:\"delete_others_shop_coupons\";b:1;s:25:\"edit_private_shop_coupons\";b:1;s:27:\"edit_published_shop_coupons\";b:1;s:24:\"manage_shop_coupon_terms\";b:1;s:22:\"edit_shop_coupon_terms\";b:1;s:24:\"delete_shop_coupon_terms\";b:1;s:24:\"assign_shop_coupon_terms\";b:1;}}}','yes'),(101,'fresh_site','0','yes'),(102,'user_count','1','no'),(103,'widget_block','a:6:{i:2;a:1:{s:7:\"content\";s:19:\"<!-- wp:search /-->\";}i:3;a:1:{s:7:\"content\";s:154:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Recent Posts</h2><!-- /wp:heading --><!-- wp:latest-posts /--></div><!-- /wp:group -->\";}i:4;a:1:{s:7:\"content\";s:227:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Recent Comments</h2><!-- /wp:heading --><!-- wp:latest-comments {\"displayAvatar\":false,\"displayDate\":false,\"displayExcerpt\":false} /--></div><!-- /wp:group -->\";}i:5;a:1:{s:7:\"content\";s:146:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Archives</h2><!-- /wp:heading --><!-- wp:archives /--></div><!-- /wp:group -->\";}i:6;a:1:{s:7:\"content\";s:150:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Categories</h2><!-- /wp:heading --><!-- wp:categories /--></div><!-- /wp:group -->\";}s:12:\"_multiwidget\";i:1;}','yes'),(104,'sidebars_widgets','a:4:{s:19:\"wp_inactive_widgets\";a:0:{}s:9:\"sidebar-1\";a:3:{i:0;s:7:\"block-2\";i:1;s:7:\"block-3\";i:2;s:7:\"block-4\";}s:9:\"sidebar-2\";a:2:{i:0;s:7:\"block-5\";i:1;s:7:\"block-6\";}s:13:\"array_version\";i:3;}','yes'),(105,'cron','a:12:{i:1672243369;a:1:{s:32:\"recovery_mode_clean_expired_keys\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1672243370;a:5:{s:18:\"wp_https_detection\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}s:34:\"wp_privacy_delete_old_export_files\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"hourly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:3600;}}s:16:\"wp_version_check\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}s:17:\"wp_update_plugins\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}s:16:\"wp_update_themes\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}}i:1672243383;a:1:{s:26:\"action_scheduler_run_queue\";a:1:{s:32:\"0d04ed39571b55704c122d726248bbac\";a:3:{s:8:\"schedule\";s:12:\"every_minute\";s:4:\"args\";a:1:{i:0;s:7:\"WP Cron\";}s:8:\"interval\";i:60;}}}i:1672243384;a:1:{s:14:\"wc_admin_daily\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1672243394;a:3:{s:33:\"woocommerce_cleanup_personal_data\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}s:30:\"woocommerce_tracker_send_event\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}s:30:\"generate_category_lookup_table\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:2:{s:8:\"schedule\";b:0;s:4:\"args\";a:0:{}}}}i:1672243444;a:1:{s:25:\"woocommerce_geoip_updater\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:11:\"fifteendays\";s:4:\"args\";a:0:{}s:8:\"interval\";i:1296000;}}}i:1672246984;a:1:{s:32:\"woocommerce_cancel_unpaid_orders\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:2:{s:8:\"schedule\";b:0;s:4:\"args\";a:0:{}}}}i:1672254184;a:2:{s:24:\"woocommerce_cleanup_logs\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}s:31:\"woocommerce_cleanup_rate_limits\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1672264984;a:1:{s:28:\"woocommerce_cleanup_sessions\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}}i:1672272000;a:1:{s:27:\"woocommerce_scheduled_sales\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1672329769;a:1:{s:30:\"wp_site_health_scheduled_check\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"weekly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:604800;}}}s:7:\"version\";i:2;}','yes'),(106,'widget_pages','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(107,'widget_calendar','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(108,'widget_archives','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(109,'widget_media_audio','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(110,'widget_media_image','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(111,'widget_media_gallery','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(112,'widget_media_video','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(113,'widget_meta','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(114,'widget_search','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(115,'widget_recent-posts','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(116,'widget_recent-comments','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(117,'widget_tag_cloud','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(118,'widget_nav_menu','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(119,'widget_custom_html','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(120,'_transient_doing_cron','1672243370.0567181110382080078125','yes'),(122,'_site_transient_update_core','O:8:\"stdClass\":4:{s:7:\"updates\";a:1:{i:0;O:8:\"stdClass\":10:{s:8:\"response\";s:6:\"latest\";s:8:\"download\";s:59:\"https://downloads.wordpress.org/release/wordpress-6.1.1.zip\";s:6:\"locale\";s:5:\"en_US\";s:8:\"packages\";O:8:\"stdClass\":5:{s:4:\"full\";s:59:\"https://downloads.wordpress.org/release/wordpress-6.1.1.zip\";s:10:\"no_content\";s:70:\"https://downloads.wordpress.org/release/wordpress-6.1.1-no-content.zip\";s:11:\"new_bundled\";s:71:\"https://downloads.wordpress.org/release/wordpress-6.1.1-new-bundled.zip\";s:7:\"partial\";s:0:\"\";s:8:\"rollback\";s:0:\"\";}s:7:\"current\";s:5:\"6.1.1\";s:7:\"version\";s:5:\"6.1.1\";s:11:\"php_version\";s:6:\"5.6.20\";s:13:\"mysql_version\";s:3:\"5.0\";s:11:\"new_bundled\";s:3:\"6.1\";s:15:\"partial_version\";s:0:\"\";}}s:12:\"last_checked\";i:1672243380;s:15:\"version_checked\";s:5:\"6.1.1\";s:12:\"translations\";a:0:{}}','no'),(124,'_site_transient_timeout_theme_roots','1672245182','no'),(125,'_site_transient_theme_roots','a:3:{s:15:\"twentytwentyone\";s:7:\"/themes\";s:17:\"twentytwentythree\";s:7:\"/themes\";s:15:\"twentytwentytwo\";s:7:\"/themes\";}','no'),(126,'_site_transient_update_themes','O:8:\"stdClass\":5:{s:12:\"last_checked\";i:1672243395;s:7:\"checked\";a:3:{s:15:\"twentytwentyone\";s:3:\"1.7\";s:17:\"twentytwentythree\";s:3:\"1.0\";s:15:\"twentytwentytwo\";s:3:\"1.3\";}s:8:\"response\";a:0:{}s:9:\"no_update\";a:3:{s:15:\"twentytwentyone\";a:6:{s:5:\"theme\";s:15:\"twentytwentyone\";s:11:\"new_version\";s:3:\"1.7\";s:3:\"url\";s:45:\"https://wordpress.org/themes/twentytwentyone/\";s:7:\"package\";s:61:\"https://downloads.wordpress.org/theme/twentytwentyone.1.7.zip\";s:8:\"requires\";s:3:\"5.3\";s:12:\"requires_php\";s:3:\"5.6\";}s:17:\"twentytwentythree\";a:6:{s:5:\"theme\";s:17:\"twentytwentythree\";s:11:\"new_version\";s:3:\"1.0\";s:3:\"url\";s:47:\"https://wordpress.org/themes/twentytwentythree/\";s:7:\"package\";s:63:\"https://downloads.wordpress.org/theme/twentytwentythree.1.0.zip\";s:8:\"requires\";s:3:\"6.1\";s:12:\"requires_php\";s:3:\"5.6\";}s:15:\"twentytwentytwo\";a:6:{s:5:\"theme\";s:15:\"twentytwentytwo\";s:11:\"new_version\";s:3:\"1.3\";s:3:\"url\";s:45:\"https://wordpress.org/themes/twentytwentytwo/\";s:7:\"package\";s:61:\"https://downloads.wordpress.org/theme/twentytwentytwo.1.3.zip\";s:8:\"requires\";s:3:\"5.9\";s:12:\"requires_php\";s:3:\"5.6\";}}s:12:\"translations\";a:0:{}}','no'),(127,'action_scheduler_hybrid_store_demarkation','4','yes'),(128,'schema-ActionScheduler_StoreSchema','6.0.1672243383','yes'),(129,'schema-ActionScheduler_LoggerSchema','3.0.1672243383','yes'),(130,'_transient_timeout_as-post-store-dependencies-met','1672329783','no'),(131,'_transient_as-post-store-dependencies-met','yes','no'),(134,'woocommerce_schema_version','430','yes'),(135,'woocommerce_store_address','','yes'),(136,'woocommerce_store_address_2','','yes'),(137,'woocommerce_store_city','','yes'),(138,'woocommerce_default_country','US:CA','yes'),(139,'woocommerce_store_postcode','','yes'),(140,'woocommerce_allowed_countries','all','yes'),(141,'woocommerce_all_except_countries','','yes'),(142,'woocommerce_specific_allowed_countries','','yes'),(143,'woocommerce_ship_to_countries','','yes'),(144,'woocommerce_specific_ship_to_countries','','yes'),(145,'woocommerce_default_customer_address','base','yes'),(146,'woocommerce_calc_taxes','no','yes'),(147,'woocommerce_enable_coupons','yes','yes'),(148,'woocommerce_calc_discounts_sequentially','no','no'),(149,'woocommerce_currency','USD','yes'),(150,'woocommerce_currency_pos','left','yes'),(151,'woocommerce_price_thousand_sep',',','yes'),(152,'woocommerce_price_decimal_sep','.','yes'),(153,'woocommerce_price_num_decimals','2','yes'),(154,'woocommerce_shop_page_id','5','yes'),(155,'woocommerce_cart_redirect_after_add','no','yes'),(156,'woocommerce_enable_ajax_add_to_cart','yes','yes'),(157,'woocommerce_placeholder_image','4','yes'),(158,'woocommerce_weight_unit','kg','yes'),(159,'woocommerce_dimension_unit','cm','yes'),(160,'woocommerce_enable_reviews','yes','yes'),(161,'woocommerce_review_rating_verification_label','yes','no'),(162,'woocommerce_review_rating_verification_required','no','no'),(163,'woocommerce_enable_review_rating','yes','yes'),(164,'woocommerce_review_rating_required','yes','no'),(165,'woocommerce_manage_stock','yes','yes'),(166,'woocommerce_hold_stock_minutes','60','no'),(167,'woocommerce_notify_low_stock','yes','no'),(168,'woocommerce_notify_no_stock','yes','no'),(169,'woocommerce_stock_email_recipient','manik@pluginever.com','no'),(170,'woocommerce_notify_low_stock_amount','2','no'),(171,'woocommerce_notify_no_stock_amount','0','yes'),(172,'woocommerce_hide_out_of_stock_items','no','yes'),(173,'woocommerce_stock_format','','yes'),(174,'woocommerce_file_download_method','force','no'),(175,'woocommerce_downloads_redirect_fallback_allowed','no','no'),(176,'woocommerce_downloads_require_login','no','no'),(177,'woocommerce_downloads_grant_access_after_payment','yes','no'),(178,'woocommerce_downloads_deliver_inline','','no'),(179,'woocommerce_downloads_add_hash_to_filename','yes','yes'),(180,'woocommerce_attribute_lookup_enabled','no','yes'),(181,'woocommerce_attribute_lookup_direct_updates','no','yes'),(182,'woocommerce_prices_include_tax','no','yes'),(183,'woocommerce_tax_based_on','shipping','yes'),(184,'woocommerce_shipping_tax_class','inherit','yes'),(185,'woocommerce_tax_round_at_subtotal','no','yes'),(186,'woocommerce_tax_classes','','yes'),(187,'woocommerce_tax_display_shop','excl','yes'),(188,'woocommerce_tax_display_cart','excl','yes'),(189,'woocommerce_price_display_suffix','','yes'),(190,'woocommerce_tax_total_display','itemized','no'),(191,'woocommerce_enable_shipping_calc','yes','no'),(192,'woocommerce_shipping_cost_requires_address','no','yes'),(193,'woocommerce_ship_to_destination','billing','no'),(194,'woocommerce_shipping_debug_mode','no','yes'),(195,'woocommerce_enable_guest_checkout','yes','no'),(196,'woocommerce_enable_checkout_login_reminder','no','no'),(197,'woocommerce_enable_signup_and_login_from_checkout','no','no'),(198,'woocommerce_enable_myaccount_registration','no','no'),(199,'woocommerce_registration_generate_username','yes','no'),(200,'woocommerce_registration_generate_password','yes','no'),(201,'woocommerce_erasure_request_removes_order_data','no','no'),(202,'woocommerce_erasure_request_removes_download_data','no','no'),(203,'woocommerce_allow_bulk_remove_personal_data','no','no'),(204,'woocommerce_registration_privacy_policy_text','Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our [privacy_policy].','yes'),(205,'woocommerce_checkout_privacy_policy_text','Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our [privacy_policy].','yes'),(206,'woocommerce_delete_inactive_accounts','a:2:{s:6:\"number\";s:0:\"\";s:4:\"unit\";s:6:\"months\";}','no'),(207,'woocommerce_trash_pending_orders','','no'),(208,'woocommerce_trash_failed_orders','','no'),(209,'woocommerce_trash_cancelled_orders','','no'),(210,'woocommerce_anonymize_completed_orders','a:2:{s:6:\"number\";s:0:\"\";s:4:\"unit\";s:6:\"months\";}','no'),(211,'woocommerce_email_from_name','Acceptance Tests','no'),(212,'woocommerce_email_from_address','manik@pluginever.com','no'),(213,'woocommerce_email_header_image','','no'),(214,'woocommerce_email_footer_text','{site_title} &mdash; Built with {WooCommerce}','no'),(215,'woocommerce_email_base_color','#7f54b3','no'),(216,'woocommerce_email_background_color','#f7f7f7','no'),(217,'woocommerce_email_body_background_color','#ffffff','no'),(218,'woocommerce_email_text_color','#3c3c3c','no'),(219,'woocommerce_merchant_email_notifications','no','no'),(220,'woocommerce_cart_page_id','6','no'),(221,'woocommerce_checkout_page_id','7','no'),(222,'woocommerce_myaccount_page_id','8','no'),(223,'woocommerce_terms_page_id','','no'),(224,'woocommerce_force_ssl_checkout','no','yes'),(225,'woocommerce_unforce_ssl_checkout','no','yes'),(226,'woocommerce_checkout_pay_endpoint','order-pay','yes'),(227,'woocommerce_checkout_order_received_endpoint','order-received','yes'),(228,'woocommerce_myaccount_add_payment_method_endpoint','add-payment-method','yes'),(229,'woocommerce_myaccount_delete_payment_method_endpoint','delete-payment-method','yes'),(230,'woocommerce_myaccount_set_default_payment_method_endpoint','set-default-payment-method','yes'),(231,'woocommerce_myaccount_orders_endpoint','orders','yes'),(232,'woocommerce_myaccount_view_order_endpoint','view-order','yes'),(233,'woocommerce_myaccount_downloads_endpoint','downloads','yes'),(234,'woocommerce_myaccount_edit_account_endpoint','edit-account','yes'),(235,'woocommerce_myaccount_edit_address_endpoint','edit-address','yes'),(236,'woocommerce_myaccount_payment_methods_endpoint','payment-methods','yes'),(237,'woocommerce_myaccount_lost_password_endpoint','lost-password','yes'),(238,'woocommerce_logout_endpoint','customer-logout','yes'),(239,'woocommerce_api_enabled','no','yes'),(240,'woocommerce_allow_tracking','no','no'),(241,'woocommerce_show_marketplace_suggestions','yes','no'),(242,'woocommerce_analytics_enabled','yes','yes'),(243,'woocommerce_navigation_enabled','no','yes'),(244,'woocommerce_feature_custom_order_tables_enabled','no','yes'),(245,'woocommerce_single_image_width','600','yes'),(246,'woocommerce_thumbnail_image_width','300','yes'),(247,'woocommerce_checkout_highlight_required_fields','yes','yes'),(248,'woocommerce_demo_store','no','no'),(249,'wc_downloads_approved_directories_mode','enabled','yes'),(250,'woocommerce_permalinks','a:5:{s:12:\"product_base\";s:7:\"product\";s:13:\"category_base\";s:16:\"product-category\";s:8:\"tag_base\";s:11:\"product-tag\";s:14:\"attribute_base\";s:0:\"\";s:22:\"use_verbose_page_rules\";b:0;}','yes'),(251,'current_theme_supports_woocommerce','yes','yes'),(252,'woocommerce_queue_flush_rewrite_rules','no','yes'),(253,'_transient_wc_attribute_taxonomies','a:0:{}','yes'),(257,'default_product_cat','15','yes'),(258,'woocommerce_refund_returns_page_created','9','no'),(259,'woocommerce_refund_returns_page_id','9','yes'),(260,'_transient_timeout__wc_activation_redirect','1672243414','no'),(261,'_transient__wc_activation_redirect','1','no'),(262,'woocommerce_paypal_settings','a:23:{s:7:\"enabled\";s:2:\"no\";s:5:\"title\";s:6:\"PayPal\";s:11:\"description\";s:85:\"Pay via PayPal; you can pay with your credit card if you don\'t have a PayPal account.\";s:5:\"email\";s:20:\"manik@pluginever.com\";s:8:\"advanced\";s:0:\"\";s:8:\"testmode\";s:2:\"no\";s:5:\"debug\";s:2:\"no\";s:16:\"ipn_notification\";s:3:\"yes\";s:14:\"receiver_email\";s:20:\"manik@pluginever.com\";s:14:\"identity_token\";s:0:\"\";s:14:\"invoice_prefix\";s:3:\"WC-\";s:13:\"send_shipping\";s:3:\"yes\";s:16:\"address_override\";s:2:\"no\";s:13:\"paymentaction\";s:4:\"sale\";s:9:\"image_url\";s:0:\"\";s:11:\"api_details\";s:0:\"\";s:12:\"api_username\";s:0:\"\";s:12:\"api_password\";s:0:\"\";s:13:\"api_signature\";s:0:\"\";s:20:\"sandbox_api_username\";s:0:\"\";s:20:\"sandbox_api_password\";s:0:\"\";s:21:\"sandbox_api_signature\";s:0:\"\";s:12:\"_should_load\";s:2:\"no\";}','yes'),(263,'woocommerce_version','7.2.2','yes'),(264,'woocommerce_db_version','7.2.2','yes'),(265,'woocommerce_admin_install_timestamp','1672243384','yes'),(266,'woocommerce_inbox_variant_assignment','12','yes'),(267,'_transient_timeout__woocommerce_upload_directory_status','1672329784','no'),(268,'_transient__woocommerce_upload_directory_status','protected','no'),(269,'_transient_woocommerce_activated_plugin','woocommerce/woocommerce.php','yes'),(270,'_transient_jetpack_autoloader_plugin_paths','a:1:{i:0;s:60:\"/private/tmp/acceptance-tests/wp-content/plugins/woocommerce\";}','yes'),(271,'woocommerce_admin_notices','a:2:{i:0;s:20:\"no_secure_connection\";i:1;s:14:\"template_files\";}','yes'),(272,'woocommerce_maxmind_geolocation_settings','a:1:{s:15:\"database_prefix\";s:32:\"fbtfNOtt7QYRZdN71pVxEI9FSUmZAa3v\";}','yes'),(273,'_transient_woocommerce_webhook_ids_status_active','a:0:{}','yes'),(274,'widget_woocommerce_widget_cart','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(275,'widget_woocommerce_layered_nav_filters','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(276,'widget_woocommerce_layered_nav','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(277,'widget_woocommerce_price_filter','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(278,'widget_woocommerce_product_categories','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(279,'widget_woocommerce_product_search','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(280,'widget_woocommerce_product_tag_cloud','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(281,'widget_woocommerce_products','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(282,'widget_woocommerce_recently_viewed_products','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(283,'widget_woocommerce_top_rated_products','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(284,'widget_woocommerce_recent_reviews','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(285,'widget_woocommerce_rating_filter','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(286,'_transient_timeout__woocommerce_helper_subscriptions','1672244287','no'),(287,'_transient__woocommerce_helper_subscriptions','a:0:{}','no'),(288,'_transient_timeout__woocommerce_helper_updates','1672286587','no'),(289,'_transient__woocommerce_helper_updates','a:4:{s:4:\"hash\";s:32:\"d751713988987e9331980363e24189ce\";s:7:\"updated\";i:1672243387;s:8:\"products\";a:0:{}s:6:\"errors\";a:1:{i:0;s:10:\"http-error\";}}','no'),(292,'_transient_product_query-transient-version','1672243397','yes'),(293,'category_children','a:0:{}','yes'),(294,'product_cat_children','a:0:{}','yes'),(295,'recently_activated','a:3:{s:27:\"woocommerce/woocommerce.php\";i:1672243418;s:41:\"wordpress-importer/wordpress-importer.php\";i:1672243417;i:0;b:0;}','yes');
/*!40000 ALTER TABLE `wp_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_postmeta`
--

DROP TABLE IF EXISTS `wp_postmeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_postmeta` (
  `meta_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`meta_id`),
  KEY `post_id` (`post_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB AUTO_INCREMENT=973 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_postmeta`
--

LOCK TABLES `wp_postmeta` WRITE;
/*!40000 ALTER TABLE `wp_postmeta` DISABLE KEYS */;
INSERT INTO `wp_postmeta` VALUES (1,2,'_wp_page_template','default'),(2,3,'_wp_page_template','default'),(3,4,'_wp_attached_file','woocommerce-placeholder.png'),(4,4,'_wp_attachment_metadata','a:6:{s:5:\"width\";i:1200;s:6:\"height\";i:1200;s:4:\"file\";s:27:\"woocommerce-placeholder.png\";s:8:\"filesize\";i:102644;s:5:\"sizes\";a:4:{s:6:\"medium\";a:5:{s:4:\"file\";s:35:\"woocommerce-placeholder-300x300.png\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:9:\"image/png\";s:8:\"filesize\";i:12475;}s:5:\"large\";a:5:{s:4:\"file\";s:37:\"woocommerce-placeholder-1024x1024.png\";s:5:\"width\";i:1024;s:6:\"height\";i:1024;s:9:\"mime-type\";s:9:\"image/png\";s:8:\"filesize\";i:98202;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:35:\"woocommerce-placeholder-150x150.png\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:9:\"image/png\";s:8:\"filesize\";i:4204;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:35:\"woocommerce-placeholder-768x768.png\";s:5:\"width\";i:768;s:6:\"height\";i:768;s:9:\"mime-type\";s:9:\"image/png\";s:8:\"filesize\";i:60014;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),(5,10,'_sku','woo-vneck-tee'),(6,10,'_sale_price_dates_from',''),(7,10,'_sale_price_dates_to',''),(8,10,'total_sales','0'),(9,10,'_tax_status','taxable'),(10,10,'_tax_class',''),(11,10,'_manage_stock','no'),(12,10,'_backorders','no'),(13,10,'_low_stock_amount',''),(14,10,'_sold_individually','no'),(15,10,'_weight','0.5'),(16,10,'_length','24'),(17,10,'_width','1'),(18,10,'_height','2'),(19,10,'_upsell_ids','a:0:{}'),(20,10,'_crosssell_ids','a:0:{}'),(21,10,'_purchase_note',''),(22,10,'_default_attributes','a:0:{}'),(23,10,'_virtual','no'),(24,10,'_downloadable','no'),(25,10,'_product_image_gallery','32,33'),(26,10,'_download_limit','0'),(27,10,'_download_expiry','0'),(28,10,'_stock',''),(29,10,'_stock_status','instock'),(30,10,'_wc_average_rating','0'),(31,10,'_wc_rating_count','a:0:{}'),(32,10,'_wc_review_count','0'),(33,10,'_downloadable_files','a:0:{}'),(34,10,'_product_attributes','a:2:{s:8:\"pa_color\";a:6:{s:4:\"name\";s:8:\"pa_color\";s:5:\"value\";s:0:\"\";s:8:\"position\";i:0;s:10:\"is_visible\";i:1;s:12:\"is_variation\";i:1;s:11:\"is_taxonomy\";i:1;}s:7:\"pa_size\";a:6:{s:4:\"name\";s:7:\"pa_size\";s:5:\"value\";s:0:\"\";s:8:\"position\";i:1;s:10:\"is_visible\";i:1;s:12:\"is_variation\";i:1;s:11:\"is_taxonomy\";i:1;}}'),(35,10,'_product_version','3.5.3'),(36,10,'_thumbnail_id','35'),(37,10,'_price','15'),(38,10,'_price','20'),(39,10,'_regular_price',''),(40,10,'_sale_price',''),(41,11,'_sku','woo-hoodie'),(42,11,'_sale_price_dates_from',''),(43,11,'_sale_price_dates_to',''),(44,11,'total_sales','0'),(45,11,'_tax_status','taxable'),(46,11,'_tax_class',''),(47,11,'_manage_stock','no'),(48,11,'_backorders','no'),(49,11,'_low_stock_amount',''),(50,11,'_sold_individually','no'),(51,11,'_weight','1.5'),(52,11,'_length','10'),(53,11,'_width','8'),(54,11,'_height','3'),(55,11,'_upsell_ids','a:0:{}'),(56,11,'_crosssell_ids','a:0:{}'),(57,11,'_purchase_note',''),(58,11,'_default_attributes','a:0:{}'),(59,11,'_virtual','no'),(60,11,'_downloadable','no'),(61,11,'_product_image_gallery','35,36,37'),(62,11,'_download_limit','0'),(63,11,'_download_expiry','0'),(64,11,'_stock',''),(65,11,'_stock_status','instock'),(66,11,'_wc_average_rating','0'),(67,11,'_wc_rating_count','a:0:{}'),(68,11,'_wc_review_count','0'),(69,11,'_downloadable_files','a:0:{}'),(70,11,'_product_attributes','a:2:{s:8:\"pa_color\";a:6:{s:4:\"name\";s:8:\"pa_color\";s:5:\"value\";s:0:\"\";s:8:\"position\";i:0;s:10:\"is_visible\";i:1;s:12:\"is_variation\";i:1;s:11:\"is_taxonomy\";i:1;}s:4:\"logo\";a:6:{s:4:\"name\";s:4:\"Logo\";s:5:\"value\";s:8:\"Yes | No\";s:8:\"position\";i:1;s:10:\"is_visible\";i:1;s:12:\"is_variation\";i:1;s:11:\"is_taxonomy\";i:0;}}'),(71,11,'_product_version','3.5.3'),(72,11,'_thumbnail_id','38'),(73,11,'_price','42'),(74,11,'_price','45'),(75,11,'_regular_price',''),(76,11,'_sale_price',''),(77,12,'_sku','woo-hoodie-with-logo'),(78,12,'_regular_price','45'),(79,12,'_sale_price',''),(80,12,'_sale_price_dates_from',''),(81,12,'_sale_price_dates_to',''),(82,12,'total_sales','0'),(83,12,'_tax_status','taxable'),(84,12,'_tax_class',''),(85,12,'_manage_stock','no'),(86,12,'_backorders','no'),(87,12,'_low_stock_amount',''),(88,12,'_sold_individually','no'),(89,12,'_weight','2'),(90,12,'_length','10'),(91,12,'_width','6'),(92,12,'_height','3'),(93,12,'_upsell_ids','a:0:{}'),(94,12,'_crosssell_ids','a:0:{}'),(95,12,'_purchase_note',''),(96,12,'_default_attributes','a:0:{}'),(97,12,'_virtual','no'),(98,12,'_downloadable','no'),(99,12,'_product_image_gallery',''),(100,12,'_download_limit','0'),(101,12,'_download_expiry','0'),(102,12,'_stock',''),(103,12,'_stock_status','instock'),(104,12,'_wc_average_rating','0'),(105,12,'_wc_rating_count','a:0:{}'),(106,12,'_wc_review_count','0'),(107,12,'_downloadable_files','a:0:{}'),(108,12,'_product_attributes','a:1:{s:8:\"pa_color\";a:6:{s:4:\"name\";s:8:\"pa_color\";s:5:\"value\";s:0:\"\";s:8:\"position\";i:0;s:10:\"is_visible\";i:1;s:12:\"is_variation\";i:0;s:11:\"is_taxonomy\";i:1;}}'),(109,12,'_product_version','3.5.3'),(110,12,'_price','45'),(111,12,'_thumbnail_id','41'),(112,13,'_sku','woo-tshirt'),(113,13,'_regular_price','18'),(114,13,'_sale_price',''),(115,13,'_sale_price_dates_from',''),(116,13,'_sale_price_dates_to',''),(117,13,'total_sales','0'),(118,13,'_tax_status','taxable'),(119,13,'_tax_class',''),(120,13,'_manage_stock','no'),(121,13,'_backorders','no'),(122,13,'_low_stock_amount',''),(123,13,'_sold_individually','no'),(124,13,'_weight','0.8'),(125,13,'_length','8'),(126,13,'_width','6'),(127,13,'_height','1'),(128,13,'_upsell_ids','a:0:{}'),(129,13,'_crosssell_ids','a:0:{}'),(130,13,'_purchase_note',''),(131,13,'_default_attributes','a:0:{}'),(132,13,'_virtual','no'),(133,13,'_downloadable','no'),(134,13,'_product_image_gallery',''),(135,13,'_download_limit','0'),(136,13,'_download_expiry','0'),(137,13,'_stock',''),(138,13,'_stock_status','instock'),(139,13,'_wc_average_rating','0'),(140,13,'_wc_rating_count','a:0:{}'),(141,13,'_wc_review_count','0'),(142,13,'_downloadable_files','a:0:{}'),(143,13,'_product_attributes','a:1:{s:8:\"pa_color\";a:6:{s:4:\"name\";s:8:\"pa_color\";s:5:\"value\";s:0:\"\";s:8:\"position\";i:0;s:10:\"is_visible\";i:1;s:12:\"is_variation\";i:0;s:11:\"is_taxonomy\";i:1;}}'),(144,13,'_product_version','3.5.3'),(145,13,'_price','18'),(146,13,'_thumbnail_id','42'),(147,14,'_sku','woo-beanie'),(148,14,'_regular_price','20'),(149,14,'_sale_price','18'),(150,14,'_sale_price_dates_from',''),(151,14,'_sale_price_dates_to',''),(152,14,'total_sales','0'),(153,14,'_tax_status','taxable'),(154,14,'_tax_class',''),(155,14,'_manage_stock','no'),(156,14,'_backorders','no'),(157,14,'_low_stock_amount',''),(158,14,'_sold_individually','no'),(159,14,'_weight','0.2'),(160,14,'_length','4'),(161,14,'_width','5'),(162,14,'_height','0.5'),(163,14,'_upsell_ids','a:0:{}'),(164,14,'_crosssell_ids','a:0:{}'),(165,14,'_purchase_note',''),(166,14,'_default_attributes','a:0:{}'),(167,14,'_virtual','no'),(168,14,'_downloadable','no'),(169,14,'_product_image_gallery',''),(170,14,'_download_limit','0'),(171,14,'_download_expiry','0'),(172,14,'_stock',''),(173,14,'_stock_status','instock'),(174,14,'_wc_average_rating','0'),(175,14,'_wc_rating_count','a:0:{}'),(176,14,'_wc_review_count','0'),(177,14,'_downloadable_files','a:0:{}'),(178,14,'_product_attributes','a:1:{s:8:\"pa_color\";a:6:{s:4:\"name\";s:8:\"pa_color\";s:5:\"value\";s:0:\"\";s:8:\"position\";i:0;s:10:\"is_visible\";i:1;s:12:\"is_variation\";i:0;s:11:\"is_taxonomy\";i:1;}}'),(179,14,'_product_version','3.5.3'),(180,14,'_price','18'),(181,14,'_thumbnail_id','43'),(182,15,'_sku','woo-belt'),(183,15,'_regular_price','65'),(184,15,'_sale_price','55'),(185,15,'_sale_price_dates_from',''),(186,15,'_sale_price_dates_to',''),(187,15,'total_sales','0'),(188,15,'_tax_status','taxable'),(189,15,'_tax_class',''),(190,15,'_manage_stock','no'),(191,15,'_backorders','no'),(192,15,'_low_stock_amount',''),(193,15,'_sold_individually','no'),(194,15,'_weight','1.2'),(195,15,'_length','12'),(196,15,'_width','2'),(197,15,'_height','1.5'),(198,15,'_upsell_ids','a:0:{}'),(199,15,'_crosssell_ids','a:0:{}'),(200,15,'_purchase_note',''),(201,15,'_default_attributes','a:0:{}'),(202,15,'_virtual','no'),(203,15,'_downloadable','no'),(204,15,'_product_image_gallery',''),(205,15,'_download_limit','0'),(206,15,'_download_expiry','0'),(207,15,'_stock',''),(208,15,'_stock_status','instock'),(209,15,'_wc_average_rating','0'),(210,15,'_wc_rating_count','a:0:{}'),(211,15,'_wc_review_count','0'),(212,15,'_downloadable_files','a:0:{}'),(213,15,'_product_attributes','a:0:{}'),(214,15,'_product_version','3.5.3'),(215,15,'_price','55'),(216,15,'_thumbnail_id','44'),(217,16,'_sku','woo-cap'),(218,16,'_regular_price','18'),(219,16,'_sale_price','16'),(220,16,'_sale_price_dates_from',''),(221,16,'_sale_price_dates_to',''),(222,16,'total_sales','0'),(223,16,'_tax_status','taxable'),(224,16,'_tax_class',''),(225,16,'_manage_stock','no'),(226,16,'_backorders','no'),(227,16,'_low_stock_amount',''),(228,16,'_sold_individually','no'),(229,16,'_weight','0.6'),(230,16,'_length','8'),(231,16,'_width','6.5'),(232,16,'_height','4'),(233,16,'_upsell_ids','a:0:{}'),(234,16,'_crosssell_ids','a:0:{}'),(235,16,'_purchase_note',''),(236,16,'_default_attributes','a:0:{}'),(237,16,'_virtual','no'),(238,16,'_downloadable','no'),(239,16,'_product_image_gallery',''),(240,16,'_download_limit','0'),(241,16,'_download_expiry','0'),(242,16,'_stock',''),(243,16,'_stock_status','instock'),(244,16,'_wc_average_rating','0'),(245,16,'_wc_rating_count','a:0:{}'),(246,16,'_wc_review_count','0'),(247,16,'_downloadable_files','a:0:{}'),(248,16,'_product_attributes','a:1:{s:8:\"pa_color\";a:6:{s:4:\"name\";s:8:\"pa_color\";s:5:\"value\";s:0:\"\";s:8:\"position\";i:0;s:10:\"is_visible\";i:1;s:12:\"is_variation\";i:0;s:11:\"is_taxonomy\";i:1;}}'),(249,16,'_product_version','3.5.3'),(250,16,'_price','16'),(251,16,'_thumbnail_id','45'),(252,17,'_sku','woo-sunglasses'),(253,17,'_regular_price','90'),(254,17,'_sale_price',''),(255,17,'_sale_price_dates_from',''),(256,17,'_sale_price_dates_to',''),(257,17,'total_sales','0'),(258,17,'_tax_status','taxable'),(259,17,'_tax_class',''),(260,17,'_manage_stock','no'),(261,17,'_backorders','no'),(262,17,'_low_stock_amount',''),(263,17,'_sold_individually','no'),(264,17,'_weight','0.2'),(265,17,'_length','4'),(266,17,'_width','1.4'),(267,17,'_height','1'),(268,17,'_upsell_ids','a:0:{}'),(269,17,'_crosssell_ids','a:0:{}'),(270,17,'_purchase_note',''),(271,17,'_default_attributes','a:0:{}'),(272,17,'_virtual','no'),(273,17,'_downloadable','no'),(274,17,'_product_image_gallery',''),(275,17,'_download_limit','0'),(276,17,'_download_expiry','0'),(277,17,'_stock',''),(278,17,'_stock_status','instock'),(279,17,'_wc_average_rating','0'),(280,17,'_wc_rating_count','a:0:{}'),(281,17,'_wc_review_count','0'),(282,17,'_downloadable_files','a:0:{}'),(283,17,'_product_attributes','a:0:{}'),(284,17,'_product_version','3.5.3'),(285,17,'_price','90'),(286,17,'_thumbnail_id','46'),(287,18,'_sku','woo-hoodie-with-pocket'),(288,18,'_regular_price','45'),(289,18,'_sale_price','35'),(290,18,'_sale_price_dates_from',''),(291,18,'_sale_price_dates_to',''),(292,18,'total_sales','0'),(293,18,'_tax_status','taxable'),(294,18,'_tax_class',''),(295,18,'_manage_stock','no'),(296,18,'_backorders','no'),(297,18,'_low_stock_amount',''),(298,18,'_sold_individually','no'),(299,18,'_weight','3'),(300,18,'_length','10'),(301,18,'_width','8'),(302,18,'_height','2'),(303,18,'_upsell_ids','a:0:{}'),(304,18,'_crosssell_ids','a:0:{}'),(305,18,'_purchase_note',''),(306,18,'_default_attributes','a:0:{}'),(307,18,'_virtual','no'),(308,18,'_downloadable','no'),(309,18,'_product_image_gallery',''),(310,18,'_download_limit','0'),(311,18,'_download_expiry','0'),(312,18,'_stock',''),(313,18,'_stock_status','instock'),(314,18,'_wc_average_rating','0'),(315,18,'_wc_rating_count','a:0:{}'),(316,18,'_wc_review_count','0'),(317,18,'_downloadable_files','a:0:{}'),(318,18,'_product_attributes','a:1:{s:8:\"pa_color\";a:6:{s:4:\"name\";s:8:\"pa_color\";s:5:\"value\";s:0:\"\";s:8:\"position\";i:0;s:10:\"is_visible\";i:1;s:12:\"is_variation\";i:0;s:11:\"is_taxonomy\";i:1;}}'),(319,18,'_product_version','3.5.3'),(320,18,'_price','35'),(321,18,'_thumbnail_id','47'),(322,19,'_sku','woo-hoodie-with-zipper'),(323,19,'_regular_price','45'),(324,19,'_sale_price',''),(325,19,'_sale_price_dates_from',''),(326,19,'_sale_price_dates_to',''),(327,19,'total_sales','0'),(328,19,'_tax_status','taxable'),(329,19,'_tax_class',''),(330,19,'_manage_stock','no'),(331,19,'_backorders','no'),(332,19,'_low_stock_amount',''),(333,19,'_sold_individually','no'),(334,19,'_weight','2'),(335,19,'_length','8'),(336,19,'_width','6'),(337,19,'_height','2'),(338,19,'_upsell_ids','a:0:{}'),(339,19,'_crosssell_ids','a:0:{}'),(340,19,'_purchase_note',''),(341,19,'_default_attributes','a:0:{}'),(342,19,'_virtual','no'),(343,19,'_downloadable','no'),(344,19,'_product_image_gallery',''),(345,19,'_download_limit','0'),(346,19,'_download_expiry','0'),(347,19,'_stock',''),(348,19,'_stock_status','instock'),(349,19,'_wc_average_rating','0'),(350,19,'_wc_rating_count','a:0:{}'),(351,19,'_wc_review_count','0'),(352,19,'_downloadable_files','a:0:{}'),(353,19,'_product_attributes','a:0:{}'),(354,19,'_product_version','3.5.3'),(355,19,'_price','45'),(356,19,'_thumbnail_id','48'),(357,20,'_sku','woo-long-sleeve-tee'),(358,20,'_regular_price','25'),(359,20,'_sale_price',''),(360,20,'_sale_price_dates_from',''),(361,20,'_sale_price_dates_to',''),(362,20,'total_sales','0'),(363,20,'_tax_status','taxable'),(364,20,'_tax_class',''),(365,20,'_manage_stock','no'),(366,20,'_backorders','no'),(367,20,'_low_stock_amount',''),(368,20,'_sold_individually','no'),(369,20,'_weight','1'),(370,20,'_length','7'),(371,20,'_width','5'),(372,20,'_height','1'),(373,20,'_upsell_ids','a:0:{}'),(374,20,'_crosssell_ids','a:0:{}'),(375,20,'_purchase_note',''),(376,20,'_default_attributes','a:0:{}'),(377,20,'_virtual','no'),(378,20,'_downloadable','no'),(379,20,'_product_image_gallery',''),(380,20,'_download_limit','0'),(381,20,'_download_expiry','0'),(382,20,'_stock',''),(383,20,'_stock_status','instock'),(384,20,'_wc_average_rating','0'),(385,20,'_wc_rating_count','a:0:{}'),(386,20,'_wc_review_count','0'),(387,20,'_downloadable_files','a:0:{}'),(388,20,'_product_attributes','a:1:{s:8:\"pa_color\";a:6:{s:4:\"name\";s:8:\"pa_color\";s:5:\"value\";s:0:\"\";s:8:\"position\";i:0;s:10:\"is_visible\";i:1;s:12:\"is_variation\";i:0;s:11:\"is_taxonomy\";i:1;}}'),(389,20,'_product_version','3.5.3'),(390,20,'_price','25'),(391,20,'_thumbnail_id','49'),(392,21,'_sku','woo-polo'),(393,21,'_regular_price','20'),(394,21,'_sale_price',''),(395,21,'_sale_price_dates_from',''),(396,21,'_sale_price_dates_to',''),(397,21,'total_sales','0'),(398,21,'_tax_status','taxable'),(399,21,'_tax_class',''),(400,21,'_manage_stock','no'),(401,21,'_backorders','no'),(402,21,'_low_stock_amount',''),(403,21,'_sold_individually','no'),(404,21,'_weight','0.8'),(405,21,'_length','6'),(406,21,'_width','5'),(407,21,'_height','1'),(408,21,'_upsell_ids','a:0:{}'),(409,21,'_crosssell_ids','a:0:{}'),(410,21,'_purchase_note',''),(411,21,'_default_attributes','a:0:{}'),(412,21,'_virtual','no'),(413,21,'_downloadable','no'),(414,21,'_product_image_gallery',''),(415,21,'_download_limit','0'),(416,21,'_download_expiry','0'),(417,21,'_stock',''),(418,21,'_stock_status','instock'),(419,21,'_wc_average_rating','0'),(420,21,'_wc_rating_count','a:0:{}'),(421,21,'_wc_review_count','0'),(422,21,'_downloadable_files','a:0:{}'),(423,21,'_product_attributes','a:1:{s:8:\"pa_color\";a:6:{s:4:\"name\";s:8:\"pa_color\";s:5:\"value\";s:0:\"\";s:8:\"position\";i:0;s:10:\"is_visible\";i:1;s:12:\"is_variation\";i:0;s:11:\"is_taxonomy\";i:1;}}'),(424,21,'_product_version','3.5.3'),(425,21,'_price','20'),(426,21,'_thumbnail_id','50'),(427,22,'_sku','woo-album'),(428,22,'_regular_price','15'),(429,22,'_sale_price',''),(430,22,'_sale_price_dates_from',''),(431,22,'_sale_price_dates_to',''),(432,22,'total_sales','0'),(433,22,'_tax_status','taxable'),(434,22,'_tax_class',''),(435,22,'_manage_stock','no'),(436,22,'_backorders','no'),(437,22,'_low_stock_amount',''),(438,22,'_sold_individually','no'),(439,22,'_weight',''),(440,22,'_length',''),(441,22,'_width',''),(442,22,'_height',''),(443,22,'_upsell_ids','a:0:{}'),(444,22,'_crosssell_ids','a:0:{}'),(445,22,'_purchase_note',''),(446,22,'_default_attributes','a:0:{}'),(447,22,'_virtual','yes'),(448,22,'_downloadable','yes'),(449,22,'_product_image_gallery',''),(450,22,'_download_limit','1'),(451,22,'_download_expiry','1'),(452,22,'_stock',''),(453,22,'_stock_status','instock'),(454,22,'_wc_average_rating','0'),(455,22,'_wc_rating_count','a:0:{}'),(456,22,'_wc_review_count','0'),(457,22,'_downloadable_files','a:2:{s:36:\"356506a5-cc15-41b9-801b-9104dda1702c\";a:3:{s:2:\"id\";s:36:\"356506a5-cc15-41b9-801b-9104dda1702c\";s:4:\"name\";s:8:\"Single 1\";s:4:\"file\";s:85:\"https://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2017/08/single.jpg\";}s:36:\"18e70c59-59f3-43a3-8525-ce1ea0c12943\";a:3:{s:2:\"id\";s:36:\"18e70c59-59f3-43a3-8525-ce1ea0c12943\";s:4:\"name\";s:8:\"Single 2\";s:4:\"file\";s:84:\"https://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2017/08/album.jpg\";}}'),(458,22,'_product_attributes','a:0:{}'),(459,22,'_product_version','3.5.3'),(460,22,'_price','15'),(461,22,'_thumbnail_id','51'),(462,23,'_sku','woo-single'),(463,23,'_regular_price','3'),(464,23,'_sale_price','2'),(465,23,'_sale_price_dates_from',''),(466,23,'_sale_price_dates_to',''),(467,23,'total_sales','0'),(468,23,'_tax_status','taxable'),(469,23,'_tax_class',''),(470,23,'_manage_stock','no'),(471,23,'_backorders','no'),(472,23,'_low_stock_amount',''),(473,23,'_sold_individually','no'),(474,23,'_weight',''),(475,23,'_length',''),(476,23,'_width',''),(477,23,'_height',''),(478,23,'_upsell_ids','a:0:{}'),(479,23,'_crosssell_ids','a:0:{}'),(480,23,'_purchase_note',''),(481,23,'_default_attributes','a:0:{}'),(482,23,'_virtual','yes'),(483,23,'_downloadable','yes'),(484,23,'_product_image_gallery',''),(485,23,'_download_limit','1'),(486,23,'_download_expiry','1'),(487,23,'_stock',''),(488,23,'_stock_status','instock'),(489,23,'_wc_average_rating','0'),(490,23,'_wc_rating_count','a:0:{}'),(491,23,'_wc_review_count','0'),(492,23,'_downloadable_files','a:1:{s:36:\"a0fdda89-5f0e-440d-93f5-188e12c910d1\";a:3:{s:2:\"id\";s:36:\"a0fdda89-5f0e-440d-93f5-188e12c910d1\";s:4:\"name\";s:6:\"Single\";s:4:\"file\";s:85:\"https://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2017/08/single.jpg\";}}'),(493,23,'_product_attributes','a:0:{}'),(494,23,'_product_version','3.5.3'),(495,23,'_price','2'),(496,23,'_thumbnail_id','52'),(497,24,'_sku','woo-vneck-tee-red'),(498,24,'_regular_price','20'),(499,24,'_sale_price',''),(500,24,'_sale_price_dates_from',''),(501,24,'_sale_price_dates_to',''),(502,24,'total_sales','0'),(503,24,'_tax_status','taxable'),(504,24,'_tax_class',''),(505,24,'_manage_stock','no'),(506,24,'_backorders','no'),(507,24,'_low_stock_amount',''),(508,24,'_sold_individually','no'),(509,24,'_weight',''),(510,24,'_length',''),(511,24,'_width',''),(512,24,'_height',''),(513,24,'_upsell_ids','a:0:{}'),(514,24,'_crosssell_ids','a:0:{}'),(515,24,'_purchase_note',''),(516,24,'_default_attributes','a:0:{}'),(517,24,'_virtual','no'),(518,24,'_downloadable','no'),(519,24,'_product_image_gallery',''),(520,24,'_download_limit','0'),(521,24,'_download_expiry','0'),(522,24,'_stock',''),(523,24,'_stock_status','instock'),(524,24,'_wc_average_rating','0'),(525,24,'_wc_rating_count','a:0:{}'),(526,24,'_wc_review_count','0'),(527,24,'_downloadable_files','a:0:{}'),(528,24,'_product_attributes','a:0:{}'),(529,24,'_product_version','3.5.3'),(530,24,'_price','20'),(531,24,'_variation_description','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum sagittis orci ac odio dictum tincidunt. Donec ut metus leo. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Sed luctus, dui eu sagittis sodales, nulla nibh sagittis augue, vel porttitor diam enim non metus. Vestibulum aliquam augue neque. Phasellus tincidunt odio eget ullamcorper efficitur. Cras placerat ut turpis pellentesque vulputate. Nam sed consequat tortor. Curabitur finibus sapien dolor. Ut eleifend tellus nec erat pulvinar dignissim. Nam non arcu purus. Vivamus et massa massa.'),(532,24,'_thumbnail_id','35'),(533,24,'attribute_pa_color','red'),(534,24,'attribute_pa_size',''),(535,25,'_sku','woo-vneck-tee-green'),(536,25,'_regular_price','20'),(537,25,'_sale_price',''),(538,25,'_sale_price_dates_from',''),(539,25,'_sale_price_dates_to',''),(540,25,'total_sales','0'),(541,25,'_tax_status','taxable'),(542,25,'_tax_class',''),(543,25,'_manage_stock','no'),(544,25,'_backorders','no'),(545,25,'_low_stock_amount',''),(546,25,'_sold_individually','no'),(547,25,'_weight',''),(548,25,'_length',''),(549,25,'_width',''),(550,25,'_height',''),(551,25,'_upsell_ids','a:0:{}'),(552,25,'_crosssell_ids','a:0:{}'),(553,25,'_purchase_note',''),(554,25,'_default_attributes','a:0:{}'),(555,25,'_virtual','no'),(556,25,'_downloadable','no'),(557,25,'_product_image_gallery',''),(558,25,'_download_limit','0'),(559,25,'_download_expiry','0'),(560,25,'_stock',''),(561,25,'_stock_status','instock'),(562,25,'_wc_average_rating','0'),(563,25,'_wc_rating_count','a:0:{}'),(564,25,'_wc_review_count','0'),(565,25,'_downloadable_files','a:0:{}'),(566,25,'_product_attributes','a:0:{}'),(567,25,'_product_version','3.5.3'),(568,25,'_price','20'),(569,25,'_variation_description','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum sagittis orci ac odio dictum tincidunt. Donec ut metus leo. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Sed luctus, dui eu sagittis sodales, nulla nibh sagittis augue, vel porttitor diam enim non metus. Vestibulum aliquam augue neque. Phasellus tincidunt odio eget ullamcorper efficitur. Cras placerat ut turpis pellentesque vulputate. Nam sed consequat tortor. Curabitur finibus sapien dolor. Ut eleifend tellus nec erat pulvinar dignissim. Nam non arcu purus. Vivamus et massa massa.'),(570,25,'_thumbnail_id','36'),(571,25,'attribute_pa_color','green'),(572,25,'attribute_pa_size',''),(573,26,'_sku','woo-vneck-tee-blue'),(574,26,'_regular_price','15'),(575,26,'_sale_price',''),(576,26,'_sale_price_dates_from',''),(577,26,'_sale_price_dates_to',''),(578,26,'total_sales','0'),(579,26,'_tax_status','taxable'),(580,26,'_tax_class',''),(581,26,'_manage_stock','no'),(582,26,'_backorders','no'),(583,26,'_low_stock_amount',''),(584,26,'_sold_individually','no'),(585,26,'_weight',''),(586,26,'_length',''),(587,26,'_width',''),(588,26,'_height',''),(589,26,'_upsell_ids','a:0:{}'),(590,26,'_crosssell_ids','a:0:{}'),(591,26,'_purchase_note',''),(592,26,'_default_attributes','a:0:{}'),(593,26,'_virtual','no'),(594,26,'_downloadable','no'),(595,26,'_product_image_gallery',''),(596,26,'_download_limit','0'),(597,26,'_download_expiry','0'),(598,26,'_stock',''),(599,26,'_stock_status','instock'),(600,26,'_wc_average_rating','0'),(601,26,'_wc_rating_count','a:0:{}'),(602,26,'_wc_review_count','0'),(603,26,'_downloadable_files','a:0:{}'),(604,26,'_product_attributes','a:0:{}'),(605,26,'_product_version','3.5.3'),(606,26,'_price','15'),(607,26,'_wpcom_is_markdown',''),(608,26,'_wp_old_slug','import-placeholder-for-78'),(609,26,'_variation_description','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum sagittis orci ac odio dictum tincidunt. Donec ut metus leo. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Sed luctus, dui eu sagittis sodales, nulla nibh sagittis augue, vel porttitor diam enim non metus. Vestibulum aliquam augue neque. Phasellus tincidunt odio eget ullamcorper efficitur. Cras placerat ut turpis pellentesque vulputate. Nam sed consequat tortor. Curabitur finibus sapien dolor. Ut eleifend tellus nec erat pulvinar dignissim. Nam non arcu purus. Vivamus et massa massa.'),(610,26,'_thumbnail_id','37'),(611,26,'attribute_pa_color','blue'),(612,26,'attribute_pa_size',''),(613,27,'_sku','woo-hoodie-red'),(614,27,'_regular_price','45'),(615,27,'_sale_price','42'),(616,27,'_sale_price_dates_from',''),(617,27,'_sale_price_dates_to',''),(618,27,'total_sales','0'),(619,27,'_tax_status','taxable'),(620,27,'_tax_class',''),(621,27,'_manage_stock','no'),(622,27,'_backorders','no'),(623,27,'_low_stock_amount',''),(624,27,'_sold_individually','no'),(625,27,'_weight',''),(626,27,'_length',''),(627,27,'_width',''),(628,27,'_height',''),(629,27,'_upsell_ids','a:0:{}'),(630,27,'_crosssell_ids','a:0:{}'),(631,27,'_purchase_note',''),(632,27,'_default_attributes','a:0:{}'),(633,27,'_virtual','no'),(634,27,'_downloadable','no'),(635,27,'_product_image_gallery',''),(636,27,'_download_limit','0'),(637,27,'_download_expiry','0'),(638,27,'_stock',''),(639,27,'_stock_status','instock'),(640,27,'_wc_average_rating','0'),(641,27,'_wc_rating_count','a:0:{}'),(642,27,'_wc_review_count','0'),(643,27,'_downloadable_files','a:0:{}'),(644,27,'_product_attributes','a:0:{}'),(645,27,'_product_version','3.5.3'),(646,27,'_price','42'),(647,27,'_variation_description','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum sagittis orci ac odio dictum tincidunt. Donec ut metus leo. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Sed luctus, dui eu sagittis sodales, nulla nibh sagittis augue, vel porttitor diam enim non metus. Vestibulum aliquam augue neque. Phasellus tincidunt odio eget ullamcorper efficitur. Cras placerat ut turpis pellentesque vulputate. Nam sed consequat tortor. Curabitur finibus sapien dolor. Ut eleifend tellus nec erat pulvinar dignissim. Nam non arcu purus. Vivamus et massa massa.'),(648,27,'_thumbnail_id','38'),(649,27,'attribute_pa_color','red'),(650,27,'attribute_logo','No'),(651,28,'_sku','woo-hoodie-green'),(652,28,'_regular_price','45'),(653,28,'_sale_price',''),(654,28,'_sale_price_dates_from',''),(655,28,'_sale_price_dates_to',''),(656,28,'total_sales','0'),(657,28,'_tax_status','taxable'),(658,28,'_tax_class',''),(659,28,'_manage_stock','no'),(660,28,'_backorders','no'),(661,28,'_low_stock_amount',''),(662,28,'_sold_individually','no'),(663,28,'_weight',''),(664,28,'_length',''),(665,28,'_width',''),(666,28,'_height',''),(667,28,'_upsell_ids','a:0:{}'),(668,28,'_crosssell_ids','a:0:{}'),(669,28,'_purchase_note',''),(670,28,'_default_attributes','a:0:{}'),(671,28,'_virtual','no'),(672,28,'_downloadable','no'),(673,28,'_product_image_gallery',''),(674,28,'_download_limit','0'),(675,28,'_download_expiry','0'),(676,28,'_stock',''),(677,28,'_stock_status','instock'),(678,28,'_wc_average_rating','0'),(679,28,'_wc_rating_count','a:0:{}'),(680,28,'_wc_review_count','0'),(681,28,'_downloadable_files','a:0:{}'),(682,28,'_product_attributes','a:0:{}'),(683,28,'_product_version','3.5.3'),(684,28,'_price','45'),(685,28,'_variation_description','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum sagittis orci ac odio dictum tincidunt. Donec ut metus leo. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Sed luctus, dui eu sagittis sodales, nulla nibh sagittis augue, vel porttitor diam enim non metus. Vestibulum aliquam augue neque. Phasellus tincidunt odio eget ullamcorper efficitur. Cras placerat ut turpis pellentesque vulputate. Nam sed consequat tortor. Curabitur finibus sapien dolor. Ut eleifend tellus nec erat pulvinar dignissim. Nam non arcu purus. Vivamus et massa massa.'),(686,28,'_thumbnail_id','40'),(687,28,'attribute_pa_color','green'),(688,28,'attribute_logo','No'),(689,29,'_sku','woo-hoodie-blue'),(690,29,'_regular_price','45'),(691,29,'_sale_price',''),(692,29,'_sale_price_dates_from',''),(693,29,'_sale_price_dates_to',''),(694,29,'total_sales','0'),(695,29,'_tax_status','taxable'),(696,29,'_tax_class',''),(697,29,'_manage_stock','no'),(698,29,'_backorders','no'),(699,29,'_low_stock_amount',''),(700,29,'_sold_individually','no'),(701,29,'_weight',''),(702,29,'_length',''),(703,29,'_width',''),(704,29,'_height',''),(705,29,'_upsell_ids','a:0:{}'),(706,29,'_crosssell_ids','a:0:{}'),(707,29,'_purchase_note',''),(708,29,'_default_attributes','a:0:{}'),(709,29,'_virtual','no'),(710,29,'_downloadable','no'),(711,29,'_product_image_gallery',''),(712,29,'_download_limit','0'),(713,29,'_download_expiry','0'),(714,29,'_stock',''),(715,29,'_stock_status','instock'),(716,29,'_wc_average_rating','0'),(717,29,'_wc_rating_count','a:0:{}'),(718,29,'_wc_review_count','0'),(719,29,'_downloadable_files','a:0:{}'),(720,29,'_product_attributes','a:0:{}'),(721,29,'_product_version','3.5.3'),(722,29,'_price','45'),(723,29,'_variation_description','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum sagittis orci ac odio dictum tincidunt. Donec ut metus leo. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Sed luctus, dui eu sagittis sodales, nulla nibh sagittis augue, vel porttitor diam enim non metus. Vestibulum aliquam augue neque. Phasellus tincidunt odio eget ullamcorper efficitur. Cras placerat ut turpis pellentesque vulputate. Nam sed consequat tortor. Curabitur finibus sapien dolor. Ut eleifend tellus nec erat pulvinar dignissim. Nam non arcu purus. Vivamus et massa massa.'),(724,29,'_thumbnail_id','39'),(725,29,'attribute_pa_color','blue'),(726,29,'attribute_logo','No'),(727,30,'_sku','Woo-tshirt-logo'),(728,30,'_regular_price','18'),(729,30,'_sale_price',''),(730,30,'_sale_price_dates_from',''),(731,30,'_sale_price_dates_to',''),(732,30,'total_sales','0'),(733,30,'_tax_status','taxable'),(734,30,'_tax_class',''),(735,30,'_manage_stock','no'),(736,30,'_backorders','no'),(737,30,'_low_stock_amount',''),(738,30,'_sold_individually','no'),(739,30,'_weight','0.5'),(740,30,'_length','10'),(741,30,'_width','12'),(742,30,'_height','0.5'),(743,30,'_upsell_ids','a:0:{}'),(744,30,'_crosssell_ids','a:0:{}'),(745,30,'_purchase_note',''),(746,30,'_default_attributes','a:0:{}'),(747,30,'_virtual','no'),(748,30,'_downloadable','no'),(749,30,'_product_image_gallery',''),(750,30,'_download_limit','0'),(751,30,'_download_expiry','0'),(752,30,'_stock',''),(753,30,'_stock_status','instock'),(754,30,'_wc_average_rating','0'),(755,30,'_wc_rating_count','a:0:{}'),(756,30,'_wc_review_count','0'),(757,30,'_downloadable_files','a:0:{}'),(758,30,'_product_attributes','a:1:{s:8:\"pa_color\";a:6:{s:4:\"name\";s:8:\"pa_color\";s:5:\"value\";s:0:\"\";s:8:\"position\";i:0;s:10:\"is_visible\";i:1;s:12:\"is_variation\";i:0;s:11:\"is_taxonomy\";i:1;}}'),(759,30,'_product_version','3.5.3'),(760,30,'_price','18'),(761,30,'_thumbnail_id','53'),(762,31,'_sku','Woo-beanie-logo'),(763,31,'_regular_price','20'),(764,31,'_sale_price','18'),(765,31,'_sale_price_dates_from',''),(766,31,'_sale_price_dates_to',''),(767,31,'total_sales','0'),(768,31,'_tax_status','taxable'),(769,31,'_tax_class',''),(770,31,'_manage_stock','no'),(771,31,'_backorders','no'),(772,31,'_low_stock_amount',''),(773,31,'_sold_individually','no'),(774,31,'_weight','0.2'),(775,31,'_length','6'),(776,31,'_width','4'),(777,31,'_height','1'),(778,31,'_upsell_ids','a:0:{}'),(779,31,'_crosssell_ids','a:0:{}'),(780,31,'_purchase_note',''),(781,31,'_default_attributes','a:0:{}'),(782,31,'_virtual','no'),(783,31,'_downloadable','no'),(784,31,'_product_image_gallery',''),(785,31,'_download_limit','0'),(786,31,'_download_expiry','0'),(787,31,'_stock',''),(788,31,'_stock_status','instock'),(789,31,'_wc_average_rating','0'),(790,31,'_wc_rating_count','a:0:{}'),(791,31,'_wc_review_count','0'),(792,31,'_downloadable_files','a:0:{}'),(793,31,'_product_attributes','a:1:{s:8:\"pa_color\";a:6:{s:4:\"name\";s:8:\"pa_color\";s:5:\"value\";s:0:\"\";s:8:\"position\";i:0;s:10:\"is_visible\";i:1;s:12:\"is_variation\";i:0;s:11:\"is_taxonomy\";i:1;}}'),(794,31,'_product_version','3.5.3'),(795,31,'_price','18'),(796,31,'_thumbnail_id','54'),(797,32,'_sku','logo-collection'),(798,32,'_sale_price_dates_from',''),(799,32,'_sale_price_dates_to',''),(800,32,'total_sales','0'),(801,32,'_tax_status','taxable'),(802,32,'_tax_class',''),(803,32,'_manage_stock','no'),(804,32,'_backorders','no'),(805,32,'_low_stock_amount',''),(806,32,'_sold_individually','no'),(807,32,'_weight',''),(808,32,'_length',''),(809,32,'_width',''),(810,32,'_height',''),(811,32,'_upsell_ids','a:0:{}'),(812,32,'_crosssell_ids','a:0:{}'),(813,32,'_purchase_note',''),(814,32,'_default_attributes','a:0:{}'),(815,32,'_virtual','no'),(816,32,'_downloadable','no'),(817,32,'_product_image_gallery','50,49,37'),(818,32,'_download_limit','0'),(819,32,'_download_expiry','0'),(820,32,'_stock',''),(821,32,'_stock_status','instock'),(822,32,'_wc_average_rating','0'),(823,32,'_wc_rating_count','a:0:{}'),(824,32,'_wc_review_count','0'),(825,32,'_downloadable_files','a:0:{}'),(826,32,'_product_attributes','a:0:{}'),(827,32,'_product_version','3.5.3'),(828,32,'_children','a:3:{i:0;i:8;i:1;i:9;i:2;i:10;}'),(829,32,'_thumbnail_id','55'),(830,32,'_price','18'),(831,32,'_price','45'),(832,33,'_sku','wp-pennant'),(833,33,'_regular_price','11.05'),(834,33,'_sale_price',''),(835,33,'_sale_price_dates_from',''),(836,33,'_sale_price_dates_to',''),(837,33,'total_sales','0'),(838,33,'_tax_status','taxable'),(839,33,'_tax_class',''),(840,33,'_manage_stock','no'),(841,33,'_backorders','no'),(842,33,'_low_stock_amount',''),(843,33,'_sold_individually','no'),(844,33,'_weight',''),(845,33,'_length',''),(846,33,'_width',''),(847,33,'_height',''),(848,33,'_upsell_ids','a:0:{}'),(849,33,'_crosssell_ids','a:0:{}'),(850,33,'_purchase_note',''),(851,33,'_default_attributes','a:0:{}'),(852,33,'_virtual','no'),(853,33,'_downloadable','no'),(854,33,'_product_image_gallery',''),(855,33,'_download_limit','0'),(856,33,'_download_expiry','0'),(857,33,'_stock',''),(858,33,'_stock_status','instock'),(859,33,'_wc_average_rating','0'),(860,33,'_wc_rating_count','a:0:{}'),(861,33,'_wc_review_count','0'),(862,33,'_downloadable_files','a:0:{}'),(863,33,'_product_attributes','a:0:{}'),(864,33,'_product_version','3.5.3'),(865,33,'_price','11.05'),(866,33,'_thumbnail_id','56'),(867,33,'_product_url','https://mercantile.wordpress.org/product/wordpress-pennant/'),(868,33,'_button_text','Buy on the WordPress swag store!'),(869,34,'_sku','woo-hoodie-blue-logo'),(870,34,'_regular_price','45'),(871,34,'_sale_price',''),(872,34,'_sale_price_dates_from',''),(873,34,'_sale_price_dates_to',''),(874,34,'total_sales','0'),(875,34,'_tax_status','taxable'),(876,34,'_tax_class',''),(877,34,'_manage_stock','no'),(878,34,'_backorders','no'),(879,34,'_low_stock_amount',''),(880,34,'_sold_individually','no'),(881,34,'_weight',''),(882,34,'_length',''),(883,34,'_width',''),(884,34,'_height',''),(885,34,'_upsell_ids','a:0:{}'),(886,34,'_crosssell_ids','a:0:{}'),(887,34,'_purchase_note',''),(888,34,'_default_attributes','a:0:{}'),(889,34,'_virtual','no'),(890,34,'_downloadable','no'),(891,34,'_product_image_gallery',''),(892,34,'_download_limit','0'),(893,34,'_download_expiry','0'),(894,34,'_stock',''),(895,34,'_stock_status','instock'),(896,34,'_wc_average_rating','0'),(897,34,'_wc_rating_count','a:0:{}'),(898,34,'_wc_review_count','0'),(899,34,'_downloadable_files','a:0:{}'),(900,34,'_product_attributes','a:0:{}'),(901,34,'_product_version','3.5.3'),(902,34,'_price','45'),(903,34,'_variation_description','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum sagittis orci ac odio dictum tincidunt. Donec ut metus leo. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Sed luctus, dui eu sagittis sodales, nulla nibh sagittis augue, vel porttitor diam enim non metus. Vestibulum aliquam augue neque. Phasellus tincidunt odio eget ullamcorper efficitur. Cras placerat ut turpis pellentesque vulputate. Nam sed consequat tortor. Curabitur finibus sapien dolor. Ut eleifend tellus nec erat pulvinar dignissim. Nam non arcu purus. Vivamus et massa massa.'),(904,34,'_thumbnail_id','41'),(905,34,'attribute_pa_color','blue'),(906,34,'attribute_logo','Yes'),(907,35,'_wp_attached_file','2019/01/vneck-tee-2.jpg'),(908,35,'_wp_attachment_metadata','a:6:{s:5:\"width\";i:801;s:6:\"height\";i:800;s:4:\"file\";s:23:\"2019/01/vneck-tee-2.jpg\";s:8:\"filesize\";i:49497;s:5:\"sizes\";a:6:{s:6:\"medium\";a:5:{s:4:\"file\";s:23:\"vneck-tee-2-300x300.jpg\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:7858;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:23:\"vneck-tee-2-150x150.jpg\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:3136;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:23:\"vneck-tee-2-768x767.jpg\";s:5:\"width\";i:768;s:6:\"height\";i:767;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:29329;}s:21:\"woocommerce_thumbnail\";a:6:{s:4:\"file\";s:23:\"vneck-tee-2-450x450.jpg\";s:5:\"width\";i:450;s:6:\"height\";i:450;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:14021;s:9:\"uncropped\";b:0;}s:18:\"woocommerce_single\";a:5:{s:4:\"file\";s:23:\"vneck-tee-2-600x599.jpg\";s:5:\"width\";i:600;s:6:\"height\";i:599;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:20707;}s:29:\"woocommerce_gallery_thumbnail\";a:5:{s:4:\"file\";s:23:\"vneck-tee-2-100x100.jpg\";s:5:\"width\";i:100;s:6:\"height\";i:100;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:1982;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),(909,35,'_wc_attachment_source','https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/vneck-tee-2.jpg'),(910,36,'_wp_attached_file','2019/01/vnech-tee-green-1.jpg'),(911,36,'_wp_attachment_metadata','a:6:{s:5:\"width\";i:800;s:6:\"height\";i:800;s:4:\"file\";s:29:\"2019/01/vnech-tee-green-1.jpg\";s:8:\"filesize\";i:102362;s:5:\"sizes\";a:6:{s:6:\"medium\";a:5:{s:4:\"file\";s:29:\"vnech-tee-green-1-300x300.jpg\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:7291;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:29:\"vnech-tee-green-1-150x150.jpg\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:2835;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:29:\"vnech-tee-green-1-768x768.jpg\";s:5:\"width\";i:768;s:6:\"height\";i:768;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:28479;}s:21:\"woocommerce_thumbnail\";a:6:{s:4:\"file\";s:29:\"vnech-tee-green-1-450x450.jpg\";s:5:\"width\";i:450;s:6:\"height\";i:450;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:13394;s:9:\"uncropped\";b:0;}s:18:\"woocommerce_single\";a:5:{s:4:\"file\";s:29:\"vnech-tee-green-1-600x600.jpg\";s:5:\"width\";i:600;s:6:\"height\";i:600;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:20269;}s:29:\"woocommerce_gallery_thumbnail\";a:5:{s:4:\"file\";s:29:\"vnech-tee-green-1-100x100.jpg\";s:5:\"width\";i:100;s:6:\"height\";i:100;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:1814;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),(912,36,'_wc_attachment_source','https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/vnech-tee-green-1.jpg'),(913,37,'_wp_attached_file','2019/01/vnech-tee-blue-1.jpg'),(914,37,'_wp_attachment_metadata','a:6:{s:5:\"width\";i:800;s:6:\"height\";i:800;s:4:\"file\";s:28:\"2019/01/vnech-tee-blue-1.jpg\";s:8:\"filesize\";i:120226;s:5:\"sizes\";a:6:{s:6:\"medium\";a:5:{s:4:\"file\";s:28:\"vnech-tee-blue-1-300x300.jpg\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:7685;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:28:\"vnech-tee-blue-1-150x150.jpg\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:2987;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:28:\"vnech-tee-blue-1-768x768.jpg\";s:5:\"width\";i:768;s:6:\"height\";i:768;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:30155;}s:21:\"woocommerce_thumbnail\";a:6:{s:4:\"file\";s:28:\"vnech-tee-blue-1-450x450.jpg\";s:5:\"width\";i:450;s:6:\"height\";i:450;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:14199;s:9:\"uncropped\";b:0;}s:18:\"woocommerce_single\";a:5:{s:4:\"file\";s:28:\"vnech-tee-blue-1-600x600.jpg\";s:5:\"width\";i:600;s:6:\"height\";i:600;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:21442;}s:29:\"woocommerce_gallery_thumbnail\";a:5:{s:4:\"file\";s:28:\"vnech-tee-blue-1-100x100.jpg\";s:5:\"width\";i:100;s:6:\"height\";i:100;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:1879;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),(915,37,'_wc_attachment_source','https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/vnech-tee-blue-1.jpg'),(916,38,'_wp_attached_file','2019/01/hoodie-2.jpg'),(917,38,'_wp_attachment_metadata','a:6:{s:5:\"width\";i:801;s:6:\"height\";i:801;s:4:\"file\";s:20:\"2019/01/hoodie-2.jpg\";s:8:\"filesize\";i:46079;s:5:\"sizes\";a:6:{s:6:\"medium\";a:5:{s:4:\"file\";s:20:\"hoodie-2-300x300.jpg\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:7951;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:20:\"hoodie-2-150x150.jpg\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:3121;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:20:\"hoodie-2-768x768.jpg\";s:5:\"width\";i:768;s:6:\"height\";i:768;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:29085;}s:21:\"woocommerce_thumbnail\";a:6:{s:4:\"file\";s:20:\"hoodie-2-450x450.jpg\";s:5:\"width\";i:450;s:6:\"height\";i:450;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:14066;s:9:\"uncropped\";b:0;}s:18:\"woocommerce_single\";a:5:{s:4:\"file\";s:20:\"hoodie-2-600x600.jpg\";s:5:\"width\";i:600;s:6:\"height\";i:600;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:20490;}s:29:\"woocommerce_gallery_thumbnail\";a:5:{s:4:\"file\";s:20:\"hoodie-2-100x100.jpg\";s:5:\"width\";i:100;s:6:\"height\";i:100;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:1974;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),(918,38,'_wc_attachment_source','https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/hoodie-2.jpg'),(919,39,'_wp_attached_file','2019/01/hoodie-blue-1.jpg'),(920,39,'_wp_attachment_metadata','a:6:{s:5:\"width\";i:800;s:6:\"height\";i:800;s:4:\"file\";s:25:\"2019/01/hoodie-blue-1.jpg\";s:8:\"filesize\";i:101298;s:5:\"sizes\";a:6:{s:6:\"medium\";a:5:{s:4:\"file\";s:25:\"hoodie-blue-1-300x300.jpg\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:7690;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:25:\"hoodie-blue-1-150x150.jpg\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:2917;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:25:\"hoodie-blue-1-768x768.jpg\";s:5:\"width\";i:768;s:6:\"height\";i:768;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:29070;}s:21:\"woocommerce_thumbnail\";a:6:{s:4:\"file\";s:25:\"hoodie-blue-1-450x450.jpg\";s:5:\"width\";i:450;s:6:\"height\";i:450;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:13858;s:9:\"uncropped\";b:0;}s:18:\"woocommerce_single\";a:5:{s:4:\"file\";s:25:\"hoodie-blue-1-600x600.jpg\";s:5:\"width\";i:600;s:6:\"height\";i:600;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:20761;}s:29:\"woocommerce_gallery_thumbnail\";a:5:{s:4:\"file\";s:25:\"hoodie-blue-1-100x100.jpg\";s:5:\"width\";i:100;s:6:\"height\";i:100;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:1805;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),(921,39,'_wc_attachment_source','https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/hoodie-blue-1.jpg'),(922,40,'_wp_attached_file','2019/01/hoodie-green-1.jpg'),(923,40,'_wp_attachment_metadata','a:6:{s:5:\"width\";i:800;s:6:\"height\";i:800;s:4:\"file\";s:26:\"2019/01/hoodie-green-1.jpg\";s:8:\"filesize\";i:98498;s:5:\"sizes\";a:6:{s:6:\"medium\";a:5:{s:4:\"file\";s:26:\"hoodie-green-1-300x300.jpg\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:7576;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:26:\"hoodie-green-1-150x150.jpg\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:2902;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:26:\"hoodie-green-1-768x768.jpg\";s:5:\"width\";i:768;s:6:\"height\";i:768;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:28529;}s:21:\"woocommerce_thumbnail\";a:6:{s:4:\"file\";s:26:\"hoodie-green-1-450x450.jpg\";s:5:\"width\";i:450;s:6:\"height\";i:450;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:13550;s:9:\"uncropped\";b:0;}s:18:\"woocommerce_single\";a:5:{s:4:\"file\";s:26:\"hoodie-green-1-600x600.jpg\";s:5:\"width\";i:600;s:6:\"height\";i:600;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:20379;}s:29:\"woocommerce_gallery_thumbnail\";a:5:{s:4:\"file\";s:26:\"hoodie-green-1-100x100.jpg\";s:5:\"width\";i:100;s:6:\"height\";i:100;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:1823;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),(924,40,'_wc_attachment_source','https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/hoodie-green-1.jpg'),(925,41,'_wp_attached_file','2019/01/hoodie-with-logo-2.jpg'),(926,41,'_wp_attachment_metadata','a:6:{s:5:\"width\";i:801;s:6:\"height\";i:801;s:4:\"file\";s:30:\"2019/01/hoodie-with-logo-2.jpg\";s:8:\"filesize\";i:46969;s:5:\"sizes\";a:6:{s:6:\"medium\";a:5:{s:4:\"file\";s:30:\"hoodie-with-logo-2-300x300.jpg\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:8250;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:30:\"hoodie-with-logo-2-150x150.jpg\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:3091;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:30:\"hoodie-with-logo-2-768x768.jpg\";s:5:\"width\";i:768;s:6:\"height\";i:768;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:30122;}s:21:\"woocommerce_thumbnail\";a:6:{s:4:\"file\";s:30:\"hoodie-with-logo-2-450x450.jpg\";s:5:\"width\";i:450;s:6:\"height\";i:450;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:14733;s:9:\"uncropped\";b:0;}s:18:\"woocommerce_single\";a:5:{s:4:\"file\";s:30:\"hoodie-with-logo-2-600x600.jpg\";s:5:\"width\";i:600;s:6:\"height\";i:600;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:21582;}s:29:\"woocommerce_gallery_thumbnail\";a:5:{s:4:\"file\";s:30:\"hoodie-with-logo-2-100x100.jpg\";s:5:\"width\";i:100;s:6:\"height\";i:100;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:1913;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),(927,41,'_wc_attachment_source','https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/hoodie-with-logo-2.jpg'),(928,42,'_wp_attached_file','2019/01/tshirt-2.jpg'),(929,42,'_wp_attachment_metadata','a:6:{s:5:\"width\";i:801;s:6:\"height\";i:801;s:4:\"file\";s:20:\"2019/01/tshirt-2.jpg\";s:8:\"filesize\";i:41155;s:5:\"sizes\";a:6:{s:6:\"medium\";a:5:{s:4:\"file\";s:20:\"tshirt-2-300x300.jpg\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:7134;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:20:\"tshirt-2-150x150.jpg\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:2793;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:20:\"tshirt-2-768x768.jpg\";s:5:\"width\";i:768;s:6:\"height\";i:768;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:26448;}s:21:\"woocommerce_thumbnail\";a:6:{s:4:\"file\";s:20:\"tshirt-2-450x450.jpg\";s:5:\"width\";i:450;s:6:\"height\";i:450;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:12591;s:9:\"uncropped\";b:0;}s:18:\"woocommerce_single\";a:5:{s:4:\"file\";s:20:\"tshirt-2-600x600.jpg\";s:5:\"width\";i:600;s:6:\"height\";i:600;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:18800;}s:29:\"woocommerce_gallery_thumbnail\";a:5:{s:4:\"file\";s:20:\"tshirt-2-100x100.jpg\";s:5:\"width\";i:100;s:6:\"height\";i:100;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:1766;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),(930,42,'_wc_attachment_source','https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/tshirt-2.jpg'),(931,43,'_wp_attached_file','2019/01/beanie-2.jpg'),(932,43,'_wp_attachment_metadata','a:6:{s:5:\"width\";i:801;s:6:\"height\";i:801;s:4:\"file\";s:20:\"2019/01/beanie-2.jpg\";s:8:\"filesize\";i:31568;s:5:\"sizes\";a:6:{s:6:\"medium\";a:5:{s:4:\"file\";s:20:\"beanie-2-300x300.jpg\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:5695;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:20:\"beanie-2-150x150.jpg\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:2447;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:20:\"beanie-2-768x768.jpg\";s:5:\"width\";i:768;s:6:\"height\";i:768;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:21231;}s:21:\"woocommerce_thumbnail\";a:6:{s:4:\"file\";s:20:\"beanie-2-450x450.jpg\";s:5:\"width\";i:450;s:6:\"height\";i:450;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:9923;s:9:\"uncropped\";b:0;}s:18:\"woocommerce_single\";a:5:{s:4:\"file\";s:20:\"beanie-2-600x600.jpg\";s:5:\"width\";i:600;s:6:\"height\";i:600;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:15022;}s:29:\"woocommerce_gallery_thumbnail\";a:5:{s:4:\"file\";s:20:\"beanie-2-100x100.jpg\";s:5:\"width\";i:100;s:6:\"height\";i:100;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:1703;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),(933,43,'_wc_attachment_source','https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/beanie-2.jpg'),(934,44,'_wp_attached_file','2019/01/belt-2.jpg'),(935,44,'_wp_attachment_metadata','a:6:{s:5:\"width\";i:801;s:6:\"height\";i:801;s:4:\"file\";s:18:\"2019/01/belt-2.jpg\";s:8:\"filesize\";i:37339;s:5:\"sizes\";a:6:{s:6:\"medium\";a:5:{s:4:\"file\";s:18:\"belt-2-300x300.jpg\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:6738;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:18:\"belt-2-150x150.jpg\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:2681;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:18:\"belt-2-768x768.jpg\";s:5:\"width\";i:768;s:6:\"height\";i:768;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:25625;}s:21:\"woocommerce_thumbnail\";a:6:{s:4:\"file\";s:18:\"belt-2-450x450.jpg\";s:5:\"width\";i:450;s:6:\"height\";i:450;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:12111;s:9:\"uncropped\";b:0;}s:18:\"woocommerce_single\";a:5:{s:4:\"file\";s:18:\"belt-2-600x600.jpg\";s:5:\"width\";i:600;s:6:\"height\";i:600;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:17802;}s:29:\"woocommerce_gallery_thumbnail\";a:5:{s:4:\"file\";s:18:\"belt-2-100x100.jpg\";s:5:\"width\";i:100;s:6:\"height\";i:100;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:1713;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),(936,44,'_wc_attachment_source','https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/belt-2.jpg'),(937,45,'_wp_attached_file','2019/01/cap-2.jpg'),(938,45,'_wp_attachment_metadata','a:6:{s:5:\"width\";i:801;s:6:\"height\";i:801;s:4:\"file\";s:17:\"2019/01/cap-2.jpg\";s:8:\"filesize\";i:37675;s:5:\"sizes\";a:6:{s:6:\"medium\";a:5:{s:4:\"file\";s:17:\"cap-2-300x300.jpg\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:6656;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:17:\"cap-2-150x150.jpg\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:2559;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:17:\"cap-2-768x768.jpg\";s:5:\"width\";i:768;s:6:\"height\";i:768;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:25713;}s:21:\"woocommerce_thumbnail\";a:6:{s:4:\"file\";s:17:\"cap-2-450x450.jpg\";s:5:\"width\";i:450;s:6:\"height\";i:450;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:12271;s:9:\"uncropped\";b:0;}s:18:\"woocommerce_single\";a:5:{s:4:\"file\";s:17:\"cap-2-600x600.jpg\";s:5:\"width\";i:600;s:6:\"height\";i:600;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:17984;}s:29:\"woocommerce_gallery_thumbnail\";a:5:{s:4:\"file\";s:17:\"cap-2-100x100.jpg\";s:5:\"width\";i:100;s:6:\"height\";i:100;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:1654;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),(939,45,'_wc_attachment_source','https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/cap-2.jpg'),(940,46,'_wp_attached_file','2019/01/sunglasses-2.jpg'),(941,46,'_wp_attachment_metadata','a:6:{s:5:\"width\";i:801;s:6:\"height\";i:801;s:4:\"file\";s:24:\"2019/01/sunglasses-2.jpg\";s:8:\"filesize\";i:24691;s:5:\"sizes\";a:6:{s:6:\"medium\";a:5:{s:4:\"file\";s:24:\"sunglasses-2-300x300.jpg\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:5341;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:24:\"sunglasses-2-150x150.jpg\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:2242;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:24:\"sunglasses-2-768x768.jpg\";s:5:\"width\";i:768;s:6:\"height\";i:768;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:20643;}s:21:\"woocommerce_thumbnail\";a:6:{s:4:\"file\";s:24:\"sunglasses-2-450x450.jpg\";s:5:\"width\";i:450;s:6:\"height\";i:450;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:9630;s:9:\"uncropped\";b:0;}s:18:\"woocommerce_single\";a:5:{s:4:\"file\";s:24:\"sunglasses-2-600x600.jpg\";s:5:\"width\";i:600;s:6:\"height\";i:600;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:14479;}s:29:\"woocommerce_gallery_thumbnail\";a:5:{s:4:\"file\";s:24:\"sunglasses-2-100x100.jpg\";s:5:\"width\";i:100;s:6:\"height\";i:100;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:1509;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),(942,46,'_wc_attachment_source','https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/sunglasses-2.jpg'),(943,47,'_wp_attached_file','2019/01/hoodie-with-pocket-2.jpg'),(944,47,'_wp_attachment_metadata','a:6:{s:5:\"width\";i:801;s:6:\"height\";i:801;s:4:\"file\";s:32:\"2019/01/hoodie-with-pocket-2.jpg\";s:8:\"filesize\";i:43268;s:5:\"sizes\";a:6:{s:6:\"medium\";a:5:{s:4:\"file\";s:32:\"hoodie-with-pocket-2-300x300.jpg\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:7984;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:32:\"hoodie-with-pocket-2-150x150.jpg\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:3018;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:32:\"hoodie-with-pocket-2-768x768.jpg\";s:5:\"width\";i:768;s:6:\"height\";i:768;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:28839;}s:21:\"woocommerce_thumbnail\";a:6:{s:4:\"file\";s:32:\"hoodie-with-pocket-2-450x450.jpg\";s:5:\"width\";i:450;s:6:\"height\";i:450;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:13835;s:9:\"uncropped\";b:0;}s:18:\"woocommerce_single\";a:5:{s:4:\"file\";s:32:\"hoodie-with-pocket-2-600x600.jpg\";s:5:\"width\";i:600;s:6:\"height\";i:600;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:20472;}s:29:\"woocommerce_gallery_thumbnail\";a:5:{s:4:\"file\";s:32:\"hoodie-with-pocket-2-100x100.jpg\";s:5:\"width\";i:100;s:6:\"height\";i:100;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:1890;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),(945,47,'_wc_attachment_source','https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/hoodie-with-pocket-2.jpg'),(946,48,'_wp_attached_file','2019/01/hoodie-with-zipper-2.jpg'),(947,48,'_wp_attachment_metadata','a:6:{s:5:\"width\";i:800;s:6:\"height\";i:800;s:4:\"file\";s:32:\"2019/01/hoodie-with-zipper-2.jpg\";s:8:\"filesize\";i:56609;s:5:\"sizes\";a:6:{s:6:\"medium\";a:5:{s:4:\"file\";s:32:\"hoodie-with-zipper-2-300x300.jpg\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:9282;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:32:\"hoodie-with-zipper-2-150x150.jpg\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:3611;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:32:\"hoodie-with-zipper-2-768x768.jpg\";s:5:\"width\";i:768;s:6:\"height\";i:768;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:33937;}s:21:\"woocommerce_thumbnail\";a:6:{s:4:\"file\";s:32:\"hoodie-with-zipper-2-450x450.jpg\";s:5:\"width\";i:450;s:6:\"height\";i:450;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:16441;s:9:\"uncropped\";b:0;}s:18:\"woocommerce_single\";a:5:{s:4:\"file\";s:32:\"hoodie-with-zipper-2-600x600.jpg\";s:5:\"width\";i:600;s:6:\"height\";i:600;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:24427;}s:29:\"woocommerce_gallery_thumbnail\";a:5:{s:4:\"file\";s:32:\"hoodie-with-zipper-2-100x100.jpg\";s:5:\"width\";i:100;s:6:\"height\";i:100;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:2148;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),(948,48,'_wc_attachment_source','https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/hoodie-with-zipper-2.jpg'),(949,49,'_wp_attached_file','2019/01/long-sleeve-tee-2.jpg'),(950,49,'_wp_attachment_metadata','a:6:{s:5:\"width\";i:801;s:6:\"height\";i:801;s:4:\"file\";s:29:\"2019/01/long-sleeve-tee-2.jpg\";s:8:\"filesize\";i:51118;s:5:\"sizes\";a:6:{s:6:\"medium\";a:5:{s:4:\"file\";s:29:\"long-sleeve-tee-2-300x300.jpg\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:8080;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:29:\"long-sleeve-tee-2-150x150.jpg\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:3300;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:29:\"long-sleeve-tee-2-768x768.jpg\";s:5:\"width\";i:768;s:6:\"height\";i:768;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:29718;}s:21:\"woocommerce_thumbnail\";a:6:{s:4:\"file\";s:29:\"long-sleeve-tee-2-450x450.jpg\";s:5:\"width\";i:450;s:6:\"height\";i:450;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:14092;s:9:\"uncropped\";b:0;}s:18:\"woocommerce_single\";a:5:{s:4:\"file\";s:29:\"long-sleeve-tee-2-600x600.jpg\";s:5:\"width\";i:600;s:6:\"height\";i:600;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:20969;}s:29:\"woocommerce_gallery_thumbnail\";a:5:{s:4:\"file\";s:29:\"long-sleeve-tee-2-100x100.jpg\";s:5:\"width\";i:100;s:6:\"height\";i:100;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:1988;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),(951,49,'_wc_attachment_source','https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/long-sleeve-tee-2.jpg'),(952,50,'_wp_attached_file','2019/01/polo-2.jpg'),(953,50,'_wp_attachment_metadata','a:6:{s:5:\"width\";i:801;s:6:\"height\";i:800;s:4:\"file\";s:18:\"2019/01/polo-2.jpg\";s:8:\"filesize\";i:44409;s:5:\"sizes\";a:6:{s:6:\"medium\";a:5:{s:4:\"file\";s:18:\"polo-2-300x300.jpg\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:7279;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:18:\"polo-2-150x150.jpg\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:2867;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:18:\"polo-2-768x767.jpg\";s:5:\"width\";i:768;s:6:\"height\";i:767;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:27582;}s:21:\"woocommerce_thumbnail\";a:6:{s:4:\"file\";s:18:\"polo-2-450x450.jpg\";s:5:\"width\";i:450;s:6:\"height\";i:450;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:13208;s:9:\"uncropped\";b:0;}s:18:\"woocommerce_single\";a:5:{s:4:\"file\";s:18:\"polo-2-600x599.jpg\";s:5:\"width\";i:600;s:6:\"height\";i:599;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:19453;}s:29:\"woocommerce_gallery_thumbnail\";a:5:{s:4:\"file\";s:18:\"polo-2-100x100.jpg\";s:5:\"width\";i:100;s:6:\"height\";i:100;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:1814;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),(954,50,'_wc_attachment_source','https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/polo-2.jpg'),(955,51,'_wp_attached_file','2019/01/album-1.jpg'),(956,51,'_wp_attachment_metadata','a:6:{s:5:\"width\";i:800;s:6:\"height\";i:800;s:4:\"file\";s:19:\"2019/01/album-1.jpg\";s:8:\"filesize\";i:120010;s:5:\"sizes\";a:6:{s:6:\"medium\";a:5:{s:4:\"file\";s:19:\"album-1-300x300.jpg\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:9469;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:19:\"album-1-150x150.jpg\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:3661;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:19:\"album-1-768x768.jpg\";s:5:\"width\";i:768;s:6:\"height\";i:768;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:33648;}s:21:\"woocommerce_thumbnail\";a:6:{s:4:\"file\";s:19:\"album-1-450x450.jpg\";s:5:\"width\";i:450;s:6:\"height\";i:450;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:16339;s:9:\"uncropped\";b:0;}s:18:\"woocommerce_single\";a:5:{s:4:\"file\";s:19:\"album-1-600x600.jpg\";s:5:\"width\";i:600;s:6:\"height\";i:600;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:24374;}s:29:\"woocommerce_gallery_thumbnail\";a:5:{s:4:\"file\";s:19:\"album-1-100x100.jpg\";s:5:\"width\";i:100;s:6:\"height\";i:100;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:2219;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),(957,51,'_wc_attachment_source','https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2022/05/album-1.jpg'),(958,52,'_wp_attached_file','2019/01/single-1.jpg'),(959,52,'_wp_attachment_metadata','a:6:{s:5:\"width\";i:800;s:6:\"height\";i:800;s:4:\"file\";s:20:\"2019/01/single-1.jpg\";s:8:\"filesize\";i:124720;s:5:\"sizes\";a:6:{s:6:\"medium\";a:5:{s:4:\"file\";s:20:\"single-1-300x300.jpg\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:9579;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:20:\"single-1-150x150.jpg\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:3736;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:20:\"single-1-768x768.jpg\";s:5:\"width\";i:768;s:6:\"height\";i:768;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:34155;}s:21:\"woocommerce_thumbnail\";a:6:{s:4:\"file\";s:20:\"single-1-450x450.jpg\";s:5:\"width\";i:450;s:6:\"height\";i:450;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:16494;s:9:\"uncropped\";b:0;}s:18:\"woocommerce_single\";a:5:{s:4:\"file\";s:20:\"single-1-600x600.jpg\";s:5:\"width\";i:600;s:6:\"height\";i:600;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:24429;}s:29:\"woocommerce_gallery_thumbnail\";a:5:{s:4:\"file\";s:20:\"single-1-100x100.jpg\";s:5:\"width\";i:100;s:6:\"height\";i:100;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:2274;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),(960,52,'_wc_attachment_source','https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/single-1.jpg'),(961,53,'_wp_attached_file','2019/01/t-shirt-with-logo-1.jpg'),(962,53,'_wp_attachment_metadata','a:6:{s:5:\"width\";i:800;s:6:\"height\";i:800;s:4:\"file\";s:31:\"2019/01/t-shirt-with-logo-1.jpg\";s:8:\"filesize\";i:67833;s:5:\"sizes\";a:6:{s:6:\"medium\";a:5:{s:4:\"file\";s:31:\"t-shirt-with-logo-1-300x300.jpg\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:8141;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:31:\"t-shirt-with-logo-1-150x150.jpg\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:3143;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:31:\"t-shirt-with-logo-1-768x768.jpg\";s:5:\"width\";i:768;s:6:\"height\";i:768;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:30507;}s:21:\"woocommerce_thumbnail\";a:6:{s:4:\"file\";s:31:\"t-shirt-with-logo-1-450x450.jpg\";s:5:\"width\";i:450;s:6:\"height\";i:450;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:14700;s:9:\"uncropped\";b:0;}s:18:\"woocommerce_single\";a:5:{s:4:\"file\";s:31:\"t-shirt-with-logo-1-600x600.jpg\";s:5:\"width\";i:600;s:6:\"height\";i:600;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:21841;}s:29:\"woocommerce_gallery_thumbnail\";a:5:{s:4:\"file\";s:31:\"t-shirt-with-logo-1-100x100.jpg\";s:5:\"width\";i:100;s:6:\"height\";i:100;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:1963;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),(963,53,'_wc_attachment_source','https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/t-shirt-with-logo-1.jpg'),(964,54,'_wp_attached_file','2019/01/beanie-with-logo-1.jpg'),(965,54,'_wp_attachment_metadata','a:6:{s:5:\"width\";i:800;s:6:\"height\";i:800;s:4:\"file\";s:30:\"2019/01/beanie-with-logo-1.jpg\";s:8:\"filesize\";i:45371;s:5:\"sizes\";a:6:{s:6:\"medium\";a:5:{s:4:\"file\";s:30:\"beanie-with-logo-1-300x300.jpg\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:5814;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:30:\"beanie-with-logo-1-150x150.jpg\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:2428;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:30:\"beanie-with-logo-1-768x768.jpg\";s:5:\"width\";i:768;s:6:\"height\";i:768;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:21616;}s:21:\"woocommerce_thumbnail\";a:6:{s:4:\"file\";s:30:\"beanie-with-logo-1-450x450.jpg\";s:5:\"width\";i:450;s:6:\"height\";i:450;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:10311;s:9:\"uncropped\";b:0;}s:18:\"woocommerce_single\";a:5:{s:4:\"file\";s:30:\"beanie-with-logo-1-600x600.jpg\";s:5:\"width\";i:600;s:6:\"height\";i:600;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:15342;}s:29:\"woocommerce_gallery_thumbnail\";a:5:{s:4:\"file\";s:30:\"beanie-with-logo-1-100x100.jpg\";s:5:\"width\";i:100;s:6:\"height\";i:100;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:1672;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),(966,54,'_wc_attachment_source','https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/beanie-with-logo-1.jpg'),(967,55,'_wp_attached_file','2019/01/logo-1.jpg'),(968,55,'_wp_attachment_metadata','a:6:{s:5:\"width\";i:800;s:6:\"height\";i:799;s:4:\"file\";s:18:\"2019/01/logo-1.jpg\";s:8:\"filesize\";i:139907;s:5:\"sizes\";a:6:{s:6:\"medium\";a:5:{s:4:\"file\";s:18:\"logo-1-300x300.jpg\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:16171;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:18:\"logo-1-150x150.jpg\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:5876;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:18:\"logo-1-768x767.jpg\";s:5:\"width\";i:768;s:6:\"height\";i:767;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:56868;}s:21:\"woocommerce_thumbnail\";a:6:{s:4:\"file\";s:18:\"logo-1-450x450.jpg\";s:5:\"width\";i:450;s:6:\"height\";i:450;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:28386;s:9:\"uncropped\";b:0;}s:18:\"woocommerce_single\";a:5:{s:4:\"file\";s:18:\"logo-1-600x599.jpg\";s:5:\"width\";i:600;s:6:\"height\";i:599;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:41259;}s:29:\"woocommerce_gallery_thumbnail\";a:5:{s:4:\"file\";s:18:\"logo-1-100x100.jpg\";s:5:\"width\";i:100;s:6:\"height\";i:100;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:3353;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),(969,55,'_wc_attachment_source','https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/logo-1.jpg'),(970,56,'_wp_attached_file','2019/01/pennant-1.jpg'),(971,56,'_wp_attachment_metadata','a:6:{s:5:\"width\";i:800;s:6:\"height\";i:800;s:4:\"file\";s:21:\"2019/01/pennant-1.jpg\";s:8:\"filesize\";i:56755;s:5:\"sizes\";a:6:{s:6:\"medium\";a:5:{s:4:\"file\";s:21:\"pennant-1-300x300.jpg\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:6928;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:21:\"pennant-1-150x150.jpg\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:2581;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:21:\"pennant-1-768x768.jpg\";s:5:\"width\";i:768;s:6:\"height\";i:768;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:28253;}s:21:\"woocommerce_thumbnail\";a:6:{s:4:\"file\";s:21:\"pennant-1-450x450.jpg\";s:5:\"width\";i:450;s:6:\"height\";i:450;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:12984;s:9:\"uncropped\";b:0;}s:18:\"woocommerce_single\";a:5:{s:4:\"file\";s:21:\"pennant-1-600x600.jpg\";s:5:\"width\";i:600;s:6:\"height\";i:600;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:19854;}s:29:\"woocommerce_gallery_thumbnail\";a:5:{s:4:\"file\";s:21:\"pennant-1-100x100.jpg\";s:5:\"width\";i:100;s:6:\"height\";i:100;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:1608;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),(972,56,'_wc_attachment_source','https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/pennant-1.jpg');
/*!40000 ALTER TABLE `wp_postmeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_posts`
--

DROP TABLE IF EXISTS `wp_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_posts` (
  `ID` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_author` bigint unsigned NOT NULL DEFAULT '0',
  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_title` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_excerpt` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'publish',
  `comment_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'open',
  `ping_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'open',
  `post_password` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `post_name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `to_ping` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `pinged` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_modified_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content_filtered` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_parent` bigint unsigned NOT NULL DEFAULT '0',
  `guid` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `menu_order` int NOT NULL DEFAULT '0',
  `post_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'post',
  `post_mime_type` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_count` bigint NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `post_name` (`post_name`(191)),
  KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`ID`),
  KEY `post_parent` (`post_parent`),
  KEY `post_author` (`post_author`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_posts`
--

LOCK TABLES `wp_posts` WRITE;
/*!40000 ALTER TABLE `wp_posts` DISABLE KEYS */;
INSERT INTO `wp_posts` VALUES (1,1,'2022-12-28 16:02:49','2022-12-28 16:02:49','<!-- wp:paragraph -->\n<p>Welcome to WordPress. This is your first post. Edit or delete it, then start writing!</p>\n<!-- /wp:paragraph -->','Hello world!','','publish','open','open','','hello-world','','','2022-12-28 16:02:49','2022-12-28 16:02:49','',0,'http://acceptance-tests.test/?p=1',0,'post','',1),(2,1,'2022-12-28 16:02:49','2022-12-28 16:02:49','<!-- wp:paragraph -->\n<p>This is an example page. It\'s different from a blog post because it will stay in one place and will show up in your site navigation (in most themes). Most people start with an About page that introduces them to potential site visitors. It might say something like this:</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\"><p>Hi there! I\'m a bike messenger by day, aspiring actor by night, and this is my website. I live in Los Angeles, have a great dog named Jack, and I like pi&#241;a coladas. (And gettin\' caught in the rain.)</p></blockquote>\n<!-- /wp:quote -->\n\n<!-- wp:paragraph -->\n<p>...or something like this:</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\"><p>The XYZ Doohickey Company was founded in 1971, and has been providing quality doohickeys to the public ever since. Located in Gotham City, XYZ employs over 2,000 people and does all kinds of awesome things for the Gotham community.</p></blockquote>\n<!-- /wp:quote -->\n\n<!-- wp:paragraph -->\n<p>As a new WordPress user, you should go to <a href=\"http://acceptance-tests.test/wp-admin/\">your dashboard</a> to delete this page and create new pages for your content. Have fun!</p>\n<!-- /wp:paragraph -->','Sample Page','','publish','closed','open','','sample-page','','','2022-12-28 16:02:49','2022-12-28 16:02:49','',0,'http://acceptance-tests.test/?page_id=2',0,'page','',0),(3,1,'2022-12-28 16:02:49','2022-12-28 16:02:49','<!-- wp:heading --><h2>Who we are</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Suggested text: </strong>Our website address is: http://acceptance-tests.test.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Comments</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Suggested text: </strong>When visitors leave comments on the site we collect the data shown in the comments form, and also the visitor&#8217;s IP address and browser user agent string to help spam detection.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>An anonymized string created from your email address (also called a hash) may be provided to the Gravatar service to see if you are using it. The Gravatar service privacy policy is available here: https://automattic.com/privacy/. After approval of your comment, your profile picture is visible to the public in the context of your comment.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Media</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Suggested text: </strong>If you upload images to the website, you should avoid uploading images with embedded location data (EXIF GPS) included. Visitors to the website can download and extract any location data from images on the website.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Cookies</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Suggested text: </strong>If you leave a comment on our site you may opt-in to saving your name, email address and website in cookies. These are for your convenience so that you do not have to fill in your details again when you leave another comment. These cookies will last for one year.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>If you visit our login page, we will set a temporary cookie to determine if your browser accepts cookies. This cookie contains no personal data and is discarded when you close your browser.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>When you log in, we will also set up several cookies to save your login information and your screen display choices. Login cookies last for two days, and screen options cookies last for a year. If you select &quot;Remember Me&quot;, your login will persist for two weeks. If you log out of your account, the login cookies will be removed.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>If you edit or publish an article, an additional cookie will be saved in your browser. This cookie includes no personal data and simply indicates the post ID of the article you just edited. It expires after 1 day.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Embedded content from other websites</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Suggested text: </strong>Articles on this site may include embedded content (e.g. videos, images, articles, etc.). Embedded content from other websites behaves in the exact same way as if the visitor has visited the other website.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>These websites may collect data about you, use cookies, embed additional third-party tracking, and monitor your interaction with that embedded content, including tracking your interaction with the embedded content if you have an account and are logged in to that website.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Who we share your data with</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Suggested text: </strong>If you request a password reset, your IP address will be included in the reset email.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>How long we retain your data</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Suggested text: </strong>If you leave a comment, the comment and its metadata are retained indefinitely. This is so we can recognize and approve any follow-up comments automatically instead of holding them in a moderation queue.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>For users that register on our website (if any), we also store the personal information they provide in their user profile. All users can see, edit, or delete their personal information at any time (except they cannot change their username). Website administrators can also see and edit that information.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>What rights you have over your data</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Suggested text: </strong>If you have an account on this site, or have left comments, you can request to receive an exported file of the personal data we hold about you, including any data you have provided to us. You can also request that we erase any personal data we hold about you. This does not include any data we are obliged to keep for administrative, legal, or security purposes.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Where your data is sent</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Suggested text: </strong>Visitor comments may be checked through an automated spam detection service.</p><!-- /wp:paragraph -->','Privacy Policy','','draft','closed','open','','privacy-policy','','','2022-12-28 16:02:49','2022-12-28 16:02:49','',0,'http://acceptance-tests.test/?page_id=3',0,'page','',0),(4,0,'2022-12-28 16:03:04','2022-12-28 16:03:04','','woocommerce-placeholder','','inherit','open','closed','','woocommerce-placeholder','','','2022-12-28 16:03:04','2022-12-28 16:03:04','',0,'http://acceptance-tests.test/wp-content/uploads/2022/12/woocommerce-placeholder.png',0,'attachment','image/png',0),(5,1,'2022-12-28 16:03:04','2022-12-28 16:03:04','','Shop','','publish','closed','closed','','shop','','','2022-12-28 16:03:04','2022-12-28 16:03:04','',0,'http://acceptance-tests.test/shop/',0,'page','',0),(6,1,'2022-12-28 16:03:04','2022-12-28 16:03:04','<!-- wp:shortcode -->[woocommerce_cart]<!-- /wp:shortcode -->','Cart','','publish','closed','closed','','cart','','','2022-12-28 16:03:04','2022-12-28 16:03:04','',0,'http://acceptance-tests.test/cart/',0,'page','',0),(7,1,'2022-12-28 16:03:04','2022-12-28 16:03:04','<!-- wp:shortcode -->[woocommerce_checkout]<!-- /wp:shortcode -->','Checkout','','publish','closed','closed','','checkout','','','2022-12-28 16:03:04','2022-12-28 16:03:04','',0,'http://acceptance-tests.test/checkout/',0,'page','',0),(8,1,'2022-12-28 16:03:04','2022-12-28 16:03:04','<!-- wp:shortcode -->[woocommerce_my_account]<!-- /wp:shortcode -->','My account','','publish','closed','closed','','my-account','','','2022-12-28 16:03:04','2022-12-28 16:03:04','',0,'http://acceptance-tests.test/my-account/',0,'page','',0),(9,1,'2022-12-28 16:03:04','0000-00-00 00:00:00','<!-- wp:paragraph -->\n<p><b>This is a sample page.</b></p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<h3>Overview</h3>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>Our refund and returns policy lasts 30 days. If 30 days have passed since your purchase, we can’t offer you a full refund or exchange.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>To be eligible for a return, your item must be unused and in the same condition that you received it. It must also be in the original packaging.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>Several types of goods are exempt from being returned. Perishable goods such as food, flowers, newspapers or magazines cannot be returned. We also do not accept products that are intimate or sanitary goods, hazardous materials, or flammable liquids or gases.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>Additional non-returnable items:</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:list -->\n<ul>\n<li>Gift cards</li>\n<li>Downloadable software products</li>\n<li>Some health and personal care items</li>\n</ul>\n<!-- /wp:list -->\n\n<!-- wp:paragraph -->\n<p>To complete your return, we require a receipt or proof of purchase.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>Please do not send your purchase back to the manufacturer.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>There are certain situations where only partial refunds are granted:</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:list -->\n<ul>\n<li>Book with obvious signs of use</li>\n<li>CD, DVD, VHS tape, software, video game, cassette tape, or vinyl record that has been opened.</li>\n<li>Any item not in its original condition, is damaged or missing parts for reasons not due to our error.</li>\n<li>Any item that is returned more than 30 days after delivery</li>\n</ul>\n<!-- /wp:list -->\n\n<!-- wp:paragraph -->\n<h2>Refunds</h2>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>Once your return is received and inspected, we will send you an email to notify you that we have received your returned item. We will also notify you of the approval or rejection of your refund.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>If you are approved, then your refund will be processed, and a credit will automatically be applied to your credit card or original method of payment, within a certain amount of days.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<b>Late or missing refunds</b>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>If you haven’t received a refund yet, first check your bank account again.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>Then contact your credit card company, it may take some time before your refund is officially posted.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>Next contact your bank. There is often some processing time before a refund is posted.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>If you’ve done all of this and you still have not received your refund yet, please contact us at {email address}.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<b>Sale items</b>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>Only regular priced items may be refunded. Sale items cannot be refunded.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<h2>Exchanges</h2>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>We only replace items if they are defective or damaged. If you need to exchange it for the same item, send us an email at {email address} and send your item to: {physical address}.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<h2>Gifts</h2>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>If the item was marked as a gift when purchased and shipped directly to you, you’ll receive a gift credit for the value of your return. Once the returned item is received, a gift certificate will be mailed to you.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>If the item wasn’t marked as a gift when purchased, or the gift giver had the order shipped to themselves to give to you later, we will send a refund to the gift giver and they will find out about your return.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<h2>Shipping returns</h2>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>To return your product, you should mail your product to: {physical address}.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>You will be responsible for paying for your own shipping costs for returning your item. Shipping costs are non-refundable. If you receive a refund, the cost of return shipping will be deducted from your refund.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>Depending on where you live, the time it may take for your exchanged product to reach you may vary.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>If you are returning more expensive items, you may consider using a trackable shipping service or purchasing shipping insurance. We don’t guarantee that we will receive your returned item.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<h2>Need help?</h2>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>Contact us at {email} for questions related to refunds and returns.</p>\n<!-- /wp:paragraph -->','Refund and Returns Policy','','draft','closed','closed','','refund_returns','','','2022-12-28 16:03:04','0000-00-00 00:00:00','',0,'http://acceptance-tests.test/?page_id=9',0,'page','',0),(10,0,'2019-01-16 13:01:52','2019-01-16 13:01:52','Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.','V-Neck T-Shirt','This is a variable product.','publish','open','closed','','v-neck-t-shirt','','','2019-01-16 13:01:52','2019-01-16 13:01:52','',0,'https://woocommercecore.mystagingwebsite.com/product/v-neck-t-shirt/',0,'product','',0),(11,0,'2019-01-16 13:01:52','2019-01-16 13:01:52','Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.','Hoodie','This is a variable product.','publish','open','closed','','hoodie','','','2019-01-16 13:01:52','2019-01-16 13:01:52','',0,'https://woocommercecore.mystagingwebsite.com/product/hoodie/',0,'product','',0),(12,0,'2019-01-16 13:01:52','2019-01-16 13:01:52','Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.','Hoodie with Logo','This is a simple product.','publish','open','closed','','hoodie-with-logo','','','2019-01-16 13:01:52','2019-01-16 13:01:52','',0,'https://woocommercecore.mystagingwebsite.com/product/hoodie-with-logo/',0,'product','',0),(13,0,'2019-01-16 13:01:52','2019-01-16 13:01:52','Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.','T-Shirt','This is a simple product.','publish','open','closed','','t-shirt','','','2019-01-16 13:01:52','2019-01-16 13:01:52','',0,'https://woocommercecore.mystagingwebsite.com/product/t-shirt/',0,'product','',0),(14,0,'2019-01-16 13:01:52','2019-01-16 13:01:52','Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.','Beanie','This is a simple product.','publish','open','closed','','beanie','','','2019-01-16 13:01:52','2019-01-16 13:01:52','',0,'https://woocommercecore.mystagingwebsite.com/product/beanie/',0,'product','',0),(15,0,'2019-01-16 13:01:52','2019-01-16 13:01:52','Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.','Belt','This is a simple product.','publish','open','closed','','belt','','','2019-01-16 13:01:52','2019-01-16 13:01:52','',0,'https://woocommercecore.mystagingwebsite.com/product/belt/',0,'product','',0),(16,0,'2019-01-16 13:01:53','2019-01-16 13:01:53','Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.','Cap','This is a simple product.','publish','open','closed','','cap','','','2019-01-16 13:01:53','2019-01-16 13:01:53','',0,'https://woocommercecore.mystagingwebsite.com/product/cap/',0,'product','',0),(17,0,'2019-01-16 13:01:53','2019-01-16 13:01:53','Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.','Sunglasses','This is a simple product.','publish','open','closed','','sunglasses','','','2019-01-16 13:01:53','2019-01-16 13:01:53','',0,'https://woocommercecore.mystagingwebsite.com/product/sunglasses/',0,'product','',0),(18,0,'2019-01-16 13:01:53','2019-01-16 13:01:53','Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.','Hoodie with Pocket','This is a simple product.','publish','open','closed','','hoodie-with-pocket','','','2019-01-16 13:01:53','2019-01-16 13:01:53','',0,'https://woocommercecore.mystagingwebsite.com/product/hoodie-with-pocket/',0,'product','',0),(19,0,'2019-01-16 13:01:53','2019-01-16 13:01:53','Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.','Hoodie with Zipper','This is a simple product.','publish','open','closed','','hoodie-with-zipper','','','2019-01-16 13:01:53','2019-01-16 13:01:53','',0,'https://woocommercecore.mystagingwebsite.com/product/hoodie-with-zipper/',0,'product','',0),(20,0,'2019-01-16 13:01:53','2019-01-16 13:01:53','Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.','Long Sleeve Tee','This is a simple product.','publish','open','closed','','long-sleeve-tee','','','2019-01-16 13:01:53','2019-01-16 13:01:53','',0,'https://woocommercecore.mystagingwebsite.com/product/long-sleeve-tee/',0,'product','',0),(21,0,'2019-01-16 13:01:53','2019-01-16 13:01:53','Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.','Polo','This is a simple product.','publish','open','closed','','polo','','','2019-01-16 13:01:53','2019-01-16 13:01:53','',0,'https://woocommercecore.mystagingwebsite.com/product/polo/',0,'product','',0),(22,0,'2019-01-16 13:01:54','2019-01-16 13:01:54','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum sagittis orci ac odio dictum tincidunt. Donec ut metus leo. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Sed luctus, dui eu sagittis sodales, nulla nibh sagittis augue, vel porttitor diam enim non metus. Vestibulum aliquam augue neque. Phasellus tincidunt odio eget ullamcorper efficitur. Cras placerat ut turpis pellentesque vulputate. Nam sed consequat tortor. Curabitur finibus sapien dolor. Ut eleifend tellus nec erat pulvinar dignissim. Nam non arcu purus. Vivamus et massa massa.','Album','This is a simple, virtual product.','publish','open','closed','','album','','','2019-01-16 13:01:54','2019-01-16 13:01:54','',0,'https://woocommercecore.mystagingwebsite.com/product/album/',0,'product','',0),(23,0,'2019-01-16 13:01:54','2019-01-16 13:01:54','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum sagittis orci ac odio dictum tincidunt. Donec ut metus leo. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Sed luctus, dui eu sagittis sodales, nulla nibh sagittis augue, vel porttitor diam enim non metus. Vestibulum aliquam augue neque. Phasellus tincidunt odio eget ullamcorper efficitur. Cras placerat ut turpis pellentesque vulputate. Nam sed consequat tortor. Curabitur finibus sapien dolor. Ut eleifend tellus nec erat pulvinar dignissim. Nam non arcu purus. Vivamus et massa massa.','Single','This is a simple, virtual product.','publish','open','closed','','single','','','2019-01-16 13:01:54','2019-01-16 13:01:54','',0,'https://woocommercecore.mystagingwebsite.com/product/single/',0,'product','',0),(24,0,'2019-01-16 13:01:54','2019-01-16 13:01:54','','V-Neck T-Shirt - Red','','publish','closed','closed','','v-neck-t-shirt-red','','','2019-01-16 13:01:54','2019-01-16 13:01:54','',10,'https://woocommercecore.mystagingwebsite.com/product/v-neck-t-shirt-red/',0,'product_variation','',0),(25,0,'2019-01-16 13:01:54','2019-01-16 13:01:54','','V-Neck T-Shirt - Green','','publish','closed','closed','','v-neck-t-shirt-green','','','2019-01-16 13:01:54','2019-01-16 13:01:54','',10,'https://woocommercecore.mystagingwebsite.com/product/v-neck-t-shirt-green/',0,'product_variation','',0),(26,0,'2019-01-16 13:01:54','2019-01-16 13:01:54','','V-Neck T-Shirt - Blue','','publish','closed','closed','','v-neck-t-shirt-blue','','','2019-01-16 13:01:54','2019-01-16 13:01:54','',10,'https://woocommercecore.mystagingwebsite.com/product/v-neck-t-shirt-blue/',0,'product_variation','',0),(27,0,'2019-01-16 13:01:54','2019-01-16 13:01:54','','Hoodie - Red, No','','publish','closed','closed','','hoodie-red-no','','','2019-01-16 13:01:54','2019-01-16 13:01:54','',11,'https://woocommercecore.mystagingwebsite.com/product/hoodie-red-no',1,'product_variation','',0),(28,0,'2019-01-16 13:01:54','2019-01-16 13:01:54','','Hoodie - Green, No','','publish','closed','closed','','hoodie-green-no','','','2019-01-16 13:01:54','2019-01-16 13:01:54','',11,'https://woocommercecore.mystagingwebsite.com/product/hoodie-green-no/',2,'product_variation','',0),(29,0,'2019-01-16 13:01:55','2019-01-16 13:01:55','','Hoodie - Blue, No','','publish','closed','closed','','hoodie-blue-no','','','2019-01-16 13:01:55','2019-01-16 13:01:55','',11,'https://woocommercecore.mystagingwebsite.com/product/hoodie-blue-no',3,'product_variation','',0),(30,0,'2019-01-16 13:01:55','2019-01-16 13:01:55','Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.','T-Shirt with Logo','This is a simple product.','publish','open','closed','','t-shirt-with-logo','','','2019-01-16 13:01:55','2019-01-16 13:01:55','',0,'https://woocommercecore.mystagingwebsite.com/product/t-shirt-with-logo/',0,'product','',0),(31,0,'2019-01-16 13:01:55','2019-01-16 13:01:55','Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.','Beanie with Logo','This is a simple product.','publish','open','closed','','beanie-with-logo','','','2019-01-16 13:01:55','2019-01-16 13:01:55','',0,'https://woocommercecore.mystagingwebsite.com/product/beanie-with-logo/',0,'product','',0),(32,0,'2019-01-16 13:01:55','2019-01-16 13:01:55','Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.','Logo Collection','This is a grouped product.','publish','open','closed','','logo-collection','','','2019-01-16 13:01:55','2019-01-16 13:01:55','',0,'https://woocommercecore.mystagingwebsite.com/product/logo-collection/',0,'product','',0),(33,0,'2019-01-16 13:01:55','2019-01-16 13:01:55','Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.','WordPress Pennant','This is an external product.','publish','open','closed','','wordpress-pennant','','','2019-01-16 13:01:55','2019-01-16 13:01:55','',0,'https://woocommercecore.mystagingwebsite.com/product/wordpress-pennant/',0,'product','',0),(34,0,'2019-01-16 13:01:55','2019-01-16 13:01:55','','Hoodie - Blue, Yes','','publish','closed','closed','','hoodie-blue-yes','','','2019-01-16 13:01:55','2019-01-16 13:01:55','',11,'https://woocommercecore.mystagingwebsite.com/product/hoodie-blue-yes/',0,'product_variation','',0),(35,0,'2019-01-16 13:01:56','2019-01-16 13:01:56','','vneck-tee-2.jpg','','inherit','open','closed','','vneck-tee-2-jpg','','','2019-01-16 13:01:56','2019-01-16 13:01:56','',10,'http://acceptance-tests.test/wp-content/uploads/2019/01/vneck-tee-2.jpg',0,'attachment','image/jpeg',0),(36,0,'2019-01-16 13:01:57','2019-01-16 13:01:57','','vnech-tee-green-1.jpg','','inherit','open','closed','','vnech-tee-green-1-jpg','','','2019-01-16 13:01:57','2019-01-16 13:01:57','',10,'http://acceptance-tests.test/wp-content/uploads/2019/01/vnech-tee-green-1.jpg',0,'attachment','image/jpeg',0),(37,0,'2019-01-16 13:01:58','2019-01-16 13:01:58','','vnech-tee-blue-1.jpg','','inherit','open','closed','','vnech-tee-blue-1-jpg','','','2019-01-16 13:01:58','2019-01-16 13:01:58','',10,'http://acceptance-tests.test/wp-content/uploads/2019/01/vnech-tee-blue-1.jpg',0,'attachment','image/jpeg',0),(38,0,'2019-01-16 13:01:58','2019-01-16 13:01:58','','hoodie-2.jpg','','inherit','open','closed','','hoodie-2-jpg','','','2019-01-16 13:01:58','2019-01-16 13:01:58','',11,'http://acceptance-tests.test/wp-content/uploads/2019/01/hoodie-2.jpg',0,'attachment','image/jpeg',0),(39,0,'2019-01-16 13:01:59','2019-01-16 13:01:59','','hoodie-blue-1.jpg','','inherit','open','closed','','hoodie-blue-1-jpg','','','2019-01-16 13:01:59','2019-01-16 13:01:59','',11,'http://acceptance-tests.test/wp-content/uploads/2019/01/hoodie-blue-1.jpg',0,'attachment','image/jpeg',0),(40,0,'2019-01-16 13:02:00','2019-01-16 13:02:00','','hoodie-green-1.jpg','','inherit','open','closed','','hoodie-green-1-jpg','','','2019-01-16 13:02:00','2019-01-16 13:02:00','',11,'http://acceptance-tests.test/wp-content/uploads/2019/01/hoodie-green-1.jpg',0,'attachment','image/jpeg',0),(41,0,'2019-01-16 13:02:01','2019-01-16 13:02:01','','hoodie-with-logo-2.jpg','','inherit','open','closed','','hoodie-with-logo-2-jpg','','','2019-01-16 13:02:01','2019-01-16 13:02:01','',11,'http://acceptance-tests.test/wp-content/uploads/2019/01/hoodie-with-logo-2.jpg',0,'attachment','image/jpeg',0),(42,0,'2019-01-16 13:02:02','2019-01-16 13:02:02','','tshirt-2.jpg','','inherit','open','closed','','tshirt-2-jpg','','','2019-01-16 13:02:02','2019-01-16 13:02:02','',13,'http://acceptance-tests.test/wp-content/uploads/2019/01/tshirt-2.jpg',0,'attachment','image/jpeg',0),(43,0,'2019-01-16 13:02:02','2019-01-16 13:02:02','','beanie-2.jpg','','inherit','open','closed','','beanie-2-jpg','','','2019-01-16 13:02:02','2019-01-16 13:02:02','',14,'http://acceptance-tests.test/wp-content/uploads/2019/01/beanie-2.jpg',0,'attachment','image/jpeg',0),(44,0,'2019-01-16 13:02:03','2019-01-16 13:02:03','','belt-2.jpg','','inherit','open','closed','','belt-2-jpg','','','2019-01-16 13:02:03','2019-01-16 13:02:03','',15,'http://acceptance-tests.test/wp-content/uploads/2019/01/belt-2.jpg',0,'attachment','image/jpeg',0),(45,0,'2019-01-16 13:02:04','2019-01-16 13:02:04','','cap-2.jpg','','inherit','open','closed','','cap-2-jpg','','','2019-01-16 13:02:04','2019-01-16 13:02:04','',16,'http://acceptance-tests.test/wp-content/uploads/2019/01/cap-2.jpg',0,'attachment','image/jpeg',0),(46,0,'2019-01-16 13:02:05','2019-01-16 13:02:05','','sunglasses-2.jpg','','inherit','open','closed','','sunglasses-2-jpg','','','2019-01-16 13:02:05','2019-01-16 13:02:05','',17,'http://acceptance-tests.test/wp-content/uploads/2019/01/sunglasses-2.jpg',0,'attachment','image/jpeg',0),(47,0,'2019-01-16 13:02:06','2019-01-16 13:02:06','','hoodie-with-pocket-2.jpg','','inherit','open','closed','','hoodie-with-pocket-2-jpg','','','2019-01-16 13:02:06','2019-01-16 13:02:06','',18,'http://acceptance-tests.test/wp-content/uploads/2019/01/hoodie-with-pocket-2.jpg',0,'attachment','image/jpeg',0),(48,0,'2019-01-16 13:02:06','2019-01-16 13:02:06','','hoodie-with-zipper-2.jpg','','inherit','open','closed','','hoodie-with-zipper-2-jpg','','','2019-01-16 13:02:06','2019-01-16 13:02:06','',19,'http://acceptance-tests.test/wp-content/uploads/2019/01/hoodie-with-zipper-2.jpg',0,'attachment','image/jpeg',0),(49,0,'2019-01-16 13:02:07','2019-01-16 13:02:07','','long-sleeve-tee-2.jpg','','inherit','open','closed','','long-sleeve-tee-2-jpg','','','2019-01-16 13:02:07','2019-01-16 13:02:07','',20,'http://acceptance-tests.test/wp-content/uploads/2019/01/long-sleeve-tee-2.jpg',0,'attachment','image/jpeg',0),(50,0,'2019-01-16 13:02:08','2019-01-16 13:02:08','','polo-2.jpg','','inherit','open','closed','','polo-2-jpg','','','2019-01-16 13:02:08','2019-01-16 13:02:08','',21,'http://acceptance-tests.test/wp-content/uploads/2019/01/polo-2.jpg',0,'attachment','image/jpeg',0),(51,0,'2019-01-16 13:02:09','2019-01-16 13:02:09','','album-1.jpg','','inherit','open','closed','','album-1-jpg','','','2019-01-16 13:02:09','2019-01-16 13:02:09','',22,'http://acceptance-tests.test/wp-content/uploads/2019/01/album-1.jpg',0,'attachment','image/jpeg',0),(52,0,'2019-01-16 13:02:10','2019-01-16 13:02:10','','single-1.jpg','','inherit','open','closed','','single-1-jpg','','','2019-01-16 13:02:10','2019-01-16 13:02:10','',23,'http://acceptance-tests.test/wp-content/uploads/2019/01/single-1.jpg',0,'attachment','image/jpeg',0),(53,0,'2019-01-16 13:02:11','2019-01-16 13:02:11','','t-shirt-with-logo-1.jpg','','inherit','open','closed','','t-shirt-with-logo-1-jpg','','','2019-01-16 13:02:11','2019-01-16 13:02:11','',30,'http://acceptance-tests.test/wp-content/uploads/2019/01/t-shirt-with-logo-1.jpg',0,'attachment','image/jpeg',0),(54,0,'2019-01-16 13:02:12','2019-01-16 13:02:12','','beanie-with-logo-1.jpg','','inherit','open','closed','','beanie-with-logo-1-jpg','','','2019-01-16 13:02:12','2019-01-16 13:02:12','',31,'http://acceptance-tests.test/wp-content/uploads/2019/01/beanie-with-logo-1.jpg',0,'attachment','image/jpeg',0),(55,0,'2019-01-16 13:02:13','2019-01-16 13:02:13','','logo-1.jpg','','inherit','open','closed','','logo-1-jpg','','','2019-01-16 13:02:13','2019-01-16 13:02:13','',32,'http://acceptance-tests.test/wp-content/uploads/2019/01/logo-1.jpg',0,'attachment','image/jpeg',0),(56,0,'2019-01-16 13:02:13','2019-01-16 13:02:13','','pennant-1.jpg','','inherit','open','closed','','pennant-1-jpg','','','2019-01-16 13:02:13','2019-01-16 13:02:13','',33,'http://acceptance-tests.test/wp-content/uploads/2019/01/pennant-1.jpg',0,'attachment','image/jpeg',0);
/*!40000 ALTER TABLE `wp_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_term_relationships`
--

DROP TABLE IF EXISTS `wp_term_relationships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_term_relationships` (
  `object_id` bigint unsigned NOT NULL DEFAULT '0',
  `term_taxonomy_id` bigint unsigned NOT NULL DEFAULT '0',
  `term_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`object_id`,`term_taxonomy_id`),
  KEY `term_taxonomy_id` (`term_taxonomy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_term_relationships`
--

LOCK TABLES `wp_term_relationships` WRITE;
/*!40000 ALTER TABLE `wp_term_relationships` DISABLE KEYS */;
INSERT INTO `wp_term_relationships` VALUES (1,1,0),(10,4,0),(10,8,0),(10,16,0),(11,4,0),(11,17,0),(12,2,0),(12,17,0),(13,2,0),(13,16,0),(14,2,0),(14,18,0),(15,2,0),(15,18,0),(16,2,0),(16,8,0),(16,18,0),(17,2,0),(17,8,0),(17,18,0),(18,2,0),(18,6,0),(18,7,0),(18,8,0),(18,17,0),(19,2,0),(19,8,0),(19,17,0),(20,2,0),(20,16,0),(21,2,0),(21,16,0),(22,2,0),(22,19,0),(23,2,0),(23,19,0),(30,2,0),(30,16,0),(31,2,0),(31,18,0),(32,3,0),(32,20,0),(33,5,0),(33,21,0);
/*!40000 ALTER TABLE `wp_term_relationships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_term_taxonomy`
--

DROP TABLE IF EXISTS `wp_term_taxonomy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_term_taxonomy` (
  `term_taxonomy_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint unsigned NOT NULL DEFAULT '0',
  `taxonomy` varchar(32) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `description` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `parent` bigint unsigned NOT NULL DEFAULT '0',
  `count` bigint NOT NULL DEFAULT '0',
  PRIMARY KEY (`term_taxonomy_id`),
  UNIQUE KEY `term_id_taxonomy` (`term_id`,`taxonomy`),
  KEY `taxonomy` (`taxonomy`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_term_taxonomy`
--

LOCK TABLES `wp_term_taxonomy` WRITE;
/*!40000 ALTER TABLE `wp_term_taxonomy` DISABLE KEYS */;
INSERT INTO `wp_term_taxonomy` VALUES (1,1,'category','',0,1),(2,2,'product_type','',0,14),(3,3,'product_type','',0,1),(4,4,'product_type','',0,2),(5,5,'product_type','',0,1),(6,6,'product_visibility','',0,1),(7,7,'product_visibility','',0,1),(8,8,'product_visibility','',0,5),(9,9,'product_visibility','',0,0),(10,10,'product_visibility','',0,0),(11,11,'product_visibility','',0,0),(12,12,'product_visibility','',0,0),(13,13,'product_visibility','',0,0),(14,14,'product_visibility','',0,0),(15,15,'product_cat','',0,0),(16,16,'product_cat','',0,5),(17,17,'product_cat','',0,4),(18,18,'product_cat','',0,5),(19,19,'product_cat','',0,2),(20,20,'product_cat','',0,1),(21,21,'product_cat','',0,1);
/*!40000 ALTER TABLE `wp_term_taxonomy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_termmeta`
--

DROP TABLE IF EXISTS `wp_termmeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_termmeta` (
  `meta_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`meta_id`),
  KEY `term_id` (`term_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_termmeta`
--

LOCK TABLES `wp_termmeta` WRITE;
/*!40000 ALTER TABLE `wp_termmeta` DISABLE KEYS */;
INSERT INTO `wp_termmeta` VALUES (1,16,'product_count_product_cat','5'),(2,17,'product_count_product_cat','3'),(3,18,'product_count_product_cat','5'),(4,19,'product_count_product_cat','2'),(5,20,'product_count_product_cat','1'),(6,21,'product_count_product_cat','1');
/*!40000 ALTER TABLE `wp_termmeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_terms`
--

DROP TABLE IF EXISTS `wp_terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_terms` (
  `term_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `slug` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `term_group` bigint NOT NULL DEFAULT '0',
  PRIMARY KEY (`term_id`),
  KEY `slug` (`slug`(191)),
  KEY `name` (`name`(191))
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_terms`
--

LOCK TABLES `wp_terms` WRITE;
/*!40000 ALTER TABLE `wp_terms` DISABLE KEYS */;
INSERT INTO `wp_terms` VALUES (1,'Uncategorized','uncategorized',0),(2,'simple','simple',0),(3,'grouped','grouped',0),(4,'variable','variable',0),(5,'external','external',0),(6,'exclude-from-search','exclude-from-search',0),(7,'exclude-from-catalog','exclude-from-catalog',0),(8,'featured','featured',0),(9,'outofstock','outofstock',0),(10,'rated-1','rated-1',0),(11,'rated-2','rated-2',0),(12,'rated-3','rated-3',0),(13,'rated-4','rated-4',0),(14,'rated-5','rated-5',0),(15,'Uncategorized','uncategorized',0),(16,'Tshirts','tshirts',0),(17,'Hoodies','hoodies',0),(18,'Accessories','accessories',0),(19,'Music','music',0),(20,'Clothing','clothing',0),(21,'Decor','decor',0);
/*!40000 ALTER TABLE `wp_terms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_usermeta`
--

DROP TABLE IF EXISTS `wp_usermeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_usermeta` (
  `umeta_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`umeta_id`),
  KEY `user_id` (`user_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_usermeta`
--

LOCK TABLES `wp_usermeta` WRITE;
/*!40000 ALTER TABLE `wp_usermeta` DISABLE KEYS */;
INSERT INTO `wp_usermeta` VALUES (1,1,'nickname','admin'),(2,1,'first_name',''),(3,1,'last_name',''),(4,1,'description',''),(5,1,'rich_editing','true'),(6,1,'syntax_highlighting','true'),(7,1,'comment_shortcuts','false'),(8,1,'admin_color','fresh'),(9,1,'use_ssl','0'),(10,1,'show_admin_bar_front','true'),(11,1,'locale',''),(12,1,'wp_capabilities','a:1:{s:13:\"administrator\";b:1;}'),(13,1,'wp_user_level','10'),(14,1,'dismissed_wp_pointers',''),(15,1,'show_welcome_panel','1');
/*!40000 ALTER TABLE `wp_usermeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_users`
--

DROP TABLE IF EXISTS `wp_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_users` (
  `ID` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_login` varchar(60) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_pass` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_nicename` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_email` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_url` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_activation_key` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_status` int NOT NULL DEFAULT '0',
  `display_name` varchar(250) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `user_login_key` (`user_login`),
  KEY `user_nicename` (`user_nicename`),
  KEY `user_email` (`user_email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_users`
--

LOCK TABLES `wp_users` WRITE;
/*!40000 ALTER TABLE `wp_users` DISABLE KEYS */;
INSERT INTO `wp_users` VALUES (1,'admin','$P$BEKJuzko8Z0HihOSdErUQpZz0Zvo0J.','admin','manik@pluginever.com','http://acceptance-tests.test','2022-12-28 16:02:49','',0,'admin');
/*!40000 ALTER TABLE `wp_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_wc_admin_note_actions`
--

DROP TABLE IF EXISTS `wp_wc_admin_note_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_wc_admin_note_actions` (
  `action_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `note_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `query` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `actioned_text` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `nonce_action` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `nonce_name` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`action_id`),
  KEY `note_id` (`note_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_wc_admin_note_actions`
--

LOCK TABLES `wp_wc_admin_note_actions` WRITE;
/*!40000 ALTER TABLE `wp_wc_admin_note_actions` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_wc_admin_note_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_wc_admin_notes`
--

DROP TABLE IF EXISTS `wp_wc_admin_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_wc_admin_notes` (
  `note_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `locale` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `title` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `content_data` longtext COLLATE utf8mb4_unicode_520_ci,
  `status` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `source` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_reminder` datetime DEFAULT NULL,
  `is_snoozable` tinyint(1) NOT NULL DEFAULT '0',
  `layout` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `image` varchar(200) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `icon` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'info',
  PRIMARY KEY (`note_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_wc_admin_notes`
--

LOCK TABLES `wp_wc_admin_notes` WRITE;
/*!40000 ALTER TABLE `wp_wc_admin_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_wc_admin_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_wc_category_lookup`
--

DROP TABLE IF EXISTS `wp_wc_category_lookup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_wc_category_lookup` (
  `category_tree_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`category_tree_id`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_wc_category_lookup`
--

LOCK TABLES `wp_wc_category_lookup` WRITE;
/*!40000 ALTER TABLE `wp_wc_category_lookup` DISABLE KEYS */;
INSERT INTO `wp_wc_category_lookup` VALUES (16,16),(17,17),(18,18),(19,19),(20,20),(21,21);
/*!40000 ALTER TABLE `wp_wc_category_lookup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_wc_customer_lookup`
--

DROP TABLE IF EXISTS `wp_wc_customer_lookup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_wc_customer_lookup` (
  `customer_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `username` varchar(60) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `first_name` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `date_last_active` timestamp NULL DEFAULT NULL,
  `date_registered` timestamp NULL DEFAULT NULL,
  `country` char(2) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `postcode` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `city` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `state` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_wc_customer_lookup`
--

LOCK TABLES `wp_wc_customer_lookup` WRITE;
/*!40000 ALTER TABLE `wp_wc_customer_lookup` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_wc_customer_lookup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_wc_download_log`
--

DROP TABLE IF EXISTS `wp_wc_download_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_wc_download_log` (
  `download_log_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` datetime NOT NULL,
  `permission_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `user_ip_address` varchar(100) COLLATE utf8mb4_unicode_520_ci DEFAULT '',
  PRIMARY KEY (`download_log_id`),
  KEY `permission_id` (`permission_id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_wc_download_log`
--

LOCK TABLES `wp_wc_download_log` WRITE;
/*!40000 ALTER TABLE `wp_wc_download_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_wc_download_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_wc_order_coupon_lookup`
--

DROP TABLE IF EXISTS `wp_wc_order_coupon_lookup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_wc_order_coupon_lookup` (
  `order_id` bigint unsigned NOT NULL,
  `coupon_id` bigint NOT NULL,
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `discount_amount` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`order_id`,`coupon_id`),
  KEY `coupon_id` (`coupon_id`),
  KEY `date_created` (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_wc_order_coupon_lookup`
--

LOCK TABLES `wp_wc_order_coupon_lookup` WRITE;
/*!40000 ALTER TABLE `wp_wc_order_coupon_lookup` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_wc_order_coupon_lookup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_wc_order_product_lookup`
--

DROP TABLE IF EXISTS `wp_wc_order_product_lookup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_wc_order_product_lookup` (
  `order_item_id` bigint unsigned NOT NULL,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `variation_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `product_qty` int NOT NULL,
  `product_net_revenue` double NOT NULL DEFAULT '0',
  `product_gross_revenue` double NOT NULL DEFAULT '0',
  `coupon_amount` double NOT NULL DEFAULT '0',
  `tax_amount` double NOT NULL DEFAULT '0',
  `shipping_amount` double NOT NULL DEFAULT '0',
  `shipping_tax_amount` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  KEY `customer_id` (`customer_id`),
  KEY `date_created` (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_wc_order_product_lookup`
--

LOCK TABLES `wp_wc_order_product_lookup` WRITE;
/*!40000 ALTER TABLE `wp_wc_order_product_lookup` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_wc_order_product_lookup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_wc_order_stats`
--

DROP TABLE IF EXISTS `wp_wc_order_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_wc_order_stats` (
  `order_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_created_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `num_items_sold` int NOT NULL DEFAULT '0',
  `total_sales` double NOT NULL DEFAULT '0',
  `tax_total` double NOT NULL DEFAULT '0',
  `shipping_total` double NOT NULL DEFAULT '0',
  `net_total` double NOT NULL DEFAULT '0',
  `returning_customer` tinyint(1) DEFAULT NULL,
  `status` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`order_id`),
  KEY `date_created` (`date_created`),
  KEY `customer_id` (`customer_id`),
  KEY `status` (`status`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_wc_order_stats`
--

LOCK TABLES `wp_wc_order_stats` WRITE;
/*!40000 ALTER TABLE `wp_wc_order_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_wc_order_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_wc_order_tax_lookup`
--

DROP TABLE IF EXISTS `wp_wc_order_tax_lookup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_wc_order_tax_lookup` (
  `order_id` bigint unsigned NOT NULL,
  `tax_rate_id` bigint unsigned NOT NULL,
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `shipping_tax` double NOT NULL DEFAULT '0',
  `order_tax` double NOT NULL DEFAULT '0',
  `total_tax` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`order_id`,`tax_rate_id`),
  KEY `tax_rate_id` (`tax_rate_id`),
  KEY `date_created` (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_wc_order_tax_lookup`
--

LOCK TABLES `wp_wc_order_tax_lookup` WRITE;
/*!40000 ALTER TABLE `wp_wc_order_tax_lookup` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_wc_order_tax_lookup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_wc_product_attributes_lookup`
--

DROP TABLE IF EXISTS `wp_wc_product_attributes_lookup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_wc_product_attributes_lookup` (
  `product_id` bigint NOT NULL,
  `product_or_parent_id` bigint NOT NULL,
  `taxonomy` varchar(32) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `term_id` bigint NOT NULL,
  `is_variation_attribute` tinyint(1) NOT NULL,
  `in_stock` tinyint(1) NOT NULL,
  PRIMARY KEY (`product_or_parent_id`,`term_id`,`product_id`,`taxonomy`),
  KEY `is_variation_attribute_term_id` (`is_variation_attribute`,`term_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_wc_product_attributes_lookup`
--

LOCK TABLES `wp_wc_product_attributes_lookup` WRITE;
/*!40000 ALTER TABLE `wp_wc_product_attributes_lookup` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_wc_product_attributes_lookup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_wc_product_download_directories`
--

DROP TABLE IF EXISTS `wp_wc_product_download_directories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_wc_product_download_directories` (
  `url_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(256) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`url_id`),
  KEY `url` (`url`(191))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_wc_product_download_directories`
--

LOCK TABLES `wp_wc_product_download_directories` WRITE;
/*!40000 ALTER TABLE `wp_wc_product_download_directories` DISABLE KEYS */;
INSERT INTO `wp_wc_product_download_directories` VALUES (1,'file:///tmp/acceptance-tests/wp-content/uploads/woocommerce_uploads/',1),(2,'http://acceptance-tests.test/wp-content/uploads/woocommerce_uploads/',1);
/*!40000 ALTER TABLE `wp_wc_product_download_directories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_wc_product_meta_lookup`
--

DROP TABLE IF EXISTS `wp_wc_product_meta_lookup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_wc_product_meta_lookup` (
  `product_id` bigint NOT NULL,
  `sku` varchar(100) COLLATE utf8mb4_unicode_520_ci DEFAULT '',
  `virtual` tinyint(1) DEFAULT '0',
  `downloadable` tinyint(1) DEFAULT '0',
  `min_price` decimal(19,4) DEFAULT NULL,
  `max_price` decimal(19,4) DEFAULT NULL,
  `onsale` tinyint(1) DEFAULT '0',
  `stock_quantity` double DEFAULT NULL,
  `stock_status` varchar(100) COLLATE utf8mb4_unicode_520_ci DEFAULT 'instock',
  `rating_count` bigint DEFAULT '0',
  `average_rating` decimal(3,2) DEFAULT '0.00',
  `total_sales` bigint DEFAULT '0',
  `tax_status` varchar(100) COLLATE utf8mb4_unicode_520_ci DEFAULT 'taxable',
  `tax_class` varchar(100) COLLATE utf8mb4_unicode_520_ci DEFAULT '',
  PRIMARY KEY (`product_id`),
  KEY `virtual` (`virtual`),
  KEY `downloadable` (`downloadable`),
  KEY `stock_status` (`stock_status`),
  KEY `stock_quantity` (`stock_quantity`),
  KEY `onsale` (`onsale`),
  KEY `min_max_price` (`min_price`,`max_price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_wc_product_meta_lookup`
--

LOCK TABLES `wp_wc_product_meta_lookup` WRITE;
/*!40000 ALTER TABLE `wp_wc_product_meta_lookup` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_wc_product_meta_lookup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_wc_rate_limits`
--

DROP TABLE IF EXISTS `wp_wc_rate_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_wc_rate_limits` (
  `rate_limit_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rate_limit_key` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `rate_limit_expiry` bigint unsigned NOT NULL,
  `rate_limit_remaining` smallint NOT NULL DEFAULT '0',
  PRIMARY KEY (`rate_limit_id`),
  UNIQUE KEY `rate_limit_key` (`rate_limit_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_wc_rate_limits`
--

LOCK TABLES `wp_wc_rate_limits` WRITE;
/*!40000 ALTER TABLE `wp_wc_rate_limits` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_wc_rate_limits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_wc_reserved_stock`
--

DROP TABLE IF EXISTS `wp_wc_reserved_stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_wc_reserved_stock` (
  `order_id` bigint NOT NULL,
  `product_id` bigint NOT NULL,
  `stock_quantity` double NOT NULL DEFAULT '0',
  `timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expires` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`order_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_wc_reserved_stock`
--

LOCK TABLES `wp_wc_reserved_stock` WRITE;
/*!40000 ALTER TABLE `wp_wc_reserved_stock` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_wc_reserved_stock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_wc_tax_rate_classes`
--

DROP TABLE IF EXISTS `wp_wc_tax_rate_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_wc_tax_rate_classes` (
  `tax_rate_class_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `slug` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`tax_rate_class_id`),
  UNIQUE KEY `slug` (`slug`(191))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_wc_tax_rate_classes`
--

LOCK TABLES `wp_wc_tax_rate_classes` WRITE;
/*!40000 ALTER TABLE `wp_wc_tax_rate_classes` DISABLE KEYS */;
INSERT INTO `wp_wc_tax_rate_classes` VALUES (1,'Reduced rate','reduced-rate'),(2,'Zero rate','zero-rate');
/*!40000 ALTER TABLE `wp_wc_tax_rate_classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_wc_webhooks`
--

DROP TABLE IF EXISTS `wp_wc_webhooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_wc_webhooks` (
  `webhook_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `status` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `name` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `delivery_url` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `secret` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `topic` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_created_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modified_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `api_version` smallint NOT NULL,
  `failure_count` smallint NOT NULL DEFAULT '0',
  `pending_delivery` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`webhook_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_wc_webhooks`
--

LOCK TABLES `wp_wc_webhooks` WRITE;
/*!40000 ALTER TABLE `wp_wc_webhooks` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_wc_webhooks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_api_keys`
--

DROP TABLE IF EXISTS `wp_woocommerce_api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_woocommerce_api_keys` (
  `key_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `description` varchar(200) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `permissions` varchar(10) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `consumer_key` char(64) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `consumer_secret` char(43) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `nonces` longtext COLLATE utf8mb4_unicode_520_ci,
  `truncated_key` char(7) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `last_access` datetime DEFAULT NULL,
  PRIMARY KEY (`key_id`),
  KEY `consumer_key` (`consumer_key`),
  KEY `consumer_secret` (`consumer_secret`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_api_keys`
--

LOCK TABLES `wp_woocommerce_api_keys` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_api_keys` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_woocommerce_api_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_attribute_taxonomies`
--

DROP TABLE IF EXISTS `wp_woocommerce_attribute_taxonomies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_woocommerce_attribute_taxonomies` (
  `attribute_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `attribute_name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `attribute_label` varchar(200) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `attribute_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `attribute_orderby` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `attribute_public` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`attribute_id`),
  KEY `attribute_name` (`attribute_name`(20))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_attribute_taxonomies`
--

LOCK TABLES `wp_woocommerce_attribute_taxonomies` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_attribute_taxonomies` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_woocommerce_attribute_taxonomies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_downloadable_product_permissions`
--

DROP TABLE IF EXISTS `wp_woocommerce_downloadable_product_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_woocommerce_downloadable_product_permissions` (
  `permission_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `download_id` varchar(36) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `order_id` bigint unsigned NOT NULL DEFAULT '0',
  `order_key` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `user_email` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `downloads_remaining` varchar(9) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `access_granted` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access_expires` datetime DEFAULT NULL,
  `download_count` bigint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`permission_id`),
  KEY `download_order_key_product` (`product_id`,`order_id`,`order_key`(16),`download_id`),
  KEY `download_order_product` (`download_id`,`order_id`,`product_id`),
  KEY `order_id` (`order_id`),
  KEY `user_order_remaining_expires` (`user_id`,`order_id`,`downloads_remaining`,`access_expires`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_downloadable_product_permissions`
--

LOCK TABLES `wp_woocommerce_downloadable_product_permissions` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_downloadable_product_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_woocommerce_downloadable_product_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_log`
--

DROP TABLE IF EXISTS `wp_woocommerce_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_woocommerce_log` (
  `log_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` datetime NOT NULL,
  `level` smallint NOT NULL,
  `source` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `message` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `context` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`log_id`),
  KEY `level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_log`
--

LOCK TABLES `wp_woocommerce_log` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_woocommerce_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_order_itemmeta`
--

DROP TABLE IF EXISTS `wp_woocommerce_order_itemmeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_woocommerce_order_itemmeta` (
  `meta_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_item_id` bigint unsigned NOT NULL,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`meta_id`),
  KEY `order_item_id` (`order_item_id`),
  KEY `meta_key` (`meta_key`(32))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_order_itemmeta`
--

LOCK TABLES `wp_woocommerce_order_itemmeta` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_order_itemmeta` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_woocommerce_order_itemmeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_order_items`
--

DROP TABLE IF EXISTS `wp_woocommerce_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_woocommerce_order_items` (
  `order_item_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_item_name` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `order_item_type` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `order_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_order_items`
--

LOCK TABLES `wp_woocommerce_order_items` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_woocommerce_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_payment_tokenmeta`
--

DROP TABLE IF EXISTS `wp_woocommerce_payment_tokenmeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_woocommerce_payment_tokenmeta` (
  `meta_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payment_token_id` bigint unsigned NOT NULL,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`meta_id`),
  KEY `payment_token_id` (`payment_token_id`),
  KEY `meta_key` (`meta_key`(32))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_payment_tokenmeta`
--

LOCK TABLES `wp_woocommerce_payment_tokenmeta` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_payment_tokenmeta` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_woocommerce_payment_tokenmeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_payment_tokens`
--

DROP TABLE IF EXISTS `wp_woocommerce_payment_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_woocommerce_payment_tokens` (
  `token_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `gateway_id` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `token` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL DEFAULT '0',
  `type` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`token_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_payment_tokens`
--

LOCK TABLES `wp_woocommerce_payment_tokens` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_payment_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_woocommerce_payment_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_sessions`
--

DROP TABLE IF EXISTS `wp_woocommerce_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_woocommerce_sessions` (
  `session_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `session_key` char(32) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `session_value` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `session_expiry` bigint unsigned NOT NULL,
  PRIMARY KEY (`session_id`),
  UNIQUE KEY `session_key` (`session_key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_sessions`
--

LOCK TABLES `wp_woocommerce_sessions` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_sessions` DISABLE KEYS */;
INSERT INTO `wp_woocommerce_sessions` VALUES (1,'1','a:7:{s:4:\"cart\";s:6:\"a:0:{}\";s:11:\"cart_totals\";s:367:\"a:15:{s:8:\"subtotal\";i:0;s:12:\"subtotal_tax\";i:0;s:14:\"shipping_total\";i:0;s:12:\"shipping_tax\";i:0;s:14:\"shipping_taxes\";a:0:{}s:14:\"discount_total\";i:0;s:12:\"discount_tax\";i:0;s:19:\"cart_contents_total\";i:0;s:17:\"cart_contents_tax\";i:0;s:19:\"cart_contents_taxes\";a:0:{}s:9:\"fee_total\";i:0;s:7:\"fee_tax\";i:0;s:9:\"fee_taxes\";a:0:{}s:5:\"total\";i:0;s:9:\"total_tax\";i:0;}\";s:15:\"applied_coupons\";s:6:\"a:0:{}\";s:22:\"coupon_discount_totals\";s:6:\"a:0:{}\";s:26:\"coupon_discount_tax_totals\";s:6:\"a:0:{}\";s:21:\"removed_cart_contents\";s:6:\"a:0:{}\";s:8:\"customer\";s:741:\"a:27:{s:2:\"id\";s:1:\"1\";s:13:\"date_modified\";s:0:\"\";s:8:\"postcode\";s:0:\"\";s:4:\"city\";s:0:\"\";s:9:\"address_1\";s:0:\"\";s:7:\"address\";s:0:\"\";s:9:\"address_2\";s:0:\"\";s:5:\"state\";s:2:\"CA\";s:7:\"country\";s:2:\"US\";s:17:\"shipping_postcode\";s:0:\"\";s:13:\"shipping_city\";s:0:\"\";s:18:\"shipping_address_1\";s:0:\"\";s:16:\"shipping_address\";s:0:\"\";s:18:\"shipping_address_2\";s:0:\"\";s:14:\"shipping_state\";s:2:\"CA\";s:16:\"shipping_country\";s:2:\"US\";s:13:\"is_vat_exempt\";s:0:\"\";s:19:\"calculated_shipping\";s:0:\"\";s:10:\"first_name\";s:0:\"\";s:9:\"last_name\";s:0:\"\";s:7:\"company\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:5:\"email\";s:20:\"manik@pluginever.com\";s:19:\"shipping_first_name\";s:0:\"\";s:18:\"shipping_last_name\";s:0:\"\";s:16:\"shipping_company\";s:0:\"\";s:14:\"shipping_phone\";s:0:\"\";}\";}',1672416195);
/*!40000 ALTER TABLE `wp_woocommerce_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_shipping_zone_locations`
--

DROP TABLE IF EXISTS `wp_woocommerce_shipping_zone_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_woocommerce_shipping_zone_locations` (
  `location_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `zone_id` bigint unsigned NOT NULL,
  `location_code` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `location_type` varchar(40) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`location_id`),
  KEY `location_id` (`location_id`),
  KEY `location_type_code` (`location_type`(10),`location_code`(20))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_shipping_zone_locations`
--

LOCK TABLES `wp_woocommerce_shipping_zone_locations` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_shipping_zone_locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_woocommerce_shipping_zone_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_shipping_zone_methods`
--

DROP TABLE IF EXISTS `wp_woocommerce_shipping_zone_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_woocommerce_shipping_zone_methods` (
  `zone_id` bigint unsigned NOT NULL,
  `instance_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `method_id` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `method_order` bigint unsigned NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`instance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_shipping_zone_methods`
--

LOCK TABLES `wp_woocommerce_shipping_zone_methods` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_shipping_zone_methods` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_woocommerce_shipping_zone_methods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_shipping_zones`
--

DROP TABLE IF EXISTS `wp_woocommerce_shipping_zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_woocommerce_shipping_zones` (
  `zone_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `zone_name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `zone_order` bigint unsigned NOT NULL,
  PRIMARY KEY (`zone_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_shipping_zones`
--

LOCK TABLES `wp_woocommerce_shipping_zones` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_shipping_zones` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_woocommerce_shipping_zones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_tax_rate_locations`
--

DROP TABLE IF EXISTS `wp_woocommerce_tax_rate_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_woocommerce_tax_rate_locations` (
  `location_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `location_code` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `tax_rate_id` bigint unsigned NOT NULL,
  `location_type` varchar(40) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`location_id`),
  KEY `tax_rate_id` (`tax_rate_id`),
  KEY `location_type_code` (`location_type`(10),`location_code`(20))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_tax_rate_locations`
--

LOCK TABLES `wp_woocommerce_tax_rate_locations` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_tax_rate_locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_woocommerce_tax_rate_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_woocommerce_tax_rates`
--

DROP TABLE IF EXISTS `wp_woocommerce_tax_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_woocommerce_tax_rates` (
  `tax_rate_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tax_rate_country` varchar(2) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `tax_rate_state` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `tax_rate` varchar(8) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `tax_rate_name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `tax_rate_priority` bigint unsigned NOT NULL,
  `tax_rate_compound` int NOT NULL DEFAULT '0',
  `tax_rate_shipping` int NOT NULL DEFAULT '1',
  `tax_rate_order` bigint unsigned NOT NULL,
  `tax_rate_class` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`tax_rate_id`),
  KEY `tax_rate_country` (`tax_rate_country`),
  KEY `tax_rate_state` (`tax_rate_state`(2)),
  KEY `tax_rate_class` (`tax_rate_class`(10)),
  KEY `tax_rate_priority` (`tax_rate_priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_woocommerce_tax_rates`
--

LOCK TABLES `wp_woocommerce_tax_rates` WRITE;
/*!40000 ALTER TABLE `wp_woocommerce_tax_rates` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_woocommerce_tax_rates` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-12-28 22:03:38
