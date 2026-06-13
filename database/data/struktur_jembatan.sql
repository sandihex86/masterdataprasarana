-- MySQL dump 10.19  Distrib 10.3.35-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: jembatan
-- ------------------------------------------------------
-- Server version	10.3.35-MariaDB

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
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `name` varchar(255) NOT NULL,
  `picture` varchar(64) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `privilege` text DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `webuser` tinyint(4) DEFAULT NULL,
  `mobileuser` tinyint(4) DEFAULT NULL,
  `wilker` text NOT NULL,
  `user_priv` int(11) NOT NULL,
  `active` tinyint(4) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(128) NOT NULL,
  `updated_by` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounts_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `announcement`
--

DROP TABLE IF EXISTS `announcement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `title` varchar(255) NOT NULL,
  `announcement` varchar(255) NOT NULL,
  `picture` varchar(64) NOT NULL,
  `priority` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `article`
--

DROP TABLE IF EXISTS `article`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `publish_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `lead` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `thumbnail` varchar(64) NOT NULL,
  `picture` varchar(64) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `counter` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(10) NOT NULL DEFAULT 'publish' COMMENT 'publish, draft',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bulletine`
--

DROP TABLE IF EXISTS `bulletine`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bulletine` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `publish_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `lead` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `thumbnail` varchar(64) NOT NULL,
  `picture` varchar(64) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `counter` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(10) NOT NULL DEFAULT 'publish' COMMENT 'publish, draft',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chart_konsumsi`
--

DROP TABLE IF EXISTS `chart_konsumsi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chart_konsumsi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `color` varchar(100) DEFAULT NULL,
  `priority` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chart_nilai`
--

DROP TABLE IF EXISTS `chart_nilai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chart_nilai` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pupukid` int(11) NOT NULL,
  `tahunid` int(11) NOT NULL,
  `konsumsiid` int(11) NOT NULL,
  `nilai` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chart_pie`
--

DROP TABLE IF EXISTS `chart_pie`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chart_pie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `title` varchar(255) NOT NULL,
  `nilai` varchar(255) NOT NULL,
  `warna` varchar(64) NOT NULL,
  `priority` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chart_pupuk`
--

DROP TABLE IF EXISTS `chart_pupuk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chart_pupuk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chart_tahun`
--

DROP TABLE IF EXISTS `chart_tahun`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chart_tahun` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `consumption_report`
--

DROP TABLE IF EXISTS `consumption_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consumption_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `publish_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `lead` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `thumbnail` varchar(64) NOT NULL,
  `picture` varchar(64) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `counter` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(10) NOT NULL DEFAULT 'publish' COMMENT 'publish, draft',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `database_chart`
--

DROP TABLE IF EXISTS `database_chart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `database_chart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `publish_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `lead` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `thumbnail` varchar(64) NOT NULL,
  `picture` varchar(64) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `counter` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(10) NOT NULL DEFAULT 'publish' COMMENT 'publish, draft',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `event`
--

DROP TABLE IF EXISTS `event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `publish_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `lead` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `thumbnail` varchar(64) NOT NULL,
  `picture` varchar(64) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `counter` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faq`
--

DROP TABLE IF EXISTS `faq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faq` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `priority` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gallery`
--

DROP TABLE IF EXISTS `gallery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `category` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `thumbnail` varchar(64) NOT NULL,
  `picture` varchar(64) NOT NULL,
  `counter` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `governance`
--

DROP TABLE IF EXISTS `governance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `governance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `publish_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `lead` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `thumbnail` varchar(64) NOT NULL,
  `picture` varchar(64) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `counter` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(10) NOT NULL DEFAULT 'publish' COMMENT 'publish, draft',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `government_relationship`
--

DROP TABLE IF EXISTS `government_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `government_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `publish_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `lead` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `thumbnail` varchar(64) NOT NULL,
  `picture` varchar(64) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `counter` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(10) NOT NULL DEFAULT 'publish' COMMENT 'publish, draft',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `highlight`
--

DROP TABLE IF EXISTS `highlight`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `highlight` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `title` varchar(255) NOT NULL,
  `highlight` varchar(255) NOT NULL,
  `picture` varchar(64) NOT NULL,
  `priority` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inquiry`
--

DROP TABLE IF EXISTS `inquiry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inquiry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inquiry_cat`
--

DROP TABLE IF EXISTS `inquiry_cat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inquiry_cat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `priority` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `active` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan`
--

DROP TABLE IF EXISTS `m_jembatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `wil_ker` varchar(255) DEFAULT NULL,
  `id_prov` varchar(32) DEFAULT NULL,
  `id_kabkot` varchar(32) DEFAULT NULL,
  `wil_op` varchar(32) DEFAULT NULL,
  `lat` varchar(32) NOT NULL,
  `lon` varchar(32) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `lintas` varchar(16) DEFAULT NULL,
  `stasiun1` varchar(32) DEFAULT NULL,
  `stasiun2` varchar(32) DEFAULT NULL,
  `no_bh` varchar(32) DEFAULT NULL,
  `arah_bh` varchar(255) DEFAULT NULL,
  `jenis` varchar(255) DEFAULT NULL,
  `km_hm` varchar(16) DEFAULT NULL,
  `foto1` varchar(255) DEFAULT NULL,
  `foto2` varchar(255) DEFAULT NULL,
  `foto3` varchar(255) DEFAULT NULL,
  `foto4` varchar(255) DEFAULT NULL,
  `caption1` varchar(255) DEFAULT NULL,
  `caption2` varchar(255) DEFAULT NULL,
  `caption3` varchar(255) DEFAULT NULL,
  `caption4` varchar(255) DEFAULT NULL,
  `dokumen` varchar(255) DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `active` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `statusdata` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_prov` (`tanggal`),
  KEY `tanggal` (`tanggal`),
  KEY `uniqid` (`uniqid`)
) ENGINE=InnoDB AUTO_INCREMENT=3078 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_baja`
--

