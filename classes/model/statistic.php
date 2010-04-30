<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Blog statistic model
 *
 * @package     Blog
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Model_Statistic extends Sprig {

	/**
	 * @var int Number of data points to record
	 */
	const LENGTH = 7;

	// Constant index for today
	const TODAY = LENGTH - 1;

	public function _init() {
		$this->_fields += array(
			'id'        => new Sprig_Field_Auto,
			'article'   => new Sprig_Field_BelongsTo(array(
				'model' => 'article',
			)),
			'data'      => new Sprig_Field_Char,
			'total'     => new Sprig_Field_Integer,
			'views'     => new Sprig_Field_Integer,
		);
	}

	/**
	 * Overload Sprig::__set() to serialize the data array
	 */
	public __set($name, $value) {
		if ($name == 'data')
		{
			$value = serialize($value);
		}
		return parent::__set($name, $value);
	}

	/**
	 * Overload Sprig::__get() to unserialize the data array
	 */
	public __get($name) {
		if ($name == 'data')
		{
			return unserialize($this->data);
		}
		else
		{
			return parent::_get($name);
		}
	}

	/**
	 * Perform page view count
	 * - Increment total view count
	 * - Increment weekly view count
	 * - Increment "today's" view count
	 */
	public function count() {
		// Increment total view count
		$this->total++;
		// Increment weekly view count
		$this->views++;
		// Increment today's view count
		$data = $this->data;
		$data[TODAY]++;
		$this->data = $data;

		return $this;
	}

	/**
	 * Perform daily view count reset
	 * - Reset weekly view count to 0
	 * - Cycle through daily data counts and shift data points
	 * - Reset "today's" view count to 0
	 */
	public function reset() {
		// Start over at 0
		$this->views = 0;
		$data = $this->data;

		foreach ($i = 0; $i < (TODAY); $i++)
		{
			$data[$i] = $data[$i+1];
			// Recount weekly view count
			$this->views += $data[$i];
		}

		$data[TODAY] = 0;
		$this->data = $data;
		return $this;
	}

}

