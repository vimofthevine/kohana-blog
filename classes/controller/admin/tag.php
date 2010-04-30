<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Blog tag management controller
 *
 * @package     Blog
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Controller_Admin_Tag extends Controller_Template_Admin {

	/**
	 * Register controller as an admin controller
	 */
	public function before() {
		parent::before();

		$this->restrict('tag', 'manage');
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
				'Create Tag' => Request::instance()->uri(array('action' => 'new')),
			));
	}

	/**
	 * Display list of tags
	 */
	public function action_list() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Tag::action_list');

		$tags = Sprig::factory('tag')->load(NULL, FALSE);

		// Check if there are any tags to display
		if (count($tags) == 0)
		{
			$hmvc = View::factory('blog/admin/hmvc/tag_none');

			$view = View::factory('blog/admin/list')
				->set('menu', $this->menu())
				->set('list', $hmvc);

			$this->template->content = $this->internal_request ? $hmvc : $view;
			return;
		}

		// Create tag list
		$grid = new Grid;
		$grid->column()->field('id')->title('ID');
		$grid->column()->field('name')->title('Name');
		$grid->column('action')->title('Actions')->text('Edit')->class('edit')
			->action(Request::instance()->uri(array('action' => 'edit')));
		$grid->column('action')->title('')->text('Delete')->class('delete')
			->action(Request::instance()->uri(array('action' => 'delete')));
		$grid->data($tags);

		// Setup HMVC view with data
		$hmvc = View::factory('blog/admin/hmvc/tag_list')
			->set('grid', $grid);

		// Setup template view
		$view = View::factory('blog/admin/list')
			->set('menu', $this->menu())
			->set('list', $hmvc);

		// Set request response
		$this->template->content = $this->internal_request ? $hmvc : $view;
	}

	/**
	 * Create a new tag
	 */
	public function action_new() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Tag::action_new');

		// Restrict access
		if ( ! $this->a2->allowed('tag', 'create'))
		{
			$message = __('You do not have permission to create new tags.');

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

		$tag = Sprig::factory('tag')->values($_POST);

		try
		{
			$tag->create();
			$message = __('The tag, :name, has been created.', array(':name'=>$tag->name));

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
			$hmvc = View::factory('blog/admin/hmvc/tag_form')
				->set('legend', __('Create Tag'))
				->set('submit', __('Create'))
				->set('tag', $tag)
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
	 * Edit tag details
	 */
	public function action_edit() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Tag::action_edit');

		$id = Request::instance()->param('id');
		$tag = Sprig::factory('tag', array('id' => $id))->load();

		// If tag is invalid, return to list
		if ( ! $tag->loaded())
		{
			$message = __('That tag does not exist.');

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
		if ( ! $this->a2->allowed($tag, 'edit'))
		{
			$message = __('You do not have permission to modify tag, :name.', array(':name'=>$tag->name));

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

		$tag->values($_POST);

		// Setup HMVC view with data
		$hmvc = View::factory('blog/admin/hmvc/tag_form')
			->set('legend', __('Modify Tag'))
			->set('submit', __('Save'))
			->set('tag', $tag);

		if (count($_POST))
		{
			try
			{
				$tag->update();
				$message = __('The tag, :name, has been modified.', array(':name'=>$tag->name));

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
	 * Delete a tag
	 */
	public function action_delete() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Tag::action_delete');

		// If deletion is not desired, redirect to list
		if (isset($_POST['no']))
		{
			Request::instance()->redirect( Request::instance()->uri(array('action'=>'', 'id'=>NULL)) );
		}

		$id = Request::instance()->param('id');
		$tag = Sprig::factory('tag', array('id' => $id))->load();
		$name = $tag->name;

		// If tag is invalid, return to list
		if ( ! $tag->loaded())
		{
			$message = __('That tag does not exist.');

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
		if ( ! $this->a2->allowed($tag, 'delete'))
		{
			$message = __('You do not have permission to delete tag, :name.', array(':name'=>$name));

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
				$tag->delete();
				$message = __('The tag, :name, has been deleted.', array(':name'=>$name));

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
				Kohana::$log->add(Kohana::ERROR, 'Error occured deleting tag, id='.$tag->id.', '.$e->getMessage());
				$message = __('An error occured deleting tag, :name.', array(':name'=>$name));

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
		$hmvc = View::factory('blog/admin/hmvc/tag_delete')
			->set('tag', $tag);

		// Setup template view
		$view = View::factory('blog/admin/delete')
			->set('menu', $this->menu())
			->set('confirm', $hmvc);

		// Set request response
		$this->template->content = $this->internal_request ? $hmvc : $view;
	}

}	// End of Controller_Admin_Tag

