<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Blog article revision model
 *
 * @package     Blog
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Model_Article_Revision extends Versioned_Revision {

	public function _init() {
		parent::_init();
		$this->_fields += array(
			'entry' => new Sprig_Field_BelongsTo(array(
				'model' => 'Article',
			)),
		);
	}
}

