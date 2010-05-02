<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Blog main management controller
 *
 * @package     Blog
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Controller_Admin_Blog extends Controller_Template_Admin {

	/**
	 * Register controller as an admin controller
	 */
	public function before() {
		parent::before();

		$this->restrict('article', 'manage');
		unset($this->template->menu->menu['Blog'][0]);
	}

	/**
	 * Blog management dashboard
	 */
	public function action_index() {
		$this->template->content = View::factory('blog/admin/dashboard')
			->set('menu', $this->menu())
			->set('stats', $this->statistics());
	}

	/**
	 * Generate menu for blog management
	 */
	private function menu() {
		return View::factory('blog/admin/menu')
			->set('links', array(
				'Create Post'     => Route::get('admin_blog')->uri(array('controller'=>'post', 'action'=>'new')),
				'Create Category' => Route::get('admin_blog')->uri(array('controller'=>'category', 'action'=>'new')),
				'Create Tag'      => Route::get('admin_blog')->uri(array('controller'=>'tag', 'action'=>'new')),
			));
	}

	/**
	 * Calculate blog statistics
	 */
	private function statistics() {
		$categories = Sprig::factory('category')->load(NULL, FALSE);
		$tags       = Sprig::factory('tag')->load(NULL, FALSE);
		$articles   = Sprig::factory('article')->load(NULL, FALSE);

		$stats = array();
		$stats['categories']['total'] = count($categories);
		$stats['tags']['total']       = count($tags);
		$stats['articles']['total']   = count($articles);

		return View::factory('blog/admin/hmvc/stats')
			->set('stats', $stats);
	}

}

