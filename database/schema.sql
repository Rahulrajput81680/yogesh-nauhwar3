-- MySQL dump 10.13  Distrib 8.0.34, for Win64 (x86_64)
--
-- Host: localhost    Database: shared-admin-panel
-- ------------------------------------------------------
-- Server version	5.7.24

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `module` varchar(50) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `details` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_module` (`module`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
INSERT INTO `activity_log` VALUES (1,1,'login','authentication',1,'User logged in','2026-02-27 05:40:37'),(2,1,'logout','authentication',1,'User logged out','2026-02-27 05:42:32'),(3,1,'login','authentication',1,'User logged in','2026-02-27 05:42:36'),(4,1,'logout','authentication',1,'User logged out','2026-02-27 05:42:43'),(5,1,'login','authentication',1,'User logged in','2026-02-27 05:42:55'),(6,1,'create','blog',3,'Created blog: this is new blog','2026-02-27 05:49:28'),(7,1,'update','gallery',1,'Updated image: campus life','2026-02-27 05:49:54'),(8,1,'delete','gallery',2,'Deleted image: Sample Image 2','2026-02-27 05:49:59'),(9,1,'delete','blog',1,'Deleted blog: Welcome to Our Blog','2026-02-27 05:50:09'),(10,1,'delete','blog',2,'Deleted blog: Getting Started Guide','2026-02-27 05:50:12'),(11,1,'update','profile',1,'Updated profile information','2026-02-27 06:06:47'),(12,1,'update','profile',1,'Updated profile information','2026-02-27 06:06:47'),(13,1,'logout','authentication',1,'User logged out','2026-02-27 06:56:24'),(14,1,'login','authentication',1,'User logged in','2026-02-27 06:56:36'),(15,1,'logout','authentication',1,'User logged out','2026-02-27 06:56:44'),(16,1,'login','authentication',1,'User logged in','2026-02-27 06:57:04'),(17,1,'logout','authentication',1,'User logged out','2026-02-27 06:57:59'),(18,1,'login','authentication',1,'User logged in','2026-02-27 07:13:48'),(19,1,'delete','blog',3,'Deleted blog: this is new blog','2026-02-27 07:34:38'),(20,1,'create','blog',4,'Created blog: this is first blog test','2026-02-27 07:35:23'),(21,1,'update','profile',1,'Updated profile information','2026-02-27 07:37:14'),(22,1,'logout','authentication',1,'User logged out','2026-02-27 07:37:21'),(23,1,'login','authentication',1,'User logged in','2026-02-27 07:37:37'),(24,1,'update','gallery',1,'Updated image: campus life','2026-02-27 07:51:17'),(25,1,'update','gallery',1,'Updated image: campus life','2026-02-27 07:51:31'),(26,1,'login','authentication',1,'User logged in','2026-03-02 06:23:52'),(27,1,'login','authentication',1,'User logged in','2026-03-02 07:16:45'),(28,1,'logout','authentication',1,'User logged out','2026-03-02 07:29:40'),(29,1,'login','authentication',1,'User logged in','2026-03-02 07:29:44'),(30,1,'logout','authentication',1,'User logged out','2026-03-02 07:37:45'),(31,1,'login','authentication',1,'User logged in','2026-03-02 07:38:47'),(32,1,'logout','authentication',1,'User logged out','2026-03-02 07:49:02'),(33,1,'login','authentication',1,'User logged in','2026-03-02 08:00:57'),(34,1,'logout','authentication',1,'User logged out','2026-03-02 08:04:21'),(35,1,'login','authentication',1,'User logged in','2026-03-02 08:07:30'),(36,1,'logout','authentication',1,'User logged out','2026-03-02 08:11:09'),(37,1,'login','authentication',1,'User logged in','2026-03-02 08:22:16'),(38,1,'update','profile',1,'Updated profile information','2026-03-02 08:22:29'),(39,1,'logout','authentication',1,'User logged out','2026-03-02 08:22:42'),(40,NULL,'password_reset_requested','authentication',1,'Password reset requested for rahulrajput81680@gmail.com','2026-03-02 08:22:49'),(41,NULL,'password_reset_requested','authentication',1,'Password reset requested for rahulrajput81680@gmail.com','2026-03-02 08:22:51'),(42,NULL,'password_reset_completed','authentication',1,'Password successfully reset','2026-03-02 08:23:43'),(43,1,'login','authentication',1,'User logged in','2026-03-02 08:24:24'),(44,1,'logout','authentication',1,'User logged out','2026-03-02 08:24:57'),(45,1,'login','authentication',1,'User logged in','2026-03-02 08:30:20'),(46,1,'update','profile',1,'Updated profile information','2026-03-02 08:31:06'),(47,1,'login','authentication',1,'User logged in','2026-03-02 09:18:44'),(48,1,'logout','authentication',1,'User logged out','2026-03-02 09:20:44'),(49,NULL,'password_reset_requested','authentication',1,'Password reset requested for rahulrajput81680@gmail.com','2026-03-02 09:25:01'),(50,NULL,'password_reset_requested','authentication',1,'Password reset requested for rahulrajput81680@gmail.com','2026-03-02 09:25:03'),(51,1,'login','authentication',1,'User logged in','2026-03-02 09:31:01'),(52,1,'create','blog',5,'Created blog: this is the first blog to check','2026-03-02 09:34:10'),(53,1,'login','authentication',1,'User logged in','2026-03-02 09:55:53'),(54,1,'update','hero',1,'Updated hero section','2026-03-02 10:44:05'),(55,1,'update','hero',1,'Updated hero section','2026-03-02 10:44:48'),(56,1,'create','gallery',2,'Uploaded image: hii','2026-03-02 10:46:38'),(57,1,'logout','authentication',1,'User logged out','2026-03-02 10:46:47'),(58,1,'login','authentication',1,'User logged in','2026-03-02 10:58:05'),(59,1,'update','hero',1,'Updated hero section','2026-03-02 11:26:02'),(60,1,'update','hero',1,'Updated hero section','2026-03-02 11:26:04'),(61,1,'update','hero',1,'Updated hero section','2026-03-02 11:26:17'),(62,1,'create','forms',1,'Created form: admission form','2026-03-02 11:51:48'),(63,1,'logout','authentication',1,'User logged out','2026-03-02 12:32:37'),(64,1,'login','authentication',1,'User logged in','2026-03-03 04:36:46'),(65,1,'logout','authentication',1,'User logged out','2026-03-03 04:36:53'),(66,1,'login','authentication',1,'User logged in','2026-03-03 04:42:46'),(67,1,'logout','authentication',1,'User logged out','2026-03-03 04:44:13'),(68,1,'login','authentication',1,'User logged in','2026-03-03 04:57:36'),(69,1,'logout','authentication',1,'User logged out','2026-03-03 05:01:45'),(70,1,'login','authentication',1,'User logged in','2026-03-03 05:03:00'),(71,1,'logout','authentication',1,'User logged out','2026-03-03 05:10:52'),(72,NULL,'password_reset_requested','authentication',1,'Password reset requested for rahulrajput81680@gmail.com','2026-03-03 05:11:04'),(73,NULL,'password_reset_requested','authentication',1,'Password reset requested for rahulrajput81680@gmail.com','2026-03-03 05:11:45'),(74,1,'login','authentication',1,'User logged in','2026-03-03 05:15:30');
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('admin','editor') DEFAULT 'admin',
  `status` enum('active','inactive') DEFAULT 'active',
  `deleted_at` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_users`
