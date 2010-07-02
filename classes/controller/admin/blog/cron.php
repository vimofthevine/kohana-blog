<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Blog cron functions controller
 *
 * @package     Blog
 * @category    Controller
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Controller_Admin_Blog_Cron extends Controller {

	/**
	 * Checks to make sure request environment is cli
	 */
	public function before() {
		if (Request::$protocol != 'cli')
		{
			throw new Kohana_Request_Exception('Attempt to access cron controller outside of command line environment',NULL,404);
		}
	}

	/**
	 * Reset daily statistics
	 */
	public function action_stats_reset() {
		$search   = Sprig::factory('blog_search');
		$articles = $search->search_by_state('published');

		foreach ($articles as $article)
		{
			$article->statistic->load()->reset()->update();
		}
	}

}	// End of Controller_Admin_Blog_Cron
