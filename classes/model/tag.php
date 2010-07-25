<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Blog tag model
 *
 * @package     Blog
 * @category    Model
 * @author      Kyle Treubig
 * @copyright   (c) 2010 B&K Web Design Solutions
 * @license     MIT
 */
class Model_Tag extends Sprig
	implements Acl_Resource_Interface {

	/**
	 * Setup fields for blog tags
	 */
	public function _init() {
		$this->_fields += array(
			'id'        => new Sprig_Field_Auto,
			'name'      => new Sprig_Field_Char,
			'articles'  => new Sprig_Field_ManyToMany(array(
				'model' => 'article',
			)),
		);
	}

	/**
	 * Get all published articles containing this tag
	 */
	public function published(Database_Query_Builder_Select $query = NULL, $limit = 1) {
		$field = $this->_fields['articles'];
		$model = Sprig::factory($field->model);
		$query = ( ! $query) ? DB::select() : $query;
		$query->join($field->through)
			->on($model->fk($field->through), '=', $model->pk(TRUE))
			->where($this->fk($field->through), '=', $this->{$this->_primary_key});
		return $model->load($query, $limit);
	}

	/**
	 * Overload Sprig::delete() to remove child articles
	 * from the article-tag pivot table
	 */
	public function delete(Database_Query_Builder_Delete $query = NULL) {
		Kohana::$log->add(Kohana::DEBUG, 'Beginning tag deletion for tag_id='.$this->id);
		if (Kohana::$profiling === TRUE)
		{
			$benchmark = Profiler::start('blog', 'delete tag');
		}

		try
		{
			DB::delete('articles_tags')->where('tag_id', '=', $this->id)->execute();
		}
		catch (Database_Exception $e)
		{
			Kohana::$log->add(Kohana::ERROR, 'Exception occured while modifying deleted tag\'s articles. '.$e->getMessage());
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
	 * @return  string
	 */
	public function get_resource_id() {
		return 'tag';
	}

}