DROP TABLE IF EXISTS `m_jembatan_baja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_baja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_jembatan` varchar(64) NOT NULL,
  `no_bentang` varchar(16) DEFAULT NULL,
  `pjg_bentang` varchar(16) DEFAULT NULL,
  `material` varchar(255) DEFAULT NULL,
  `jembatan_baja` varchar(255) DEFAULT NULL,
  `gelagar_rasuk` varchar(255) DEFAULT NULL,
  `rangka_dinding` varchar(255) DEFAULT NULL,
  `tipe_perletakan` varchar(255) DEFAULT NULL,
  `urut` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_jembatan`)
) ENGINE=InnoDB AUTO_INCREMENT=4991 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_bawah`
--

DROP TABLE IF EXISTS `m_jembatan_bawah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_bawah` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_jembatan` varchar(64) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `nomor` varchar(255) DEFAULT NULL,
  `material` varchar(255) DEFAULT NULL,
  `tipe` varchar(255) DEFAULT NULL,
  `manteling` varchar(255) DEFAULT NULL,
  `jenis` varchar(255) DEFAULT NULL,
  `urut` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_jembatan`)
) ENGINE=InnoDB AUTO_INCREMENT=14049 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_bentang`
--

DROP TABLE IF EXISTS `m_jembatan_bentang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_bentang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_jembatan` varchar(64) NOT NULL,
  `pjg_bentang` varchar(16) DEFAULT NULL,
  `urut` tinyint(4) NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`,`active`),
  KEY `id_survey` (`id_jembatan`)
) ENGINE=InnoDB AUTO_INCREMENT=8764 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_beton`
--

DROP TABLE IF EXISTS `m_jembatan_beton`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_beton` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_jembatan` varchar(64) NOT NULL,
  `no_bentang` varchar(16) DEFAULT NULL,
  `pjg_bentang` varchar(16) DEFAULT NULL,
  `material` varchar(255) DEFAULT NULL,
  `jembatan_beton` varchar(255) DEFAULT NULL,
  `gelagar_beton` varchar(255) DEFAULT NULL,
  `tipe_perletakan` varchar(255) DEFAULT NULL,
  `urut` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_jembatan`)
) ENGINE=InnoDB AUTO_INCREMENT=3774 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_bu300922`
--

DROP TABLE IF EXISTS `m_jembatan_bu300922`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_bu300922` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_prov` varchar(255) DEFAULT NULL,
  `id_kabkot` varchar(255) DEFAULT NULL,
  `kode` varchar(255) DEFAULT NULL,
  `lat` varchar(32) NOT NULL,
  `lon` varchar(32) NOT NULL,
  `wil_op` varchar(32) DEFAULT NULL,
  `wil_ker` varchar(255) DEFAULT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `lintas` varchar(255) DEFAULT NULL,
  `stasiun1` varchar(32) DEFAULT NULL,
  `stasiun2` varchar(32) DEFAULT NULL,
  `thn_bng` varchar(4) DEFAULT NULL,
  `thn_ops` varchar(4) DEFAULT NULL,
  `no_bh` varchar(32) DEFAULT NULL,
  `arah_bh` varchar(128) DEFAULT NULL,
  `jenis` varchar(255) DEFAULT NULL,
  `panjang_m` varchar(16) DEFAULT NULL,
  `banyak_unt` varchar(16) DEFAULT NULL,
  `bentang` varchar(16) DEFAULT NULL,
  `km` varchar(16) DEFAULT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`),
  KEY `uniqid` (`uniqid`,`kode`,`active`),
  KEY `id_prov` (`id_prov`,`id_kabkot`),
  KEY `wil_op` (`wil_op`)
) ENGINE=InnoDB AUTO_INCREMENT=2686 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_bu_250922`
--

DROP TABLE IF EXISTS `m_jembatan_bu_250922`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_bu_250922` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_prov` varchar(255) DEFAULT NULL,
  `id_kabkot` varchar(255) DEFAULT NULL,
  `kode` varchar(255) DEFAULT NULL,
  `lat` varchar(32) NOT NULL,
  `lon` varchar(32) NOT NULL,
  `wil_op` varchar(32) DEFAULT NULL,
  `wil_ker` varchar(255) DEFAULT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `lintas` varchar(255) DEFAULT NULL,
  `stasiun1` varchar(32) DEFAULT NULL,
  `stasiun2` varchar(32) DEFAULT NULL,
  `thn_bng` varchar(4) DEFAULT NULL,
  `thn_ops` varchar(4) DEFAULT NULL,
  `no_bh` varchar(32) DEFAULT NULL,
  `arah_bh` varchar(128) DEFAULT NULL,
  `jenis` varchar(255) DEFAULT NULL,
  `panjang_m` varchar(16) DEFAULT NULL,
  `banyak_unt` varchar(16) DEFAULT NULL,
  `bentang` varchar(16) DEFAULT NULL,
  `km` varchar(16) DEFAULT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`),
  KEY `uniqid` (`uniqid`,`kode`,`active`),
  KEY `id_prov` (`id_prov`,`id_kabkot`),
  KEY `wil_op` (`wil_op`)
) ENGINE=InnoDB AUTO_INCREMENT=835 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_detail`
--

DROP TABLE IF EXISTS `m_jembatan_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `kode_jembatan` varchar(255) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `pemeriksa` varchar(255) DEFAULT NULL,
  `lat` varchar(32) NOT NULL,
  `lon` varchar(32) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `lintas` varchar(16) DEFAULT NULL,
  `stasiun1` varchar(32) DEFAULT NULL,
  `stasiun2` varchar(32) DEFAULT NULL,
  `no_bh` varchar(32) DEFAULT NULL,
  `arah_bh` varchar(255) DEFAULT NULL,
  `jenis` varchar(255) DEFAULT NULL,
  `km_hm` varchar(16) DEFAULT NULL,
  `foto1` varchar(255) DEFAULT NULL,
  `foto2` varchar(255) DEFAULT NULL,
  `foto3` varchar(255) DEFAULT NULL,
  `foto4` varchar(255) DEFAULT NULL,
  `caption1` varchar(255) DEFAULT NULL,
  `caption2` varchar(255) DEFAULT NULL,
  `caption3` varchar(255) DEFAULT NULL,
  `caption4` varchar(255) DEFAULT NULL,
  `dokumen` varchar(255) DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `active` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `statusdata` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_prov` (`kode_jembatan`,`tanggal`),
  KEY `kode_jembatan` (`kode_jembatan`),
  KEY `tanggal` (`tanggal`),
  KEY `uniqid` (`uniqid`)
) ENGINE=InnoDB AUTO_INCREMENT=3145 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_detil_3`
--

