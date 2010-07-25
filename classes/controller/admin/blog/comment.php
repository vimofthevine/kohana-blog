<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Blog comment management controller
 *
 * @package     Admin
 * @category    Controller
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Controller_Admin_Blog_Comment extends Controller_Admin {

	protected $_resource = 'comment';

	protected $_acl_map = array(
		'default' => 'manage',
	);

	protected $_acl_required = 'all';

	protected $_view_map = array(
		'edit'    => 'admin/layout/narrow_column_with_menu',
		'delete'  => 'admin/layout/narrow_column_with_menu',
		'default' => 'admin/layout/wide_column_with_menu',
	);

	protected $_current_nav = 'admin/blog';

	/**
	 * Generate menu for comment management
	 */
	protected function _menu() {
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
				'Approved Comments'.$ham   => $this->request->uri(array('action' => 'approved')),
				'Moderation Queue'.$queued => $this->request->uri(array('action' => 'queue')),
				'Spam Comments'.$spam      => $this->request->uri(array('action' => 'spam')),
			));
	}

	/**
	 * Show report
	 */
	public function action_list() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Admin_Blog_Comment::action_list');
		$this->template->content = Request::factory('comments/blog-admin/report/86400')
			->execute()->response;
	}

	/**
	 * Show approved comments
	 */
	public function action_approved() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Admin_Blog_Comment::action_approved');
		$this->template->content = Request::factory('comments/blog-admin/ham')
			->execute()->response;
	}

	/**
	 * Show moderation queue
	 */
	public function action_queue() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Admin_Blog_Comment::action_queue');
		$this->template->content = Request::factory('comments/blog-admin/queue')
			->execute()->response;
	}

	/**
	 * Show spam comments
	 */
	public function action_spam() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Admin_Blog_Comment::action_spam');
		$this->template->content = Request::factory('comments/blog-admin/spam')
			->execute()->response;
	}

	/**
	 * Edit a comment
	 */
	public function action_edit() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Admin_Blog_Comment::action_edit');
		$id = $this->request->param('id');
		$this->template->content = Request::factory('comments/blog-admin/update/'.$id)
			->execute()->response;

		// Check if update was successful
		if ($this->template->content === TRUE)
		{
			Message::instance()->info('Comment has been updated');
			$this->request->redirect( $this->request->uri(array('action'=>NULL, 'id'=>NULL)) );
		}
	}

	/**
	 * Delete a comment
	 */
	public function action_delete() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Admin_Blog_Comment::action_delete');
		$id = $this->request->param('id');
		$this->template->content = Request::factory('comments/blog-admin/delete/'.$id)
			->execute()->response;

		// Check if deletion was successful
		if ($this->template->content === TRUE)
		{
			Message::instance()->info('Comment has been deleted');
			$this->request->redirect( $this->request->uri(array('action'=>NULL, 'id'=>NULL)) );
		}

		// Check if deletion was unsuccessful or not attempted
		if ($this->template->content === FALSE)
		{
			if (isset($_POST['yes']))
			{
				Message::instance()->error('An error occured deleting the comment');
			}
			$this->request->redirect( $this->request->uri(array('action'=>NULL, 'id'=>NULL)) );
		}
	}

}	// End of Controller_Admin_Blog_Comment

