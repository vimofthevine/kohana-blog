<?php defined('SYSPATH') OR die('No direct script access.');

Route::set('admin_blog', 'admin/blog/<controller>(/<action>(/<id>))')
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'blog',
		'action'     => 'list',
	));

