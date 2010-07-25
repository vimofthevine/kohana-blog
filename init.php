<?php defined('SYSPATH') OR die('No direct script access.');

Route::set('blog', 'blog(/page<page>)', array('page' => '\d+'))
	->defaults(array(
		'controller' => 'blog',
		'action'     => 'published',
	));

Route::set('blog/filter', 'blog/<action>/<name>(/page<page>)', array(
		'action'  => 'category|tag',
		'name'    => '[A-Za-z0-9_-]+',
		'page'    => '\d+',
	))->defaults(array(
		'controller' => 'blog',
	));

Route::set('blog/permalink', 'blog/article/<date>/<slug>(/comments/<page>)', array(
		'date'    => '\d{4}\/\d{1,2}\/\d{1,2}',
		'slug'    => '[A-Za-z0-9_-]+',
		'page'    => '\d+',
	))->defaults(array(
		'controller' => 'blog',
		'action'     => 'article',
	));

Route::set('blog/archive', 'blog/archive/<date>(/page<page>)', array(
		'date'    => '\d{4}(\/\d{1,2}|)',
		'page'    => '\d+',
	))->defaults(array(
		'controller' => 'blog',
		'action'     => 'archive',
	));

Route::set('blog/stats', 'blog/stats/<action>(/<id>)')
	->defaults(array(
		'directory'  => 'blog',
		'controller' => 'stats',
	));

Route::set('admin/blog/diff', 'admin/blog/diff/<id>/<ver1>/<ver2>')
	->defaults(array(
		'directory'  => 'admin/blog',
		'controller' => 'article',
		'action'     => 'diff',
	));

Route::set('admin/blog', 'admin/blog/<controller>(/<action>(/<id>))(/<type>)(/page/<page>)', array(
		'id'         => '\d+',
		'type'       => 'draft|published|archived',
		'page'       => '\d+',
	))->defaults(array(
		'directory'  => 'admin/blog',
		'action'     => 'list',
	));

