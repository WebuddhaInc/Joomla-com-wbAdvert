
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__wbadvert_advert` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `caption` varchar(128) NOT NULL DEFAULT '',
  `imptotal` int(11) NOT NULL DEFAULT '0',
  `impmade` int(11) NOT NULL DEFAULT '0',
  `clicks` int(11) NOT NULL DEFAULT '0',
  `file_type` varchar(4) DEFAULT NULL,
  `width` int(10) unsigned NOT NULL DEFAULT '0',
  `height` int(10) unsigned NOT NULL DEFAULT '0',
  `url` varchar(255) DEFAULT NULL,
  `target` enum('_blank','_self','_parent','_top') NOT NULL DEFAULT '_self',
  `code` text,
  `imageurl` varchar(255) DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_stop` datetime DEFAULT NULL,
  `weekdays` varchar(27) DEFAULT NULL,
  `time_start` int(10) unsigned DEFAULT NULL,
  `time_stop` int(10) unsigned DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `published` (`published`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__wbadvert_campaign` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `imptotal` int(11) NOT NULL DEFAULT '0',
  `impmade` int(11) NOT NULL DEFAULT '0',
  `clicks` int(11) NOT NULL DEFAULT '0',
  `date_start` datetime DEFAULT NULL,
  `date_stop` datetime DEFAULT NULL,
  `weekdays` varchar(27) DEFAULT NULL,
  `time_start` int(10) unsigned DEFAULT NULL,
  `time_stop` int(10) unsigned DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `published` (`published`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__wbadvert_client` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL DEFAULT '',
  `contact` varchar(60) NOT NULL DEFAULT '',
  `email` varchar(60) NOT NULL DEFAULT '',
  `extrainfo` text NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` time DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__wbadvert_group` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `module_id` int(10) unsigned NOT NULL DEFAULT '0',
  `count` int(10) unsigned NOT NULL DEFAULT '1',
  `order` enum('random','ordering','name') NOT NULL DEFAULT 'random',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` time DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__wbadvert_keyword` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `synonym_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `keyword` varchar(32) NOT NULL DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` time DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__wbadvert_idx_category` (
  `advert_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `campaign_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `category_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `idx_key` (`advert_id`,`campaign_id`,`category_id`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__wbadvert_idx_client` (
  `advert_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `campaign_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `client_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `idx_key` (`advert_id`,`campaign_id`,`client_id`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__wbadvert_idx_content` (
  `advert_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `campaign_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `content_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `idx_key` (`advert_id`,`campaign_id`,`content_id`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__wbadvert_idx_group` (
  `advert_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `campaign_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `group_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `ordering` bigint(20) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `idx_key` (`advert_id`,`campaign_id`,`group_id`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__wbadvert_idx_keyword` (
  `advert_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `campaign_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `keyword_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `idx_key` (`advert_id`,`campaign_id`,`keyword_id`)
) DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__wbadvert_idx_menu` (
  `advert_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `campaign_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `menu_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `idx_key` (`advert_id`,`campaign_id`,`menu_id`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