DROP TABLE IF EXISTS `m_jembatan_detil_3`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_detil_3` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_jembatan` varchar(64) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pelindung_arus_material` varchar(255) DEFAULT NULL,
  `pelindung_arus_tipe` varchar(255) DEFAULT NULL,
  `pengarah_arus_material` varchar(255) DEFAULT NULL,
  `pengarah_arus_tipe` varchar(255) DEFAULT NULL,
  `pelindung_longsoran_material` varchar(255) DEFAULT NULL,
  `pelindung_longsoran_tipe` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_jembatan`)
) ENGINE=InnoDB AUTO_INCREMENT=5286 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_nilai_atas`
--

DROP TABLE IF EXISTS `m_jembatan_nilai_atas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_nilai_atas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_jembatan` varchar(64) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `bentang` tinyint(4) DEFAULT NULL,
  `perletakan` tinyint(4) DEFAULT NULL,
  `urut` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_jembatan`)
) ENGINE=InnoDB AUTO_INCREMENT=8764 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_nilai_bawah`
--

DROP TABLE IF EXISTS `m_jembatan_nilai_bawah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_nilai_bawah` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_jembatan` varchar(64) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `jenis` varchar(255) DEFAULT NULL,
  `kondisi1` tinyint(4) DEFAULT NULL,
  `kondisi2` tinyint(4) DEFAULT NULL,
  `urut` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_jembatan`)
) ENGINE=InnoDB AUTO_INCREMENT=14049 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_nilai_pelindung`
--

DROP TABLE IF EXISTS `m_jembatan_nilai_pelindung`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_nilai_pelindung` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_jembatan` varchar(64) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `pelindung1` tinyint(4) DEFAULT NULL,
  `pelindung2` tinyint(4) DEFAULT NULL,
  `pelindung3` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_jembatan`)
) ENGINE=InnoDB AUTO_INCREMENT=5286 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_nilai_review`
--

DROP TABLE IF EXISTS `m_jembatan_nilai_review`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_nilai_review` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_survey` varchar(64) NOT NULL,
  `userid` varchar(32) NOT NULL,
  `dokumen` varchar(255) DEFAULT NULL,
  `status` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `atas1` tinyint(4) DEFAULT NULL,
  `atas2` tinyint(4) DEFAULT NULL,
  `atas3` tinyint(4) DEFAULT NULL,
  `atas4` tinyint(4) DEFAULT NULL,
  `atas5` tinyint(4) DEFAULT NULL,
  `atas6` tinyint(4) DEFAULT NULL,
  `bawah1` tinyint(4) DEFAULT NULL,
  `bawah2` tinyint(4) DEFAULT NULL,
  `bawah3` tinyint(4) DEFAULT NULL,
  `bawah4` tinyint(4) DEFAULT NULL,
  `bawah5` tinyint(4) DEFAULT NULL,
  `bawah6` tinyint(4) DEFAULT NULL,
  `bawah7` tinyint(4) DEFAULT NULL,
  `bawah8` tinyint(4) DEFAULT NULL,
  `pelindung1` tinyint(4) DEFAULT NULL,
  `pelindung2` tinyint(4) DEFAULT NULL,
  `pelindung3` tinyint(4) DEFAULT NULL,
  `total` float DEFAULT NULL,
  `kesimpulan` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_survey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_nilai_total`
--

DROP TABLE IF EXISTS `m_jembatan_nilai_total`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_nilai_total` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_jembatan` varchar(64) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `total` float DEFAULT NULL,
  `kesimpulan` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_jembatan`)
) ENGINE=InnoDB AUTO_INCREMENT=5285 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_perawatan`
--

DROP TABLE IF EXISTS `m_jembatan_perawatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_perawatan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `kode_jembatan` varchar(255) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `pemeriksa` varchar(255) DEFAULT NULL,
  `lat` varchar(32) NOT NULL,
  `lon` varchar(32) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `lintas` varchar(16) DEFAULT NULL,
  `stasiun1` varchar(32) DEFAULT NULL,
  `stasiun2` varchar(32) DEFAULT NULL,
  `no_bh` varchar(32) DEFAULT NULL,
  `arah_bh` varchar(255) DEFAULT NULL,
  `jenis` varchar(255) DEFAULT NULL,
  `km_hm` varchar(16) DEFAULT NULL,
  `dokumen` varchar(255) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `iduser` varchar(100) DEFAULT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_prov` (`kode_jembatan`,`tanggal`),
  KEY `kode_jembatan` (`kode_jembatan`),
  KEY `tanggal` (`tanggal`),
  KEY `uniqid` (`uniqid`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_profil`
--

