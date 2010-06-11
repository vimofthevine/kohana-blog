<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Blog article management controller
 *
 * @package     Blog
 * @category    Controller
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Controller_Admin_Blog_Article extends Controller_Admin {

	protected $_resource = 'article';

	protected $_resource_required = array('edit', 'history', 'diff');

	protected $_acl_map = array(
		'new'     => 'create',
		'edit'    => 'edit',
		'history' => 'edit',
		'diff'    => 'edit',
		'delete'  => 'delete',
		'default' => 'manage',
	);

	protected $_acl_required = 'all';

	protected $_view_map = array(
		'history' => 'admin/layout/full_width',
		'default' => 'admin/layout/wide_column_with_menu',
	);

	protected $_current_nav = 'admin/blog';

	/**
	 * Generate menu for blog management
	 */
	protected function _menu() {
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
	 * Load the specified article
	 */
	protected function _load_resource() {
		$id = $this->request->param('id', 0);
		$this->_resource = Sprig::factory('article', array('id'=>$id))->load();
		if ( ! $this->_resource->loaded())
			throw new Kohana_Exception('That article does not exist.', NULL, 404);
	}

	/**
	 * Redirect index action to list
	 */
	public function action_index() {
		$this->request->redirect( $this->request->uri(
			array('action' => 'list')), 301);
	}

	/**
	 * Display list of articles
	 */
	public function action_list() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Admin_Article::action_list');
		$this->template->content = View::factory('blog/admin/article_list')
			->bind('request', $this->request)
			->bind('legend', $legend)
			->bind('pagination', $pagination)
			->bind('articles', $articles);

		$type = Request::instance()->param('type', 'all');
		$search = Sprig::factory('blog_search');
		$articles = $search->search_by_state($type);
		$legend = __(':state Articles', array(':state' => ucfirst($type)));
	}

	/**
	 * Create a new article
	 */
	public function action_new() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Admin_Article::action_new');
		$this->template->content = View::factory('blog/admin/article_form')
			->set('legend', __('Create Article'))
			->set('submit', __('Save'))
			->set('slug_editable', FALSE)
			->set('comment_needed', FALSE)
			->bind('article', $article)
			->bind('errors', $errors);

		$article = Sprig::factory('article')->values($_POST);
		$article->author = $this->a1->get_user();

		if ($_POST)
		{
			try
			{
				$article->create();
				//$article->load();
				$statistic = Sprig::factory('statistic', array('article'=>$article))->create();

				Message::instance()->info('The article, :title, has been created.',
					array(':title' => $article->title));

				if ( ! $this->_internal)
					$this->request->redirect( $this->request->uri(array('action'=>'list')) );
			}
			catch (Validate_Exception $e)
			{
				$errors = $e->array->errors('admin');
			}
		}

		// Set template scripts and styles
		$this->template->scripts[] = 'http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.js';
		$this->template->scripts[] = Route::get('media')->uri(array('file'=>'js/markitup/jquery.markitup.js'));
		$this->template->scripts[] = Route::get('media')->uri(array('file'=>'js/markitup/sets/html/set.js'));
		$this->template->styles[Route::get('media')->uri(array('file'=>'js/markitup/skins/markitup/style.css'))] = 'screen';
		$this->template->styles[Route::get('media')->uri(array('file'=>'js/markitup/sets/html/style.css'))] = 'screen';
	}

	/**
	 * Edit article details
	 */
	public function action_edit() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Admin_Article::action_edit');
		$this->template->content = View::factory('blog/admin/article_form')
			->set('legend', __('Modify Article'))
			->set('submit', __('Save'))
			->set('slug_editable', FALSE)
			->bind('slug_editable', $slug_editable)
			->set('comment_needed', TRUE)
			->bind('article', $this->_resource)
			->bind('errors', $errors);

		// Bind locally
		$article = & $this->_resource;
		$slug_editable = ($article->state == 'draft');

		if ($_POST)
		{
			if ( ! empty($_POST['comment']))
			{
				$user = $this->a1->get_user();
				$_POST['comment'] = '['.$user->username.'] '.$_POST['comment'];
			}

			$article->values($_POST);

			try
			{
				$article->update();

				Message::instance()->info('The article, :title, has been modified.',
					array(':title' => $article->title));

				if ( ! $this->_internal)
					$this->request->redirect( $this->request->uri(array('action'=>'list', 'id'=>NULL)) );
			}
			catch (Validate_Exception $e)
			{
				$errors = $e->array->errors('admin');
			}
		}

		// Set template scripts and styles
		$this->template->scripts[] = 'http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.js';
		$this->template->scripts[] = Route::get('media')->uri(array('file'=>'js/markitup/jquery.markitup.js'));
		$this->template->scripts[] = Route::get('media')->uri(array('file'=>'js/markitup/sets/html/set.js'));
		$this->template->styles[Route::get('media')->uri(array('file'=>'js/markitup/skins/markitup/style.css'))] = 'screen';
		$this->template->styles[Route::get('media')->uri(array('file'=>'js/markitup/sets/html/style.css'))] = 'screen';
	}

	/**
	 * List revision history for an article
	 */
	public function action_history() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Admin_Article::action_history');

		if ( ! empty($_POST['ver1']) AND ! empty($_POST['ver2']))
		{
			$this->request->redirect( Route::get('admin/blog/diff')->uri(array(
				'id'         => $this->_resource->id,
				'ver1'       => $_POST['ver1'],
				'ver2'       => $_POST['ver2'],
			)) );
		}

		$this->template->content = View::factory('blog/admin/article_history')
			->bind('request', $this->request)
			->bind('article', $this->_resource)
			->bind('revisions', $revisions);
		$revisions = $this->_resource->revisions;
	}

	/**
	 * Show inline difference between two versions
	 */
	public function action_diff() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Admin_Article::action_diff');
		$this->template->content = View::factory('blog/admin/article_diff')
			->bind('article', $this->_resource)
			->bind('ver1', $ver1)
			->bind('ver2', $ver2)
			->bind('diff', $diff);

		// Bind locally
		$article = & $this->_resource;
		$ver1 = $this->request->param('ver1');
		$ver2 = $this->request->param('ver2');

		// Get versions of the text
		$article->version($ver2);
		$new_text = $article->text;
		$article->version($ver1);
		$old_text = $article->text;

		$diff = Versioned::inline_diff($old_text, $new_text);
	}

	/**
	 * Delete an article
	 */
	public function action_delete() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Admin_Article::action_delete');

		// If deletion is not desired, redirect to list
		if (isset($_POST['no']))
			$this->request->redirect( $this->request->uri(array('action'=>'list', 'id'=>NULL)) );

		$this->template->content = View::factory('blog/admin/article_delete')
			->bind('article', $this->_resource);

		// Bind locally
		$article = & $this->_resource;
		$title = $article->title;

		// If deletion is confirmed
		if (isset($_POST['yes']))
		{
			try
			{
				$article->delete();
				Message::instance()->info('The article, :title, has been deleted.',
					array(':title' => $title));

				if ( ! $this->_internal)
					$this->request->redirect( $this->request->uri(array('action'=>'list', 'id'=>NULL)) );
			}
			catch (Exception $e)
			{
				Kohana::$log->add(Kohana::ERROR, 'Error occured deleting article, id='.$article->id.', '.$e->getMessage());
				Message::instance()->error('An error occured deleting article, :title.',
					array(':title' => $title));

				if ( ! $this->_internal)
					$this->request->redirect( $this->request->uri(array('action'=>'list', 'id'=>NULL)) );
			}
		}
	}

}	// End of Controller_Admin_Article

