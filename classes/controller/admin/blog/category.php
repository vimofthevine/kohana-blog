<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Blog category management controller
 *
 * @package     Blog
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Controller_Admin_Blog_Category extends Controller_Template_Admin {

	/**
	 * Register controller as an admin controller
	 */
	public function before() {
		parent::before();

		$this->restrict('category', 'manage');
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
				'Create Category' => Request::instance()->uri(array('action'=>'new')),
			));
	}

	/**
	 * Display list of categories
	 */
	public function action_list() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Category::action_list');

		$categories = Sprig::factory('category')->load(NULL, FALSE);

		// Check if there are any categories to display
		if (count($categories) == 0)
		{
			$hmvc = View::factory('blog/admin/hmvc/category_none');

			$view = View::factory('blog/admin/list')
				->set('menu', $this->menu())
				->set('list', $hmvc);

			$this->template->content = $this->internal_request ? $hmvc : $view;
			return;
		}

		// Create category list
		$grid = new Grid;
		$grid->column()->field('id')->title('ID');
		$grid->column()->field('name')->title('Name');
		$grid->column('action')->title('Actions')->text('Edit')->class('edit')
			->action(Request::instance()->uri(array('action' => 'edit')));
		$grid->column('action')->title('')->text('Delete')->class('delete')
			->action(Request::instance()->uri(array('action' => 'delete')));
		$grid->data($categories);

		// Setup HMVC view with data
		$hmvc = View::factory('blog/admin/hmvc/category_list')
			->set('grid', $grid);

		// Setup template view
		$view = View::factory('blog/admin/list')
			->set('menu', $this->menu())
			->set('list', $hmvc);

		// Set request response
		$this->template->content = $this->internal_request ? $hmvc : $view;
	}

	/**
	 * Create a new category
	 */
	public function action_new() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Category::action_new');

		// Restrict access
		if ( ! $this->a2->allowed('category', 'create'))
		{
			$message = __('You do not have permission to create new categories.');

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

		$category = Sprig::factory('category')->values($_POST);

		try
		{
			$category->create();
			$message = __('The category, :name, has been created.', array(':name'=>$category->name));

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
			$hmvc = View::factory('blog/admin/hmvc/category_form')
				->set('legend', __('Create Category'))
				->set('submit', __('Create'))
				->set('category', $category)
				->set('errors', count($_POST) ? $e->array->errors('blog') : array() );

			// Setup template view
			$view = View::factory('blog/admin/form')
				->set('menu', $this->menu())
				->set('form', $hmvc);

			// Set request response
			$this->template->content = $this->internal_request ? $hmvc : $view;
		}
	}

	/**
	 * Edit category details
	 */
	public function action_edit() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Category::action_edit');

		$id = Request::instance()->param('id');
		$category = Sprig::factory('category', array('id' => $id))->load();

		// If category is invalid, return to list
		if ( ! $category->loaded())
		{
			$message = __('That category does not exist.');

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
		if ( ! $this->a2->allowed($category, 'edit'))
		{
			$message = __('You do not have permission to modify category, :name.', array(':name'=>$category->name));

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

		$category->values($_POST);

		// Setup HMVC view with data
		$hmvc = View::factory('blog/admin/hmvc/category_form')
			->set('legend', __('Modify Category'))
			->set('submit', __('Save'))
			->set('category', $category);

		if (count($_POST))
		{
			try
			{
				$category->update();
				$message = __('The category, :name, has been modified.', array(':name'=>$category->name));

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
		$view = View::factory('blog/admin/form')
			->set('menu', $this->menu())
			->set('form', $hmvc);

		// Set request response
		$this->template->content = $this->internal_request ? $hmvc : $view;
	}

	/**
	 * Delete a category
	 */
	public function action_delete() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Category::action_delete');

		// If deletion is not desired, redirect to list
		if (isset($_POST['no']))
		{
			Request::instance()->redirect( Request::instance()->uri(array('action'=>'', 'id'=>NULL)) );
		}

		$id = Request::instance()->param('id');
		$category = Sprig::factory('category', array('id' => $id))->load();
		$name = $category->name;

		// If category is invalid, return to list
		if ( ! $category->loaded())
		{
			$message = __('That category does not exist.');

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
		if ( ! $this->a2->allowed($category, 'delete'))
		{
			$message = __('You do not have permission to delete category, :name.', array(':name'=>$name));

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
				$category->delete();
				$message = __('The category, :name, has been deleted.', array(':name'=>$name));

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
				Kohana::$log->add(Kohana::ERROR, 'Error occured deleting category, id='.$category->id.', '.$e->getMessage());
				$message = __('An error occured deleting category, :name.', array(':name'=>$name));

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
		$hmvc = View::factory('blog/admin/hmvc/category_delete')
			->set('category', $category);

		// Setup template view
		$view = View::factory('blog/admin/delete')
			->set('menu', $this->menu())
			->set('confirm', $hmvc);

		// Set request response
		$this->template->content = $this->internal_request ? $hmvc : $view;
	}

}	// End of Controller_Admin_Category

