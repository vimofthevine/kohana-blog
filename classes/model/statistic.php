<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Blog statistic model
 *
 * @package     Blog
 * @category    Model
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Model_Statistic extends Sprig {

	/**
	 * @var int Number of data points to record
	 */
	private $_length = 7;

	/**
	 * @var int Constant index for today
	 */
	private $_today = 6;

	/**
	 * Setup fields for the statistic model
	 */
	public function _init() {
		$this->_fields += array(
			'id'        => new Sprig_Field_Auto,
			'article'   => new Sprig_Field_BelongsTo(array(
				'model' => 'article',
			)),
			'data'      => new Sprig_Field_Char(array(
				'empty' => TRUE,
			)),
			'total'     => new Sprig_Field_Integer(array(
				'empty' => TRUE,
				'default' => 0,
			)),
			'views'     => new Sprig_Field_Integer(array(
				'empty' => TRUE,
				'default' => 0,
			)),
		);
	}

	/**
	 * Overload Sprig::__set() to serialize the data array
	 */
	public function __set($name, $value) {
		if ($name == 'data' AND is_array($value))
		{
			$value = implode(",", $value);
		}
		return parent::__set($name, $value);
	}

	/**
	 * Overload Sprig::__get() to unserialize the data array
	 */
	public function __get($name) {
		if ($name == 'data')
		{
			if (empty($this->_original['data']))
			{
				return array_fill(0, $this->_length, 0);
			}
			else
			{
				return explode(",", $this->_original['data']);
			}
		}
		else
		{
			return parent::__get($name);
		}
	}

	/**
	 * Perform page view count
	 *
	 * - Increment total view count
	 * - Increment weekly view count
	 * - Increment "today's" view count
	 */
	public function record() {
		// Increment total view count
		$this->total++;
		// Increment weekly view count
		$this->views++;
		// Increment today's view count
		$data = $this->data;
		$data[$this->_today] = $data[$this->_today] + 1;
		$this->data = $data;

		return $this;
	}

	/**
	 * Perform daily view count reset
	 *
	 * - Shift daily data counts
	 * - Reset "today's" view count to 0
	 * - Recalculate weekly view count
	 */
	public function reset() {
		// Import data locally
		$data = $this->data;
		// Shift data
		array_shift($data);
		// Reset "today's" data
		$data[] = 0;
		// Save data
		$this->data = $data;
		// Recount weekly view count
		$this->views = array_sum($data);

		return $this;
	}

}

