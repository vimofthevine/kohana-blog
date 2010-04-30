<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Blog post management controller
 *
 * @package     Blog
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Controller_Admin_Post extends Controller_Template_Admin {

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
		return View::factory('blog/admin/menu')
			->set('links', array(
				'Create Post' => Request::instance()->uri(array('action' => 'new')),
			));
	}

	/**
	 * Display list of articles
	 */
	public function action_list() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Post::action_list');

		$articles = Sprig::factory('article')->load(NULL, FALSE);

		// Check if there are any articles to display
		if (count($articles) == 0)
		{
			$hmvc = View::factory('blog/admin/hmvc/article_none');

			$view = View::factory('blog/admin/list')
				->set('menu', $this->menu())
				->set('list', $hmvc);

			$this->template->content = $this->internal_request ? $hmvc : $view;
			return;
		}

		// Create article list
		$grid = new Grid;
		$grid->column()->field('id')->title('ID');
		$grid->column()->field('title')->title('Title');
		$grid->column('action')->title('Actions')->text('Edit')->class('edit')
			->action(Request::instance()->uri(array('action' => 'edit')));
		$grid->column('action')->title('')->text('Delete')->class('delete')
			->action(Request::instance()->uri(array('action' => 'delete')));
		$grid->data($articles);

		// Setup HMVC view with data
		$hmvc = View::factory('blog/admin/hmvc/article_list')
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
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Post::action_new');

		// Restrict access
		if ( ! $this->a2->allowed('article', 'create'))
		{
			$message = __('You do not have permission to create new posts.');

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
			$message = __('The post, :title, has been created.', array(':title'=>$article->title));

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
				->set('legend', __('Create Post'))
				->set('submit', __('Create'))
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
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Post::action_edit');

		$id = Request::instance()->param('id');
		$article = Sprig::factory('article', array('id' => $id))->load();

		// If article is invalid, return to list
		if ( ! $article->loaded())
		{
			$message = __('That post does not exist.');

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
			$message = __('You do not have permission to modify post, :title.', array(':title'=>$article->title));

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
			->set('legend', __('Modify Post'))
			->set('submit', __('Save'))
			->set('article', $article);

		if (count($_POST))
		{
			try
			{
				$article->update();
				$message = __('The post, :title, has been modified.', array(':title'=>$article->title));

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
	 * Delete an article
	 */
	public function action_delete() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Post::action_delete');

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
			$message = __('That post does not exist.');

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
			$message = __('You do not have permission to delete post, :title.', array(':title'=>$title));

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
				$message = __('The post, :title, has been deleted.', array(':title'=>$title));

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
				$message = __('An error occured deleting post, :title.', array(':title'=>$title));

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

}	// End of Controller_Admin_Post

