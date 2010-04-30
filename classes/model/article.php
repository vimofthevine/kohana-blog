<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Blog article model
 *
 * @package     Blog
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Model_Article extends Sprig
	implements Acl_Resource_Interface {

	public function _init() {
		$this->_fields += array(
			'id'         => new Sprig_Field_Auto,
			// Relationships
			'statistic'  => new Sprig_Field_HasOne(array(
				'model'  => 'statistic',
			)),
			'category'   => new Sprig_Field_BelongsTo(array(
				'model'  => 'category',
			)),
			'author'     => new Sprig_Field_BelongsTo(array(
				'model'  => 'user',
				'column' => 'author_id',
			)),
			/* To be implemented
			'comments'   => new Sprig_Field_HasMany(array(
				'model'  => 'comment',
			)),
			 */
			'tags'       => new Sprig_Field_ManyToMany(array(
				'model'  => 'tag',
			)),
			// Metadata
			'title' => new Sprig_Field_Char,
			'slug'  => new Sprig_Field_Char,
			'text'  => new Sprig_Field_Text,
			'date'  => new Sprig_Field_Timestamp(array(
				'auto_now_create' => TRUE,
			)),
			'state' => new Sprig_Field_Char(array(
				'choices' => array('draft','published','archived'),
			)),
		);
	}

	/**
	 * Acl_Resource_Interface implementation of get_resource_id
	 *
	 * @return  string
	 */
	public function get_resource_id() {
		return 'blog';
	}

}

