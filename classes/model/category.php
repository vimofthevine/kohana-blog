<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Blog category model
 *
 * @package     Blog
 * @author      Kyle Treubig
 * @copyright   (c) 2010 B&K Web Design Solutions
 * @license     MIT
 */
class Model_Category extends Sprig
	implements Acl_Resource_Interface {

	/**
	 * Setup model fields
	 */
	public function _init() {
		$this->_fields += array(
			'id'       => new Sprig_Field_Auto,
			'name'     => new Sprig_Field_Char,
			'articles' => new Sprig_Field_HasMany,
		);
	}

	/**
	 * Get all published articles belonging to this category
	 */
	public function published(Database_Query_Builder_Select $query = NULL, $limit = 1) {
		return Sprig::factory('article', array(
			'state'    => 'published',
			'category' => $this,
		))->load($query, $limit);
	}

	/**
	 * Overload Sprig::delete() to update child articles
	 * to become children of the uncategorized category
	 */
	public function delete(Database_Query_Builder_Delete $query = NULL) {
		Kohana::$log->add(Kohana::DEBUG, 'Beginning category deletion for category_id='.$this->id);
		if (Kohana::$profiling === TRUE)
		{
			$benchmark = Profiler::start('blog', 'delete category');
		}

		$uncategorized = Sprig::factory('category', array('name'=>'uncategorized'))->load();

		// Modify category IDs for all child articles
		try
		{
			DB::update('articles')->value('category_id', $uncategorized->id)
				->where('category_id', '=', $this->id)->execute();
		}
		catch (Database_Exception $e)
		{
			Kohana::$log->add(Kohana::ERROR, 'Exception occured while modifying deleted category\'s articles. '.$e->getMessage());
			return $this;
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}

		return parent::delete($query);
	}

	/**
	 * Acl_Resource_Interface implementation of get_resource_id
	 *
	 * If the current category is uncategorized, return a bogus
	 * resource to prevent deletion/modification
	 *
	 * @return  string
	 */
	public function get_resource_id() {
		if ($this->loaded() AND $this->name == 'uncategorized')
			return 'bogus_resource';
		else
			return 'category';
	}

}