DROP TABLE IF EXISTS `m_jembatan_profil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_profil` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_jembatan` varchar(64) NOT NULL,
  `perpotongan` varchar(255) DEFAULT NULL,
  `jml_lintasan` tinyint(4) DEFAULT NULL,
  `jml_bentang` tinyint(4) DEFAULT NULL,
  `pjg_bentang1` varchar(16) DEFAULT NULL,
  `pjg_bentang2` varchar(16) DEFAULT NULL,
  `pjg_bentang3` varchar(16) DEFAULT NULL,
  `pjg_total` varchar(32) DEFAULT NULL,
  `thn_selesai` varchar(4) DEFAULT NULL,
  `rm_bgn_atas` varchar(16) DEFAULT NULL,
  `rm_bgn_bawah` varchar(16) DEFAULT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`,`jml_lintasan`,`active`),
  KEY `id_survey` (`id_jembatan`)
) ENGINE=InnoDB AUTO_INCREMENT=5286 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_survey`
--

DROP TABLE IF EXISTS `m_jembatan_survey`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_survey` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `kode_jembatan` varchar(255) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `pemeriksa` varchar(255) DEFAULT NULL,
  `lat` varchar(32) NOT NULL,
  `lon` varchar(32) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `lintas` varchar(16) DEFAULT NULL,
  `stasiun1` varchar(32) DEFAULT NULL,
  `stasiun2` varchar(32) DEFAULT NULL,
  `no_bh` varchar(32) DEFAULT NULL,
  `arah_bh` varchar(255) DEFAULT NULL,
  `jenis` varchar(255) DEFAULT NULL,
  `km_hm` varchar(16) DEFAULT NULL,
  `foto1` varchar(255) DEFAULT NULL,
  `foto2` varchar(255) DEFAULT NULL,
  `foto3` varchar(255) DEFAULT NULL,
  `foto4` varchar(255) DEFAULT NULL,
  `caption1` varchar(255) DEFAULT NULL,
  `caption2` varchar(255) DEFAULT NULL,
  `caption3` varchar(255) DEFAULT NULL,
  `caption4` varchar(255) DEFAULT NULL,
  `dokumen` varchar(255) DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `active` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `statusdata` int(11) NOT NULL,
  `iduser` varchar(100) DEFAULT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_prov` (`kode_jembatan`,`tanggal`),
  KEY `kode_jembatan` (`kode_jembatan`),
  KEY `tanggal` (`tanggal`),
  KEY `uniqid` (`uniqid`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_survey_baja`
--

DROP TABLE IF EXISTS `m_jembatan_survey_baja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_survey_baja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_survey` varchar(64) NOT NULL,
  `no_bentang` varchar(16) DEFAULT NULL,
  `pjg_bentang` varchar(16) DEFAULT NULL,
  `material` varchar(255) DEFAULT NULL,
  `jembatan_baja` varchar(255) DEFAULT NULL,
  `gelagar_rasuk` varchar(255) DEFAULT NULL,
  `rangka_dinding` varchar(255) DEFAULT NULL,
  `tipe_perletakan` varchar(255) DEFAULT NULL,
  `urut` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_survey`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_survey_baja1`
--

DROP TABLE IF EXISTS `m_jembatan_survey_baja1`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_survey_baja1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_survey` varchar(64) NOT NULL,
  `j1_no_bentang` varchar(16) DEFAULT NULL,
  `j1_pjg_bentang` varchar(16) DEFAULT NULL,
  `j1_material` varchar(255) DEFAULT NULL,
  `j1_jembatan_baja` varchar(255) DEFAULT NULL,
  `j1_gelagar_rasuk` varchar(255) DEFAULT NULL,
  `j1_rangka_dinding` varchar(255) DEFAULT NULL,
  `j1_tipe_perletakan` varchar(255) DEFAULT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `j2_no_bentang` varchar(255) DEFAULT NULL,
  `j2_pjg_bentang` varchar(255) DEFAULT NULL,
  `j2_material` varchar(255) DEFAULT NULL,
  `j2_jembatan_baja` varchar(255) DEFAULT NULL,
  `j2_gelagar_rasuk` varchar(255) DEFAULT NULL,
  `j2_rangka_dinding` varchar(255) DEFAULT NULL,
  `j2_tipe_perletakan` varchar(255) DEFAULT NULL,
  `j3_no_bentang` varchar(255) DEFAULT NULL,
  `j3_pjg_bentang` varchar(255) DEFAULT NULL,
  `j3_material` varchar(255) DEFAULT NULL,
  `j3_jembatan_baja` varchar(255) DEFAULT NULL,
  `j3_gelagar_rasuk` varchar(255) DEFAULT NULL,
  `j3_rangka_dinding` varchar(255) DEFAULT NULL,
  `j3_tipe_perletakan` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_survey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_survey_bawah`
--

