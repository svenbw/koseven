<?php
/**
 * Testing Unittest Module Helper Class
 * Very Important as other Unittests depend on that
 *
 * @package    KO7/Unittest
 * @group      koseven
 * @group      koseven.unittest
 * @category   Test
 *
 * 	   Koseven Team
 * @copyright  (c) since 2018 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
class HelpersTest extends Unittest_TestCase {

	/**
	 * Replaces all / in string with directory Separator
	 */
	public function test_dir_separator() : void
	{
		$expected = 'test'.DIRECTORY_SEPARATOR.'dir';
		self::assertSame($expected, KO7_Unittest_Helpers::dir_separator('test/dir'));
	}
	/**
	 * Tests cleaning cache dir which should happen recursivley.
	 * Following structure will be created to test that:
	 *
	 *   - Folder
	 *     .hidden-file
	 *     - Folder2
	 *       - file2
	 *   - file
	 */
	public function test_clean_cache_dir() : void
	{
		// Kohana Cache directory
		$cacheDir = KO7::$cache_dir;

		// Create Test Directories and Files
		$folder = $cacheDir.DIRECTORY_SEPARATOR.'Folder';
		$folder2 = $folder.DIRECTORY_SEPARATOR.'Folder2';
		$file = $folder.DIRECTORY_SEPARATOR.'.hidden-file';
		$file2 = $cacheDir.DIRECTORY_SEPARATOR.'file';
		$file3 = $folder2.DIRECTORY_SEPARATOR.'file2';
		if ( ! mkdir($folder) || ! mkdir($folder2) || ! touch($file) || ! touch($file2) || ! touch($file3)) {
			self::fail('Could not create Test Files. Please make sure your cache dir is writable');
		}
		KO7_Unittest_Helpers::clean_cache_dir();
		$files = scandir($cacheDir, 0);

		// Only files left should now be: '.','..','.gitignore'
		self::assertCount(3, $files);
	}
	/**
	 * Test setting configuration options via set environment
	 * @throws KO7_Exception
	 * @throws ReflectionException
	 */
	public function test_set_environment_config_options()
	{
		// Init
		$config = [
			'cache.default' => 'test'
		];
		$helpers = new KO7_Unittest_Helpers();

		// Set new environment and check if set correctly
		$helpers->set_environment($config);
		self::assertSame('test', KO7::$config->load('cache')->get('default'));

		// Restore old environment and check if restored
		$helpers->restore_environment();
		self::assertNotSame('test', KO7::$config->load('cache')->get('default'));
	}
}
