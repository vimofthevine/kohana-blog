<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Comment model
 *
 * @package     Blog
 * @category    Model
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Model_Blog_Comment extends Sprig {

	/**
	 * Set fields for the blog comment model
	 */
	public function _init() {
		$this->_fields += array(
			'id'     => new Sprig_Field_Auto,
			'parent' => new Sprig_Field_BelongsTo(array(
				'column' => 'parent_id',
				'model'  => 'article',
			)),
			'state'  => new Sprig_Field_Char(array(
				'choices' => array('ham'=>'Ham', 'queued'=>'Queued', 'spam'=>'Spam'),
			)),
			'date'   => new Sprig_Field_Timestamp(array(
				'auto_now_create' => TRUE,
			)),
			'name'   => new Sprig_Field_Char(array(
				'min_length' => 3,
				'max_length' => 64,
			)),
			'email'  => new Sprig_Field_Email,
			'url'    => new Sprig_Field_Char(array(
				'empty' => TRUE,
			)),
			'text'   => new Sprig_Field_Text(array(
				'filters' => array(
					'Security::xss_clean' => NULL,
				),
			)),
		);
	}

	/**
	 * Overload Sprig::__get() to return
	 *
	 * - email md5 hash for gravatar
	 */
	public function __get($name) {
		if ($name == 'gravatar')
		{
			$email = md5(strtolower(trim($this->email)));
			return $email;
		}
		else
		{
			return parent::__get($name);
		}
	}

}

