-- Adminer 4.8.1 MySQL 5.5.5-10.4.10-MariaDB dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `coin`;
CREATE TABLE `coin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `api_name` varchar(100) NOT NULL,
  `symbol` varchar(10) NOT NULL,
  `image` varchar(255) NOT NULL,
  `ignore` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `coin_history`;
CREATE TABLE `coin_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coin_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `price` double NOT NULL,
  `mcap` double NOT NULL,
  `volume` double NOT NULL,
  `dominance` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `coin_id` (`coin_id`),
  CONSTRAINT `coin_history_ibfk_1` FOREIGN KEY (`coin_id`) REFERENCES `coin` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `coin_import`;
CREATE TABLE `coin_import` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coin_id` int(11) NOT NULL,
  `last_complete` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `coin_id` (`coin_id`),
  CONSTRAINT `coin_import_ibfk_1` FOREIGN KEY (`coin_id`) REFERENCES `coin` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 2022-01-02 18:06:30