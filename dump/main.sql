

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
  `source` text NOT NULL,
  `sortdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `selector` (`id_parent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `art_similar`
--

CREATE TABLE IF NOT EXISTS `art_similar` (
  `id_art` int(10) unsigned NOT NULL,
  `id_similar` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_art`,`id_similar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
-- используется(#1356 - View 'api.art_tag_count' references invalid table(s) or column(s) or function(s) or definer/invoker of view lack rights to use them)

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `comment`
--

CREATE TABLE IF NOT EXISTS `comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rootparent` int(10) unsigned NOT NULL,
  `parent` int(10) unsigned NOT NULL,
  `id_item` int(10) unsigned NOT NULL,
  `area` tinyint(3) unsigned NOT NULL COMMENT '1 - art, 2 - post',
  `username` varchar(255) CHARACTER SET utf8 NOT NULL,
  `email` varchar(255) CHARACTER SET utf8 NOT NULL,
  `ip` bigint(20) NOT NULL,
  `cookie` char(32) CHARACTER SET utf8 NOT NULL,
  `text` text CHARACTER SET utf8 NOT NULL,
  `editdate` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `sortdate` timestamp NULL DEFAULT '0000-00-00 00:00:00',
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
-- Структура таблицы `meta`
--

CREATE TABLE IF NOT EXISTS `meta` (
  `item_type` tinyint(3) unsigned NOT NULL COMMENT '1 - art, 2 - post, 3 - art_pack, 4 - art_group, 5 - art_manga, 6 - art_artist',
  `id_item` int(10) unsigned NOT NULL,
  `meta_type` tinyint(3) unsigned NOT NULL COMMENT '1 - art_tag, 2 - state, 3 - art_pack, 4 - art_group, 5 - art_manga, 6 - art_artist, 7 - art_rating, 		8 - date, 9 - comment_count, 10 - comment_date, 11 - tag_count, 12 - user',
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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `user`
--

INSERT INTO `user` (`id`, `login`, `pass`, `email`, `cookie`, `rights`) VALUES
(1, 'Анонимус', '', 'default@avatar.mail', '', 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;