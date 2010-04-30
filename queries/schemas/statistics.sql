CREATE TABLE IF NOT EXISTS `statistics` (
	`id` int(11) NOT NULL auto_increment,
	`article_id` int(11) NOT NULL,
	`total` int(11) NOT NULL,
	`views` int(11) NOT NULL,
	`data` varchar(256) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

