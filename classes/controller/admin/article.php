<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Blog article management controller
 *
 * @package     Blog
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Controller_Admin_Article extends Controller_Template_Admin {

	/**
	 * Register controller as an admin controller
	 */
	public function before() {
		parent::before();

		$this->restrict('article', 'manage');
		unset($this->template->menu->menu['Blog'][0]);
	}

	/**
	 * Default action to list
	 */
	public function action_index() {
		$this->action_list();
	}

	/**
	 * Generate menu for blog management
	 */
	private function menu() {
		// Get count of draft articles
		$drafts = Sprig::factory('article', array('state'=>'draft'))->load(NULL,FALSE)->count();
		$drafts = ($drafts > 0) ? ' ['.$drafts.']' : '';

		// Get count of published articles
		$pub = Sprig::factory('article', array('state'=>'published'))->load(NULL,FALSE)->count();
		$pub = ($pub > 0) ? ' ['.$pub.']' : '';

		// Get count of archived articles
		$arch = Sprig::factory('article', array('state'=>'archived'))->load(NULL,FALSE)->count();
		$arch = ($arch > 0) ? ' ['.$arch.']' : '';

		return View::factory('blog/admin/menu')
			->set('links', array(
				'Drafts'.$drafts => Request::instance()->uri(array('action'=>'list', 'type'=>'draft')),
				'Published'.$pub => Request::instance()->uri(array('action'=>'list', 'type'=>'published')),
				'Archived'.$arch => Request::instance()->uri(array('action'=>'list', 'type'=>'archived')),
				'Create Article' => Request::instance()->uri(array('action'=>'new', 'page'=>NULL)),
			));
	}

	/**
	 * Display list of articles
	 */
	public function action_list() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Article::action_list');

		// Get type
		$type = Request::instance()->param('type', 'all');

		// Get total number of articles
		if ($type == 'all')
		{
			$total = Sprig::factory('article')->load(NULL, FALSE)->count();
		}
		else
		{
			$total = Sprig::factory('article', array('state'=>$type))->load(NULL, FALSE)->count();
		}

		// Check if there are any articles to display
		if ($total == 0)
		{
			$hmvc = View::factory('blog/admin/hmvc/article_none');

			$view = View::factory('blog/admin/list')
				->set('menu', $this->menu())
				->set('list', $hmvc);

			$this->template->content = $this->internal_request ? $hmvc : $view;
			return;
		}

		// Determine pagination offset
		$page = Request::instance()->param('page', 1);
		$per_page = Kohana::config('blog.per_page');
		$offset   = ($page - 1) * $per_page;

		// Create query
		$query = DB::select()->offset($offset);

		if ($type == 'all')
		{
			$articles = Sprig::factory('article')->load($query, $per_page);
		}
		else
		{
			$articles = Sprig::factory('article', array('state'=>$type))->load($query, $per_page);
		}

		// If no articles found, return to main page (bad offset/page)
		if (count($articles) == 0)
		{
			Kohana::$log->add(Kohana::INFO, 'No articles found for admin page '.$page);
			Request::instance()->redirect( Request::instance()->uri(array('page'=>1)) );
		}

		$pagination = Pagination::factory(array(
			'current_page'   => array('source'=>'route', 'key'=>'page'),
			'total_items'    => $total,
			'items_per_page' => $per_page,
		));

		// Create article list
		$grid = new Grid;
		$grid->column()->field('id')->title('ID');
		$grid->column()->field('title')->title('Title');
		$grid->column()->field('state')->title('State');
		$grid->column('action')->title('Actions')->text('Edit')->class('edit')
			->action(Request::instance()->uri(array('action' => 'edit')));
		$grid->column('action')->title('')->text('History')->class('history')
			->action(Request::instance()->uri(array('action' => 'history')));
		$grid->data($articles);

		// Setup HMVC view with data
		$hmvc = View::factory('blog/admin/hmvc/article_list')
			->set('legend', ucfirst($type).' Articles')
			->set('pagination', $pagination)
			->set('grid', $grid);

		// Setup template view
		$view = View::factory('blog/admin/list')
			->set('menu', $this->menu())
			->set('list', $hmvc);

		// Set request response
		$this->template->content = $this->internal_request ? $hmvc : $view;
	}

	/**
	 * Create a new article
	 */
	public function action_new() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Article::action_new');

		// Restrict access
		if ( ! $this->a2->allowed('article', 'create'))
		{
			$message = __('You do not have permission to create new articles.');

			// Return message if an ajax request
			if (Request::$is_ajax)
			{
				$this->template->content = $message;
			}
			// Else set flash message and redirect
			else
			{
				Message::instance()->error($message);
				Request::instance()->redirect( Request::instance()->uri(array('action' => '')) );
			}
		}

		$article = Sprig::factory('article')->values($_POST);
		$article->author = $this->a1->get_user();

		try
		{
			$article->create();
			$article->load();
			$statistic = Sprig::factory('statistic', array('article'=>$article))->create();

			$message = __('The article, :title, has been created.', array(':title'=>$article->title));

			// Return message if an ajax request
			if (Request::$is_ajax)
			{
				$this->template->content = $message;
			}
			// Else set flash message and redirect
			else
			{
				Message::instance()->info($message);
				Request::instance()->redirect( Request::instance()->uri(array('action' => '')) );
			}
		}
		catch (Validate_Exception $e)
		{
			// Setup HMVC view with data
			$hmvc = View::factory('blog/admin/hmvc/article_form')
				->set('legend', __('Create Article'))
				->set('submit', __('Create'))
				->set('slug_editable', FALSE)
				->set('comment_needed', FALSE)
				->set('article', $article)
				->set('errors', count($_POST) ? $e->array->errors('blog') : array() );

			// Setup template view
			$view = View::factory('blog/admin/article_form')
				->set('menu', $this->menu())
				->set('form', $hmvc);

			// Set template scripts and styles
			$this->template->scripts[] = 'http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.js';
			$this->template->scripts[] = Route::get('media')->uri(array('file'=>'js/markitup/jquery.markitup.js'));
			$this->template->scripts[] = Route::get('media')->uri(array('file'=>'js/markitup/sets/html/set.js'));
			$this->template->styles[Route::get('media')->uri(array('file'=>'js/markitup/skins/markitup/style.css'))] = 'screen';
			$this->template->styles[Route::get('media')->uri(array('file'=>'js/markitup/sets/html/style.css'))] = 'screen';

			// Set request response
			$this->template->content = $this->internal_request ? $hmvc : $view;
		}
	}

	/**
	 * Edit article details
	 */
	public function action_edit() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Article::action_edit');

		$id = Request::instance()->param('id');
		$article = Sprig::factory('article', array('id' => $id))->load();

		// If article is invalid, return to list
		if ( ! $article->loaded())
		{
			$message = __('That article does not exist.');

			// Return message if an ajax request
			if (Request::$is_ajax)
			{
				$this->template->content = $message;
			}
			// Else set flash message and redirect
			else
			{
				Message::instance()->error($message);
				Request::instance()->redirect( Request::instance()->uri(array('action'=>'', 'id'=>NULL)) );
			}
		}

		// Restrict access
		if ( ! $this->a2->allowed($article, 'edit'))
		{
			$message = __('You do not have permission to modify article, :title.', array(':title'=>$article->title));

			// Return message if an ajax request
			if (Request::$is_ajax)
			{
				$this->template->content = $message;
			}
			// Else set flash message and redirect
			else
			{
				Message::instance()->error($message);
				Request::instance()->redirect( Request::instance()->uri(array('action'=>'', 'id'=>NULL)) );
			}
		}

		$article->values($_POST);

		// Setup HMVC view with data
		$hmvc = View::factory('blog/admin/hmvc/article_form')
			->set('legend', __('Modify Article'))
			->set('submit', __('Save'))
			->set('slug_editable', ($article->state == 'draft'))
			->set('comment_needed', TRUE)
			->set('article', $article);

		if (count($_POST))
		{
			try
			{
				$article->update();
				$message = __('The article, :title, has been modified.', array(':title'=>$article->title));

				// Return message if an ajax request
				if (Request::$is_ajax)
				{
					$this->template->content = $message;
				}
				// Else set flash message and redirect
				else
				{
					Message::instance()->info($message);
					Request::instance()->redirect( Request::instance()->uri(array('action'=>'', 'id'=>NULL)) );
				}
			}
			catch (Validate_Exception $e)
			{
				$hmvc->errors = count($_POST) ? $e->array->errors('admin') : array();
			}
		}

		// Setup template view
		$view = View::factory('blog/admin/article_form')
			->set('menu', $this->menu())
			->set('form', $hmvc);

		// Set template scripts and styles
		$this->template->scripts[] = 'http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.js';
		$this->template->scripts[] = Route::get('media')->uri(array('file'=>'js/markitup/jquery.markitup.js'));
		$this->template->scripts[] = Route::get('media')->uri(array('file'=>'js/markitup/sets/html/set.js'));
		$this->template->styles[Route::get('media')->uri(array('file'=>'js/markitup/skins/markitup/style.css'))] = 'screen';
		$this->template->styles[Route::get('media')->uri(array('file'=>'js/markitup/sets/html/style.css'))] = 'screen';

		// Set request response
		$this->template->content = $this->internal_request ? $hmvc : $view;
	}

	/**
	 * List revision history for an article
	 */
	public function action_history() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Article::action_history');

		$id = Request::instance()->param('id');
		$article = Sprig::factory('article', array('id'=>$id))->load();

		// If article is invalid, return to list
		if ( ! $article->loaded())
		{
			$message = __('That article does not exist.');

			// Return message if an ajax request
			if (Request::$is_ajax)
			{
				$this->template->content = $message;
			}
			// Else set flash message and redirect
			else
			{
				Message::instance()->error($message);
				Request::instance()->redirect( Request::instance()->uri(array('action'=>'', 'id'=>NULL)) );
			}
		}

		// Restrict access
		if ( ! $this->a2->allowed($article, 'edit'))
		{
			$message = __('You do not have permission to view revision history for :title.', array(':title'=>$article->title));

			// Return message if an ajax request
			if (Request::$is_ajax)
			{
				$this->template->content = $message;
			}
			// Else set flash message and redirect
			else
			{
				Message::instance()->error($message);
				Request::instance()->redirect( Request::instance()->uri(array('action'=>'', 'id'=>NULL)) );
			}
		}

		$ver1 = isset($_POST['ver1']) ? $_POST['ver1'] : NULL;
		$ver2 = isset($_POST['ver2']) ? $_POST['ver2'] : NULL;

		if ($ver1 !== NULL AND $ver2 !== NULL)
		{
			Request::instance()->redirect( Route::get('admin_blog_diff')->uri(array(
				'id'         => $id,
				'ver1'       => $_POST['ver1'],
				'ver2'       => $_POST['ver2'],
			)) );
		}

		// Create revision list
		$grid = new Grid;
		$grid->column('radio')->field('version')->title('Version 1')->name('ver1');
		$grid->column('radio')->field('version')->title('Version 2')->name('ver2');
		$grid->column()->field('version')->title('Revision');
		$grid->column()->field('editor')->title('Editor')->callback(array($this, 'print_username'));
		$grid->column('date')->field('date')->title('Date');
		$grid->column()->field('comments')->title('Comments')->callback(array($this, 'parse_comments'));
		$grid->link('submit')->text('View Diff')
			->action(Route::get('admin_blog')->uri(array('controller'=>'article', 'action'=>'diff')) );
		$grid->data($article->revisions);

		// Setup HMVC view with data
		$hmvc = View::factory('blog/admin/hmvc/article_history')
			->set('article', $article)
			->set('grid', $grid);

		// Setup template view
		$view = View::factory('blog/admin/history')
			->set('menu', $this->menu())
			->set('history', $hmvc);

		// Set request response
		$this->template->content = $this->internal_request ? $hmvc : $view;
	}

	/**
	 * Parse comment array as unordered list
	 *
	 * @param   array   comments
	 * @return  string
	 */
	public function parse_comments($comments) {
		$return = '<ul>';
		foreach ($comments as $comment)
		{
			$return .= '<li>'.$comment.'</li>';
		}
		$return .= '</ul>'.PHP_EOL;
		return $return;
	}

	/**
	 * Print username callback
	 *
	 * @param   object  user
	 * @return  string
	 */
	public function print_username($user) {
		if ( ! $user->loaded())
		{
			$user->load();
		}
		return $user->username;
	}

	/**
	 * Show inline difference between two versions
	 */
	public function action_diff() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Article::action_diff');

		$id   = Request::instance()->param('id');
		$ver1 = Request::instance()->param('ver1');
		$ver2 = Request::instance()->param('ver2');
		$article = Sprig::factory('article', array('id'=>$id))->load();

		// If article is invalid, return to list
		if ( ! $article->loaded())
		{
			$message = __('That article does not exist.');

			// Return message if an ajax request
			if (Request::$is_ajax)
			{
				$this->template->content = $message;
			}
			// Else set flash message and redirect
			else
			{
				Message::instance()->error($message);
				Request::instance()->redirect( Request::instance()->uri(array('action'=>'', 'id'=>NULL)) );
			}
		}

		// Restrict access
		if ( ! $this->a2->allowed($article, 'edit'))
		{
			$message = __('You do not have permission to view revision history for :title.', array(':title'=>$article->title));

			// Return message if an ajax request
			if (Request::$is_ajax)
			{
				$this->template->content = $message;
			}
			// Else set flash message and redirect
			else
			{
				Message::instance()->error($message);
				Request::instance()->redirect( Request::instance()->uri(array('action'=>'', 'id'=>NULL)) );
			}
		}

		// Get versions of the text
		$article->version($ver2);
		$new_text = $article->text;
		$article->version($ver1);
		$old_text = $article->text;

		$diff = Versioned::inline_diff($old_text, $new_text);

		// Setup HMVC view with data
		$hmvc = View::factory('blog/admin/hmvc/article_diff')
			->set('article', $article)
			->set('ver1', $ver1)
			->set('ver2', $ver2)
			->set('diff', $diff);

		// Setup template view
		$view = View::factory('blog/admin/diff')
			->set('menu', $this->menu())
			->set('diff', $hmvc);

		// Set request response
		$this->template->content = $this->internal_request ? $hmvc : $view;
	}

	/**
	 * Delete an article
	 */
	public function action_delete() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Article::action_delete');

		// If deletion is not desired, redirect to list
		if (isset($_POST['no']))
		{
			Request::instance()->redirect( Request::instance()->uri(array('action'=>'', 'id'=>NULL)) );
		}

		$id = Request::instance()->param('id');
		$article = Sprig::factory('article', array('id' => $id))->load();
		$title = $article->title;

		// If article is invalid, return to list
		if ( ! $article->loaded())
		{
			$message = __('That article does not exist.');

			// Return message if an ajax request
			if (Request::$is_ajax)
			{
				$this->template->content = $message;
			}
			// Else set flash message and redirect
			else
			{
				Message::instance()->error($message);
				Request::instance()->redirect( Request::instance()->uri(array('action'=>'', 'id'=>NULL)) );
			}
		}

		// Restrict access
		if ( ! $this->a2->allowed($article, 'delete'))
		{
			$message = __('You do not have permission to delete article, :title.', array(':title'=>$title));

			// Return message if an ajax request
			if (Request::$is_ajax)
			{
				$this->template->content = $message;
			}
			// Else set flash message and redirect
			else
			{
				Message::instance()->error($message);
				Request::instance()->redirect( Request::instance()->uri(array('action'=>'', 'id'=>NULL)) );
			}
		}

		// If deletion is confirmed
		if (isset($_POST['yes']))
		{
			try
			{
				$article->delete();
				$message = __('The article, :title, has been deleted.', array(':title'=>$title));

				// Return message if an ajax request
				if (Request::$is_ajax)
				{
					$this->template->content = $message;
				}
				// Else set flash message and redirect
				else
				{
					Message::instance()->info($message);
					Request::instance()->redirect( Request::instance()->uri(array('action'=>'', 'id'=>NULL)) );
				}
			}
			catch (Exception $e)
			{
				Kohana::$log->add(Kohana::ERROR, 'Error occured deleting article, id='.$article->id.', '.$e->getMessage());
				$message = __('An error occured deleting article, :title.', array(':title'=>$title));

				// Return message if an ajax request
				if (Request::$is_ajax)
				{
					$this->template->content = $message;
				}
				// Else set flash message and redirect
				else
				{
					Message::instance()->error($message);
					Request::instance()->redirect( Request::instance()->uri(array('action'=>'', 'id'=>NULL)) );
				}
			}
		}

		// Setup HMVC view with data
		$hmvc = View::factory('blog/admin/hmvc/article_delete')
			->set('article', $article);

		// Setup template view
		$view = View::factory('blog/admin/delete')
			->set('menu', $this->menu())
			->set('confirm', $hmvc);

		// Set request response
		$this->template->content = $this->internal_request ? $hmvc : $view;
	}

}	// End of Controller_Admin_Article

