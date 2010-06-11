<?php defined('SYSPATH') OR die('No direct script access.');

Route::set('blog', 'blog(/page/<page>)', array('page' => '\d+'))
	->defaults(array(
		'controller' => 'blog',
		'action'     => 'published',
	));

Route::set('blog/filter', 'blog/<action>/<name>(/page/<page>)', array(
		'action'  => 'category|tag',
		'name'    => '[A-Za-z0-9_-]+',
		'page'    => '\d+',
	))->defaults(array(
		'controller' => 'blog',
	));

Route::set('blog/permalink', 'blog/article/<year>/<month>/<day>/<slug>(/comments/<page>)', array(
		'year'    => '\d+',
		'month'   => '\d+',
		'day'     => '\d+',
		'slug'    => '[A-Za-z0-9_-]+',
		'page'    => '\d+',
	))->defaults(array(
		'controller' => 'blog',
		'action'     => 'article',
	));

Route::set('blog/archive', 'blog/archive/<year>(/<month>)(/page/<page>)', array(
		'year'    => '\d+',
		'month'   => '\d+',
		'page'    => '\d+',
	))->defaults(array(
		'controller' => 'blog',
		'action'     => 'archive',
		'month'      => 0,
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

