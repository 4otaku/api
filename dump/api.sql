

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `api`
--

-- --------------------------------------------------------

--
-- Структура таблицы `art`
--

CREATE TABLE IF NOT EXISTS `art` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_parent` int(10) unsigned DEFAULT NULL,
  `id_parent_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `id_user` int(10) unsigned NOT NULL,
  `md5` char(32) NOT NULL,
  `ext` varchar(4) NOT NULL,
  `width` mediumint(8) unsigned NOT NULL,
  `height` mediumint(8) unsigned NOT NULL,
  `weight` int(10) unsigned NOT NULL,
  `resized` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `animated` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `vector` text,
  `similar_tested` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `source` text NULL DEFAULT NULL,
  `comment` text NULL DEFAULT NULL,
  `sortdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `selector` (`id_parent`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
ALTER TABLE  `art` ADD UNIQUE  `md5` (  `md5` );

-- --------------------------------------------------------

--
-- Структура таблицы `art_artist`
--

CREATE TABLE IF NOT EXISTS `art_artist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(10) unsigned NOT NULL,
  `text` text NOT NULL,
  `sortdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `art_group`
--

CREATE TABLE IF NOT EXISTS `art_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `text` text NOT NULL,
  `sortdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



CREATE TABLE IF NOT EXISTS `art_group_item` (
  `id_group` int(10) unsigned NOT NULL,
  `id_art` int(10) unsigned NOT NULL,
  `sortdate` timestamp NOT NULL,
  PRIMARY KEY (`id_group`,`id_art`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `art_manga`
--

CREATE TABLE IF NOT EXISTS `art_manga` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `filename` text NOT NULL,
  `weight` int(10) unsigned NOT NULL DEFAULT '0',
  `title` text NOT NULL,
  `text` text NOT NULL,
  `sortdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `art_manga_item`
--

