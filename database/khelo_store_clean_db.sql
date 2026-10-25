-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for osx10.10 (x86_64)
--
-- Host: localhost    Database: boot
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (9,'AutoPlay','autoplay','2026-08-21 06:18:48','2026-08-26 04:23:59');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `keys`
--

DROP TABLE IF EXISTS `keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `keys` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `duration` varchar(255) NOT NULL,
  `license_key` text NOT NULL,
  `is_sold` tinyint(1) NOT NULL DEFAULT 0,
  `telegram_user_id` bigint(20) unsigned DEFAULT NULL,
  `sold_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `renewal_alert_sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `keys_product_id_foreign` (`product_id`),
  KEY `keys_telegram_user_id_foreign` (`telegram_user_id`),
  CONSTRAINT `keys_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `keys_telegram_user_id_foreign` FOREIGN KEY (`telegram_user_id`) REFERENCES `telegram_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=436 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `keys`
--

LOCK TABLES `keys` WRITE;
/*!40000 ALTER TABLE `keys` DISABLE KEYS */;
/*!40000 ALTER TABLE `keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_08_13_130000_create_telegram_tables',1),(5,'2026_08_13_140000_create_categories_table',1),(6,'2026_08_21_121716_add_details_to_products_table',2),(7,'2026_08_25_224500_add_gamification_and_referrals_tables',3),(8,'2026_08_25_175155_add_expiry_tracking_to_keys_table',4),(9,'2026_08_25_181129_create_stock_subscriptions_table',5),(10,'2026_08_26_140000_create_user_product_prices_table',6),(11,'2026_08_26_111453_add_is_reseller_to_telegram_users_table',7),(12,'2026_08_26_114500_add_queue_prices_to_products_table',8),(13,'2026_08_26_120000_add_cost_prices_to_products_table',9),(14,'2026_08_26_155644_add_reseller_prices_to_products_table',10);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `game_name` varchar(255) DEFAULT 'Carrom Pool',
  `image` varchar(255) DEFAULT NULL,
  `price_1d` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_1d` decimal(10,2) DEFAULT NULL,
  `reseller_price_1d` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_3d` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_3d` decimal(10,2) DEFAULT NULL,
  `reseller_price_3d` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_3d_queue` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_3d_queue` decimal(10,2) DEFAULT NULL,
  `reseller_price_3d_queue` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_7d` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_7d` decimal(10,2) DEFAULT NULL,
  `reseller_price_7d` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_7d_queue` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_7d_queue` decimal(10,2) DEFAULT NULL,
  `reseller_price_7d_queue` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_10d` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_10d` decimal(10,2) DEFAULT NULL,
  `reseller_price_10d` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_15d` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_15d` decimal(10,2) DEFAULT NULL,
  `reseller_price_15d` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_30d` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_30d` decimal(10,2) DEFAULT NULL,
  `reseller_price_30d` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_30d_queue` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_30d_queue` decimal(10,2) DEFAULT NULL,
  `reseller_price_30d_queue` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_90d` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_90d` decimal(10,2) DEFAULT NULL,
  `reseller_price_90d` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_90d_queue` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_90d_queue` decimal(10,2) DEFAULT NULL,
  `reseller_price_90d_queue` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_lifetime` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_lifetime` decimal(10,2) DEFAULT NULL,
  `reseller_price_lifetime` decimal(10,2) NOT NULL DEFAULT 0.00,
  `buying_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_category_id_foreign` (`category_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,9,'Aim Carrom King','Carrom Pool',NULL,25.00,NULL,25.00,190.00,180.00,190.00,240.00,230.00,240.00,340.00,330.00,340.00,440.00,430.00,440.00,75.00,NULL,75.00,100.00,NULL,100.00,900.00,880.00,900.00,1150.00,1130.00,1150.00,2080.00,2050.00,2070.00,2525.00,2500.00,2525.00,699.00,NULL,699.00,0.00,1,'2026-08-20 16:54:56','2026-08-28 15:54:06'),(2,9,'Snake Engine','Carrom Pool',NULL,20.00,NULL,20.00,180.00,140.00,180.00,0.00,NULL,0.00,50.00,NULL,50.00,0.00,NULL,0.00,480.00,340.00,480.00,90.00,NULL,90.00,799.00,740.00,799.00,0.00,NULL,0.00,2045.00,1960.00,2045.00,0.00,NULL,0.00,599.00,NULL,599.00,0.00,1,'2026-08-20 16:54:56','2026-08-28 15:48:06'),(3,9,'KOS','Carrom Pool',NULL,99.00,65.00,99.00,45.00,NULL,45.00,0.00,NULL,0.00,250.00,170.00,250.00,0.00,NULL,0.00,85.00,NULL,85.00,360.00,310.00,360.00,590.00,550.00,590.00,0.00,NULL,0.00,380.00,NULL,380.00,0.00,NULL,0.00,799.00,NULL,799.00,0.00,1,'2026-08-20 16:54:56','2026-08-28 15:44:34'),(4,9,'Aim Ai Engine','Carrom Pool',NULL,65.00,5.00,65.00,130.00,NULL,130.00,0.00,NULL,0.00,210.00,NULL,210.00,0.00,NULL,0.00,95.00,NULL,95.00,340.00,NULL,340.00,580.00,NULL,580.00,0.00,NULL,0.00,1650.00,NULL,1650.00,0.00,NULL,0.00,899.00,NULL,899.00,0.00,1,'2026-08-20 16:54:56','2026-08-28 15:39:57'),(75,9,'Snake Engine (8 Ball Pool)','8 Ball Pool',NULL,0.00,NULL,0.00,250.00,180.00,250.00,0.00,NULL,0.00,0.00,NULL,0.00,0.00,NULL,0.00,500.00,440.00,500.00,0.00,NULL,0.00,950.00,880.00,950.00,0.00,NULL,0.00,2440.00,2370.00,2440.00,0.00,NULL,0.00,599.00,NULL,599.00,0.00,1,'2026-08-26 06:22:32','2026-08-28 15:48:55'),(76,9,'KOS (8 Ball Pool)','8 Ball Pool',NULL,110.00,70.00,110.00,0.00,NULL,0.00,0.00,NULL,0.00,299.00,220.00,299.00,0.00,NULL,0.00,0.00,NULL,0.00,540.00,450.00,540.00,950.00,850.00,950.00,0.00,NULL,0.00,0.00,NULL,0.00,0.00,NULL,0.00,799.00,NULL,799.00,0.00,1,'2026-08-26 06:22:32','2026-08-28 15:49:52'),(78,9,'bitAIM+','Carrom Pool',NULL,0.00,NULL,0.00,0.00,NULL,0.00,0.00,NULL,0.00,59.00,40.00,59.00,0.00,NULL,0.00,0.00,NULL,0.00,0.00,NULL,0.00,189.00,130.00,189.00,0.00,NULL,0.00,369.00,300.00,369.00,0.00,NULL,0.00,1999.00,1750.00,1999.00,0.00,1,'2026-08-26 06:42:42','2026-08-28 15:53:00'),(80,9,'Snake Engine (Soccer Star)','Soccer Star',NULL,0.00,NULL,0.00,160.00,120.00,160.00,0.00,NULL,0.00,0.00,NULL,0.00,0.00,NULL,0.00,350.00,270.00,350.00,0.00,NULL,0.00,900.00,520.00,900.00,0.00,NULL,0.00,0.00,NULL,0.00,0.00,NULL,0.00,0.00,NULL,0.00,0.00,1,'2026-08-28 14:53:24','2026-08-28 15:49:16');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referrals`
--

DROP TABLE IF EXISTS `referrals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referrals` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `referrer_id` bigint(20) unsigned NOT NULL,
  `referred_user_id` bigint(20) unsigned NOT NULL,
  `is_rewarded` tinyint(1) NOT NULL DEFAULT 0,
  `reward_amount` decimal(10,2) NOT NULL DEFAULT 30.00,
  `rewarded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `referrals_referrer_id_foreign` (`referrer_id`),
  KEY `referrals_referred_user_id_foreign` (`referred_user_id`),
  CONSTRAINT `referrals_referred_user_id_foreign` FOREIGN KEY (`referred_user_id`) REFERENCES `telegram_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `referrals_referrer_id_foreign` FOREIGN KEY (`referrer_id`) REFERENCES `telegram_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referrals`
--

LOCK TABLES `referrals` WRITE;
/*!40000 ALTER TABLE `referrals` DISABLE KEYS */;
/*!40000 ALTER TABLE `referrals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `spins`
--

