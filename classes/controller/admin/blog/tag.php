<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Blog tag management controller
 *
 * @package     Admin
 * @category    Controller
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Controller_Admin_Blog_Tag extends Controller_Admin {

	protected $_resource = 'tag';

	protected $_acl_map = array(
		'new'     => 'create',
		'edit'    => 'edit',
		'delete'  => 'delete',
		'default' => 'manage',
	);

	protected $_acl_required = 'all';

	protected $_view_map = array(
		'list'    => 'admin/layout/wide_column_with_menu',
		'default' => 'admin/layout/narrow_column_with_menu',
	);

	protected $_resource_required = array('edit', 'delete');

	protected $_current_nav = 'admin/blog';

	/**
	 * Generate menu for blog management
	 */
	protected function _menu() {
		return View::factory('blog/admin/menu')
			->set('links', array(
				'Create Tag' => $this->request->uri(array('action'=>'new')),
			));
	}

	/**
	 * Load the specified category
	 */
	protected function _load_resource() {
		$id = $this->request->param('id', 0);
		$this->_resource = Sprig::factory('tag', array('id'=>$id))->load();
		if ( ! $this->_resource->loaded())
			throw new Kohana_Exception('That tag does not exist.', NULL, 404);
	}

	/**
	 * Redirect index action to list
	 */
	public function action_index() {
		$this->request->redirect( $this->request->uri(
			array('action' => 'list')), 301);
	}

	/**
	 * Display list of tags
	 */
	public function action_list() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Admin_Tag::action_list');
		$this->template->content = View::factory('blog/admin/tag_list')
			->bind('request', $this->request)
			->bind('tags', $tags);
		$tags = Sprig::factory('tag')->load(NULL, FALSE);
	}

	/**
	 * Create a new tag
	 */
	public function action_new() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Admin_Tag::action_new');
		$this->template->content = View::factory('blog/admin/tag_form')
			->set('legend', __('Create Tag'))
			->set('submit', __('Create'))
			->bind('tag', $tag)
			->bind('errors', $errors);

		$tag = Sprig::factory('tag')->values($_POST);

		if ($_POST)
		{
			try
			{
				$tag->create();

				Message::instance()->info('The tag, :name, has been created.',
					array(':name' => $tag->name));

				if ( ! $this->_internal)
					$this->request->redirect( $this->request->uri(array('action'=>'list')) );
			}
			catch (Validate_Exception $e)
			{
				$errors = $e->array->errors('admin');
			}
		}
	}

	/**
	 * Edit tag details
	 */
	public function action_edit() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Admin_Tag::action_edit');
		$this->template->content = View::factory('blog/admin/tag_form')
			->set('legend', __('Modify Tag'))
			->set('submit', __('Save'))
			->bind('tag', $this->_resource)
			->bind('errors', $errors);

		// Bind locally
		$tag = & $this->_resource;

		if ($_POST)
		{
			$tag->values($_POST);

			try
			{
				$tag->update();

				Message::instance()->info('The tag, :name, has been modified.',
					array(':name' => $tag->name));

				if ( ! $this->_internal)
					$this->request->redirect( $this->request->uri(array('action'=>'list', 'id'=>NULL)) );
			}
			catch (Validate_Exception $e)
			{
				$errors = $e->array->errors('admin');
			}
		}
	}

	/**
	 * Delete a tag
	 */
	public function action_delete() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Admin_Tag::action_delete');

		// If deletion is not desired, redirect to list
		if (isset($_POST['no']))
			$this->request->redirect( $this->request->uri(array('action'=>'list', 'id'=>NULL)) );

		$this->template->content = View::factory('blog/admin/tag_delete')
			->bind('tag', $this->_resource);

		// Bind locally
		$tag = & $this->_resource;
		$name = $tag->name;

		// If deletion is confirmed
		if (isset($_POST['yes']))
		{
			try
			{
				$tag->delete();
				Message::instance()->info('The tag, :name, has been deleted.',
					array(':name' => $name));

				if ( ! $this->_internal)
					$this->request->redirect( $this->request->uri(array('action'=>'list', 'id'=>NULL)) );
			}
			catch (Exception $e)
			{
				Kohana::$log->add(Kohana::ERROR, 'Error occured deleting tag, id='.$tag->id.', '.$e->getMessage());
				Message::instance()->error('An error occured deleting tag, :name.',
					array(':name' => $name));

				if ( ! $this->_internal)
					$this->request->redirect( $this->request->uri(array('action'=>'list', 'id'=>NULL)) );
			}
		}
	}

}	// End of Controller_Admin_Tag

