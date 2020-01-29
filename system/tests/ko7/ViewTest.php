<?php

/**
 * Tests the View class
 *
 * @group ko7
 * @group ko7.core
 * @group ko7.core.view
 *
 * @package    KO7
 * @category   Tests
 *
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
class KO7_ViewTest extends Unittest_TestCase
{
	protected static $old_modules = [];

	/**
	 * Setups the filesystem for test view files
	 *
	 * @return null
	 */
	// @codingStandardsIgnoreStart
	public static function setupBeforeClass() : void
	// @codingStandardsIgnoreEnd
	{
		self::$old_modules = KO7::modules();

		$new_modules = self::$old_modules+[
			'test_views' => realpath(dirname(__FILE__).'/../test_data/')
		];
		KO7::modules($new_modules);
	}

	/**
	 * Restores the module list
	 *
	 * @return null
	 */
	// @codingStandardsIgnoreStart
	public static function teardownAfterClass() : void
	// @codingStandardsIgnoreEnd
	{
		KO7::modules(self::$old_modules);
	}

	/**
	 * Provider for test_instaniate
	 *
	 * @return array
	 */
	public function provider_instantiate()
	{
		return [
			['ko7/error', FALSE],
			['test.css', FALSE],
			['doesnt_exist', TRUE],
		];
	}

	/**
	 * Provider to test_set
	 *
	 * @return array
	 */
	public function provider_set()
	{
		return [
			['foo', 'bar', 'foo', 'bar'],
			[['foo' => 'bar'], NULL, 'foo', 'bar'],
			[new ArrayIterator(['foo' => 'bar']), NULL, 'foo', 'bar'],
		];
	}

	/**
	 * Tests that we can instantiate a view file
	 *
	 * @test
	 * @dataProvider provider_instantiate
	 *
	 * @return null
	 */
	public function test_instantiate($path, $expects_exception)
	{
		try
		{
			$view = new View($path);
			$this->assertSame(FALSE, $expects_exception);
		}
		catch(View_Exception $e)
		{
			$this->assertSame(TRUE, $expects_exception);
		}
	}

	/**
	 * Tests that we can set using string, array or Traversable object
	 *
	 * @test
	 * @dataProvider provider_set
	 *
	 * @return null
	 */
	public function test_set($data_key, $value, $test_key, $expected)
	{
		$view = View::factory()->set($data_key, $value);
		$this->assertSame($expected, $view->$test_key);
	}

	/**
	 * Tests that we can set global using string, array or Traversable object
	 *
	 * @test
	 * @dataProvider provider_set
	 *
	 * @return null
	 */
	public function test_set_global($data_key, $value, $test_key, $expected)
	{
		$view = View::factory();
		$view::set_global($data_key, $value);
		$this->assertSame($expected, $view->$test_key);
	}
}