DROP TABLE IF EXISTS `m_jembatan_survey_bawah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_survey_bawah` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_survey` varchar(64) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `nomor` varchar(255) DEFAULT NULL,
  `material` varchar(255) DEFAULT NULL,
  `tipe` varchar(255) DEFAULT NULL,
  `manteling` varchar(255) DEFAULT NULL,
  `jenis` varchar(255) DEFAULT NULL,
  `urut` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_survey`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_survey_bentang`
--

DROP TABLE IF EXISTS `m_jembatan_survey_bentang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_survey_bentang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_survey` varchar(64) NOT NULL,
  `pjg_bentang` varchar(16) DEFAULT NULL,
  `urut` tinyint(4) NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`,`active`),
  KEY `id_survey` (`id_survey`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_survey_beton`
--

DROP TABLE IF EXISTS `m_jembatan_survey_beton`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_survey_beton` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_survey` varchar(64) NOT NULL,
  `no_bentang` varchar(16) DEFAULT NULL,
  `pjg_bentang` varchar(16) DEFAULT NULL,
  `material` varchar(255) DEFAULT NULL,
  `jembatan_beton` varchar(255) DEFAULT NULL,
  `gelagar_beton` varchar(255) DEFAULT NULL,
  `tipe_perletakan` varchar(255) DEFAULT NULL,
  `urut` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_survey`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_survey_beton1`
--

DROP TABLE IF EXISTS `m_jembatan_survey_beton1`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_survey_beton1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_survey` varchar(64) NOT NULL,
  `j1_no_bentang` varchar(16) DEFAULT NULL,
  `j1_pjg_bentang` varchar(16) DEFAULT NULL,
  `j1_material` varchar(255) DEFAULT NULL,
  `j1_jembatan_beton` varchar(255) DEFAULT NULL,
  `j1_gelagar_beton` varchar(255) DEFAULT NULL,
  `j1_tipe_perletakan` varchar(255) DEFAULT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `j2_no_bentang` varchar(255) DEFAULT NULL,
  `j2_pjg_bentang` varchar(255) DEFAULT NULL,
  `j2_material` varchar(255) DEFAULT NULL,
  `j2_jembatan_beton` varchar(255) DEFAULT NULL,
  `j2_gelagar_beton` varchar(255) DEFAULT NULL,
  `j2_tipe_perletakan` varchar(255) DEFAULT NULL,
  `j3_no_bentang` varchar(255) DEFAULT NULL,
  `j3_pjg_bentang` varchar(255) DEFAULT NULL,
  `j3_material` varchar(255) DEFAULT NULL,
  `j3_jembatan_beton` varchar(255) DEFAULT NULL,
  `j3_gelagar_beton` varchar(255) DEFAULT NULL,
  `j3_tipe_perletakan` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_survey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_survey_detil_2`
--

DROP TABLE IF EXISTS `m_jembatan_survey_detil_2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_survey_detil_2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_survey` varchar(64) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `abutment_no` varchar(255) DEFAULT NULL,
  `abutment_material` varchar(255) DEFAULT NULL,
  `abutment_tipe` varchar(255) DEFAULT NULL,
  `abutment_manteling` varchar(255) DEFAULT NULL,
  `pilar_no` varchar(255) DEFAULT NULL,
  `pilar_material` varchar(255) DEFAULT NULL,
  `pilar_tipe` varchar(255) DEFAULT NULL,
  `pilar_manteling` varchar(255) DEFAULT NULL,
  `pilar2_no` varchar(255) DEFAULT NULL,
  `pilar2_material` varchar(255) DEFAULT NULL,
  `pilar2_tipe` varchar(255) DEFAULT NULL,
  `pilar2_manteling` varchar(255) DEFAULT NULL,
  `abutment2_no` varchar(255) DEFAULT NULL,
  `abutment2_material` varchar(255) DEFAULT NULL,
  `abutment2_tipe` varchar(255) DEFAULT NULL,
  `abutment2_manteling` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_survey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_survey_detil_3`
--

DROP TABLE IF EXISTS `m_jembatan_survey_detil_3`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_survey_detil_3` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_survey` varchar(64) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pelindung_arus_material` varchar(255) DEFAULT NULL,
  `pelindung_arus_tipe` varchar(255) DEFAULT NULL,
  `pengarah_arus_material` varchar(255) DEFAULT NULL,
  `pengarah_arus_tipe` varchar(255) DEFAULT NULL,
  `pelindung_longsoran_material` varchar(255) DEFAULT NULL,
  `pelindung_longsoran_tipe` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_survey`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_survey_nilai`
--

DROP TABLE IF EXISTS `m_jembatan_survey_nilai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_survey_nilai` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_survey` varchar(64) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `atas1` tinyint(4) DEFAULT NULL,
  `atas2` tinyint(4) DEFAULT NULL,
  `atas3` tinyint(4) DEFAULT NULL,
  `atas4` tinyint(4) DEFAULT NULL,
  `atas5` tinyint(4) DEFAULT NULL,
  `atas6` tinyint(4) DEFAULT NULL,
  `bawah1` tinyint(4) DEFAULT NULL,
  `bawah2` tinyint(4) DEFAULT NULL,
  `bawah3` tinyint(4) DEFAULT NULL,
  `bawah4` tinyint(4) DEFAULT NULL,
  `bawah5` tinyint(4) DEFAULT NULL,
  `bawah6` tinyint(4) DEFAULT NULL,
  `bawah7` tinyint(4) DEFAULT NULL,
  `bawah8` tinyint(4) DEFAULT NULL,
  `pelindung1` tinyint(4) DEFAULT NULL,
  `pelindung2` tinyint(4) DEFAULT NULL,
  `pelindung3` tinyint(4) DEFAULT NULL,
  `total` float DEFAULT NULL,
  `kesimpulan` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_survey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_survey_nilai_atas`
--

DROP TABLE IF EXISTS `m_jembatan_survey_nilai_atas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_survey_nilai_atas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_survey` varchar(64) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `bentang` tinyint(4) DEFAULT NULL,
  `perletakan` tinyint(4) DEFAULT NULL,
  `urut` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_survey`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_survey_nilai_bawah`
--

DROP TABLE IF EXISTS `m_jembatan_survey_nilai_bawah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_survey_nilai_bawah` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_survey` varchar(64) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `jenis` varchar(255) DEFAULT NULL,
  `kondisi1` tinyint(4) DEFAULT NULL,
  `kondisi2` tinyint(4) DEFAULT NULL,
  `urut` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_survey`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_survey_nilai_pelindung`
--

DROP TABLE IF EXISTS `m_jembatan_survey_nilai_pelindung`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_survey_nilai_pelindung` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_survey` varchar(64) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `pelindung1` tinyint(4) DEFAULT NULL,
  `pelindung2` tinyint(4) DEFAULT NULL,
  `pelindung3` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_survey`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_survey_nilai_review`
--

DROP TABLE IF EXISTS `m_jembatan_survey_nilai_review`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_survey_nilai_review` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_survey` varchar(64) NOT NULL,
  `userid` varchar(32) NOT NULL,
  `dokumen` varchar(255) DEFAULT NULL,
  `status` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `atas1` tinyint(4) DEFAULT NULL,
  `atas2` tinyint(4) DEFAULT NULL,
  `atas3` tinyint(4) DEFAULT NULL,
  `atas4` tinyint(4) DEFAULT NULL,
  `atas5` tinyint(4) DEFAULT NULL,
  `atas6` tinyint(4) DEFAULT NULL,
  `bawah1` tinyint(4) DEFAULT NULL,
  `bawah2` tinyint(4) DEFAULT NULL,
  `bawah3` tinyint(4) DEFAULT NULL,
  `bawah4` tinyint(4) DEFAULT NULL,
  `bawah5` tinyint(4) DEFAULT NULL,
  `bawah6` tinyint(4) DEFAULT NULL,
  `bawah7` tinyint(4) DEFAULT NULL,
  `bawah8` tinyint(4) DEFAULT NULL,
  `pelindung1` tinyint(4) DEFAULT NULL,
  `pelindung2` tinyint(4) DEFAULT NULL,
  `pelindung3` tinyint(4) DEFAULT NULL,
  `total` float DEFAULT NULL,
  `kesimpulan` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_survey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_survey_nilai_total`
--

DROP TABLE IF EXISTS `m_jembatan_survey_nilai_total`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_survey_nilai_total` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_survey` varchar(64) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `total` float DEFAULT NULL,
  `kesimpulan` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_survey`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_survey_profil`
--

DROP TABLE IF EXISTS `m_jembatan_survey_profil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_survey_profil` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_survey` varchar(64) NOT NULL,
  `perpotongan` varchar(255) DEFAULT NULL,
  `jml_lintasan` tinyint(4) DEFAULT NULL,
  `jml_bentang` tinyint(4) DEFAULT NULL,
  `pjg_bentang1` varchar(16) DEFAULT NULL,
  `pjg_bentang2` varchar(16) DEFAULT NULL,
  `pjg_bentang3` varchar(16) DEFAULT NULL,
  `pjg_total` varchar(32) DEFAULT NULL,
  `thn_selesai` varchar(4) DEFAULT NULL,
  `rm_bgn_atas` varchar(16) DEFAULT NULL,
  `rm_bgn_bawah` varchar(16) DEFAULT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`,`jml_lintasan`,`active`),
  KEY `id_survey` (`id_survey`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_survey_review`
--

DROP TABLE IF EXISTS `m_jembatan_survey_review`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_survey_review` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_survey` varchar(64) NOT NULL,
  `userid` varchar(32) NOT NULL,
  `tahap` tinyint(4) NOT NULL,
  `catatan` text NOT NULL,
  `status` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_survey`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_survey_review_dokumen`
--

DROP TABLE IF EXISTS `m_jembatan_survey_review_dokumen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_survey_review_dokumen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_survey` varchar(64) NOT NULL,
  `userid` varchar(32) NOT NULL,
  `dokumen` varchar(255) DEFAULT NULL,
  `status` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_survey`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_survey_review_nilai_atas`
--

DROP TABLE IF EXISTS `m_jembatan_survey_review_nilai_atas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_survey_review_nilai_atas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_survey` varchar(64) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `bentang` tinyint(4) DEFAULT NULL,
  `perletakan` tinyint(4) DEFAULT NULL,
  `urut` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_survey`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_survey_review_nilai_bawah`
--

DROP TABLE IF EXISTS `m_jembatan_survey_review_nilai_bawah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_survey_review_nilai_bawah` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_survey` varchar(64) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `jenis` varchar(255) DEFAULT NULL,
  `kondisi1` tinyint(4) DEFAULT NULL,
  `kondisi2` tinyint(4) DEFAULT NULL,
  `urut` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_survey`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_survey_review_nilai_pelindung`
--

DROP TABLE IF EXISTS `m_jembatan_survey_review_nilai_pelindung`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_survey_review_nilai_pelindung` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_survey` varchar(64) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `pelindung1` tinyint(4) DEFAULT NULL,
  `pelindung2` tinyint(4) DEFAULT NULL,
  `pelindung3` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_survey`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_survey_review_nilai_total`
--

DROP TABLE IF EXISTS `m_jembatan_survey_review_nilai_total`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_survey_review_nilai_total` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_survey` varchar(64) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `total` float DEFAULT NULL,
  `kesimpulan` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_survey`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jembatan_survey_status`
--

DROP TABLE IF EXISTS `m_jembatan_survey_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_jembatan_survey_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_survey` varchar(64) NOT NULL,
  `userid` varchar(100) DEFAULT NULL,
  `progress` tinyint(4) NOT NULL,
  `status` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `id_survey` (`id_survey`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_kabkot`
--

DROP TABLE IF EXISTS `m_kabkot`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_kabkot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_prov` varchar(32) NOT NULL,
  `kode` varchar(255) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`),
  KEY `uniqid` (`uniqid`,`kode`,`active`)
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_lintas`
--

DROP TABLE IF EXISTS `m_lintas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_lintas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `kode` varchar(255) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`,`kode`,`active`)
) ENGINE=InnoDB AUTO_INCREMENT=179 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_petak`
--

