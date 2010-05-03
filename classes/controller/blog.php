<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Blog front controller
 *
 * @package     Blog
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Controller_Blog extends Controller_Template_Website {

	public function action_published() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Blog::action_published');

		// Get request parameters
		$request = Request::instance();
		$page = $request->param('page', 1);

		// Get total number of published articles
		$total = Sprig::factory('article', array('state' => 'published'))
			->load(NULL, FALSE)->count();

		if ($total == 0)
		{
			Kohana::$log->add(Kohana::INFO, 'No published articles found');
			$this->template->content = View::factory('blog/front/none');
			return;
		}

		// Determine pagination offset
		$per_page = Kohana::config('blog.per_page');
		$offset   = ($page - 1) * $per_page;

		// Create query
		$query = DB::select()->offset($offset);
		$articles = Sprig::factory('article', array('state' => 'published'))
			->load($query, $per_page);

		// If no articles found, return to main page (bad offset/page)
		if (count($articles) == 0)
		{
			Kohana::$log->add(Kohana::INFO, 'No published articles found for page '.$page);
			Request::instance()->redirect( Request::instance()->uri(array('page'=>1)) );
		}

		$pagination = Pagination::factory(array(
			'current_page'   => array('source'=>'route', 'key'=>'page'),
			'total_items'    => $total,
			'items_per_page' => $per_page,
		));

		$this->template->content = View::factory('blog/front/list')
			->set('legend', __('Published Articles'))
			->set('articles', $articles)
			->set('pagination', $pagination);
	}

	public function action_category() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Blog::action_category');

		// Get request parameters
		$request = Request::instance();
		$name = $request->param('name');
		$page = $request->param('page', 1);

		// Get category
		$category = Sprig::factory('category', array('name'=>$name))->load();

		if ( ! $category->loaded())
		{
			Kohana::$log->add(Kohana::ERROR, 'Attempt to access non-existent category name='.$id);
			Request::instance()->redirect( Route::get('blog_main')->uri() );
		}

		// Get total number of articles under category
		$total = $category->articles->count();

		if ($total == 0)
		{
			Kohana::$log->add(Kohana::INFO, 'No published articles found for category '.$category->name);
			$this->template->content = View::factory('blog/front/none');
			return;
		}

		// Determine pagination offset
		$per_page = Kohana::config('blog.per_page');
		$offset   = ($page - 1) * $per_page;

		// Create query
		$query = DB::select()->offset($offset);
		$articles = $category->published($query, $per_page);

		// If no articles found, return to main page (bad offset/page)
		if ($articles->count() == 0)
		{
			Kohana::$log->add(Kohana::INFO, 'No published articles found for category '.$category->name.', page '.$page);
			Request::instance()->redirect( Request::instance()->uri(array('page'=>1)) );
		}

		$pagination = Pagination::factory(array(
			'current_page'   => array('source'=>'route', 'key'=>'page'),
			'total_items'    => $total,
			'items_per_page' => $per_page,
		));

		$this->template->content = View::factory('blog/front/list')
			->set('legend', __(ucfirst($category->name).' Articles'))
			->set('articles', $articles)
			->set('pagination', $pagination);
	}

	public function action_tag() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Blog::action_tag');

		// Get request parameters
		$request = Request::instance();
		$name = $request->param('name');
		$page = $request->param('page', 1);

		// Get tag
		$tag = Sprig::factory('tag', array('name'=>$name))->load();

		if ( ! $tag->loaded())
		{
			Kohana::$log->add(Kohana::ERROR, 'Attempt to access non-existent tag name='.$id);
			Request::instance()->redirect( Route::get('blog_main')->uri() );
		}

		// Get total number of articles under tag
		$total = $tag->articles->count();

		if ($total == 0)
		{
			Kohana::$log->add(Kohana::INFO, 'No published articles found for category '.$category->name);
			$this->template->content = View::factory('blog/front/none');
			return;
		}

		// Determine pagination offset
		$per_page = Kohana::config('blog.per_page');
		$offset   = ($page - 1) * $per_page;

		// Create query
		$query = DB::select()->offset($offset);
		$articles = $tag->published($query, $per_page);

		// If no articles found, return to main page (bad offset/page)
		if (count($articles) == 0)
		{
			Kohana::$log->add(Kohana::INFO, 'No published articles found for tag '.$tag->name.', page '.$page);
			Request::instance()->redirect( Request::instance()->uri(array('page'=>1)) );
		}

		$pagination = Pagination::factory(array(
			'current_page'   => array('source'=>'route', 'key'=>'page'),
			'total_items'    => $total,
			'items_per_page' => $per_page,
		));

		$this->template->content = View::factory('blog/front/list')
			->set('legend', 'Articles Tagged with '.ucfirst($tag->name))
			->set('articles', $articles)
			->set('pagination', $pagination);
	}

	public function action_article() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Blog::action_article');

		// Get request parameters
		$request = Request::instance();
		$year  = $request->param('year');
		$month = $request->param('month');
		$day   = $request->param('day');
		$slug = $request->param('slug');
		$page = $request->param('page', 1);

		$article = Sprig::factory('article', array('slug' => $slug))->load();

		if ( ! $article->loaded())
		{
			Kohana::$log->add(Kohana::ERROR, 'Error loading article by slug, '.$slug);

			// Search for article by date
			$begin = strtotime($year.'-'.$month.'-'.$day);
			Kohana::$log->add(Kohana::DEBUG, 'Search from begin date, '.$begin);
			$end   = strtotime('+1 day', $begin);
			Kohana::$log->add(Kohana::DEBUG, 'Search from end date, '.$end);

			$query = DB::select()
				->where('date', '>=', $begin)
				->where('date', '<', $end);
			$article = Sprig::factory('article')->load($query, 1);

			if ( ! $article->loaded())
			{
				Kohana::$log->add(Kohana::ERROR, 'Error loading article by date, '.$year.'/'.$month.'/'.$day);
				// TODO display a message
				Request::instance()->redirect( Route::get('blog_main')->uri() );
			}
		}

		$this->template->content = View::factory('blog/front/article')
			->set('article', $article);
	}

	public function action_archive() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Blog::action_archive');

		// Get request parameters
		$request = Request::instance();
		$year  = $request->param('year');
		$month = $request->param('month');
		$page  = $request->param('page', 1);

		// Determine start/stop dates
		if ($month == 0)
		{
			$begin = strtotime($year.'-01-01');
			$end   = strtotime('+1 year', $begin);
			$date  = $year;
		}
		else
		{
			$begin = strtotime($year.'-'.$month.'-01');
			$end   = strtotime('+1 month', $begin);
			$date  = $year.'/'.$month;
		}

		// Create query
		$query = DB::select()
			->where('date', '>=', $begin)
			->where('date', '<', $end);

		// Get total number of published articles
		$total = Sprig::factory('article', array('state' => 'published'))
			->load($query, FALSE)->count();

		if ($total == 0)
		{
			Kohana::$log->add(Kohana::INFO, 'No published articles found for date, '.$date);
			$this->template->content = View::factory('blog/front/none');
			return;
		}

		// Determine pagination offset
		$per_page = Kohana::config('blog.per_page');
		$offset   = ($page - 1) * $per_page;
		$query->offset($offset);

		$articles = Sprig::factory('article', array('state' => 'published'))
			->load($query, $per_page);

		// If no articles found, return to main page (bad offset/page)
		if (count($articles) == 0)
		{
			Kohana::$log->add(Kohana::INFO, 'No published articles found for date '.$date.', page '.$page);
			Request::instance()->redirect( Request::instance()->uri(array('page'=>1)) );
		}

		$pagination = Pagination::factory(array(
			'current_page'   => array('source'=>'route', 'key'=>'page'),
			'total_items'    => $total,
			'items_per_page' => $per_page,
		));

		$this->template->content = View::factory('blog/front/list')
			->set('legend', __('Published Articles'))
			->set('articles', $articles)
			->set('pagination', $pagination);
	}

}

