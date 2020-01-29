<?php

/**
 * Test case for Minion_Util
 *
 * @package    KO7/Minion
 * @group      ko7
 * @group      ko7.minion
 * @category   Test
 *
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */

class Minion_TaskTest extends KO7_Unittest_TestCase
{
	/**
	 * Default values for the environment, see setEnvironment
	 * @var array
	 */
	protected $environmentDefault =	[
		'url.trusted_hosts' => ['www\.example\.com', 'www\.example2\.com'],
		'site.minion_domain_name' => 'http://www.example2.com',

		// Keep the old request object and base_url
		// These are changed in set_domain_name and will cause other tests to
		// fail, so they need to be stored and restored in the tearDown method.
		'Request::$initial' => NULL,
		'Kohana::$base_url' => ''
	];

	/**
	 * Provides test data for test_convert_task_to_class_name()
	 *
	 * @return array
	 */
	public function provider_convert_task_to_class_name()
	{
		return [
			['Task_Db_Migrate', 'db:migrate'],
			['Task_Db_Status',  'db:status'],
			['', ''],
		];
	}

	/**
	 * Tests that a task can be converted to a class name
	 *
	 * @covers Minion_Task::convert_task_to_class_name
	 * @dataProvider provider_convert_task_to_class_name
	 * @param string Expected class name
	 * @param string Input task name
	 */
	public function test_convert_task_to_class_name($expected, $task_name)
	{
		$this->assertSame($expected, Minion_Task::convert_task_to_class_name($task_name));
	}

	/**
	 * Provides test data for test_convert_class_to_task()
	 *
	 * @return array
	 */
	public function provider_convert_class_to_task()
	{
		return [
			['db:migrate', 'Task_Db_Migrate'],
		];
	}

	/**
	 * Tests that the task name can be found from a class name / object
	 *
	 * @covers Minion_Task::convert_class_to_task
	 * @dataProvider provider_convert_class_to_task
	 * @param string Expected task name
	 * @param mixed  Input class
	 */
	public function test_convert_class_to_task($expected, $class)
	{
		$this->assertSame($expected, Minion_Task::convert_class_to_task($class));
	}

	/**
	 * Provides test data for test_set_domain_name()
	 *
	 * @return array
	 */
	public function provider_set_domain_name()
	{
		return [
			['https://www.example.com/welcome', 'https://www.example.com', 'welcome'],
			['http://www.example.com/welcome', 'http://www.example.com', 'welcome'],
			['http://www.example2.com/welcome', NULL, 'welcome'],
			['http://www.example.com:8080/welcome', 'http://www.example.com:8080', 'welcome'],
		];
	}

	/**
	 * Tests that a the domain name can be configured for url's
	 *
	 * @covers Minion_Task::set_domain_name
	 * @dataProvider provider_set_domain_name
	 * @param string Expected domain url
	 * @param string Input domain name
	 */
	public function test_set_domain_name($expected, $name, $uri)
	{
		Minion_Task::set_domain_name($name);

		$this->assertSame(
			$expected,
			URL::site($uri, TRUE, FALSE)
		);
	}
}
