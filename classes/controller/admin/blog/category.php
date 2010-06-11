<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Blog category management controller
 *
 * @package     Blog
 * @category    Controller
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Controller_Admin_Blog_Category extends Controller_Admin {

	protected $_resource = 'category';

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
				'Create Category' => $this->request->uri(array('action'=>'new')),
			));
	}

	/**
	 * Load the specified category
	 */
	protected function _load_resource() {
		$id = $this->request->param('id', 0);
		$this->_resource = Sprig::factory('category', array('id'=>$id))->load();
		if ( ! $this->_resource->loaded())
			throw new Kohana_Exception('That category does not exist.', NULL, 404);
	}

	/**
	 * Redirect index action to list
	 */
	public function action_index() {
		$this->request->redirect( $this->request->uri(
			array('action' => 'list')), 301);
	}

	/**
	 * Display list of categories
	 */
	public function action_list() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Admin_Category::action_list');
		$this->template->content = View::factory('blog/admin/category_list')
			->bind('request', $this->request)
			->bind('categories', $categories);
		$categories = Sprig::factory('category')->load(NULL, FALSE);
	}

	/**
	 * Create a new category
	 */
	public function action_new() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Admin_Category::action_new');
		$this->template->content = View::factory('blog/admin/category_form')
			->set('legend', __('Create Category'))
			->set('submit', __('Create'))
			->bind('category', $category)
			->bind('errors', $errors);

		$category = Sprig::factory('category')->values($_POST);

		if ($_POST)
		{
			try
			{
				$category->create();

				Message::instance()->info('The category, :name, has been created.',
					array(':name' => $category->name));

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
	 * Edit category details
	 */
	public function action_edit() {
		Kohana::$log->add(Kohana::DEBUG,
			'Executing Controller_Admin_Category::action_edit');
		$this->template->content = View::factory('blog/admin/category_form')
			->set('legend', __('Modify Category'))
			->set('submit', __('Save'))
			->bind('category', $this->_resource)
			->bind('errors', $errors);

		// Bind locally
		$category = & $this->_resource;

		if ($_POST)
		{
			$category->values($_POST);

			try
			{
				$category->update();

				Message::instance()->info('The category, :name, has been modified.',
					array(':name' => $category->name));

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
	 * Delete a category
	 */
	public function action_delete() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Category::action_delete');

		// If deletion is not desired, redirect to list
		if (isset($_POST['no']))
			$this->request->redirect( $this->request->uri(array('action'=>'list', 'id'=>NULL)) );

		$this->template->content = View::factory('blog/admin/category_delete')
			->bind('category', $this->_resource);

		// Bind locally
		$category = & $this->_resource;
		$name = $category->name;

		// If deletion is confirmed
		if (isset($_POST['yes']))
		{
			try
			{
				$category->delete();
				Message::instance()->info('The category, :name, has been deleted.',
					array(':name' => $name));

				if ( ! $this->_internal)
					$this->request->redirect( $this->request->uri(array('action'=>'list', 'id'=>NULL)) );
			}
			catch (Exception $e)
			{
				Kohana::$log->add(Kohana::ERROR, 'Error occured deleting category, id='.$category->id.', '.$e->getMessage());
				Message::instance()->error('An error occured deleting category, :name.',
					array(':name' => $name));

				if ( ! $this->_internal)
					$this->request->redirect( $this->request->uri(array('action'=>'list', 'id'=>NULL)) );
			}
		}
	}

}	// End of Controller_Admin_Category

