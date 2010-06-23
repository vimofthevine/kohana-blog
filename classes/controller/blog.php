<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Blog front controller
 *
 * @package     Blog
 * @category    Controller
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Controller_Blog extends Controller_Template_Website {

	public function action_published() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Blog::action_published');
		$this->template->content = View::factory('blog/front/list')
			->set('legend', __('Published Articles'))
			->bind('articles', $articles)
			->bind('pagination', $pagination);

		$search     = Sprig::factory('blog_search');
		$articles   = $search->search_by_state('published');
		$pagination = $search->pagination;
	}

	public function action_category() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Blog::action_category');
		$this->template->content = View::factory('blog/front/list')
			->bind('legend', $legend)
			->bind('articles', $articles)
			->bind('pagination', $pagination);

		$category   = $this->request->param('name');
		$search     = Sprig::factory('blog_search');
		$articles   = $search->search_by_category($category);
		$pagination = $search->pagination;
		$legend     = __(':name Articles', array(':name'=>ucfirst($category)));
	}

	public function action_tag() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Blog::action_tag');
		$this->template->content = View::factory('blog/front/list')
			->bind('legend', $legend)
			->bind('articles', $articles)
			->bind('pagination', $pagination);

		$tag        = $this->request->param('name');
		$search     = Sprig::factory('blog_search');
		$articles   = $search->search_by_tag($tag);
		$pagination = $search->pagination;
		$legend     = __('Articles Tagged with :name',
			array(':name' => ucfirst($tag)));
	}

	public function action_article() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Blog::action_article');
		$this->template->content = View::factory('blog/front/article')
			->bind('article', $article)
			->bind('comment_form', $form)
			->bind('comment_list', $list);

		$slug    = $this->request->param('slug');
		$date    = $this->request->param('date');
		$search  = Sprig::factory('blog_search');
		$article = $search->load_article($slug, $date);

		if ($article !== FALSE AND $article->loaded())
		{
			$article->statistic->load()->record()->update();

			// Handle comment posting
			$form = Request::factory('comments/blog/create/'.$article->id)->execute()->response;
			if ($form === TRUE)
			{
				$form = __('Thank you for posting!');
			}

			// Handle comment listing
			$list = Request::factory('comments/blog/public/'.$article->id)->execute()->response;
		}
		else
		{
			throw new Kohana_Request_Exception('Article not found', NULL, 404);
		}
	}

	public function action_archive() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Blog::action_archive');
		Kohana::$log->add(Kohana::DEBUG,
			'Route is '.Route::name($this->request->route));
		$this->template->content = View::factory('blog/front/list')
			->set('legend', __('Published Articles'))
			->bind('articles', $articles)
			->bind('pagination', $pagination);

		$date       = $this->request->param('date');
		$search     = Sprig::factory('blog_search');
		$articles   = $search->search_by_date($date);
		$pagination = $search->pagination;
	}

}	// End of Controller_Blog

