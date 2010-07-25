<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Blog statistics controller
 *
 * @package     Blog
 * @category    Controller
 * @author      Kyle Treubig
 * @copyright   (C) 2010 Kyle Treubig
 * @license     MIT
 */
class Controller_Blog_Stats extends Controller_Template_Website {

	/**
	 * Show recent articles
	 */
	public function action_recent_articles() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Blog_Stats::action_recent_articles');
		$this->template->content = View::factory('blog/stats/articles')
			->set('legend', __('Recent Articles'))
			->bind('articles', $articles);

		$limit    = $this->request->param('id', NULL);
		$search   = Sprig::factory('blog_search');
		$articles = $search->get_recent_articles($limit);
	}

	/**
	 * Show popular articles for the current week
	 */
	public function action_popular_articles() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Blog_Stats::action_popular_articles');
		$this->template->content = View::factory('blog/stats/articles')
			->set('legend', __('Popular This Week'))
			->bind('articles', $articles);

		$limit    = $this->request->param('id');
		$search   = Sprig::factory('blog_search');
		$articles = $search->get_popular_articles($limit);
	}

	/**
	 * Show recent comments
	 */
	public function action_recent_comments() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Blog_Stats::action_recent_comments');
		$this->template->content = View::factory('blog/stats/comments')
			->set('legend', __('Recent Comments'))
			->bind('comments', $comments);

		$limit    = $this->request->param('id');
		$search   = Sprig::factory('blog_search');
		$comments = $search->get_recent_comments($limit);
	}

}	// End of Controller_Blog_Stats

