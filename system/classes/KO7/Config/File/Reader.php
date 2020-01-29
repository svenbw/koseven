<?php
/**
 * File-based configuration reader. Multiple configuration directories can be
 * used by attaching multiple instances of this class to [KO7_Config].
 *
 * @package    KO7
 * @category   Configuration
 *
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
class KO7_Config_File_Reader implements KO7_Config_Reader {

	/**
	 * The directory where config files are located
	 * @var string
	 */
	protected $_directory = '';

	/**
	 * Cached Configurations
	 * @var array
	 */
	protected static $_cache;

	/**
	 * Creates a new file reader using the given directory as a config source
	 *
	 * @param string    $directory  Configuration directory to search
	 */
	public function __construct($directory = 'config')
	{
		$this->_directory = trim($directory, '\/');
	}

	/**
	 * Load and merge all of the configuration files in this group.
	 *
	 *     $config->load($name);
	 *
	 * @param   string  $group  configuration group name
	 * @return  array   Configuration
	 * @throws KO7_Exception
	 */
	public function load($group): array
	{
		// Check caches and start Profiling
		if (KO7::$caching && isset(self::$_cache[$group]))
		{
			// This group has been cached
			// @codeCoverageIgnoreStart
			return self::$_cache[$group];
			// @codeCoverageIgnoreEnd
		}

		if (KO7::$profiling && class_exists('Profiler'))
		{
			// Start a new benchmark
			$benchmark = Profiler::start('Config', __FUNCTION__);
		}

		// Init
		$config = [];

		// Loop through paths. Notice: array_reverse, so system files get overwritten by app files
		foreach (array_reverse(KO7::include_paths()) as $path)
		{
			// Build path
			$file = $path.$this->_directory.DIRECTORY_SEPARATOR.$group;
			$value = FALSE;
			// Try .php .json and .yaml extensions and parse contents with PHP support
			if (file_exists($path = $file.'.php'))
			{
				$value = KO7::load($path);
			}
			elseif (file_exists($path = $file.'.json'))
			{
				$value = json_decode($this->read_from_ob($path), true);
			}
			elseif (file_exists($path = $file.'.yaml'))
			{
				if ( ! extension_loaded('yaml'))
				{
					// @codeCoverageIgnoreStart
					throw new KO7_Exception('PECL Yaml Extension is required in order to parse YAML Config');
					// @codeCoverageIgnoreEnd
				}
				$value = yaml_parse($this->read_from_ob($path));
			}

			// Merge config
			if ($value !== FALSE)
			{
				$config = Arr::merge($config, $value);
			}
		}

		if (KO7::$caching)
		{
			// @codeCoverageIgnoreStart
			self::$_cache[$group] = $config;
			// @codeCoverageIgnoreEnd
		}

		if (isset($benchmark))
		{
			// Stop the benchmark
			Profiler::stop($benchmark);
		}

		return $config;
	}

	/**
	 * Read Contents from file with output buffering.
	 * Used to support <?php ?> tags and code inside Configurations
	 *
	 * @param  string $path Path to File
	 * @return false|string
	 * @codeCoverageIgnore
	 */
	protected function read_from_ob($path)
	{
		// Start output buffer
		ob_start();

		KO7::load($path);

		// Get contents of buffer
		$content = ob_get_contents();

		// Clear Buffer
		ob_end_clean();

		return $content;
	}
}
