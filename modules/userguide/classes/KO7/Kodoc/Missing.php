<?php
/**
 * Set Kodoc_Missing::create_class as an autoloading to prevent missing classes
 * from crashing the api browser.  Classes that are missing a parent will
 * extend this class, and get a warning in the API browser.
 *
 * @package    KO7/Userguide
 * @category   Undocumented
 * 
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license    https://koseven.dev/LICENSE
 * @since      3.0.7
 */
abstract class KO7_Kodoc_Missing {

	/**
	 * Creates classes when they are otherwise not found.
	 *
	 *     Kodoc::create_class('ThisClassDoesNotExist');
	 *
	 * [!!] All classes created will extend [Kodoc_Missing].
	 *
	 * @param   string   class name
	 * @return  boolean
	 * @since   3.0.7
	 */
	public static function create_class($class)
	{
		if ( ! class_exists($class))
		{
			// Create a new missing class
			if (FALSE === strpos($class, '\\'))
			{
				eval("class {$class} extends Kodoc_Missing {}");
			}
			else
			{
				$namespace = explode('\\', $class);
				$class = array_pop($namespace);
				eval("namespace ".implode('\\', $namespace)."; class {$class} extends \Kodoc_Missing {}");
			}
		}

		return TRUE;
	}

} // End KO7_Kodoc_Missing