DROP TABLE IF EXISTS `m_petak`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_petak` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `lat1` varchar(32) DEFAULT NULL,
  `lon1` varchar(32) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `lat2` varchar(32) DEFAULT NULL,
  `lon2` varchar(32) DEFAULT NULL,
  `jarak` varchar(8) DEFAULT NULL,
  `koordinat` text DEFAULT NULL,
  `keterangan` varchar(64) DEFAULT NULL,
  `kondisi` varchar(64) DEFAULT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`,`kondisi`),
  KEY `nama` (`nama`)
) ENGINE=InnoDB AUTO_INCREMENT=727 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_provinsi`
--

DROP TABLE IF EXISTS `m_provinsi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_provinsi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `kode` varchar(255) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`),
  KEY `uniqid` (`uniqid`,`kode`,`active`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_stasiun`
--

DROP TABLE IF EXISTS `m_stasiun`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_stasiun` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_prov` varchar(255) DEFAULT NULL,
  `id_kabkot` varchar(255) NOT NULL,
  `kode` varchar(255) NOT NULL,
  `lat` varchar(32) DEFAULT NULL,
  `lon` varchar(32) DEFAULT NULL,
  `wil_op` varchar(32) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `bt` varchar(32) DEFAULT NULL,
  `remark` varchar(32) DEFAULT NULL,
  `no_bh` varchar(32) DEFAULT NULL,
  `fgssta` varchar(16) DEFAULT NULL,
  `konkon` varchar(16) DEFAULT NULL,
  `klssta` varchar(16) DEFAULT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`,`kode`,`active`),
  KEY `id_prov` (`id_prov`,`id_kabkot`),
  KEY `wil_op` (`wil_op`),
  KEY `kode` (`kode`),
  KEY `nama` (`nama`)
) ENGINE=InnoDB AUTO_INCREMENT=1708 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_stasiun_280922_ori`
--

