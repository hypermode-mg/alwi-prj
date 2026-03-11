-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Окт 24 2023 г., 15:26
-- Версия сервера: 10.2.40-MariaDB
-- Версия PHP: 8.0.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
--
--

-- --------------------------------------------------------
-- --------------------------------------------------------

--
-- Структура таблицы `png_groupinuser`
--

CREATE TABLE IF NOT EXISTS `png_groupinuser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `png_groups`
--

CREATE TABLE IF NOT EXISTS `png_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `addr` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `png_hostingroup`
--

CREATE TABLE IF NOT EXISTS `png_hostingroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `png_hosts`
--

CREATE TABLE IF NOT EXISTS `png_hosts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) DEFAULT NULL,
  `active` int(2) DEFAULT 0,
  `type` varchar(100) DEFAULT NULL,
  `devname` varchar(200) DEFAULT NULL,
  `location` varchar(700) DEFAULT NULL,
  `vendor` varchar(200) DEFAULT NULL,
  `model` varchar(200) DEFAULT NULL,
  `os` varchar(100) DEFAULT NULL,
  `comment` varchar(100) DEFAULT NULL,
  `linktoadm` varchar(700) DEFAULT NULL,
  `curstate` int(11) DEFAULT NULL,
  `statedate` int(11) DEFAULT NULL,
  `numtries` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `png_listdevicetypes`
--

CREATE TABLE IF NOT EXISTS `png_listdevicetypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `png_listlocations`
--

CREATE TABLE IF NOT EXISTS `png_listlocations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(700) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `png_listmodels`
--

CREATE TABLE IF NOT EXISTS `png_listmodels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `png_listos`
--

CREATE TABLE IF NOT EXISTS `png_listos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `png_listvendors`
--

CREATE TABLE IF NOT EXISTS `png_listvendors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `png_logtable`
--

CREATE TABLE IF NOT EXISTS `png_logtable` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datestart` int(11) DEFAULT NULL,
  `dateend` int(11) DEFAULT NULL,
  `message` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `png_messages`
--

CREATE TABLE IF NOT EXISTS `png_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` int(11) DEFAULT NULL,
  `host` varchar(20) DEFAULT NULL,
  `alarm` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `png_modules_param`
--

CREATE TABLE IF NOT EXISTS `png_modules_param` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(100) NOT NULL,
  `param` varchar(100) NOT NULL,
  `val` varchar(256) NOT NULL,
  `switch` tinyint(4) NOT NULL DEFAULT 0,
  `comment` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- --------------------------------------------------------

--
-- Структура таблицы `png_mod_mailman_users`
--

CREATE TABLE IF NOT EXISTS `png_mod_mailman_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventdate` int(11) NOT NULL,
  `message` varchar(500) NOT NULL,
  `useremail` varchar(200) NOT NULL,
  `sentstate` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `png_mod_trends`
--

CREATE TABLE IF NOT EXISTS `png_mod_trends` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `date` int(11) NOT NULL,
  `value` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `png_mod_user_trends`
--

CREATE TABLE IF NOT EXISTS `png_mod_user_trends` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `value` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------



--
-- Структура таблицы `png_nmapcmd`
--

CREATE TABLE IF NOT EXISTS `png_nmapcmd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` int(11) DEFAULT NULL,
  `scanarray` varchar(300) DEFAULT NULL,
  `is_executed` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `png_nmapstat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` int(11) DEFAULT NULL,
  `host` varchar(50) NOT NULL,
  `hostname` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `png_pingstat`
--

CREATE TABLE IF NOT EXISTS `png_pingstat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` int(11) DEFAULT NULL,
  `host` varchar(20) DEFAULT NULL,
  `result` int(3) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------


--
-- Структура таблицы `png_users`
--

CREATE TABLE IF NOT EXISTS `png_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(100) DEFAULT NULL,
  `pass` varchar(100) DEFAULT NULL,
  `active` int(11) NOT NULL DEFAULT 0,
  `email` varchar(200) DEFAULT NULL,
  `hash` varchar(100) DEFAULT NULL,
  `comment` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `png_servicestat` (
  `id` int(11) NOT NULL DEFAULT 0,
  `date` int(11) DEFAULT NULL,
  `host` varchar(20) DEFAULT NULL,
  `port` int(11) DEFAULT NULL,
  `result` int(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `png_hosts_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host` varchar(20) DEFAULT NULL,
  `port` int(11) DEFAULT NULL,
  `curstate` int(11) DEFAULT 0,
  `statedate` int(11) DEFAULT 0,
  `numtries` int(11) DEFAULT 0,
  `comment` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `png_users`
--

CREATE TABLE IF NOT EXISTS `png_hosts_sensors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host` varchar(20) DEFAULT NULL,
  `sensor_type` varchar(100) DEFAULT NULL,
  `sensor_cmddata` varchar(200) DEFAULT NULL,
  `sensor_pollperiod` int(11) DEFAULT NULL,
  `sensor_polltime` varchar(5) DEFAULT NULL,
  `curvalue` int(11) DEFAULT 0,
  `prevvalue` int(11) DEFAULT NULL,
  `lastpolldate` int(11) DEFAULT 0,
  `numtries` int(11) DEFAULT 0,
  `comment` varchar(100) DEFAULT NULL,
  `sensor_version` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `png_users` (`id`, `login`, `pass`, `active`, `email`, `hash`, `comment`) VALUES
(1, 'admin', 'c3284d0f94606de1fd2af172aba15bf3', 1, NULL, NULL, NULL);
COMMIT;





/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