DROP TABLE IF EXISTS `spins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `spins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `telegram_user_id` bigint(20) unsigned NOT NULL,
  `amount_won` decimal(10,2) NOT NULL,
  `reward_label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `spins_telegram_user_id_foreign` (`telegram_user_id`),
  CONSTRAINT `spins_telegram_user_id_foreign` FOREIGN KEY (`telegram_user_id`) REFERENCES `telegram_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `spins`
--

LOCK TABLES `spins` WRITE;
/*!40000 ALTER TABLE `spins` DISABLE KEYS */;
/*!40000 ALTER TABLE `spins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_subscriptions`
--

DROP TABLE IF EXISTS `stock_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `telegram_user_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `duration` varchar(255) NOT NULL,
  `is_notified` tinyint(1) NOT NULL DEFAULT 0,
  `notified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product_dur_unique` (`telegram_user_id`,`product_id`,`duration`),
  KEY `stock_subscriptions_product_id_foreign` (`product_id`),
  CONSTRAINT `stock_subscriptions_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_subscriptions_telegram_user_id_foreign` FOREIGN KEY (`telegram_user_id`) REFERENCES `telegram_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_subscriptions`
--

LOCK TABLES `stock_subscriptions` WRITE;
/*!40000 ALTER TABLE `stock_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telegram_users`
--

DROP TABLE IF EXISTS `telegram_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `telegram_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `telegram_id` bigint(20) NOT NULL,
  `referrer_id` bigint(20) unsigned DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_reseller` tinyint(1) NOT NULL DEFAULT 0,
  `last_spin_at` timestamp NULL DEFAULT NULL,
  `total_spins` int(11) NOT NULL DEFAULT 0,
  `total_spin_won` decimal(10,2) NOT NULL DEFAULT 0.00,
  `referral_count` int(11) NOT NULL DEFAULT 0,
  `referral_earnings` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_blocked` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `telegram_users_telegram_id_unique` (`telegram_id`),
  KEY `telegram_users_referrer_id_foreign` (`referrer_id`),
  CONSTRAINT `telegram_users_referrer_id_foreign` FOREIGN KEY (`referrer_id`) REFERENCES `telegram_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `telegram_users`
--

LOCK TABLES `telegram_users` WRITE;
/*!40000 ALTER TABLE `telegram_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `telegram_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `telegram_user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transactions_telegram_user_id_foreign` (`telegram_user_id`),
  CONSTRAINT `transactions_telegram_user_id_foreign` FOREIGN KEY (`telegram_user_id`) REFERENCES `telegram_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_product_prices`
--

DROP TABLE IF EXISTS `user_product_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_product_prices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `telegram_user_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `duration` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_product_prices_user_id_foreign` (`user_id`),
  KEY `user_product_prices_product_id_foreign` (`product_id`),
  KEY `user_product_prices_telegram_user_id_product_id_duration_index` (`telegram_user_id`,`product_id`,`duration`),
  CONSTRAINT `user_product_prices_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_product_prices_telegram_user_id_foreign` FOREIGN KEY (`telegram_user_id`) REFERENCES `telegram_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_product_prices_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_product_prices`
--

LOCK TABLES `user_product_prices` WRITE;
/*!40000 ALTER TABLE `user_product_prices` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_product_prices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin User','admin@khelostore.com',NULL,'$2y$12$SSX/o4FR2jPucZ775NSTheazXUXdLnKrAiukiGeOVvLgobO9OA192',NULL,'2026-08-20 16:53:48','2026-08-28 06:45:47');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-29 17:39:01
