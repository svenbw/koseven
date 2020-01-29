<?php

/**
 * Unit tests for internal methods of userguide controller
 *
 * @group ko7
 * @group ko7.userguide
 * @group ko7.userguide.controller
 *
 * @package    KO7/Userguide
 * @category   Tests
 *
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
class Userguide_ControllerTest extends Unittest_TestCase
{

	public function provider_file_finds_markdown_files()
	{
		return [
			['userguide'.DIRECTORY_SEPARATOR.'adding', 'guide'.DIRECTORY_SEPARATOR.'userguide'.DIRECTORY_SEPARATOR.'adding.md'],
			['userguide'.DIRECTORY_SEPARATOR.'adding.md', 'guide'.DIRECTORY_SEPARATOR.'userguide'.DIRECTORY_SEPARATOR.'adding.md'],
			['userguide'.DIRECTORY_SEPARATOR.'adding.markdown', 'guide'.DIRECTORY_SEPARATOR.'userguide'.DIRECTORY_SEPARATOR.'adding.md'],
			['userguide'.DIRECTORY_SEPARATOR.'does_not_exist.md', FALSE]
		];
	}

	/**
	 * @dataProvider provider_file_finds_markdown_files
	 * @param  string  $page           Page name passed in the URL
	 * @param  string  $expected_file  Expected result from Controller_Userguide::file
	 */
	public function test_file_finds_markdown_files($page, $expected_file)
	{
		$controller = $this->createMock('Controller_Userguide');

		$cache_reflection = new ReflectionClass('Controller_Userguide');
		$file_method = $cache_reflection->getMethod('file');

		$path = $file_method->invoke($controller, $page);

		// Only verify trailing segments to avoid problems if file overwritten in CFS
		$expected_len = strlen($expected_file);
		$file = substr($path, -$expected_len, $expected_len);

		$this->assertEquals($expected_file, $file);
	}

}