--

LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
INSERT INTO `admin_users` (`id`,`username`,`email`,`password`,`full_name`,`role`,`status`,`deleted_at`,`last_login`,`created_at`,`updated_at`) VALUES (1,'admin','rahulrajput81680@gmail.com','$2y$12$qoLGe1MHqcWgZ4Ek3jHrI.TYKVIhiYMSYpr8V9KJ6grIPWlUcT4QS','Rahul Rajput','admin','active',NULL,'2026-03-03 10:45:30','2026-02-27 05:27:07','2026-03-03 05:15:30');
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blogs`
--

DROP TABLE IF EXISTS `blogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` text,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `tags` varchar(500) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_status` (`status`),
  KEY `idx_category` (`category`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `blogs_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blogs`
--

LOCK TABLES `blogs` WRITE;
/*!40000 ALTER TABLE `blogs` DISABLE KEYS */;
INSERT INTO `blogs` (`id`,`title`,`slug`,`content`,`thumbnail`,`category`,`status`,`seo_title`,`seo_description`,`meta_keywords`,`tags`,`author_id`,`deleted_at`,`created_at`,`updated_at`) VALUES (4,'this is first blog test','this-is-first-blog-test','<p>this is the description of the blog&nbsp;</p>','blog/library_1772177722_69a1493af21fc.webp','this is first blog test','published','this is first blog test','this is first blog test','this is first blog test','this is first blog test',1,NULL,'2026-02-27 07:35:22','2026-02-27 07:35:22'),(5,'this is the first blog to check','this-is-the-first-blog-to-check','<p>this is the content of the first blog to check&nbsp;</p>','blog/digital-lavender-style-interior-design_1__1772444050_69a55992c523c.jpg','blogs','published','this is the first blog to check','this is the first blog to check','blog1,blog2','blog1, blog2',1,NULL,'2026-03-02 09:34:10','2026-03-02 09:34:10');
/*!40000 ALTER TABLE `blogs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `date_of_birth` varchar(50) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_email` (`email`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(120) DEFAULT NULL,
  `event_type` enum('upcoming','past') NOT NULL DEFAULT 'upcoming',
  `event_date` date DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_events_type` (`event_type`),
  KEY `idx_events_status` (`status`),
  KEY `idx_events_date` (`event_date`),
  KEY `idx_events_created_by` (`created_by`),
  CONSTRAINT `events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `form_fields`
--

DROP TABLE IF EXISTS `form_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `form_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `field_type` enum('text','email','number','phone','date','textarea','select','radio','checkbox','file') DEFAULT 'text',
  `is_required` tinyint(1) NOT NULL DEFAULT '0',
  `options` text COMMENT 'JSON array of choices for select/radio/checkbox',
  `field_order` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_form_id` (`form_id`),
  CONSTRAINT `form_fields_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `forms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `form_fields`
--

LOCK TABLES `form_fields` WRITE;
/*!40000 ALTER TABLE `form_fields` DISABLE KEYS */;
INSERT INTO `form_fields` VALUES (1,1,'full name','full_name','text',1,NULL,1,'2026-03-02 11:52:13'),(2,1,'mobile no','mobile_no','number',1,NULL,2,'2026-03-02 11:52:33'),(3,1,'email','email','email',1,NULL,3,'2026-03-02 11:52:48'),(4,1,'address','address','textarea',1,NULL,4,'2026-03-02 11:52:59'),(6,1,'Last school name','last_school_name','text',1,NULL,5,'2026-03-02 11:53:41'),(7,1,'Last school name','last_school_name','text',1,NULL,5,'2026-03-02 11:53:41'),(8,1,'Class applying for','class_applying_for','number',1,NULL,7,'2026-03-02 11:54:08');
/*!40000 ALTER TABLE `form_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `form_submissions`
--

DROP TABLE IF EXISTS `form_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `form_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` int(11) NOT NULL,
  `data` json NOT NULL COMMENT 'Submitted field values as JSON object',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_form_id` (`form_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `form_submissions_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `forms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `form_submissions`
--

LOCK TABLES `form_submissions` WRITE;
/*!40000 ALTER TABLE `form_submissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `form_submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forms`
--

DROP TABLE IF EXISTS `forms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `forms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forms`
--

LOCK TABLES `forms` WRITE;
/*!40000 ALTER TABLE `forms` DISABLE KEYS */;
INSERT INTO `forms` VALUES (1,'admission form','admission-form','this is the admission form to take students data','active','2026-03-02 11:51:48','2026-03-02 11:51:48');
/*!40000 ALTER TABLE `forms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gallery`
--

DROP TABLE IF EXISTS `gallery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `display_section` enum('gallery','our_work','media_coverage') NOT NULL DEFAULT 'gallery',
  `status` enum('active','inactive') DEFAULT 'active',
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_category` (`category`),
  KEY `idx_display_section` (`display_section`),
  KEY `uploaded_by` (`uploaded_by`),
  CONSTRAINT `gallery_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gallery`
--

LOCK TABLES `gallery` WRITE;
/*!40000 ALTER TABLE `gallery` DISABLE KEYS */;
INSERT INTO `gallery` VALUES (1,'campus life','gallery/facilities-breadcrumb_2__1772178691_69a14d035532f.webp','General','gallery','active',1,'2026-02-27 05:27:07'),(2,'hii','gallery/Gemini_Generated_Image_kc9uzqkc9uzqkc9u_1772448396_69a56a8c609e8.jpg','hf','gallery','active',1,'2026-03-02 10:46:38');
/*!40000 ALTER TABLE `gallery` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hero_sections`
--

DROP TABLE IF EXISTS `hero_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hero_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `heading` varchar(255) NOT NULL,
  `description` text,
  `button_text` varchar(100) DEFAULT NULL,
  `button_link` varchar(500) DEFAULT NULL,
  `background_image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hero_sections`
--

LOCK TABLES `hero_sections` WRITE;
/*!40000 ALTER TABLE `hero_sections` DISABLE KEYS */;
INSERT INTO `hero_sections` VALUES (1,'Welcome to Our Website','We deliver outstanding solutions tailored to your needs.','Get Started','#contact','hero/digital-lavender-style-interior-design_1__1772450777_69a573d91a008.jpg','active','2026-03-02 10:42:25','2026-03-02 11:26:17');
/*!40000 ALTER TABLE `hero_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `reset_token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reset_token` (`reset_token`),
  KEY `idx_reset_token` (`reset_token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires_at` (`expires_at`),
  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES (1,1,'b8da80cc6eb1c03e334dc85731e5cdb0e752d3df9841b43ec0751597a8ffb923','2026-03-02 08:52:46',0,'2026-03-02 08:22:46'),(2,1,'f9b7ad7a8544651bb9a9d1c835f406feb5db7ad1c4d467b439a5a57156ab090d','2026-03-02 08:52:49',1,'2026-03-02 08:22:49'),(3,1,'7998ab3da76cb7fb751da34a4660598ac9be5b50cc7bb71c593308b466cdb782','2026-03-02 09:54:59',0,'2026-03-02 09:24:59'),(4,1,'cd319220f7860780521936fbb6149a111d2a18c97b03cc4c289d090fc9723394','2026-03-02 09:55:01',0,'2026-03-02 09:25:01'),(5,1,'f8430372bbf12a608a198c811a03d8cd6d122cab2153ee28887856e198da4794','2026-03-02 11:16:54',0,'2026-03-02 10:46:54'),(6,1,'d15ace67d07702a81599dfe6c60f3a7d6ff66a16a47c5b1d2a8b5cc494151756','2026-03-02 11:26:07',0,'2026-03-02 10:56:07'),(7,1,'e7cf53d89bbc4091d504a943b71d3d72133980947ef96c9bcf5fa37fe8f8d387','2026-03-02 13:02:45',0,'2026-03-02 12:32:45'),(8,1,'5ac8aa12bde0966859ae72a15e1cf2e5e1c3cd266f714ce83b2d52a3c5f6e1f0','2026-03-02 13:02:45',0,'2026-03-02 12:32:45'),(9,1,'e22e4a9ac7cbe0319161dcf1b7906e62b97bbae7f38548e31b93a75c05c79757','2026-03-02 13:06:22',0,'2026-03-02 12:36:22'),(10,1,'3513843f0ea7f08cc2571992571a53ed5acc0574a89cfbf26472d42edd561c3b','2026-03-03 05:07:00',0,'2026-03-03 04:37:00'),(11,1,'86919b51d1b374fd1027e0135c951fd71f421a5dd4fe2695710202c162d952cd','2026-03-03 05:31:54',0,'2026-03-03 05:01:54'),(12,1,'108c3dc8d9c2eb18e0d15a22af83a4b1c24d8968c0ba8a479af7870342208fc7','2026-03-03 05:40:59',0,'2026-03-03 05:10:59'),(13,1,'485896d01e634b887750e20f31db848688a17240ed67c7818553ca934b6bee63','2026-03-03 05:41:40',0,'2026-03-03 05:11:40');
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-03 11:50:44
