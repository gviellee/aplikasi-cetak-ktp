-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: ktp_management
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
-- Table structure for table `pengajuan_ktp`
--

DROP TABLE IF EXISTS `pengajuan_ktp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengajuan_ktp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nik` varchar(16) NOT NULL,
  `nama_pemohon` varchar(30) NOT NULL,
  `gambar_path` varchar(255) NOT NULL,
  `foto_diri_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','proses','selesai','ditolak') DEFAULT 'pending',
  `alasan_penolakan` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nama_atasan` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pengajuan_ktp_ibfk_1` (`user_id`),
  CONSTRAINT `pengajuan_ktp_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengajuan_ktp`
--

LOCK TABLES `pengajuan_ktp` WRITE;
/*!40000 ALTER TABLE `pengajuan_ktp` DISABLE KEYS */;
INSERT INTO `pengajuan_ktp` VALUES (4,'3224223121342482','Zhafiraa','ktp_6a8e8e3cd63f98.27731480.jpg','foto_diri_admin_5_1787727420_2aec16f0.jpeg','pending',NULL,5,NULL,'2026-08-26 06:57:00','2026-08-26 06:57:00'),(5,'3424656465346343','Salki','ktp_6a955c84885341.21355542.jpg','foto_diri_admin_5_1788173444_ac16e530.jpg','pending',NULL,5,NULL,'2026-08-31 10:50:44','2026-08-31 10:50:44'),(6,'3232384827487837','Yunia','ktp_6a964b1249fff9.91013581.jpg','foto_diri_admin_5_1788234514_11155e12.jpg','pending',NULL,5,NULL,'2026-09-01 03:48:34','2026-09-01 03:48:34'),(7,'3212372727728388','Pram','Array','foto_diri_9_1788242783_19617df6.jpg','pending',NULL,9,NULL,'2026-09-01 06:06:23','2026-09-01 06:06:23'),(8,'3212372727728388','Pram','dokumen_9_1788243060_3bb9d69f.jpg','foto_diri_9_1788243060_2211e366.jpg','selesai',NULL,9,NULL,'2026-09-01 06:11:00','2026-09-03 05:55:11'),(9,'3245893782020001','luna','dokumen_12_1788247153_a3f53af7.jpg','foto_diri_12_1788247153_9a3be45c.jpg','ditolak','data tidak lengkap',12,NULL,'2026-09-01 07:19:13','2026-09-03 05:27:17'),(11,'3233934828328382','Danendra','dokumen_20260903081100_cfaad2a1c92a.jpg','foto_diri_12_1788415860_abda0bf9.jpg','pending',NULL,12,NULL,'2026-09-03 06:11:00','2026-09-03 06:11:00'),(12,'2323928198239819','Zia','ktp_2323928198239819_1788427667_89196de2402e.jpg','foto_diri_2323928198239819_1788427667_89196de2402e.jpg','ditolak','tidak valid',5,NULL,'2026-09-03 09:27:47','2026-09-03 09:52:42'),(13,'3242413134241241','Lia','uploads/images/ktp_1788450760_013859cf.jpeg','uploads/images/foto_diri_1788450760_a8df84b8.jpg','selesai',NULL,5,NULL,'2026-09-03 15:52:40','2026-09-03 15:53:13'),(14,'3214322223445543','Sasa','ktp_5_20260903180954_25011b377f37.jpeg','foto_diri_5_20260903180954_25011b377f37.jpg','selesai',NULL,5,NULL,'2026-09-03 16:09:54','2026-09-06 03:52:43'),(15,'6545644534234354','Alya','ktp_5_20260906055431_3f3de5c6c41e.jpg','foto_diri_5_20260906055431_3f3de5c6c41e.jpeg','ditolak','data tidak sesuai',5,NULL,'2026-09-06 03:54:31','2026-09-06 03:55:12'),(16,'7754543432543665','Gebby','ktp_12_20260906055818_844dccd92739.jpeg','foto_diri_12_20260906055818_844dccd92739.jpg','',NULL,12,NULL,'2026-09-06 03:58:18','2026-09-06 03:58:18'),(17,'1234546754534343','mudya','ktp_12_20260907085144_3d84071607f1.jpg','foto_diri_12_20260907085144_3d84071607f1.jpg','ditolak','data tidak valid',12,NULL,'2026-09-07 06:51:44','2026-09-07 06:53:35'),(18,'3276536245234523','danen','ktp_5_20260907085656_be90edfb2b8b.png','foto_diri_5_20260907085656_be90edfb2b8b.png','',NULL,5,NULL,'2026-09-07 06:56:56','2026-09-07 06:56:56'),(19,'3232838248274872','Gabriel','ktp_5_20260908061340_636cd5827b22.png','foto_diri_5_20260908061340_636cd5827b22.png','',NULL,5,'Pak Arifin','2026-09-08 04:13:40','2026-09-08 04:13:40'),(20,'3245843727321199','Steven','ktp_5_20260908063010_18cc782d9f5b.png','foto_diri_5_20260908063010_18cc782d9f5b.png','selesai',NULL,5,'Pak Yusuf','2026-09-08 04:30:10','2026-09-08 13:37:44'),(21,'3426156153625163','Louis','ktp_5_20260908063049_fdb709ccfb5f.png','foto_diri_5_20260908063049_fdb709ccfb5f.png','',NULL,5,NULL,'2026-09-08 04:30:49','2026-09-08 04:30:49'),(22,'3228372812200011','Vanya','ktp_5_20260908065242_c43986d1981f.png','foto_diri_5_20260908065242_c43986d1981f.png','',NULL,5,'Pak Yusuf','2026-09-08 04:52:42','2026-09-08 04:52:42');
/*!40000 ALTER TABLE `pengajuan_ktp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `password_plain` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (5,'admin','$2y$10$8r6pXgPGslr.UR0l.0lnlu0QRerLkSISSnEtZwsbKP3licAwfgISW',NULL,'admin','2026-08-24 02:24:26'),(9,'Luna','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCwE3e4e8q7R0V1K8X8W','1234','user','2026-09-01 03:51:56'),(12,'unaaa','$2y$10$VonZUG/Ajiw/PlLLrlhU1eU9Z5s8l4E6kU1KTA7VKT9OORXWq6DU2','una1212','user','2026-09-01 07:14:38'),(14,'pram','$2y$10$z.iF5atuTJe8maAWVUen6.YDpFM0sJWGpRTWvHVidBsLF.E2J7zw6','pramudya','user','2026-09-03 15:54:00');
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

-- Dump completed on 2026-09-11  8:55:59
