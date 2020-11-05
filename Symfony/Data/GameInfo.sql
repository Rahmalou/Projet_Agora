-- MySQL dump 10.13  Distrib 5.7.22, for Linux (x86_64)
--
-- Host: localhost    Database: symfony
-- ------------------------------------------------------
-- Server version	5.7.22-0ubuntu0.16.04.1

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
DROP TABLE IF EXISTS `game_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `game_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `minJoueurs` int(11) NOT NULL,
  `maxJoueurs` int(11) NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imageURL` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reglesURL` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nom` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `gameCode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_209C5D7B6C6E55B5` (`nom`),
  UNIQUE KEY `UNIQ_209C5D7B81169599` (`gameCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `game_info` (`id`, `description`, `imageURL`, `reglesURL`, `nom`, `minJoueurs`, `maxJoueurs`, `gameCode`) VALUES (1,'Ave cesar, un jeu franchement incroyable ! Avec des chars ! Excitant n\'est-ce pas ?','ave-caesar.jpg','http://jeuxstrategie.free.fr/Ave_cesar_complet.php','Ave Cesar',3,6,'avc'),
(2,'Le 6 qui prend est un jeu de cartes passionnant qui creuse les méninges !','6QP.png','https://www.gigamic.com/files/catalog/products/rules/rules-6quiprend-05-2012.pdf','6 qui prend',2,4,'sqp'),
(3, 'Gagnez de l\'influence en plaçant vos légions pour devenir Consul à Rome.'           , 'augustus.jpg'  ,'http://regle.jeuxsoc.fr/augus_rg.pdf'                                            , 'Augustus'   , 2, 6, 'aug'),
(4, 'Dans Splendor, vous êtes à la tête d une guilde de marchands. Vous avez pour objectif de vous enrichir et de devenir le commerçant le plus prestigieux du royaume.', 'Splendor.jpg', 'https://www.spacecowboys.fr/img/games/splendor/details/rules/Rules_Splendor_FR.pdf', 'Splendor', 2, 4, 'spldr');


-- Dump completed on 2018-05-31 13:32:52
