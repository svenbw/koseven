<?php

/**
 * Tests KO7 Core
 *
 * @TODO Use a virtual filesystem (see phpunit doc on mocking fs) for find_file etc.
 *
 * @group ko7
 * @group ko7.core
 * @group ko7.core.core
 *
 * @package    KO7
 * @category   Tests
 *
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
class KO7_CoreTest extends Unittest_TestCase
{
	protected $old_modules = [];

	/**
	 * Captures the module list as it was before this test
	 *
	 * @return null
	 */
	// @codingStandardsIgnoreStart
	public function setUp(): void
	// @codingStandardsIgnoreEnd
	{
		parent::setUp();
		$this->old_modules = KO7::modules();
	}

	/**
	 * Restores the module list
	 *
	 * @return null
	 */
	// @codingStandardsIgnoreStart
	public function tearDown(): void
	// @codingStandardsIgnoreEnd
	{
		KO7::modules($this->old_modules);
	}

	/**
	 * Provides test data for test_sanitize()
	 *
	 * @return array
	 */
	public function provider_sanitize()
	{
		return [
			// $value, $result
			['foo', 'foo'],
			["foo\r\nbar", "foo\nbar"],
			["foo\rbar", "foo\nbar"],
		];
	}

	/**
	 * Tests KO7::santize()
	 *
	 * @dataProvider provider_sanitize
	 * @covers KO7::sanitize
	 *
	 * @param boolean $value  Input for KO7::sanitize
	 * @param boolean $result Output for KO7::sanitize
	 */
	public function test_sanitize($value, $result)
	{
		$this->assertSame($result, KO7::sanitize($value));
	}

	/**
	 * Passing FALSE for the file extension should prevent appending any extension.
	 * See issue #3214
	 *
	 * @covers  KO7::find_file
	 */
	public function test_find_file_no_extension()
	{
		// EXT is manually appened to the _file name_, not passed as the extension
		$path = KO7::find_file('classes', $file = 'KO7/Core'.EXT, FALSE);

		$this->assertIsString($path);

		$this->assertStringEndsWith($file, $path);
	}

	/**
	 * If a file can't be found then find_file() should return FALSE if
	 * only a single file was requested, or an empty array if multiple files
	 * (i.e. configuration files) were requested
	 *
	 * @covers KO7::find_file
	 */
	public function test_find_file_returns_false_or_array_on_failure()
	{
		$this->assertFalse(KO7::find_file('configy', 'zebra'));

		$this->assertSame([], KO7::find_file('configy', 'zebra', NULL, TRUE));
	}

	/**
	 * KO7::list_files() should return an array on success and an empty array on failure
	 *
	 * @covers KO7::list_files
	 */
	public function test_list_files_returns_array_on_success_and_failure()
	{
		$files = KO7::list_files('config');

		$this->assertIsArray($files);
		$this->assertGreaterThan(3, count($files));

		$this->assertSame([], KO7::list_files('geshmuck'));
	}

	/**
	 * KO7::list_files() should only return files with specific extension if
	 * $ext param is passed to it
	 *
	 * @covers KO7::list_files
	 */
	public function test_list_files_with_extension() : void
	{
		// Test with string
		$ext = '.php';
		$files = KO7::list_files('tests' . DIRECTORY_SEPARATOR . 'test_data', [SYSPATH], $ext);
		array_walk_recursive($files, function($item) use ($ext)
		{
			self::assertSame($ext, '.'.pathinfo($item, PATHINFO_EXTENSION));
		});

		// Test with array
		$ext = ['.php', '.atom'];
		$files = KO7::list_files('tests' . DIRECTORY_SEPARATOR . 'test_data', [SYSPATH], $ext);
		array_walk_recursive($files, function($item) use ($ext)
		{
			self::assertContains('.'.pathinfo($item, PATHINFO_EXTENSION), $ext);
		});
	}

	/**
	 * Provides test data for testCache()
	 *
	 * @return array
	 */
	public function provider_cache()
	{
		return [
			// $value, $result
			['foo', 'hello, world', 10],
			['bar', NULL, 10],
			['bar', NULL, -10],
		];
	}

	/**
	 * Tests KO7::cache()
	 *
	 * @test
	 * @dataProvider provider_cache
	 * @covers KO7::cache
	 * @param boolean $key      Key to cache/get for KO7::cache
	 * @param boolean $value    Output from KO7::cache
	 * @param boolean $lifetime Lifetime for KO7::cache
	 */
	public function test_cache($key, $value, $lifetime)
	{
		KO7::cache($key, $value, $lifetime);
		$this->assertEquals($value, KO7::cache($key));
	}

	/**
	 * Tests KO7::find_file() cache is saved on shutdown.
	 *
	 * @test
	 */
	/*public function test_find_file_cache_saved()
	{
		$old_caching     = KO7::$caching;
		$old_errors      = KO7::$errors;
		KO7::$caching = TRUE;
		KO7::$errors  = FALSE;

		// trigger find_file() so KO7::$_files_changed is set to TRUE
		KO7::find_file('abc', 'def');

		// trigger shutdown so ko7 write to file cache
		KO7::shutdown_handler();

		$this->assertIsArray(KO7::file_cache('KO7::find_file()'));

		KO7::$caching = $old_caching;
		KO7::$errors  = $old_errors;
	}*/

	/**
	 * Provides test data for test_message()
	 *
	 * @return array
	 */
	public function provider_message()
	{
		return [
			['no_message_file', 'anything', 'default', 'default'],
			['no_message_file', NULL, 'anything', []],
			['ko7_core_message_tests', 'bottom_only', 'anything', 'inherited bottom message'],
			['ko7_core_message_tests', 'cfs_replaced', 'anything', 'overriding cfs_replaced message'],
			['ko7_core_message_tests', 'top_only', 'anything', 'top only message'],
			['ko7_core_message_tests', 'missing', 'default', 'default'],
			['ko7_core_message_tests', NULL, 'anything',
				[
					'bottom_only'  => 'inherited bottom message',
					'cfs_replaced' => 'overriding cfs_replaced message',
					'top_only'     => 'top only message'
				]
			],
		];
	}

	/**
	 * Tests KO7::message()
	 *
	 * @test
	 * @dataProvider provider_message
	 * @covers       KO7::message
	 * @param string $file     to pass to KO7::message
	 * @param string $key      to pass to KO7::message
	 * @param string $default  to pass to KO7::message
	 * @param string $expected Output for KO7::message
	 */
	public function test_message($file, $key, $default, $expected)
	{
		$test_path = realpath(dirname(__FILE__).'/../test_data/message_tests');
		KO7::modules(['top' => "$test_path/top_module", 'bottom' => "$test_path/bottom_module"]);

		$this->assertEquals($expected, KO7::message($file, $key, $default, $expected));
	}

	/**
	 * Provides test data for test_error_handler()
	 *
	 * @return array
	 */
	public function provider_error_handler()
	{
		return [
			[1, 'Foobar', 'foobar.php', __LINE__],
		];
	}

	/**
	 * Tests KO7::error_handler()
	 *
	 * @test
	 * @dataProvider provider_error_handler
	 * @covers KO7::error_handler
	 * @param boolean $code  Input for KO7::sanitize
	 * @param boolean $error  Input for KO7::sanitize
	 * @param boolean $file  Input for KO7::sanitize
	 * @param boolean $line Output for KO7::sanitize
	 */
	public function test_error_handler($code, $error, $file, $line)
	{
		$error_level = error_reporting();
		error_reporting(E_ALL);
		try
		{
			KO7::error_handler($code, $error, $file, $line);
		}
		catch (Exception $e)
		{
			$this->assertEquals($code, $e->getCode());
			$this->assertEquals($error, $e->getMessage());
		}
		error_reporting($error_level);
	}

	/**
	 * Provides test data for test_modules_sets_and_returns_valid_modules()
	 *
	 * @return array
	 */
	public function provider_modules_detects_invalid_modules()
	{
		return [
			[['unittest' => MODPATH.'fo0bar']],
			[['unittest' => MODPATH.'unittest', 'fo0bar' => MODPATH.'fo0bar']],
		];
	}

	/**
	 * Tests KO7::modules()
	 *
	 * @dataProvider provider_modules_detects_invalid_modules
	 * @param boolean $source   Input for KO7::modules
	 *
	 * @throws KO7_Exception
	 */
	public function test_modules_detects_invalid_modules($source)
	{
		$this->expectException(KO7_Exception::class);

		$modules = KO7::modules();

		try
		{
			KO7::modules($source);
		}
		catch(Exception $e)
		{
			// Restore modules
			KO7::modules($modules);

			throw $e;
		}

		// Restore modules
		KO7::modules($modules);
	}

	/**
	 * Provides test data for test_modules_sets_and_returns_valid_modules()
	 *
	 * @return array
	 */
	public function provider_modules_sets_and_returns_valid_modules()
	{
		return [
			[[], []],
			[['module' => __DIR__], ['module' => $this->dirSeparator(__DIR__.'/')]],
		];
	}

	/**
	 * Tests KO7::modules()
	 *
	 * @test
	 * @dataProvider provider_modules_sets_and_returns_valid_modules
	 * @param boolean $source   Input for KO7::modules
	 * @param boolean $expected Output for KO7::modules
	 */
	public function test_modules_sets_and_returns_valid_modules($source, $expected)
	{
		$modules = KO7::modules();

		try
		{
			$this->assertEquals($expected, KO7::modules($source));
		}
		catch(Exception $e)
		{
			KO7::modules($modules);

			throw $e;
		}

		KO7::modules($modules);
	}

	/**
	 * To make the tests as portable as possible this just tests that
	 * you get an array of modules when you can KO7::modules() and that
	 * said array contains unittest
	 *
	 * @test
	 * @covers KO7::modules
	 */
	public function test_modules_returns_array_of_modules()
	{
		$modules = KO7::modules();

		$this->assertIsArray($modules);

		$this->assertArrayHasKey('unittest', $modules);
	}

	/**
	 * Tests KO7::include_paths()
	 *
	 * The include paths must contain the apppath and syspath
	 * @test
	 * @covers KO7::include_paths
	 */
	public function test_include_paths()
	{
		$include_paths = KO7::include_paths();
		$modules       = KO7::modules();

		$this->assertIsArray($include_paths);

		// We must have at least 2 items in include paths (APP / SYS)
		$this->assertGreaterThan(2, count($include_paths));
		// Make sure said paths are in the include paths
		// And make sure they're in the correct positions
		$this->assertSame(APPPATH, reset($include_paths));
		$this->assertSame(SYSPATH, end($include_paths));

		foreach ($modules as $module)
		{
			$this->assertContains($module, $include_paths);
		}
	}
}

