<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Blog comment management controller
 *
 * @package     Blog
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Controller_Admin_Blog_Comment extends Controller_Template_Admin {

	/**
	 * Register controller as admin controller
	 */
	public function before() {
		parent::before();

		$this->restrict('comment', 'manage');
		unset($this->template->menu->menu['Blog'][0]);
	}

	/**
	 * Generate menu for comment management
	 */
	private function menu() {
		// Get count of ham comments
		$ham = Sprig::factory('blog_comment', array('state'=>'ham'))->load(NULL, FALSE)->count();
		$ham = ($ham > 0) ? ' ['.$ham.']' : '';

		// Get count of queued comments
		$queued = Sprig::factory('blog_comment', array('state'=>'queued'))->load(NULL,FALSE)->count();
		$queued = ($queued > 0) ? ' ['.$queued.']' : '';

		// Get count of spam comments
		$spam = Sprig::factory('blog_comment', array('state'=>'spam'))->load(NULL,FALSE)->count();
		$spam = ($spam > 0) ? ' ['.$spam.']' : '';

		return View::factory('blog/admin/menu')
			->set('links', array(
				'Approved Comments'.$ham   => Request::instance()->uri(array('action' => 'approved')),
				'Moderation Queue'.$queued => Request::instance()->uri(array('action' => 'queue')),
				'Spam Comments'.$spam      => Request::instance()->uri(array('action' => 'spam')),
			));
	}

	/**
	 * Show report
	 */
	public function action_list() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Blog_Comment::action_list');

		// Get HMVC view for comment report
		$hmvc = Request::factory('comments/blog-admin/report/86400')->execute()->response;

		// Setup template view
		$view = View::factory('blog/admin/list')
			->set('menu', $this->menu())
			->set('list', $hmvc);

		// Set request response
		$this->template->content = $this->internal_request ? $hmvc : $view;
	}

	/**
	 * Show approved comments
	 */
	public function action_approved() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Blog_Comment::action_approved');

		// Get HMVC view for moderation queue
		$hmvc = Request::factory('comments/blog-admin/ham')->execute()->response;

		// Setup template view
		$view = View::factory('blog/admin/list')
			->set('menu', $this->menu())
			->set('list', $hmvc);

		// Set request response
		$this->template->content = $this->internal_request ? $hmvc : $view;
	}

	/**
	 * Show moderation queue
	 */
	public function action_queue() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Blog_Comment::action_queue');

		// Get HMVC view for moderation queue
		$hmvc = Request::factory('comments/blog-admin/queue')->execute()->response;

		// Setup template view
		$view = View::factory('blog/admin/list')
			->set('menu', $this->menu())
			->set('list', $hmvc);

		// Set request response
		$this->template->content = $this->internal_request ? $hmvc : $view;
	}

	/**
	 * Show spam comments
	 */
	public function action_spam() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Blog_Comment::action_spam');

		// Get HMVC view for moderation queue
		$hmvc = Request::factory('comments/blog-admin/spam')->execute()->response;

		// Setup template view
		$view = View::factory('blog/admin/list')
			->set('menu', $this->menu())
			->set('list', $hmvc);

		// Set request response
		$this->template->content = $this->internal_request ? $hmvc : $view;
	}

	/**
	 * Edit a comment
	 */
	public function action_edit() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Blog_Comment::action_edit');

		$id = $this->request->param('id');

		// Get HMVC view for comment edit form
		$hmvc = Request::factory('comments/blog-admin/update/'.$id)->execute()->response;

		// Check if update was successful
		if ($hmvc === TRUE)
		{
			Message::instance()->info('Comment has been updated');
			Request::instance()->redirect( Request::instance()->uri(array('action' => '', 'id' => NULL)) );
		}

		// Setup template view
		$form = View::factory('blog/admin/form')
			->set('menu', $this->menu())
			->set('form', $hmvc);

		// Set request response
		$this->template->content = $this->internal_request ? $hmvc : $form;
	}

	/**
	 * Delete a comment
	 */
	public function action_delete() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Blog_Comment::action_delete');

		$id = $this->request->param('id');

		// Get HMVC view for comment deletion
		$hmvc = Request::factory('comments/blog-admin/delete/'.$id)->execute()->response;

		// Check if deletion was successful
		if ($hmvc === TRUE)
		{
			Message::instance()->info('Comment has been deleted');
			Request::instance()->redirect( Request::instance()->uri(array('action' => '', 'id' => NULL)) );
		}

		// Check if deletion was unsuccessful or not attempted
		if ($hmvc === FALSE)
		{
			if (isset($_POST['yes']))
			{
				Message::instance()->error('An error occured deleting the comment');
			}
			Request::instance()->redirect( Request::instance()->uri(array('action' => '', 'id' => NULL)) );
		}

		// Setup template view
		$delete = View::factory('blog/admin/delete')
			->set('menu', $this->menu())
			->set('confirm', $hmvc);

		// Set request response
		$this->template->content = $this->internal_request ? $hmvc : $delete;
	}


}

