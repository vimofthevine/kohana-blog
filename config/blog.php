<?php defined('SYSPATH') OR die('No direct script access.');

return array(
	'per_page' => 10,
	'comment_report' => array(
		'email_to'         => 'recipient',
		'email_from'       => 'sender',
		'email_subject'    => 'Comment Report',
		'email_type'       => 'text/html',
	),
);
