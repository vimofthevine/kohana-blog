<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Blog article model
 *
 * @package     Blog
 * @category    Model
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Model_Article extends Versioned_Sprig
	implements Acl_Resource_Interface {

	public function _init() {
		parent::_init();
		$this->_fields += array(
			'id'         => new Sprig_Field_Auto,
			// Metadata
			'title' => new Sprig_Field_Tracked,
			'slug'  => new Sprig_Field_Tracked(array(
				'editable' => FALSE,
			)),
			'date'  => new Sprig_Field_Timestamp(array(
				'auto_now_create' => TRUE,
				'editable'        => FALSE,
			)),
			'state' => new Sprig_Field_Tracked(array(
				'choices' => array(
					'draft'     => 'Draft',
					'published' => 'Published',
					'archived'  => 'Archived'
				),
			)),
			'text'  => new Sprig_Field_Versioned,
			'comment'   => new Sprig_Field_Char(array(
				'empty' => TRUE,
				'in_db' => FALSE,
				'label' => 'Reason for edit',
			)),
			// Relationships
			'statistic'  => new Sprig_Field_HasOne(array(
				'model'  => 'statistic',
			)),
			'category'   => new Sprig_Field_BelongsTo(array(
				'model'  => 'category',
				'null'   => FALSE,
			)),
			'author'     => new Sprig_Field_BelongsTo(array(
				'model'    => 'user',
				'column'   => 'author_id',
				'editable' => FALSE,
			)),
			/* To be implemented
			'comments'   => new Sprig_Field_HasMany(array(
				'model'  => 'comment',
			)),
			 */
			'revisions'  => new Sprig_Field_HasMany(array(
				'model'  => 'Article_Revision',
			)),
			'tags'       => new Sprig_Field_ManyToMany(array(
				'model'  => 'tag',
			)),
		);
	}

	/**
	 * Overload Sprig::__get() to get date fields
	 */
	public function __get($name) {
		if ($name == 'year')
		{
			return date("Y", $this->date);
		}
		elseif ($name == 'month')
		{
			return date("n", $this->date);
		}
		elseif ($name == 'day')
		{
			return date("j", $this->date);
		}
		else
		{
			return parent::__get($name);
		}
	}

	/**
	 * Overload Sprig::create() to set slug field
	 */
	public function create() {
		$this->slug = URL::title($this->title);
		return parent::create();
	}

	/**
	 * Overload Sprig::delete() to remove tags
	 * from the article-tag pivot table
	 */
	public function delete(Database_Query_Builder_Delete $query = NULL) {
		Kohana::$log->add(Kohana::DEBUG, 'Beginning article deletion for article_id='.$this->id);
		if (Kohana::$profiling === TRUE)
		{
			$benchmark = Profiler::start('blog', 'delete article');
		}

		try
		{
			DB::delete('articles_tags')->where('article_id', '=', $this->id)->execute();
		}
		catch (Database_Exception $e)
		{
			Kohana::$log->add(Kohana::ERROR, 'Exception occured while modifying deleted article\'s tags. '.$e->getMessage());
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
		return 'article';
	}

}

