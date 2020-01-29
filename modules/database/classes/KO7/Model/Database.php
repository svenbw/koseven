<?php
/**
 * Database Model base class.
 *
 * @package    KO7/Database
 * @category   Models
 *
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
abstract class KO7_Model_Database extends Model {

	/**
	 * Create a new model instance. A [Database] instance or configuration
	 * group name can be passed to the model. If no database is defined, the
	 * "default" database group will be used.
	 *
	 *     $model = Model::factory($name);
	 *
	 * @param   string   $name  model name
	 * @param   mixed    $db    Database instance object or string
	 * @return  Model
	 */
	public static function factory($name, $db = NULL)
	{
		// Add the model prefix
		$class = 'Model_'.$name;

		return new $class($db);
	}

	// Database instance
	protected $_db;

	/**
	 * Loads the database.
	 *
	 *     $model = new Foo_Model($db);
	 *
	 * @param   mixed  $db  Database instance object or string
	 * @return  void
	 */
	public function __construct($db = NULL)
	{
		if ($db)
		{
			// Set the instance or name
			$this->_db = $db;
		}
		elseif ( ! $this->_db)
		{
			// Use the default name
			$this->_db = Database::$default;
		}

		if (is_string($this->_db))
		{
			// Load the database
			$this->_db = Database::instance($this->_db);
		}
	}

} // End Model
