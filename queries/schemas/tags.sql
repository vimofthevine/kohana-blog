CREATE TABLE IF NOT EXISTS `tags` (
	`id` int(11) NOT NULL auto_increment,
	`name` varchar(32) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `articles_tags` (
	`article_id` int(11) NOT NULL,
	`tag_id` int(11) NOT NULL,
	PRIMARY KEY (`article_id`, `tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

