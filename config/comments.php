<?php defined('SYSPATH') OR die('No direct script access.');

return array(
	'blog' => array(
		'model'       => 'blog_comment',
		'per_page'    => 10,
		'view'        => 'blog/front/comments',
		'lower_limit' => 0.2,
		'upper_limit' => 0.9,
		'order'       => 'DESC',
	),
	'blog-admin' => array(
		'model'       => 'blog_comment',
		'per_page'    => 10,
		'view'        => 'blog/admin/comments',
		'lower_limit' => 0.2,
		'upper_limit' => 0.9,
		'order'       => 'DESC',
	),
);

