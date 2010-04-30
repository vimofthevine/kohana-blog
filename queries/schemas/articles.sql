CREATE TABLE IF NOT EXISTS `articles` (
	`id` int(11) NOT NULL auto_increment,
	`title` varchar(128) NOT NULL,
	`slug` varchar(128) NOT NULL,
	`text` text NOT NULL,
	`date` int(10) NOT NULL,
	`state` varchar(16) NOT NULL,
	`author_id` int(11) NOT NULL,
	`category_id` int(11) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