CREATE TABLE IF NOT EXISTS `art_manga_item` (
  `id_manga` int(10) unsigned NOT NULL,
  `id_art` int(10) unsigned NOT NULL,
  `order` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id_manga`,`order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `art_pack`
--

CREATE TABLE IF NOT EXISTS `art_pack` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `filename` text NOT NULL,
  `weight` int(10) unsigned NOT NULL DEFAULT '0',
  `cover` int(10) unsigned DEFAULT NULL,
  `title` text NOT NULL,
  `text` text NOT NULL,
  `sortdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `art_pack_item`
--

CREATE TABLE IF NOT EXISTS `art_pack_item` (
  `id_pack` int(10) unsigned NOT NULL,
  `id_art` int(10) unsigned NOT NULL,
  `filename` text NOT NULL,
  `order` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id_pack`,`order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `art_rating`
--

CREATE TABLE IF NOT EXISTS `art_rating` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_art` int(10) unsigned NOT NULL,
  `cookie` char(32) NOT NULL,
  `ip` bigint(20) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cookie` (`id_art`,`cookie`),
  UNIQUE KEY `unique_ip` (`id_art`,`ip`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------


--
-- Структура таблицы `art_tag`
--

CREATE TABLE IF NOT EXISTS `art_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `color` char(6) DEFAULT NULL,
  `have_description` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `art_tag_count`
--

CREATE TABLE IF NOT EXISTS `art_tag_count` (
  `id_tag` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `count` int(10) unsigned NOT NULL DEFAULT '0',
  `original` tinyint(3) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `art_tag_variant`
--

CREATE TABLE IF NOT EXISTS `art_tag_variant` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_tag` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `art_translation`
--

CREATE TABLE IF NOT EXISTS `art_translation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_translation` smallint(5) unsigned NOT NULL,
  `id_art` int(10) unsigned NOT NULL,
  `id_user` int(10) unsigned NOT NULL,
  `x1` mediumint(8) unsigned DEFAULT NULL,
  `x2` mediumint(8) unsigned DEFAULT NULL,
  `y1` mediumint(8) unsigned DEFAULT NULL,
  `y2` mediumint(8) unsigned DEFAULT NULL,
  `text` text,
  `sortdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `state` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1 - active, 2 - old, 3 - deleted',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_translation` (`id_translation`,`id_art`,`sortdate`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Структура таблицы `art_upload`
--

CREATE TABLE IF NOT EXISTS `art_upload` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `md5` char(32) NOT NULL,
  `ext` varchar(4) NOT NULL,
  `name` text NOT NULL,
  `resized` tinyint(3) unsigned NOT NULL,
  `animated` tinyint(3) unsigned NOT NULL,
  `width` mediumint(8) unsigned NOT NULL,
  `height` mediumint(8) unsigned NOT NULL,
  `weight` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `comment`
--

CREATE TABLE IF NOT EXISTS `comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rootparent` int(10) unsigned NOT NULL DEFAULT '0',
  `parent` int(10) unsigned NOT NULL DEFAULT '0',
  `id_item` int(10) unsigned NOT NULL,
  `area` tinyint(3) unsigned NOT NULL COMMENT '1 - art, 2 - post',
  `username` varchar(255) CHARACTER SET utf8 NOT NULL,
  `email` varchar(255) CHARACTER SET utf8 NOT NULL,
  `ip` bigint(20) NOT NULL,
  `cookie` char(32) CHARACTER SET utf8 NOT NULL,
  `text` text CHARACTER SET utf8 NOT NULL,
  `editdate` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `sortdate` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Триггеры `comment`
--
DROP TRIGGER IF EXISTS `comment_sortdate`;
DELIMITER //
CREATE TRIGGER `comment_sortdate` BEFORE INSERT ON `comment`
 FOR EACH ROW BEGIN
        IF NEW.sortdate is NULL THEN
           SET NEW.sortdate = CURRENT_TIMESTAMP ;
        END IF;
    END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблицы `cron`
--

CREATE TABLE IF NOT EXISTS `cron` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class` varchar(64) CHARACTER SET utf8 NOT NULL,
  `function` varchar(64) CHARACTER SET utf8 NOT NULL,
  `period` varchar(10) CHARACTER SET utf8 NOT NULL,
  `last_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `last_time` (`last_time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

--
-- Дамп данных таблицы `cron`
--

INSERT INTO `cron` (`id`, `class`, `function`, `period`, `last_time`) VALUES
(1, 'Tag', 'do_count', '1d', '2012-09-16 21:45:56'),
(2, 'Meta', 'comment_count', '1d', '2012-08-18 00:21:53'),
(3, 'Meta', 'comment_date', '1d', '2012-08-18 00:22:10'),
(4, 'Meta', 'translation_date', '1d', '2012-08-18 00:22:10'),
(5, 'Meta', 'translator', '1d', '2012-08-18 00:22:10'),
(6, 'Pool', 'delete_empty', '1h', '2012-08-17 01:22:54'),
(7, 'Pool', 'create_pack_archive', '5m', '2012-08-17 00:48:48'),
(8, 'Pool', 'create_manga_archive', '5m', '2012-08-17 00:48:48');

-- --------------------------------------------------------

--
-- Структура таблицы `cron_log`
--

CREATE TABLE IF NOT EXISTS `cron_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_task` int(10) unsigned NOT NULL,
  `exec_time` float unsigned NOT NULL,
  `exec_memory` bigint(20) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `head_menu`
--

CREATE TABLE IF NOT EXISTS `head_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `parent` int(10) unsigned NOT NULL,
  `order` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order` (`order`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `meta`
--

CREATE TABLE IF NOT EXISTS `meta` (
  `item_type` tinyint(3) unsigned NOT NULL COMMENT '1 - art, 2 - post, 3 - art_pack, 4 - art_group, 5 - art_manga, 6 - art_artist',
  `id_item` int(10) unsigned NOT NULL,
  `meta_type` tinyint(3) unsigned NOT NULL COMMENT '1 - art_tag, 2 - state, 3 - art_pack, 4 - art_group, 5 - art_manga, 6 - art_artist, 7 - art_rating, 8 - date, 9 - comment_count, 10 - comment_date, 11 - tag_count, 12 - translator, 13 - translation_date',
  `meta` int(11) NOT NULL,
  PRIMARY KEY (`item_type`,`id_item`,`meta_type`,`meta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `state`
--

CREATE TABLE IF NOT EXISTS `state` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Дамп данных таблицы `state`
--

INSERT INTO `state` (`id`, `name`) VALUES
(1, 'unapproved'),
(2, 'approved'),
(3, 'disapproved'),
(4, 'deleted'),
(5, 'untagged'),
(6, 'tagged');

-- --------------------------------------------------------

--
-- Структура таблицы `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(255) CHARACTER SET utf8 NOT NULL,
  `pass` char(32) CHARACTER SET utf8 NOT NULL,
  `email` varchar(255) CHARACTER SET utf8 NOT NULL,
  `cookie` char(32) CHARACTER SET utf8 NOT NULL,
  `rights` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`login`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
INSERT INTO  `user` (`id` ,`login` ,`pass` ,`email` ,`cookie` ,`rights`)
VALUES ('0',  'Анонимно',  '********************************',  'default@avatar.mail',  '********************************',  '0');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
