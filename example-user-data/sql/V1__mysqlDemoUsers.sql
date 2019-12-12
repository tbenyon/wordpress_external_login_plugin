# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.7.28)
# Database: externalDb
# Generation Time: 2019-12-12 20:12:28 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table User
# ------------------------------------------------------------

DROP TABLE IF EXISTS `User`;

CREATE TABLE `User` (
  `UserID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(20) NOT NULL,
  `LastName` varchar(20) NOT NULL,
  `NickName` varchar(20) NOT NULL,
  `CreatedDate` date NOT NULL,
  `DOB` date DEFAULT NULL,
  `UserType` varchar(20) DEFAULT NULL,
  `LastUpdate` datetime DEFAULT NULL,
  `ContactNumber` varchar(20) DEFAULT NULL,
  `Hash` varchar(128) DEFAULT NULL,
  `cms_state` varchar(128) DEFAULT '0',
  `Email` varchar(128) DEFAULT NULL,
  `address` varchar(128) DEFAULT NULL,
  `city` varchar(128) DEFAULT NULL,
  `zip_code` varchar(128) DEFAULT NULL,
  `state` varchar(128) DEFAULT NULL,
  `phone` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`UserID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `User` WRITE;
/*!40000 ALTER TABLE `User` DISABLE KEYS */;

INSERT INTO `User` (`UserID`, `FirstName`, `LastName`, `NickName`, `CreatedDate`, `DOB`, `UserType`, `LastUpdate`, `ContactNumber`, `Hash`, `cms_state`, `Email`, `address`, `city`, `zip_code`, `state`, `phone`)
VALUES
	(2,'Thomas','Benyon','T-DAWG','2016-09-10','1987-04-23','Mentor','2018-03-24 11:27:36','07792736282','$1$ZfPE84Z7$n0xJl3t36AewrQyyU9gXa.','blocked','tom.benyon2@gmail.com',NULL,NULL,NULL,NULL,NULL),
	(1,'Will','Benyon','monkeyMan','2016-09-10','1982-10-20','Mentor','2016-09-10 10:16:52','07236388181',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);

/*!40000 ALTER TABLE `User` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
