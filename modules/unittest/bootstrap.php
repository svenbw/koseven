<?php

/**
 * The directory in which your application specific resources are located.
 * The application directory must contain the bootstrap.php file.
 */
$application = 'application';

/**
 * The directory in which your modules are located.
 */
$modules = 'modules';

/**
 * The directory in which the KO7 resources are located. The system
 * directory must contain the classes/ko7.php file.
 */
$system = 'system';

/**
 * The directory in which the KO7 public files are located. The public
 * directory contains for example the index.php and .htaccess files.
 */
$public = 'public';

/**
 * The default extension of resource files. If you change this, all resources
 * must be renamed to use the new extension.
 *
 * @link http://koseven.dev/guide/about.install#ext
 */
define('EXT', '.php');

/**
 * Set the path to the document root
 *
 * This assumes that this file is stored 2 levels below the DOCROOT, if you move
 * this bootstrap file somewhere else then you'll need to modify this value to
 * compensate.
 */
define('DOCROOT', dirname(__DIR__, 2).DIRECTORY_SEPARATOR);

/**
 * Set the PHP error reporting level. If you set this in php.ini, you remove this.
 * @link http://www.php.net/manual/errorfunc.configuration#ini.error-reporting
 *
 * When developing your application, it is highly recommended to enable notices
 * and warnings. Enable them by using: E_ALL
 *
 * In a production environment, it is safe to ignore notices. Disable them by
 * using: E_ALL & ~E_NOTICE
 *
 * When using a legacy application, it is recommended to disable deprecated
 * notices. Disable with: E_ALL & ~E_DEPRECATED
 */
error_reporting(E_ALL & ~E_DEPRECATED);

/**
 * End of standard configuration! Changing any of the code below should only be
 * attempted by those with a working knowledge of KO7 internals.
 *
 * @link http://koseven.dev/guide/using.configuration
 */

// Make the application relative to the docroot
if ( ! is_dir($application) && is_dir(DOCROOT.$application))
{
	$application = DOCROOT.$application;
}

// Make the modules relative to the docroot
if ( ! is_dir($modules) && is_dir(DOCROOT.$modules))
{
	$modules = DOCROOT.$modules;
}

// Make the system relative to the docroot
if ( ! is_dir($system) && is_dir(DOCROOT.$system))
{
	$system = DOCROOT.$system;
}

// Make the public relative to the docroot
if ( ! is_dir($public) && is_dir(DOCROOT.$public))
{
	$public = DOCROOT.$public;
}

// Define the absolute paths for configured directories
define('APPPATH', realpath($application).DIRECTORY_SEPARATOR);
define('MODPATH', realpath($modules).DIRECTORY_SEPARATOR);
define('SYSPATH', realpath($system).DIRECTORY_SEPARATOR);
define('PUBPATH', realpath($public).DIRECTORY_SEPARATOR);

// Clean up the configuration vars
unset($application, $modules, $system, $public);

/**
 * Define the start time of the application, used for profiling.
 */
if ( ! defined('KO7_START_TIME'))
{
	define('KO7_START_TIME', microtime(TRUE));
}

/**
 * Define the memory usage at the start of the application, used for profiling.
 */
if ( ! defined('KO7_START_MEMORY'))
{
	define('KO7_START_MEMORY', memory_get_usage());
}

// Bootstrap the application
require APPPATH.'bootstrap'.EXT;

// Disable output buffering
if (($ob_len = ob_get_length()) !== FALSE)
{
	// flush_end on an empty buffer causes headers to be sent. Only flush if needed.
	if ($ob_len > 0)
	{
		ob_end_flush();
	}
	else
	{
		ob_end_clean();
	}
}

// Enable all modules we can find
$modules_iterator = new DirectoryIterator(MODPATH);

$modules = [];

foreach ($modules_iterator as $module)
{
	if ($module->isDir() AND ! $module->isDot())
	{
		$modules[$module->getFilename()] = MODPATH.$module->getFilename();
	}
}

KO7::modules($modules);

unset($modules_iterator, $modules, $module);
