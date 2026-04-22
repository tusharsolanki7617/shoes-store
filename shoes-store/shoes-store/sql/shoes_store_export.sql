-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for osx10.10 (x86_64)
--
-- Host: localhost    Database: shoes_store
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
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Men\'s Shoes','mens-shoes','Premium footwear collection for men including formal, casual, and sports shoes','2026-02-08 16:57:13'),(2,'Women\'s Shoes','womens-shoes','Stylish and comfortable shoes for women featuring heels, flats, and sneakers','2026-02-08 16:57:13'),(3,'Kids Shoes','kids-shoes','Durable and fun footwear for children of all ages','2026-02-08 16:57:13'),(4,'Sports Shoes','sports-shoes','High-performance athletic shoes for running, training, and outdoor activities','2026-02-08 16:57:13');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_read` (`is_read`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
INSERT INTO `contact_messages` VALUES (3,'ttt','tushar633712@gmail.com','tttewf ewf','tttttttfewfewfew',0,'2026-02-12 16:54:57'),(4,'Tushar Solanki','solankitushar010@gmail.com','asasdssss','ssssss s s s. s s. s s. s s. ss',0,'2026-03-12 02:54:57'),(5,'Tushar Solanki','solankitushar010@gmail.com','dwqedew','gdhasDG D D',0,'2026-04-01 05:13:19');
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_purchase` decimal(10,2) DEFAULT 0.00,
  `max_uses` int(11) DEFAULT 0,
  `used_count` int(11) DEFAULT 0,
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_code` (`code`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
INSERT INTO `coupons` VALUES (1,'WELCOME10','percentage',10.00,2000.00,0,0,'2026-02-08','2026-03-10',1,'2026-02-08 16:57:13'),(2,'FLAT500','fixed',500.00,5000.00,100,0,'2026-02-08','2026-04-09',1,'2026-02-08 16:57:13'),(3,'SUMMER20','percentage',20.00,3000.00,50,0,'2026-02-08','2026-03-25',1,'2026-02-08 16:57:13');
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `size` varchar(10) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_product` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (8,28,20,'Nike Dunk Low Retro SE Leather/Suede','11',10795.00,2,21590.00),(9,29,20,'Nike Dunk Low Retro SE Leather/Suede','10',10795.00,1,10795.00),(10,30,19,'Nike Air Max Excee','9',8695.00,1,8695.00),(11,30,18,'Nike Air Force 1:07','11',7495.00,1,7495.00),(12,31,20,'Nike Dunk Low Retro SE Leather/Suede','7',10795.00,1,10795.00),(13,32,1,'Nike Domain 3 Low','7',13995.00,1,13995.00),(14,33,20,'Nike Dunk Low Retro SE Leather/Suede','9',10795.00,1,10795.00),(15,34,2,'SKY ELITE FF 3/ Special Amit Addition','11',12999.00,1,12999.00),(16,35,19,'Nike Air Max Excee','9',8695.00,1,8695.00),(17,36,19,'Nike Air Max Excee','8',8695.00,1,8695.00),(18,37,15,'Nike x Hyperice Hyperboot','11',72843.00,1,72843.00),(19,38,3,'Nike LD-1000','8',8695.00,1,8695.00),(20,39,1,'Nike Domain 3 Low','8',13995.00,1,13995.00),(21,40,19,'Nike Air Max Excee','9',8695.00,2,17390.00),(22,41,20,'Nike Dunk Low Retro SE Leather/Suede','10',10795.00,1,10795.00),(23,42,20,'Nike Dunk Low Retro SE Leather/Suede','8',10795.00,1,10795.00),(24,42,18,'Nike C1TY','8',8695.00,1,8695.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `order_number` varchar(50) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `coupon_code` varchar(50) DEFAULT NULL,
  `status` enum('pending','processing','shipped','delivered','completed','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `razorpay_payment_id` varchar(100) DEFAULT NULL COMMENT 'Razorpay payment ID returned after successful payment',
  `shipping_address` text DEFAULT NULL,
  `shipping_name` varchar(255) DEFAULT NULL,
  `shipping_phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_order_number` (`order_number`),
  KEY `idx_user_created` (`user_id`,`created_at`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (28,7,'ORD202602184654',21590.00,0.00,21590.00,NULL,'pending','paid','razorpay','pay_SHhQzcL5vhgfVv','dewfew fwe fww few, fewqfefw, fewfwe - 783878','Tushar Solanki','1234567811','2026-02-18 17:59:40','2026-02-18 17:59:40'),(29,7,'ORD202602183466',10795.00,0.00,10795.00,NULL,'pending','paid','razorpay','pay_SHhSuKhZf3NLfK','swedefrgt gtntgt, fewqfefw, fewfwe - 783878','Tushar Solanki','1234567811','2026-02-18 18:01:30','2026-02-18 18:01:30'),(30,7,'ORD202602195682',16190.00,500.00,15690.00,NULL,'delivered','paid','razorpay','pay_SHqRazPg1KpAHO','RK ejfediv sdvsdnv, fewqfefw, fewfwe - 783878','Tushar Solanki','1234567811','2026-02-19 02:48:31','2026-02-19 03:18:57'),(31,7,'ORD202603014368',10795.00,0.00,10795.00,NULL,'pending','paid','razorpay','pay_SM1rRXFuMMa4cN','gy fduvf vs v s kvnjksd, fewqfefw, fewfwe - 783878','Tushar Solanki','1234567811','2026-03-01 16:34:40','2026-03-01 16:34:40'),(32,7,'ORD202603097027',13995.00,0.00,13995.00,NULL,'delivered','paid','razorpay','pay_SOz8SQeoC6IiBf','adsafsdf f sfsdsd fsdf, fewqfefw, fewfwe - 783878','Tushar Solanki','1234567811','2026-03-09 03:51:39','2026-03-09 03:53:36'),(33,7,'ORD202603128430',10795.00,500.00,10295.00,NULL,'delivered','paid','razorpay','pay_SQ9gZxPF5f8mQv','wqugdw dihq iuw hudwqh dwq, fewqfefw, fewfwe - 783878','Tushar Solanki','1234567811','2026-03-12 02:49:53','2026-03-12 02:50:21'),(34,7,'ORD202603218624',12999.00,500.00,12499.00,NULL,'delivered','paid','razorpay','pay_STuBSxwIuiOrov','wfkgwefh we hfh wefh ewfhewfhkefwhk, rajkot, dsfbkjads gkfs - 783878','Tushar Solanki','1234567811','2026-03-21 14:16:02','2026-03-21 14:16:43'),(35,7,'ORD202603238992',8695.00,0.00,8695.00,NULL,'cancelled','paid','cod',NULL,'dsjf sakjfsf ds f sajlfk, fewqfefw, fewfwe - 783878','Tushar Solanki','1234567811','2026-03-23 03:19:24','2026-03-23 03:30:27'),(36,7,'ORD202603236273',8695.00,0.00,8695.00,NULL,'shipped','paid','cod',NULL,'iugf dshvsv. dsvhku, fewqfefw, fewfwe - 783878','Tushar Solanki','1234567811','2026-03-23 03:31:00','2026-03-23 03:33:45'),(37,7,'ORD202603235948',72843.00,0.00,72843.00,NULL,'delivered','paid','cod',NULL,'iguaesfd uf kzcjkvv, fewqfefw, fewfwe - 783878','Tushar Solanki','1234567811','2026-03-23 03:34:13','2026-03-30 07:42:30'),(38,7,'ORD202603307317',8695.00,0.00,8695.00,NULL,'delivered','paid','razorpay','pay_SXMKilrzZis8l1','grhtfjg mv gj, er, fewfwe - 233456','Tushar Solanki','1234567811','2026-03-30 07:45:17','2026-03-30 07:46:05'),(39,7,'ORD202603304145',13995.00,500.00,13495.00,NULL,'pending','paid','razorpay','pay_SXMee28Wl2mTA0','fdsasadsadsa, dsas, dsadas - 345676','Tushar Solanki','1234567811','2026-03-30 08:04:12','2026-03-30 08:04:12'),(40,7,'ORD202603318916',17390.00,0.00,17390.00,NULL,'pending','paid','razorpay','pay_SXuMs9KCyWcKxh','dsvs sv svs, fewqfefw, dsadas - 783878','Tushar Solanki','1234567811','2026-03-31 17:02:50','2026-03-31 17:02:50'),(41,21,'ORD202604013608',10795.00,0.00,10795.00,NULL,'delivered','paid','cod',NULL,'gufaiffabfd, fadf, dfdf - 321244','miral vasoya','1234567888','2026-04-01 03:07:31','2026-04-01 03:08:07'),(42,7,'ORD202604139895',19490.00,0.00,19490.00,NULL,'pending','paid','razorpay','pay_Sd22MCCI7RIpFV','RK University, rajkot, gujrat - 360020','Tushar Solanki','1234567811','2026-04-13 15:48:00','2026-04-13 15:48:00');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `gallery` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gallery`)),
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_category` (`category_id`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_slug` (`slug`),
  KEY `idx_active` (`is_active`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_category_active` (`category_id`,`is_active`),
  KEY `idx_stock` (`stock`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'Nike Domain 3 Low','nike-domain-3-low','Batting, fielding or making run-saving plays at the boundarythe Domain 3 Low can do it all. Responsive React foam helps keep you feeling fresh throughout the match. Added support in the lace area allows you to fine-tune a secure, supportive fit.\r\nColour Shown: Work Blue|Court Blue|Life Lime\r\nStyle: IR5776-400',15995.00,13995.00,46,'img_6996c1676a78e.png','[\"img_6996c1676ab12.png\",\"img_6996c1676abc9.png\",\"img_6996c1676ac95.png\"]',1,1,'2026-02-08 16:57:13','2026-03-30 08:04:12'),(2,1,'SKY ELITE FF 3/ Special Amit Addition','sky-elite-ff-3-special-amit-addition','The SKY ELITE™ FF 3 indoor shoe helps you experience powerful jumps, allowing your game and mind to reach new heights.​\r\n\r\nTo create faster acceleration and set up for higher jumps, this shoe uses a RISETRUSS™ technology unit for a powerful lift-off.​\r\n\r\nThe shoe features a midsole with FF BLAST™ PLUS foam to help create advanced cushioning and a more energized bounce when jumping.​\r\n\r\nLastly, the upper construction is designed with more support to help create a locked down feel, an improved fit, and increased force efficiency.\r\nonly for Amit',12999.00,NULL,74,'img_699712eb0103c.jpg','[\"img_699711a87f92a.png\",\"img_699711a87fa09.png\",\"img_699711a87fa88.png\"]',0,1,'2026-02-08 16:57:13','2026-03-21 14:16:02'),(3,1,'Nike LD-1000','nike-ld-1000','he dramatically flared heel on the LD-1000 was originally created to support long-distance runners. First released in 1977, this fan favourite returns to bring one of Nike&amp;#039;s most famous innovations to the street.\r\nColour Shown: Camo Green|Sail|Black|Sea Glass\r\nStyle: HJ4687-302',10950.00,8695.00,39,'img_69971be7a5121.png','[\"img_69971be7a5b3b.png\",\"img_69971be7a5c8b.jpg\",\"img_69971be7a5d30.png\"]',1,1,'2026-02-08 16:57:13','2026-04-01 02:54:34'),(4,1,'Nike SB Vertebrae','nike-sb-vertebrae','The Vertebrae breaks in fast and breaks down slow, creating a consistent fit straight out of the box. A Nike heritage colour palette gives this modern shoe the perfect touch of retro.\r\nColour Shown: Black|Anthracite|Summit White\r\nStyle: FD4691-001',7495.00,NULL,60,'img_69971d7526eb5.png','[\"img_69971d75274e4.png\",\"img_69971d7527612.jpg\",\"img_69971d7527716.png\"]',0,1,'2026-02-08 16:57:13','2026-02-19 14:25:57'),(5,2,'Nike Court Vision Low','nike-court-vision-low','In love with the old-school style of &#039;80s basketball? Meet the Court Vision. It combines durable real and synthetic leather with a rubber cupsole, bringing you all-day comfort inspired by some of the most iconic silhouettes of our past.\r\nColour Shown: Summit White|Phantom|Tattoo\r\nStyle: IB5873-121',8000.00,6495.00,35,'img_6996c2bf1a1a9.png','[\"img_6996c2bf1a635.png\",\"img_6996c2bf1a7ea.png\",\"img_6996c2bf1a862.png\"]',1,1,'2026-02-08 16:57:13','2026-02-19 07:58:55'),(7,2,'Nike Air Force 1 &#039;07 Next Nature','nike-air-force-1-039-07-next-nature','The radiance lives on in the Air Force 1 &#039;07, the b-ball icon that puts a fresh spin on what you know best: crisp materials, bold colours and the perfect amount of flash to make you shine.\r\nColour Shown: White|Team Crimson\r\nStyle: DC9486-117',11195.00,8195.00,45,'img_69970e7a78a32.png','[\"img_69970e7a7901b.png\",\"img_69970e7a7913c.png\",\"img_69970e7a791e5.png\"]',1,1,'2026-02-08 16:57:13','2026-02-19 13:22:02'),(8,2,'Nike Air Max SC','nike-air-max-sc','With its easy-going lines, heritage athletics look and, of course, visible Air cushioning, the Nike Air Max SC is the perfect finish to any outfit. The rich mixture of materials adds depth while making it a durable and lightweight shoe for everyday wear.\r\nColour Shown: White|Photon Dust\r\nStyle: CW4554-101',5995.00,NULL,30,'img_69970fceca27d.png','[\"img_69970fceca72d.png\",\"img_69970fceca8dd.png\",\"img_69970fceca979.png\"]',0,1,'2026-02-08 16:57:13','2026-02-19 13:27:42'),(9,3,'Nike Court Borough Low Essential+','nike-court-borough-low-essential','Inspired by hooping, designed for everyday life. The Court Borough is reimagined with your younger one&#039;s feet in mind, featuring more room in the toe, elastic laces and a hook-and-loop strap for easy on and off.\r\nColour Shown: White|Moon Particle|Rust Factor\r\nStyle: IQ2726-100',5895.00,3895.00,7,'img_69913092407f8.png','[\"img_6991309240bb6.png\",\"img_6991309240c47.png\",\"img_6991309240cc0.jpg\"]',1,1,'2026-02-08 16:57:13','2026-02-15 02:33:54'),(10,3,'Nike Air Max Nova','nike-air-max-nova','Big cushioning and bold style that&#039;s the Air Max Nova in a nutshell. Airy mesh and sturdy Ripstop fabric keep the upper lightweight but durable. An athletic design gives you a sleek look that&#039;s perfect for everyday wear.\r\nColour Shown: Anthracite|University Red|Black\r\nStyle: FN4446-007',5995.00,NULL,120,'img_6996c451e1b20.png','[\"img_6996c451e1eb3.png\",\"img_6996c451e1fc9.jpg\",\"img_6996c451e2036.png\"]',0,1,'2026-02-08 16:57:13','2026-02-19 08:06:30'),(11,3,'Nike Star Runner 5','nike-star-runner-5','Breathable and lightweight, the Nike Star Runner 5 will have you dreaming about your next run. Springy cushioning and a secure, stabilising fit help you chase after your next record-breaking time.\r\nColour Shown: Midnight Navy|University Blue|Lime Blast|Laser Orange\r\nStyle: HF7004-403',4695.00,NULL,90,'img_6996c75dbc9c6.png','[\"img_6996c75dbcce7.png\",\"img_6996c75dbcd37.jpg\",\"img_6996c75dbcd7b.jpg\"]',0,1,'2026-02-08 16:57:13','2026-02-19 08:18:37'),(13,4,'Nike Alphafly 3','nike-alphafly-3','Shown: Laser Orange/Citron Pulse/Volt Ice/Indigo Burst\r\nStyle: FD8311-800',26894.00,NULL,35,'img_699709fd47969.png','[\"img_699709fd47ca7.png\",\"img_699709fd47cf7.jpg\",\"img_699709fd47d3a.jpg\"]',1,1,'2026-02-08 16:57:13','2026-02-19 13:02:53'),(14,4,'Nike Maxfly 2 Glam','nike-maxfly-2-glam','Fit for the world&#039;s fastest, the Maxfly 2 is back and bolder than ever. Designed for the 100 meters to 400 meters (hurdle events too), it combines an ultra-aggressive feel with the responsiveness of an Air Zoom unit to propel the speediest sprinters on the planet. This pair steps it up with shimmery accents, letting you release your style and your speed.\r\n\r\n\r\nShown: Fierce Purple/Flash Crimson/Pink Blast/Black\r\nStyle: IM9129-500',20145.00,19145.00,40,'img_69970bad04bc1.png','[\"img_69970bad04f00.png\",\"img_69970bad04f8f.jpg\",\"img_69970bad0500f.png\"]',0,1,'2026-02-08 16:57:13','2026-02-19 13:10:05'),(15,4,'Nike x Hyperice Hyperboot','nike-x-hyperice-hyperboot','Optimize your warm-up and recovery routines with the Hyperboot, a Nike x Hyperice collaboration. The wearable technology offers heat and Normatec dynamic air compression for feet and ankles that you can customize on the go.\r\n\r\nShown: Black\r\nStyle: 65000-001',72843.00,NULL,8,'img_69970d3546b1e.png','[\"img_69970d354734e.png\",\"img_69970d3547498.png\",\"img_69970d3547527.png\"]',0,1,'2026-02-08 16:57:13','2026-03-23 03:34:13'),(16,1,'Nike Quest 6','nike-quest-6','The Nike Quest 6 is for runners of all levels. But make no mistake, it&#039;s anything but entry level. A super-comfortable and supportive midfoot fit band helps keep you stable for your miles. Plus, a super-soft midsole foam helps cushion each step.\r\nColour Shown: Black|Dark Smoke Grey\r\nStyle: FD6033-003',7095.00,NULL,10,'img_6996bf0be0b57.png','[\"img_6996bf0be0e12.png\",\"img_6996bf0be0ebd.png\",\"img_6996bf0be0f5f.png\"]',0,1,'2026-02-09 16:09:11','2026-02-19 07:43:07'),(17,1,'Nike Air Force 107','nike-air-force-107','Comfortable, durable and timeless it&#039;s number one for a reason. The classic &#039;80s construction pairs smooth leather with bold details for style that tracks whether you&#039;re on court or on the go.\r\nStyle: CW2288-111',10000.00,7495.00,2323,'img_6996bd24f40c4.png','[\"img_6996bd25001c9.png\",\"img_6996bd25004d0.png\",\"img_6996bd2500518.png\"]',0,1,'2026-02-09 16:10:11','2026-02-19 07:35:01'),(18,1,'Nike C1TY','nike-c1ty','Nike C1TY is engineered to overcome anything the city throws your way. A mesh upper keeps the fit breathable, while the reinforced sides and toe box help protect your feet from the elements. Each colourway is inspired by the spirit of city life giving street style a whole new meaning.\r\nStyle: FZ3863-018',10000.00,8695.00,110,'img_6996bbe417f17.png','[\"img_6996bbe41815e.png\",\"img_6996bbe4181e4.png\",\"img_6996bbe418254.png\"]',0,1,'2026-02-09 16:11:13','2026-04-13 15:48:00'),(19,1,'Nike Air Max Excee','nike-air-max-excee','Get into the groove with the Nike Air Max Excee and subtle pops of fresh colour for style that defies time. Inspired by the Nike Air Max 90, these kicks deliver a modern twist on a legendary icon through elongated design lines and distorted proportions.\r\nColour Shown: Black|Cool Grey|Wolf Grey|White\r\nStyle: FN7304-001',12095.00,8695.00,49,'img_69912cd35a6a0.png','[\"img_69912cd35a9d5.png\",\"img_69912cd35aa32.jpg\",\"img_69912cd35aaff.png\"]',0,1,'2026-02-09 18:32:13','2026-03-31 17:02:50'),(20,1,'Nike Dunk Low Retro SE Leather/Suede','nike-dunk-low-retro-se-leather-suede','You can always count on a classic. This colour-blocked design combines leather and suede with plush padding for game-changing comfort that lasts. The possibilities are endless how will you wear your Dunks?\r\nColour Shown: Pale Ivory|Baroque Brown\r\nStyle: FQ8249-104',10795.00,NULL,39,'img_69912b6b081a4.png','[\"img_69912b6b0bb4c.png\",\"img_69912b6b0bbae.jpg\",\"img_69912b6b0bc6a.png\"]',0,1,'2026-02-10 13:39:35','2026-04-13 15:48:00');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_approved` (`is_approved`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,20,7,4,'fefwefervrvv',1,'2026-02-12 17:44:47'),(2,19,7,4,'t ttfd y fc',1,'2026-02-19 03:13:45'),(4,3,7,4,'amazing product',1,'2026-03-30 07:46:59'),(5,20,21,2,'sss',1,'2026-04-07 02:52:10');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `is_active` tinyint(1) DEFAULT 0,
  `activation_token` varchar(100) DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_activation` (`activation_token`),
  KEY `idx_reset` (`reset_token`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin@shoesstore.com','$2y$10$nSjSj.e8jfLVrU5GMM1F6e2sgGBlbGm6SAeQ52ItsAwkHsWi13PD6','Admin','User','',NULL,'user_1_1775055298.jpeg','admin',1,NULL,NULL,NULL,'2026-02-08 16:57:13','2026-04-01 14:54:58'),(7,'tushar633712@gmail.com','$2y$10$a18IRAT43ed/rfGNXtc7J.qleIbg0YGbWsctUrhREvpFPIruSa2je','Tushar','Solanki','1234567811',NULL,'user_7_1774974222.jpeg','customer',1,NULL,'730123','2026-04-01 10:55:31','2026-02-09 03:00:55','2026-04-01 05:10:31'),(10,'tsolanki299@rku.ac.in','$2y$10$1pYh0MWd9DAq7LriUuPX/Ot0yRLJ9WjUvrU33Z01JDYWdfhhOYwD6','solanki','tushar','1234567811',NULL,NULL,'customer',1,NULL,NULL,NULL,'2026-02-12 13:07:10','2026-02-19 04:05:18'),(21,'mvasoya913@rku.ac.in','$2y$10$frcifsAd1lTHvbcQQoXeieavUgPnvZGCvU9If9aY1rWjGeiR0Ku6m','miral','vasoya','1234567888',NULL,'user_21_1775012732.jpeg','customer',1,NULL,NULL,NULL,'2026-04-01 03:03:35','2026-04-01 03:05:32');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlist`
--

DROP TABLE IF EXISTS `wishlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlist`
--

LOCK TABLES `wishlist` WRITE;
/*!40000 ALTER TABLE `wishlist` DISABLE KEYS */;
INSERT INTO `wishlist` VALUES (7,7,20,'2026-04-13 15:45:37'),(8,7,18,'2026-04-13 15:45:38');
/*!40000 ALTER TABLE `wishlist` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-14 21:08:11
