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

	public function __get($key) {
		if ($key == 'comment_list')
		{
			$return = '<ul>';
			foreach (parent::__get('comments') as $comment)
			{
				$return .= '<li>'.$comment.'</li>';
			}
			$return .= '</ul>'.PHP_EOL;
			return $return;
		}
		return parent::__get($key);
	}
}