DROP TABLE IF EXISTS `m_stasiun_280922_ori`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_stasiun_280922_ori` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_prov` varchar(255) DEFAULT NULL,
  `id_kabkot` varchar(255) NOT NULL,
  `kode` varchar(255) NOT NULL,
  `lat` varchar(32) DEFAULT NULL,
  `lon` varchar(32) DEFAULT NULL,
  `wil_op` varchar(32) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `bt` varchar(32) DEFAULT NULL,
  `remark` varchar(32) DEFAULT NULL,
  `no_bh` varchar(32) DEFAULT NULL,
  `fgssta` varchar(16) DEFAULT NULL,
  `konkon` varchar(16) DEFAULT NULL,
  `klssta` varchar(16) DEFAULT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama` (`nama`),
  KEY `uniqid` (`uniqid`,`kode`,`active`),
  KEY `id_prov` (`id_prov`,`id_kabkot`),
  KEY `wil_op` (`wil_op`),
  KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=1611 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_stasiun_bu300922`
--

DROP TABLE IF EXISTS `m_stasiun_bu300922`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_stasiun_bu300922` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_prov` varchar(255) DEFAULT NULL,
  `id_kabkot` varchar(255) NOT NULL,
  `kode` varchar(255) NOT NULL,
  `lat` varchar(32) DEFAULT NULL,
  `lon` varchar(32) DEFAULT NULL,
  `wil_op` varchar(32) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `bt` varchar(32) DEFAULT NULL,
  `remark` varchar(32) DEFAULT NULL,
  `no_bh` varchar(32) DEFAULT NULL,
  `fgssta` varchar(16) DEFAULT NULL,
  `konkon` varchar(16) DEFAULT NULL,
  `klssta` varchar(16) DEFAULT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`,`kode`,`active`),
  KEY `id_prov` (`id_prov`,`id_kabkot`),
  KEY `wil_op` (`wil_op`),
  KEY `kode` (`kode`),
  KEY `nama` (`nama`)
) ENGINE=InnoDB AUTO_INCREMENT=1662 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_stasiun_old`
--

DROP TABLE IF EXISTS `m_stasiun_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_stasiun_old` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_prov` varchar(255) DEFAULT NULL,
  `id_kabkot` varchar(255) NOT NULL,
  `kode` varchar(255) NOT NULL,
  `lat` varchar(32) DEFAULT NULL,
  `lon` varchar(32) DEFAULT NULL,
  `wil_op` varchar(32) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `bt` varchar(32) DEFAULT NULL,
  `remark` varchar(32) DEFAULT NULL,
  `no_bh` varchar(32) DEFAULT NULL,
  `fgssta` varchar(16) DEFAULT NULL,
  `konkon` varchar(16) DEFAULT NULL,
  `klssta` varchar(16) DEFAULT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama` (`nama`),
  KEY `uniqid` (`uniqid`,`kode`,`active`),
  KEY `id_prov` (`id_prov`,`id_kabkot`),
  KEY `wil_op` (`wil_op`),
  KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=1612 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_stasiun_old2`
--

DROP TABLE IF EXISTS `m_stasiun_old2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_stasiun_old2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_prov` varchar(255) DEFAULT NULL,
  `id_kabkot` varchar(255) NOT NULL,
  `kode` varchar(255) NOT NULL,
  `lat` varchar(32) DEFAULT NULL,
  `lon` varchar(32) DEFAULT NULL,
  `wil_op` varchar(32) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `bt` varchar(32) DEFAULT NULL,
  `remark` varchar(32) DEFAULT NULL,
  `no_bh` varchar(32) DEFAULT NULL,
  `fgssta` varchar(16) DEFAULT NULL,
  `konkon` varchar(16) DEFAULT NULL,
  `klssta` varchar(16) DEFAULT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama` (`nama`),
  KEY `uniqid` (`uniqid`,`kode`,`active`),
  KEY `id_prov` (`id_prov`,`id_kabkot`),
  KEY `wil_op` (`wil_op`),
  KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=1611 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_wilayah_kerja`
--

DROP TABLE IF EXISTS `m_wilayah_kerja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_wilayah_kerja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `kode` varchar(255) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`,`kode`,`active`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_wilayah_operasi`
--

DROP TABLE IF EXISTS `m_wilayah_operasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `m_wilayah_operasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `kode` varchar(255) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`,`kode`,`active`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `market_logistic`
--

DROP TABLE IF EXISTS `market_logistic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `market_logistic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `publish_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `lead` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `thumbnail` varchar(64) NOT NULL,
  `picture` varchar(64) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `counter` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(10) NOT NULL DEFAULT 'publish' COMMENT 'publish, draft',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `market_outlook`
--

DROP TABLE IF EXISTS `market_outlook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `market_outlook` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `publish_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `lead` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `thumbnail` varchar(64) NOT NULL,
  `picture` varchar(64) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `counter` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(10) NOT NULL DEFAULT 'publish' COMMENT 'publish, draft',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` bigint(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `publish_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `lead` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `active` int(11) NOT NULL,
  `thumbnail` varchar(64) NOT NULL,
  `picture` varchar(64) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `counter` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(10) NOT NULL DEFAULT 'publish' COMMENT 'publish, draft',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `newsletter`
--

DROP TABLE IF EXISTS `newsletter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newsletter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `newsletter` text DEFAULT NULL,
  `status` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `publish_date` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `newsletter_member`
--

DROP TABLE IF EXISTS `newsletter_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newsletter_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `company` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nutrient_efficiency`
--

DROP TABLE IF EXISTS `nutrient_efficiency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nutrient_efficiency` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `publish_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `lead` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `thumbnail` varchar(64) NOT NULL,
  `picture` varchar(64) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `counter` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(10) NOT NULL DEFAULT 'publish' COMMENT 'publish, draft',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `page`
--

DROP TABLE IF EXISTS `page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `codepage` varchar(8) NOT NULL,
  `title` varchar(255) NOT NULL,
  `highlight` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `picture` varchar(64) NOT NULL,
  `judul1` varchar(255) DEFAULT NULL,
  `judul2` varchar(255) DEFAULT NULL,
  `excel1` text DEFAULT NULL,
  `excel2` text DEFAULT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `page_stat`
--

DROP TABLE IF EXISTS `page_stat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_stat` (
  `codepage` varchar(255) NOT NULL,
  `browser` varchar(255) NOT NULL,
  `os` varchar(255) NOT NULL,
  `jenis` varchar(255) NOT NULL,
  `devicename` varchar(255) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `negara` varchar(32) DEFAULT NULL,
  `lokasi` varchar(128) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  KEY `codepage` (`codepage`),
  KEY `tanggal` (`tanggal`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `partner`
--

DROP TABLE IF EXISTS `partner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `partner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `picture` varchar(64) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `priority` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `petakjalan`
--

DROP TABLE IF EXISTS `petakjalan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `petakjalan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `id_wilker` varchar(64) NOT NULL,
  `id_sta1` varchar(64) NOT NULL,
  `id_sta2` varchar(64) NOT NULL,
  `latitude_awal` decimal(10,8) NOT NULL,
  `longitude_awal` decimal(11,8) NOT NULL,
  `latitude_akhir` decimal(10,8) NOT NULL,
  `longitude_akhir` decimal(11,8) NOT NULL,
  `jarak` decimal(8,2) NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_by` varchar(64) NOT NULL,
  `updated_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniqid` (`uniqid`),
  KEY `idx_petakjalan_uniqid` (`uniqid`),
  KEY `idx_petakjalan_status` (`status`),
  KEY `idx_petakjalan_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `petakjalan_status`
--

DROP TABLE IF EXISTS `petakjalan_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `petakjalan_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `id_petakjalan` varchar(64) NOT NULL,
  `userid` varchar(64) NOT NULL,
  `progress` tinyint(4) NOT NULL,
  `status` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_petakjalan` (`id_petakjalan`),
  KEY `uniqid` (`uniqid`),
  KEY `idx_petakjalan_status_progress` (`progress`),
  KEY `idx_petakjalan_status_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `preferences`
--

DROP TABLE IF EXISTS `preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `meta_desc` varchar(255) NOT NULL,
  `meta_keyword` varchar(255) NOT NULL,
  `konten_head` text NOT NULL,
  `konten_body_t` text NOT NULL,
  `konten_body_b` text NOT NULL,
  `footer` text NOT NULL,
  `twitter` text NOT NULL,
  `linkedin` text NOT NULL,
  `facebook` text NOT NULL,
  `instagram` text NOT NULL,
  `youtube` text DEFAULT NULL,
  `contact` text DEFAULT NULL,
  `maps` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `priority`
--

DROP TABLE IF EXISTS `priority`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `priority` (
  `approvalid` int(11) NOT NULL AUTO_INCREMENT,
  `modulid` int(11) NOT NULL,
  `adminid` int(11) NOT NULL,
  `status` varchar(10) NOT NULL,
  `priority` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `created_by` varchar(50) NOT NULL,
  PRIMARY KEY (`approvalid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `production_technology`
--

DROP TABLE IF EXISTS `production_technology`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `production_technology` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `publish_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `lead` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `thumbnail` varchar(64) NOT NULL,
  `picture` varchar(64) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `counter` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(10) NOT NULL DEFAULT 'publish' COMMENT 'publish, draft',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `report`
--

DROP TABLE IF EXISTS `report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `publish_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `lead` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `thumbnail` varchar(64) NOT NULL,
  `picture` varchar(64) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `counter` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(10) NOT NULL DEFAULT 'publish' COMMENT 'publish, draft',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `privilege` text NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`,`nama`,`active`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `safety_enviroment`
--

DROP TABLE IF EXISTS `safety_enviroment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `safety_enviroment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `publish_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `lead` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `thumbnail` varchar(64) NOT NULL,
  `picture` varchar(64) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `counter` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(10) NOT NULL DEFAULT 'publish' COMMENT 'publish, draft',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sop`
--

DROP TABLE IF EXISTS `sop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_sop` varchar(255) NOT NULL,
  `counter` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(10) NOT NULL DEFAULT 'publish' COMMENT 'publish, draft',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sponsorship`
--

DROP TABLE IF EXISTS `sponsorship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sponsorship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `url` text NOT NULL,
  `picture` varchar(64) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `priority` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stasiun`
--

DROP TABLE IF EXISTS `stasiun`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stasiun` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `idencrypt` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idprovinsi` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idkota` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idwilop` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idwilker` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provinsi` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kota` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `wilker` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `wilop` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `updateby` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kelas` int(11) NOT NULL,
  `is_operation` tinyint(4) NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=773 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `supply_report`
--

DROP TABLE IF EXISTS `supply_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supply_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `publish_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `lead` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `thumbnail` varchar(64) NOT NULL,
  `picture` varchar(64) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `counter` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(10) NOT NULL DEFAULT 'publish' COMMENT 'publish, draft',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sustainability_fertilizer`
--

DROP TABLE IF EXISTS `sustainability_fertilizer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sustainability_fertilizer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `publish_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `lead` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `thumbnail` varchar(64) NOT NULL,
  `picture` varchar(64) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `counter` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  `created_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(64) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(10) NOT NULL DEFAULT 'publish' COMMENT 'publish, draft',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tempfile`
--

DROP TABLE IF EXISTS `tempfile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tempfile` (
  `id` bigint(20) NOT NULL,
  `typedoc` tinyint(4) NOT NULL,
  `typefile` varchar(64) NOT NULL,
  `realname` varchar(64) NOT NULL,
  `randomname` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_access_levels`
--

DROP TABLE IF EXISTS `user_access_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_access_levels` (
  `id` char(36) NOT NULL,
  `user_portal_id` varchar(64) NOT NULL,
  `privilege` text DEFAULT NULL,
  `wilker` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `level` tinyint(4) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(128) NOT NULL DEFAULT '',
  `updated_by` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_auth`
--

DROP TABLE IF EXISTS `user_auth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_auth` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uniqid` varchar(64) NOT NULL,
  `userid` varchar(64) NOT NULL,
  `expired_time` datetime NOT NULL,
  `logout_time` datetime DEFAULT NULL,
  `active` tinyint(4) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(128) NOT NULL,
  `updated_by` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'jembatan'
--

--
-- Dumping routines for database 'jembatan'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-28 15:29:19
